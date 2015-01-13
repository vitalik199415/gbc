<?php
function helper_catalogue_mass_edit_price_grid_build($Grid)
{	
	$Grid->addGridColumn(
			array(
				'ID',
				array
					(
						'index'		 => 'ID',
						'tdwidth'	 => '6%'
					)
			)
		);
	$Grid->addGridColumn(
			array(
				'ID Родителя',
				array
					(
						'index'		 => 'id_parent',
						'tdwidth'	 => '8%'
					)
			)
		);
	$Grid->addGridColumn(
			array(
				'Уровень',
				array
					(
						'index'		 => 'level',
						'tdwidth'	 => '8%'
					)
			)
		);	
	$Grid->addGridColumn(
		array(
			'Название',
			array
				(
					'index'		 => 'name'
				)
		)
	);
	$Grid->addGridColumn(
		array(
			'Создана',
			array
				(
					'index'		 => 'create_date',
					'tdwidth'	 => '10%'
				)
		)
	);
	$Grid->addGridColumn(
		array(
			'Обновлена',
			array
				(
					'index'		 => 'update_date',
					'tdwidth'	 => '10%'
				)
		)
	);
	$Grid->addGridColumn(
		array(
			'Активность',
			array
				(
					'index'		 => 'active',
					'tdwidth'	 => '8%'
				)
		)
	);
	$Grid->addGridColumn(
		array(
			'Действия',
			array
				(
					'index'		 => 'action',
					'type'		 => 'action',
					'tdwidth'	 => '10%',
					'option_string' => 'align="center"',
					'actions'	 => array(
						array(
							'type' 			=> 'link',
							'html' 			=> '',
							'href' 			=> set_url(array('*','*','actions','cat_id','$1')),
							'href_values' 	=> array('ID'),
							'options'		=> array('class' => 'icon_arrow_r', 'title' => 'Перейти')
						)
					)
				)
		));
	return $Grid;
}

function helper_catalogue_mass_edit_price_categorie_products_grid_build($grid, $cat_id)
{
	$grid->set_checkbox_actions('ID', 'products_checkbox[]',
			array(
				'options' => NULL,
				'name' => NULL
			)
		);
	$grid->add_column(
		array(
			'index'		 => 'sku',
			//'searchtable'=> 'A',
			'type'		 => 'text',
			'tdwidth'	 => '9%',
			'filter'	 => true
		), 'Артикул');
	$grid->add_column(
		array(
			'index'		 => 'name',
			//'searchtable'=> 'B',
			'type'		 => 'text',
			'filter'	 => true
		), 'Название');
	$grid->add_column(
		array(
			'index'		 => 'price',
			'type'		 => 'text',
			'tdwidth'	 => '8%',
		), 'Цена');
	$grid->add_column(
		array(
			'index'		 => 'special_price',
			'type'		 => 'text',
			'tdwidth'	 => '9%',
		), 'Спец. цена');
	$grid->add_column(
		array
			(
			'index'		 => 'special_price_from',
			'type'		 => 'date',
			'tdwidth'	 => '9%'
		), 'С.Ц. от');
	$grid->add_column(
		array
			(
			'index'		 => 'special_price_to',
			'type'		 => 'date',
			'tdwidth'	 => '9%'
		), 'С.Ц. до');
	$grid->add_column(
		array
			(
			'index'		 => 'status',
			'type'		 => 'select',
			'options'	 => array(''=>'','0'=>'Нет','1'=>'Да'),
			'tdwidth'	 => '9%',
			'filter'	 => TRUE
		), 'В поиске');
	$grid->add_column(
		array
			(
			'index'		 => 'in_stock',
			'type'		 => 'select',
			'options'	 => array(''=>'','0'=>'Нет','1'=>'Да'),
			'tdwidth'	 => '9%',
			'filter'	 => TRUE
		),'В наличии');
	$grid->add_column(
		array
			(
			'index'		 => 'sale',
			'type'		 => 'select',
			'options'	 => array(''=>'','0'=>'Нет','1'=>'Да'),
			'tdwidth'	 => '7%',
			'filter'	 => TRUE
		),'Акция');
	$grid->add_column(
		array(
			'index'		 => 'action',
			'type'		 => 'action',
			'tdwidth'	 => '8%',
			'option_string' => 'align="center"',
			'sortable' 	 => false,
			'filter'	 => false,
			'actions'	 => array(
			array(
					'type' 			=> 'link',
					'html' 			=> '',
					'href' 			=> set_url(array('catalogue','products','view','id','$1')),
					'href_values' 	=> array('ID'),
					'options'		=> array('class'=>'icon_view products_view', 'title'=>'Просмотр продукта')
				)
			)
		), 'Действие');
}

