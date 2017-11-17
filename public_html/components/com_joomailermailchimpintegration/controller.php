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

class joomailermailchimpintegrationController extends jmController {

    public function display($cachable = false, $urlparams = false) {
        parent::display($cachable, $urlparams);
    }

    public function edit() {
        $this->app->input->set('layout', 'form');
        parent::display();
    }

    public function save() {
        // check for request forgeries
        JSession::checkToken() or jexit('JINVALID_TOKEN');

        $user = JFactory::getUser();
        if (!$user->id) {
            $uri = JUri::getInstance();
            $this->app->enqueueMessage(JText::_('JM_ONLY_LOGGED_IN_USERS_CAN_VIEW_SUBSCRIPTIONS'), 'error');
            $this->app->redirect('index.php?option=com_users&view=login&return=' . base64_encode($uri->toString()));
        }

        $itemId = $this->app->input->getString('Itemid', '');
        $itemId = ($itemId) ? '&Itemid=' . $itemId : '';
        $redirectLink = 'index.php?option=com_joomailermailchimpintegration&view=subscriptions' . $itemId;

        $lists = $this->app->input->get('lists', array(), 'RAW');
        $currentStatus = $this->app->input->get('currentStatus', array(), 'RAW');

        if (!count($lists) || !count($currentStatus)) {
            $this->app->enqueueMessage(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'), 'error');
            $this->app->redirect($redirectLink . '&task=edit');
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/sync.php');
        $syncModel = new joomailermailchimpintegrationModelSync();
        $syncModel->addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models',
                                   'JoomailermailchimpintegrationModel');

        $mergeFields = array();
        $names = explode(' ', $user->name);
        if (count($names) > 1) {
            $mergeFields['FNAME'] = $names[0];
            unset($names[0]);
            $mergeFields['LNAME'] = implode(' ', $names);
        } else {
            $mergeFields['FNAME'] = $user->name;
        }

        foreach ($lists as $listId => $subscribe) {
            if ($currentStatus[$listId] == $subscribe) {
                continue;
            }

            try {
                $params = $syncModel->getUserParams($user->id, $listId);

                if ($subscribe) {
                    $this->getModel('subscriptions')->getMcObject()->listMemberSubscribe($listId, $params);
                    $this->dbInsert($user->id, $user->email, $listId);
                } else {
                    $this->getModel('subscriptions')->getMcObject()->listMemberUnsubscribe($listId, $user->email);
                    $this->dbDelete($user->email, $listId);
                }
            } catch (Exception $e) {
                $query = $this->db->getQuery(true)
                    ->delete($this->db->qn('#__joomailermailchimpintegration'))
                    ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId))
                    ->where($this->db->qn('email') . ' = ' . $this->db->q($user->email));
                $this->db->setQuery($query)->execute();
            }
        }

