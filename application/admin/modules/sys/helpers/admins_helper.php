<?php

function helper_admins_grid_build($grid) {
    $grid->add_button(
        'Добавить нового администратора',
        set_url('*/*/add'),
        array(
            'class' => 'addButton'
        )
    );

    $grid->set_checkbox_actions(
        'ID',
        'permissions_modules_grid_checkbox',
        array(
            'option' => array(
                'on'     => 'Активность: Да',
                'off'    => 'Активность: Нет',
                'delete' => 'Удалить выбранные'
            ),
            'name'  => 'permissions_modules_grid_select'
        )
    );

    $grid->add_column(
        array(
            'index'         => 'name',
            'option_string' => 'align="center"',
        ), 'Им`я администратора:'
    );

    $grid->add_column(
        array(
            'index'         => 'login',
            'option_string' => 'align="center"',
        ), 'Логин:'
    );

    $grid->add_column(
        array(
            'index'         => 'email',
            'option_string' => 'align="center"'
        ), 'Електронная пошта:'
    );

    $grid->add_column(
        array(
            'index'         => 'active',
            'option_string' => 'align="center"'
        ), 'Активность:'
    );

    $grid->add_column(
        array(
            'index'         => 'note',
            'option_string' => 'align="center"'
        ), 'Заметки:'
    );

    $grid->add_column(
        array(
            'index'         => 'action',
            'type'          => 'action',
            'tdwidth'       => '8%',
            'option_string' => 'align="center"',
            'actions'       => array(
                array(
                    'type'          => 'link',
                    'html'          => '',
                    'href'          => set_url('*/*/edit/id/$1'),
                    'href_values'   => array('ID'),
                    'options'       => array('class'=>'icon_edit', 'title'=>'Редактировать')
                ),
                array(
                    'type'          => 'link',
                    'html'          => '',
                    'href'          => set_url('*/*/delete/id/$1'),
                    'href_values'   => array('ID'),
                    'options'       => array('class'=>'icon_delete delete_question' , 'title'=>'Удалить')
                )
            )
        ), 'Действия:'
    );
}

