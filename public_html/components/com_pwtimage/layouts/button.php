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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

// Load the scripts that are required to make the modal work
HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'com_pwtimage/pwtimage.min.js', array('relative' => true, 'version' => 'auto'));
HTMLHelper::_('stylesheet', 'com_pwtimage/pwtimage.min.css', array('relative' => true, 'version' => 'auto'));

// Load the language files
$language = Factory::getLanguage();
$language->load('com_pwtimage', JPATH_SITE . '/components/com_pwtimage', 'en-GB');
$language->load('com_pwtimage', JPATH_SITE . '/components/com_pwtimage');

// Default settings
$modalId      = uniqid();
$buttonText   = 'JSELECT';
$imagePreview = '';
$target       = null;
$value        = null;

// Get the override values
/**
 * @param   int     $modalId       The unique identifier for the image block
 * @param   string  $ratio         The image ratio to use
 * @param   int     $width         The fixed with for an image
 * @param   string  $sourcePath    The main image path
 * @param   string  $subPath       The image sub-folder
 * @param   bool    $showFolder    Set if the image selection from server should be shown
 * @param   bool    $showTools     Set if the toolbar needs to be shown
 * @param   string  $activePage    Set which tab should be shown by default this is the upload tab
 * @param   string  $imagePreview  A given image to show in preview
 * @param   bool    $multiple      Set if multiple images should be allowed
 * @param   bool    $modal         Set if the canvas should be shown in a modal popup
 * @param   bool    $showGallery   Set if the gallery tab should be shown
 * @param   bool    $showHelp      Set if the help tab should be shown
 * @param   string  $target        The name of the original form field
 * @param   string  $value         The value of the original form field
 */
/** @var array $displayData */
extract($displayData);

// The link to PWT Image on frontend
$link = Uri::root() . 'index.php?option=com_pwtimage&amp;view=image&amp;tmpl=component&modalId=' . $modalId . '&settings=' . base64_encode(json_encode($displayData));

$modal = '<div class="js-image-controls">
				<!-- Render the image preview -->
				<div id="' . $modalId .'_preview" class="pwt-image-preview">
					<img ' . ($imagePreview ? 'src="' . Uri::root() . $imagePreview . '"' : "") . '/>
				</div>
				
				<!-- Render the original input field -->
				' . ($target ? '<input type="hidden" id="' . $modalId .'_value" name="' . $target . '" value="' . $value . '" />' : "") . '

				<!-- Select button to open the modal window -->
				<button href="#' . $modalId . '_modal" class="btn btn-primary pwt-image-select js-modal"
				        data-modal-close-text="' . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') .'"
				        data-modal-content-id="' . $modalId . '_modal"
				        data-modal-background-click="disabled"
				        data-modal-prefix-class="pwt"
				        title="' . Text::_($buttonText) . '"
				        onclick="jQuery(\'iframe#pwtImageFrame-' . $modalId . '\').attr(\'src\', \'' . $link . '\'); pwtImage.setTargetId(\'' . $modalId . '\')"
				        type="button">
					<span class="icon-list icon-white"></span>
					' . Text::_($buttonText) . '
				</button>

				<!-- Reset button -->
				<button type="button" class="btn ' . ($imagePreview ? '' : ' hidden') . '" id="' . $modalId . '_clear" onclick="pwtImage.clearImage(\'' . $modalId . '\');">
					<span class="icon-remove"></span>
					' . Text::_('JCLEAR') . '
				</button>
			</div>
			';

if ((bool) $multiple)
{
	$modal .= HTMLHelper::_('link', 'index.php', Text::_('COM_PWTIMAGE_ADD_ROW'), array('class' => 'btn btn-success', 'id' => 'addmore', 'onclick' => 'pwtImage.addRepeatImage(); return false;'));
}

$modal .= '<div id="' . $modalId . '_modal" class="pwt-component is-hidden">
				' . HTMLHelper::_('iframe', Uri::root() . 'index.php?option=com_pwtimage&amp;view=image&layout=iframe&amp;tmpl=component', 'pwtmodel', 'id="pwtImageFrame-' . $modalId . '"') . '
			</div>';

echo $modal;
