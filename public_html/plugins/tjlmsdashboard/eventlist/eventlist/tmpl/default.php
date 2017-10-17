<?php
/**
 * @package    Shika
 * @author     TechJoomla | <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
// No direct access
defined('_JEXEC') or die('Restricted access');
?>

<div class="your_enrolled_events <?php echo $plg_data->size; ?>">
	<div class="panel-heading grey_border_class">
		<img alt="Your Events" src="<?php echo $this->dash_icons_path.'enrolled-courses.png'; ?>" />
		<span><?php echo JText::_('PLG_TJLMSDASHBOARD_EVENT_LIST_MY_EVENTS'); ?></span>
	</div>

	<div class="side_padding no-top-border grey_border_class">
	<?php $i = count($eventList);
			$j = 0;
	?>
	<?php if (empty($eventList)): ?>
			<div class="alert alert-warning">
				<?php echo JText::_('PLG_TJLMSDASHBOARD_EVENT_LIST_NO'); ?>
			</div>
	<?php endif; ?>
	<?php foreach ($eventList as $eachevent)
	{
		$j++;
		?>
		<div class="row-fluid each_liked_course">
			<div class="">
				<a href="index.php?option=com_jticketing&view=event&id=<?php echo $eachevent->id; ?> "><?php echo $eachevent->title; ?></a>
			</div>
		</div>

		<?php if ($i != $j): ?>
		<hr class="hr hr-condensed tjlms_hr_dashboard">
		<?php endif;?>
		<?php
	}
	?>
	</div>
</div>

