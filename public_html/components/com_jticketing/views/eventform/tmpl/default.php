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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

$mainframe = JFactory::getApplication();
$isAdmin = 0;
$adminApproval = 0;

if ($mainframe->isAdmin())
{
	$isAdmin = 1;
}

if(($this->vendorCheck && $this->enforceVendor == 1) || $this->enforceVendor == 0)
{
	$allowed = 1;
}
else
{
	$allowed = 0;
}

if($allowed == 1)
{
	if ($this->integration != 2)
{
?>
	<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NATIVE_NOTICE');?>
	</div>
<?php
	return false;
}
else
{
?>
<div id="eventform1" class="row">
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?> jtick-wrapper">
	<div class="page-header">
		<h2>
			<?php
			if ($this->item->id)
			{
				echo JText::_('COM_JTICKETING_EDIT_EVENT');
				echo ':&nbsp' . $this->item->title;
			}
			else
			{
				echo JText::_('COM_JTICKETING_CREATE_NEW_EVENT');
			}
			?>
		</h2>
	</div>

<?php
	$params = JComponentHelper::getParams('com_jticketing');
	$handle_transactions = $params->get('handle_transactions');
	$adaptivePayment = $params->get('gateways');

	if($this->checkGatewayDetails == "true" && ($handle_transactions == 1 || in_array('adaptive_paypal', $adaptivePayment)))
	{
	?>
	<div class="alert alert-warning">
		<?php
		$vendor_id = $this->vendorCheck;
		$link = 'index.php?option=com_tjvendors&view=vendor&layout=profile&client=com_jticketing';
		echo JText::_('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG1');
		$Jticketingmainhelper = new Jticketingmainhelper;
		$itemid = $Jticketingmainhelper->getItemId($link);?>
			<a href="<?php echo JRoute::_($link . '&itemId='. $itemid .'&vendor_id=' . $vendor_id,false);?>" target="_blank">
			<?php echo JText::_('COM_JTICKETING_VENDOR_FORM_LINK'); ?></a>
		<?php echo JText::_('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG2');?>
	</div>
<?php
}
?>

		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<form id="adminForm" name="adminForm" action="" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			<?php
				echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));
					echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_JTICKETING_EVENT_TAB_DETAILS', true));?>
						<div class="">
							<div class="panel-group" id="accordion">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#collapseBasicDetails">
												<?php echo JText::_('COM_JTICKETING_EVENT_TAB_BASIC');?>
											</a>
										</h4>
									</div>
									<div id="collapseBasicDetails" class="panel-collapse collapse in">
										<div class="panel-body">
										<p>
											<?php echo $this->loadTemplate('details');?>
										</p>
										</div>
									</div>
								</div>

								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#collapseEventLocation">
												<?php echo JText::_('COM_JTICKETING_EVENT_TAB_LOCATION');?>
											</a>
										</h4>
									</div>
									<div id="collapseEventLocation" class="panel-collapse collapse in">
										<div class="panel-body">
										<p>
											<?php echo $this->loadTemplate('location');?>
										</p>
										</div>
									</div>
								</div>

								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#collapseEventTimeDetails">
												<?php echo JText::_('COM_JTICKETING_EVENT_TAB_TIME');?>
											</a>
										</h4>
									</div>
									<div id="collapseEventTimeDetails" class="panel-collapse collapse in">
										<div class="panel-body">
										<p>
											<?php echo $this->loadTemplate('booking');?>
										</p>
										</div>
									</div>
								</div>
							</div>
						</div>
				<?php
					echo JHtml::_('bootstrap.endTab');

					// Tab start for ticket types.
					echo JHtml::_('bootstrap.addTab', 'myTab', 'tickettypes', JText::_('COM_JTICKETING_EVENT_TAB_TICKET_TYPES', true));
						?>
						<div>
							 <div class="jticketing_params_container">
									<div><?php echo $this->form->getInput('tickettypes'); ?></div>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-12">
								<?php
								if ($this->params->get('siteadmin_comm_per') > 0 || $this->params->get('siteadmin_comm_flat') > 0)
								{
								?>
									<div id="commission">
										<span class="help-inline">
											<strong>
												<?php echo JText::sprintf('COMMISSION_DEDUCTED_NOT_PERCENT', $this->params->get('siteadmin_comm_per'), '%');?>
											</strong>
										</span>
										<?php
										if ($this->params->get('siteadmin_comm_flat') > 0)
										{
										?>
											<span class="help-inline">
												<strong>
													<?php echo JText::sprintf('COMMISSION_DEDUCTED_NOT_FLAT', $this->params->get('siteadmin_comm_flat'), $this->params->get('currency'));?>
												</strong>
											</span>
										<?php
										}
										?>
									</div>
								<?php
								}
								?>
							</div>
						</div>
						<?php
					echo JHtml::_('bootstrap.endTab');

					if ($this->form_extra)
					{
						echo JHtml::_('bootstrap.addTab', 'myTab', 'extrafields', JText::_('COM_JTICKETING_EVENT_TAB_EXTRA_FIELDS', true));

							if (empty($this->item->id))
							{
							?>
								<div class="alert alert-info">
									<?php echo JText::_('COM_JTICKETING_EVENT_EXTRA_DETAILS_SAVE_PROD_MSG');?>
								</div>
							<?php
							}
							elseif ($this->form_extra)
							{
								echo $this->loadTemplate('extrafields');
							}

						echo JHtml::_('bootstrap.endTab');
					}

					// Tab start for Attendee Fields.
					if ($this->collect_attendee_info_checkout)
					{
						echo JHtml::_('bootstrap.addTab', 'myTab', 'attendeefields', JText::_('COM_JTICKETING_EVENT_TAB_EXTRA_FIELDS_ATTENDEE', true));
							echo $this->loadTemplate('attendee_core_fields');
						echo JHtml::_('bootstrap.endTab');
					}

					echo JHtml::_('bootstrap.addTab', 'myTab', 'gallery', JText::_('COM_JTICKETING_EVENT_GALLERY', true));
					?>
						<div class="row">
							<div class="col-sm-12 event-gallery-option">
								<div class="form-group">
									<div class="col-sm-2 col-xs-12 control-label ">
										<?php echo $this->form->getLabel('gallery_file'); ?>
									</div>
									<div class="col-sm-10 col-xs-12 controls">
										<?php echo $this->form->getInput('gallery_file'); ?>
									</div>
								</div>
							</div>
							<div class="col-sm-12 event-gallery-option">
								<div class="col-sm-6 col-xs-12">
									<?php echo $this->form->renderField('gallery_link'); ?>
								</div>
								<div class="col-sm-6 col-xs-12">
									<input type="button" class="validate_video_link" onclick="tjMediaFile.validateFile(this,1, <?php echo $isAdmin;?>)"
									value="<?php echo JText::_('COM_TJMEDIA_ADD_VIDEO_LINK');?>">
								</div>
							</div>
						</div>

						<div class="row">
							<div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<div class="subform-wrapper">
									<ul class="media_gallary_parent thumbnails list-inline">
										<li class="clone_media hide_jtdiv span2">
											<button class="close" onclick="tjMediaFile.tjMediaGallery.deleteMedia(this, <?php echo $isAdmin;?>);return false;">×</button>
											<input type="hidden" name="jform[gallery_file][media][]" class="media_field_value" value="">
											<div class="thumbnail"></div>
										</li>
									</ul>
								</div>
							</div>
						</div>
					<?php
					echo JHtml::_('bootstrap.endTab');
				echo JHtml::_('bootstrap.endTabSet');
				?>
				<div class="">
					<button type="button" class="btn btn-default btn-primary com_jticketing_margin validate"
					onclick="Joomla.submitbutton('eventform.save')">
						<span><?php echo JText::_('JSUBMIT'); ?></span>
					</button>

					<a class="btn btn-default com_jticketing_margin"
						href="<?php echo JRoute::_('index.php?option=com_jticketing&task=eventform.cancel'); ?>"
						title="<?php echo JText::_('JCANCEL'); ?>">
						<?php echo JText::_('JCANCEL'); ?>
					</a>
				</div>

				<input type="hidden" name="option" value="com_jticketing" />
				<input type="hidden" name="task" id="task" value="eventform.save" />
				<input type="hidden" name="id" value="<?php if (!empty($this->item->id)) echo $this->item->id; ?>"/>
				<input type="hidden" name="jform[id]" value="<?php if (!empty($this->item->id)) echo $this->item->id;?>"/>
				<input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by ? $this->item->created_by:JFactory::getUser()->id;?>" />

				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>
