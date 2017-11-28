<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
$jticketingMainHelper = new jticketingmainhelper;
?>
<div class="row event-counter-details" id="event-org-info">
	<div class="col-sm-12">
		<div class="col-sm-6">
			<h4>
				<p><?php echo $eventStartDate = JHtml::date($this->item->startdate, JText::_("COM_JTICKETING_DATE_FORMAT_EVENT_DETAIL_MONTH"), true);?></p>
				<p><?php echo $eventStartDate = JHtml::date($this->item->startdate, JText::_("COM_JTICKETING_DATE_FORMAT_EVENT_DETAIL_DATE"), true);?></p>
			</h4>
		</div>
		<div class="col-sm-6">
			<ul class="list-unstyled">
				<span id="jt-countdown"></span>
			</ul>
		</div>
	</div>
	<div class="col-sm-12 event-title">
		<h2>
			<?php
				if ($this->item->created_by == $this->userid)
				{
					$editEventlink = JRoute::_('index.php?option=com_jticketing&task=eventform.edit&id=' . $this->item->id . '&Itemid=' . $this->createEventItemid, false);
				?>
					<a id="eventEdit" href="<?php echo $editEventlink?>" title="<?php echo JText::_('COM_JTICKETING_EDIT_EVENT_LINK')?>">
						<?php echo $this->escape($this->item->title);?>
						<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
					</a>
				<?php
				}
				else
				{
					echo $this->escape($this->item->title);
				}
			?>
		</h2>
	</div>
	<div class="col-sm-12">
		<?php
			$long_desc_char = $this->params->get('desc_length', '', 'INT');;

			if (strlen($this->item->long_description) > $long_desc_char)
			{
				echo substr(strip_tags($this->item->long_description), 0, $long_desc_char);?>
				<a href="#myModal" data-toggle="modal" data-target="#myModal">...Read more</a>
			<?php
			}
			else
			{
				echo strip_tags($this->item->long_description);
			}
		?>
		<div class="modal fade" id="myModal" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-body">
							<p><?php echo $this->item->long_description;?></p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
	</div>
	<div class="col-sm-12">
		<!--Promoter Details-->
		<h6 class="text-uppercase"><strong><?php echo strtoupper(JText::_('COM_JTICKETING_EVENT_ORGANIZER'));?></strong></h6>
		<div class="row">
			<div class="col-xs-3 organizer-profile">
				<?php
				if ($this->item->organizerAvatar)
				{
					$profileImg = $this->item->organizerAvatar;
				}
				else
				{
					$profileImg = JUri::root(true) . '/media/com_jticketing/images/default_avatar.png';
				}
				?>
				<img src="<?php echo $profileImg; ?>" class="img-circle" alt="<?php echo JText::_('COM_JTICKETING_EVENT_OWNER_AVATAR')?>">
			</div>
			<div class="col-xs-9 text-muted">
				<?php
					$userinfo = JFactory::getUser($this->item->created_by);
					echo $userinfo->name . '<br>';
					echo $userinfo->email;

					if ($this->item->organizerProfileUrl)
					{
						echo '<br>';
					?>
						<a href="<?php echo $this->item->organizerProfileUrl;?>">
							<?php echo ucfirst(JText::_('COM_JTICKETING_EVENT_ORGANIZER_DETAILS'));?>
						</a>
				<?php
					}
				?>
			</div>
		</div>
		<!--Promoter Details end here-->
	</div>
</div>
<!--
<div class="row ticket-price-range">
-->
<div class="row">
	<div class="col-sm-12">
		<?php
			if (($this->item->eventPriceMaxValue == $this->item->eventPriceMinValue) AND (($this->item->eventPriceMaxValue == 0) AND ($this->item->eventPriceMinValue == 0)))
			{
			?>
				<strong><?php echo strtoupper(JText::_('COM_JTICKETING_ONLY_FREE_TICKET_TYPE'));?></strong>
			<?php
			}
			elseif (($this->item->eventPriceMaxValue == $this->item->eventPriceMinValue) AND  (($this->item->eventPriceMaxValue != 0) AND ($this->item->eventPriceMinValue != 0)))
			{
			?>
				<strong><?php echo $jticketingMainHelper->getFormattedPrice(number_format(($this->item->eventPriceMaxValue), 2), $this->params['currency']);?></strong>
			<?php
			}
			else
			{
			?>
				<strong>
					<?php
						echo $jticketingMainHelper->getFormattedPrice(number_format(($this->item->eventPriceMinValue), 2), $this->params->get('currency'));
						echo ' - ';
						echo $jticketingMainHelper->getFormattedPrice(number_format(($this->item->eventPriceMaxValue), 2), $this->params->get('currency'));
					?>
				</strong>
			<?php
			}
		?>
	</div>
</div>
