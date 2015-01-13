<?php 
class Mproducts_types extends AG_Model
{
	const PR_TYPES			= 'm_c_products_types';
	const ID_PR_TYPES		= 'id_m_c_products_types';
	const PR_TYPES_DESC		= 'm_c_products_types_description';
	const ID_PR_TYPES_DESC 	= 'id_m_c_products_types_description';
	
	public $id_type = FALSE;
	
	function __construct()
	{
		parent::__construct();
	}
	
	public function get_collection_to_select()
	{
		$query = $this->db ->select("A.`".self :: ID_PR_TYPES."` AS ID, A.`alias`, B.`name`") 
				->from("`".self :: PR_TYPES."` AS A")
				->join( "`".self :: PR_TYPES_DESC."` AS B",
						"B.`".self :: ID_PR_TYPES."` = A.`".self :: ID_PR_TYPES."` && B.`".self::ID_LANGS."` = ".$this->id_langs,
						"LEFT")
				->where("A.`".self::ID_USERS."`", $this->id_users)->order_by("A.`sort`");
	  
		$result = $query->get()->result_array();
		$return = array();
		foreach($result as $ms)
		{
			$return[$ms['ID']] = $ms['alias'].' - '.$ms['name']; 
		}
		return $return;    
	}
	
	public function render_types_grid()
	{
		$this->load->library("grid");
		$this->grid->_init_grid("products_types_grid", array());
		
		$this->grid->db	
			->select("A.`".self::ID_PR_TYPES."` AS ID, A.`sort`, A.`active`, A.`alias`, B.`name`,")
			->from("`".self::PR_TYPES."` AS A")
			->join("`".self::PR_TYPES_DESC."` AS B", 
				   "B.`".self::ID_PR_TYPES."` = A.`".self::ID_PR_TYPES."` && B.`".self::ID_LANGS."` = '".$this->id_langs."'",
				   "left") 
			->where("A.`".self::ID_USERS."`", $this->id_users);
		
		$this->load->helper('catalogue/products_types_helper');
		$Grid = helper_products_types_grid_build($this->grid);
		$this->grid->add_extra_sort('sort');
		$this->grid->create_grid_data();
		$this->grid->update_grid_data_using_string("sort", "<a class='arrow_down' href='".set_url('*/*/change_position/')."id/$1/type/down' title='Смена позиции: Опустить'></a><a class='arrow_up' href='".set_url('*/*/change_position/')."id/$1/type/up' title='Смена позиции: Поднять'></a>", array('$1' => 'ID'));

		$this->grid->update_grid_data('active', array('0'=>'Нет', '1'=>'Да'));
		$this->grid->render_grid();
	}
	
	public function add()
	{
		$this->load->helper('catalogue/products_types_helper');
		
		$this->load->model('langs/mlangs'); 
		$data['on_langs'] = $this->mlangs->get_active_languages();
		helper_products_types_form_build($data);
	}
	
	public function edit($id)
	{
		$result = $this->get_edit_query($id);
		$result = $result->get()->result_array();
		$data = array();
		if(count($result)>0)
		{
			foreach($result as $ms)
			{
				$data['main']['alias'] = $ms['alias'];
				$data['main']['active'] = $ms['active'];
				$data['desc'][$ms['id_langs']] = $ms;
				unset($data['desc'][$ms['id_langs']]['alias']);
				unset($data['desc'][$ms['id_langs']]['ID']);
				unset($data['desc'][$ms['id_langs']]['active']);	
			}
			$this->load->model('langs/mlangs');
			$data['on_langs'] = $this->mlangs->get_active_languages();
			
			$this->load->helper('catalogue/products_types_helper');
			
			helper_products_types_form_build($data,'/id/'.$id);
			return TRUE;
		}
		return FALSE;
	}
	
