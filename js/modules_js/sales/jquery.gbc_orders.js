(function( $ )
{
var order_required_payment_fields = new Object(
{		
	"order_address[B][name]" : 			{required: true},
	"order_address[B][country]" : 		{required: true},
	"order_address[B][city]" : 			{required: true},
	"order_address[B][address]" : 		{required: true},
	"order_address[B][telephone]" : 	{required: true},
	"order_address[B][address_email]" : {required: true, email: true}
});
	
var order_required_shipping_fields = new Object(
{		
	"order_address[S][name]" : 			{required: true},
	"order_address[S][country]" : 		{required: true},
	"order_address[S][city]" : 			{required: true},
	"order_address[S][address]" : 		{required: true},
	"order_address[S][telephone]" : 	{required: true},
	"order_address[S][address_email]" : {required: true, email: true}
});

var option = new Object(
{
	order_overlay_id : '#order_overlay',
	order_overlay_cn : '#order_overlay_content',
	overlay_close : '.close',
	
	cart_id : '#cart_block',
	cart_min_id : '#cart_min_block',
	
	customer_login : '#create_order_customer_login',
	customer_block_login : '#customer_login',
	
	shipping_method_id : '#order_select_shipping_method',
	shipping_block_id : '#order_shipping_block"',
	
	payment_method_id : '#order_select_payment_method',
	payment_block_id : '#order_billing_block',
	payment_method_desc : '#payment_methods_description',
	
	order_message_block_id : '#order_message_block',
	order_message_block_bot_id : '#order_message_block_bot',
	order_success_block_id : '.success_message',
	order_error_block_id : '.error_message',
	
	edit_message_block_id : '#order_cart_edit_message_block',
	edit_success_block_id : '#order_cart_edit_success',
	edit_error_block_id : '#order_cart_edit_error',
	
	submit_button_id : '#order_submit, #order_submit_bot',
	
	order_products_block_id : '#order_products',
	cart_edit : '.cart_edit_edit_item',
	cart_delete : '.cart_edit_delete_item',
	
	url_change_shipping_method : false,
	url_change_payment_method : false,
	
	url_login_form : false,
	url_edit_item : false,
	url_delete_item : false,
	
	url_edit_item : false,
	url_delete_item : false,
	
	success_create_order : false,
	error_submit : false,
	error_create_order : false,
	
	edit_pr_timeout_id : false
});

$.fn.gbc_orders = function(method, settings)
{
	var methods = new Object(
	{
		init : function(settings)
		{
			option = $.extend(option, settings);
			return this.each(function(i,el)
			{
				var $this = $(el);
				var  data = $this.data('gbc_checkout');
				if(!data)
				{	
					$this.data('gbc_checkout', true);
					
					methods.init_order_form.apply($this, Array());
					methods.init_customer_login.apply($this, Array());
					methods.init_payment_method.apply($this, Array());
					methods.init_shipping_method.apply($this, Array());
					methods.init_copy_billing_to_shipping.apply($this, Array());
					methods.init_cart_edit_buttons.apply($this, Array());
				}
			});
		},
		
		reinit_required_shipping_fields : function(required_shipping_fields_update)
		{
			order_required_shipping_fields = required_shipping_fields_update;
		},
		
		init_order_form : function()
		{
			$this = $(this);
			if(typeof($timeoutId) != "undefined")
			{
				clearTimeout($timeoutId);
			}
			$this.find(option.submit_button_id).click(function()
			{
				$this.validate({
					rules : $.extend({}, order_required_shipping_fields, order_required_payment_fields),
					errorPlacement: function(error, element) {
						error.insertAfter(element.parent('div').find('div'));
					}
				});
				
				methods.hide_message_block($this);
				var options = { 
					beforeSubmit: function() { return methods.form_validate($this, $this) },
					success: methods.form_create_order_success,
					dataType:  'json'
				};
				$this.ajaxSubmit(options);
			});
		},
		
		init_customer_login : function()
		{
			$this = $(this);
			$this.find(option.customer_login).click(function()
			{
				$(option.order_overlay_id).find(option.overlay_close).trigger('click');
				setTimeout(function(customer_block_login){$(customer_block_login).trigger('click')}, 500, option.customer_block_login);
				return false;
			});
		},
		
		init_payment_method : function()
		{
			$this = $(this);
			$this.find(option.payment_method_id).on('change', function()
			{
				methods.change_payment_method.apply($this, Array($(this).val()));
			});
		},
		
		change_payment_method : function($val)
		{
			$this = $(this);
			if(option.url_change_payment_method)
			{
				var payment_method_id = $val;
				var data = new Object();
				$this.find(option.payment_block_id).find('input, select').each(function()
				{
					data[$(this).attr('name')] = $(this).val();
				});
				data['payment_method_id'] = payment_method_id;
				jQuery.ajaxAG(
				{
					url: option.url_change_payment_method,
					type: "POST",
					data: data,
					dataType : 'json',
					success: function(d)
					{
						if(d.status == 1)
						{
							$this.find(option.payment_method_desc).find('span').html(d.html);
							//console.log($this.find(option.payment_method_desc).find('span'));
							//methods.init_payment_method.apply($this, Array());
						}	
					}
				});
			}
		},
		
		init_shipping_method : function()
		{
			$this = $(this);
			$this.find(option.shipping_method_id).on('change', function()
			{
				methods.change_shipping_method.apply($this, Array($(this).val()));
			});
		},
		
		change_shipping_method : function($val)
		{
			$this = $(this);
			if(option.url_change_shipping_method)
			{
				var shipping_method_id = $val;
				var data = new Object();
				$this.find(option.shipping_block_id).find('input, select').each(function()
				{
					data[$(this).attr('name')] = $(this).val();
				});
				data['shipping_method_id'] = shipping_method_id;
				jQuery.ajaxAG(
				{
					url: option.url_change_shipping_method,
					type: "POST",
					data: data,
					dataType : 'json',
					success: function(d)
					{
						if(d.status == 1)
						{
							$this.find(option.shipping_block_id).html(d.html);
							methods.init_shipping_method.apply($this, Array());
						}	
					}
				});
			}
		},
		
		init_copy_billing_to_shipping : function()
		{
			$this = $(this);
			$this.find(option.shipping_block_id).on('click', '#same_as_billing', function()
			{
				$.each($this.find('#customer_address_b_fieldset').find('input[type=text], select'), function(i)
				{
					fname = $(this).attr('name');
					$this.find('#customer_address_s_fieldset').find('input[name="'+fname.replace('B', 'S')+'"], select[name="'+fname.replace('B', 'S')+'"]').val($(this).val());
				});
			});
		},
		
		form_validate : function(formData, jqForm, options)
		{
			if($this.valid())
			{
				loading_start();
				return true;
			}
			else
			{
				methods.show_errors(jqForm, option.error_submit);
				return false;
			}	
		},
		
		form_create_order_success : function(responseText, statusText, xhr, $form)
		{
			if(responseText.status == 1)
			{
				methods.show_success($form, responseText.success);
				$(option.cart_id).html(responseText.cart_html);
				$(option.cart_min_id).html(responseText.cart_min_html);
				
				$form.find(option.submit_button_id).remove();
				var $timeoutId = setTimeout(function(block_overlay, overlay_close)
				{
					$(block_overlay).find(overlay_close).trigger('click');
				}, 13000, option.order_overlay_id, option.overlay_close);
			}
			if(responseText.status == 0)
			{
				methods.show_errors($form, responseText.errors);
			}
			loading_stop();
		},
		
		show_errors : function($form, errors)
		{
			var $mblock = $form.find(option.order_message_block_id+','+option.order_message_block_bot_id);
			$mblock.find(option.order_error_block_id).find('div').html(errors);
			$mblock.find(option.order_error_block_id).show();
			$mblock.show();
			$(option.order_overlay_cn).scrollTo(option.order_message_block_id, {duration : 2000});

		},
		
		show_success : function($form, success)
		{
			var $mblock = $form.find(option.order_message_block_id+','+option.order_message_block_bot_id);
			$mblock.find(option.order_success_block_id).find('div').html(success);
			$mblock.find(option.order_success_block_id).show();
			$mblock.show();
			$(option.order_overlay_cn).scrollTo(option.order_message_block_id, {duration : 2000});

		},
		
		hide_message_block : function($form)
		{
			var $mblock = $form.find(option.order_message_block_id+','+option.order_message_block_bot_id);
			$mblock.find(option.order_error_block_id).find('div').html('');
			$mblock.find(option.order_success_block_id).find('div').html('');
			$mblock.find(option.order_error_block_id).hide();
			$mblock.find(option.order_success_block_id).hide();
			$mblock.hide();
		},
		
		init_cart_edit_buttons : function()
		{
			var $this = $(this);
			var PR_block = $this.find(option.order_products_block_id);
			PR_block.on('click', option.cart_edit, function()
			{
				methods.hide_edit_message_block();
				methods.edit_item.apply($this, Array(this));
				return false;
			});
			
			PR_block.on('click', option.cart_delete, function()
			{
				methods.hide_edit_message_block();
				methods.delete_item.apply($this, Array(this));
				return false;
			});
		},
		
		edit_item : function($item)
		{
			methods.hide_edit_message_block(100);
			var $this = $(this);
			var PR_block = $this.find(option.order_products_block_id);
			var data = {};
			var block = PR_block.find($item).parents('tr');
			data['rowid'] = PR_block.find(block).find('input[name="rowid"]').val();
			data['qty'] = PR_block.find(block).find('input[name="qty"]').val();
			if(option.url_edit_item !== false)
			{
				jQuery.ajaxAG(
				{
					url: option.url_edit_item,
					type: "POST",
					data: data,
					dataType : "json",
					success: function(d)
					{
						if(d.success == 1)
						{
							if(d.cart_edit_html == false)
							{
								PR_block.html(d.cart_edit_html);
								$(option.cart_id).html(d.cart_html);
								$(option.cart_min_id).html(d.cart_min_html);
							}
							else
							{
								PR_block.html(d.cart_edit_html);
								$(option.cart_id).html(d.cart_html);
								$(option.cart_min_id).html(d.cart_min_html);
								
								methods.show_edit_success(d.site_messages);
								methods.hide_edit_message_block(d.delay);
							}
						}
						else
						{
							if(typeof(d.available_qty) != "undefined")
							{
								block.find('input[name="qty"]').val(d.available_qty);
							}
							
							methods.show_edit_errors(d.site_messages);
							methods.hide_edit_message_block(d.delay);
						}
					}
				});
			}
		},
		
		delete_item : function($item)
		{
			methods.hide_edit_message_block(100);
			var $this = $(this);
			var PR_block = $this.find(option.order_products_block_id);
			var data = {};
			var block = PR_block.find($item).parents('tr');
			data['rowid'] = PR_block.find(block).find('input[name="rowid"]').val();
			if(option.url_delete_item !== false)
			{
				jQuery.ajaxAG(
				{
					url: option.url_delete_item,
					type: "POST",
					data: data,
					dataType : "json",
					success: function(d)
					{
						if(d.success == 1)
						{
							if(d.cart_edit_html == false)
							{
								$(option.order_overlay_id).find(option.overlay_close).trigger('click');
								$(option.cart_id).html(d.cart_html);
								$(option.cart_min_id).html(d.cart_min_html);
							}
							else
							{
								PR_block.html(d.cart_edit_html);
								$(option.cart_id).html(d.cart_html);
								$(option.cart_min_id).html(d.cart_min_html);
								
								methods.show_edit_success(d.site_messages);
								methods.hide_edit_message_block(d.delay);
							}
						}
						else
						{
							if(d.cart_edit_html == false)
							{
								$(option.order_overlay_id).find(option.overlay_close).trigger('click');
								$(option.cart_id).html(d.cart_html);
								$(option.cart_min_id).html(d.cart_min_html);
							}
							else
							{
								PR_block.html(d.cart_edit_html);
								$(option.cart_id).html(d.cart_html);
								$(option.cart_min_id).html(d.cart_min_html);
								
								methods.show_edit_errors(d.site_messages);
								methods.hide_edit_message_block(d.delay);
							}
						}
					}
				});
			}
		},
		
		show_edit_errors : function(errors)
		{
			$(option.edit_message_block_id).find(option.edit_error_block_id).find('div').html(errors);
			$(option.edit_message_block_id).find(option.edit_error_block_id).show();
			$(option.edit_message_block_id).show();
		},
		
		show_edit_success : function(success)
		{
			$(option.edit_message_block_id).find(option.edit_success_block_id).find('div').html(success);
			$(option.edit_message_block_id).find(option.edit_success_block_id).show();
			$(option.edit_message_block_id).show();
		},
		
		hide_edit_message_block : function(time)
		{
			if(option.edit_pr_timeout_id)
			{
				clearTimeout(option.edit_pr_timeout_id);
				var $ms_block = $(option.edit_message_block_id);
				var $ms_block_s = $(option.edit_message_block_id).find(option.edit_success_block_id);
				var $ms_block_e = $(option.edit_message_block_id).find(option.edit_error_block_id);
				option.edit_pr_timeout_id = setTimeout(function($ms_block, $ms_block_s, $ms_block_e)
				{
					$ms_block_s.find('div').html('');
					$ms_block_e.find('div').html('');
					$ms_block_s.hide();
					$ms_block_e.hide();
					$ms_block.hide();
					
				}, time, $ms_block, $ms_block_s, $ms_block_e);
			}
			else
			{
				var $ms_block = $(option.edit_message_block_id);
				var $ms_block_s = $(option.edit_message_block_id).find(option.edit_success_block_id);
				var $ms_block_e = $(option.edit_message_block_id).find(option.edit_error_block_id);
				option.edit_pr_timeout_id = setTimeout(function($ms_block, $ms_block_s, $ms_block_e)
				{
					$ms_block_s.find('div').html('');
					$ms_block_e.find('div').html('');
					$ms_block_s.hide();
					$ms_block_e.hide();
					$ms_block.hide();
					
				}, time, $ms_block, $ms_block_s, $ms_block_e);
			}
		}
	});
	
	if ( methods[method] )
	{
		return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
	}
	else if ( typeof method === 'object' || ! method )
	{
		return methods.init.apply( this, arguments );
	}
	else
	{
		$.error( 'Метод ' +  method + ' не существует' );
	}
}	
})(jQuery);	