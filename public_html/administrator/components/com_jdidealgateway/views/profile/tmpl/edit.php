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
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen');
?>
<form action="<?php echo JRoute::_('index.php?option=com_jdidealgateway&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm" class="form-validate">
	<?php
	$url = JUri::getInstance();

	if ($url->getHost() === 'localhost' && in_array($this->activeProvider, array('mollie'), true))
	{
		JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JDIDEALGATEWAY_MUST_BE_ONLINE', ucfirst($this->activeProvider)), 'error');
	}

	if (in_array($this->activeProvider, array('ing-lite', 'abn-lite'), true))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('COM_JDIDEALGATEWAY_NO_FEEDBACK'), 'warning');
	}
	?>
	<div class="form-inline form-inline-header">
		<?php
		echo $this->form->renderField('psp');
		echo $this->form->renderField('name');
		echo $this->form->renderField('alias');
		?>
	</div>
	<hr />
	<div class="form-horizontal">
		<?php
		// Only show the form if we were able to load it
		if ($this->pspForm)
		{
			echo $this->loadTemplate($this->activeProvider);
		}
		?>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('id'); ?>
	<?php echo JHtml::_('form.token'); ?>
</form>
