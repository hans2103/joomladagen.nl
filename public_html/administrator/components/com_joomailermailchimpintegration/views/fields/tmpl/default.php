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

<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(pressbutton) {
    Joomla.submitform(pressbutton);
}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <?php
    if ($this->fields['total_items'] == 0) {
        echo '<div class="alert alert-info">' . JText::_('JM_NO_CUSTOM_MERGE_FIELDS') . '</div>';
    } else { ?>
        <div id="editcell">
            <table class="adminlist">
                <thead>
                    <tr>
                        <th width="5" align="center">#</th>
                        <th width="20" align="center">
                            <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
                        </th>
                        <th>
                            <?php echo JText::_('JM_MERGE_FIELD_NAME'); ?>
                        </th>
                        <th width="230">
                            <?php echo JText::_('JM_TYPE'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('JM_REQUIRED'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('JM_DEFAULT_VALUE'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('JM_TAG'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('JM_ORDER'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $k = 0;
                foreach ($this->fields['merge_fields'] as $index => $field) {
                    $choices = '';
                    if (isset($field['options']['choices'])) {
                        $choices = implode('||', $field['options']['choices']);
                    }
                    $val = $field['merge_id'] . ';' . $field['name'] . ';' . $field['tag'] . ';' . $field['type'] . ';'
                        . $field['required'] . ';' . $choices;
                    $checkbox = JHTML::_('grid.id', $index, $val); ?>
                    <tr class="<?php echo "row$k"; ?>">
                        <td align="center">
                            <?php echo $index+1; ?>
                        </td>
                        <td align="center">
                            <?php echo $checkbox;?>
                        </td>
                        <td>
                            <?php echo $field['name']; ?>
                        </td>
                        <td align="center">
                            <?php echo $field['type']; ?>
                        </td>
                        <td align="center">
                            <?php echo JText::_(($field['required'] ? 'JYES' : 'JNO')); ?>
                        </td>
                        <td align="center">
                            <?php echo $field['default_value']; ?>
                        </td>
                        <td align="center">
                            <?php echo $field['tag']; ?>
                        </td>
                        <td align="center">
                            <?php echo $field['display_order']; ?>
                        </td>
                    </tr>
                    <?php
                    $k = 1 - $k;
                } ?>
                </tbody>
            </table>
        </div>

        <?php } // end if no lists created ?>

    <input type="hidden" name="listId" value="<?php echo $this->input->getString('listId');?>" />
    <input type="hidden" name="listName" value="<?php echo $this->name;?>" />
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="fields" />
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
