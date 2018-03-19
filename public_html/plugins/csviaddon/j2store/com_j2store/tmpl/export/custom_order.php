<?php
/**
 * @package     CSVI
 * @subpackage  VirtueMart
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

$form = $this->forms->custom_order;
?>

<div class="control-group">
	<label class="control-label <?php echo $form->getField('splitorderline', 'jform')->labelClass; ?>" for="<?php echo $form->getField('splitorderline', 'jform')->id; ?>">
		<?php echo JText::_('COM_CSVI_' . $form->getField('splitorderline', 'jform')->id . '_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo $form->getInput('splitorderline', 'jform'); ?>
		<span class="help-block">
			<?php echo JText::_('COM_CSVI_' . $form->getField('splitorderline', 'jform')->id . '_DESC'); ?>
		</span>
		<span class="cron-block">
			<?php echo str_replace('jform_', 'form.', $form->getField('splitorderline', 'jform')->id); ?>
		</span>
	</div>
</div>

<div class="control-group">
	<label class="control-label <?php echo $form->getField('ordernostart', 'jform')->labelClass; ?>" for="<?php echo $form->getField('ordernostart', 'jform')->id; ?>">
		<?php echo JText::_('COM_CSVI_' . $form->getField('ordernostart', 'jform')->id . '_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo $form->getInput('ordernostart', 'jform'); ?>
		<?php echo $form->getInput('ordernoend', 'jform'); ?>
		<span class="help-block">
			<?php echo JText::_('COM_CSVI_' . $form->getField('ordernostart', 'jform')->id . '_DESC'); ?>
		</span>
		<span class="cron-block">
			<?php echo str_replace('jform_', 'form.', $form->getField('ordernostart', 'jform')->id); ?>
			/
			<?php echo str_replace('jform_', 'form.', $form->getField('ordernoend', 'jform')->id); ?>
		</span>
	</div>
</div>

<div class="control-group">
	<label class="control-label <?php echo $form->getField('orderlist', 'jform')->labelClass; ?>" for="<?php echo $form->getField('orderlist', 'jform')->id; ?>">
		<?php echo JText::_('COM_CSVI_' . $form->getField('orderlist', 'jform')->id . '_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo $form->getInput('orderlist', 'jform'); ?>
		<span class="help-block">
			<?php echo JText::_('COM_CSVI_' . $form->getField('orderlist', 'jform')->id . '_DESC'); ?>
		</span>
		<span class="cron-block">
			<?php echo str_replace('jform_', 'form.', $form->getField('orderlist', 'jform')->id); ?>
		</span>
	</div>
</div>

<div class="control-group">
	<label class="control-label <?php echo $form->getField('orderdaterange', 'jform')->labelClass; ?>" for="<?php echo $form->getField('orderdaterange', 'jform')->id; ?>">
		<?php echo JText::_('COM_CSVI_' . $form->getField('orderdaterange', 'jform')->id . '_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo $form->getInput('orderdaterange', 'jform'); ?>
		<span class="help-block">
			<?php echo JText::_('COM_CSVI_' . $form->getField('orderdaterange', 'jform')->id . '_DESC'); ?>
		</span>
		<span class="cron-block">
			<?php echo str_replace('jform_', 'form.', $form->getField('orderdaterange', 'jform')->id); ?>
		</span>
	</div>
</div>

<div class="control-group">
	<label class="control-label <?php echo $form->getField('orderdatestart', 'jform')->labelClass; ?>" for="<?php echo $form->getField('orderdatestart', 'jform')->id; ?>">
		<?php echo JText::_('COM_CSVI_' . $form->getField('orderdatestart', 'jform')->id . '_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo $form->getInput('orderdatestart', 'jform'); ?>
		<?php echo $form->getInput('orderdateend', 'jform'); ?>
		<span class="help-block">
			<?php echo JText::_('COM_CSVI_' . $form->getField('orderdatestart', 'jform')->id . '_DESC'); ?>
		</span>
		<span class="cron-block">
			<?php echo str_replace('jform_', 'form.', $form->getField('orderdatestart', 'jform')->id); ?>
			/
			<?php echo str_replace('jform_', 'form.', $form->getField('orderdateend', 'jform')->id); ?>
		</span>
	</div>
</div>

<div class="control-group">
	<label class="control-label <?php echo $form->getField('ordermdatestart', 'jform')->labelClass; ?>" for="<?php echo $form->getField('ordermdatestart', 'jform')->id; ?>">
		<?php echo JText::_('COM_CSVI_' . $form->getField('ordermdatestart', 'jform')->id . '_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo $form->getInput('ordermdatestart', 'jform'); ?>
		<?php echo $form->getInput('ordermdateend', 'jform'); ?>
		<span class="help-block">
			<?php echo JText::_('COM_CSVI_' . $form->getField('ordermdatestart', 'jform')->id . '_DESC'); ?>
		</span>
		<span class="cron-block">
			<?php echo str_replace('jform_', 'form.', $form->getField('ordermdatestart', 'jform')->id); ?>
			/
			<?php echo str_replace('jform_', 'form.', $form->getField('ordermdateend', 'jform')->id); ?>
		</span>
	</div>
</div>

<div class="control-group">
	<label class="control-label <?php echo $form->getField('orderstatus', 'jform')->labelClass; ?>" for="<?php echo $form->getField('orderstatus', 'jform')->id; ?>">
		<?php echo JText::_('COM_CSVI_' . $form->getField('orderstatus', 'jform')->id . '_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo $form->getInput('orderstatus', 'jform'); ?>
		<span class="help-block">
			<?php echo JText::_('COM_CSVI_' . $form->getField('orderstatus', 'jform')->id . '_DESC'); ?>
		</span>
		<span class="cron-block">
			<?php echo str_replace('jform_', 'form.', $form->getField('orderstatus', 'jform')->id); ?>
		</span>
	</div>
</div>

<div class="control-group">
	<label class="control-label <?php echo $form->getField('ordercurrency', 'jform')->labelClass; ?>" for="<?php echo $form->getField('ordercurrency', 'jform')->id; ?>">
		<?php echo JText::_('COM_CSVI_' . $form->getField('ordercurrency', 'jform')->id . '_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo $form->getInput('ordercurrency', 'jform'); ?>
		<span class="help-block">
			<?php echo JText::_('COM_CSVI_' . $form->getField('ordercurrency', 'jform')->id . '_DESC'); ?>
		</span>
		<span class="cron-block">
			<?php echo str_replace('jform_', 'form.', $form->getField('ordercurrency', 'jform')->id); ?>
		</span>
	</div>
</div>

<div class="control-group">
	<label class="control-label <?php echo $form->getField('orderpricestart', 'jform')->labelClass; ?>" for="<?php echo $form->getField('orderpricestart', 'jform')->id; ?>">
		<?php echo JText::_('COM_CSVI_' . $form->getField('orderpricestart', 'jform')->id . '_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo $form->getInput('orderpricestart', 'jform'); ?>
		<?php echo $form->getInput('orderpriceend', 'jform'); ?>
		<span class="help-block">
			<?php echo JText::_('COM_CSVI_' . $form->getField('orderpricestart', 'jform')->id . '_DESC'); ?>
		</span>
		<span class="cron-block">
			<?php echo str_replace('jform_', 'form.', $form->getField('orderpricestart', 'jform')->id); ?>
			/
			<?php echo str_replace('jform_', 'form.', $form->getField('orderpriceend', 'jform')->id); ?>
		</span>
	</div>
</div>
