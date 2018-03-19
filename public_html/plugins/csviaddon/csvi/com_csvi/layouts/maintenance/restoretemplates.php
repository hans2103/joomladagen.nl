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

?>
<div class="control-group">
	<label for="template_name" class="control-label">
		<?php echo JText::_('COM_CSVI_CHOOSE_RESTORE_FILE_LABEL'); ?>
	</label>
	<div class="controls">
		<input type="file" name="form[restore_file]" id="file" class="span5"/>
		<span class="help-block"
		      style="display: none;"><?php echo JText::_('COM_CSVI_CHOOSE_RESTORE_FILE_DESC'); ?></span>
	</div>
</div>
<div class="control-group">
	<label for="template_name" class="control-label">
		<?php echo JText::_('COM_CSVI_USE_EXISTING_RULES_LABEL'); ?>
	</label>
	<div class="controls">
		<fieldset id="jform_useexistingrules" class="btn-group btn-group-yesno">
			<?php
				$options = array();
				$options[] = JHtml::_('select.option', 1, JText::_('JYES'));
				$options[] = JHtml::_('select.option', 0, JText::_('JNO'));
				echo JHtml::_('select.genericlist', $options, 'form[useexistingrules]', 'class="input-small"', 'value', 'text', 1);
			?>
			<span class="help-block"
				style="display: none;"><?php echo JText::_('COM_CSVI_USE_EXISTING_RULES_DESC'); ?></span>
		</fieldset>
	</div>
</div>
