<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

// Load PWT CSS
HTMLHelper::_('stylesheet', 'com_pwtseo/pwtseo.css', array('relative' => true, 'version' => 'auto'));
?>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
    <div class="pwt-component-section">

        <div class="pwt-section pwt-section--border-bottom">
            <div class="pwt-flag-object">
                <div class="pwt-flag-object__aside">
					<?php echo JHtml::_('image', 'com_pwtseo/PWT-seo.png', 'PWT Image', array('width' => 160), true); ?>
                </div>
                <div class="pwt-flag-object__body">
                    <p class="pwt-heading">
						<?php echo JText::_('COM_PWTSEO_ABOUT_PWTSEO_HEADER'); ?>
                    </p>
                    <p>
						<?php echo JText::_('COM_PWTSEO_ABOUT_PWTSEO_DESCRIPTION'); ?>
                    </p>
                    <p>
                        <a href="https://extensions.perfectwebteam.com/pwt-seo"
                           target="_blank">
							<?php echo JText::_('COM_PWTSEO_ABOUT_PWTSEO_WEBSITE'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <div class="pwt-section">
            <p>
                <a class="pwt-button pwt-button--primary"
                   href="<?php echo JUri::Base(); ?>index.php?option=com_plugins&view=plugins&filter[search]=PWT+SEO">
					<?php echo JText::_('COM_PWTSEO_ABOUT_PLUGIN_SETTINGS'); ?>
                </a>
                -
                <a class="pwt-button pwt-button--primary"
                   target="_blank"
                   href="https://extensions.perfectwebteam.com/pwt-seo/documentation">
					<?php echo JText::_('COM_PWTSEO_DOCUMENTATION_LINK'); ?>
                </a>
            </p>
        </div>

        <div class="pwt-section pwt-section--border-top">
            <p>
                <strong>
                <?php echo JText::sprintf('COM_PWTSEO_VERSION', '</strong>1.0.0'); ?>
            </p>
        </div>

    </div>
</div>
