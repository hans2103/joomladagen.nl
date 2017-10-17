<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('startdate'); ?></div>
	<div class="controls">
		<?php
		for($i = 1; $i <= 12; $i++)
		{
			$hours[] = JHtml::_('select.option', $i, $i);
		}

		$minutes   = array();
		$minutes[] = JHtml::_('select.option', '00', '00');
		$minutes[] = JHtml::_('select.option', 15, '15');
		$minutes[] = JHtml::_('select.option', 30, '30');
		$minutes[] = JHtml::_('select.option', 45, '45');

		$amPmSelect   = array();
		$amPmSelect[] = JHtml::_('select.option', 'AM', 'am' );
		$amPmSelect[] = JHtml::_('select.option', 'PM', 'pm' );

		if(!isset($this->item->startdate) or $this->item->startdate=='0000-00-00 00:00:00')
		{
			$selectedmin = JFactory::getDate()->Format('i');
			$startAmPm   = JFactory::getDate()->Format('H') >= 12 ? 'PM' : 'AM';
			$final_start_event_date = JFactory::getDate()->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
			$selectedStartHour = JFactory::getDate()->Format('H');

		}
		else
		{
			$startAmPm   = JFactory::getDate($this->item->startdate)->Format('H');
			$startAmPm   = JHtml::date($this->item->startdate,JText::_('H'), true);
			$startAmPm  = $startAmPm >= 12 ? 'PM' : 'AM';
			$selectedmin = JFactory::getDate($this->item->startdate)->Format('i');
			$selectedmin = JHtml::date($this->item->startdate,JText::_('i'), true);
			$selectedStartHour = JFactory::getDate($this->item->startdate)->Format('H');
			$selectedStartHour = JHtml::date($this->item->startdate,JText::_('H'), true);

			if($selectedStartHour > 12)
			{
				$selectedStartHour = $selectedStartHour - 12;
			}

			$final_start_event_date = JFactory::getDate($this->item->startdate)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
			$final_start_event_date = JHtml::date($this->item->startdate,JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'), true);
		}

		if($selectedStartHour=='00' or $selectedStartHour=='0')
		{
			$selectedStartHour = 12;
		}

		echo $calender = JHtml::_('calendar', $final_start_event_date, 'jform[startdate]', 'jform_startdate', JText::_('COM_JTICKETING_DATE_FORMAT_CALENDER'));

		echo "&nbsp;&nbsp;";

		echo $startHourSelect = JHtml::_('select.genericlist', $hours, 'jform[event_start_time_hour]', array('class'=>'required input input-mini chzn-done changevenue'), 'value', 'text', $selectedStartHour, false );

		echo $startMinSelect = JHtml::_('select.genericlist', $minutes, 'jform[event_start_time_min]', array('class'=>'required input input-mini chzn-done changevenue'), 'value', 'text', $selectedmin, false );

		echo $startAmPmSelect = JHtml::_('select.genericlist', $amPmSelect, 'jform[event_start_time_ampm]', array('class'=>'required input input-mini chzn-done changevenue'), 'value', 'text', $startAmPm, false);

		echo "<br/>";

		echo "<i>" . JText::_('COM_JTICKETING_DATE_FORMAT_CALENDER_DESC') . "</i>";
		?>
	</div>
</div>

<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('enddate'); ?></div>
	<div class="controls">
	<?php
		$selectedStartHour = $selectedmin = $startAmPm = $end_date_event = '';

		// Set date to current date.
		$end_date_event = JFactory::getDate()->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));

		if(!isset($this->item->enddate))
		{
			$selectedmin = JFactory::getDate()->Format('i');
			$startAmPm   = JFactory::getDate()->Format('H') >= 12 ? 'PM' : 'AM';
			$final_end_event_date = JFactory::getDate()->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
			$selectedStartHour = JFactory::getDate()->Format('H');
		}
		else
		{
			$startAmPm   = JFactory::getDate($this->item->enddate)->Format('H');
			$startAmPm   = JHtml::date($this->item->enddate,JText::_('H'), true);
			$startAmPm  = $startAmPm >= 12 ? 'PM' : 'AM';
			$selectedmin = JFactory::getDate($this->item->enddate)->Format('i');
			$selectedmin = JHtml::date($this->item->enddate,JText::_('i'), true);
			$selectedStartHour = JFactory::getDate($this->item->enddate)->Format('H');
			$selectedStartHour = JHtml::date($this->item->enddate,JText::_('H'), true);

			if($selectedStartHour > 12)
			{
				$selectedStartHour = $selectedStartHour - 12;
			}

			$final_end_event_date = JFactory::getDate($this->item->enddate)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
			$final_end_event_date = JHtml::date($this->item->enddate,JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'), true);
		}

		if ($selectedStartHour=='00' OR $selectedStartHour=='0')
		{
			$selectedStartHour = 12;
		}

		echo $calendar = JHtml::_('calendar', $final_end_event_date, 'jform[enddate]', 'jform_enddate', JText::_('COM_JTICKETING_DATE_FORMAT_CALENDER'));

		echo "&nbsp;&nbsp;";

		echo $endHourSelect = JHtml::_('select.genericlist', $hours, 'jform[event_end_time_hour]', array('class'=>'required input input-mini chzn-done changevenue'), 'value', 'text', $selectedStartHour, false );

		echo $endMinSelect = JHtml::_('select.genericlist',  $minutes , 'jform[event_end_time_min]', array('class'=>'required input input-mini chzn-done changevenue'), 'value', 'text',$selectedmin , false );

		echo $endAmPmSelect = JHtml::_('select.genericlist', $amPmSelect, 'jform[event_end_time_ampm]', array('class'=>'required input input-mini chzn-done changevenue'), 'value', 'text', $startAmPm, false );

		echo "<br/>";

	echo "<i>" . JText::_('COM_JTICKETING_DATE_FORMAT_CALENDER_DESC') . "</i>";
		?>
	</div>
</div>
<?php

	$OnlineEvents = $this->params->get('enable_online_events');

	if ($OnlineEvents == 1)
	{	?>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('online_events'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('online_events'); ?></div>
		</div>
	<?php
	}

	if($this->item->venue == 0)
	{
?>
	<div class="control-group id="note_id">
		<div class="controls">
			<?php echo JText::sprintf('COM_VENUE_LOCATION_NOTE');?>
		</div>
	</div>
<?php
	}
?>
<div class="control-group" id="venue_id">

	<div class="control-label"><?php echo $this->form->getLabel('venue'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('venue'); ?>
	<div id="ajax_loader"></div>

	</div>
</div>

<?php
	if ($OnlineEvents == 1)
	{	?>
		<div class="control-group" id="venuechoice_id">
			<div class="control-label"><?php echo $this->form->getLabel('venuechoice'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('venuechoice'); ?></div>
			<input type="hidden" name="event_url" class="event_url" id="event_url" value=""/>
			<input type="hidden" name="event_sco_id" class="event_sco_id" id="event_sco_id" value=""/>
		</div>
		<div class="control-group" id="existingEvent">
			<div class="control-label"><?php echo $this->form->getLabel('existing_event'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('existing_event'); ?></div>
		</div>
<?php
	}?>
<div class="control-group" id="event-location">

	<div class="control-label"><?php echo $this->form->getLabel('location'); ?></div>
	<div class="controls">
		<?php echo $this->form->getInput('location'); ?>
	</div>

</div>

<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places&key=<?php echo $this->googleMapApiKey;?>" type="text/javascript"></script>
<script type="text/javascript">

	// Google Map autosuggest  for location
	function initialize()
	{
		input = document.getElementById('jform_location');
		var autocomplete = new google.maps.places.Autocomplete(input);
	}

	google.maps.event.addDomListener(window, 'load', initialize);
</script>
