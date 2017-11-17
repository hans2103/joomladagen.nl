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

class joomailermailchimpintegrationControllerGroups extends joomailermailchimpintegrationController {

    public function __construct($config = array()) {
        parent::__construct($config);
        
        $this->registerTask('add', 'edit');
    }

    public function edit() {
        $this->input->set('view', 'groups');
        $this->input->set('layout', 'form' );
        $this->input->set('hidemainmenu', 1);
        parent::display();
    }

    public function save() {
        $action = $this->input->getString('action', 'add');
        if ($action !== 'add') {
            throw new Exception('Editing interest categories (groups) not supported by MailChimp API');
        }

        $fieldId = $this->input->getString('fieldId', '');
        $groupingId = $this->input->getString('groupingId', '');
        $listId = $this->input->getString('listId', 0);
        $name = ($action == 'add') ? $this->input->get('name', 0, 'RAW') : $this->input->get('nameOld', 0, 'RAW');

        $coreType = $this->input->getString('coreType', 0);
        $CBfield  = $this->input->get('CBfield', 0, 'RAW');
        $JSfield  = $this->input->get('JSfield', 0, 'RAW');
        $VMfield  = $this->input->get('VMfield', 0, 'RAW');

        $params = array(
            'title' => $this->input->get('name', 'Untitled', 'RAW'),
            'type'  => $coreType
        );
        $options = array();

        if ($coreType) {
            $framework = 'core';
            $db_field  = '';
        } else if ($CBfield) {
            $framework = 'CB';
            $CBfield = explode('|*|', $CBfield);
            $db_field = $CBfield[0];
            $db_id = $CBfield[1];
        } else if ($JSfield) {
            $framework = 'JS';
            $db_field = $JSfield;
        } else if ($VMfield) {
            $framework = 'VM';
        }

        if ($framework == 'CB') {
            $query = $this->db->getQuery(true);
            $query->select($this->db->qn('type'))
                ->from('#__comprofiler_fields')
                ->where($this->db->qn('fieldid') . ' = ' . $this->db->q($db_id));
            $fieldType = $this->db->setQuery($query)->loadResult();

            if ($fieldType == 'select' || $fieldType == 'singleselect') {
                $params['type'] = 'dropdown';
            } else if ($fieldType == 'checkbox' || $fieldType == 'multicheckbox' || $fieldType == 'multiselect'){
                $params['type'] = 'checkboxes';
            } else if ($fieldType != 'radio'){
                $params['type'] = 'hidden';
            } else {
                $params['type'] = $fieldType;
            }

            $query = $this->db->getQuery(true)
                ->select($this->db->qn('fieldtitle', 'options'))
                ->from('#__comprofiler_field_values')
                ->where($this->db->qn('fieldid') . ' = ' . $this->db->q($db_id));
            $fieldData = $this->db->setQuery($query)->loadObjectList();

            if ($fieldType == 'checkbox') {
                $options[] = array('name' => 'Yes');
            } else {
                foreach ($fieldData as $o) {
                    $options[] = array('name' => $o->options);
                }
            }
        } else if ($framework == 'JS') {
            $query = $this->db->getQuery(true)
                ->select($this->db->qn(array('type', 'options')))
                ->from('#__community_fields')
                ->where($this->db->qn('id') . ' = ' . $this->db->q($db_field));
            $fieldData = $this->db->setQuery($query)->loadObject();

            if (in_array($fieldData->type, array('select', 'radio', 'gender', 'country'))) {
                $params['type'] = 'dropdown';
            } else if (in_array($fieldData->type, array('checkbox', 'list'))) {
                $params['type'] = 'checkboxes';
            } else {
                $params['type'] = 'hidden';
            }

            if ($fieldData->type == 'gender') {
                $options = array(
                    array('name' => \Joomla\String\StringHelper::ucfirst(JText::_('JM_FEMALE'))),
                    array('name' => \Joomla\String\StringHelper::ucfirst(JText::_('JM_MALE')))
                );
            } else {
                $res = explode("\n", $fieldData->options);
                foreach ($res as $r) {
                    $options[] = array('name' => $r);
                }
            }

        } else if ($framework == 'VM') {
            $query = $this->db->getQuery(true)
                ->select($this->db->qn(array('name', 'type')))
                ->from('#__vm_userfield')
                ->where($this->db->qn('fieldid') . ' = ' . $this->db->q($VMfield));
            $fieldInfo = $this->db->setQuery($query)->loadObject();

            $db_field  = $fieldInfo->name;
            $fieldType = $fieldInfo->type;

            if ($fieldType == 'select' || $fieldType == 'singleselect') {
                $params['type'] = 'dropdown';
            } else if ($fieldType == 'checkbox' || $fieldType == 'multicheckbox' || $fieldType == 'multiselect') {
                $params['type'] = 'checkboxes';
            } else if ($fieldType != 'radio') {
                $params['type'] = 'hidden';
            } else {
                $params['type'] = $fieldType;
            }

            $query = $this->db->getQuery(true)
                ->select('*')
                ->from('#__vm_userfield_values')
                ->where($this->db->qn('fieldid') . ' = ' . $this->db->q($VMfield));
            $fieldData = $this->db->setQuery($query)->loadObjectList();

            foreach ($fieldData as $o) {
                $options[] = array('name' => $o->fieldvalue);
            }
        } else {    // ($framework == 'core')
            $res = explode("\n", $this->input->get('coreOptions', '', 'RAW'));
            foreach ($res as $r) {
                $options[] = array('name' => $r);
            }
            foreach ($options as $index => $o) {
                $value = trim($o['name']);
                if ($value) {
                    $options[$index]['name'] = $value;
                } else {
                    unset($options[$index]);
                }
            }
        }

        if (count($options) > 60) {
            $this->app->enqueueMessage(JText::_('JM_TOO_MANY_OPTIONS'), 'error');
        } else {
            try {
                // create interest category
                $method = ($action == 'edit') ? 'PATCH' : 'POST';
                $response = $this->getModel('groups')->getMcObject()->listInterestCategories($listId, '', $params, $method);
                if (!isset($response['id']) || !$response['id']) {
                    throw new Exception('Request failed.');
                }

                // add interests to interest category
                foreach ($options as $option) {
                    $this->getModel('groups')->getMcObject()->listInterestCategories($listId, $response['id'], $option, $method);
                }

                // store field association in J! db
                if ($framework != 'core') {
                    $this->db->transactionStart();
                    $query = $this->db->getQuery(true);
                    if ($action == 'add') {
                        $query
                            ->insert('#__joomailermailchimpintegration_custom_fields')
                            ->set($this->db->qn('grouping_id') . ' = ' . $this->db->q($response['id']));
                    } else {
                        $query
                            ->update('#__joomailermailchimpintegration_custom_fields')
                            ->where($this->db->qn('grouping_id') . ' = ' . $this->db->q($response['id']));
                    }
                    $query
                        ->set($this->db->qn('listID') . ' = ' . $this->db->q($listId))
                        ->set($this->db->qn('name') . ' = ' . $this->db->q($name))
                        ->set($this->db->qn('framework') . ' = ' . $this->db->q($framework))
                        ->set($this->db->qn('dbfield') . ' = ' . $this->db->q($db_field))
                        ->set($this->db->qn('type') . ' = ' . $this->db->q('group'));

                    $this->db->setQuery($query)->execute();

                    $this->db->transactionCommit();
                }

                $msg = JText::_(($action == 'add') ? 'JM_CUSTOM_FIELD_CREATED' : 'JM_CUSTOM_FIELD_UPDATED');
                $this->app->enqueueMessage($msg);
            } catch (Exception $e) {
                $this->app->enqueueMessage($e->getMessage(), 'error');
                if ($framework != 'core') {
                    $this->db->transactionRollback();
                    try {
                        // remove field again from MC as we couldn't store it in the J! DB
                        if (isset($response['id']) && $response['id'] > 0) {
                            $this->getModel('groups')->getMcObject()->listInterestCategories($listId, $response['id'], array(), 'DELETE');
                        }
                    } catch (Exception $e) {}
                }
            }
        }

        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=groups&listId=' . $listId);
    }

