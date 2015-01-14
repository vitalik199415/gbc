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
            1   => array('Меню|Модули', '#', 4),
            2   => array('Каталог продукции', '#', 4),
            3   => array('Продажи', '#', 4),
            4   => array('Покупатели', '#', 4),
            5   => array('Настройка сайта', '#', 4),
            6   => array('Склад', '#', 4),
            7   => array('Выход', 'login/logout', 4)
        ),
        1 => array(
            8   => array('Настройки главной', 'home', 4),
            9   => array('Меню сайта', 'menu', 4),
            10  => array('Модули сайта', 'site_modules', 4)
        ),
        2 => array(
            11  => array('Категории каталога', '#', 4),
            12  => array('Продукты каталога', '#', 4),
            13  => array('Свойства продуктов', '#', 4),
            14  => array('Атрибуты продукции', '#', 4),
            15  => array('Скидки на покупку', '#', 4)
        ),
        3 => array(
            16  => array('Заказы', 'sales/orders', 4),
            17  => array('Инвойсы', 'sales/invoices', 4),
            18  => array('Отправки', 'sales/shippings', 4),
            19  => array('Возвраты', 'sales/credit_memo', 4),
            20  => array('Методы оплаты', 'sales/payment_methods', 4),
            21  => array('Методы доставки', 'sales/shipping_methods', 4),
            22  => array('Настройки', 'sales/sales_settings', 4)
        ),
        4 => array(
            23  => array('Покупатели', 'customers', 4),
            24  => array('Группы покупателей', 'customers/customers_types', 4),
            25  => array('Настройки', 'customers/customers_settings', 4)
        ),
        5 => array(
            26  => array('Настройки сайта', 'site_settings', 4),
            27  => array('Настройки языков', 'langs', 4),
            28  => array('Дополнительные блоки', '#', 4)
        ),
        6 => array(
            29  => array('Склады', 'warehouse/warehouses', 4),
            30  => array('Продукты', 'warehouse/warehouses_products', 4),
            31  => array('Продажи', '#', 4),
            32  => array('Переносы', 'warehouse/warehouses_transfers', 4),
            33  => array('Логи, отчеты', 'warehouse/warehouses_logs', 4),
            34  => array('Настройки склада', 'warehouse/wh_settings', 4)
        ),
        11 => array(
            35  => array('Категории каталога', 'catalogue/categories', 4),
            36  => array('Продукты в категории', 'catalogue/categories_products', 4)
        ),
        12 => array(
            37  => array('Продукты каталога', 'catalogue/products', 4),
            38  => array('Продукты дополнительно', 'catalogue/products/additionally_grid', 4),
            39  => array('Сопутствующие продукты', 'catalogue/products_related', 4),
            40  => array('Похожие продукты', 'catalogue/products_similar', 4),
            41  => array('Отзывы к продуктам', 'catalogue/products_comments', 4),
            42  => array('Настройки продуктов', 'catalogue/products_settings', 4),
            43  => array('Настройки валют', 'catalogue/currency', 4)
        ),
        13 => array(
            44  => array('Свойства продуктов', 'catalogue/products_properties', 4),
            45  => array('Группы свойств продуктов', 'catalogue/products_types', 4)
        ),
        14 => array(
            46  => array('Атрибуты', 'catalogue/products_attributes', 4),
            47  => array('Опции атрибутов','catalogue/products_attributes_options',4)
        ),
        15 => array(
            48  => array('Скидки на покупку', 'catalogue/discounts', 4),
            49  => array('Купоны на скидку', 'catalogue/discount_coupons', 4)
        ),
        28 => array(
            50  => array('Дополнительное header', 'block_additionally/block_additionally_header', 4),
            51  => array('Счетчики footer', 'block_additionally/block_additionally_footer', 4)
        ),
        31 => array(
            52  => array('Продажи', 'warehouse/warehouses_sales', 4),
            53  => array('Инвойсы', 'warehouse/warehouses_invoices', 4),
            54  => array('Отправки', 'warehouse/warehouses_shippings', 4),
            55  => array('Возвраты', 'warehouse/warehouses_credit_memo', 4)
        )
    );

    
    function build_menu($menu_arr, $parrent_id = 0) {
        if(is_array($menu_arr) and count(@$menu_arr[$parrent_id]) > 0) {
            $tree = '<ul>';
            foreach($menu_arr[$parrent_id] as $key => $vall) {
                $tree .= '<li><a href="'.set_url($vall[1]).'"><p>'.$vall[0].'</p></a>';
                $tree .= $this->build_menu($menu_arr, $key);
                $tree .= '</li>';
            }
            $tree .= '</ul>';
        }
        else return null;
        
        return $tree;
    }
    
    public function get_menu() {
        $final_menu = '<div class="menu">'; 
        $final_menu .= $this->build_menu($this->menu);
        $final_menu .= '</div>';
        $final_menu .= '<script>
                            $("div.menu > ul").attr("id", "gbc_dropdown_menu");
                            $("#gbc_dropdown_menu").gbc_dropdown_menu();
                        </script>';
        return $final_menu;
    }

}

/* End of file mpermissions.php */