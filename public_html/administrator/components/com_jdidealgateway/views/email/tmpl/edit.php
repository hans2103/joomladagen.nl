<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;
JHtml::_('formbehavior.chosen');

?>
<form action="<?php echo 'index.php?option=com_jdidealgateway&layout=edit&id=' . (int) $this->item->id; ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="span9">
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('trigger'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('trigger'); ?></div>
		</div>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('subject'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('subject'); ?></div>
		</div>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('body'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('body'); ?></div>
		</div>
	</div>
	<div class="span3">
		<fieldset>
			<ul>
				<li class="tag_title"><?php echo JText::_('COM_JDIDEALGATEWAY_ADMIN_STATUS_MISMATCH'); ?></li>
				<li>{ORDERNR}</li>
				<li>{ORDERID}</li>
				<li>{EXPECTED_STATUS}</li>
				<li>{FOUND_STATUS}</li>
				<li>{STATUS}</li>
				<li>{HTTP_HOST}</li>
				<li>{QUERY_STRING}</li>
				<li>{REMOTE_ADDRESS}</li>
				<li>{SCRIPT_FILENAME}</li>
				<li>{REQUEST_TIME}</li>
				<li class="tag_title"><?php echo JText::_('COM_JDIDEALGATEWAY_ADMIN_PAYMENT_FAILED'); ?></li>
				<li>{ORDERNR}</li>
				<li>{ORDERID}</li>
				<li>{BEDRAG}</li>
				<li>{USER_EMAIL}</li>
				<li class="tag_title"><?php echo JText::_('COM_JDIDEALGATEWAY_ADMIN_ORDER_PAYMENT'); ?></li>
				<li>{ORDERNR}</li>
				<li>{ORDERID}</li>
				<li>{BEDRAG}</li>
				<li>{STATUS}</li>
				<li>{STATUS_NAME}</li>
				<li>{TRANSACTION_ID}</li>
				<li>{USER_EMAIL}</li>
				<li>{CONSUMERACCOUNT}</li>
				<li>{CONSUMERIBAN}</li>
				<li>{CONSUMERBIC}</li>
				<li>{CONSUMERNAME}</li>
				<li>{CONSUMERCITY}</li>
				<li>{CARD}</li>
				<li class="tag_title"><?php echo JText::_('COM_JDIDEALGATEWAY_ADMIN_INFORM_EMAIL'); ?></li>
				<li>{ORDERNR}</li>
				<li>{ORDERID}</li>
				<li>{BEDRAG}</li>
				<li class="tag_title"><?php echo JText::_('COM_JDIDEALGATEWAY_CUSTOMER_CHANGE_STATUS'); ?></li>
				<li>{ORDERNR}</li>
				<li>{ORDERID}</li>
				<li>{STATUS_NAME}</li>
				<li>{ORDER_LINK}</li>
			</ul>
		</fieldset>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
