<?php
class Mm_catalogue extends AG_Model
{
	const MID = 3;
	
	const CAT 			= 'm_catalogue';
	const ID_CAT 		= 'id_m_catalogue';
	const CAT_DESC		= 'm_catalogue_description';
	
	const CP 			= 'm_catalogue_photos';
	const ID_CP 		= 'id_m_catalogue_photos';
	const CP_DESC 		= 'm_catalogue_photos_description';
	const ID_CP_DESC 	= 'id_m_catalogue_photos_description';
	
	private $segment = FALSE;
	const IMG_FOLDER = '/m_catalogue_album/';
	private $img_path = FALSE;
	
	private $tree_array = array();
	
	public $id_categorie = FALSE;
	
	function __construct()
	{
		parent::__construct();
		$this->segment = $this->uri->segment(self::MID);
		$this->img_path = IMG_PATH.$this->id_users.'/media/module_'.$this->segment.self::IMG_FOLDER;
	}
	
	public function getCollectionToHtml()
	{
		$this->load->library("grid");
		$this->grid->_init_grid("m_catalogue_grid", array(), FALSE);
		
		$this->grid->db	->select("A.`".self :: ID_CAT."` AS ID, A.`price`, A.`active`, B.`name`, A.`create_date`, A.`update_date`")
						->from("`".self :: CAT."` AS A")
						->join("`".self :: CAT_DESC."` AS B","B.`".self :: ID_CAT."` = A.`".self :: ID_CAT."` && B.`".self::ID_LANGS."` = '1'","left")
						->where("A.`id_users_modules`", $this->segment)->where("A.`id_users`", $this->id_users);
		
		$this->load->helper('m_catalogue/m_catalogue_helper');
		helper_m_catalogue_grid_build($this->grid);
		$this->grid->create_grid_data();
		$this->grid->update_grid_data('active',array('0'=>'Нет','1'=>'Да'));
		$this->grid->render_grid();				
	}
	public function add()
	{
		$this->load->helper('m_catalogue/m_catalogue_helper');
		$this->load->model('langs/mlangs');
		$data['on_langs'] = $this->mlangs->get_active_languages();
		
		helper_m_catalogue_form_build($data);
	}
	private function getEditQuery($id, $id_langs = FALSE)
	{
		if($id_langs)
		{
			$select = "B.`".self::ID_LANGS."`, B.id_m_catalogue_description AS DID";
		}
		else
		{
			$select = "A.`".self :: ID_CAT."` AS ID, A.`active` , A.`price`, A.`url`, B.`name`, B.`short_description`, B.`full_description`, B.`".self::ID_LANGS."`, B.`id_m_catalogue_description`";
		}
		$result = $this->db	->select($select)
							->from("`".self :: CAT."` AS A")
							->join("`".self :: CAT_DESC."` AS B","B.`".self :: ID_CAT."` = '".$id."'","left")
							->where("A.`".self :: ID_CAT."`",$id)->where("A.`id_users`", $this->id_users);
		return $result;					
	}
	public function save($id = FALSE)
	{
		if($id)
		{
			if(isset($_POST['main']['active']))
			{
				if($_POST['main']['active'] == '') unset($_POST['main']['active']);
				$database_data = $this->getEditQuery($id, TRUE);
				$database_data = $database_data->get()->result_array();
				if(count($database_data)>0)
				{
					foreach($database_data as $ms)
					{
						$database_data[$ms['id_langs']] = $ms;
					}
					$this->db->trans_start();
					$result = $this->sql_add_data($_POST['main'])->sql_update_date()->sql_using_user()->sql_save(self :: CAT, $id);
					if($result && isset($_POST['langs']))
					{
						$POST = $_POST['langs'];
						$this->load->model('langs/mlangs');
						$langs = $this->mlangs->get_active_languages();
						foreach($langs as $key => $ms)
						{
							if(isset($POST[$key]))
							{
								if(isset($POST[$key]['id_m_catalogue_description']))
								{
									if(!isset($database_data[$key]))
									{
										$database_data[$key]['DID'] = 0;
									}
									$DID = intval($POST[$key]['id_m_catalogue_description']);
									if($DID > 0 && $DID == $database_data[$key]['DID'])
									{
										$data = $POST[$key];
										$this->sql_add_data($data)->sql_save(self :: CAT_DESC, $DID);
									}
									else
									{
										$data = $POST[$key] + array('id_langs' => $key) + array(self :: ID_CAT => $id);
										$this->sql_add_data($data)->sql_save(self :: CAT_DESC);
									}
								}
							}
						}
						$this->db->trans_complete();
						if($this->db->trans_status()) 
						{
							return TRUE;
						}
						return FALSE;
					}
				}
				return FALSE;
			}
		}
		else
		{
			if(isset($_POST['main']['active']))
			{
				if($_POST['main']['active'] == '') unset($_POST['main']['active']);
				$this->db->trans_start();
				$ID = $this->sql_add_data($_POST['main']+array('id_users_modules' => $this->segment))->sql_update_date()->sql_using_user()->sql_save(self :: CAT);
				if($ID && $ID > 0 && isset($_POST['langs']))
				{
					$POST = $_POST['langs'];
					$this->load->model('langs/mlangs');
					$langs = $this->mlangs->get_active_languages();
					foreach($langs as $key => $ms)
					{
						if(isset($POST[$key]))
						{
							$data = $POST[$key] + array('id_langs' => $key) + array(self :: ID_CAT => $ID);
							$this->sql_add_data($data)->sql_save(self :: CAT_DESC);
						}
					}
					$this->db->trans_complete();
					if($this->db->trans_status())
					{
						return $ID;
					}
					return false;
				}
			}	
		}
	}
	public function edit($id)
	{
		$result = $this->getEditQuery($id);
		$result = $result->get()->result_array();
		$data = array();
		if(count($result) > 0)
		{
			foreach($result as $ms)
			{
				$data['base']['main']['price'] 	= $ms['price'];
				$data['base']['main']['url'] 	= $ms['url'];
				$data['base']['main']['active'] = $ms['active'];
				$data['desc']['langs'][$ms['id_langs']] = $ms;
				unset($data['desc']['langs'][$ms['id_langs']]['price']);
				unset($data['desc']['langs'][$ms['id_langs']]['ID']);
				unset($data['desc']['langs'][$ms['id_langs']]['active']);
			}
			$this->load->model('langs/mlangs');
			$data['on_langs'] = $this->mlangs->get_active_languages();
			
			$this->load->helper('m_catalogue/m_catalogue_helper');
			
			helper_m_catalogue_form_build($data, '/id/'.$id);
			return TRUE;
		}
		return FALSE;
	}
	public function delete($id)
	{
		$this->load->helper('agfiles_helper');
		if(is_array($id))
		{
			foreach($id as $ms)
			{
				$path = BASE_PATH.'users/'.$this->id_users.'/media/module_'.$this->segment.self::IMG_FOLDER.$ms;
				remove_dir($path);
			}
			$this->db->where_in(self::ID_CAT, $id)->where(self::ID_USERS, $this->id_users)->delete("`".self::CAT."`");
			return TRUE;
		}
		
		$path = BASE_PATH.'users/'.$this->id_users.'/media/module_'.$this->segment.self::IMG_FOLDER.$id;
		remove_dir($path);
			
		$this->db->where(self :: ID_CAT, $id)->where(self::ID_USERS, $this->id_users);
		if($this->db->delete(self::CAT))
		{
			return TRUE;
		}
		return FALSE;
	}
	
