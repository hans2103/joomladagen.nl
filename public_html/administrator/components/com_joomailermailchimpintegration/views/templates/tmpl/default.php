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

$params = JComponentHelper::getParams('com_joomailermailchimpintegration');
$MCapi = $params->get('params.MCapi');
$JoomlamailerMC = new JoomlamailerMC();

if (!$MCapi) {
    echo '<table>';
    echo $JoomlamailerMC->apiKeyMissing();
    return;
} else if (!$JoomlamailerMC->pingMC()) {
    echo '<table>';
    echo $JoomlamailerMC->apiKeyMissing(1);
    return;
} ?>
<?php echo $this->sidebar; ?>
<form action="index.php?option=com_joomailermailchimpintegration&view=templates" method="post" name="adminForm" id="adminForm">
<?php
if (!count($this->templateFolders)) {
    echo JText::_('JM_NO_TEMPLATES');
} else { ?>
    <table class="adminlist">
        <thead>
            <tr>
                <th width="20">#</th>
                <th width="20">&nbsp;</th>
                <th><?php echo JText::_('JM_NAME'); ?></th>
                <th width="200"><?php echo JText::_('JM_EXAMPLE'); ?></th>
                <th width="180"><?php echo JText::_('JM_DOWNLOAD'); ?></th>
            </tr>
        </thead>
        <?php
        $i = 1;
        $fileTypes = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
        foreach ($this->templateFolders as $tf) {
            $relPath = preg_replace('#^[/\\\]#', '', $tf['relname']);
            $editLink = 'index.php?option=com_joomailermailchimpintegration&view=templates&layout=edit&template[]=' . urlencode($tf['name']);
            echo '<tr>';
            echo '<td>' . $i . '</td>';
            echo '<td width="20"><input type="checkbox" name="template[]" id="template" value="' . $tf['name'] . '" onclick="Joomla.isChecked(this.checked);"></td>';
            echo '<td align="center"><a title="' . JText::_('JACTION_EDIT') . '" href="' . $editLink . '">' . $tf['name'] . '</a></td>';
            echo '<td align="center">';
            $screenshot = false;
            foreach ($fileTypes as $fileType) {
                if (file_exists($tf['fullname'] . '/screenshot.' . $fileType)) {
                    $screenshot = JURI::root() . $relPath . '/screenshot.' . $fileType;
                    break;
                }
            }
            if (!$screenshot) {
                $screenshot = JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/';
                if (file_exists($tf['fullname'] . '/l.txt')) {
                    $screenshot .= 'templateLeftCol.gif';
                } else if (file_exists($tf['fullname'] . '/r.txt')){
                    $screenshot .= 'templateRightCol.gif';
                } else {
                    $screenshot .= 'templateSingleCol.gif';
                }
            }
            echo '<a class="modal" rel="{handler: \'iframe\', size: {x: 980, y: 550} }" href="'
                . JURI::root() . $relPath . '/template.html">';
            echo '<img src="' . $screenshot . '" height="150" />';
            echo '</a>';
            echo '</td>';
            echo '<td align="center" nowrap="nowrap">';
            echo '<a href="index.php?option=com_joomailermailchimpintegration&controller=templates&task=download&format=raw&template=' . urlencode($tf['name']) . '">' . JText::_('JM_DOWNLOAD') . '</a>';
            echo '</td>';
            echo '</tr>';
            $i++;
        } ?>
    </table>
    <div class="clr"></div>
    <?php
} ?>
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="templates" />
    <input type="hidden" name="type" value="templates" />
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
