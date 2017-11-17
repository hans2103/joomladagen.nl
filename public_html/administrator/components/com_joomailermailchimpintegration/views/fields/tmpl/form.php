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
defined('_JEXEC') or die('Restricted Access');?>

<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(pressbutton) {
    if (pressbutton == 'save') {
        if (document.adminForm.name.value == '') {
            alert('<?php echo JText::_('JM_ENTER_A_NAME');?>');
            return false;
        } else if (document.adminForm.type.selectedIndex == 0
            <?php if ($this->CBfields){?> && document.adminForm.CBfield.selectedIndex  == 0 <?php } ?>
            <?php if ($this->JSfields){?> && document.adminForm.JSfield.selectedIndex  == 0 <?php } ?>
            <?php if ($this->VMfields){?> && document.adminForm.VMfield.selectedIndex  == 0 <?php } ?>
            )
            {
                alert('<?php echo JText::_('JM_SELECT_FIELD');?>');
            } else {
                Joomla.submitform(pressbutton);
            }
    } else {
        Joomla.submitform(pressbutton);
    }
}
!function($){
    $(document).ready(function(){
        joomlamailerJS.functions.initFieldsForm();
    });
}(jQuery);
</script>
<style>
#coreRow1:hover, #coreRow2:hover, #CBrow:hover, #JSrow:hover, #VMrow:hover {
    opacity: 1 !important;
}
</style>

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div class="col100">
        <fieldset class="adminform">
            <legend><?php echo JText::_('JM_SETTINGS'); ?></legend>

            <table class="admintable">
                <tr>
                    <td align="right" class="key" width="200" style="width:200px !important;">
                        <label for="name">
                            <?php echo JText::_('JM_NAME'); ?>:
                        </label>
                    </td>
                    <td>
                        <input class="text_area" type="text" name="name" id="name" size="48" maxlength="250" value="<?php echo $this->item['name'];?>" />
                    </td>
                </tr>
                <tr id="coreRow1">
                    <td width="100" align="right" class="key">
                        <label for="type">
                            <?php echo JText::_('JM_DATA_TYPE'); ?>:
                        </label>
                    </td>
                    <td>
                        <?php echo $this->typeDropDown;?>
                    </td>
                </tr>
                <tr id="coreRow2" style="display:none;">
                    <td width="100" align="right" class="key">
                        <label for="options">
                            <?php echo JText::_('JM_OPTIONS'); ?>:
                        </label>
                    </td>
                    <td>
                        <textarea name="coreOptions" class="text_area" cols="40" rows="4" style="float:left;min-width:303px;"><?php
                            echo implode("\n", $this->item['choices']);
                        ?></textarea>&nbsp;<?php echo JText::_('JM_ONE_OPTION_PER_LINE');?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="required">
                            <?php echo JText::_('JM_REQUIRED'); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="checkbox" value="1" name="required" id="required" <?php if ($this->item['required']) { echo "checked"; }?>/>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="tag">
                            <?php echo JText::_('JM_TAG'); ?>:
                        </label>
                    </td>
                    <td>
                        <input class="text_area" type="text" name="tag" id="tag" size="48" maxlength="10" value="<?php echo $this->item['tag'];?>" />
                    </td>
                </tr>
                <?php if ($this->CBfields) { ?>
                    <tr id="CBrow">
                        <td align="right" class="key">
                            <label for="CBfield">
                                <?php echo JText::_('JM_ASSIGN_COMMUNITYBUILDER_FIELD'); ?>:
                            </label>
                        </td>
                        <td>
                            <select name="CBfield" id="CBfield" style="min-width:303px;">
                                <option value=""><?php echo '--- '.JText::_('JM_SELECT_FIELD').' ---';?></option>
                                <?php
                                foreach($this->CBfields as $cb){
                                    $selected = ($this->CBeditID==$cb->name)?'selected="selected"':'';
                                    echo '<option value="'.$cb->name.'|*|'.$cb->fieldid.'" '.$selected.'>'.$cb->name.'</option>';
                                } ?>
                            </select>
                        </td>
                    </tr>
                <?php } ?>
                <?php if ($this->JSDropDown) { ?>
                    <tr id="JSrow">
                        <td align="right" class="key">
                            <label for="JSfield">
                                <?php echo JText::_('JM_ASSIGN_JOMSOCIAL_FIELD'); ?>:
                            </label>
                        </td>
                        <td>
                            <?php echo $this->JSDropDown;?>
                        </td>
                    </tr>
                <?php } ?>
                <?php if ($this->VMDropDown) { ?>
                    <tr id="VMrow">
                        <td align="right" class="key">
                            <label for="VMfield">
                                <?php echo JText::_('JM_ASSIGN_VIRTUEMART_FIELD'); ?>:
                            </label>
                        </td>
                        <td>
                            <?php echo $this->VMDropDown;?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </fieldset>
    </div>
    <div class="clr"></div>

    <input type="hidden" name="action" value="<?php echo $this->input->getString('task', 'add');?>" />
    <input type="hidden" name="oldtag" value="<?php echo $this->item['tag'];?>" />
    <input type="hidden" name="listId" value="<?php echo $this->item['listId']; ?>" />
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="key" value="" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="controller" value="fields" />
</form>
