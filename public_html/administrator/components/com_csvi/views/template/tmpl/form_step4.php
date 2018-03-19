<?php
/**
 * @package     CSVI
 * @subpackage  Template
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

if ($this->action === 'export')
{
	if (isset($this->forms->form))
	{
		echo $this->forms->form;
	}

	?>
	<div class="pull-left">
		<?php echo JHtml::_(
			'link',
			'index.php?option=com_csvi&view=templatefields&csvi_template_id=' . $this->item->csvi_template_id,
			JText::_('COM_CSVI_EDIT_TEMPLATEFIELDS'),
			'class="btn btn-primary" target="_new"');
		?>
	</div>
	<?php
}
else
{
	if (!$this->item->options->get('use_column_headers'))
	{
		?>
		<div class="step_info">
			<?php echo JText::_('COM_CSVI_WIZARD_IMPORT_FIELDS_NEEDED'); ?>
		</div>

		<div class="pull-left">
			<?php echo JHtml::_(
				'link',
				'index.php?option=com_csvi&view=templatefields&csvi_template_id=' . $this->item->csvi_template_id,
				JText::_('COM_CSVI_EDIT_TEMPLATEFIELDS'),
				'class="btn btn-primary" target="_new"');
		?>
		</div>
	<?php
	}
	else
	{
		echo JText::_('COM_CSVI_WIZARD_IMPORT_FIELDS_NOT_NEEDED');
	}
}