function helper_admins_form_build($data = array(), $save_param = '') {
    $form_id = 'admins_add_edit_form';
    $CI = &get_instance();
    $CI->load->library('form');
    $CI->form->_init('Администраторы системы', $form_id, set_url("*/*/save".$save_param));

    $CI->form->add_button(
        array(
            'name'  => 'Назад',
            'href'  => set_url('*/*/')
        )
    );

    $CI->form->add_button(
        array(
            'name' => 'Добавить администратора',
            'href' => set_url('*/*/add')
        )
    );

    $CI->form->add_button(
        array(
            'name' => 'Сохранить',
            'href' => '#',
            'options' => array(
                'class' => 'addButton',
                'id' => 'submit',
                'display' => NULL
            )
        )
    );

    $CI->form->add_button(
        array(
            'name' => 'Сохранить и продолжить',
            'href' => '#',
            'options' => array(
                'class' => 'addButton',
                'id' => 'submit_back',
                'display' => NULL
            )
        )
    );

    $CI->form->add_tab('main_block', 'Основные данные');
    $CI->form->add_tab('a_modules', 'Системные модули');
    $CI->form->add_tab('u_modules', 'Пользовательские модули');

    //Блок основных данных
    $PMdata = FALSE;
    if(isset($data['main'])) $PMdata['main'] = $data['main'];

    $CI->form->add_group('main_block', $PMdata);

    $main = $CI->form->group('main_block')->add_object(
        'fieldset',
        'base_fieldset',
        'Основные данные'
    );

    $CI->form->group('main_block')->add_object_to($main,
        'text',
        'main[login]',
        'Логин:'
    );

    $CI->form->group('main_block')->add_object_to($main,
        'text',
        'main[password]',
        'Пароль:'
    );

    $CI->form->group('main_block')->add_object_to($main,
        'text',
        'main[email]',
        'Електронная пошта:'
    );

    $CI->form->group('main_block')->add_object_to($main,
        'text',
        'main[name]',
        'Имя:'
    );

    $CI->form->group('main_block')->add_object_to($main,
        'text',
        'main[note]',
        'Заметки:'
    );

    $CI->form->group('main_block')->add_object_to($main,
        'select',
        'main[active]',
        'Активность:',
        array(
            'options' => array('0' => 'Нет', '1' => 'Да')
        )
    );

    $CI->form->group('main_block')->add_object_to($main,
        'select',
        'main[superadmin]',
        'Суперадминистратор:',
        array(
            'options' => array('0' => 'Нет', '1' => 'Да')
        )
    );

    $PMdata = FALSE;
    if(isset($data['system_modules'])) $PMdata['system_modules'] = $data['system_modules'];

    $CI->form->add_group('a_modules', $PMdata);

    $system_modules = $CI->form->group('a_modules')->add_object(
        'fieldset',
        'base_fieldset',
        'Доступ к системным модулям'
    );

    if(count($data['a_modules']) > 0) {
        foreach ($data['a_modules'] as $key => $vall) {
            $CI->form->group('a_modules')->add_object_to($system_modules,
                'checkbox',
                'system_modules['.$key.'][id]',
                $vall['name'],
                array(
                    'value' => $key,
                    'option' => array('class' => 'attributes')
                )
            );

            $CI->form->group('a_modules')->add_object_to($system_modules,
                'html',
                '<div style="padding:5px 30px; margin: 0 0 10px 50px;color: #fff">'
            );

            $CI->form->group('a_modules')->add_object_to($main,
                'select',
                'system_modules['.$key.'][type]',
                '',
                array(
                    'options' => array('0' => 'Просмотр записей в таблицах',
                                       '1' => 'Добавление, редактирование, удаление собственных записей в таблицах',
                                       '2' => 'Добавление, редактирование, удаление всех записей в таблицах')
                )
            );

            $CI->form->group('a_modules')->add_object_to($system_modules,
                'html',
                '<p>'.$vall['desc'].'</p>'
            );

            $CI->form->group('a_modules')->add_object_to($system_modules,
                'html',
                '</div>'
            );
        }
    }

    $PMdata = FALSE;
    if(isset($data['user_modules'])) $PMdata['user_modules'] = $data['user_modules'];

    $CI->form->add_group('u_modules', $PMdata);

    $user_modules = $CI->form->group('u_modules')->add_object(
        'fieldset',
        'base_fieldset',
        'Доступ к пользовательским модулям'
    );

    if(count($data['u_modules']) > 0) {
        foreach ($data['u_modules'] as $key => $vall) {
            $CI->form->group('u_modules')->add_object_to($user_modules,
                'checkbox',
                'user_modules['.$key.'][id]',
                $vall['name'],
                array(
                    'value' => $key,
                    'option' => array('class' => 'attributes')
                )
            );

            $CI->form->group('u_modules')->add_object_to($user_modules,
                'html',
                '<div style="padding:5px 30px; margin: 0 0 10px 50px; color: #fff">'
            );

            $CI->form->group('u_modules')->add_object_to($main,
                'select',
                'user_modules['.$key.'][type]',
                '',
                array(
                    'options' => array('0' => 'Просмотр записей в таблицах',
                        '1' => 'Добавление, редактирование, удаление собственных записей в таблицах',
                        '2' => 'Добавление, редактирование, удаление всех записей в таблицах')
                )
            );
            $CI->form->group('u_modules')->add_object_to($user_modules,
                'html',
                '<p>'.$vall['desc'].'</p>'
            );

            $CI->form->group('u_modules')->add_object_to($user_modules,
                'html',
                '</div>'
            );
        }
    }

    $CI->form->add_block_to_tab('main_block', 'main_block');
    $CI->form->add_block_to_tab('a_modules', 'a_modules');
    $CI->form->add_block_to_tab('u_modules', 'u_modules');

    $CI->form->render_form();

}