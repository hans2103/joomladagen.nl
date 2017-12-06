<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('script', 'media/j2store/js/j2store.js', false, false);

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/message.php';
?>
<script type="text/javascript">
function insertText(value) {
	(function($){
		 jInsertEditorText(value,'body');
	    })(j2store.jQuery);
}
</script>
<div class="j2store-invoicetemplate">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','invoicetemplates');?>
		<?php echo J2Html::hidden('id',$this->item->j2store_invoicetemplate_id,array('id'=>'id'));?>
		<?php echo J2Html::hidden('j2store_invoicetemplate_id',$this->item->j2store_invoicetemplate_id);?>
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
		                echo JHtml::_('bootstrap.startTabSet', 'invoicetemplate', array('active' => $attr->name));
		            }
		            echo JHtml::_('bootstrap.addTab', 'invoicetemplate', $attr->name, JText::_($attr->label, true));
		            ?>
		            <div class="row-fluid">
		                <div class="span12">
	                    <?php
		                    $layout = '';
		                    $style = '';
		                    $fields = $this->form->getFieldset($attr->name);
		                    foreach ($fields as $key => $field):
		                    ?>
								<?php if($key=='body'):?>
								<div class="row-fluid">
									<div class="span9">
										<div class="control-group <?php echo $layout; ?>" <?php echo $style; ?>>
				                            <div class="control-label"><?php echo $field->label; ?></div>
				                            	<div><?php echo $field->input; ?></div>
				                        </div>
				                         <?php echo $this->loadTemplate('tags');?>
									</div>
									<div class="span3">
									<label class="control-label"><?php echo JText::_('J2STORE_TEMPLATE_INSERT_MESSAGE_TAGS');?></label>
									 <div class="input-append">
										<a class="btn btn-success" onclick="insertText(jQuery('#message_tag').attr('value'));">
											<i class="icon-arrow-left"></i>
										</a>
											<select id="message_tag" size="40">
												<?php $message_tags = J2StoreMessage::getMessageTags();?>
													<?php if(isset($message_tags) && !empty($message_tags)):?>
														<?php foreach($message_tags as $key => $option_group):?>
												  			<optgroup label="<?php echo JText::_('J2STORE_'.JString::strtoupper($key));?>">
												  				<?php if(isset($option_group) && !empty($option_group)):?>
												  				<?php foreach($option_group as $key => $text):?>
												  				<option value="<?php echo $key;?>"><?php echo $text?></option>
												  				<?php endforeach;?>
												  				<?php endif;?>
													  		</optgroup>
												  		<?php endforeach;?>
												  <?php endif;?>
												</select>
										</div>
									</div>
								</div>

		                        <?php else:?>
		                         <div class="control-group <?php echo $layout; ?>" <?php echo $style; ?>>
		                            <div class="control-label"><?php echo $field->label; ?></div>
		                            	<div class="controls"><?php echo $field->input; ?>
		                            </div>
		                        </div>
		                        <?php endif;?>
		                    <?php endforeach; ?>
		                </div>
		            </div>

		            <?php
			            echo JHtml::_('bootstrap.endTab');
			            $tab_count++;
	       			 }
	        			?>
	 </form>
</div>