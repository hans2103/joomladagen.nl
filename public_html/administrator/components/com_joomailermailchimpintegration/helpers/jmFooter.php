<?php
/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined('_JEXEC') or die('Restricted Access');

require_once(__DIR__ . '/../models/update.php');
$model = new joomailermailchimpintegrationModelUpdate();

if (stripos(JOOMLAMAILER_VERSION, 'beta') !== false || preg_match('/RC\d/', JOOMLAMAILER_VERSION)) {
    $app = JFactory::getApplication();
    $app->enqueueMessage('This is a pre-release version and not intended for production sites. Please test it thoroughly and report any bugs on <a href="http://www.joomlamailer.com/support/default-forum/11-beta-3-x-mailchimp-api-v3" target="_blank">joomlamailer.com</a>. Thank you!', 'Version ' . JOOMLAMAILER_VERSION);
} ?>
<div style="clear:both;"></div>
<table width="100%">
    <tr>
	<td width="380" style="vertical-align:bottom; text-align:left;">
	    <a href="http://www.joomlamailer.com" title="Visit joomlamailer.com" target="_blank" style="position:relative;top:12px;">
            <img src="../media/com_joomailermailchimpintegration/backend/images/logo.png" alt="joomlamailer.com">
	    </a>
	</td>
	<td style="vertical-align:bottom;text-align:center;"><?php
        $liveupdateinfo = $model->getUpdates();
        if ($liveupdateinfo->supported) {
            if ($liveupdateinfo->update_available) { ?>
                <a href="index.php?option=com_joomailermailchimpintegration&view=update">
                    <span style="color:#ff0000;">
                        <?php echo JText::_('JM_UPDATE_AVAILABLE') . ' ' . $liveupdateinfo->latest_version; ?>
                    </span>
                </a>
                <div style="position:fixed;bottom:0;left:50%;display:block;width:45px;height:32px;z-index:999999;">
                    <a href="index.php?option=com_joomailermailchimpintegration&view=update" title="<?php echo JText::_('JM_UPDATE_AVAILABLE'); ?>">
                        <img src="../media/com_joomailermailchimpintegration/backend/images/freddie_32_right.png" alt="<?php echo JText::_('JM_UPDATE_AVAILABLE'); ?>">
                    </a>
                </div><?php
            } else { ?>
                <a href="index.php?option=com_joomailermailchimpintegration&view=update">
                    <?php echo JText::_('JM_UPDATE_IS_LATEST') . ' (' . JOOMLAMAILER_VERSION . ')'; ?>
                </a><?php
            }
        } else { ?>
            <a href="http://www.joomlamailer.com" target="_blank"><?php echo JText::_('JM_UPDATE_NOTSUPPORTED'); ?></a><?php
        } ?>
    </td>
	<td width="380" style="vertical-align:bottom;text-align:right;">
	    <a href="https://www.freakedout.de" target="_blank" title="Visit freakedout.de for more amazing Joomla! extensions">
            <img src="../media/com_joomailermailchimpintegration/backend/images/freakedout.png" alt="freakedout.de">
        </a>
	</td>
    </tr>
</table>
<div style="display: none;">
    <img src="../media/com_joomailermailchimpintegration/backend/images/loader_55.gif" alt="">
</div>
<br />
