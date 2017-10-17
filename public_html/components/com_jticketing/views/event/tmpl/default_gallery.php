<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
JHtml::_('behavior.modal', 'a.modal');

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true) . '/media/com_jticketing/vendors/css/magnific-popup.css');
$document->addScript(JUri::root(true) . '/media/com_jticketing/vendors/js/jquery.magnific-popup.min.js');

if (isset($this->item->gallery))
{
	$eventVideoData = array();
	$eventImageData = array();

	for ($i = 0; $i <= count($this->item->gallery); $i++)
	{
		if (isset($this->item->gallery[$i]->type))
		{
			$eventContentType = substr($this->item->gallery[$i]->type, 0, 5);

			if ($eventContentType == 'image')
			{
				$eventImageData[$i] = $this->item->gallery[$i];
			}
			elseif ($eventContentType == 'video')
			{
				$eventVideoData[$i] = $this->item->gallery[$i];
			}
		}
	}

	if (count($eventVideoData) > 0)
	{
	?>
		<div class="row">
			<div class="col-xs-12 col-sm-10 videosText">
				<h5><?php echo JText::_('COM_JTICKETING_EVENT_VIDEOS');?></h5>
			</div>
			<?php
			if (count($eventVideoData) > 0 && count($eventImageData) > 0)
			{
			?>
				<div class="col-xs-12 col-sm-2 gallary-filters">
					<select id="gallary_filter" class="pull-right">
						<option value="0"><?php echo JText::_('COM_JTICKETING_EVENT_TYPE');?></option>
						<option value="1"><?php echo JText::_('COM_JTICKETING_EVENT_GALLERY_VIDEOS');?></option>
						<option value="2"><?php echo JText::_('COM_JTICKETING_EVENT_GALLERY_IMAGES');?></option>
					</select>
				</div>
			<?php
			}?>
		</div>
		<div id="videos">
			<?php
				if (!empty($eventVideoData))
				{
				?>
					<div id="eventVideo">
						<div class="row">
							<div class="media" id="jt_video_gallery">
								<?php
								foreach ($eventVideoData as $eventVideo)
								{
									$eventVideoType = substr($eventVideo->type, 6);
									$link = JRoute::_(
									JUri::root() . "index.php?option=com_jticketing&view=event&layout=default_playvideo&id=" . $this->item->id . "&vid=" .
									$eventVideo->id . "&type=" . trim($eventVideoType) . "&tmpl=component"
									);

									$videoId  = JticketingMediaHelper::videoId($eventVideoType, $eventVideo->media);
									$thumbSrc = JticketingMediaHelper::videoThumbnail($eventVideoType, $videoId);
									?>
									<div class="col-sm-3 jt_gallery_image_item">
										<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $link; ?>" class="modal jt-bs3-modal">
											<img src="<?php echo JUri::root(true) . '/media/com_jticketing/images/play_icon.png';?>"class="play_icon"/>
											<img src="<?php echo $thumbSrc; ?>" width="100%"/>
										</a>
									</div>
								<?php
								}?>
							</div>
						</div>
					</div>
				<?php
				}
			?>
		</div>
	<?php
	}

	if (count($eventImageData) > 0)
	{
	?>
		<div id="images">
			<div class="row">
				<div class="col-xs-12 col-sm-12 imagesText">
					<h5><?php echo JText::_('COM_JTICKETING_EVENT_IMAGES');?></h5>
				</div>
			</div>
			<?php
				if (!empty($eventImageData))
				{
				?>
					<div id="eventImages">
						<div class="row">
							<div class="media" id="jt_image_gallery">
								<div class="popup-gallery">
									<?php
									foreach ($eventImageData as $eventImage)
									{
										$img_path = $eventImage->media_l;
										?>
										<div class="col-xs-6 col-sm-3 jt_image_item">
											<a href="<?php echo $img_path;?>" title="" class="" >
												<div class="jt-image-gallery-inner" style="background-size:contain;background-repeat:no-repeat; background-position:center center;background-image: url('<?php echo $img_path;?>');">
												</div>
											</a>
										</div>
									<?php
									}?>
								</div>
							</div>
						</div>
					</div>
				<?php
				}
			?>
		</div>
	<?php
	}?>
<?php
}
?>
<script type="text/javascript">
	jQuery(document).ready(function()
	{
		jtSite.event.eventImgPopup();
		jtSite.event.onChangefun();
	});
</script>
