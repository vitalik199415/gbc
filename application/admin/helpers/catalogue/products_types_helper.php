<?php
function helper_products_types_grid_build($grid)
{
	$grid->add_button('Свойства продуктов', set_url('*/products_properties'),
		array(
			'class' => 'addButton'
		)
	);
	$grid->add_button('Добавить группу свойств', set_url('*/*/add'),
		array(
			'class' => 'addButton'
		)
	);
	
	$grid->set_checkbox_actions('ID', 'products_types_grid_checkbox',
		array(
			'options' => array(
				'on'	=> 'Активность: Да',
				'off'	=> 'Активность: Нет',
				'delete'=> 'Удалить'
			),
			'name'	=> 'products_types_grid_select'
		)
	);
	
	$grid->add_column(
		array
			(
				'index'		 => 'sort',
				'tdwidth'	 => '6%',
				'option_string' => 'align="center"'
			), 'Позиция');
	
	$grid->add_column(
		array(
			'index'		=> 'alias',
			'type'		=> 'text',
			'tdwidth'	=> '24%',
			'filter'	=> true
		), 'Индификатор');
	
	$grid->add_column(
		array(
			'index'		=> 'name',
			'type'		=> 'text',
			'filter'	=> 	true
		), 'Название группы свойств');
	
	$grid->add_column(
		array(
			'index'		=> 'active',
			'type'		=> 'select',
			'tdwidth'	=> '12%',
			'filter'	=> true,
			'options'	=> array('' => '', '0' => 'Нет', '1' => 'Да')
		), 'Активность');
	
	$grid->add_column(
		array(
			'index'			=> 'action',
			'type'			=> 'action',
			'tdwidth'		=> '12%',
			'option_string'	=> 'align="center"',
			'actions'		=> array(
				array(
					'type'			=> 'link',
					'html'			=> '',
					'href'			=> set_url('*/*/edit/id/$1'),
					'href_values'	=> array('ID'),
					'options'		=> array('class'=>'icon_edit', 'title'=>'Редактировать тип свойств')
				),
				array(
					'type'			=> 'link',
					'html'			=> '',
					'href'			=> set_url('*/*/delete/id/$1'),
					'href_values'	=> array('ID'),
					'options'		=> array('class'=>'icon_detele  delete_question', 'title'=>'Удалить тип свойств')	
				)
			)
		), 'Действия');
}
function helper_products_types_form_build($data = array(), $save_param = '')
{
	$form_id = 'products_types_add_edit_form';
	$CI = & get_instance();
	$CI->load->library('form');
	$CI->form->_init('Группы свойств продукции', $form_id, set_url('*/*/save'.$save_param));
	
	$CI->form->add_button(
		array(
			'name' 		=> 'Назад',
			'href' 		=> set_url('*/*/'),
		));
	
	if($save_param !='')
	{
		$CI->form->add_button(
			array(
				'name'		=> 'Добавить группу свойств',
				'href'		=> set_url('*/*/add'),
			));
		
		$CI->form->add_button(
			array(
				'name'		=> 'Удалить группу',
				'href'		=> set_url('*/*/delete'.$save_param),
				'options'	=> array('class'=>'delete_question')
			));
	}
	
	$CI->form->add_button(
		array(
			'name'		=> 'Сохранить и продолжить редактирование',
			'href'		=> '#',
			'options'	=> array(
				'id'	 => 'submit_back', 
				'class' => 'addButton'
			)
		));
	
	$CI->form->add_button(
		array(
			'name'		=> 'Сохранить',
			'href'		=> '#',
			'options'	=> array(
				'id'	=> 'submit',
				'class' => 'addButton'
			)
		));
	
	if($save_param == '')
	{
		$CI->form->add_validation('main[alias]', array('required' => 'true', 'remote' => '{url:"'.set_url('*/*/check_alias').'", type:"post"}'));
	}
	else
	{
		$CI->form->add_validation('main[alias]', array('required' => 'true', 'remote' => '{url:"'.set_url('*/*/check_alias'.$save_param).'", type:"post"}'));
	}
	$CI->form->add_validation_massages('main[alias]', array('remote' => 'Группа свойств с указанным индификатором уже существует!'));
	
	$CI->form->add_tab('main_block', 'Основные данные');
	$CI->form->add_tab('desc_block', 'Описание типа');
	
	$PMdata['main'] = FALSE;
	if(isset($data['main'])) $PMdata['main'] = $data['main'];
	$CI->form->add_group('main_block', $PMdata);

	$lid = $CI->form->group('main_block')->add_object(
		'fieldset',
		'base_fieldset',
		'Основные данные'
	);
	$CI->form->group('main_block')->add_object_to($lid,
		'text',
		'main[alias]', 
		'Индификатор(латиницей) (*):'
	);
	$CI->form->group('main_block')->add_object_to($lid,
		'select',
		'main[active]',
		'Активнось (*):',
		array(
			'options' => array('0'=>'Нет', '1'=>'Да')
		)
	); 
	
	$PDdata['desc'] = FALSE;
	if(isset($data['desc'])) $PDdata['desc'] = $data['desc'];
	$CI->form->add_group('desc_block', $PDdata, $data['on_langs']);
	$lid = $CI->form->group('desc_block')->add_object(
		'fieldset',
		'name_fieldset',
		'Описание типа свойств'
	);
	$CI->form->group('desc_block')->add_object_to($lid,
		'text',
		'desc[$][name]',
		'Название типа :'
	);
	$CI->form->group('desc_block')->add_object_to($lid,
		'textarea',
		'desc[$][description]',
		'Oписание :',
		array(
			'option'=>array('rows' => '4') 
		)
	);
		
	$CI->form->add_block_to_tab('main_block', 'main_block');
	$CI->form->add_block_to_tab('desc_block', 'desc_block');
	$CI->form->render_form();
}
?>