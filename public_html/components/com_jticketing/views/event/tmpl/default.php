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
jimport('techjoomla.common');
$this->techjoomlacommon = new TechjoomlaCommon;
$jticketingMainHelper = new jticketingmainhelper;
$jteventHelper = new JteventHelper;
JticketingCommonHelper::getLanguageConstant();
$document = JFactory::getDocument();
$eventUrl = 'index.php?option=com_jticketing&view=event&id=' . $this->item->id;
$eventUrl = JUri::root() . substr(JRoute::_($eventUrl), strlen(JUri::base(true)) + 1);

$document = JFactory::getDocument();
$document->addscript(JUri::root(true) . '/media/com_jticketing/js/googlemap.js');

// Load Chart Javascript Files.
$document->addScript(JUri::root(true) . '/media/com_jticketing/vendors/js/Chart.min.js');
$document->addscript(JUri::root(true) . '/media/com_jticketing/vendors/js/jquery.countdown.min.js');

// If event is offline then only show location graph condition checked here.
if ($this->item->online_events == '0')
{
	$data = $jteventHelper->getCoordinates($this->item->id, $this->item->venue);
}

if ($this->item->image)
{
	$imagePath = $this->item->image->media_l;
}
else
{
	$imagePath = JRoute::_(JUri::base() . 'media/com_jticketing/images/default-event-image.png');
}
?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?> col-xs-12">
	<div class="row">
		<div class="col-xs-12 col-sm-8">
			<?php
			if ($this->item->created_by == $this->userid)
			{
			?>
				<div class="col-xs-12 col-sm-12">
					<div class="pull-right">
					<select id='event-graph-period'>
						<option value = '0'><?php echo JText::_('COM_JTICKETING_FILTER_LATEST');?></option>
						<option value = '1'><?php echo JText::_('COM_JTICKETING_FILTER_LAST_MONTH');?></option>
						<option value = '2'><?php echo JText::_('COM_JTICKETING_FILTER_LAST_YEAR');?></option>
					</select>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12">
					<canvas id="myevent_graph"></canvas>
				</div>
			<?php
			}
			else
			{
				if ($this->item->image)
				{
					$imagePath = $this->item->image->media_l;
				}
				else
				{
					$imagePath = JRoute::_(JUri::base() . 'media/com_jticketing/images/default-event-image.png');
				}
				?>
				<div class="text-center eventMainImg">
						<img itemprop="image" class="jt_img-thumbnail com_jticketing_image_w98pc" src="<?php echo $imagePath;?>">
				</div>
				<?php
			}
			?>
		</div>
		<div class="col-xs-12 col-sm-4">
			<?php echo $this->loadTemplate("organizer");?>
		</div>
	</div>

	<!--Sharing Option, Booking Button-->
	<hr>
		<div class="row">
			<!--Sharing Option-->
			<div class="col-xs-12 col-sm-8">
				<?php
				if ($this->params->get('social_sharing'))
				{
				?>
				<?php
					echo '<div id="fb-root"></div>';
					$fbLikeTweet = JUri::root() . 'media/com_jticketing/js/fblike.js';
					echo "<script type='text/javascript' src='" . $fbLikeTweet . "'></script>";

					// Set metadata
					$config = JFactory::getConfig();
					$siteName = $config->get('sitename');
					$document->addCustomTag('<meta property="og:title" content="' . $this->escape($this->item->title) . '" />');
					$document->addCustomTag('<meta property="og:image" content="' . $imagePath . '" />');
					$document->addCustomTag('<meta property="og:url" content="' . $eventUrl . '" />');
					$document->addCustomTag('<meta property="og:description" content="' . nl2br($this->escape($this->item->short_description)) . '" />');
					$document->addCustomTag('<meta property="og:site_name" content="' . $this->siteName . '" />');
					$document->addCustomTag('<meta property="og:type" content="event" />');
					$pid = $this->params->get('addthis_publishid');

					if ($this->params->get('social_shring_type') == 'addthis')
					{
						$addThisShare = '
						<div class="addthis_toolbox addthis_default_style">

						<a class="addthis_button_facebook_like" fb:like:layout="button_count" class="addthis_button" addthis:url="' . $eventUrl . '"></a>
						<a class="addthis_button_google_plusone" g:plusone:size="medium" class="addthis_button" addthis:url="' . $eventUrl . '"></a>
						<a class="addthis_button_tweet" class="addthis_button" addthis:url="' . $eventUrl . '"></a>
						<a class="addthis_button_pinterest_pinit" class="addthis_button" addthis:url="' . $eventUrl . '"></a>
						<a class="addthis_counter addthis_pill_style" class="addthis_button" addthis:url="' . $eventUrl . '"></a>
						</div>
						<script type="text/javascript">
							var addthis_config ={ pubid: "' . $pid . '"};
						</script>
						<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid="' . $pid . '"></script>';

						$addThisJs = 'http://s7.addthis.com/js/300/addthis_widget.js';
						$this->techjoomlacommon->loadScriptOnce($addThisJs);

						/*output all social sharing buttons*/
						echo' <div id="rr" style="">
							<div class="social_share_container">
							<div class="social_share_container_inner">' .
								$addThisShare .
							'</div>
						</div>
						</div>
						';
					}
					else
					{
						echo '<div class="com_jticketing_horizontal_social_buttons">';
							echo '<div class="com_jticketing_float_left">
										<div class="fb-like" style=" display: inline-grid" data-href="' . $eventUrl . '" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true">
										</div>
								  </div>';

							echo '<div class="com_jticketing_float_left">
									&nbsp; <div class="g-plus" data-action="share" data-annotation="bubble" data-href="' . $eventUrl . '"></div>
								  </div>';
							echo '<div class="com_jticketing_float_left">
									&nbsp; <a href="https://twitter.com/share" class="twitter-share-button" data-url="' . $eventUrl . '" data-counturl="' . $eventUrl . '"  data-lang="en">Tweet</a>
								  </div>';
						echo '</div>
							<div class="com_jticketing_clear_both"></div>';
					}
				?>
				<?php
				}
				?>
				<div class="clearfix"></div>
				<span>
				<!--Like Dislike Option-->
				<?php
					// Integration with Jlike
					if (file_exists(JPATH_SITE . '/' . 'components/com_jlike/helper.php'))
					{
						$showComments = -1;
						$showLikeButtons = 1;

						$jTicketingIntegrationsHelper = new JTicketingIntegrationsHelper;
						$jlikeHtml = $jTicketingIntegrationsHelper->DisplayjlikeButton(
						$eventUrl, $this->item->id, $this->escape($this->item->title), $showComments, $showLikeButtons
						);

						if ($jlikeHtml)
						{
							echo $jlikeHtml;
						}
					}
				?>
				</span>
			</div>
			<div class="col-xs-12 col-sm-4 booking-btn-fixes">
				<i class="fa fa-calendar" aria-hidden="true"></i>
				<?php
				$this->currentTime = JFactory::getDate()->toSql();

				if ($this->item->booking_start_date > $this->currentTime)
				{
					echo JText::_('COM_JTICKETING_EVENTS_BOOKING_WILL_START');
					echo $bookingStartDate = JHtml::date($this->item->booking_start_date, $this->params->get('date_format_show'), true);
				}
				elseif ($this->item->booking_end_date < $this->currentTime)
				{
					echo JText::_('COM_JTICKETING_EVENTS_BOOKING_IS_CLOSED');
				}
				else
				{
					echo JText::_('COM_JTICKETING_EVENTS_BOOKING_WILL_CLOSED');
					echo $bookingEndDate = JHtml::date($this->item->booking_end_date, $this->params->get('date_format_show'), true);
				}
				?>

				<?php echo "<br>";?>
				<!--Button code-->
				<?php
				$btnBlock = '';
				$class = '';
				$closeBtnStyle = '';

				if ($this->item->eventBookStatus == 1) // Booking not started
				{
				?>
					<input type="button"
					class="btn btn-default btn-lg disabled <?php echo $btnBlock;?>"
					style="<?php echo $closeBtnStyle;?>" value="<?php echo JText::_('COM_JTICKETING_EVENTS_BOOKING_BTN_NOT_STARTED');?>">
				<?php
				}

				if($this->item->enddate < $this->currentTime)
				{
					if ($this->item->online_events == 1 && ($this->item->isboughtEvent == 1 ||  $this->item->created_by == $this->userid))
					{?>
						<button type="button" class="btn btn-info btn-lg enable com_jticketing_button"
							id="jt-meetingRecording"
							data-loading-text="<i class='fa fa-spinner fa-spin '></i> Loading.." onclick = 'jtSite.event.meetingRecordingUrl(this)' >
						<?php echo JText::_('COM_JTICKETING_VIEW_MEETINGS_RECORDINGS');?></button>
					<?php
					}
					else
					{?>
						<input type="button"
						class="btn btn-default btn-lg disabled <?php echo $btnBlock;?>"
						style="<?php echo $closeBtnStyle;?>"
						value="<?php echo JText::_("COM_JTICKETING_EVENTS_BOOKING_BTN_CLOSED");?>"
					<?php
					}
				}

				if ($this->item->booking_end_date < $this->currentTime)
				{
					if($this->item->enddate > $this->currentTime && $this->item->isboughtEvent == 1 && $this->showAdobeButton == 1)
					{?>
					<button type="button" class="btn btn-info btn-lg enable com_jticketing_button"
						id="jt-enterMeeting"
						data-loading-text="<i class='fa fa-spinner fa-spin '></i> Loading.." onclick ='jtSite.event.onlineMeetingUrl(this)' >
						<?php echo JText::_('COM_JTICKETING_MEETING_BUTTON');?>
					</button>
					<?php
					}
					elseif ($this->item->online_events == 1 && $this->item->enddate > $this->currentTime && $this->item->isboughtEvent == 1 && $this->showAdobeButton == 0)
					{?>
						<span class="tool-tip" data-toggle="tooltip" data-placement="top" title="<?php echo JText::sprintf('COM_JT_MEETING_ACCESS',$this->beforeEventStartTime);?>">
							<button class="btn btn-info btn-lg com_jticketing_button" disabled="disabled"><?php echo JText::_('COM_JTICKETING_MEETING_BUTTON');?></button>
						</span>
					<?php
					}
					else
					{?>
						<input type="button"
						class="btn btn-default btn-lg disabled <?php echo $btnBlock;?>"
						style="<?php echo $closeBtnStyle;?>"
						value="<?php echo JText::_("COM_JTICKETING_EVENTS_BOOKING_BTN_CLOSED");?>"
					<?php
					}
				}
				else
				{
					if($this->item->online_events == 1 && $this->item->isboughtEvent == 1 && $this->showAdobeButton == 1)
					{
					?>
						<button type="button" class="btn btn-info btn-lg enable com_jticketing_button"
						id="jt-enterMeeting"
						data-loading-text="<i class='fa fa-spinner fa-spin '></i> Loading.." onclick ='jtSite.event.onlineMeetingUrl(this)' >
						<?php echo JText::_('COM_JTICKETING_MEETING_BUTTON');?>
						</button>
					<?php
					}
					elseif ($this->item->online_events == 1 && $this->item->isboughtEvent == 1 && $this->showAdobeButton == 0)
					{?>
						<span class="tool-tip" data-toggle="tooltip" data-placement="top" title="<?php echo JText::sprintf('COM_JT_MEETING_ACCESS',$this->beforeEventStartTime);?>">
							<button class="btn btn-info btn-lg com_jticketing_button" disabled="disabled"><?php echo JText::_('COM_JTICKETING_MEETING_BUTTON');?></button>
						</span>
					<?php
					}
					elseif($this->item->online_events == 0 && $this->item->isboughtEvent == 1 && array_key_exists('buy_button', $this->item))
					{?>
						<div class="info">
							<p><?php echo JText::_("COM_JTICKETING_ONLINE_EVENT_ALREADY_BOUGHT");?></p>
						</div>
					<?php echo $this->item->buy_button;
					}
					elseif($this->item->online_events == 0 && $this->item->isboughtEvent == 1 && array_key_exists('enrol_button', $this->item))
					{?>
						<div class="info">
							<p><?php echo JText::_("COM_JTICKETING_ALREADY_ENROLLED");?></p>
						</div>
					<?php
					}
					else
					{
						if (array_key_exists('enrol_button', $this->item))
						{
							echo $this->item->enrol_button;
						}
						elseif (array_key_exists('buy_button', $this->item))
						{
							echo $this->item->buy_button;
						}
						elseif (!(array_key_exists('enrol_button', $this->item)) && !(array_key_exists('buy_button', $this->item)) && $this->item->isboughtEvent == 1)
						{?>
							<input type="button" class="btn btn-info btn-lg disabled com_jticketing_button"
							value="<?php echo JText::_('COM_JTICKETING_EVENTS_ENROLLED_BTN');?>"/>
						<?php
						}
					}
				}
				?>
				<!--Button code end here-->
			</div>
		</div>
	<hr>
	<!--Sharing Option, Booking Button end here-->

	<div class="row">
		<div class="col-xs-12 col-sm-8">
			<div class="row">
				<div class="col-xs-12 col-sm-12">
					<ul id="eventTab" class="nav nav-tabs text-uppercase">
						<li class="active">
							<a data-toggle="tab" href="#event_activity">
								<?php echo strtoupper(JText::_('COM_JTICKETING_EVENT_ACTIVITY'));?>
							</a>
						</li>
						<?php
						if ($this->params->get('collect_attendee_info_checkout'))
						{
							if ($this->item->allow_view_attendee == 1 && count($this->item->eventAttendeeInfo) > 0)
							{
							?>
								<li>
									<a data-toggle="tab" href="#event_attendee">
										<?php echo strtoupper(JText::_('COM_JTICKETING_EVENT_ATTENDEE'));?>
									</a>
								</li>
							<?php
							}
						}

						if (isset($this->item->gallery))
						{
						?>
							<li>
								<a data-toggle="tab" href="#event_gallery">
									<?php echo strtoupper(JText::_('COM_JTICKETING_EVENT_GALLERY'));?>
								</a>
							</li>
						<?php
						}
						?>

						<?php
						if (count($this->extraData))
						{
						?>
							<li>
								<a data-toggle="tab" href="#event_addtional_info">
									<?php echo strtoupper(JText::_('COM_JTICKETING_EVENT_ADDITIONAL_INFO'));?>
								</a>
							</li>
						<?php
						}?>
					</ul>
				</div>
				<div class="col-xs-12 col-sm-12">
					<div class="tab-content">
						<div id="event_activity" class="tab-pane active">
							<?php
								echo $this->loadTemplate("activity");
							?>
						</div>
						<div id="event_attendee" class="tab-pane">
							<?php
								echo $this->loadTemplate("attendee");
							?>
						</div>
						<div id="event_gallery" class="tab-pane">
							<?php
								echo $this->loadTemplate("gallery");
							?>
						</div>
						<div id="event_addtional_info" class="tab-pane">
							<?php
								echo $this->loadTemplate("extrafields");
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-4">
			<?php
				echo $this->loadTemplate("eventdatetime");
			?>
		</div>
	</div>
	<div class="clearfix">&nbsp;</div>
	<div clas="row">
		<?php
		// Integration with Jlike
		if (file_exists(JPATH_SITE . '/components/com_jlike/helper.php'))
		{
			$showComments = 1;
			$showLikeButtons = 0;
			$jTicketingIntegrationsHelper = new JTicketingIntegrationsHelper;
			$jlikeHtml = $jTicketingIntegrationsHelper->DisplayjlikeButton(
					$eventUrl, $this->item->id, $this->escape($this->item->title), $showComments, $showLikeButtons
					);

			if ($jlikeHtml)
			{
				echo $jlikeHtml;
			}
		}
		?>
	</div>
	<div class="clearfix">&nbsp;</div>
	<div class="row">
		<div class="col-sm-12">
		<?php
		if ($this->item->online_events == '0' && $this->response_a->status == 'OK')
		{
		?>
			<div id="jticketing-event-map" class="jticketing-event-map box-style">
				<?php
					if ($this->response_a)
					{
						$lat = $this->response_a->results[0]->geometry->location->lat;
						$long = $this->response_a->results[0]->geometry->location->lng;
					}?>
				<div id="evnetGoogleMapLocation" >
					<?php echo JText::_('COM_JTICKETING_MAPS_LOADING'); ?>
				</div>
				<?php
					if ($this->siteHTTPS == 2)
					{?>
						<div class="text-center">
							<button id = "googleloc" class='btn btn-small btn-success'><?php echo JText::_('COM_JTICKETING_GET_LOCATION_BUTTON')?></button>
						</div>
					<?php
					}
				?>
			</div>
		<?php
		}?>
		</div>
	</div>
