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

    public function show(){

        if(isset($this->permissions[0]['discounts'])) echo "Vivat!";

    }


}

/* End of file mpermissions.php */