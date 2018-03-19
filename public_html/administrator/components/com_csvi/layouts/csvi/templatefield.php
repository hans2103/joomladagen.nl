<?php
/**
 * @package     CSVI
 * @subpackage  Layouts
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/** @var array $displayData */
$field = $displayData['field'];
$form  = $displayData['form'];
?>
<div class="control-group">
	<label class="control-label <?php echo $form->getField($field, 'jform')->labelClass; ?>" for="<?php echo $form->getField($field, 'jform')->id; ?>">
		<?php echo JText::_('COM_CSVI_' . $form->getField($field, 'jform')->id . '_LABEL'); ?>
	</label>
	<div class="controls">
		<?php echo $form->getInput($field, 'jform'); ?>
		<span class="help-block">
			<?php echo JText::_('COM_CSVI_' . $form->getField($field, 'jform')->id . '_DESC'); ?>
		</span>
		<span class="cron-block">
			<?php echo str_replace('jform_', 'form.', $form->getField($field, 'jform')->id); ?>
		</span>
	</div>
</div>
