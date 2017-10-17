<?php
/**
 * @version    SVN:
 * @package    Com_Tjvendors
 * @author     Techjoomla  <contact@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
	// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'media/com_tjvendors/css/form.css');
?>
<script type="text/javascript">

	Joomla.submitbutton = function (task)
	{
		if (task == 'payout.cancel')
		{
			Joomla.submitform(task, document.getElementById('payout-form'));
		}
		else
		{
			Joomla.submitform(task, document.getElementById('payout-form'));
		}
	}
	function confirmationMsg()
	{
		var txt;
		var r = confirm("<?php echo JText::_('CONFIRM_MESSAGE_YES_NO'); ?>");

		if (r == true)
		{
			// Do nothing
		}
		else
		{
			return false;
		}
	}

</script>
<form method="post" enctype="multipart/form-data" name="adminForm" id="payout-form" class="form-validate">
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_TJVENDORS_TITLE_PAYOUT', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">
					<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
					<?php echo $this->form->renderField('vendor_title'); ?>
					<?php echo $this->form->renderField('currency'); ?>
					<?php if($this->bulkPayoutStatus!=0)
					{
						echo $this->form->renderField('bulk_total');
					}
					else
					{
						echo $this->form->renderField('total');
					}
					?>
					<?php echo $this->form->renderField('reference_order_id'); ?>
					<?php echo $this->form->renderField('status'); ?>
					<button class="validate btn btn-primary" id="pay-confirmation" onclick="return confirmationMsg()" >PAY</button>
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		<input type="hidden" name="task" value="payout.save"/>
		<input type="hidden" name="client" value="<?php echo $this->input->get('client', '', 'STRING');?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