</div>
<div class="modal fade" id="recordingUrl" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel"><?php echo JText::_('COM_JTICKETING_RECORDING_LIST');?></h4>
			</div>
			<div class="modal-body" id="content">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('COM_JTICKETING_MODAL_CLOSE');?></button>
			</div>
		</div>
	</div>
</div>
<?php
$currentDate = JFactory::getDate();

// If event is offline then only show location graph condition checked here.
if ($this->item->online_events == '0')
{
	$data = $jteventHelper->getCoordinates($this->item->id, $this->item->venue);
}

if ($this->item->online_events == '0')
{
	$lat = $lat;
	$long = $long;
	$data['latitude'] = $data['latitude'];
	$data['longitude'] = $data['longitude'];
}
else
{
	$lat = 0;
	$long = 0;
	$data['latitude'] = 0;
	$data['longitude'] = 0;
}
?>
<input type="hidden" id="event_id" name="event_id" value="<?php echo $this->item->id;?>"/>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=true&key=<?php echo $this->params->get('google_map_api_key');?>"></script>
<script type="text/javascript">
	var jticketing_baseurl = "<?php echo JUri::root();?>";
	var event_id = "<?php echo $this->item->id;?>";
	var recording_error = "<?php echo JText::_('COM_JTICKETING_NO_RECORDING_FOUND');?>";
	var recording_name = "<?php echo JText::_('COM_JTICKETING_RECORDING_NAME');?>";
	var currentDate ="<?php echo $currentDate;?>";
	var startDate = "<?php echo $this->item->startdate;?>";
	var endDate = "<?php echo $this->item->enddate;?>";
	var onlineEvent = "<?php echo $this->item->online_events;?>";

	if (onlineEvent != '1')
	{
		var lat = "<?php echo $lat;?>";
		var lon = "<?php echo $long;?>";
		var destinationlat="<?php echo $data['latitude'];?>";
		var destinationlong="<?php echo $data['longitude'];?>";
	}
	defaultGMapLevel = <?php echo $this->params->get('gmaps_default_zoom_level'); ?>;

	jtSite.event.initEventDetailJs();
	google.maps.event.addDomListener(window, 'load', initialize);
	jtSite.event.getGoogleLocation();
</script>