    public function remove() {
        $this->db->transactionStart();

        $listId = $this->input->getString('listId');
        $listName = $this->input->getString('listName', '');
        $cid = $this->input->get('cid', array());

        $counter = 0;
        foreach ($cid as $categoryId) {
            try {
                $this->getModel('groups')->getMcObject()->listInterestCategories($listId, $categoryId, array(), 'DELETE');

                // remove field association from J! db
                $query = $this->db->getQuery(true);
                $query->delete('#__joomailermailchimpintegration_custom_fields')
                    ->where($this->db->qn('grouping_id') . ' = ' . $this->db->q($categoryId));
                $this->db->setQuery($query)->execute();
            } catch (Exception $e) {
                $this->db->transactionRollback();
                $this->app->enqueueMessage($e->getMessage(), 'error');
                continue;
            }

            $counter++;
        }

        $this->db->transactionCommit();

        if ($counter > 0) {
            $this->app->enqueueMessage(JText::_('JM_CUSTOM_FIELD_DELETED'));
        }
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=groups&listId=' . $listId . '&name=' . $listName);
    }

    public function cancel() {
        $listId = $this->input->getString('listId');
        $listName = $this->input->getString('listName', '');
        $this->app->enqueueMessage(JText::_('JM_OPERATION_CANCELLED'), 'notice');
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=groups&listId=' . $listId . '&name=' . $listName);
    }

    function goToLists(){
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=lists');
    }
}
