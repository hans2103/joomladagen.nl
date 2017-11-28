<?php
/**
 * @package    Pwtimage
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

// Load the component language file
$language = Factory::getLanguage();
$language->load('com_pwtimage', JPATH_ADMINISTRATOR . '/components/com_pwtimage');

// Load the JavaScript and CSS files
HTMLHelper::_('script', 'com_pwtimage/pwtimage.min.js', array('relative' => true, 'version' => 'auto'));
HTMLHelper::_('stylesheet', 'com_pwtimage/pwtimage.min.css', array('relative' => true, 'version' => 'auto'));

// Set the JavaScript language strings
Text::script('COM_PWTIMAGE_IMAGE_LOADING', true);
Text::script('COM_PWTIMAGE_CHOOSE_IMAGE', true);
Text::script('COM_PWTIMAGE_SAVE_FAILED', true);

// Get our helper
if (!class_exists('PwtImageHelper'))
{
	require_once JPATH_ADMINISTRATOR . '/components/com_pwtimage/helpers/pwtimage.php';
}

$helper = new PwtimageHelper;
$input  = Factory::getApplication()->input;

// Load the component parameters
$parameters = ComponentHelper::getParams('com_pwtimage');

// Get the token to validate we are true heroes
list($tokenName, $tokenValue) = explode(':', $helper->getToken());

// Set the default values
$modalId        = $input->getCmd('modalId', uniqid());
$ratio          = '';
$width          = null;
$sourcePath     = $parameters->get('sourcePath', '/images');
$subPath        = $parameters->get('subPath', '{year}/{month}');
$showFolder     = $parameters->get('showFolder', false);
$showTools      = true;
$activePage     = 'upload';
$imagePreview   = '';
$multiple       = false;
$showGallery    = $parameters->get('showGallery', false);
$showHelp       = $parameters->get('showHelp', true);
$baseFolder     = $helper->getImageFolder(true);
$imageFolder    = $helper->getImageFolder();
$maxSize        = (string) $helper->fileUploadMaxSize();
$maxSizeMessage = Text::_('COM_PWTIMAGE_MAX_SIZE_MESSAGE');
$maxDimension   = 15000;
$buttonText     = 'JSELECT';
$wysiwyg        = $input->getBool('wysiwyg', false);
$siteUrl        = JUri::current();

/**
 * @param   int    $modalId      The unique ID for the modal
 * @param   string $ratio        The image ratio to use
 * @param   int    $width        The fixed with for an image
 * @param   string $sourcePath   The main image path
 * @param   string $subPath      The image sub-folder
 * @param   bool   $showFolder   Set if the image selection from server should be shown
 * @param   bool   $showTools    Set if the toolbar needs to be shown
 * @param   string $activePage   Set which tab should be shown by default this is the upload tab
 * @param   string $imagePreview A given image to show in preview
 * @param   bool   $multiple     Set if multiple images should be allowed
 * @param   bool   $showGallery  Set if the gallery option should be shown
 * @param   bool   $showHelp     Set if the help option should be shown
 */
/** @var array $displayData */
extract($displayData);

// Get the settings passed from the button
$settings = json_decode(base64_decode($input->getBase64('settings')), true);

if (is_array($settings))
{
	extract($settings);
}

// Sanity check on the source path, it cannot be empty
if (!$sourcePath)
{
	$sourcePath = $parameters->get('sourcePath', '/images');
}

// Enrich the displayData for the sublayouts
$displayData['modalId']        = $modalId;
$displayData['ratio']          = $ratio;
$displayData['width']          = $width;
$displayData['sourcePath']     = $sourcePath;
$displayData['subPath']        = $subPath;
$displayData['showTools']      = $showTools;
$displayData['maxSize']        = $maxSize;
$displayData['maxSizeMessage'] = $maxSizeMessage;
$displayData['maxDimension']   = $maxDimension;
$displayData['tokenName']      = $tokenName;
$displayData['tokenValue']     = $tokenValue;
$displayData['parameters']     = $parameters;
$displayData['wysiwyg']        = $wysiwyg;
$displayData['canDo']          = $canDo;

// Set which tab should be visible
$uploadActive = 'is-active';
$folderActive = '';

if ($showFolder && $activePage === 'folder')
{
	$uploadActive = '';
	$folderActive = 'is-active';
}

$iFrameLink = Uri::root() . 'index.php?option=com_pwtimage&amp;view=image&layout=iframe&amp;tmpl=component';

// Make sure we have a gallery value
$showGallery = $showGallery ?: 0;

// Gallery function is for version 1.1.0
$showGallery = 0;

// Do some sanity checks
$user = Factory::getUser();

