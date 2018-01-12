<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */


// No direct access
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

if(!empty($this->product->addtocart_text)) {
	$cart_text = JText::_($this->product->addtocart_text);
} else {
	$cart_text = JText::_('J2STORE_ADD_TO_CART');
}

$show = J2Store::product ()->validateVariableProduct($this->product);
$message = JText::_('J2STORE_ITEM_ADDED_TO_CART');

if ($this->params->get('list_enable_quickview',0) && JFactory::getApplication()->input->getString('tmpl') =='component') :
	$message .= ' <a href="' . $this->product->checkout_link . '" class="j2store-checkout-link" target="_top">';
else :
	$message .= ' <a href="' . $this->product->checkout_link . '" class="j2store-checkout-link">';
endif;
	$message .= JText::_('J2STORE_CHECKOUT') . '</a>';

Factory::getDocument()->addScriptDeclaration(<<<JS
		jQuery(document).ready(function(){
			jQuery('body').on('after_adding_to_cart', function(element, data, type) {
				// Add our own logic here, like rendering a message
				if (type.success === 1) {
					jQuery('div.message__wrapper div.content').html('{$message}');
					jQuery('div.message__wrapper').removeClass('message__wrapper--none');
				}
				else {
					jQuery('div.message__wrapper div.content').html('Er was een probleem om het product toe te voegen. Probeer het nogmaals.');
					jQuery('div.message__wrapper').removeClass('message__wrapper--none').addClass('message__wrapper--error');
				}
			});
		});
JS
);
?>
	<?php echo J2Store::plugin()->eventWithHtml('BeforeAddToCartButton', array($this->product, J2Store::utilities()->getContext('view_cart'))); ?>
	<?php if($show): ?>
		<div id="add-to-cart-<?php echo $this->product->j2store_product_id; ?>" class="j2store-add-to-cart">

		<?php echo J2Store::product()->displayQuantity('com_j2store.product.default', $this->product, $this->params, array( 'class'=>'input-mini form-control ' ) ); ?>

			<input type="hidden" id="j2store_product_id" name="product_id" value="<?php echo $this->product->j2store_product_id; ?>" />

				<input
					data-cart-action-always="<?php echo JText::_('J2STORE_ADDING_TO_CART'); ?>"
					data-cart-action-done="<?php echo $cart_text; ?>"
					data-cart-action-timeout="1000"
				   value="<?php echo $cart_text; ?>"
				   type="submit"
				   class="j2store-cart-button <?php echo $this->params->get('addtocart_button_class', 'btn btn-primary');?>"
				   />
	   </div>
	<?php else: ?>
			<input value="<?php echo JText::_('J2STORE_OUT_OF_STOCK'); ?>" type="button" class="j2store_button_no_stock btn btn-warning" />
	<?php endif; ?>

	<?php echo J2Store::plugin()->eventWithHtml('AfterAddToCartButton', array($this->product, J2Store::utilities()->getContext('view_cart'))); ?>

	<input type="hidden" name="option" value="com_j2store" />
	<input type="hidden" name="view" value="carts" />
	<input type="hidden" name="task" value="addItem" />
	<input type="hidden" name="ajax" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="return" value="<?php echo base64_encode( JUri::getInstance()->toString() ); ?>" />
	<div class="j2store-notifications"></div>