	public function check_isset_m_catalogue($id)
	{
		$query = $this->db	->select("COUNT(*) AS COUNT")
							->from("`".self::CAT."`")
							->where("`".self::ID_CAT."`", $id)
							->where("`".self::ID_USERS."`", $this->id_users);
		$result = $query->get()->row_array();
		if($result['COUNT'] == 1)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	public function activate($id, $active = 1)
	{
		if(is_array($id))
		{
			$data = array('active' => $active);
			foreach($id as $ms)
			{
				$this->sql_add_data($data)->sql_save(self :: CAT, $ms);
			}
			return TRUE;			
		}
		return FALSE;
	}
	public function edit_photo($id)
	{
		$this->load->helper('m_catalogue/m_catalogue_helper');
		$this->load->model('langs/mlangs');
		$data['on_langs'] = $this->mlangs->get_active_languages();
		$data['id_users'] = $this->id_users;
		$query = $this->db	->select("A.*, A.`sort` AS SORT, B.`name`, B.`title`, B.`alt`, B.`".self::ID_LANGS."`, B.`".self::ID_CP_DESC."`")
							->from("`".self::CP."` AS A")
							->join("`".self::CP_DESC."` AS B", "B.`".self::ID_CP."` = A.`".self::ID_CP."`",	"left")
							->where("A.`".self::ID_CAT."`", $id)
							->order_by("SORT");

		$result = $query->get()->result_array();
		foreach($result as $ms)
		{
			$data['image'][$ms[self::ID_CP]] = array(self::ID_CP => $ms[self::ID_CP], 'image' => $this->img_path.$ms[self::ID_CAT].'/thumb_'.$ms['image']);
			$data['img_desc'][$ms[self::ID_CP]][$ms['id_langs']] = array(self::ID_CP_DESC => $ms[self::ID_CP_DESC], 'name' => $ms['name'], 'title' => $ms['title'], 'alt' => $ms['alt']);
		}
		
		helper_m_catalogue_photo_form($id, $data, $save_param = '/id/'.$id);
	}
	
	public function save_photo_desc($id)
	{
		$this->db->trans_start();
		if(isset($_POST['img_desc']))
		{
			$IPOST = $_POST['img_desc'];
			$this->load->model('langs/mlangs');
			$langs = $this->mlangs->get_active_languages();
			
			$query = $this->db	->select("A.`".self::ID_CP."` AS ID , B.`".self::ID_CP_DESC."`, B.`".self::ID_LANGS."`")
								->from("`".self::CP."` AS A")
								->join(	"`".self::CP_DESC."` AS B",	"B.`".self::ID_CP."` = A.`".self::ID_CP."`", "left")
								->where("`".self::ID_CAT."`", $id);
					
			$result = $query->get()->result_array();
			if(count($result)>0)
			{
				$images = array();
				foreach($result as $ms)
				{
					$images[$ms['ID']][$ms['id_langs']] = array('ID' => $ms['ID'], self::ID_CP_DESC => $ms[self::ID_CP_DESC], 'id_langs' => $ms['id_langs']);
				}
				
				foreach($IPOST as $ikey => $ims)
				{	
					if(isset($images[$ikey]))
					{
						$POST = $ims;
						foreach($langs as $key => $ms)
						{
							if(isset($POST[$key]))
							{	
								if(isset($POST[$key][self::ID_CP_DESC]) && intval($POST[$key][self::ID_CP_DESC])>0 && isset($images[$ikey][$key][self::ID_CP_DESC]) && $images[$ikey][$key][self::ID_CP_DESC] == $POST[$key][self::ID_CP_DESC])
								{
									$data = $POST[$key];
									$this->sql_add_data($data)->sql_save(self::CP_DESC, $POST[$key][self::ID_CP_DESC]);
								}
								else if(!isset($images[$ikey][$key][self::ID_CP_DESC]))
								{
									$data = $POST[$key] + array('id_langs' => $key) + array(self::ID_CP => $ikey);
									$this->sql_add_data($data)->sql_save(self::CP_DESC);
								}
							}
						}
					}
				}
				$this->db->trans_complete();
				if($this->db->trans_status()) 
				{
					return TRUE; 
				}
			}
		}	
		return FALSE;
	}
	
	public function save_photo($id, $data)
	{
		$POST = array(self::ID_CAT => $id, 'image' => $data['file_name']);
		
		$this->db->trans_start();
		$ID = $this->sql_add_data($POST)->sql_save(self::CP);
		if($ID)
		{
			$this->sql_add_data(array('sort' => $ID))->sql_save(self::CP, $ID);
		}
		$this->db->trans_complete();
		if($this->db->trans_status()) 
		{
			return $ID; 
		}
		return FALSE;
	}
	
	private function get_upload_config($ID)
	{
		$dir = BASE_PATH.'users/'.$this->id_users.'/media/module_'.$this->segment.'/m_catalogue_album/'.$ID;
		if(!is_dir($dir))
		{
			$this->load->helper('agfiles_helper');
			create_dir($dir, 2);
		}
		$config['upload_path'] = $dir.'/';
		$config['allowed_types'] = 'jpg|jpeg';
		$config['max_size']	= '4092';
		$config['encrypt_name'] = TRUE;
		
		return $config;
	}
	
	public function upload_photo($ID)
	{
		$config = $this->get_upload_config($ID);
		$this->load->library('upload', $config);
		if($this->upload->do_upload('Filedata'))
		{
			$file_data = $this->upload->data();
			$this->crop_img($config['upload_path'].$file_data['file_name']);
			
			if($img_id = $this->save_photo($ID, $file_data))
			{
				$this->load->helper('m_catalogue/m_catalogue_helper');
				$this->load->model('langs/mlangs');
				$data['on_langs'] = $this->mlangs->get_active_languages();
				
				$data['PID'] = $ID;
				$data['form_id'] = 'm_catalogue_form';
				$data['id_users'] = $this->id_users;
				$data['id'] = $img_id;
				$data['image'] = $this->img_path.$ID.'/thumb_'.$file_data['file_name'];
				$data['values'] = FALSE;
				$data['ajax'] = TRUE;
				
				echo json_encode(array('id' => $img_id, 'html' => helper_m_catalogue_photo_desc_form($data)));
				return TRUE;
			}
			return FALSE;
		}
		else
		{
			echo $this->upload->display_errors();
			return FALSE;
		}
	}
	
	public function crop_img($img)
	{
		$this->load->model('mm_catalogue_settings');
		$config = $this->mm_catalogue_settings->get_m_catalogue_settings(TRUE);
		
		$Lconfig['source_image'] = $img;
		$Lconfig['width'] = $config['img_width_thumbs'];
		$Lconfig['height'] = $config['img_height_thumbs'];
		$Lconfig['create_thumb'] = TRUE;
		$Lconfig['quality'] = $config['img_quality'];
		
		$Bconfig['source_image'] = $img;
		$Bconfig['width'] = $config['img_width'];
		$Bconfig['height'] = $config['img_height'];
		$Bconfig['create_thumb'] = FALSE;
		$Bconfig['quality'] = $config['img_quality'];
		
		$this->load->library('image_lib', $Lconfig);
		$this->image_lib->resize();
		$this->image_lib->clear();
		$this->image_lib->initialize($Bconfig);
		$this->image_lib->resize();
		$this->image_lib->clear();
		if($config['img_wm'])
		{
			$Cconfig['quality'] = 			$config['img_quality'];
			$Cconfig['source_image'] = 		$img;
			$Cconfig['wm_vrt_alignment'] = 	$config['img_wm_valign'];
			$Cconfig['wm_hor_alignment'] = 	$config['img_wm_align'];
			$Cconfig['wm_opacity'] = 		$config['img_wm_opacity'];
			$Cconfig['wm_text'] = 			$config['img_wm_text'];
			$Cconfig['wm_font_size'] = 		$config['img_wm_text_size'];
			$Cconfig['wm_font_color'] = 	$config['img_wm_text_color'];
			$Cconfig['wm_shadow_color'] = 	$config['img_wm_text_shadow_color'];
			$Cconfig['wm_shadow_distance'] = $config['img_wm_text_shadow_padding'];
			$Cconfig['wm_font_path'] = 		BASE_PATH.'fonts/TIMCYRB.TTF';
			
			$Cconfig['wm_padding'] = 		0;
			$Cconfig['wm_hor_offset'] = 	1;
			$Cconfig['wm_vrt_offset'] = 	1;
			
			$this->image_lib->initialize($Cconfig);
			$this->image_lib->watermark();
			$this->image_lib->clear();
		}
	}
	
	public function change_position_photo($id, $type)
	{
		if($type == 'up' || $type == 'down')
		{
			switch($type)
			{
				case "up":
					if($c_id = $this->change_photo_position_query('<=', $id))
					{
						return TRUE;
					}
					return FALSE;
				break;
				case "down":
					if($c_id = $this->change_photo_position_query('>=', $id))
					{
						return TRUE;
					}
					return FALSE;
				break;
			}
		}
		return true;
	}
	
	private function change_photo_position_query($type, $id)
	{
		$OB = '';
		if($type == '<=')
		{
			$OB = 'DESC';
		}
		$query = $this->db	->select("DISTINCT(A.`".self::ID_CP."`) AS ID, A.`sort` AS SORT, A.`".self::ID_CAT."` AS PARENT")
							->from("`".self::CP."` AS A")
							/*->join(	"`".self::NEWS."` AS B",
									"A.`".self::ID_NEWS."` = B.`".self::ID_NEWS."` && A.`sort` ".$type." (SELECT `sort` FROM `".self::NP."` WHERE `".self::ID_NP."` = ".$id." LIMIT 1)"
							)*/
							->where("A.`".self::ID_CAT."` = (SELECT `".self::ID_CAT."` FROM `".self::CP."` WHERE `".self::ID_CP."` = '".$id."' LIMIT 1)", NULL, FALSE)
							->where("A.`sort` ".$type." (SELECT `sort` FROM `".self::CP."` WHERE `".self::ID_CP."` = '".$id."' LIMIT 1)", NULL, FALSE)
							->order_by('sort',$OB)->limit(2);
		$query = $query->get();
		
		if($query->num_rows() == 2)
		{
			$result = $query->result_array();
			if($result[0]['PARENT'] == $result[1]['PARENT'])
			{
				$ID = $result[0]['ID'];
				$SORT = $result[0]['SORT'];
				
				$id = $result[1]['ID'];
				$sort = $result[1]['SORT'];

				$this->db->trans_start();
				$this->sql_add_data(array('sort' => $SORT))->sql_save(self::CP, $id);
				$this->sql_add_data(array('sort' => $sort))->sql_save(self::CP, $ID);
				$this->db->trans_complete();
				if($this->db->trans_status()) 
				{
					return TRUE; 
				}
				return FALSE;
			}
			return FALSE;
		}
		return FALSE;
	}
	
	public function delete_photo($id)
	{
		$query = $this->db->select("A.*")
				->from("`".self::CP."` AS A")
				->where("A.`".self::ID_CP."`", $id);
		$query = $query->get();
		if($query->num_rows()==1)
		{
			$result = $query->row_array();
			$file = $result['image'];
			$config = $this->get_upload_config($result[self::ID_CAT]);
			
			@unlink($config['upload_path'].$file);
			@unlink($config['upload_path'].'thumb_'.$file);
			$this->db->where(self::ID_CP, $id);
			$this->db->delete(self::CP);
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	public function check_isset_url($url)
	{
		$url = trim($url);
		if($url == '') return TRUE;
		$query = $this->db->select("COUNT(*) AS COUNT")->from("`".self::CAT."`")->where("`".self::ID_USERS."`", $this->id_users)->where("`url`", $url)->limit(1);
		if($this->id_categorie)
		{
			$query->where("`".self::ID_CAT."` <>", $this->id_categorie);
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
		$this->form_validation->set_rules('main[url]','Сегмент URL','trim|check_isset_ulr');
		$this->form_validation->set_message('check_isset_url', 'Объект ADA с указанным сегментом URL уже существует!');
	}
}
?>