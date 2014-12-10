<?php

/**
 * Discount_coupons
 * 
 * @package gbcportal
 * @author Vitalik
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class Discount_coupons extends AG_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->template->add_title('Каталог продукции - ')->add_title('Купоны на скидку - ');
        $this->template->add_navigation('Каталог продукции')->add_navigation('Купоны на скидку',
            set_url('*/*/'));
    }

    public function index()
    {
        $this->load->model('mdiscount_coupons');
        $this->mdiscount_coupons->render_discount_coupons_grid();
    }

    public function add()
    {
        $this->template->add_js('jquery.gbc_show_product',
            'modules_js/catalogue/products');
        $this->load->model('mdiscount_coupons');
        $this->template->add_navigation('Добавление купона');
        $this->mdiscount_coupons->add();
    }

    public function edit()
    {
        $this->template->add_js('jquery.gbc_show_product',
            'modules_js/catalogue/products');
        $this->template->add_title('Редактирование');
        $this->template->add_navigation('Редактирование купона');

        $this->load->model('mdiscount_coupons');
        $URI = $this->uri->uri_to_assoc(4);

        if (isset($URI['id']) && ($ID = intval($URI['id'])) > 0) {
            if (!$this->mdiscount_coupons->edit($ID)) {
                $this->messages->add_error_message('Возникли проблемы при редактировании купона!');
                $this->_redirect(set_url('*/*'));
            }
        } else {
            $this->messages->add_error_message('Параметр ID не установлено, редактирование невозможно!');
            $this->_redirect(set_url('*/*'));
        }
    }

    public function save()
    {
        if (isset($_POST)) {
            $this->load->model('mdiscount_coupons');
            $URI = $this->uri->uri_to_assoc(4);
            if (isset($URI['id']) && ($ID = intval($URI['id'])) > 0) {
                if ($this->mdiscount_coupons->save($ID)) {
                    $this->messages->add_success_message('Купон успешно отредактировано!');
                    $this->_redirect(set_url('*/*'));
                } else {
                    $this->messages->add_error_message('Ошибка при редактировании купона!');
                    $this->_redirect(set_url('*/*'));
                }
                if (isset($_GET['return'])) {
                    //$this->messages->add_success_message('Купон успешно отредактированo!');
                    $this->_redirect(set_url('*/*/edit/id/' . $ID));
                }
            } else {
                if ($ID = $this->mdiscount_coupons->save()) {
                    $this->messages->add_success_message('Купон успешно добавлено');
                    $this->_redirect(set_url('*/*'));

                    if (isset($_GET['return'])) {
                        $this->_redirect(set_url('*/*/edit/id/' . $ID));
                    }
                } else {
                    $this->messages->add_error_message('Ошибка при добавлении купона!');
                    $this->_redirect(set_url('*/*'));
                }
            }
        } else {
            $this->_redirect(set_url('*/*'));
        }
    }

    public function activate()
    {
        $URI = $this->uri->uri_to_assoc(4);
        $ID = $URI['id'];

        $this->load->model('mdiscount_coupons');
        if ($this->mdiscount_coupons->activate($ID)) {
            $this->messages->add_success_message('Купон успешно активирован! Редактирование купона больше не возможно!');
            $this->_redirect(set_url('*/*'));
        }
    }

    public function get_ajax_customers_sort()
    {
        $this->load->model('mdiscount_coupons');
        echo $this->mdiscount_coupons->get_customers();
    }

    public function get_ajax_products_sort()
    {
        $this->load->model('mdiscount_coupons');
        echo $this->mdiscount_coupons->get_products();
    }
}

?>