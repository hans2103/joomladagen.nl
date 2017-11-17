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
$MCapi  = $params->get('params.MCapi');
$JoomlamailerMC = new JoomlamailerMC();

if (!$MCapi) {
    echo '<table>';
    echo $JoomlamailerMC->apiKeyMissing();
    return;
} else if (!$JoomlamailerMC->pingMC()) {
    echo '<table>';
    echo $JoomlamailerMC->apiKeyMissing(1);
    return;
} else { ?>
    <script language="javascript" type="text/javascript">
        Joomla.submitbutton = function(pressbutton) {
            if (document.adminForm.Filedata.value == '' && pressbutton != 'cancel'){
                alert('<?php echo JText::_('JM_PLEASE_SELECT_A_FILE_TO_UPLOAD'); ?>');
            } else {
                Joomla.submitform(pressbutton);
            }
        }
    </script>
    <form action="index.php?option=com_joomailermailchimpintegration&view=templates" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >
        <legend><?php echo JText::_('JM_UPLOAD_TEMPLATE'); ?></legend>
        <input type="file" id="file-upload" name="Filedata" size="40" />
        <div class="clr"></div>

        <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="1" />
        <input type="hidden" name="controller" value="templates" />
        <input type="hidden" name="type" value="templates" />
        <input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_joomailermailchimpintegration&view=templates'); ?>" />
    </form>
<?php
}
