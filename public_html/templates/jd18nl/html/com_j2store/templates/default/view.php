<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 *
 * Bootstrap 2 layout of product detail
 */
// No direct access
defined('_JEXEC') or die;

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

$title       = $this->escape($this->params->get('page_heading'));
echo JLayouts::render('template.content.header', array('title' => $title));

?>
<div class="j2store-single-product <?php echo $this->product->product_type; ?> detail bs2 <?php echo $this->product->params->get('product_css_class','');?>">
	<?php if ($this->params->get('item_show_page_heading', 0)) : ?>
		<div class="page-header">
			<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
		</div>
	<?php endif; ?>
<?php echo J2Store::modules()->loadposition('j2store-single-product-top'); ?>
	<?php if($this->params->get('item_show_back_to',0) && isset($this->back_link) && !empty($this->back_link)):?>
		<div class="j2store-view-back-button">
			<a href="<?php echo $this->back_link; ?>" class="j2store-product-back-btn btn btn-small btn-info">
				<i class="fa fa-chevron-left"> </i> <?php echo JText::_('J2STORE_PRODUCT_BACK_TO').' '.$this->back_link_title; ?>
			</a>
		</div>
	<?php endif;?>
	<?php echo $this->loadTemplate($this->product->product_type); ?>
	<?php echo J2Store::plugin ()->eventWithHtml ( 'AfterProductDisplay', array($this->product,$this) )?>
	<?php echo J2Store::modules()->loadposition('j2store-single-product-bottom'); ?>
  	<script>
	    (function ($) {
			$(document).on('change', '#j2store-addtocart-form-<?php echo $this->product->j2store_product_id;?> input[name="product_qty"]', function () {
				qtyBasedTextBox('<?php echo $this->product->j2store_product_id;?>');
			});

			function qtyBasedTextBox(product_id) {
				(function ($) {
					var qty = $('#j2store-addtocart-form-' + product_id + ' input[name="product_qty"]').val();
					// Hide the options
					$('#j2store-addtocart-form-'+product_id+' [class*="showOption"]').hide();

					// Show the options
					for (var i = 1; i <= qty; i++) {
						$('#j2store-addtocart-form-'+product_id+' .showOption' + i).show();
					}
				})(jQuery);

			}

			qtyBasedTextBox('<?php echo $this->product->j2store_product_id;?>');
	    })(jQuery);

	</script>
</div>

