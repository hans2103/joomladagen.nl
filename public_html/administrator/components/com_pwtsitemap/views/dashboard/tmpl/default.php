<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

// Load PWT CSS
HTMLHelper::_('stylesheet', 'com_pwtsitemap/pwtsitemap.css', array('relative' => true, 'version' => 'auto'));
?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<div class="pwt-component-section">

		<!-- PWT branding -->
		<div class="pwt-section pwt-section--border-bottom">
			<div class="pwt-flag-object">
				<div class="pwt-flag-object__aside">
					<?php echo HTMLHelper::_('image', 'com_pwtsitemap/PWT-Sitemap.png', 'PWT Sitemap', array('width' => 160), true); ?>
				</div>
				<div class="pwt-flag-object__body">
					<p class="pwt-heading"><?php echo Text::_('COM_PWTSITEMAP_ABOUT_PWTSITEMAP_HEADER'); ?></p>
					<p><?php echo Text::_('COM_PWTSITEMAP_ABOUT_PWTSITEMAP_DESCRIPTION'); ?></p>
					<p><a href="https://extensions.perfectwebteam.com/pwt-sitemap"><?php echo Text::_('COM_PWTSITEMAP_ABOUT_PWTSITEMAP_WEBSITE'); ?></a></p>
				</div>
			</div>
		</div><!-- .pwt-branding -->

		<div class="pwt-section">
			<p>
				<a class="pwt-button pwt-button--primary" href="https://extensions.perfectwebteam.com/pwt-sitemap/documentation"><?php echo Text::_('COM_PWTSITEMAP_DOCUMENTATION_LINK'); ?></a>
			</p>
		</div>

		<div class="pwt-section pwt-section--border-top">
			<p><strong><?php echo Text::sprintf('COM_PWTSITEMAP_VERSION', '</strong>1.0.1'); ?></p>
		</div>

	</div><!-- .pwt-content -->
</div>
