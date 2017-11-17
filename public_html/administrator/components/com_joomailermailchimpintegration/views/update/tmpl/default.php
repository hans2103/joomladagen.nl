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
*
* This file is based on AdminTools' default.php from Nicholas K. Dionysopoulos
* @copyright Copyright (c)2010 Nicholas K. Dionysopoulos
**/

// no direct access
defined('_JEXEC') or die('Restricted Access');

JHTML::_('behavior.modal');

echo $this->sidebar;

if (!$this->updates->supported) {
    $overview_class = 'notok';
    $mode = 'unsupported';
} else if ($this->updates->update_available) {
    $overview_class = 'update';
    $mode = 'update';
} else {
    $overview_class = 'ok';
    $mode = 'ok';
} ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    var button1 = $j('#updateform .submit');
    var button2 = $j('#requeryform .submit');
    button1Width = parseInt(button1.outerWidth());
    button2Width = parseInt(button2.outerWidth());
    if (button1Width > button2Width) {
        button2.css('width', button1Width + 2);
    } else {
        button1.css('width', button2Width + 2);
    }
});
</script>
<div class="note <?php echo $overview_class; ?>">
    <h3>
        <?php switch($mode):
        case 'ok': ?>
            <?php echo JText::_('JM_LBL_UPDATE_NOUPGRADESFOUND') ?>
            <?php    break;
        case 'update': ?>
            <?php echo JText::_('JM_LBL_UPDATE_UPGRADEFOUND') ?>
            <?php    break;
        default: ?>
            <?php echo JText::_('JM_LBL_UPDATE_NOTAVAILABLE') ?>
            <?php endswitch; ?>
    </h3>
</div>
<?php if ($mode != 'unsupported'): ?>
    <div id="version_info_container">
        <table id="version_info_table" class="ui-corner-all">
            <tr>
                <td class="label"><?php echo JText::_('JM_LBL_UPDATE_EDITION'); ?></td>
                <td colspan="3">
                    <strong>joomlamailer MailChimp integration</strong>
                </td>
            </tr>
            <tr>
                <td class="label"><?php echo JText::_('JM_LBL_UPDATE_YOURVERSION') ?></td>
                <td>
                    <span class="version"><?php echo $this->updates->current_version ?></span>
                    <span class="version-status">
                        (<?php echo JText::_('JM_LBL_UPDATE_STATUS_' . strtoupper($this->updates->current_status)); ?>)
                    </span>
                </td>
                <td colspan="2">
                    <?php echo JText::_('JM_LBL_UPDATE_RELEASEDON') ?>
                    <span class="reldate"><?php echo $this->updates->current_date ?></span>
                </td>
            </tr>
            <tr>
                <td class="label"><?php echo JText::_('JM_LBL_UPDATE_LATESTVERSION') ?></td>
                <td>
                    <span class="version"><?php echo $this->updates->latest_version ?></span>
                    <span class="version-status">
                        (<?php echo JText::_('JM_LBL_UPDATE_STATUS_' . strtoupper($this->updates->status)); ?>)
                    </span>
                </td>
                <td colspan="2">
                    <?php echo JText::_('JM_LBL_UPDATE_RELEASEDON') ?>
                    <span class="reldate"><?php echo $this->updates->latest_date ?></span>
                </td>
            </tr>
            <tr>
                <td class="label"><?php echo JText::_('JM_LBL_UPDATE_PACKAGELOCATION') ?></td>
                <td colspan="3">
                    <a href="<?php echo $this->updates->packageUrl; ?>">
                        <?php echo $this->updates->packageUrlShort; ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td class="label"><?php echo JText::_('JM_LBL_UPDATE_RELEASE_NOTES') ?></td>
                <td colspan="3">
                    <a href="<?php echo $this->updates->infoUrl; ?>" class="modal" rel="{handler: 'iframe', size: {x: 980, y: 750} }">
                        <?php echo $this->updates->infoUrlShort; ?>
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <div id="updater-buttons">
        <table width="100%">
            <?php if ($mode == 'update'): ?>
                <tr>
                    <td></td>
                    <td width="200">
                        <form action="index.php" method="post" id="updateform">
                            <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
                            <input type="hidden" name="controller" value="update" />
                            <input type="hidden" name="task" value="update" />
                            <input type="submit" class="submit" value="<?php echo JText::_('JM_LBL_UPDATE_UPDATENOW'); ?>" />
                        </form>
                    </td>
                    <td></td>
                </tr>
                <?php endif; ?>
            <tr>
                <td></td>
                <td width="200">
                    <form action="index.php?option=com_joomailermailchimpintegration&view=update" method="post" id="requeryform">
                        <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
                        <input type="hidden" name="view" value="update" />
                        <input type="hidden" name="task" value="force" />
                        <input type="submit" class="submit" value="<?php echo JText::_('JM_LBL_UPDATE_FORCE'); ?>" />
                    </form>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <br />
<?php endif;
echo $this->sidebar ? '</div>' : '';
