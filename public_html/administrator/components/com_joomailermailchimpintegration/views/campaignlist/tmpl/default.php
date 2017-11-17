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

jimport('joomla.filesystem.file');
JHTML::_('behavior.modal');

$params = JComponentHelper::getParams('com_joomailermailchimpintegration');
$MCapi = $params->get('params.MCapi');
$JoomlamailerMC = new JoomlamailerMC();

if (!$MCapi) {
    echo '<table>' . $JoomlamailerMC->apiKeyMissing();
    return;
} else if (!$JoomlamailerMC->pingMC()) {
    echo '<table>' . $JoomlamailerMC->apiKeyMissing(1);
    return;
}

$limitstart	= $this->app->getUserStateFromRequest('campaignlist_limitstart', 'campaignlist_limitstart', 0, 'int');
$filter = $this->input->getString('filter_status', 'sent', '', 'string'); ?>
<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(pressbutton) {
    if (pressbutton == 'unschedule') {
        if (confirm('<?php echo JText::_('JM_ARE_YOU_SURE_TO_UNSCHEDULE_THE_SELECTED_CAMPAIGNS');?>?')){
            joomlamailerJS.functions.preloader();
            Joomla.submitform(pressbutton);
        }
    } else {
        joomlamailerJS.functions.preloader();
        Joomla.submitform(pressbutton);
    }
}
</script>
<?php echo $this->sidebar; ?>
<form action="index.php?option=com_joomailermailchimpintegration&view=campaignlist" method="post" name="adminForm" id="adminForm">
    <div id="filter"><?php
        if (isset($this->foldersDropDown) && JOOMLAMAILER_MANAGE_CAMPAIGNS) {
            echo JText::_('JM_SELECT_FOLDER') . ': ';
            echo $this->foldersDropDown;
        }
        echo JText::_('JM_SELECT_STATUS') . ':'; ?>
        <select onchange="document.adminForm.submit();" size="1" class="inputbox" id="filter_status" name="filter_status">
            <?php if (JOOMLAMAILER_CREATE_DRAFTS) { ?>
                <option value="save"<?php if ($filter == 'save') echo ' selected';?>><?php
                    echo JText::_('JM_SAVED'); ?></option><?php
            }
            if (JOOMLAMAILER_MANAGE_CAMPAIGNS) {
                foreach (array('paused', 'schedule', 'sending', 'sent') as $status) {
                    echo '<option value="' . $status . '"' . ($status == $filter ? ' selected' : '') . '>'
                        . JText::_('JM_' . strtoupper($status)) . '</option>';
                }
            } ?>
        </select>
    </div>
    <div style="clear: both;"></div>
    <?php
    if (!isset($this->campaigns[0])) {
        echo '<div>' . JText::_('JM_NO_CAMPAIGNS_WITH_THE_SELECTED_STATUS') . '</div>';
    } else { ?>
        <div id="editcell">
            <table class="adminlist">
                <thead>
                    <tr>
                        <th width="10">#</th>
                        <th width="20">
                            <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                        </th>
                        <th><?php echo JText::_('JM_NAME'); ?></th>
                        <th><?php echo JText::_('JM_SUBJECT'); ?></th>
                        <th width="75"><?php echo JText::_('JM_STATUS'); ?></th>
                        <th width="150"><?php echo ($filter == 'save') ? JText::_('JM_CREATION_DATE') : JText::_('JM_DELIVERY_DATE'); ?></th>
                        <?php if (!in_array($filter, array('save', 'schedule'))) { ?>
                            <th width="110"><?php echo JText::_('JM_TOTAL_RECIPIENTS'); ?></th>
                            <th width="100"><?php echo JText::_('JM_UNIQUE_OPENS'); ?></th>
                            <th width="100"><?php echo JText::_('JM_CLICKS'); ?></th><?php
                        } ?>
                        <th width="75"><?php echo JText::_('JM_ARCHIVE'); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="15">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot><?php
                $k = 0;
                foreach ($this->campaigns as $index => $campaign) {
                    $checked = JHTML::_('grid.id', $index, $campaign['id']);
                    $link = JRoute::_('index.php?option=com_joomailermailchimpintegration&controller=joomailermailchimpintegration&task=edit&listid=' . $campaign['id']); ?>
                    <tr class="<?php echo "row$k"; ?>">
                        <td align="center">
                            <?php echo ($index + 1 + $limitstart); ?>
                        </td>
                        <td>
                            <?php echo $checked;?>
                        </td>
                        <td align="center" nowrap="nowrap">
                            <?php echo (strlen($campaign['settings']['title']) > 50)
                                ? substr($campaign['settings']['title'], 0, 47) . '...'
                                : $campaign['settings']['title']; ?>
                        </td>
                        <td align="center">
                            <?php echo (strlen($campaign['settings']['subject_line']) > 50)
                                ? substr($campaign['settings']['subject_line'], 0, 47) . '...'
                                : $campaign['settings']['subject_line']; ?>
                        </td>
                        <td align="center">
                            <?php echo ($filter != 'save') ? JText::_($campaign['status']) : JText::_('JM_SAVED'); ?>
                        </td>
                        <td align="center">
                            <?php echo ($filter == 'save')
                                ? JHTML::_('date', date('Y-m-d H:i:s', $campaign['creation_date']), JText::_('DATE_FORMAT_LC2'))
                                : JHTML::_('date', date('Y-m-d H:i:s', strtotime($campaign['send_time'])), JText::_('DATE_FORMAT_LC2')); ?>
                        </td>
                        <?php if (!in_array($filter, array('save', 'schedule'))) { ?>
                            <td align="center">
                                <?php echo $campaign['emails_sent']; ?>
                            </td>
                            <td align="center">
                                <?php echo $campaign['report']['opens']['unique_opens']; ?>
                            </td>
                            <td align="center">
                                <?php echo $campaign['report']['clicks']['clicks_total']; ?>
                            </td>
                            <?php } ?>
                        <td align="center">
                            <a href="<?php echo $campaign['archive_url'];?>" target="_blank">
                                <img src="../media/com_joomailermailchimpintegration/backend/images/preview_32.png" alt="Preview" title="Preview" height="17"/>
                            </a>
                        </td>
                    </tr>
                    <?php
                    $k = 1 - $k;
                } ?>
            </table>
        </div>
        <?php } // end (if no campaigns) ?>
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="campaignlist" />
    <input type="hidden" name="type" value="<?php echo $filter;?>" />
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
