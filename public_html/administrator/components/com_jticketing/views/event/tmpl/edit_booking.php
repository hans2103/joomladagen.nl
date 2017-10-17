<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('booking_start_date');?>
	</div>
	<div class="controls">
	<?php
		for ($i = 1; $i <= 12; $i++)
		{
			$hours[] = JHtml::_('select.option', $i, $i);
		}

		$minutes   = array();
		$minutes[] = JHtml::_('select.option', 0, '00');
		$minutes[] = JHtml::_('select.option', 15, '15');
		$minutes[] = JHtml::_('select.option', 30, '30');
		$minutes[] = JHtml::_('select.option', 45, '45');

		$amPmSelect   = array();
		$amPmSelect[] = JHtml::_('select.option', 'AM', JText::_('COM_JTICKETING_EVENT_TIME_AM'));
		$amPmSelect[] = JHtml::_('select.option', 'PM', JText::_('COM_JTICKETING_EVENT_TIME_PM'));

		if (!isset($this->item->booking_start_date) or $this->item->booking_start_date == '0000-00-00 00:00:00')
		{
			$selectedBkStartmin = JFactory::getDate()->Format('i');
			$startBkAmPm   = JFactory::getDate()->Format('H') >= 12 ? 'PM' : 'AM';
			$final_start_booking_date = JFactory::getDate()->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
			$selectedBkStartHour = JFactory::getDate()->Format('H');
		}
		else
		{
			$startBkAmPm   = JFactory::getDate($this->item->booking_start_date)->Format('H');
			$startBkAmPm   = JHtml::date($this->item->booking_start_date, JText::_('H'), true);
			$startBkAmPm  = $startBkAmPm >= 12 ? 'PM' : 'AM';
			$selectedBkStartmin = JFactory::getDate($this->item->booking_start_date)->Format('i');
			$selectedBkStartmin = JHtml::date($this->item->booking_start_date, JText::_('i'), true);
			$selectedBkStartHour = JFactory::getDate($this->item->booking_start_date)->Format('H');
			$selectedBkStartHour = JHtml::date($this->item->booking_start_date, JText::_('H'), true);

			if ($selectedBkStartHour > 12)
			{
				$selectedBkStartHour = $selectedBkStartHour - 12;
			}

			$final_start_booking_date = JFactory::getDate($this->item->booking_start_date)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
			$final_start_booking_date = JHtml::date($this->item->booking_start_date, JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'), true);
		}

		if ($selectedBkStartHour == '00' or $selectedBkStartHour == '0')
		{
			$selectedBkStartHour = 12;
		}

		echo $calender = JHtml::_('calendar', $final_start_booking_date, 'jform[booking_start_date]', 'jform_booking_start_date', JText::_('COM_JTICKETING_DATE_FORMAT_CALENDER'), array('class' => '')
					);
		echo $startBkHourSelect = JHtml::_('select.genericlist', $hours, 'jform[booking_start_time_hour]',
			array('class' => 'required input input-mini chzn-done changevenue'), 'value', 'text',
						$selectedBkStartHour, false
						);

		echo $startBkMinSelect = JHtml::_('select.genericlist', $minutes, 'jform[booking_start_time_min]',
			array('class' => 'required input input-mini chzn-done changevenue'), 'value', 'text',
			$selectedBkStartmin, false
			);

		echo $startBkAmPmSelect = JHtml::_('select.genericlist', $amPmSelect, 'jform[booking_start_time_ampm]',
			array('class' => 'required input input-mini chzn-done changevenue'), 'value', 'text',
			$startBkAmPm, false
			);
		echo "<br/>";
		echo "<i>" . JText::_('COM_JTICKETING_DATE_FORMAT_CALENDER_DESC') . "</i>";
					?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('booking_end_date'); ?>
	</div>
	<div class="controls">
	<?php
		$selectedBkStartHour = $selectedBkStartmin = $startBkAmPm = $end_date_event = '';

		// Set date to current date.
		$end_date_event = JFactory::getDate()->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));

		if (!isset($this->item->booking_end_date))
		{
			$selectedBkStartmin = JFactory::getDate()->Format('i');
			$startBkAmPm   = JFactory::getDate()->Format('H') >= 12 ? 'PM' : 'AM';
			$booking_end_date = JFactory::getDate()->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
			$selectedBkStartHour = JFactory::getDate()->Format('H');
		}
		else
		{
			$startBkAmPm = JFactory::getDate($this->item->booking_end_date)->Format('H');
			$startBkAmPm   = JHtml::date($this->item->booking_end_date, JText::_('H'), true);
			$startBkAmPm  = $startBkAmPm >= 12 ? 'PM' : 'AM';
			$selectedBkStartmin = JFactory::getDate($this->item->booking_end_date)->Format('i');
			$selectedBkStartmin = JHtml::date($this->item->booking_end_date, JText::_('i'), true);
			$selectedBkStartHour = JFactory::getDate($this->item->booking_end_date)->Format('H');
			$selectedBkStartHour = JHtml::date($this->item->booking_end_date, JText::_('H'), true);

			if ($selectedBkStartHour > 12)
			{
				$selectedBkStartHour = $selectedBkStartHour - 12;
			}

			$booking_end_date = JFactory::getDate($this->item->booking_end_date)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
			$booking_end_date = JHtml::date($this->item->booking_end_date, JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'), true);
		}

		if ($selectedBkStartHour == '00' OR $selectedBkStartHour == '0')
		{
			$selectedBkStartHour = 12;
		}

		echo $calendar = JHtml::_(
		'calendar', $booking_end_date, 'jform[booking_end_date]', 'jform_booking_end_date', JText::_('COM_JTICKETING_DATE_FORMAT_CALENDER'), array('class' => '')
		);

		echo $endHourSelect = JHtml::_(
		'select.genericlist', $hours, 'jform[booking_end_time_hour]',
		array('class' => 'required input input-mini chzn-done changevenue'), 'value', 'text',
		$selectedBkStartHour, false
		);

		echo $endMinSelect = JHtml::_(
		'select.genericlist', $minutes, 'jform[booking_end_time_min]',
		array('class' => 'required input input-mini chzn-done changevenue'), 'value', 'text',
		$selectedBkStartmin, false
		);

		echo $endAmPmSelect = JHtml::_(
		'select.genericlist', $amPmSelect, 'jform[booking_end_time_ampm]',
		array('class' => 'required input input-mini chzn-done changevenue'), 'value', 'text',
		$startBkAmPm, false
		);
		echo "<br/>";

		echo "<i>" . JText::_('COM_JTICKETING_DATE_FORMAT_CALENDER_DESC') . "</i>";
	?>
	</div>
</div>
