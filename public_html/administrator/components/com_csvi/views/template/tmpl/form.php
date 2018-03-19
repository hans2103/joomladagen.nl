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

// Load some needed behaviors
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen');

?>
<div id="editTemplate">
	<form action="<?php echo JRoute::_('index.php?option=com_csvi&view=template&id=' . $this->item->csvi_template_id); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
		<?php
		// Check if we have a new template, if so start the wizard
		if (null === $this->item->csvi_template_id || 0 === $this->item->csvi_template_id || $this->step > 0)
		{
			echo $this->loadTemplate('wizard');
		}
		else
		{
			echo $this->loadTemplate('edit');
		}
		?>
		<input type="hidden" name="task" value="save" />
		<input type="hidden" id="csvi_template_id" name="csvi_template_id" value="<?php echo $this->item->csvi_template_id; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
