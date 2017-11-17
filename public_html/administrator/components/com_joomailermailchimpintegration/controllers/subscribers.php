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

class joomailermailchimpintegrationControllerSubscribers extends joomailermailchimpintegrationController {

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function unsubscribe() {
        $emails = $this->input->getString('emails', array(), 'post', 'array');
        $listId = $this->input->getString('listid', 0, 'post', 'string');

        $i = 0;
        $errors = array();
        if (isset($emails[0]) && $listId) {
            foreach ($emails as $email) {
                try {
                    $this->getModel('subscribers')->unsubscribe($listId, $email);
                } catch (Exception $e) {
                    $errors[] = $email . ': ' . $e->getMessage();
                    continue;
                }

                $i++;
            }

            if ($i > 0) {
                $this->app->enqueueMessage($i . ' ' . JText::_('JM_USER_UNSUBSCRIBED'));

                // clear cache
                $this->getModel('main')->cache('joomlamailerMisc')->clean('Lists');
            }
            if (count($errors)) {
                foreach ($errors as $error) {
                    $this->app->enqueueMessage($error, 'error');
                }
            }
        } else {
            $this->app->enqueueMessage(JText::_('JM_NO_USERS_SELECTED'), 'error');
        }

        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=subscribers&type=s&listid=' . $listId);
    }

    public function delete() {
        $emails = $this->input->get('emails', array());
        $listId = $this->input->getString('listid');

        $deleted = $errors = array();
        if (!empty($emails) && $listId) {
            foreach ($emails as $email) {
                try {
                    $this->getModel('subscribers')->delete($listId, $email);
                } catch (Exception $e) {
                    $errors[] = $email . ': ' . $e->getMessage();
                    continue;
                }

                $deleted[] = $email;
            }

            if (count($deleted) > 0) {
                $this->app->enqueueMessage(count($deleted) . ' ' . JText::_('JM_USER_DELETED'));

                $query = $this->db->getQuery(true)
                    ->delete('#__joomailermailchimpintegration')
                    ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId))
                    ->where($this->db->qn('email') . ' IN ("' . implode('","', $deleted) . '")');
                $this->db->setQuery($query)->execute();

                // clear cache
                $this->getModel('main')->cache('joomlamailerMisc');
            }
            if (count($errors)) {
                foreach ($errors as $error) {
                    $this->app->enqueueMessage($error, 'error');
                }
            }
        } else {
            $this->app->enqueueMessage(JText::_('JM_NO_USERS_SELECTED'), 'error');
        }

        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=subscribers&type=s&listid=' . $listId);
    }

    /*public function resubscribe() {
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi  = $params->get('params.MCapi');
        $MC = new joomlamailerMCAPI($MCapi);

        $listId = $this->input->getString('listid', 0, 'post', 'string');
        $emails = $this->input->getString('emails', array(), 'post', 'array');

        $i = 0;
        if (isset($emails[0]) && $listId) {
            foreach ($emails as $email) {
                $memberInfo = $MC->listMemberInfo($listId, $email);
                $resubscribe = $MC->listSubscribe($listId, $email, $memberInfo, $memberInfo['email_type'], false, true, false, false );
                if (!$MC->errorCode) $i++;
            }
        }

        if ($MC->errorCode) {
            $msg = MCerrorHandler::getErrorMsg($MC);
        } else {
            $msg = $i . ' ' . JText::_('JM_USER_RESUBSCRIBED');
        }

        $this->setRedirect('index.php?option=com_joomailermailchimpintegration&view=lists', $msg);
    }*/

    public function cancel() {
        $this->setRedirect('index.php?option=com_joomailermailchimpintegration&view=templates', JText::_('JM_OPERATION_CANCELLED'));
    }

    public function goToLists() {
        $this->setRedirect('index.php?option=com_joomailermailchimpintegration&view=lists');
    }
}
