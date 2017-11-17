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
<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(pressbutton) {
  if (pressbutton == 'remove'){
        if (confirm('<?php echo JText::_('JM_ARE_YOU_SURE_TO_DELETE_THIS_FIELD');?>')) {
			Joomla.submitform(pressbutton);
		}
  } else {
      Joomla.submitform(pressbutton);
  }
}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php
if ($this->interestCategories['total_items'] > 0) { ?>
    <div id="editcell">
        <table class="adminlist">
        <thead>
            <tr>
                <th width="5">
                    #
                </th>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);">
                </th>
                <th class="alignLeft">
                    <?php echo JText::_('JM_FIELD_NAME'); ?>
                </th>
                <th width="130">
                    <?php echo JText::_('JM_DATA_TYPE'); ?>
                </th>
                <th width="300">
                    <?php echo JText::_('JM_OPTIONS'); ?>
                </th>
            </tr>
        </thead>
        <?php
        $k = 0;
        foreach ($this->interestCategories['categories'] as $index => $category) {
            switch($category['type']) {
                case 'checkboxes':
                    $dataType = JText::_('JM_CHECKBOXES');
                    break;
                case 'radio':
                    $dataType = JText::_('JM_RADIO_BUTTONS');
                    break;
                case 'dropdown':
                    $dataType = JText::_('JM_DROPDOWN_LIST');
                    break;
                case 'hidden':
                    $dataType = JText::_('JM_HIDDEN_INPUT');
                    break;
            }

            $options = '';
            if (isset($category['options'])) {
                $options = implode(', ', $category['options']);
                $options = (strlen($options) > 50) ? substr($options, 0, 50) . ' ...' : $options;
            } ?>
            <tr class="<?php echo "row$k"; ?>">
                <td>
                    <?php echo $index + 1; ?>
                </td>
                <td>
                    <?php echo JHTML::_('grid.id', $index, $category['id']);?>
                </td>
                <td class="alignLeft">
                    <?php echo $category['title']; ?>
                </td>
                <td>
                    <?php echo $dataType; ?>
                </td>
                <td>
                    <?php echo $options; ?>
                </td>
            </tr>
            <?php
            $k = 1 - $k;
        } ?>
        </table>
    </div>
<?php } else { ?>
    <div class="alert alert-info"><?php echo JText::_('JM_NO_CUSTOM_FIELDS');?></div>
<?php }?>

    <input type="hidden" name="listId" value="<?php echo $this->listId;?>" />
    <input type="hidden" name="listName" value="<?php echo $this->listName;?>" />
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="groups" />
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
