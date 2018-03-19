<?php
/**
 * Import page
 *
 * @author 		RolandD Cyber Produksi
 * @link 		https://csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: default.php 2436 2013-05-25 13:14:20Z Roland $
 */

defined('_JEXEC') or die;

// Add chosen
JHtml::_('formbehavior.chosen');
?>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<?php if (count($this->templates) > 0) : ?>
		<div class="span2">
			<?php
				$layout = new JLayoutFile('csvi.import.steps');
				echo $layout->render(array('step' => $this->step));
			?>
		</div>
		<div class="span10">
			<form  action="index.php?option=com_csvi&view=imports" id="adminForm" name="adminForm" method="post" class="form-horizontal">
				<?php
					$select = JHtml::_('select.option', '', JText::_('COM_CSVI_MAKE_A_CHOICE'), 'csvi_template_id', 'template_name');
					array_unshift($this->templates, $select);
					echo JHtml::_('select.genericlist', $this->templates, 'csvi_template_id', 'class="input-xxlarge advancedSelect"', 'csvi_template_id', 'template_name', $this->lastRunId);
				?>
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	<?php else : ?>
		<?php echo JText::_('COM_CSVI_NO_TEMPLATES_CREATE_THEM'); ?>
	<?php endif; ?>
</div>
