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
<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(pressbutton) {
	Joomla.submitform(pressbutton);
}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <h2 class="componentheading">
        <?php echo ($this->menuParams->get('show_page_heading') && $this->menuParams->get('page_heading'))
            ? $this->menuParams->get('page_heading') : JText::_('JM_CAMPAIGN_ARCHIVE'); ?>
    </h2>
	<table class="admintable">
        <colgroup>
            <col width="" />
            <col width="150" />
            <col width="150" />
        </colgroup><?php
        foreach ($this->lists['lists'] as $list) { ?>
            <tr>
                <td align="left" class="key" style="padding: 0 15px 0 0;">
                    <label for="listid"><?php echo $list['name']; ?></label>
                </td>
                <td style="padding: 0 15px 0 0;">
                    <input type="hidden" name="currentStatus[<?php echo $list['id']; ?>]" value="<?php echo ($list['currentUserIsSubscribed']) ? 1 : 0; ?>" />

                    <label for="lists_<?php echo $list['id']; ?>_yes">
                        <?php echo JText::_('JM_SUBSCRIBE'); ?>:
                        <input type="radio" name="lists[<?php echo $list['id']; ?>]"<?php echo ($list['currentUserIsSubscribed']) ? 'checked="checked"' : ''; ?> value="1" id="lists_<?php echo $list['id']; ?>_yes" />
                    </label>
                </td>
                <td>
                    <label for="lists_<?php echo $list['id']; ?>_no">
                        <?php echo JText::_('JM_UNSUBSCRIBE'); ?>:
                        <input type="radio" name="lists[<?php echo $list['id']; ?>]"<?php echo ($list['currentUserIsSubscribed']) ? '' : 'checked="checked"'; ?>  value="0" id="lists_<?php echo $list['id']; ?>_no" />
                    </label>
                </td>
            </tr><?php
        } ?>
	</table>
	<br />
    <button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('save')"><?php echo JText::_('JM_SAVE');?></button>
    <a class="btn btn-default" href="<?php echo JRoute::_('index.php?option=com_joomailermailchimpintegration&view=subscriptions&Itemid=' . $this->app->input->getUint('Itemid'));?>">
        <?php echo JText::_('JM_CANCEL');?>
    </a>

    <input type="hidden" name="Itemid" value="<?php echo $this->app->input->getUint('Itemid');?>">
    <input type="hidden" name="option" value="com_joomailermailchimpintegration">
    <input type="hidden" name="task" value="save">
    <input type="hidden" name="controller" value="">
    <?php echo JHTML::_('form.token'); ?>
</form>
