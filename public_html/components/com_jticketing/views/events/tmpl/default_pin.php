<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

$jticketingMainHelper = new jticketingmainhelper;
$integration = $this->params['integration'];
$pin_width = $this->params['pin_width'];
$pin_padding = $this->params['pin_padding'];

?>
<style type="text/css">
	.jticketing_pin {
		width: <?php echo $pin_width . 'px'; ?> !important;
		margin-right: <?php echo $pin_padding . 'px'; ?> !important;
	}
</style>
<?php
foreach ($this->items as $eventData)
{
?>
<div class="col-sm-3 col-xs-12 jticketing_pin">
	<div class="jticketing_pin_img">
		<?php
			$eventDetailUrl = $jticketingMainHelper->getEventlink($eventData->id);

			if ($integration == 4)
			{
				$imagePath = '/media/com_easysocial/avatars/event/' . $eventData->id . '/';
			}

			if($eventData->image)
			{
				$imagePath = $eventData->image->media_m;
			}
			else
			{
				$imagePath = JRoute::_(JUri::base() . 'media/com_jticketing/images/default-event-image.png');
			}
		?>
		<a href="<?php echo $eventDetailUrl;?>" title="<?php echo $eventData->title;?>" style="background:url('<?php echo $imagePath;?>'); background-position: center center; background-size: cover; background-repeat: no-repeat;">
		</a>
	</div>
	<div class="jt-heading">
		<span class="jt-event-ticket-price-text">
			<a href="<?php echo $eventDetailUrl;?>" title="<?php echo $eventData->title;?>">
			<?php
				if (($eventData->eventPriceMaxValue == $eventData->eventPriceMinValue) AND (($eventData->eventPriceMaxValue == 0) AND ($eventData->eventPriceMinValue == 0)))
				{
				?>
					<strong><?php echo strtoupper(JText::_('COM_JTICKETING_ONLY_FREE_TICKET_TYPE'));?></strong>
				<?php
				}
				elseif (($eventData->eventPriceMaxValue == $eventData->eventPriceMinValue) AND  (($eventData->eventPriceMaxValue != 0) AND ($eventData->eventPriceMinValue != 0)))
				{
				?>
					<strong><?php echo $jticketingMainHelper->getFromattedPrice(number_format(($eventData->eventPriceMaxValue), 2), $this->params['currency']);?></strong>
				<?php
				}
				else
				{
				?>
					<strong>
						<?php
							echo $jticketingMainHelper->getFromattedPrice(number_format(($eventData->eventPriceMinValue), 2), $this->params['currency']);
							echo ' - ';
							echo $jticketingMainHelper->getFromattedPrice(number_format(($eventData->eventPriceMaxValue), 2), $this->params['currency']);
						?>
					</strong>
				<?php
				}
			?>
			</a>
		</span>
	</div>

	<div class="thumbnail jt-pin-data">
		<div class="caption">
			<ul class="list-unstyled">
				<li>
					<div>
						<i class="fa fa-calendar" aria-hidden="true"></i>
					<?php
						echo $startDate = JHtml::date($eventData->startdate, $this->dateFormat, true);
					?>
					</div>
				</li>
				<li>
					<?php
					$online = JUri::base() . 'media/com_jticketing/images/online.png';
					if ($eventData->online_events)
					{
					?>
						<img src="<?php echo $online; ?>" 
						class="img-circle" alt="<?php echo JText::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE')?>"
						title="<?php echo JText::sprintf('COM_JTICKETING_ONLINE_EVENT', $eventData->title);?>">
					<?php
					}?>
					
					<b>
					<?php
					if (strlen($eventData->title) > 20)
					{
					?>
						<a href="<?php echo $eventDetailUrl;?>" title="<?php echo $eventData->title;?>">
							<?php echo substr($eventData->title, 0, 20) . '...';?>
							<?php
							if ($eventData->featured == 1)
							{
							?>
								<span>
									<i class="fa fa-star pull-right" aria-hidden="true" 
									title="<?php echo JText::sprintf('COM_JTICKETING_FEATURED_EVENT', $eventData->title);?>"></i>
								</span>
							<?php
							}?>
						</a>
					<?php
					}
					else
					{
					?>
						<a href="<?php echo $eventDetailUrl;?>" title="<?php echo $eventData->title;?>">
							<?php echo $eventData->title;?>
							<?php
							if ($eventData->featured == 1)
							{
							?>
								<span>
								<i class="fa fa-star pull-right" aria-hidden="true" 
								title="<?php echo JText::sprintf('COM_JTICKETING_FEATURED_EVENT', $eventData->title);?>"></i>
								</span>
							<?php
							}
							?>
						</a>
					<?php
					}
					?>
					</b>
				</li>
				<li class="events-pin-location">
					<i class="fa fa-map-marker" aria-hidden="true"></i>
					<?php echo $eventData->location;?>
				</li>
			</ul>
			<div class="clearfix"></div>
		</div>
	</div>
</div>
<?php
$currTime = JFactory::getDate()->toSql();
}?>


