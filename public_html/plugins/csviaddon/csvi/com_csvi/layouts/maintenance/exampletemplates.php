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

$csvihelper = new CsviHelperCsvi;
$components = $csvihelper->getComponents();
?>
<div>
	<?php echo JText::_('COM_CSVI_EXAMPLETEMPLATES_DESC');?>
</div>
<div class="control-group">
	<label for="template_name" class="control-label ">
		<?php echo JText::_('COM_CSVI_SELECT_ADDONS_INSTALL_LABEL'); ?>
	</label>
	<div class="controls">
		<input
				type="checkbox"
				onclick="Joomla.checkAll(this)"
				checked="checked"
				title="<?php echo JText::_('COM_CSVI_CHECK_ALL_FIELDS'); ?>"
				value=""
				name="checkall-toggle"
		/>
		<?php echo JText::_('COM_CSVI_CHECK_ALL_FIELDS'); ?>
		<span class="help-block" style="display: none;"><?php echo JText::_('COM_CSVI_SELECT_ADDONS_INSTALL_DESC'); ?></span>
	</div>
	<div class="controls">
		<?php
			foreach ($components as $key => $component)
			{
				if ($key > 0)
				{
					if (empty($component->value))
					{
					?>
					<div class="span5"><label><?php echo $component->text; ?></label>
					<?php
					}
					else
					{
						?>
						<div>
							<input type="checkbox" checked="checked" name="form[addons][]" id="cb<?php echo $key; ?>"
									value="<?php echo $component->value; ?>" />
							<?php echo $component->text; ?>
						</div>
						<?php
					}
				}
			}
			?>
	</div>
</div>

<div class="control-group">
	<label for="debug_log" class="control-label ">
		<?php echo JText::_('COM_CSVI_ENABLE_DEBUG_LOG_LABEL'); ?>
	</label>
	<div class="controls">
		<?php
		$options = array();
		$options[] = JHtml::_('select.option', 1, JText::_('JYES'));
		$options[] = JHtml::_('select.option', 0, JText::_('JNO'));
		echo JHtml::_('select.genericlist', $options, 'form[enablelog]', 'class="input-small"', 'value', 'text', 0);
		?>
		<span class="help-block" style="display: none;"><?php echo JText::_('COM_CSVI_ENABLE_DEBUG_LOG_DESC'); ?></span>
	</div>
</div>
