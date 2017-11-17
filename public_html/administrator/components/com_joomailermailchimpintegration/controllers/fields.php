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

class joomailermailchimpintegrationControllerFields extends joomailermailchimpintegrationController {

    public function __construct($config = array()) {
        parent::__construct($config);

        $this->registerTask('add', 'edit');
        $this->registerTask('apply', 'save');
    }

    public function edit() {
        $this->input->set('view', 'fields');
        $this->input->set('layout', 'form');
        $this->input->set('hidemainmenu', 1);

        parent::display();
    }

    public function save() {
        $action = $this->input->getString('action', 'add');
        $listId = $this->input->getString('listId');

        $params = array(
            'name'     => $this->input->get('name', 'Untitled', 'RAW'),
            'type'     => $this->input->getString('type', 0),
            'required' => ($this->input->getUint('required', 0) ? true : false)
        );

        $newTag = $this->input->getString('tag', '');
        $oldTag = $this->input->getString('oldtag', '');
        $tag = ($newTag != $oldTag) ? $newTag : $oldTag;
        $params['tag'] = strtoupper($tag);

        $CBfield = $this->input->get('CBfield', 0, 'RAW');
        $JSfield = $this->input->get('JSfield', 0, 'RAW');
        $VMfield = $this->input->get('VMfield', 0, 'RAW');

        if ($CBfield) {
            $framework = 'CB';
            list($db_field, $db_id) = explode('|*|', $CBfield);
        } else if ($JSfield) {
            $framework = 'JS';
            $db_field = $JSfield;
        } else if ($VMfield) {
            $framework = 'VM';
        } else {
            $framework = 'core';
            $db_field = '';
        }

        // get options
        if ($CBfield || $JSfield) {
            $options['choices'] = '';
        }

        if ($framework == 'CB') {
            $query = $this->db->getQuery(true)
                ->select($this->db->qn('type'))
                ->from($this->db->qn('#__comprofiler_fields'))
                ->where($this->db->qn('fieldid') . ' = ' . $this->db->q($db_id));
            $fieldType = $this->db->setQuery($query)->loadResult();

            if ($fieldType == 'select') {
                $params['type'] = 'dropdown';

                $query = $this->db->getQuery(true)
                    ->select($this->db->qn('fieldtitle', 'options'))
                    ->from($this->db->qn('#__comprofiler_field_values'))
                    ->where($this->db->qn('fieldid') . ' = ' . $this->db->q($db_id));
                $fieldData = $this->db->setQuery($query)->loadObjectList();
                if (count($fieldData)) {
                    $params['options']['choices'] = array();
                    foreach ($fieldData as $o) {
                        $params['options']['choices'][] = $o->options;
                    }
                }
            } else if (in_array($fieldType, array('radio', 'checkbox', 'multicheckbox', 'multiselect', 'terms'))) {
                $params['type'] = 'radio';
                if ($fieldType == 'checkbox') {
                    $params['options']['choices'] = array('1');
                }
            } else if (in_array($fieldType, array('date', 'datetime'))) {
                $params['type'] = 'date';
            } else if ($fieldType == 'emailaddress') {
                $params['type'] = 'email';
            } else if ($fieldType == 'webaddress') {
                $params['type'] = 'url';
            } else if ($fieldType == 'integer') {
                $params['type'] == 'number';
            } else /*if (in_array($fieldType, array('predefined', 'text', 'textarea', 'editorta'))) {*/{
                $params['type'] = 'text';
            }
        } else if ($framework == 'JS') {
            $query = $this->db->getQuery(true)
                ->select($this->db->qn(array('type', 'options')))
                ->from($this->db->qn('#__community_fields'))
                ->where($this->db->qn('id') . ' = ' . $this->db->q($db_field));
            $fieldData = $this->db->setQuery($query)->loadObject();

            if ($fieldData) {
                if (in_array($fieldData->type, array('select', 'country', 'gender'))) {
                    $params['type'] = 'dropdown';
                    if ($fieldData->type == 'gender') {
                        $params['options']['choices'] = array(
                            \Joomla\String\StringHelper::ucfirst(JText::_('JM_FEMALE')),
                            \Joomla\String\StringHelper::ucfirst(JText::_('JM_MALE'))
                        );
                    } else {
                        $params['options']['choices'] = explode("\n", $fieldData->options);
                    }
                } else if ($fieldData->type == 'radio') {
                    $params['type'] = 'radio';
                    $params['options']['choices'] = explode("\n", $fieldData->options);
                } else if ($fieldData->type == 'url') {
                    $params['type'] = 'url';
                } else if ($fieldData->type == 'date') {
                    $params['type'] = 'date';
                } else if ($fieldData->type == 'birthdate') {
                    $params['type'] = 'birthday';
                } else {
                    $params['type'] = 'text';
                }
            }
        } else if ($framework == 'VM'){
            $query = $this->db->getQuery(true)
                ->select($this->db->qn(array('name', 'type')))
                ->from($this->db->qn('#__virtuemart_userfields'))
                ->where($this->db->qn('virtuemart_userfield_id') . ' = ' . $this->db->q($VMfield));
            $fieldInfo = $this->db->setQuery($query)->loadObject();

            $db_field = $fieldInfo->name;
            if (in_array($db_field, array('title', 'country', 'state'))) {
                $params['type'] = 'text';
            } else {
                $params['type'] = $fieldInfo->type;
            }

            $query = $this->db->getQuery(true)
                ->select('*')
                ->from($this->db->qn('#__virtuemart_userfield_values'))
                ->where($this->db->qn('virtuemart_userfield_id') . ' = ' . $this->db->q($VMfield));
            $fieldData = $this->db->setQuery($query)->loadObject();

            if ($fieldData) {
                if ($params['type'] == 'checkbox') {
                    $params['type'] = 'dropdown';
                    $params['options']['choices'] = array(
                        JText::_('JM_NO'),
                        JText::_('JM_YES')
                    );
                } else if (in_array($params['type'], array('text', 'textarea', 'euvatid', 'editorta'))) {
                    $params['type'] = 'text';
                } else if ($params['type'] == 'webaddress') {
                    $params['type'] = 'url';
                } else  if ($params['type'] == 'age_verification') {
                    $params['type'] = 'date';
                } else {
                    if ($params['type'] != 'radio') {
                        $params['type'] = 'dropdown';
                    }
                    $params['options']['choices'] = array();
                    foreach ($fieldData as $o) {
                        $params['options']['choices'][] = $o->fieldvalue;
                    }
                }
            }
        } else { // ($framework == 'core')
            if (in_array($params['type'], array('radio', 'dropdown'))) {
                $value = str_replace("\r\n", "\n", trim($this->input->getString('coreOptions', '')));
                $params['options']['choices'] = explode("\n", $value);
                if (count($params['options']['choices'])) {
                    array_walk($params['options']['choices'], 'trim');
                }
                $params['options']['choices'] = array_values(array_filter($params['options']['choices']));
            }
        }

        try {
            // get merge_id if we are editing
            if ($action == 'edit') {
                $method = 'PATCH';
                // get this lists merge fields
                $listMergeFields = $this->getModel('lists')->getListMergeFields($listId);
                foreach ($listMergeFields['merge_fields'] AS $field) {
                    if ($field['tag'] == $oldTag) {
                        $params['merge_id'] = $field['merge_id'];
                        break;
                    }
                }
                if (!isset($params['merge_id'])) {
                    throw new Exception('Merge Field unknown');
                }
            } else {
                $method = 'POST';
            }

            // send API request
            $response = $this->getModel('fields')->getMcObject()->listMergeField($listId, $params, $method);

            if ($framework != 'core') {
                $this->db->transactionStart();

                if (!$params['tag']) {
                    $params['tag'] = $response['tag'];
                }

                // check to see if field associations are stored locally
                $query = $this->db->getQuery(true)
                    ->select($this->db->qn('id'))
                    ->from($this->db->qn('#__joomailermailchimpintegration_custom_fields'))
                    ->where($this->db->qn('grouping_id') . ' = ' . $this->db->q($params['tag']));
                $cfid = $this->db->setQuery($query)->loadResult();
                // store field association in J! db
                if (!isset($response['merge_id']) || !$cfid) {
                    $query = $this->db->getQuery(true)
                        ->insert($this->db->qn('#__joomailermailchimpintegration_custom_fields'))
                        ->set($this->db->qn('listID') . ' = ' . $this->db->q($listId))
                        ->set($this->db->qn('name') . ' = ' . $this->db->q($params['name']))
                        ->set($this->db->qn('framework') . ' = ' . $this->db->q($framework))
                        ->set($this->db->qn('dbfield') . ' = ' . $this->db->q($db_field))
                        ->set($this->db->qn('grouping_id') . ' = ' . $this->db->q($params['tag']))
                        ->set($this->db->qn('type') . ' = ' . $this->db->q('field'));
                } else {
                    $query = $this->db->getQuery(true)
                        ->update($this->db->qn('#__joomailermailchimpintegration_custom_fields'))
                        ->set($this->db->qn('listID') . ' = ' . $this->db->q($listId))
                        ->set($this->db->qn('name') . ' = ' . $this->db->q($params['name']))
                        ->set($this->db->qn('framework') . ' = ' . $this->db->q($framework))
                        ->set($this->db->qn('dbfield') . ' = ' . $this->db->q($db_field))
                        ->set($this->db->qn('type') . ' = ' . $this->db->q('field'))
                        ->where($this->db->qn('grouping_id') . ' = ' . $this->db->q($params['tag']));
                }
                $this->db->setQuery($query)->execute();

                $this->db->transactionCommit();
            }

            if (isset($params['merge_id'])) {
                $msg = JText::_('JM_MERGE_FIELD_UPDATED');
            } else {
                $msg = JText::_('JM_MERGE_FIELD_CREATED');
                $params['merge_id'] = $response['merge_id'];
            }

            $this->app->enqueueMessage($msg);

        } catch (Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
            if ($framework != 'core') {
                $this->db->transactionRollback();
                try {
                    // remove field again from MC as we couldn't store it in the J! DB
                    if (isset($response['merge_id']) && $response['merge_id'] > 0) {
                        $this->getModel('fields')->getMcObject()->listMergeField($listId,
                            array('merge_id' => $response['merge_id']), 'DELETE');
                    }
                } catch (Exception $e) {}
            }
        }

        $url = 'index.php?option=com_joomailermailchimpintegration&view=fields&listId=' . $listId;
        if ($this->input->getCmd('task') == 'apply') {
            $cid = $params['merge_id'] . ';' . $params['name'] . ';' . $params['tag'] . ';' . $params['type'] . ';'
                . $params['required'] . ';';
            if (isset($params['options']['choices'])) {
                $cid .= implode('||', $params['options']['choices']);
            }
            $url .= '&layout=form&cid[]=' . $cid;
        }
        $this->app->redirect($url);
    }

    public function remove() {
        $listId = $this->input->getString('listId', 0);
        $cid = $this->input->get('cid', array(), 'RAW');

        foreach ($cid as $id) {
            list($mergeId, $name, $tag) = explode(';', $id);
            $params = array('merge_id' => $mergeId);

            try {
                $this->db->transactionStart();

                $this->getModel('fields')->getMcObject()->listMergeField($listId, $params, 'DELETE');

                // remove field association from J! db
                $query = $this->db->getQuery(true)
                    ->delete($this->db->qn('#__joomailermailchimpintegration_custom_fields'))
                    ->where($this->db->qn('grouping_id') . ' = ' . $this->db->q($tag));
                $this->db->setQuery($query)->execute();

                $this->app->enqueueMessage(JText::_('JM_MERGE_FIELDS_DELETED'));

                $this->db->transactionCommit();
            } catch (Exception $e) {
                $this->db->transactionRollback();
                $this->app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=fields&listId=' . $listId);
    }

    public function cancel() {
        $listId = $this->input->getString('listId');
        $this->app->enqueueMessage(JText::_('JM_OPERATION_CANCELLED'), 'notice');
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=fields&listId=' . $listId);
    }

    function goToLists() {
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=lists');
    }
}
