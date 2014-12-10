<?php
class Mcatalogue_mass_edit_price extends AG_Model
{
	const CAT 				= 'm_c_categories';
	const ID_CAT 			= 'id_m_c_categories';
	const CAT_DESC 			= 'm_c_categories_description';
	const ID_CAT_DESC 		= 'id_m_c_categories_description';
	const CAT_LINK			= 'm_c_categories_link';
	
	const PR 	= 'm_c_products';
	const ID_PR = 'id_m_c_products';
	const PR_DESC = 'm_c_products_description';
	const PR_PRICE 	= 'm_c_products_price';
	const ID_PR_PRICE = 'id_m_c_products_price';
	
	const PR_CAT = 'm_c_productsNcategories';
	const ID_PR_CAT = 'id_m_c_productsNcategories';
	
	private $tree_array = array();
	
	public $id_categorie = FALSE;
	
	function __construct()
	{
		parent::__construct();
	}
	
	public function render_categories_grid()
	{
		$this->load->helper('aggrid_tree_helper');
		$Grid = new Aggrid_tree_Helper('catalogue_categories_mass_edit_price_grid');
		
		$Grid->db	->select("A.`".self::ID_CAT."` AS ID, A.`id_parent`, A.`level`, A.`sort` AS sort, A.`active`, A.`create_date`, A.`update_date`, B.`name`, 
							(SELECT COUNT(*) FROM `".self::CAT."` WHERE `id_parent` = A.`".self::ID_CAT."`) AS PARENT_COUNT")
					->from("`".self::CAT."` AS A")
					->join(	"`".self::CAT_DESC."` AS B",
							"B.`".self::ID_CAT."` = A.`".self::ID_CAT."` && B.`".self::ID_LANGS."` = ".$this->id_langs,
							"left")
					->where("A.`".self::ID_USERS."`",$this->id_users)->order_by('sort');
					
		$this->load->helper('catalogue/catalogue_mass_edit_price_helper');
		
		$Grid = helper_catalogue_mass_edit_price_grid_build($Grid);
		$Grid->createDataArray();
		$Grid->updateGridValues('active', array('0' => 'Нет', '1' => 'Да'));
		$Grid->renderGrid();
	}
	
	public function render_actions($cat_id)
	{
		if(($cat_id = intval($cat_id))>0)
		{
			if($this->check_isset_categorie($cat_id))
			{
				$query = $this->db->select("B.`name`")
					->from("`".self::CAT."` AS A")
					->join(	"`".self::CAT_DESC."` AS B",
							"B.`".self::ID_CAT."` = A.`".self::ID_CAT."` && B.`".self::ID_LANGS."` = ".$this->id_langs,
							"LEFT")
					->where("A.`".self::ID_CAT."`", $cat_id)->limit(1);
				$result = $query->get()->row_array();
				
				$this->template->add_navigation($result['name']);

				$data['products'] = $this->get_categories_products_grid($cat_id);
				
				helper_catalogue_mass_edit_price_action_form_build($cat_id, $data);
				return TRUE;
			}
			return FALSE;
		}
		return FALSE;
	}
	
	public function get_categories_products_grid($cat_id)
	{ //echo var_dump($this->input->post('search'));
		$this->load->library('nosql_grid');
		$this->nosql_grid->_init_grid('products_mass_edit_price_grid', array( 'limit' => '', 'url' => setUrl('catalogue_mass_sale/get_ajax_categories_products/cat_id/'.$cat_id)), TRUE);
		$this->nosql_grid->init_fixed_buttons(FALSE);
		$query = $this->db
			->select("A.`".self::ID_PR."` AS ID, A.`sku`, B.`name`, A.`status`, A.`in_stock`, A.`sale`, PRICE.`price`, PRICE.`special_price`, PRICE.`special_price_from`, PRICE.`special_price_to`")
			->from("`".self::PR."` AS A")
			->join("`".self::PR_CAT."` AS C",
					"C.`".self::ID_CAT."` = '".$cat_id."' && C.`".self::ID_PR."` = A.`".self::ID_PR."`",
					"INNER")
			->join(	"`".self::PR_DESC."` AS B",
					"B.`".self::ID_PR."` = A.`".self::ID_PR."` && B.`".self::ID_LANGS."` = ".$this->id_langs,
					"LEFT")
			->join( "`".self::PR_PRICE."` AS PRICE",
						"PRICE.`".self::ID_PR."` = A.`".self::ID_PR."` && PRICE.`".self::ID_PR_PRICE."` = (SELECT `".self::ID_PR_PRICE."` FROM `".self::PR_PRICE."` WHERE `".self::ID_PR."` = A.`".self::ID_PR."` ORDER BY `".self::ID_PR_PRICE."` LIMIT 1)",
						"LEFT")
			->where("A.`".self::ID_USERS."`", $this->id_users)
			->order_by("A.`".self::ID_PR."`");
		$result = $query->get()->result_array();
		$this->load->helper('catalogue/catalogue_mass_edit_price_helper');
		helper_catalogue_mass_edit_price_categorie_products_grid_build($this->nosql_grid, $cat_id);
		$this->nosql_grid->set_grid_data($result);
		
		//$this->grid->create_grid_data();
		$this->nosql_grid->update_grid_data('in_stock',array('0'=>'Нет', '1'=>'Да'));
		$this->nosql_grid->update_grid_data('status',array('0'=>'Нет', '1'=>'Да'));
		$this->nosql_grid->update_grid_data('sale',array('0'=>'Нет', '1'=>'Да'));

		//echo var_dump($this->nosql_grid->get_options());


		return $this->nosql_grid->render_grid(TRUE);
	}
	
	public function check_isset_categorie($cat_id)
	{
		$query = $this->db->select("COUNT(*) AS COUNT")
				->from("`".self::CAT."`")
				->where("`".self::ID_USERS."`",$this->id_users)
				->where("`".self::ID_CAT."`", $cat_id);
		$result = $query->get()->row_array();
		if($result['COUNT'] == 1)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	public function save_changes($cat_id)
	{
		if($POST = $this->input->post('sale_actions'))
		{ //echo var_dump($POST);
			if($PROD = $this->input->post('products_checkbox'))
			{
				if($POST['type'] == 'percent')
				{
					if($POST['value'] >= 100) return FALSE;
					$p = (100 - $POST['value'])/100;
					$spd = '';
					if($POST['special_price_from'] != '') $spd .= " , `special_price_from` = '".$POST['special_price_from']."'"; else $spd .= " , `special_price_from` = NULL";
					if($POST['special_price_to'] != '') $spd .= " , `special_price_to` = '".$POST['special_price_to']."'"; else $spd .= " , `special_price_to` = NULL";
					$pr_str = '';
					foreach($PROD as $ms)
					{
						$pr_str .= $ms.',';
					}
					$pr_str = substr($pr_str,0,-1);

					$this->db->query("UPDATE `".self::PR_PRICE."` SET `special_price` = ROUND(`price` * ".$p.", 0) ".$spd." WHERE `".self::ID_PR."` IN(".$pr_str.")");
					if(intval($POST['sale_options']) == 1)
					{
						$this->db->query("UPDATE `".self::PR."` SET `sale` = '1' WHERE `".self::ID_PR."` IN(".$pr_str.")");
					}
					return TRUE;
				}
				if($POST['type'] == 'minus_price')
				{
					if($POST['value'] <= 0) return FALSE;
					$p = $POST['value'];
					$spd = '';
					if($POST['special_price_from'] != '') $spd .= " , `special_price_from` = '".$POST['special_price_from']."'"; else $spd .= " , `special_price_from` = NULL";
					if($POST['special_price_to'] != '') $spd .= " , `special_price_to` = '".$POST['special_price_to']."'"; else $spd .= " , `special_price_to` = NULL";
					$pr_str = '';
					foreach($PROD as $ms)
					{
						$pr_str .= $ms.',';
					}
					$pr_str = substr($pr_str,0,-1);
					$this->db->query("UPDATE `".self::PR_PRICE."` SET `special_price` = ROUND(`price` - ".$p.", 0) ".$spd." WHERE `".self::ID_PR."` IN(".$pr_str.")");
					$this->db->query("UPDATE `".self::PR."` SET `sale` = '1' WHERE `".self::ID_PR."` IN(".$pr_str.")");
					return TRUE;
				}
				/*
				if($POST['type'] == 'fixed_price')
				{
					if($POST['value'] <= 100) return FALSE;
					$p = $POST['value'];
					$spd = '';
					if($POST['special_price_from'] != '') $spd .= " , `special_price_from` = '".$POST['special_price_from']."'"; else $spd .= " , `special_price_from` = NULL";
					if($POST['special_price_to'] != '') $spd .= " , `special_price_to` = '".$POST['special_price_to']."'"; else $spd .= " , `special_price_to` = NULL";
					$pr_str = '';
					foreach($PROD as $ms)
					{
						$pr_str .= $ms.',';
					}
					$pr_str = substr($pr_str,0,-1);
					$this->db->query("UPDATE `".self::PR_PRICE."` SET `special_price` = '".$p."' ".$spd." WHERE `".self::ID_PR."` IN(".$pr_str.")");
					$this->db->query("UPDATE `".self::PR."` SET `sale` = '1' WHERE `".self::ID_PR."` IN(".$pr_str.")");
					return TRUE;
				}*/
				if($POST['type'] == 'cancel')
				{
					$spd = '';
					$spd .= " , `special_price_from` = NULL";
					$spd .= " , `special_price_to` = NULL";
					
					$pr_str = '';
					foreach($PROD as $ms)
					{
						$pr_str .= $ms.',';
					}
					$pr_str = substr($pr_str,0,-1);
					$this->db->query("UPDATE `".self::PR_PRICE."` SET `special_price` = NULL ".$spd." WHERE `".self::ID_PR."` IN(".$pr_str.")");
					$this->db->query("UPDATE `".self::PR."` SET `sale` = '0' WHERE `".self::ID_PR."` IN(".$pr_str.")");
					return TRUE;
				}
				return FALSE;
			}
			return FALSE;
		}
		return FALSE;
	}
}	
?>