Factory::getDocument()->addScriptDeclaration(<<<JS
	jQuery(document).ready(function (){
	    // Make sure we are in an iFrame
	    if (window.self === window.top)
        {
			window.location = '{$siteUrl}';   
        }

		pwtImage.setIframeLink('{$iFrameLink}');
		pwtImage.setWysiwyg('{$wysiwyg}');
		pwtImage.setTargetId('{$modalId}');
		
		// New tabs
		jQuery('[data-tab').on('click', function(event) {
			var tabId = jQuery(this).attr('href');
			jQuery('[data-tab]').parent().removeClass('is-active');
			jQuery(this).parent().addClass('is-active');
			jQuery('[data-tabs-pane]').removeClass('is-active');
			jQuery(tabId).addClass('is-active');
			event.preventDefault();
		});
	
		// Initiate tabscroller
		vanillaTabScroller.init({
			wrapper: '[data-tabs-wrapper-{$modalId}]'
		});
		
		// Set the button visibility
		jQuery('.js-button-image').prop('disabled', true);
		
		jQuery('[href="#upload"], [href="#select"], [href="#help"]').on('click', function() {
			jQuery('.js-button-image').prop('disabled', true);
		});
	});
JS
);
?>

<script>document.body.classList.add('pwt-image-styling');</script>

<!-- ID field is required here so the script knows which block it must target -->
<div class="pwt-component" id="<?php echo $modalId; ?>_modal">

	<!-- PWT Id container -->
	<div class="pwt-id js-pwtimage-id" id="<?php echo $modalId; ?>">

		<!-- Header -->
		<div class="pwt-header">

			<!-- Tabs -->
			<div class="pwt-tabs-wrapper" data-tabs-wrapper-<?php echo $modalId; ?>>
				<div class="pwt-tabs-scroller" data-tabs-scroller>
					<ul class="pwt-tabs" data-tabs>
						<li class="<?php echo $uploadActive; ?>">
							<a data-tab href="#upload"><?php echo Text::_('COM_PWTIMAGE_TAB_UPLOAD'); ?></a>
						</li>
						<?php if ($showFolder) : ?>
						<li class="<?php echo $folderActive; ?>">
							<a data-tab href="#select"><?php echo Text::_('COM_PWTIMAGE_TAB_SELECT'); ?></a>
						</li>
						<?php endif; ?>
						<li>
							<a data-tab href="#edit"><?php echo Text::_('COM_PWTIMAGE_TAB_EDIT'); ?></a>
						</li>
						<?php if ($showGallery) : ?>
						<li>
							<a data-tab href="#gallery"><?php echo Text::_('COM_PWTIMAGE_TAB_GALLERY'); ?></a>
						</li>
						<?php endif; ?>
						<?php if ($showHelp) : ?>
						<li>
							<a data-tab href="#help"><?php echo Text::_('COM_PWTIMAGE_TAB_HELP'); ?></a>
						</li>
						<?php endif; ?>
					</ul>
				</div>
			</div><!-- .pwt-tabs-wrapper -->

		</div><!-- .pwt-header -->

		<!-- Body -->
		<div class="pwt-body">

			<!-- Tabs panes -->
			<div class="pwt-tabs-panes" data-tabs-content>
				<div class="pwt-tabs-pane <?php echo $uploadActive; ?>" data-tabs-pane id="upload">
					<?php echo $this->sublayout('upload', $displayData); ?>
				</div>
				<?php if ($showFolder) : ?>
					<div class="pwt-tabs-pane <?php echo $folderActive; ?>" data-tabs-pane id="select">
						<?php echo $this->sublayout('select', array('baseFolder' => $baseFolder, 'sourcePath' => $sourcePath, 'tokenName' => $tokenName, 'tokenValue' => $tokenValue, 'wysiwyg' => $wysiwyg)); ?>
					</div>
				<?php endif; ?>
				<div class="pwt-tabs-pane" data-tabs-pane id="edit">
					<?php echo $this->sublayout('edit', $displayData); ?>
				</div>
				<?php if ($showGallery) : ?>
					<div class="pwt-tabs-pane" data-tabs-pane id="gallery">
						<?php echo $this->sublayout('gallery', array('baseFolder' => $baseFolder, 'sourcePath' => $sourcePath, 'tokenName' => $tokenName, 'tokenValue' => $tokenValue)); ?>
					</div>
				<?php endif; ?>
				<?php if ($showHelp) : ?>
				<div class="pwt-tabs-pane" data-tabs-pane id="help">
					<?php echo $this->sublayout('help', array()); ?>
				</div>
				<?php endif; ?>
			</div><!-- .pwt-tabs-panes -->

		</div><!-- .pwt-body -->

		<!-- Footer -->
		<div class="pwt-footer">
			<button class="pwt-button pwt-button--success process-image js-button-image" onclick="pwtImage.saveImage('<?php echo $modalId; ?>','<?php echo $tokenName; ?>','<?php echo $tokenValue; ?>');">
				<?php echo Text::_('COM_PWTIMAGE_INSERT_IMAGE'); ?>
			</button>
			<!--
			<button class="pwt-button pwt-button--success is-hidden js-button-gallery" onclick="return pwtImage.saveGallery('<?php echo $modalId; ?>','<?php echo $tokenName; ?>','<?php echo $tokenValue; ?>', true);">
				<?php echo Text::_('COM_PWTIMAGE_SAVE_GALLERY'); ?>
			</button>
			-->
			<button class="pwt-button" type="button" onclick="pwtImage.closeModal();">
				<?php echo Text::_('COM_PWTIMAGE_CLOSE_MODAL') ?>
			</button>
		</div>
	</div><!-- .pwt-footer -->

</div><!-- .pwt-component -->