        $this->app->enqueueMessage(JText::_('JM_SUBSCRIPTIONS_UPDATED'));
        $this->app->redirect($redirectLink);
    }

    private function dbInsert($id, $email, $listId) {
        $query = $this->db->getQuery(true)
            ->insert('#__joomailermailchimpintegration')
            ->set($this->db->qn('userid') . ' = ' . $this->db->q($id))
            ->set($this->db->qn('email') . ' = ' . $this->db->q($email))
            ->set($this->db->qn('listid') . ' = ' . $this->db->q($listId));
        $this->db->setQuery($query)->execute();
    }

    private function dbDelete($email, $listId) {
        $query = $this->db->getQuery(true)
            ->delete('#__joomailermailchimpintegration')
            ->where($this->db->qn('email') . ' = ' . $this->db->q($email))
            ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId));
        $this->db->setQuery($query)->execute();
    }

    public function signup() {
        header('Content-Type: application/json');

        $response = array();

        if (!JSession::checkToken()) {
            $response['html'] = 'Invalid Token';
            $response['error'] = true;
            echo json_encode($response);
            exit;
        }

        //file_put_contents(__DIR__ . '/post2.txt', print_r($_POST, true));

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/helpers/JoomlamailerMC.php');
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();
        if (!$MCapi || !$JoomlamailerMC->pingMC()) {
            $response['html'] = 'No MailChimp API key';
            $response['error'] = true;
            echo json_encode($response);
            exit;
        }

        // set Itemid so we can retrieve the correct module parameters below
        $this->app->input->set('Itemid', $this->app->input->getUint('itemId', ''));

        jimport('joomla.application.module.helper');
        $module = JModuleHelper::getModule('mod_mailchimpsignup', $this->app->input->getString('title', ''));
        $moduleParams = new JRegistry();
        $moduleParams->loadString($module->params);
        $listId = $moduleParams->get('listid');

        // make sure SIGNUPAPI field exists to record signup date
        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/sync.php');
        $syncModel = new joomailermailchimpintegrationModelSync();
        $syncModel->addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models',
                                   'JoomailermailchimpintegrationModel');
        $syncModel->createSignupApiMergeField($listId);

        $user = JFactory::getUser();

        $fields = $this->app->input->get('fields', array(), 'RAW');

        $email = ($user->id ? $user->email : $fields['EMAIL']);
        unset($fields['EMAIL']);

        $params = array(
            'email_address' => $email,
            'email_type'    => $this->app->input->getString('email_type', 'html'),
            'status'        => 'pending' //($user->id ? 'subscribed' : 'pending') @TODO: implement different response if user is logged in and can be signed up without double opt-in
        );

        $params['merge_fields'] = array();
        foreach ($fields as $fieldName => $field) {
            if (is_array($field)) {
                foreach ($field as $key => $value) {
                    if ($key == 'date') {
                        $stamp = strtotime($value);
                        $params['merge_fields'][$fieldName] = date('Y-m-d', $stamp);
                    } else if (in_array($key, array('birthday', 'phone'))) {
                        if (!in_array('MM', $value) && !in_array('DD', $value)) {
                            $params['merge_fields'][$fieldName] = implode('/', $value);
                        }
                        break;
                    } else {
                        $params['merge_fields'][$fieldName][$key] = $value;
                    }
                }
            } else {
                if ($fieldName == 'FNAME' && !isset($fields['LNAME']) && strpos($field, ' ') !== false) {
                    $tmp = explode(' ', $field);
                    $field = $tmp[0];
                    unset($tmp[0]);
                    $params['merge_fields']['LNAME'] = implode(' ', $tmp);
                    $params['merge_fields'][$fieldName] = $field;
                } else {
                    $params['merge_fields'][$fieldName] = $field;
                }
            }
        }

        $interestCategories = $this->app->input->get('interests', array(), 'RAW');
        foreach ($interestCategories as $interests) {
            $interests = array_filter($interests);
            if (count($interests)) {
                foreach ($interests as $interestId) {
                    $params['interests'][$interestId] = true;
                }
            }
        }

        if ($this->getModel('subscriptions')->isSubscribed($listId, $email)) {
            $update = true;
        } else {
            $update = false;
            // add signup date (new subscriber)
            $params['ip_opt'] = $this->app->input->getString('ip', '');
            $params['merge_fields']['SIGNUPAPI'] = date('Y-m-d');
        }

        // send API request
        try {
            $this->db->transactionStart();

            //file_put_contents(__DIR__ . '/params.txt', print_r($params, true));
            $this->getModel('subscriptions')->getMcObject()->listMemberSubscribe($listId, $params);

            $query = $this->db->getQuery(true)
                ->select($this->db->qn('userid'))
                ->from($this->db->qn('#__joomailermailchimpintegration'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($email))
                ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId));
            $userIdSubscribed = $this->db->setQuery($query)->loadResult();

            if ($userIdSubscribed === null) {
                $query = $this->db->getQuery(true)
                    ->insert($this->db->qn('#__joomailermailchimpintegration'))
                    ->set($this->db->qn('email') . ' = ' . $this->db->q($email))
                    ->set($this->db->qn('listid') . ' = ' . $this->db->q($listId));
                if ($user->id) {
                    $query->set($this->db->qn('userid') . ' = ' . $this->db->q($user->id));
                }
            }

            $this->db->transactionCommit();

            $response['html'] = ($update) ? $moduleParams->get('updateMsg') : $moduleParams->get('thankyou');
            $response['error'] = false;

        } catch (Exception $e) {
            $this->db->transactionRollback();

            $response['html'] = $e->getMessage();
            $response['error'] = true;
        }

        echo json_encode($response);
    }
}
