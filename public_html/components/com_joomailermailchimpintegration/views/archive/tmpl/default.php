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

if (empty($this->items['total_items']) || empty($this->items['campaigns'])) {
    echo JText::_('JM_NO_CAMPAIGNS');
    return;
} ?>
<h2 class="componentheading">
    <?php echo ($this->menuParams->get('show_page_heading') && $this->menuParams->get('page_heading'))
        ? $this->menuParams->get('page_heading') : JText::_('JM_CAMPAIGN_ARCHIVE'); ?>
</h2>

<table class="adminlist" width="100%">
    <thead>
        <tr>
            <th width="20" align="center">#</th>
            <th align="left"><?php echo JText::_('JM_SUBJECT'); ?></th>
            <th width="120"><?php echo JText::_('JM_SENT_DATE'); ?></th>
        </tr>
    </thead>
    <tbody><?php
    $type = ($this->displayType == 0) ? 'class="modal" rel="{handler: \'iframe\', size: {x: 980, y: 550} }"' : 'target="_blank"';
    foreach ($this->items['campaigns'] as $index => $campaign) { ?>
        <tr>
            <td align="center"><?php echo ($index + 1); ?></td>
            <td align="left" nowrap="nowrap">
                <a href="<?php echo $campaign['archive_url']; ?>" <?php echo $type; ?>>
                    <?php echo $campaign['settings']['subject_line']; ?>
                </a>
            </td>
            <td align="right" nowrap="nowrap">
                <?php echo JHTML::_('date', date('Y-m-d H:i:s', strtotime($campaign['send_time'])), JText::_('DATE_FORMAT_LC1')); ?>
            </td>
        </tr><?php
    } ?>
    </tbody>
</table>
