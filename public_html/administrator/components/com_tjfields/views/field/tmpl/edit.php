<?php
/**
 * @version     1.0.0
 * @package     com_tjfields
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      TechJoomla <extensions@techjoomla.com> - http://www.techjoomla.com
 */
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

if(JVERSION >= '3.0')
{
	JHtml::_('formbehavior.chosen', 'select');
}
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_tjfields/assets/css/tjfields.css');
$document->addScript(JUri::root() . 'administrator/components/com_tjfields/assets/js/field.js');
$input = JFactory::getApplication()->input;

		$full_client = $input->get('client','','STRING');
		$full_client =  explode('.',$full_client);

		$client = $full_client[0];
		$client_type = $full_client[1];

// Import helper for declaring language constant
JLoader::import('TjfieldsHelper', JUri::root().'administrator/components/com_tjfields/helpers/tjfields.php');
// Call helper function
TjfieldsHelper::getLanguageConstant();

?>
<script type="text/javascript">

	techjoomla.jQuery( document ).ready(function(){
			var field_type=techjoomla.jQuery('#jform_type').val();
			show_option_div(field_type);
			//if edit ..make name field readonly
			var field_id=techjoomla.jQuery('#jform_id').val();
			if(field_id!=0)
			{
				techjoomla.jQuery('#jform_name').attr('readonly',true);
			}

		});

	Joomla.submitbutton = function(task)
	{
		// Remove disable attribute from category select so that the selected category can be saved
		if (task == 'field.apply' || task == 'field.save' || task == 'field.newsave' || task == 'field.save2copy')
		{
			techjoomla.jQuery('#jformcategory').attr("disabled", false);
		}

		whitespaces_not_llowed = Joomla.JText._('COM_TJFIELDS_LABEL_WHITESPACES_NOT_ALLOWED');
		//alert(task);
		if(task == 'field.cancel'){
			Joomla.submitform(task, document.getElementById('field-form'));
		}
		else{

			if (task != 'field.cancel' && document.formvalidator.isValid(document.id('field-form'))) {

				var isrequired = techjoomla.jQuery('input[name="jform[required]"]:checked', '#field-form').val();
				var isreadonly = techjoomla.jQuery('input[name="jform[readonly]"]:checked', '#field-form').val();
				var field_type = techjoomla.jQuery('#jform_type').val();

				switch(field_type)
				{
					case 'multi_select':
					case 'single_select':
					case 'checkbox':
					case 'radio':
						if(isrequired == 1)
						{
							if(techjoomla.jQuery('.tjfields_optionname').val().trim() == '' && techjoomla.jQuery('.tjfields_optionvalue').val().trim() == '')
							{
								techjoomla.jQuery('.tjfields_optionname').val('');
								techjoomla.jQuery('.tjfields_optionname').attr('required', 'required');
								techjoomla.jQuery('.tjfields_optionvalue').attr('required', 'required');
								techjoomla.jQuery('.tjfields_optionname').focus();
								return false;
							}
						}
						break;
					case 'text':
					case 'textarea':
					case 'calendar':
					case 'email_field':
						if ((isrequired == 1 && isreadonly == 1) || isreadonly == 1)
						{
							if (techjoomla.jQuery('#jform_default_value').val().trim() == '')
							{
								techjoomla.jQuery('#jform_default_value').attr('required', 'required');
								techjoomla.jQuery('#jform_default_value').focus();
								return false;
							}
						}
						break;
				}

				if (techjoomla.jQuery('#jform_label').val().trim() == '')
				{
					alert(whitespaces_not_llowed);
					techjoomla.jQuery('#jform_label').val('');
					techjoomla.jQuery('#jform_label').focus();
					return false;
				}

				if (techjoomla.jQuery('#jform_name').val().trim() == '')
				{
					alert(whitespaces_not_llowed);
					techjoomla.jQuery('#jform_name').val('');
					techjoomla.jQuery('#jform_name').focus();
					return false;
				}

				Joomla.submitform(task, document.getElementById('field-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('COM_TJFIELDS_INVALID_FORM')); ?>');
			}
		}
	}

	function show_option_div(field_value)
	{
			techjoomla.jQuery('#jform_filterable').parent().parent().hide();

			switch (field_value)
			{
				case	"radio":
							showOptions();
							techjoomla.jQuery('#jform_filterable').parent().parent().show();
							break;
				case 	"single_select":
							showOptions();
							techjoomla.jQuery('#jform_filterable').parent().parent().show();
							break;
				case 	"multi_select":
							showOptions();
							techjoomla.jQuery('#jform_filterable').parent().parent().show();
							break;
				case	"checkbox":
							showOptions();
							techjoomla.jQuery('#jform_filterable').parent().parent().show();
							break;
				case	"text":
				case	"textarea":
				case	"email_field":
							techjoomla.jQuery('#option_div').hide();
							techjoomla.jQuery('#option_min_char').show();
							techjoomla.jQuery('#option_max_char').show();
							techjoomla.jQuery('#div_placeholder').show();

							techjoomla.jQuery('#date_format').hide();
							techjoomla.jQuery('#default_value_text').show();

							if(field_value == "textarea")
							{
								techjoomla.jQuery('#textarea_rows').show();
								techjoomla.jQuery('#textarea_cols').show();
								//techjoomla.jQuery('#textarea_cols').addClass('required');
								techjoomla.jQuery('.textarea_inputs').children().attr('required','required');
							}
							else
							{
								techjoomla.jQuery('.textarea_inputs').children().removeAttr('required');
								techjoomla.jQuery('#textarea_rows').hide();
								techjoomla.jQuery('#textarea_cols').hide();
							}

							break;
				case	"calendar":
				case	"editor":
				case	"file":
				case	"user":
				case	"hidden":
							techjoomla.jQuery('#option_div').hide();
							techjoomla.jQuery('#option_min_char').hide();
							techjoomla.jQuery('#option_max_char').hide();
							techjoomla.jQuery('#div_placeholder').hide();
							techjoomla.jQuery('#date_format').hide();
							techjoomla.jQuery('#default_value_text').hide();
							techjoomla.jQuery('.textarea_inputs').children().removeAttr('required');
							techjoomla.jQuery('#textarea_rows').hide();
							techjoomla.jQuery('#textarea_cols').hide();

							if(field_value == "calendar")
							{
								techjoomla.jQuery('#date_format').show();
								techjoomla.jQuery('#default_value_text').show();
							}
							else if(field_value == "hidden")
							{
								techjoomla.jQuery('#default_value_text').show();
							}

							break;

			}


	}

	function showOptions()
	{
		techjoomla.jQuery('#option_div').show();
		techjoomla.jQuery('#option_min_char').hide();
		techjoomla.jQuery('#option_max_char').hide();
		techjoomla.jQuery('#date_format').hide();
		techjoomla.jQuery('#default_value_text').hide();
		techjoomla.jQuery('.textarea_inputs').children().removeAttr('required');
		techjoomla.jQuery('#textarea_rows').hide();
		techjoomla.jQuery('#textarea_cols').hide();
		techjoomla.jQuery('#div_placeholder').hide();
	}

</script>

<div class="techjoomla-bootstrap">
	<form action="<?php echo JRoute::_('index.php?option=com_tjfields&layout=edit&id='.(int) $this->item->id).'&client='.$input->get('client','','STRING'); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="field-form" class="form-validate">
		<div class="techjoomla-bootstrap">
		<div class="row-fluid">

			<div class="container1">
				<div class="span6 ">
				<fieldset class="adminform form-horizontal">
				<legend>
					<?php
						echo JText::_('COM_TJFIELDS_BASIC_FIELDS_VALUES');
					?>
				</legend>

							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('label'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('label'); ?>
									<span class="alert alert-info alert-help-inline span9 alert_no_margin">
										<?php echo JText::_('COM_TJFIELDS_LABEL_LANG_CONSTRAINT_ONE'); ?>
										<span class="alert-text-change">
											<?php echo JText::sprintf('COM_TJFIELDS_LABEL_LANG_CONSTRAINT_TWO', $client); ?>
										</span>
									</span>
								</div>

							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('type'); ?></div>
							</div>
							<div class="control-group displaynone" id="option_div" >
								<div class="control-label"><?php echo $this->form->getLabel('fieldoption'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('fieldoption'); ?></div>
							</div>
							<div class="control-group displaynone" id="date_format" >
								<div class="control-label"><?php echo $this->form->getLabel('format'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('format'); ?></div>
							</div>
							<div class="control-group displaynone" id="option_min_char">
								<div class="control-label"><?php echo $this->form->getLabel('min'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('min'); ?></div>
							</div>
							<div class="control-group displaynone" id="option_max_char">
								<div class="control-label"><?php echo $this->form->getLabel('max'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('max'); ?></div>
							</div>
							<div class="control-group displaynone" id="textarea_rows">
								<div class="control-label"><?php echo $this->form->getLabel('rows'); ?></div>
								<div class="controls textarea_inputs"><?php echo $this->form->getInput('rows'); ?></div>
							</div>
							<div class="control-group displaynone" id="textarea_cols">
								<div class="control-label"><?php echo $this->form->getLabel('cols'); ?></div>
								<div class="controls textarea_inputs"><?php echo $this->form->getInput('cols'); ?></div>
							</div>
							<div class="control-group displaynone" id="default_value_text">
								<div class="control-label"><?php echo $this->form->getLabel('default_value'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('default_value'); ?></div>
							</div>

					</fieldset>
					<input type="hidden" name="jform[client]" value="<?php echo $input->get('client','','STRING'); ?>" />
				</div>


				<div class="span5 form-horizontal">
				<fieldset class="adminform form-horizontal">
				<legend>
					<?php
						echo JText::_('COM_TJFIELDS_EXTRA_FIELDS_VALUES');
					?>
				</legend>

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('group_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('group_id'); ?></div>
					</div>

					<div class="control-group" >
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('required'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('required'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('readonly'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('readonly'); ?></div>
					</div>
					<div class="control-group" id="div_placeholder">
						<div class="control-label"><?php echo $this->form->getLabel('placeholder'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('placeholder'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('category') ; ?></div>
						<div class="controls">
							<?php
							echo $this->form->getInput('category');?>
							<div style="clear:both" ></div>
							<span class="alert alert-warning alert-help-inline span9 alert_no_margin">
								<?php echo JText::_('COM_TJFIELDS_CATEGORY_NOTE'); ?>
							</span>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('filterable'); ?></div>
						<div class="controls">
							<?php echo $this->form->getInput('filterable'); ?>
							<div style="clear:both" ></div>
							<span class="alert alert-info alert-help-inline span9 alert_no_margin">
								<?php echo JText::_('COM_TJFIELDS_FILTERABLE_NOTE'); ?>
							</span>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('js_function'); ?></div>
						<div class="controls">
							<?php echo $this->form->getInput('js_function'); ?>

						</div>

					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('validation_class'); ?></div>
						<div class="controls">
							<?php echo $this->form->getInput('validation_class'); ?>
							<div style="clear:both" ></div>
							<span class="alert alert-info alert-help-inline span9 alert_no_margin">
								<?php echo JText::_('COM_TJFIELDS_VALIDATION_CLASS_NOTE'); ?>
							</span>
						</div>
					</div>
					</fieldset>
				</div>
			</div>
			<!--</fieldset>-->
		</div>

		<input type="hidden" name="client_type" value="<?php echo $client_type;?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>

		</div><!--row fuild ends-->
		</div><!--techjoomla ends-->
	</form>
</div>
