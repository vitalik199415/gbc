<?php
class Catalogue_mass_edit_price extends AG_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->template->add_title('Каталог - Массовые скидки');
		$this->template->add_navigation('Каталог')->add_navigation('Массовые скидки',set_url('*/*'));
	}
	
	public function index()
	{
		$this->load->model('catalogue/mcatalogue_mass_edit_price');
		$this->mcatalogue_mass_edit_price->render_categories_grid();
	}
	
	public function actions()
	{
		$URI = $this->uri->uri_to_assoc(4);
		if(isset($URI['cat_id']) && ($cat_id = intval($URI['cat_id']))>0)
		{
			$this->template->add_js('jquery.gbc_products_grid', 'modules_js/catalogue');
			$this->template->add_js('highslide.min', 'highslide');
			$this->template->add_css('highslide', 'highslide');
			$this->template->add_js('highslide.def_gallery.config.ru', 'highslide');
			$this->load->model('catalogue/mcatalogue_mass_edit_price');
			if(!$this->mcatalogue_mass_edit_price->render_actions($cat_id))
			{
				$this->messages->add_error_message('Категории с указанным ID не существует!');
				$this->_redirect(set_url('*/*'));
			}
		}
		else
		{
			$this->messages->add_error_message('Параметр отсутствует!');
			$this->_redirect(set_url('*/*'));
		}
	}
	
	public function get_ajax_categories_products()
	{
		$URI = $this->uri->uri_to_assoc(4);
		if(isset($URI['cat_id']) && ($cat_id = intval($URI['cat_id']))>0)
		{
			$this->load->model('catalogue/mcatalogue_mass_edit_price');
			//return $this->mcatalogue_mass_sale->get_categories_products_grid($cat_id);
			//echo var_dump($this->mcatalogue_mass_sale->get_categories_products_grid($cat_id));
			echo $this->mcatalogue_mass_edit_price->get_categories_products_grid($cat_id);
		}
	}
	
	public function save_changes()
	{
		$URI = $this->uri->uri_to_assoc(4);
		if(isset($URI['cat_id']) && ($cat_id = intval($URI['cat_id']))>0)
		{
			$this->load->model('catalogue/mcatalogue_mass_edit_price');
			if($this->mcatalogue_mass_edit_price->save_changes($cat_id))
			{
				$this->messages->add_success_message('Действие выполнено успешно!');
				$this->_redirect(set_url('*/*/actions/cat_id/'.$cat_id));
			}
			else
			{
				$this->messages->add_error_message('Возникли ошибки при выполнение действия!');
				$this->_redirect(set_url('*/*/actions/cat_id/'.$cat_id));
			}
		}
		else
		{
			$this->messages->add_error_message('Параметр отсутствует!');
			$this->_redirect(set_url('*/*'));
		}
	}
}
?>