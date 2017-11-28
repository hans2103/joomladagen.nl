<?php
/**
 * @package    Pwtimage
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

// Load PWT CSS
HTMLHelper::_('stylesheet', 'com_pwtimage/pwtimage.min.css', array('relative' => true, 'version' => 'auto'));
?>

<div class="pwt-component-section">

	<!-- PWT branding -->
	<div class="pwt-section pwt-section--border-bottom">
		<div class="pwt-flag-object">
			<div class="pwt-flag-object__aside">
				<?php echo HTMLHelper::_('image', 'com_pwtimage/PWT-image.png', 'PWT Image', array('width' => 160), true); ?>
			</div>
			<div class="pwt-flag-object__body">
				<p class="pwt-heading"><?php echo Text::_('COM_PWTIMAGE_ABOUT_PWTIMAGE_HEADER'); ?></p>
				<p><?php echo Text::_('COM_PWTIMAGE_ABOUT_PWTIMAGE_DESCRIPTION'); ?></p>
				<p><a href="https://extensions.perfectwebteam.com/pwt-image"><?php echo Text::_('COM_PWTIMAGE_ABOUT_PWTIMAGE_WEBSITE'); ?></a></p>
			</div>
		</div>
	</div><!-- .pwt-branding -->

	<div class="pwt-section">
		<p>
			<a class="pwt-button pwt-button--primary" href="<?php echo Uri::Base(); ?>/index.php?option=com_plugins&view=plugins&filter[search]=PWT+Image"><?php echo Text::_('COM_PWTIMAGE_ABOUT_PLUGIN_SETTINGS'); ?></a>

			<a class="pwt-button pwt-button--primary" href="https://extensions.perfectwebteam.com/pwt-image/documentation"><?php echo Text::_('COM_PWTIMAGE_DOCUMENTATION_LINK'); ?></a>
		</p>
	</div>

	<div class="pwt-section pwt-section--border-top">
		<p><strong><?php echo Text::sprintf('COM_PWTIMAGE_VERSION', '</strong>1.0.0'); ?></p>
	</div>

</div><!-- .pwt-content -->