<?php
}
}
else
{?>
	<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COM_JTICKETING_VENDOR_ENFORCEMENT_VENUE_ERROR');?>
	</div>
	<div>
		<?php echo JText::_('COM_JTICKETING_VENDOR_ENFORCEMENT_EVENT_REDIRECT_MESSAGE');?>
		<div>
			<a href="<?php echo JRoute::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=com_jticketing');?>" target="_blank" >
			<button class="btn btn-primary"><?php echo JText::_('COM_JTICKETING_VENDOR_ENFORCEMENT_EVENT_REDIRECT_LINK'); ?></button>
			</a>
		</div>
	</div>
<?php
}

$existingUrl = '';
$existingParams = json_decode($this->item->jt_params);

if (!empty($existingParams))
{
	$existingUrl = $existingParams->event_url;
}

$mediaGalleryObj = 0;

if (!empty($this->item->gallery))
{
	$mediaGalleryObj = json_encode($this->item->gallery);
}
?>
<script type="text/javascript">
	var existing_url = "<?php echo $existingUrl; ?>";
	var enforceVendor = '<?php echo $this->enforceVendor;?>';
	var eventId = "<?php echo $this->item->id; ?>";
	var venueName = "<?php echo $this->venueName;?>";
	var root_url = "<?php echo JUri::root(); ?>";
	var venueId = "<?php echo $this->venueId;?>";
	var mediaSize = '<?php echo $this->mediaSize;?>';
	var mediaGallery = '<?php echo $mediaGalleryObj;?>';
	var eventGalleryImage = '<?php echo $this->eventGalleryImage;?>';
	var eventMainImage = '<?php echo $this->eventMainImage;?>';
	var enableOnlineEvents = '<?php echo $this->onlineEvents;?>';
	var jticketing_baseurl = "<?php echo JUri::root(); ?>";
	var enableOnlineVenues = '<?php echo $this->enableOnlineVenues;?>';
	var descriptionError = '<?php echo JText::_('COM_JTICKETING_EMPTY_DESCRIPTION_ERROR');?>';
	var vendor_id = '<?php $this->vendorCheck;?>';
	var selectedVenue = '<?php echo $this->item->venue;?>';
	jtSite.eventform.initEventJs();
	validation.positiveNumber();
</script>
<?php
if (!$this->accessLevel)
{?>
<style>
		.subform-repeatable-group .form-group:last-child{
		display: none;
</style>
<?php
}
