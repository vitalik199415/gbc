<?php
if(isset($PRD_array) && isset($PRD_block_id))
{
?>
	<div class="clear_both"></div>
	<div class="product_detail_block" id="<?=$PRD_block_id?>">
		<div class="block">
			<div style="padding:5px 0 10px 0;">
			<?php
			if($ORD_ID)
			{
				?><a href="<?=set_url('sales/orders/ajax_get_shop_products_grid/ord_id/'.$ORD_ID)?>" id="back_to_products_top">Назад к списку продуктов</a><?
			}
			else
			{
				?><a href="<?=set_url('sales/orders/ajax_get_shop_products_grid')?>" id="back_to_products_top">Назад к списку продуктов</a><?
			}
			?>
			</div>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
			<td valign="top" width="1">
			<?=$this->load->view('catalogue/products/view_product/images_detail', array(), TRUE);?>
			</td>
			<td valign="top">
				<div class="product_name"><span class="value"><?=$PRD_array['product']['name']?></span></div>
				<div class="product_sku"><span class="label">Артикул</span> : <span class="value"><?=$PRD_array['product']['sku']?></span></div>
				<?
				if($PRD_array['product']['bestseller']) echo '<div class="BNS_label"><span>Хит продаж</span></div>';
				if($PRD_array['product']['new']) echo '<div class="BNS_label"><span>Новинка</span></div>';
				if(!$PRD_array['product']['in_stock'])
				{
					?><div><div class="BNS_label"><span>Нет в наличии</span></div></div><?
				}
				if($PRD_array['product']['in_stock'] && $PRD_array['product']['sale'])
				{
					?><div><div class="BNS_label"><span>Акция</span></div></div><?
				}
				?>
				<?=$this->load->view('catalogue/products/view_product/albums_detail', array(), TRUE);?>
				<table cellspacing="0" cellpadding="0" border="0" width="100%" class="prices_n_attributes">
				<tr>
				<td width="70%" valign="top">
					<div class="product_price">
						<?=$this->load->view('catalogue/products/view_product/prices_detail', array(), TRUE);?>
					</div>
				</td>
				<td width="30%" valign="bottom" align="right">
					<div class="products_attributes">
						<?=$this->load->view('catalogue/products/view_product/attributes_detail', array(), TRUE);?>
					</div>
				</td>
				</tr>
				</table>
				<div class="add_to_cart_bot">
					<div class="add_to_cart"><div class="add_to_cart_input">Количество : <input type="text" name="qty" value="1" autocomplete="off"><div class="real_qty_block" id="real_qty_block"></div></div><a href="<?=set_url('*/*/ajax_add_product_to_cart')?>" class="to_cart" id="to_cart"><span>Добавить к заказу</span></a></div>
				</div>
				<div class="form_message_block create_order_message_block" id="pr_order_message_block">
					<div class="error_message">
						<div></div>
					</div>
					<div class="success_message">
						<div></div>
					</div>
				</div>
			</td>
			</tr>
			</table>
			<div style="margin:5px 0;">
			<?php
			if($ORD_ID)
			{
				?><a href="<?=set_url('sales/orders/ajax_get_shop_products_grid/ord_id/'.$ORD_ID)?>" id="back_to_products_top">Назад к списку продуктов</a><?
			}
			else
			{
				?><a href="<?=set_url('sales/orders/ajax_get_shop_products_grid')?>" id="back_to_products_top">Назад к списку продуктов</a><?
			}
			?>
			</div>
		</div>
	</div>
	<div class="clear_both"></div>
<?
}
?>