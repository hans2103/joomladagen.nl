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

JHTML::_('behavior.modal');
$params = JComponentHelper::getParams('com_joomailermailchimpintegration');
$MCapi = $params->get('params.MCapi');
$sugar_name = $params->get('params.sugar_name', 0);
$sugar_pwd = $params->get('params.sugar_pwd', 0);
$sugar_url = $params->get('params.sugar_url', 0);
$highrise_url = $params->get('params.highrise_url', 0);
$highrise_api_token = $params->get('params.highrise_api_token', 0);

$JoomlamailerMC = new JoomlamailerMC();

if (!$MCapi) {
    echo $JoomlamailerMC->apiKeyMissing();
    return;
}
if (!$JoomlamailerMC->pingMC()) {
    echo $JoomlamailerMC->apiKeyMissing(1);
    return;
}

if ($sugar_name && $sugar_pwd && $sugar_url) {
    $CRMauth = new CRMauth();
    echo $CRMauth->checkSugarLogin();
}
if ($highrise_url && $highrise_api_token) {
    $CRMauth = new CRMauth();
    echo $CRMauth->checkHighriseLogin();
} ?>

<?php echo $this->sidebar; ?>
<div id="ajax_response" style="display: none"></div>
<div id="message" style="display: none"></div>
<div id="form_container" style="display: none">
    <form action="index.php?option=com_joomailermailchimpintegration&view=sync" method="post" name="adminForm" id="adminForm">
        <?php  // no lists created yet?
        if (empty($this->lists['total_items'])) {
            echo JText::_('JM_CREATE_A_LIST');
            $i = $n = 1;
        } else { ?>
            <div class="note">
                <table>
                    <tr>
                        <td valign="top"><?php echo JText::_('JM_NOTE'); ?>:</td>
                        <td valign="top">
                            <?php echo JText::_('JM_ADDING_USERS_TAKES_SOME_TIME'); ?>
                            <br />
                            <?php echo JText::_('JM_ADDING_USERS_AGAIN_MAY_CAUSE_TROUBLE'); ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="clearfix">
                <div class="left">
                    <select name="listId" id="listId" class="left" style="width: 250px;">
                        <?php if ($this->lists['total_items'] > 1) { ?>
                            <option value=""><?php echo JText::_('JM_SELECT_A_LIST_TO_ASSIGN_THE_USERS_TO'); ?></option>
                        <?php }
                        foreach ($this->lists['lists'] as $list) { ?>
                            <option value="<?php echo $list['id'];?>"><?php echo $list['name'];?></option><?php
                        } ?>
                    </select>
                    <div id="addUsersLoader" class="left">
                        <img src="<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/loader_16.gif"/>
                    </div>

                    <div class="margin-right-10 left" style="margin-top: 5px;">
                        <?php echo JText::_('JM_FILTERS'); ?>:
                    </div>
                    <input type="text" name="search" id="search" class="margin-right-10 left" value="<?php echo $this->filters['search'];?>" onchange="document.adminForm.submit();" />
                    <?php echo $this->filters['type'];?>
                    <div id="calendarContainer" class="margin-right-10 left">
                        <?php echo $this->filters['filter_date'];?>
                    </div>
                    <button onclick="this.form.submit();" class="btn margin-right-10 left"><?php echo JText::_('JM_GO'); ?></button>
                    <button onclick="document.getElementById('search').value='';document.getElementById('filter_type').selectedIndex = 0;this.form.getElementById('filter_date').value='';this.form.getElementById('filter_logged').value='0';this.form.submit();" class="btn left"><?php echo JText::_('JM_RESET');?></button>
                </div>
                <div class="right">
                    <div class="legendIcon" id="alreadyAssigned"><?php echo JText::_('JM_ALREADY_ASSIGNED_TO_LIST'); ?></div><br />
                    <div class="legendIcon" id="infoUpdated"><?php echo JText::_('JM_EMAIL_ADDRESS_CHANGED'); ?></div><br />
                    <?php /*<div class="legendIcon" id="suppressed"><?php echo JText::_('JM_SUPPRESSED'); ?></div>*/ ?>
                </div>
            </div>

            <div id="editcell">
                <table class="adminlist">
                    <thead>
                        <tr>
                            <th width="5">
                                <?php echo JText::_('JM_ID'); ?>
                            </th>
                            <th width="20">
                                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
                            </th>
                            <?php if (($sugar_name && $sugar_pwd) || ($highrise_url && $highrise_api_token)) { ?>
                                <th>
                                    <?php echo JText::_('JM_CRM'); ?>
                                </th>
                                <?php } ?>
                            <th style="text-align:left;">
                                <?php echo JText::_('JM_NAME'); ?>
                            </th>
                            <th style="text-align:left;">
                                <?php echo JText::_('JM_USERNAME'); ?>
                            </th>
                            <th style="text-align:left;">
                                <?php echo JText::_('JM_EMAIL_ADDRESS'); ?>
                            </th>
                            <th width="50">
                                <?php echo JText::_('JM_ENABLED'); ?>
                            </th>
                            <th width="150">
                                <?php echo JText::_('JM_USERGROUP'); ?>
                            </th>
                            <th width="150">
                                <?php echo JText::_('JM_LAST_VISIT'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <td colspan="15">
                                <?php echo $this->get('Pagination')->getListFooter(); ?>
                            </td>
                        </tr>
                    </tfoot>
                    <?php
                    $k = 0;
                    for ($i = 0, $n = count($this->items); $i < $n; $i++) {
                        $row = $this->items[$i];
                        $checked = JHTML::_('grid.id', $i, $row->id . '" class="userCB');
                        $link = JRoute::_('index.php?option=com_users&view=user&layout=edit&id=' . $row->id);
                        if ($row->block == 0){
                            $blocked = '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/tick_16.png" border="0" alt="Enabled" title="Enabled" />';
                        } else {
                            $blocked = '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/cross.png" width="16" height="16" border="0" alt="Blocked" title="Blocked" />';
                        }
                        $user_subscribed = '';
                        ?>

                        <tr class="<?php echo "row$k"; ?>" id="row_<?php echo $row->id;?>" <?php echo $user_subscribed; ?>>
                            <td>
                                <?php echo $row->id; ?>
                            </td>
                            <td>
                                <?php echo $checked;?>
                            </td>
                            <?php if (($sugar_name && $sugar_pwd) || ($highrise_url && $highrise_api_token)) { ?>
                                <td>
                                    <?php
                                    if (isset($this->CRMusers['sugar']) && in_array($row->id, $this->CRMusers['sugar'])){
                                        echo '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/dot_blue.png" border="0" alt="SugarCRM" title="Added to SugarCRM" />&nbsp;';
                                    }
                                    if (isset($this->CRMusers['highrise']) && in_array($row->id, $this->CRMusers['highrise'])) {
                                        echo '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/dot_green.png" border="0" alt="Highrise" title="Added to Highrise" />';
                                    }
                                    ?>
                                </td>
                                <?php } ?>
                            <td style="text-align:left">
                                <a href="<?php echo $link; ?>"  id="link_<?php echo $row->id;?>" <?php echo $user_subscribed; ?>><?php echo $row->name; ?></a>
                            </td>
                            <td style="text-align:left">
                                <?php echo $row->username; ?>
                            </td>
                            <td style="text-align:left">
                                <?php echo $row->email; ?>
                            </td>
                            <td>
                                <?php echo $blocked; ?>
                            </td>
                            <td>
                                <?php echo $row->groupname; ?>
                            </td>
                            <td>
                                <?php echo ($row->lastvisitDate == '0000-00-00 00:00:00') ? JText::_('JM_NEVER') : $row->lastvisitDate; ?>
                            </td>
                        </tr>
                        <?php
                        $k = 1 - $k;
                    }
                    ?>
                </table>

                <?php if ($sugar_name && $sugar_pwd) { ?>
                    <p>
                        <img src="<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/dot_blue.png" border="0" alt="SugarCRM" title="Added to SugarCRM" />
                        &nbsp;
                        <?php echo JText::_('JM_USER_ADDED_TO_SUGAR');?>
                    </p>
                    <?php } ?>
                <?php if ($highrise_url && $highrise_api_token){ ?>
                    <p>
                        <img src="<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/dot_green.png" border="0" alt="SugarCRM" title="Added to SugarCRM" />
                        &nbsp;
                        <?php echo JText::_('JM_USER_ADDED_TO_HIGHRISE');?>
                    </p>
                    <?php } ?>

            </div>

            <?php } // end - no list created ?>

        <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" id="boxchecked" value="0" />
        <input type="hidden" name="controller" id="controller" value="sync" />
        <input type="hidden" name="type" value="sync" />
        <input type="hidden" name="total" id="total" value="<?php echo $this->total;?>" />
    </form>

</div>
<?php
if (($i++) == $n) { // hide user list until page completed loading ?>
    <script type="text/javascript">$j('#form_container').css('display', '');</script>
    <?php
}?>
<?php echo $this->sidebar ? '</div>' : ''; ?>
