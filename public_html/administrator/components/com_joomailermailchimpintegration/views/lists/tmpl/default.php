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
JHTML::_('behavior.tooltip');

$ttImage = JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/info.png';

$params = JComponentHelper::getParams('com_joomailermailchimpintegration');
$MCapi  = $params->get('params.MCapi');
$JoomlamailerMC = new JoomlamailerMC();

if (!$MCapi) {
    echo '<table>' . $JoomlamailerMC->apiKeyMissing();
    return;
}
if (!$JoomlamailerMC->pingMC()) {
	echo '<table>' . $JoomlamailerMC->apiKeyMissing(1);
    return;
} ?>
<?php echo $this->sidebar; ?>
<form action="index.php" method="post" id="adminForm"><?php
    if (empty($this->lists['total_items'])) {
        echo '<h2>' . JText::_('JM_CREATE_A_LIST') . '</h2></form>';
        return;
    } else { ?>
    <div id="editcell">
        <table class="adminlist">
	        <thead>
	            <tr>
		        <th width="15">#</th>
		        <th nowrap="nowrap">
		            <?php echo JText::_('JM_NAME'); ?>
		        </th>
		        <th width="100" nowrap="nowrap">
		            <?php echo JText::_('JM_MERGE_FIELDS'); ?>
		        </th>
		        <th width="100" nowrap="nowrap">
		            <?php echo JText::_('JM_CUSTOM_FIELDS'); ?>
		        </th>
		        <th width="100">
		            <?php echo JText::_('JM_LIST_RATING');
		              echo '<a href="http://kb.mailchimp.com/lists/growth/best-practices-for-lists#List-Management" target="_blank">';
		              echo '&nbsp;'.JHTML::tooltip(JText::_('JM_TOOLTIP_LIST_RATING'), JText::_('JM_LIST_RATING'), $ttImage, '');
		              echo '</a>';
                        ?>
		        </th>
		        <th width="8%">
		            <?php echo JText::_('JM_SUBSCRIBERS'); ?>
		        </th>
		        <th width="8%">
		            <?php echo JText::_('JM_UNSUBSCRIBED'); ?>
		        </th>
		        <th width="8%">
		            <?php echo JText::_('JM_CLEANED'); ?>
		        </th>
	            </tr>
	        </thead>
            <tbody>
	        <?php
	        $k = 0;
            foreach ($this->lists['lists'] as $index => $list) {
	            $checked = JHTML::_('grid.id', $index, $list['id']); ?>
	            <tr class="<?php echo "row$k"; ?>">
		            <td align="center">
		                <?php echo $index+1; ?>
		            </td>
		            <td nowrap="nowrap">
		                <a href="index.php?option=com_joomailermailchimpintegration&view=subscribers&listid=<?php echo $list['id'];?>&type=s">
			                <?php echo $list['name']; ?>
		                </a>
		            </td>
		            <td align="center">
		                <a href="index.php?option=com_joomailermailchimpintegration&view=fields&listId=<?php echo $list['id'];?>&name=<?php echo urlencode($list['name']);?>">
                            <?php echo JText::_('JM_MANAGE'); ?>
                        </a>
		            </td>
		            <td align="center">
		                <a href="index.php?option=com_joomailermailchimpintegration&view=groups&listId=<?php echo $list['id'];?>&name=<?php echo urlencode($list['name']);?>">
                            <?php echo JText::_('JM_MANAGE'); ?>
                        </a>
		            </td>
		            <td align="center">
		                <a href="http://kb.mailchimp.com/lists/growth/best-practices-for-lists#List-Management" target="_blank" title="<?php echo JText::_('JM_WHAT_IS_LIST_RATING');?>">
                            <span class="ratingBG">
                                <?php $ratingWidth = round($list['list_rating'] * 20);?>
                                <span class="rating-value" style="width:<?php echo $ratingWidth;?>%"></span>
                            </span>
		                </a>
		            </td>
		            <td align="center">
		                <a href="index.php?option=com_joomailermailchimpintegration&view=subscribers&listid=<?php echo $list['id'];?>&type=s">
			                <?php echo $list['stats']['member_count']; ?>
		                </a>
		            </td>
		            <td align="center">
		                <a href="index.php?option=com_joomailermailchimpintegration&view=subscribers&listid=<?php echo $list['id'];?>&type=u">
			                <?php echo $list['stats']['unsubscribe_count']; ?>
		                </a>
		            </td>
		            <td align="center">
		                <a href="index.php?option=com_joomailermailchimpintegration&view=subscribers&listid=<?php echo $list['id'];?>&type=c">
			                <?php echo $list['stats']['cleaned_count']; ?>
		                </a>
		            </td>
	            </tr><?php
	            $k = 1 - $k;
	        } ?>
            </tbody>
	    </table>
    </div>
    <?php } ?>
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="lists" />
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
