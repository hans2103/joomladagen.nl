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

require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/jmModel.php');
require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/fields.php');

class joomailermailchimpintegrationModelSync extends jmModel {

    private $data;
    private $total = null;
    private $pagination = null;

    private static $listMergeFields = null;
    private static $customFields = null;
    private static $interestCategories = null;

    public function __construct() {
        parent::__construct();

        $option = $this->input->getCmd('option');

        // Get pagination request variables
        $limit = $this->app->getUserStateFromRequest('global.list.limit', 'limit', $this->app->getCfg('list_limit'), 'int');
        $limitstart = $this->input->getString($option . '.limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    public function createSignupApiMergeField($listId) {
        $listMergeFields = $this->getListMergeFieldsAsArray($listId);

        // create hidden signup date merge fields if it doesn't exist
        if (!isset($listMergeFields['SIGNUPAPI'])) {
            try {
                $params = array(
                    'name'   => 'date added (API)',
                    'tag'    => 'SIGNUPAPI',
                    'type'   => 'hidden',
                    'public' => false
                );
                $this->getMcObject()->listMergeField($listId, $params, 'POST');
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the merge fields of a list as array
     */
    public function getListMergeFieldsAsArray($listId) {
        // get this lists merge fields
        if (self::$listMergeFields === null) {
            self::$listMergeFields = [];
            $res = $this->getModel('fields')->getMergeFields($listId);
            if ($res['total_items'] > 0) {
                foreach ($res['merge_fields'] as $field) {
                    self::$listMergeFields[$field['tag']] = $field;
                }
            }
        }

        return self::$listMergeFields;
    }

    private function buildQuery() {
        $filter_type = $this->app->getUserStateFromRequest('filter_type', 'filter_type', 0, 'string');
        $search	= \Joomla\String\StringHelper::strtolower($this->app->getUserStateFromRequest('search', 'search', '', 'string'));
        $filter_date = $this->app->getUserStateFromRequest('filter_date', 'filter_date', '', 'string');
        if ($filter_date == JText::_('Last visit after')) {
            $filter_date = false;
        }
        if (isset($search) && $search != '') {
            $searchEscaped = '"%' . $this->db->escape($search, true) . '%"';
            $where[] = ' username LIKE ' . $searchEscaped . ' OR email LIKE ' . $searchEscaped . ' OR name LIKE ' . $searchEscaped;
        }

        if ($filter_type > 1) {
            $where[] = ' um.group_id = '.$this->db->q($filter_type).' ';
        }

        $where[] = "block = '0'";

        $where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

        if ($filter_date && $filter_date != JText::_('JM_LAST_VISIT_AFTER')) {
            $where .= " AND lastvisitDate >= '" . $filter_date . "' ";
        }

        $query =  'SELECT a.*, ug.title AS groupname'
            . ' FROM #__users AS a'
            . ' INNER JOIN #__user_usergroup_map AS um ON um.user_id = a.id'
            . ' INNER JOIN #__usergroups AS ug ON ug.id = um.group_id'
            . $where
            . ' ORDER BY a.id';

        return $query;
    }

    public function getData() {
        if (empty($this->data)) {
            $query = $this->buildQuery();
            $this->data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->data;
    }

    public function getUser($id) {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('u.id', 'u.name', 'u.username', 'u.email', 'u.block', 'g.title'),
                array('id', 'name', 'username', 'email', 'block', 'usergroup')))
            ->from($this->db->qn('#__users', 'u'))
            ->join('LEFT', $this->db->qn('#__user_usergroup_map') . ' AS m ON (u.id = m.user_id)')
            ->join('LEFT', $this->db->qn('#__usergroups') . ' AS g ON (g.id = m.group_id)')
            ->where($this->db->qn('u.id') . ' = ' . $this->db->q($id));

        return $this->db->setQuery($query)->loadObject();
    }

    public function getUserParams($userId, $listId) {
        if (!(int)$userId) {
            throw new Exception('INVALID REQUEST');
        }

        $user = $this->getUser($userId);

        // bail out if user not found or user id blocked
        if (!$user) {
            throw new Exception('User not found');
        } elseif($user->block == 1) {
            throw new Exception('User is blocked');
        }

        $params = array(
            'email_address' => $user->email,
            'email_type'    => 'html',
            'status'        => 'subscribed'
        );

        // add signup date if the merge field exists
        if ($this->createSignupApiMergeField($listId) === true) {
            $params['merge_fields']['SIGNUPAPI'] = date('Y-m-d');
        }

        // split first and last name
        // name
        $names = explode(' ', $user->name);
        if (count($names) > 1) {
            $params['merge_fields']['FNAME'] = $names[0];
            unset($names[0]);
            $params['merge_fields']['LNAME'] = implode(' ', $names);
        } else {
            $params['merge_fields']['FNAME'] = $user->name;
        }

        // gather custom fields data if not done yet
        if (self::$customFields === null) {
            $query = $this->db->getQuery(true)
                ->select($this->db->qn(
                    array('f.framework', 'f.dbfield', 'f.grouping_id', 'f.type', 'js.type', 'cb.type', 'vm.type'),
                    array('framework', 'dbfield', 'grouping_id', 'type', 'jsType', 'cbType', 'vmType')))
                ->from($this->db->qn('#__joomailermailchimpintegration_custom_fields') . ' AS f')
                ->leftJoin($this->db->qn('#__community_fields') . ' AS js ON (' . $this->db->qn('f.dbfield') . ' = ' . $this->db->qn('js.id') . ')')
                ->leftJoin($this->db->qn('#__comprofiler_fields') . ' AS cb ON (' . $this->db->qn('f.dbfield') . ' = ' . $this->db->qn('cb.name') . ')')
                ->leftJoin($this->db->qn('#__virtuemart_userfields') . ' AS vm ON (' . $this->db->qn('f.dbfield') . ' = ' . $this->db->qn('vm.name') . ')')
                ->where($this->db->qn('f.listID') . ' = ' . $this->db->q($listId));
            $this->db->setQuery($query);
            self::$customFields = $this->db->loadObjectList();
        }

        // get interest-categories for this list
        if (count(self::$customFields) && self::$interestCategories === null) {
            self::$interestCategories = [];
            $res = $this->getModel('lists')->getListInterestCategories($listId);
            if ($res['total_items'] > 0) {
                foreach ($res['categories'] as $category) {
                    $res = $this->getModel('lists')->getListInterestCategories($listId, $category['id']);
                    if ($res['total_items'] > 0) {
                        foreach ($res['interests'] as $interest) {
                            self::$interestCategories[$category['id']][\Joomla\String\StringHelper::strtolower($interest['name'])] = $interest['id'];
                        }
                    }
                }
            }
        }

        if (is_array(self::$customFields) && count(self::$customFields)) {
            foreach (self::$customFields as $field) {
                $query = $this->db->getQuery(true);

                if ($field->framework == 'CB') {
                    $query->select($this->db->qn($field->dbfield))
                        ->from($this->db->qn('#__comprofiler'))
                        ->where($this->db->qn('user_id') . ' = ' . $this->db->q($user->id));
                } else if ($field->framework =='JS') {
                    $query->select($this->db->qn('value'))
                        ->from($this->db->qn('#__community_fields_values'))
                        ->where($this->db->qn('field_id') . ' = ' . $this->db->q($field->dbfield))
                        ->where($this->db->qn('user_id') . ' = ' . $this->db->q($user->id));
                }
                $fieldValue = $this->db->setQuery($query)->loadResult();

                if ($field->framework == 'CB') {
                    $fieldValue = str_replace('|*|', ',', $fieldValue);
                }
                if ($field->framework == 'JS') {
                    if (in_array($field->jsType, array('checkbox', 'list'))) {
                        $fieldValue = array_filter(explode(',', \Joomla\String\StringHelper::strtolower($fieldValue)));
                    }
                    if ($fieldValue == NULL) {
                        $fieldValue = '';
                    }
                }

                if ($field->type == 'group') {
                    foreach (self::$interestCategories[$field->grouping_id] as $interestName => $interestId) {
                        if ((is_array($fieldValue) && in_array($interestName, $fieldValue))
                            || (is_string($fieldValue) && \Joomla\String\StringHelper::strtolower($fieldValue) == $interestName)) {
                            $params['interests'][$interestId] = true;
                        } else {
                            $params['interests'][$interestId] = false;
                        }
                    }
                } else {
                    if ($fieldValue && isset(self::$listMergeFields[$field->grouping_id])) {
                        switch(self::$listMergeFields[$field->grouping_id]['type']) {
                            case 'date':
                            case 'birthday':
                                $stamp = strtotime($fieldValue);
                                $formatted = explode('/', self::$listMergeFields[$field->grouping_id]['options']['date_format']);
                                $values = [];
                                foreach ($formatted as $index => $f) {
                                    switch($f) {
                                        case 'YYYY':
                                            $values[0] =  date('Y', $stamp);
                                            break;
                                        case 'MM':
                                            $values[1] =  date('m', $stamp);
                                            break;
                                        case 'DD':
                                            $values[2] =  date('d', $stamp);
                                            break;
                                    }
                                }
                                ksort($values);
                                $separator = (self::$listMergeFields[$field->grouping_id]['type'] == 'date' ? '-' : '/');
                                $fieldValue = implode($separator, $values);

                                break;
                        }

                        if ($field->jsType == 'gender') {
                            $fieldValue = \Joomla\String\StringHelper::ucfirst($fieldValue);
                        } else if ($field->jsType == 'country') {
                            $jlang = JFactory::getLanguage();
                            $jlang->load('com_community.country', JPATH_SITE, 'en-GB', true);
                            $fieldValue = JText::_($fieldValue);
                        } else if (is_array($fieldValue)) {
                            if (count($fieldValue)) {
                                // get first element from the array
                                $fieldValue = array_values($fieldValue);
                                $fieldValue = array_shift($fieldValue);
                            } else {
                                $fieldValue = '';
                            }
                        } else if (substr($fieldValue, -1) == ',') {
                            // remove trailing comma
                            $fieldValue = substr($fieldValue, 0, -1);
                        }

                        $params['merge_fields'][$field->grouping_id] = $fieldValue;
                    }
                }
            }
        }

        return $params;
    }

    public function getTotalUsers() {
        $query = $this->db->getQuery(true)
            ->select('COUNT(' . $this->db->qn('id') . ')')
            ->from($this->db->qn('#__users'))
            ->where($this->db->qn('block') . ' = ' . $this->db->q(0));

        return $this->db->setQuery($query)->loadResult();
    }

    public function getTotal() {
        if (empty($this->total)) {
            $query = $this->buildQuery();
            $this->total = $this->_getListCount($query);
        }

        return $this->total;
    }

    public function getPagination() {
        if (empty($this->pagination)) {
            jimport('joomla.html.pagination');
            $this->pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->pagination;
    }

    public function getGroups() {
        require_once(JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php');

        return UsersHelper::getGroups();
    }

    public function getConfig($crm) {
        $query = $this->db->getQUery(true)
            ->select('params')
            ->from($this->db->qn('#__joomailermailchimpintegration_crm'))
            ->where($this->db->qn('crm') . ' = ' . $this->db->q($crm));
        $this->db->setQuery($query);

        return json_decode($this->db->loadResult());
    }

    public function getJSFields() {
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_community/admin.community.php')) {
            return array();
        }
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from($this->db->qn('#__community_fields'))
            ->where($this->db->qn('published') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('type') . ' != ' . $this->db->q('group'));
        $this->db->setQuery($query);

        return $this->db->loadObjectList();
    }

    public function getCBFields() {
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/admin.comprofiler.php')) {
            return array();
        }

        $query = $this->db->getQuery(true)
            ->select(array('fieldid', 'name', 'title'), array('id', 'name', 'title'))
            ->from($this->db->qn('#__comprofiler_fields'))
            ->where($this->db->qn('published') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('profile') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('readonly') . ' = ' . $this->db->q(0))
            ->where($this->db->qn('calculated') . ' = ' . $this->db->q(0));
        $this->db->setQuery($query);

        return $this->db->loadObjectList();
    }

    public function getSugarFields() {
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $sugar_name = $params->get('params.sugar_name');
        $sugar_pwd  = $params->get('params.sugar_pwd');
        $sugar_url  = $params->get('params.sugar_url');

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/sugar.php');
        $sugar = new SugarCRMWebServices;
        $sugar->SugarCRM($sugar_name, $sugar_pwd, $sugar_url);
        $sugar->login();

        $fields = $sugar->getModuleFields('Contacts');

        $disallowedFields = array(
            'id',
            'date_entered',
            'date_modified',
            'modified_user_id',
            'modified_by_name',
            'created_by',
            'created_by_name',
            'deleted',
            'assigned_user_id',
            'assigned_user_name',
            'email1'
        );

        for ($x = 0; $x < count($fields); $x++) {
            if (in_array($fields[$x]['name'], $disallowedFields)){
                unset($fields[$x]);
            }
        }

        return $fields;
    }

    public function buildFieldsDropdown($name, $JS, $CB, $config, $email = false) {
        $html = '<select name="crmFields[' . $name . ']" id="' . $name . '" style="min-width: 200px;">';
        $html .= '<option value="">do not sync</option>';

        if ($email) {
            $selected = (isset($config->{$name}) && $config->{$name} == 'default') ? ' selected="selected"' : '';
            $html .= '<option value="default"' . $selected . '>Joomla User Account Email</option>';
        }

        if ($JS) {
            $html .= '<optgroup label="JomSocial">';
            foreach ($JS as $field) {
                $selected = (isset($config->{$name}) && $config->{$name} == 'js;' . $field->id) ? ' selected="selected"' : '';
                $html .= '<option value="js;' . $field->id . '"' . $selected . '>' . $field->name . '</option>';
            }
            $html .= '</optgroup>';
        }
        if ($CB){
            $html .= '<optgroup label="Community Builder">';
            foreach ($CB as $field) {
                $selected = (isset($config->{$name}) && $config->{$name} == 'cb;'.$field->name) ? ' selected="selected"' : '';
                $html .= '<option value="cb;' . $field->name . '"' . $selected . '>' . $field->title . '</option>';
            }
            $html .= '</optgroup>';
        }

        $html .= '</select>';

        return $html;
    }

    public function getCRMusers() {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('crm', 'user_id')))
            ->from($this->db->qn('#__joomailermailchimpintegration_crm_users'));
        $data = $this->db->setQuery($query)->loadObjectList();

        if (count($data)) {
            foreach ($data as $d) {
                $result[$d->crm][] = $d->user_id;
            }

            return $result;
        } else {
            return false;
        }
    }
}
