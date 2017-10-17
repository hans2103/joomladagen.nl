<?php
/**
 * @version     1.0.0
 * @package     com_tjfields
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      TechJoomla <extensions@techjoomla.com> - www.techjoomla.com
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
$input = JFactory::getApplication()->input;

// Import helper for declaring language constant
JLoader::import('TjfieldsHelper', JUri::root().'administrator/components/com_tjfields/helpers/tjfields.php');
// Call helper function
TjfieldsHelper::getLanguageConstant();
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){

    });

    Joomla.submitbutton = function(task)
    {
        if(task == 'group.cancel'){
            Joomla.submitform(task, document.getElementById('group-form'));
        }
        else{

            if (task != 'group.cancel' && document.formvalidator.isValid(document.id('group-form')))
            {
				if (techjoomla.jQuery('#jform_name').val().trim() == '')
				{
					alert(Joomla.JText._('COM_TJFIELDS_LABEL_WHITESPACES_NOT_ALLOWED'));
					techjoomla.jQuery('#jform_name').val('');
					techjoomla.jQuery('#jform_name').focus();
					return false;
				}

                Joomla.submitform(task, document.getElementById('group-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<div class="techjoomla-bootstrap">
	<form action="<?php echo JRoute::_('index.php?option=com_tjfields&layout=edit&id=' . (int) $this->item->id).' &client='.$input->get('client','','STRING'); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="group-form" class="form-validate">
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">

								<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
				</div>

					<input type="hidden" name="jform[client]" value="<?php echo $input->get('client','','STRING'); ?>" />


				</fieldset>
			</div>



			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>

		</div>
	</form>
</div>
