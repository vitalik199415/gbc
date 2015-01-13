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
        0 => array()
    );


}

/* End of file mpermissions.php */