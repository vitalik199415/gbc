<?php

class Mpermissions_modules extends AG_Model {
    
    const   P_MODULES           = 'm_permissions_modules';
    const   ID_P_MODULES        = 'id_m_permissions_modules';
    const   P_MODULES_DESC      = 'm_permissions_modules_description';
    const   ID_P_MODULES_DESC   = 'id_m_permissions_modules_description';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function render_permissions_modules_grid() {
        $this->load->library('grid');
        $this->grid->_init_grid('permissions_modules_grid', array());

        $this->grid->db
                ->select("PM.`".self::ID_P_MODULES."` AS ID, PM.`module` AS aliace, PMD.`name`, PMD.`description`")
                ->from("`".self::P_MODULES."` as PM")
                ->join("`".self::P_MODULES_DESC."` as PMD", "PM.`".self::ID_P_MODULES."`=PMD.`".self::ID_P_MODULES."`", "INNER")
                ->where("PMD.`id_langs`", 1)->order_by('PM.`sort`', 'asc');

        $this->load->helper('permissions_modules');
        helper_permissions_modules_grid_build($this->grid);
        $this->grid->create_grid_data();
//        $this->grid->update_grid_data('discount_type', array('0' => 'Сумма', '1' => 'Процент'));
//        $this->grid->update_grid_data('consider_promotional_items', array('0' => 'Нет', '1' => 'Да'));
//        $this->grid->update_grid_data('is_start', array('0' => 'Нет', '1' => 'Да'));
        $this->grid->render_grid();
    }
    
    public function add() {
        $this->db
             ->select("`id_langs`, `name`")
             ->from("`langs`");
        $langs = $this->db->get()->result_array();
        
        $data['langs'] = array();
        foreach($langs as $lang) {
            $data['langs'][$lang['id_langs']] = $lang['name'];
        }
        
        $this->load->helper('permissions_modules');
        helper_permissions_modules_form_build($data);
    }
    
    public function edit($id){
        $main = $this->db->select('*')
                           ->from('`'.self::P_MODULES.'` AS A')
                           ->where('A.`'.self::ID_P_MODULES.'`',$id)->limit(1)
                           ->get()->row_array();

        if(count($main)>0)
        {
            $data = array();

            $data['main'] = $main;                                                          // основная информация о купоне

            
            $this->db
                 ->select("`id_langs`, `name`")
                 ->from("`langs`");
            $langs = $this->db->get()->result_array();

            $data['langs'] = array();
            foreach($langs as $lang) {
                $data['langs'][$lang['id_langs']] = $lang['name'];
            }
            
            $description = $this->db->select('*')
                              ->from('`'.self::P_MODULES_DESC.'`')
                              ->where('`'.self::ID_P_MODULES.'`', $id)->get()->result_array();

            foreach($description as $desc)
            {
                $data['desc'][$desc['id_langs']]['name'] = $desc['name'];
                $data['desc'][$desc['id_langs']]['description'] = $desc['description'];
            }

            $this->load->helper('permissions_modules');
            helper_permissions_modules_form_build($data, '/id/'.$id);
            return TRUE;
        }
        return FALSE;
    }
    
    public function save($id = FALSE)
    {
        if($this->input->post('main'))
        {
            if($id)
            {
                $main = $this->input->post('main');
                $desc = $this->input->post('desc');

                if (!$this->save_validate()) return FALSE;

                $this->db->trans_start();

                $this->db->where('`'.self::ID_P_MODULES.'`', $id)->update('`'.self::P_MODULES.'`', $main);

                $this->db
                     ->select("`id_langs`, `name`")
                     ->from("`langs`");
                $langs = $this->db->get()->result_array();

                foreach ($langs as $lang) {
                    if (isset($desc[$lang['id_langs']])) {
                        $description = array(
                            'name' => $desc[$lang['id_langs']]['name'],
                            'description' => $desc[$lang['id_langs']]['description']
                        );
                        $this->db->where('`'.self::ID_P_MODULES.'`', $id)->where('`id_langs`', $lang['id_langs'])
                                 ->update('`'.self::P_MODULES_DESC.'`', $description);
                    }
                }

                $this->db->trans_complete();
                if (!$this->db->trans_status()) return FALSE;

                return TRUE;
            }
            else
            {
                $main = $this->input->post('main');
                $desc = $this->input->post('desc');

                if (!$this->save_validate()) return FALSE;

                $this->db->trans_start();
                $last_id = $this->sql_add_data($main)->sql_save(self::P_MODULES);
                $sort['sort'] = $last_id;
                $this->db->where('`'.self::ID_P_MODULES.'`', $last_id)->update('`'.self::P_MODULES.'`', $sort);

                $this->db
                     ->select("`id_langs`, `name`")
                     ->from("`langs`");
                $langs = $this->db->get()->result_array();

                foreach ($langs as $lang) {
                    if (isset($desc[$lang['id_langs']])) {
                        $description = array(
                            self::ID_P_MODULES => $last_id,
                            'name' => $desc[$lang['id_langs']]['name'],
                            'description' => $desc[$lang['id_langs']]['description'],
                            'id_langs' => $lang['id_langs']
                        );
                        $this->sql_add_data($description)->sql_save(self::P_MODULES_DESC);
                    }
                }

                $this->db->trans_complete();
                if (!$this->db->trans_status()) return FALSE;

                return $last_id;
            }
        }
    }
    
    public function delete($id)
    {
        if(is_array($id))
        {
            $this->db->where_in(self::ID_P_MODULES, $id)->delete(self::P_MODULES);
            //$this->db->where_in(self::ID_P_MODULES, $id)->delete(self::P_MODULES_DESC);
            return TRUE;
        }

        $result = $this->db ->select('count(*) AS COUNT')
                            ->from('`'.self::P_MODULES.'`')
                            ->where('`'.self::ID_P_MODULES.'`', $id)->get()->row_array();
        if($result['COUNT'] > 0)
        {
            $this->db->where(self::ID_P_MODULES, $id);
            if($this->db->delete(self::P_MODULES))
            {
                return TRUE;
            }
            return FALSE;
        }
        return FALSE;
    }
    
    public function save_validate()
    {
        $this->load->library("form_validation");

        $this->form_validation->set_rules('main[module]', 'Алиас модуля', 'required');

        if(!$this->form_validation->run())
        {
            $this->messages->add_error_message(validation_errors());
            return FALSE;
        }

        return TRUE;
    }
    
}

/*  End of file mpermissions_modules.php  */