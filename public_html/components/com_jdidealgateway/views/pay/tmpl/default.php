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

JHtml::_('behavior.formvalidation');
$jinput = JFactory::getApplication()->input;
$silent = $jinput->getBool('silent', false);
?>
<div class="clear"></div>
<?php if ($silent) : ?>
	<div style="display: none">
<?php endif; ?>
<form name="adminForm" id="adminForm" method="post" class="form-validate" action="<?php echo JRoute::_('index.php?option=com_jdidealgateway&task=pay.sendmoney'); ?>">
	<fieldset>
		<legend><?php echo JText::_('COM_JDIDEALGATEWAY_PAYMENT_FORM'); ?></legend>
			<div>
				<?php echo $this->form->getLabel('user_email'); ?>
				<?php echo $this->form->getInput('user_email'); ?>
			</div>
			<div>
				<?php echo $this->form->getLabel('amount'); ?>
				<?php echo $this->form->getInput('amount'); ?>
			</div>
			<div>
				<?php echo $this->form->getLabel('remark'); ?>
				<?php echo $this->form->getInput('remark'); ?>
			</div>

			<?php if ($jinput->getString('order_number', false)): ?>
				<div>
					<?php echo $this->form->getLabel('order_number'); ?>
					<?php
						$this->form->setValue('order_number', '', $jinput->getString('order_number', ''));
						echo $this->form->getInput('order_number');
					?>
				</div>
			<?php endif; ?>
		<?php if (!$silent) : ?>
			<div id="paybox_button" class="submit">
				<div>
					<input type="submit" class="validate" id="submit" name="submit" value="<?php echo JText::_('COM_JDIDEALGATEWAY_SEND_MONEY'); ?>" />
				</div>
			</div>
		<?php else : ?>
			<script type="text/javascript">
				document.adminForm.submit();
			</script>
		<?php endif; ?>
	</fieldset>
</form>
<?php if ($silent) : ?>
	</div>
	<?php echo JText::_('COM_JDIDEALGATEWAY_REDIRECT_5_SECS'); ?>
<?php endif; ?>
