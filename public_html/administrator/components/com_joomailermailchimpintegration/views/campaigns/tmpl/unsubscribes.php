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
defined('_JEXEC') or die('Restricted Access'); ?>
<?php echo $this->sidebar; ?>
<form action="index.php?option=com_joomailermailchimpintegration&view=campaigns" method="post" name="adminForm" id="adminForm">
    <table class="adminlist">
        <thead>
            <tr>
                <th width="5">#</th>
                <th width="100">
                    <?php echo JText::_('JM_DATE'); ?>
                </th>
                <th class="alignLeft" width="250" nowrap="nowrap">
                    <?php echo JText::_('JM_EMAIL_ADDRESS'); ?>
                </th>
                <th class="alignLeft">
                    <?php echo JText::_('JM_REASON'); ?>
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
        foreach ($this->data['unsubscribes'] as $index => $item) { ?>
            <tr class="<?php echo "row$k"; ?>">
                <td>
                    <?php echo ($index + 1 + $this->limitstart); ?>
                </td>
                <td class="alignLeft" nowrap="nowrap">
                    <?php echo JHTML::_('date', date('Y-m-d H:i:s', strtotime($item['timestamp'])), JText::_('DATE_FORMAT_LC1')); ?>
                </td>
                <td class="alignLeft">
                    <a href="mailto:<?php echo $item['email_address']; ?>"><?php echo $item['email_address']; ?></a>
                </td>
                <td class="alignLeft">
                    <?php echo $item['reason']; ?>
                </td>
            </tr>
            <?php
            $k = 1 - $k;
        } ?>
    </table>
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="campaigns" />
    <input type="hidden" name="layout" value="<?php echo $this->input->getString('layout');?>" />
    <input type="hidden" name="cid" value="<?php echo $this->input->getString('cid');?>" />
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
