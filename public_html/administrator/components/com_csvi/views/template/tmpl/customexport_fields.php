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

?>
<?php if ($this->action == 'export') :?>
	<?php $form = $this->forms->customexport_fields; ?>
	<div class="control-group">
		<label class="control-label <?php echo $form->getField('groupbyfields', 'jform')->labelClass; ?>" for="<?php echo $form->getField('groupbyfields', 'jform')->id; ?>">
			<?php echo JText::_('COM_CSVI_' . $form->getField('groupbyfields', 'jform')->id . '_LABEL'); ?>
		</label>
		<div class="controls">
			<?php echo $form->getInput('groupbyfields', 'jform'); ?>
			<span class="help-block">
				<?php echo JText::_('COM_CSVI_' . $form->getField('groupbyfields', 'jform')->id . '_DESC'); ?>
			</span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label <?php echo $form->getField('sortfields', 'jform')->labelClass; ?>" for="<?php echo $form->getField('sortfields', 'jform')->id; ?>">
			<?php echo JText::_('COM_CSVI_' . $form->getField('sortfields', 'jform')->id . '_LABEL'); ?>
		</label>
		<div class="controls">
			<?php echo $form->getInput('sortfields', 'jform'); ?>
			<span class="help-block">
				<?php echo JText::_('COM_CSVI_' . $form->getField('sortfields', 'jform')->id . '_DESC'); ?>
			</span>
		</div>
	</div>
	<hr />
<?php endif; ?>
<div class="pull-right">
	<?php echo JHtml::_(
		'link',
		'index.php?option=com_csvi&view=templatefields&csvi_template_id=' . $this->item->csvi_template_id,
		JText::_('COM_CSVI_EDIT_TEMPLATEFIELDS'),
		'class="btn btn-primary" target="_new"');
	?>
</div>
<div class="row-fluid">
<table class="table table-striped table-condensed">
	<thead>
		<tr>
			<th class="span1"><?php echo JText::_('JFIELD_ORDERING_LABEL'); ?></th>
			<th><?php echo JText::_('COM_CSVI_FIELD_NAME'); ?></th>
		</tr>
	</thead>
	<tfoot></tfoot>
	<tbody>
		<?php
			foreach ($this->fields as $field)
			{
			?>
			<tr>
				<th><?php echo $field->ordering; ?></th>
				<th><?php echo $field->field_name; ?></th>
			</tr>
			<?php
			}
			?>
	</tbody>
</table>
</div>