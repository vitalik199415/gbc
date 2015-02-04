<?php

class Madmins extends AG_Model {
    const M_ADMIN = 'm_administrators';
    const ID_M_ADMIN = 'id_m_administrators';

    function __construct() {
        parent::__construct();
    }

    public function render_admins_grid() {
        $this->load->library('grid');
        $this->grid->_init_grid('permissions_modules_grid', array());

        $this->grid->db->select(self::ID_M_ADMIN." as ID, name, login, email, note, active")->from(self::M_ADMIN);

        $this->load->helper('admins');
        helper_admins_grid_build($this->grid);
        $this->grid->create_grid_data();
        $this->grid->render_grid();
    }

    public function add() {
        if($this->session->get_data('super') == 1) {

            $data = array();

            $rang = $this->session->get_data('rang');

            $a_modules = $this->db->select('PM.`id_m_permissions_modules` as ID,PM.`module`, PM.`rang`, PMD.`name`')
                ->from('m_permissions_modules as PM')
                ->join('m_permissions_modules_description as PMD', 'PM.`id_m_permissions_modules`=PMD.`id_m_permissions_modules`', 'INNER')
                ->where('PMD.`id_langs`', 1)->get()->result_array();

            foreach ($a_modules as $key => $vall) {
                if ($vall['rang'] <= $rang) {
                    $data['a_modules'][$vall['ID']] = $vall['module'].'     ['.$vall['name'].']';
                }
            }

            $u_modules = $this->db->select('UM.`id_users_modules` as ID, UM.`alias`, M.`rang`, MD.`name`')
                ->from('users_modules as UM')
                ->join('modules as M', 'UM.`id_modules`=M.`id_modules`', 'INNER')
                ->join('modules_description as MD', 'UM.`id_modules`=MD.`id_modules`', 'INNER')
                ->where('UM.`id_users`', $this->id_users)->where('MD.`id_langs`', 1)->get()->result_array();

            foreach ($u_modules as $key => $vall) {
                if ($vall['rang'] <= $rang) {
                    $data['u_modules'][$vall['ID']] = $vall['alias'].'     ['.$vall['name'].']';
                }
            }

            $this->load->helper('admins');
            helper_admins_form_build($data);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function edit($id){
        if($this->session->get_data('super') == 1) {
            $main = $this->db->select(self::ID_M_ADMIN." as ID, name, login, password, email, note, active")
                ->from(self::M_ADMIN)->where(self::ID_M_ADMIN, $id)->where('id_users', $this->id_users)->limit(1)
                ->get()->row_array();

            if(count($main)>0)
            {
                $rang = $this->session->get_data('rang');
                $data = array();

                $data['main'] = $main;

                $a_modules = $this->db->select('PM.`id_m_permissions_modules` as ID,PM.`module`, PM.`rang`, PMD.`name`')
                    ->from('m_permissions_modules as PM')
                    ->join('m_permissions_modules_description as PMD', 'PM.`id_m_permissions_modules`=PMD.`id_m_permissions_modules`', 'INNER')
                    ->where('PMD.`id_langs`', 1)->get()->result_array();

                foreach ($a_modules as $key => $vall) {
                    if ($vall['rang'] <= $rang) {
                        $data['a_modules'][$vall['ID']] = $vall['module'].'     ['.$vall['name'].']';
                    }
                }

                $u_modules = $this->db->select('UM.`id_users_modules` as ID, UM.`alias`, M.`rang`, MD.`name`')
                    ->from('users_modules as UM')
                    ->join('modules as M', 'UM.`id_modules`=M.`id_modules`', 'INNER')
                    ->join('modules_description as MD', 'UM.`id_modules`=MD.`id_modules`', 'INNER')
                    ->where('UM.`id_users`', $this->id_users)->where('MD.`id_langs`', 1)->get()->result_array();

                foreach ($u_modules as $key => $vall) {
                    if ($vall['rang'] <= $rang) {
                        $data['u_modules'][$vall['ID']] = $vall['alias'].'     ['.$vall['name'].']';
                    }
                }

                $system_modules = $this->db->select("id_m_permissions_modules as ID")
                    ->from("m_administrator_permissions_modules")->where(self::ID_M_ADMIN, $id)
                    ->get()->result_array();

                foreach ($system_modules as $sys_module) {
                    $data['system_modules'][$sys_module['ID']] = $sys_module['ID'];
                }

                $user_modules = $this->db->select("id_users_modules as ID")
                    ->from("m_administrator_permissions_users_modules")->where(self::ID_M_ADMIN, $id)
                    ->get()->result_array();

                foreach ($user_modules as $user_module) {
                    $data['user_modules'][$user_module['ID']] = $user_module['ID'];
                }

                $this->load->helper('admins');
                helper_admins_form_build($data, '/id/'.$id);
                return TRUE;
            }
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
                $main['id_users'] = $this->id_users;
                $user_modules = $this->input->post('user_modules');
                $system_modules = $this->input->post('system_modules');

                if (!$this->save_validate()) return FALSE;

                $this->db->trans_start();

                $this->db->where('`'.self::ID_M_ADMIN.'`', $id)->update('`'.self::M_ADMIN.'`', $main);

                $this->save_permissions_modules($system_modules, $id);
                $this->save_user_modules($user_modules, $id);

                $this->db->trans_complete();
                if (!$this->db->trans_status()) return FALSE;

                return TRUE;
            }
            else
            {
                $main = $this->input->post('main');
                $main['id_users'] = $this->id_users;
                $user_modules = $this->input->post('user_modules');
                $system_modules = $this->input->post('system_modules');

                if (!$this->save_validate()) return FALSE;

                $this->db->trans_start();
                $last_id = $this->sql_add_data($main)->sql_save(self::M_ADMIN);

                foreach($system_modules as $a_module) {
                    $module = array(
                        self::ID_M_ADMIN => $last_id,
                        'id_m_permissions_modules' => $a_module
                    );
                    $this->db->insert('m_administrator_permissions_modules', $module);
                }

                foreach($user_modules as $u_module) {
                    $module = array(
                        self::ID_M_ADMIN => $last_id,
                        'id_users_modules' => $u_module
                    );
                    $this->db->insert('m_administrator_permissions_users_modules', $module);
                }

                $this->db->trans_complete();
                if (!$this->db->trans_status()) return FALSE;

                return $last_id;
            }
        }
    }

    public function save_permissions_modules($POST, $ID)
    {
        $result = $this->db->where('`'.self::ID_M_ADMIN.'`', $ID)->get('`m_administrator_permissions_modules`')->result_array();

        foreach($result as $module_id)
        {
            $data[$module_id['id_m_permissions_modules']] = $module_id['id_m_permissions_modules'];
        }

        foreach($POST as $m_id)
        {
            if(isset($data[$m_id]))
            {
                unset($data[$m_id]);
            }
            else
            {
                $group = array('id_m_permissions_modules' => $m_id, self::ID_M_ADMIN => $ID);
                $this->db->insert('`m_administrator_permissions_modules`', $group);
            }
        }

        $del_data = FALSE;
        if(isset($data))
        {
            foreach($data as $id)
            {
                $del_data[] = $id;
            }
        }
        if($del_data)
        {
            $this->db->where('`'.self::ID_M_ADMIN.'`', $ID)->where_in('`id_m_permissions_modules`', $del_data);
            $this->db->delete('m_administrator_permissions_modules');
        }
    }

    public function save_user_modules($POST, $ID)
    {
        $result = $this->db->where('`'.self::ID_M_ADMIN.'`', $ID)->get('`m_administrator_permissions_users_modules`')->result_array();

        foreach($result as $module_id)
        {
            $data[$module_id['id_users_modules']] = $module_id['id_users_modules'];
        }

        foreach($POST as $m_id)
        {
            if(isset($data[$m_id]))
            {
                unset($data[$m_id]);
            }
            else
            {
                $module = array('id_users_modules' => $m_id, self::ID_M_ADMIN => $ID);
                $this->db->insert('`m_administrator_permissions_users_modules`', $module);
            }
        }

        $del_data = FALSE;
        if(isset($data))
        {
            foreach($data as $id)
            {
                $del_data[] = $id;
            }
        }
        if($del_data)
        {
            $this->db->where('`'.self::ID_M_ADMIN.'`', $ID)->where_in('`id_users_modules`', $del_data);
            $this->db->delete('m_administrator_permissions_users_modules');
        }
    }

    public function delete($id)
    {
        if(is_array($id))
        {
            $this->db->where_in(self::ID_M_ADMIN, $id)->delete(self::M_ADMIN);
            //$this->db->where_in(self::ID_P_MODULES, $id)->delete(self::P_MODULES_DESC);
            return TRUE;
        }

        $result = $this->db ->select('count(*) AS COUNT')
            ->from('`'.self::M_ADMIN.'`')
            ->where('`'.self::ID_M_ADMIN.'`', $id)->get()->row_array();
        if($result['COUNT'] > 0)
        {
            $this->db->where(self::ID_M_ADMIN, $id);
            if($this->db->delete(self::M_ADMIN))
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

        $this->form_validation->set_rules('main[login]', 'Логин', 'required');
        $this->form_validation->set_rules('main[password]', 'Пароль', 'required');
        $this->form_validation->set_rules('main[email]', 'Email', 'required');
        $this->form_validation->set_rules('main[name]', 'Имя', 'required');

        if(!$this->form_validation->run())
        {
            $this->messages->add_error_message(validation_errors());
            return FALSE;
        }

        return TRUE;
    }
}