	public function check_isset_type($id)
	{
		$id = intval($id);
		$query = $this->db->select("COUNT(*) AS COUNT")->from("`".self::PR_TYPES."`")->where("`".self::ID_USERS."`", $this->id_users)->where("`".self::ID_PR_TYPES."`", $id)->limit(1);
		$result = $query->get()->row_array();
		if($result['COUNT'] == 0)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	public function check_isset_alias($alias)
	{
		$alias = trim($alias);
		$query = $this->db->select("COUNT(*) AS COUNT")->from("`".self::PR_TYPES."`")->where("`".self::ID_USERS."`", $this->id_users)->where("`alias`", $alias)->limit(1);
		if($this->id_type)
		{
			$query->where("`".self::ID_PR_TYPES."` <>", $this->id_type);
		}
		$result = $query->get()->row_array();
		if($result['COUNT'] == 0)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	public function set_validation()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('main[alias]','Индификатор','trim|required|check_isset_alias');
		$this->form_validation->set_message('check_isset_alias', 'Группа с указанным индификатором уже существует!');
	}
	
	public function save($id = FALSE)
	{
		if($this->input->post('main'))
		{
			if($id)
			{
				//$this->id_attribute = $id;
				$query = $this->db->select("B.`".self::ID_PR_TYPES_DESC."`, B.`".self::ID_LANGS."`")
							->from("`".self::PR_TYPES."` AS A")
							->join("`".self::PR_TYPES_DESC."` AS B",
									"B.`".self::ID_PR_TYPES."` = A.`".self::ID_PR_TYPES."`",
									"LEFT")
							->where("A.`".self::ID_PR_TYPES."`", $id)->where("A.`".self::ID_USERS."`", $this->id_users);
				$lang_result_temp = $query->get()->result_array();
				if(count($lang_result_temp)>0)
				{
					$lang_result = array();
					foreach($lang_result_temp as $ms)
					{
						$lang_result[$ms['id_langs']] = $ms;
					}
					
					$POST = $this->input->post('main');
					$MPOST = array('alias' => $POST['alias'], 'active' => $POST['active']);
					
					$this->db->trans_start();
					$result = $this->sql_add_data($MPOST)->sql_using_user()->sql_save(self::PR_TYPES, $id);
					if($result && ($POST = $this->input->post('desc')) != FALSE)
					{
						$this->load->model('langs/mlangs');
						$langs = $this->mlangs->get_active_languages();
						foreach($langs as $key => $ms)
						{
							if(isset($POST[$key]))
							{
								if(isset($lang_result[$key])) 
								{
									$this->sql_add_data($POST[$key])->sql_save(self::PR_TYPES_DESC, $lang_result[$key][self::ID_PR_TYPES_DESC]);
								}
								else
								{
									$this->sql_add_data($POST[$key]+array(self::ID_PR_TYPES => $id, self::ID_LANGS => $key))->sql_save(self::PR_TYPES_DESC);
								}
							}
						}
						$this->db->trans_complete();
						if($this->db->trans_status()) 
						{
							return TRUE;
						}
					}
					return FALSE;
				}
				return FALSE;
				}
				else
				{
					$POST = $this->input->post('main');
					
					$this->db->trans_start();
					$ID = $this->sql_add_data($POST)->sql_using_user()->sql_save(self :: PR_TYPES);
					if($ID && $ID > 0 && ($POST = $this->input->post('desc')) != FALSE)
					{
						$this->sql_add_data(array('sort' => $ID))->sql_using_user()->sql_save(self :: PR_TYPES, $ID);
						$this->load->model('langs/mlangs');
						$langs = $this->mlangs->get_active_languages();
						foreach($langs as $key => $ms)
						{
							if(isset($POST[$key]))
							{
								$data = $POST[$key] + array('id_langs' => $key) + array(self :: ID_PR_TYPES => $ID);
								$this->sql_add_data($data)->sql_save(self :: PR_TYPES_DESC);
							}
						}
						$this->db->trans_complete();
						if($this->db->trans_status()) 
						{
							return $ID; 
						}
						else
						{
							$this->set_post_to_session();
							return FALSE;
						}
					}
					return FALSE;
				}
				
			}
					
		return FALSE;	
	}
	
	public function set_post_to_session()
	{
		$this->session->set_flashdata('products_types_add_edit_form', $this->input->post());
		return $this;
	}
	
	public function get_edit_query($id, $id_langs = FALSE)
	{
		if($id_langs)
		{
			$select = "B.`".self::ID_LANGS."`, B.`".self::ID_PR_TYPES_DESC."` AS DID"; 
		}
		else
		{
			$select = "A.`".self::ID_PR_TYPES."` AS ID, A.`active`, A.`alias`, B.`name`, B.`description`, B.`".self::ID_LANGS."`, B.`".self::ID_PR_TYPES_DESC."`";	
		}
		
		$result = $this->db ->select($select)
							->from("`".self::PR_TYPES."` AS A")
							->join("`".self::PR_TYPES_DESC."` AS B",
								   "B.`".self::ID_PR_TYPES."` = A.`".self::ID_PR_TYPES."`", 
								   "LEFT")
							->where("A.`".self::ID_PR_TYPES."`", $id)->where("A.`".self::ID_USERS."`", $this->id_users);
		return $result;
	}

	public function delete($id)
	{
		if(is_array($id))
		{
			$this->db->where_in(self::ID_PR_TYPES, $id)->where("`".self::ID_USERS."`",$this->id_users);  
			$this->db->delete(self::PR_TYPES);
			return TRUE;
		}
		$result = $this->db	->select("count(*) AS COUNT")
							->from("`".self :: PR_TYPES."` AS A")
							->where("A.`".self :: ID_PR_TYPES."`", $id)->where("`A.`".self::ID_USERS."`", $this->id_users);
		$result = $result->get()->row_array();
		if($result['COUNT'] > 0)
		{	
			
			$this->db->where(self::ID_PR_TYPES, $id)->where("`".self::ID_USERS."`", $this->id_users);
			if($this->db->delete(self::PR_TYPES))
			{
				return TRUE;
			}
			return FALSE;
		}
		return FALSE;
			
	}
	
	public function activate($id, $activate = 1)
	{
		if(is_array($id))
		{
			$data = array('active' => $activate); 
			foreach($id as $ms) 
			{
				$this->sql_add_data($data)->sql_using_user()->sql_save(self::PR_TYPES, $ms); 
			}
			return TRUE;
		}
		return FALSE;
	}
	
	public function change_position($id, $type)
	{
		switch($type)
		{
			case "up":
				return $c_id = $this->_change_position_query('<=', $id);
			break;
			case "down":
				return $c_id = $this->_change_position_query('>=', $id);
			break;
		}
		return FALSE;
	}
	
	private function _change_position_query($type, $id)
	{
		$OB = '';
		if($type == '<=')
		{
			$OB = 'DESC';
		}
		$query = $this->db
			->select("DISTINCT(A.`".self::ID_PR_TYPES."`) AS ID, A.`sort` AS SORT ")
			->from("`".self::PR_TYPES."` AS A")
			->where("A.`".self::ID_USERS."`", $this->id_users)
			->where("`sort` ".$type." (SELECT `sort` FROM `".self::PR_TYPES."` WHERE `".self::ID_PR_TYPES."` = '".$id."' LIMIT 1)")
			->order_by('sort', $OB)->limit(2);
		//echo $this->db->_compile_select();

		$query = $query->get();
		if($query->num_rows() == 2)
		{
			$result = $query->result_array();
				
			$ID = $result[0]['ID'];
			$SORT = $result[0]['SORT'];
			
			$id = $result[1]['ID'];
			$sort = $result[1]['SORT'];

			$this->db->trans_start();
			$this->sql_add_data(array('sort' => $SORT))->sql_save(self::PR_TYPES, $id);
			$this->sql_add_data(array('sort' => $sort))->sql_save(self::PR_TYPES, $ID);
			$this->db->trans_complete();
			if($this->db->trans_status()) 
			{
				return TRUE; 
			}
			return FALSE;
		}
		return FALSE;
	}
}
?>