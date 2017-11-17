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

echo $this->sidebar; ?>
<form action="index.php?option=com_joomailermailchimpintegration&view=campaigns" method="post" name="adminForm" id="adminForm">
    <table class="adminlist">
        <thead>
            <tr>
                <th width="10">#</th>
                <th>
                    <?php echo JText::_('JM_NAME'); ?>
                </th>
                <th width="100" nowrap="nowrap">
                    <?php echo JText::_('JM_EMAIL_ADDRESS'); ?>
                </th>
                <th width="100" nowrap="nowrap">
                    <?php echo JText::_('JM_TOTAL_TIMES_OPENED'); ?>
                </th>
                <th width="20" nowrap="nowrap">
                    <?php echo JText::_('JM_ID'); ?>
                </th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <td colspan="15">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <?php
        $k = 0;
        foreach($this->data['sent_to'] as $index => $item) {
            $user = $this->getModel()->getUserDetails($item['email_address']); ?>
            <tr class="<?php echo "row$k"; ?>">
                <td>
                    <?php echo $index + 1 + $this->limitstart; ?>
                </td>
                <td class="alignLeft">
                    <?php if ($user) { ?>
                    <a href="index.php?option=com_joomailermailchimpintegration&view=subscriber&uid=<?php echo $user->id; ?>&email=<?php echo urlencode($item['email_address']); ?>">
                        <?php echo $user->name; ?>
                    </a>
                    <?php } else {
                        if (!empty($item['merge_fields']['FNAME']) && !empty($item['merge_fields']['LNAME'])) {
                            echo $item['merge_fields']['FNAME'] . ' ' . $item['merge_fields']['LNAME'] . ' ';
                        }
                        echo '<span class="small grey">(' . JText::_('JM_UNREGISTERED_USER') . ')</span>';
                    } ?>
                </td>
                <td nowrap="nowrap">
                    <?php echo $item['email_address']; ?>
                </td>
                <td nowrap="nowrap">
                    <?php echo $item['open_count']; ?>
                </td>
                <td>
                    <?php echo ($user) ? $user->id : '-'; ?>
                </td>
            </tr>
            <?php
            $k = 1 - $k;
        } ?>
    </table>
    <input type="hidden" name="option" value="com_joomailermailchimpintegration">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="controller" value="campaigns">
    <input type="hidden" name="layout" value="<?php echo $this->input->getString('layout');?>">
    <input type="hidden" name="cid" value="<?php echo $this->input->getString('cid');?>">
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
