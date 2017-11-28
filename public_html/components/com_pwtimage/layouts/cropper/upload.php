<?php
/**
 * @package    Pwtimage
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

extract($displayData);

Factory::getDocument()->addScriptDeclaration(
	<<<JS
	jQuery(document).ready(function () {
		var dragZone  = jQuery('#js-dragarea');
		
		dragZone.on('dragenter', function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			dragZone.addClass('hover');
			
			return false;
		});
	
		// Notify user when file is over the drop area
		dragZone.on('dragover', function(e) {
			e.preventDefault();
			e.stopPropagation();
	
			dragZone.addClass('hover');
	
			return false;
		});
	
		dragZone.on('dragleave', function(e) {
			e.preventDefault();
			e.stopPropagation();
			dragZone.removeClass('hover');
	
			return false;
		});
		
		dragZone.on('drop', function(e) {
			e.preventDefault();
			e.stopPropagation();
	
			dragZone.removeClass('hover');
			
			var files = e.originalEvent.target.files || e.originalEvent.dataTransfer.files;

			if (!files.length) {
				return;
			}
			
			// Store the file into the form
			pwtImage.createPreview(this, files);
			
			// Clean up after the upload
			pwtImage.cleanUpAfterUpload(this);
		});
	});
JS
);
?>

<!-- Content -->
<div class="pwt-content">

    <!-- Message -->
    <?php // @TODO: rename `has_folder` class below; ?>
	<div class="pwt-message">
		<?php echo Text::_('COM_PWTIMAGE_IMAGE_SAVED_IN'); ?><span class="has_folder"></span>
	</div><!-- .pwt-message -->

    <!-- Dropper area -->
	<div class="pwt-dropper" id="js-dragarea">
		<div class="pwt-dropper__content" id="dragarea-content">
			<p class="pwt-dropper__lead"><?php echo Text::_('COM_PWTIMAGE_DRAG_AND_UPLOAD'); ?></p>
			<p class="pwt-dropper__support">
				<label class="pwt-button pwt-button--primary" for="<?php echo $modalId; ?>_upload" title="<?php echo Text::_('COM_PWTIMAGE_UPLOAD_IMAGE'); ?>">
					<input class="visually-hidden" type="file" id="<?php echo $modalId; ?>_upload" name="image" accept="image/*" onclick="pwtImage.prepareUpload('<?php echo $modalId; ?>'); pwtImage.uploadImagePreview(this);"><?php echo Text::_('COM_PWTIMAGE_DRAG_AND_UPLOAD_SUPPORT'); ?>
				</label>
			</p>
		</div>
	</div><!-- .pwt-dropper -->

</div><!-- .pwt-content -->

<input type="hidden" id="post-url" value="//<?php echo str_ireplace('/administrator', '', Uri::getInstance()->toString(array('host', 'path'))); ?>" />
<input type="hidden" class="js-pwt-image-data" name="pwt-image-data">
<input type="hidden" class="js-pwt-image-ratio" name="pwt-image-ratio" value="<?php echo $ratio; ?>">
<input type="hidden" class="js-pwt-image-width" name="pwt-image-width" value="<?php echo $width; ?>">
<input type="hidden" class="js-pwt-image-sourcePath" name="pwt-image-sourcePath" value="<?php echo $sourcePath; ?>">
<input type="hidden" class="js-pwt-image-subPath" name="pwt-image-subPath" value="<?php echo $subPath; ?>">
<input type="hidden" class="js-pwt-image-maxsize" name="pwt-image-maxsize" value="<?php echo $maxSize; ?>">
<input type="hidden" class="js-pwt-image-maxsize-message" name="pwt-image-maxsize-message" value="<?php echo $maxSizeMessage; ?>">
<input type="hidden" class="js-pwt-image-dimensionsize" name="pwt-image-dimensionsize" value="<?php echo $maxDimension; ?>">
<input type="hidden" class="js-pwt-image-localfile" name="pwt-image-localFile" value="">
<input type="hidden" name="<?php echo $tokenName; ?>" value="<?php echo $tokenValue; ?>" />