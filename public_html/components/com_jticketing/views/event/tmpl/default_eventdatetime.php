<div class="row">
	<div class="col-xs-12">
		<strong><?php echo JText::_('COM_JTICKETING_EVENT_DATE_AND_TIME');?></strong><br>
		<?php
			$startDateD = JHtml::date($this->item->startdate, JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_FJY_DATE'), true);
			$endDateD = JHtml::date($this->item->enddate, JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_FJY_DATE'), true);

			if ($startDateD == $endDateD)
			{
				echo $bookingStartDate = JHtml::date($this->item->startdate, $this->params->get('date_format_show'), true);
			}
			else
			{
				echo $bookingStartDate = JHtml::date($this->item->startdate, $this->params->get('date_format_show'), true);
				echo " - ";
				echo $bookingStartDate = JHtml::date($this->item->enddate, $this->params->get('date_format_show'), true);
			}

		echo "<br>";
		$link = JRoute::_('index.php?option=com_jticketing&view=event&tmpl=component&layout=add_to_calendar&id=' . $this->item->id); ?>
		<a href="#addToGoogleModal" data-toggle="modal" data-target="#addToGoogleModal"> 
			<?php echo JText::_('COM_JTICKETING_EVENT_ADD_TO_CALENDER');?>
		</a>
		<div class="modal fade" id="addToGoogleModal" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-body">
						<p><?php echo $this->loadTemplate("add_to_calendar");?></p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<?php
	if ($this->item->online_events == '0')
	{
		if ($this->item->venue != '0')
		{
		?>
			<div class="col-xs-12">
				<?php echo "<br/>";?>
				<strong><?php echo JText::_('COM_JTICKETING_EVENT_LOCATION');?></strong><br>
				<i class="fa fa-map-marker" aria-hidden="true"></i>
				<?php
					if ($this->item->online_events != '0')
					{
						echo $this->venueName;
					}
					else
					{
						echo $this->venueName . ', ' . $this->venueAddress;
					}
				?>
				<br>
				<strong>
					<a id="googleMap" href="#evnetGoogleMapLocation" title="">
					<?php echo JText::_('COM_JTICKETING_VIEW_MAP_LINK')?>
					</a>
				</strong>
			</div>
		<?php
		}
		else
		{?>
			<div class="col-xs-12">
				<p></p>
				<strong><?php echo JText::_('COM_JTICKETING_EVENT_LOCATION');?></strong><br>
				<?php echo $this->item->location;?>
				<br>
				<a id="googleMap" href="#evnetGoogleMapLocation" title="">
					<?php echo JText::_('COM_JTICKETING_VIEW_MAP_LINK')?>
				</a>
			</div>
		<?php
		}
	}
	?>
</div>
