<?php
class Products_types extends AG_Controller
{
	function __construct()
	{
		parent:: __construct();
		$this->template->add_title('Каталог продукции - ')->add_title('Группы свойств продуткции - ');
		$this->template->add_navigation('Каталог продукции')->add_navigation('Группы свойств продукции',site_url('/*/products_types'));
	}
	
	public function index()
	{
		$this->load->model('catalogue/mproducts_types');
		if ($select = $this->input->post('products_types_grid_select'))
		{
			if ($checkbox = $this->input->post('products_types_grid_checkbox'))
			{
				$data_ID = array();
				foreach($checkbox as $ms)
				{
					$data_ID[] = $ms;
				}
				switch($select)
				{
					case "delete":
						$this->mproducts_types->delete($data_ID);
						$this->messages->add_success_message('Удаление выбраных позиций прошло успешно');
					break;
					case "on":
						$this->mproducts_types->activate($data_ID);
						$this->messages->add_success_message('Активация выбраных позиций прошла успешно');
					break;
					case "off":
						$this->mproducts_types->activate($data_ID,0);
						$this->messages->add_success_message('Деактивация выбраных позиций прошла успешно');
					break;
				}
			}
		}
		$this->mproducts_types->render_types_grid();
	}	
	
	public function add()
	{
		$this->template->add_title('Добавление группы свойств');
		$this->template->add_navigation('Добавление группы свойств');

		$this->load->model('catalogue/mproducts_types');
		$this->mproducts_types->add();
	}
	
	public function edit()
	{
		$this->template->add_title('Редактирование группы свойств');
		$this->template->add_navigation('Редактирование группы свойств');
		
		$this->load->model('catalogue/mproducts_types');
		
		$URI = $this->uri->uri_to_assoc(4);
		if(isset($URI['id']) && ($ID = intval($URI['id']))>0)
		{
			if(!$this->mproducts_types->edit($ID))
			{
				$this->messages->add_error_message('Возникли ошибки генерации редактирования группы свойств!');
				$this->_redirect(set_url('*/*/'));
			}
		}
		else
		{
			$this->messages->add_error_message('Параметр ID отсутствует! Редактирование невозможно!');
			$this->_redirect(set_url('*/*/'));
		}
	}
	
	public function save()
	{
		if($this->input->post('main'))
		{
			$this->load->model('catalogue/mproducts_types');
			$URI = $this->uri->uri_to_assoc(4);
			if(isset($URI['id']) && ($ID = intval($URI['id']))>0)
			{
				if($this->mproducts_types->save($ID))
				{
					$this->messages->add_success_message('Группа свойств продукции успешно отредактирован.');
					$this->_redirect(set_url('*/*/')); 
				}
				else
				{
					$this->messages->add_error_message('Возникли ошибки при редактировании группы свойств!');
					$this->_redirect(set_url('*/*/edit/id/'.$ID));
				}
				if(isset($_GET['return'])) 
				{
					$this->_redirect(set_url('*/*/edit/id/'.$ID));
				}
			}
			else 
			{
				if($ID = $this->mproducts_types->save()) 
				{
					$this->messages->add_success_message('Группа свойств продукции успешно добавлен.');
					$this->_redirect(set_url('*/*/')); 
					if(isset($_GET['return']))
					{
						$this->_redirect(set_url('*/*/edit/id/'.$ID)); 
					}
				}
				else
				{
					$this->messages->add_error_message('Возникли ошибки при добавлении группы свойств!');
					$this->_redirect(set_url('*/*/add'));
				}
			}
		}
		else
		{
			$this->_redirect(set_url('*/*/'));		
		}
	}
	public function delete()
	{
		$URI = $this->uri->uri_to_assoc(4);
		if(isset($URI['id']) && ($id = intval($URI['id']))>0)
		{
			$this->load->model('catalogue/mproducts_types');
			if($this->mproducts_types->delete($id))
			{
				$this->messages->add_success_message('Группа свойств продукции успешно удален!');
				$this->_redirect(set_url('*/*/'));
			}
			else
			{
				$this->messages->add_error_message('Группа свойств продукции с ID = '.$id.' не существует, или произошла ошибка при удалении!');
				$this->_redirect(set_url('*/*/'));
			}
		}
		else
		{
			$this->messages->add_error_message('Параметр ID отсутствует! Процес удаления не возможен!');
			$this->_redirect(set_url('*/*/'));
		}
	}
	
	public function change_position()
	{
		$URI = $this->uri->uri_to_assoc(4);
		if(isset($URI['id']) && ($id = intval($URI['id'])) > 0 && ($URI['type'] == 'up' || $URI['type'] == 'down'))
		{
			$this->load->model('catalogue/mproducts_types');
			if($this->mproducts_types->change_position($id, $URI['type']))
			{
				$this->messages->add_success_message('Смена позиции прошла успешно!');
				$this->index();
			}
			else
			{
				$this->messages->add_error_message('Смена позиции не возможна!');
				$this->index();
			}
		}
		else
		{
			$this->index();
		}
	}
	
	public function check_alias()
	{
		if($alias = $this->input->post('main'))
		{
			if(isset($alias['alias']))
			{
				$alias = $alias['alias'];
				$this->load->model('catalogue/mproducts_types');
				
				$URI = $this->uri->uri_to_assoc(4);
				if(isset($URI['id']) && ($id = intval($URI['id']))>0)
				{
					$this->mproducts_types->id_type = $id;
				}
				if($this->mproducts_types->check_isset_alias($alias))
				{
					echo json_encode(true);
				}
				else
				{
					echo json_encode(false);
				}
			}	
		}
	}
}