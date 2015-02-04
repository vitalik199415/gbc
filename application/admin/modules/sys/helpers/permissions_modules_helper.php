<?php

function helper_permissions_modules_grid_build($grid) {
    $grid->add_button(
        'Добавить новый модуль',
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
            'index'         => 'aliace',
            'option_string' => 'align="center"',
        ), 'Алиас модуля:'
    );

    $grid->add_column(
        array(
            'index'         => 'name',
            'option_string' => 'align="center"',
        ), 'Название модуля:'
    );

    $grid->add_column(
        array(
            'index'         => 'description',
            'option_string' => 'align="center"'
        ), 'Описание модуля:'
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
                    'options'       => array('class'=>'icon_edit', 'title'=>'Редактировать модуль')
                ),
                array(
                    'type'          => 'link',
                    'html'          => '',
                    'href'          => set_url('*/*/delete/id/$1'),
                    'href_values'   => array('ID'),
                    'options'       => array('class'=>'icon_delete delete_question' , 'title'=>'Удалить модуль')
                )
            )
        ), 'Действия:'
    );
}

function helper_permissions_modules_form_build($data = array(), $save_param = '') {
    $form_id = 'permissions_modules_add_edit_form';
    $CI = &get_instance();
    $CI->load->library('form');
    $CI->form->_init('Модули системы', $form_id, set_url("*/*/save".$save_param));
    
    $CI->form->add_button(
        array(
            'name'  => 'Назад',
            'href'  => set_url('*/*/')
        )
    );
    
    $CI->form->add_button(
        array(
            'name' => 'Добавить модуль',
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
    $CI->form->add_tab('desc_block', 'Описание модуля');
    
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
        'main[module]',
        'Алиас модуля:'
    );

    $CI->form->group('main_block')->add_object_to($main,
        'text',
        'main[rang]',
        'Ранг доступа к модулю:'
    );

    //Блок описания
    $PMdata = FALSE;
    if(isset($data['desc'])) $PMdata['desc'] = $data['desc'];
    $CI->form->add_group('desc_block', $PMdata, $data['langs']);

    $desc = $CI->form->group('desc_block')->add_object(
        'fieldset',
        'base_fieldset',
        'Описание:'
    );

    $CI->form->group('desc_block')->add_object_to($desc,
        'text',
        'desc[$][name]',
        'Название модуля:'
    );

    $CI->form->group('desc_block')->add_object_to($desc,
        'textarea',
        'desc[$][description]', 
        'Описание модуля:'
    );
    
    $CI->form->add_block_to_tab('main_block', 'main_block');
    $CI->form->add_block_to_tab('desc_block', 'desc_block');
    
    $CI->form->render_form();
    
}


/*  End of file permissions_modules_helper.php  */