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

echo $this->sidebar;

$listid = $this->input->getString('listid', 0);
foreach($this->lists['lists'] as $list) {
    if ($list['id'] == $listid) {
        $listName = $list['name'];
        break;
    }
}

$type = $this->input->getString('type', 's');
switch ($type) {
    case 's':
        $state = JText::_('JM_ACTIVE_SUBSCRIBERS');
        break;
    case 'u':
        $state = JText::_('JM_SUBSCRIBERS') . ' ' . JText::_('JM_STATE_UNSUBSCRIBED');
        break;
    case 'c':
        $state = JText::_('JM_SUBSCRIBERS') . ' ' . JText::_('JM_STATE_CLEANED');
        break;
} ?>
<h3><?php echo $listName;?> - <?php echo $this->members['total_items'] . ' ' . $state;?></h3>

<?php if (!count($this->members['members'])) {
    echo '<p>' . JText::_('JM_RECIPIENT_LIST_EMPTY') . '</p>';
    return;
}?>
<form action="index.php?option=com_joomailermailchimpintegration&view=subscribers&listid=<?php echo $this->input->getString('listid');?>&type=<?php echo $this->input->getString('type');?>" method="post" id="adminForm" name="adminForm">
    <?php if (count($this->members['members'])) { ?>
        <table class="adminlist">
            <thead>
                <tr>
                    <th width="15">#</th>
                    <?php if ($type == 's') { ?>
                        <th width="15">
                            <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                        </th>
                        <?php } ?>
                    <th width="150" nowrap="nowrap">
                        <?php echo JText::_('JM_EMAIL_ADDRESS'); ?>
                    </th>
                    <th>
                        <?php echo JText::_('JM_NAME'); ?>
                    </th>
                    <th width="110" nowrap="nowrap">
                        <?php echo JText::_('JM_MEMBER_RATING'); ?>
                    </th>
                    <th width="110" nowrap="nowrap">
                        <?php echo JText::_('JM_DATE'); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="15">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot><?php
            $k = 0; ?>
            <tbody><?php
            foreach ($this->members['members'] as $index => $user) { ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td align="center">
                        <?php echo $index + 1 + $this->limitstart; ?>
                    </td>
                    <?php if ($type == 's') { ?>
                        <td>
                            <input type="checkbox" name="emails[]" id="cb<?php echo $index;?>" value="<?php echo $user->email; ?>" onclick="Joomla.isChecked(this.checked);"/>
                        </td>
                        <?php } ?>
                    <td align="center" nowrap="nowrap">
                        <?php echo $user->email; ?>
                    </td>
                    <td>
                        <?php if ($user->id) { ?>
                            <a href="index.php?option=com_joomailermailchimpintegration&view=subscriber&listId=<?php echo $listid; ?>&uid=<?php echo $user->id; ?>&email=<?php echo urlencode($user->email); ?>">
                                <?php echo $user->name; ?>
                            </a>
                        <?php } ?>
                    </td>
                    <td align="center">
                        <span class="ratingBG">
                            <?php $ratingWidth = round($user->member_rating * 20);?>
                            <span class="rating-value" style="width:<?php echo $ratingWidth;?>%"></span>
                        </span>
                    </td>
                    <td align="center" nowrap="nowrap">
                        <?php echo JHTML::_('date', $user->timestamp_opt, JText::_('DATE_FORMAT_LC2')); ?>
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
            </tbody>
        </table>
        <?php } ?>
    <input type="hidden" name="listId" value="<?php echo $this->input->getString('listId');?>" />
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="subscribers" />
</form>

<?php echo $this->sidebar ? '</div>' : ''; ?>