function helper_catalogue_mass_edit_price_action_form_build($cat_id, $data)
{
	$form_id = 'catalogue_mass_sale_categories_action_form';
	$CI = & get_instance();
	$CI->load->library('form');
	$CI->form->_init('Действия с товарами', $form_id, set_url('*/*/save_changes/cat_id/'.$cat_id));
	
	
	$CI->form->add_button(
		array(
		'name' => 'Применить действие к выбраным продуктам',
		'href' => '#',
		'options' => array(
			'id' => 'submit'
		)
	));
	
	$CI->form->add_tab('m_b', 'Действия');
	
	$CI->form->add_group('m_b');
	
	$lid = $CI->form->group('m_b')->add_object(
		'fieldset',
		'sale_actions_data',
		'Действие'
	);

	//$CI->form->group('m_b')->add_html_to($lid, "<div id='sale_actions_js_actions'>");

	$CI->form->group('m_b')->add_object_to($lid,
		'select', 
		'sale_actions[type]',
		'Выбор типа действия (*):',
		array(
			'options'	=> array('percent' => 'Уменьшение значения цены на %', 'minus_price' => 'Уменьшение цены на фиксированую сумму', 'cancel' => 'Отмена акции на товары'),
			'option' => array('id' => 'sale_actions')
		)
	);

	$CI->form->group('m_b')->add_html_to($lid, "<div id='sale_actions_hide_all'>");
	
	$CI->form->group('m_b')->add_object_to($lid,
		'text',
		'sale_actions[value]',
		'Значение (зависит от выбраного типа действия) :'
	);

	$CI->form->group('m_b')->add_object_to($lid,
		'select',
		'sale_actions[price_options]',
		'Тип действия :',
		array(
			'options'	=> array('noise_price' => 'Уменьшение оригинальной цены', 'new_action_price' => 'Создание акционной цены'),
			'option' => array('id' => 'sale_actions_new_action_price')
		)
	);

	$CI->form->group('m_b')->add_object_to($lid,
		'select',
		'sale_actions[sale_options]',
		'Отметить как акция :',
		array(
			'options'	=> array('1' => 'Да', '0' => 'Нет')

		)
	);

	$CI->form->group('m_b')->add_html_to($lid, "<div id='sale_actions_date_select'>");

	$CI->form->group('m_b')->add_object_to($lid,
		'text',
		'sale_actions[special_price_from]',
		'Специальная цена от даты :',
		array(
			'option' => array('class' => 'datepicker')
		)
	);

	$CI->form->group('m_b')->add_object_to($lid,
		'text',
		'sale_actions[special_price_to]',
		'Специальная цена до даты :',
		array(
			'option' => array('class' => 'datepicker')
		)
	);
	$CI->form->group('m_b')->add_html_to($lid, "</div>");
	$CI->form->group('m_b')->add_html_to($lid, "</div>");
	//$CI->form->group('m_b')->add_html_to($lid, "</div>");

	$js = "
		$(document).ready(function(){
			$('#sale_actions_date_select').hide();
			$('#".$form_id."').find('#sale_actions').change(function()
			{
				if($('#sale_actions').val() == 'cancel')
				{
					$('#sale_actions_hide_all').hide();
					$('#".$form_id."').find('#sale_actions_new_action_price').val('noise_price');
					$('#sale_actions_date_select').hide();
				}else{
					$('#sale_actions_hide_all').show();
				}
			});
			$('#".$form_id."').find('#sale_actions_new_action_price').change(function()
				{
				if($('#sale_actions_new_action_price').val() == 'new_action_price')
				{
					$('#sale_actions_date_select').show();
				}else{
					$('#sale_actions_date_select').hide();
				}
			});
		});
	";

	$CI->form->group('m_b')->add_object(
		'js',
		$js
	);
	
	$lid = $CI->form->group('m_b')->add_object(
		'fieldset',
		'categories_products_data',
		'Товары в категории',
		array(
			'style' => 'background-color:#CCCCCC;'
		)	
	);
	$CI->form->group('m_b')->add_html_to($lid, $data['products']);
	$CI->form->group('m_b')->add_view_to($lid, 'catalogue/products/products_grid_js', array('product_grid_id' => 'products_mass_sale_grid'));
	
	$CI->form->add_block_to_tab('m_b', 'm_b');
	$CI->form->render_form();
}	
?>