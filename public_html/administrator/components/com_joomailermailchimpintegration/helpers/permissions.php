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

class checkPermissions {

    public static function check(){
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $archiveDir = $params->get('params.archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive');
        $archiveDir = ($archiveDir[0] == '/') ? $archiveDir : '/' . $archiveDir;
        $archiveDir = (substr($archiveDir, -1) == '/') ? substr($archiveDir, 0, -1) : $archiveDir;

        if ($archiveDir != $params->get('params.archiveDir') && $params->get('params.MCapi')) {
            $params->set('params.archiveDir', $archiveDir);

            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->update('#__extensions')
                ->set($db->qn('params') . ' = ' . $db->q($params->toString()))
                ->where($db->qn('element') . ' = ' . $db->q('com_joomailermailchimpintegration'));
            $db->setQuery($query)->execute();
        }

        if (!JFolder::exists(JPATH_SITE . $archiveDir)) {
            $msg  = '<table width="100%"><tr><td align="left" valign="center" colspan="6">';
            $msg .= '<div style="border: 2px solid #ff0000; padding: 10px; margin: 0 0 1em 0;">';
            $msg .= '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/warning.png" align="left"/>';
            $msg .= '<span style="padding-left: 10px; line-height: 28px;">';
            $msg .= JText::_('JM_INVALID_ARCHIVE_DIRECTORY') . ': ' . $archiveDir;
            $msg .= '</span>';
            $msg .= '</div>';
            $msg .= '</td></tr>';
            $msg .= '</table>';

            return $msg;
        }
        $archiveDir = JPATH_SITE . $archiveDir;

        $isWritable = false;
        $fileName = JPATH_SITE . '/tmp/test.xyz';
        $tmp = '42';
        $handle = JFile::write($fileName, $tmp);
        if ($handle) {
            $isWritable = true;
            JFile::delete($fileName);
        }
        if ($isWritable) {
            $fileName = $archiveDir . '/test.xyz';
            $handle = JFile::write($fileName, $tmp);
            if ($handle) {
                $isWritable = true;
                JFile::delete($fileName);
            } else {
                $isWritable = false;
            }
        }

        if ($isWritable) {
            $msg  = '';
        } else {
            $msg  = '<table width="100%"><tr><td align="left">';
            $msg .= '<div style="border: 2px solid #ff0000; padding: 10px; margin: 0 0 1em 0;">';
            $msg .= '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/warning.png" align="left"/>';
            $msg .= '<span style="padding-left: 10px; line-height: 28px;">';
            $msg .= JText::sprintf('JM_PERMISSIONS_ERROR_GLOBAL', $params->get('params.archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive'));
            $msg .= '</span>';
            $msg .= '</div>';
            $msg .= '</td></tr>';
            $msg .= '</table>';
        }

        return $msg;
    }
}
