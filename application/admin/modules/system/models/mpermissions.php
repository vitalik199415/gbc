<?php

class Mpermissions extends AG_Model {

    public $permissions = array(
        0 => array(
            'discounts' => 1,
            'products'  => 1
        ),
        1 => array(
            'discount_coupons' => 1
        )
    );
    
    public $menu = array(
        0 => array(
            1 => array('Меню|Модули', -1, '#'),
            2 => array('Каталог продукции', -1, '#'),
            3 => array('Продажи', -1, '#'),
            4 => array('Покупатели', -1, '#'),
            5 => array('Настройка сайта', -1, '#'),
            6 => array('Склад', -1, '#'),
            7 => array('Выход', -1, 'login/logout')
        )
    );


}

/* End of file mpermissions.php */