<?php
/**
 * @package     CSVI
 * @subpackage  Maintenance
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

// Load the list of templates
/** @var CsviModelTemplates $templateModel */
$templateModel = JModelLegacy::getInstance('Templates', 'CsviModel');
$templates     = $templateModel->getTemplates();

// Remove unneeded templates
foreach ($templates as $key => $template)
{
	if (!isset($template->action) || $template->action == 'import')
	{
		unset($templates[$key]);
	}
}

// Add a select option
$select = JHtml::_('select.option', '', JText::_('COM_CSVI_SELECT_TEMPLATE'));
array_unshift($templates, $select);
?>
<span class="help-block"><?php echo JText::_('COM_CSVI_REFRESHSEFURLS_DESC'); ?></span>
<div class="control-group">
	<label for="template_name" class="control-label ">
		<?php echo JText::_('COM_CSVI_REFRESHSEFURLS_TEMPLATE_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo JHtml::_('select.genericlist', $templates, 'form[template]', 'class="input-xxlarge"'); ?>
		<span class="help-block" style="display: none;"><?php echo JText::_('COM_CSVI_REFRESHSEFURLS_TEMPLATE_DESC'); ?></span>
	</div>
</div>
<div class="control-group">
	<label for="template_name" class="control-label ">
		<?php echo JText::_('COM_CSVI_REFRESHSEFURLS_EMPTYTABLE_LABEL'); ?>
	</label>
	<div class="controls">
		<?php
			$options = array();
			$options[] = JHtml::_('select.option', 1, JText::_('JYES'));
			$options[] = JHtml::_('select.option', 0, JText::_('JNO'));
			echo JHtml::_('select.genericlist', $options, 'form[emptytable]', 'class="input-small"', 'value', 'text', 1);
		?>
		<span class="help-block" style="display: none;"><?php echo JText::_('COM_CSVI_REFRESHSEFURLS_EMPTYTABLE_DESC'); ?></span>
	</div>
</div>
