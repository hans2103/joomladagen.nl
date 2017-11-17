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
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ROOT . '/components/com_community/libraries/core.php');

class plgCommunityJoomlamailer extends CApplications {

    public function onUserDetailsUpdate($user) {
        // check if the signup plugin is enabled; if not: return.
        $plugin = JPluginHelper::getPlugin('system', 'joomailermailchimpsignup');

        // make sure API wrapper is available (i.e. Joomlamailer is installed)
        jimport('joomla.filesystem.file');
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/MCAPI.class.php')) {
            return;
        }

        $app = JFactory::getApplication();
        $oldEmail = ($app->isSite()) ? $user->emailpass : $app->input->get('oldEmail', '' ,'RAW');

        // update user if email has changed
        if ($user->email != $oldEmail) {
            // include MCAPI wrapper
            if (!class_exists('joomlamailerMCAPI')) {
                require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/MCAPI.class.php');
            }
            // create instance of api object
            $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
            $MCapi = $params->get('params.MCapi');
            $api = new joomlamailerMCAPI($MCapi);

            // get the ID of the mailing list
            $plugin = JPluginHelper::getPlugin('system', 'joomailermailchimpsignup');
            $pluginParams = new JRegistry($plugin->params);
            $listId = $pluginParams->get('listid');

            // check if the user is subscribed
            $userLists = $api->listsForEmail($oldEmail);
            if (!$userLists || !in_array($listId, $userLists)) {
                return;
            }

            $name = explode(' ', $app->input->getString('name', $user->name));
            $fname = $name[0];
            $lname = '';
            if (isset($name[1])) {
                for ($i = 1; $i < count($name); $i++) {
                    $lname .= $name[$i] . ' ';
                }
                $lname = trim($lname);
            }

            $mergeVars = array('FNAME' => $fname, 'LNAME' => $lname, 'EMAIL' => $user->email, 'OPTINIP' => $this->getIpAddress());
            $emailType = '';

            // submit to MailChimp
            $api->listUpdateMember($listId, $oldEmail, $mergeVars, $emailType, true);
            // update local database entry
            $db = JFactory::getDBO();
            $query = $db->getQuery(true)
                ->update('#__joomailermailchimpintegration')
                ->set($db->qn('email') . ' = ' . $db->q($user->email))
                ->where($db->qn('email') . ' = ' . $db->q($oldEmail))
                ->where($db->qn('listid') . ' = ' . $db->q($listId));
            $db->setQuery($query)->execute();
        }
    }

    private function getIpAddress() {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }
}
