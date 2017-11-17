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
                <th nowrap="nowrap">
                    <?php echo JText::_('JM_LINK_URL'); ?>
                </th>
                <th width="100" nowrap="nowrap">
                    <?php echo JText::_('JM_TOTAL_CLICKS'); ?>
                </th>
                <th width="100" nowrap="nowrap">
                    <?php echo JText::_('JM_UNIQUE_CLICKS'); ?>
                </th>

                <th width="100" nowrap="nowrap">
                    <?php echo JText::_('JM_CLICK_PERCENTAGE'); ?>
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
        <tbody>
        <?php
        $k = 0;
        foreach ($this->data['urls_clicked'] as $index => $item) {
            $link = 'index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=clickedlinkdetails&cid=' . $this->cid . '&id=' . $item['id']; ?>
            <tr class="<?php echo "row$k"; ?>">
                <td align="center">
                    <?php echo $index + 1 + $this->limitstart; ?>
                </td>
                <td>
                    <a href="<?php echo $link; ?>">
                        <?php echo $item['url']; ?>
                    </a>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php echo $item['total_clicks']; ?>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php echo $item['unique_clicks']; ?>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php echo round($item['click_percentage'], 1); ?>
                </td>
            </tr>
            <?php
            $k = 1 - $k;
        } ?>
        </tbody>
    </table>
    <input type="hidden" name="option" value="com_joomailermailchimpintegration">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="controller" value="campaigns">
    <input type="hidden" name="layout" value="<?php echo $this->input->getString('layout');?>">
    <input type="hidden" name="cid" value="<?php echo $this->input->getString('cid');?>">
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
