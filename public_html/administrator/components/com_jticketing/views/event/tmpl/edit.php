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
JHtml::_('behavior.formvalidator');

	$existingUrl = '';
	$existingParams = json_decode($this->item->jt_params);
	if (!empty($existingParams))
	$existingUrl = $existingParams->event_url;
	$mediaGalleryObj = 0;

	if (isset($this->item->gallery))
	{
		$mediaGalleryObj = json_encode($this->item->gallery);
	}

	// Event detail view resized image setting
	$this->eventMainImage = $this->com_params->get('admin_event_detail_view','media_s');
	// Event detail view resized image setting
	$this->eventGalleryImage = $this->com_params->get('admin_event_gallery_view','media_s');

$mainframe = JFactory::getApplication();
$isAdmin = 0;

if ($mainframe->isAdmin())
{
	$isAdmin = 1;
}
?>
<script type="text/javascript">
	var existing_url = "<?php echo $existingUrl; ?>";
	var enforceVendor = "<?php echo $this->enforceVendor; ?>";
	var eventId = "<?php echo $this->item->id; ?>";
	var venueName = "<?php echo $this->venueName;?>";
	var root_url = "<?php echo JUri::root(); ?>";
	var venueId = "<?php echo $this->venueId;?>" ;
	var mediaSize = '<?php echo $this->mediaSize;?>';
	var mediaGallery = '<?php echo $mediaGalleryObj;?>';
	var eventGalleryImage = '<?php echo $this->eventGalleryImage;?>';
	var eventMainImage = '<?php echo $this->eventMainImage;?>';
	var enableOnlineVenues = '<?php echo $this->enableOnlineVenues;?>';
	var selectedVenue = '<?php echo $this->item->venue;?>';
	var jticketing_baseurl = "<?php echo JUri::root(); ?>";
	var handle_transactions = "<?php echo $this->com_params->get('handle_transactions'); ?>";
	var array_check = "<?php echo $this->arra_check; ?>";
	jtAdmin.event.initEventJs();
	validation.positiveNumber();
</script>
<div id="warning_message">
</div>
<form action="<?php echo JRoute::_('index.php?option=com_jticketing&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span12">
				<?php
				echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));
					echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_JTICKETING_EVENT_TAB_DETAILS', true));
						echo JHtml::_('bootstrap.startAccordion', 'myAccordian', array('active' => 'collapse1'));
							echo JHtml::_('bootstrap.addSlide', 'myAccordian', JText::_('COM_JTICKETING_EVENT_TAB_BASIC'), 'collapse1', $class = '');
								echo $this->loadTemplate('details');
							echo JHtml::_('bootstrap.endSlide');

							echo JHtml::_('bootstrap.addSlide', 'myAccordian', JText::_('COM_JTICKETING_EVENT_TAB_LOCATION'), 'collapse2', $class='');
								echo $this->loadTemplate('location');
							echo JHtml::_('bootstrap.endSlide');

							echo JHtml::_('bootstrap.addSlide', 'myAccordian', JText::_('COM_JTICKETING_EVENT_TAB_TIME'), 'collapse3', $class='');
								echo $this->loadTemplate('booking');
							echo JHtml::_('bootstrap.endSlide');
						echo JHtml::_('bootstrap.endAccordion');
					echo JHtml::_('bootstrap.endTab');
					// TJ-Field Additional fields for Event
					if ($this->form_extra)
					{
						echo JHtml::_('bootstrap.addTab', 'myTab', 'extrafields', JText::_('COM_JTICKETING_EVENT_TAB_EXTRA_FIELDS', true));

						if (empty($this->item->id))
						{ ?>
							<div class="alert alert-info">
								<?php echo JText::_('COM_JTICKETING_EVENT_EXTRA_DETAILS_SAVE_PROD_MSG');?>
							</div>
							<?php
						}
						else
						{
							// @TODO SNEHAL- Load layout for this
							echo $this->loadTemplate('extrafields');
						}

						echo JHtml::_('bootstrap.endTab');
					}

					// Tab start for ticket types.
					echo JHtml::_('bootstrap.addTab', 'myTab', 'tickettypes', JText::_('COM_JTICKETING_EVENT_TAB_TICKET_TYPES', true));
							echo $this->form->getInput('tickettypes');
					echo JHtml::_('bootstrap.endTab');

					// Tab start for Attendee Fields.
					if ($this->collect_attendee_info_checkout)
					{
						echo JHtml::_('bootstrap.addTab', 'myTab', 'attendeefields', JText::_('COM_JTICKETING_EVENT_TAB_EXTRA_FIELDS_ATTENDEE', true));
							echo $this->loadTemplate('attendee_core_fields');
						echo JHtml::_('bootstrap.endTab');
					}

					echo JHtml::_('bootstrap.addTab', 'myTab', 'gallery', JText::_('COM_JTICKETING_EVENT_GALLERY', true));
					?>
	<div class="span12">
		<div class="span5 well">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('gallery_file'); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('gallery_file'); ?>
				</div>
			</div>
		<?php //echo $this->form->renderField('gallery_file'); ?>
		</div>
		<div class="span7 well">
			<div class="span8">
				<?php echo $this->form->renderField('gallery_link'); ?>
			</div>
			<div class="span4">
				<input type="button" class="validate_video_link" onclick="tjMediaFile.validateFile(this,1, <?php echo $isAdmin;?>)"
				value="<?php echo JText::_('COM_TJMEDIA_ADD_VIDEO_LINK');?>">
			</div>
		</div>
	</div>
	<div class="span12">
		<div class="subform-wrapper">
			<ul class="media_gallary_parent thumbnails">
			  <li class="clone_media hide_jtdiv span2">
			  <button class="close" onclick="tjMediaFile.tjMediaGallery.deleteMedia(this, <?php echo $isAdmin;?>);return false;">×</button>
				<input type="hidden" name="jform[gallery_file][media][]" class="media_field_value" value="">
				<div class="thumbnail"></div>
			  </li>
			</ul>
		</div>
	</div>
			<?php 	echo JHtml::_('bootstrap.endTab');

					if (JFactory::getUser()->authorise('core.edit.own','com_jticketing.event')) :
						echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true));
							echo $this->form->getInput('rules');
						echo JHtml::_('bootstrap.endTab');
					endif;
					echo JHtml::_('bootstrap.endTabSet'); ?>
			<div>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
<?php
if(!$this->accessLevel)
{?>
	<style>
		.subform-repeatable-group .control-group:last-child{
		display: none;
}
	</style>
<?php
}
