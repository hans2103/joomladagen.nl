<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

JHtml::_('behavior.framework', true);
JHtml::_('behavior.multiselect');
JHtml::_('behavior.modal');
JHtml::_('script', 'media/j2store/js/j2store.js', false, false);
JHtml::_('formbehavior.chosen', '.chosenselect');
?>
<script type="text/javascript">

	/**
	  * when product title of the
	  * Method
	  */
	function jSelectProduct(product_id ,product_name ,field_id){
		var form = jQuery("#adminForm");
		var html ='';
		if(form.find('#'+field_id+ '  #product-row-'+product_id).length == 0){
			html +='<tr id="product-row-'+product_id +'"><td><input type="hidden" name="products['+product_id+']" value='+product_id+' />'+product_name +'</td><td><button class="btn btn-danger" onclick="jQuery(this).closest(\'tr\').remove();"><i class="icon icon-trash"></button></td></tr>';
			form.find("#"+field_id).append(html);
			alert('<?php echo JText::_('J2STORE_PRODUCT_ADDED');?>');
		}else{
			alert('<?php echo JText::_('J2STORE_PRODUCT_ADDED_ALREADY');?>');
		}
	}
</script>

<div class="j2store-configuration">
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
<?php echo J2Html::hidden('option','com_j2store');?>
<?php echo J2Html::hidden('view','coupons');?>
<?php echo J2Html::hidden('j2store_coupon_id',$this->item->j2store_coupon_id);?>
<?php echo J2Html::hidden('task','',array('id'=>'task'));?>
<?php echo JHtml::_('form.token'); ?>
<?php
        $fieldsets = $this->form->getFieldsets();
        $shortcode = $this->form->getValue('text');
        $tab_count = 0;

        foreach ($fieldsets as $key => $attr)
        {

	            if ( $tab_count == 0 )
	            {
	                echo JHtml::_('bootstrap.startTabSet', 'configuration', array('active' => $attr->name));
	            }
	            echo JHtml::_('bootstrap.addTab', 'configuration', $attr->name, JText::_($attr->label, true));
	            ?>
	         <?php  if(J2Store::isPro() != 1 && isset($attr->ispro) && $attr->ispro ==1 ) : ?>
				<?php echo J2Html::pro(); ?>
			<?php else: ?>
	            <div class="row-fluid">
	                <div class="span12">
	                    <?php
	                    $layout = '';
	                    $style = '';
	                    $fields = $this->form->getFieldset($attr->name);
	                    foreach ($fields as $key => $field)
	                    {

	                    	$pro = $field->getAttribute('pro');
	                    ?>
	                        <div class="control-group <?php echo $layout; ?>" <?php echo $style; ?>>
	                            <div class="control-label"><?php echo $field->label; ?></div>

	                            <?php if(J2Store::isPro() != 1 && $pro ==1 ): ?>
	                            	<?php echo J2Html::pro(); ?>
	                            <?php else: ?>
	                           <div class="controls"><?php echo $field->input; ?>
	                            	<br />
	                            	<small class="muted"><?php echo JText::_($field->description); ?></small>
	                            <?php endif; ?>


	                            <?php if($key == 'product_links'):?>
	                            <br/>
	                            <div class="row-fluid">
	                            	<div class="span6">
	                                	<div class="table-responsive">
		                            		<table class="table table-striped table-condensed" id="jform_product_list">
			          		                	<tbody>
			            		                	<?php if(!empty($this->item->products)):?>
			            		                	<tr>
			            		                		<td colspan="3">
			            		                			<a class="btn btn-danger" href="javascript:void(0);"
			            		                				onclick="jQuery('.j2store-product-list-tr').remove();">
			            		                				<?php echo JText::_('J2STORE_DELETE_ALL_PRODUCTS');?>

			            		                			<i class="icon icon-trash"></i></a>

			            		                		</td>

			            		                	</tr>
			    		                        	<?php $product_ids = explode(',',$this->item->products);
			    		                        		$i =1;
			    		                        	?>
													<?php foreach($product_ids as  $pid):?>
													<?php $product = F0FModel::getTmpInstance('Products','J2StoreModel')->getItem($pid);?>
													<tr class="j2store-product-list-tr" id="product-row-<?php echo $pid?>">
														<td><input type="hidden" name="products[<?php echo $pid;?>]" value='<?php echo $pid;?>' /><?php echo $product->product_name;?></td>
														<td><a class="btn btn-danger" href="javascript:void(0);" onclick="jQuery(this).closest('tr').remove();"><i class="icon icon-trash"></i></a></td>
													</tr>
													<?php
													$i++;
													endforeach;?>
													</tbody>
													<?php endif;?>
												</table>
											</div>
	                            		</div>
		                            	<div class="span6">
		                            		<div class="alert alert-success">
		                            			<?php echo JText::_('J2STORE_COUPON_ADDING_PRODUCT_HELP');?>
		                            		</div>
		                            	</div>
	            	                </div>
							<?php endif;?>
	                            </div>
	                        </div>
	                    <?php }   ?>
	                </div>
	            </div>
	            <?php endif; ?>
	            <?php
	            echo JHtml::_('bootstrap.endTab');
	            $tab_count++;

        }
        ?>
 </form>
</div>

<script type="text/javascript">
/* Override joomla.javascript, as form-validation not work with ToolBar */

 Joomla.submitbutton =function(pressbutton){
    if (pressbutton == 'cancel') {
        submitform(pressbutton);
    }else if(pressbutton =='apply' || pressbutton =='save' ){

        //to add selected attribute
    	jQuery('#adminForm').find('.selected').find('option').each(function(i,e){
        	jQuery(e).prop('selected',true);
    	});
        submitform(pressbutton);
    }else if(pressbutton == 'copy'){
		submitform(pressbutton);
	}
}

//assign default value type
var value_type = '<?php echo $this->item->value_type?>';
/** when coupon value type changed will toggle  **/
jQuery('select[name=value_type]').on('change',function(){
	 value_type = jQuery('select[name=value_type] option:selected').val();
		if(jQuery('#max_quantity').length > 0){
			jQuery('#max_quantity').closest('.control-group').hide();
		   		if(value_type == 'percentage_product' || value_type == 'fixed_product' ){
		   		jQuery('#max_quantity').closest('.control-group').show();
		  	}
		}
});
jQuery('select[name=value_type]').trigger('change');
</script>