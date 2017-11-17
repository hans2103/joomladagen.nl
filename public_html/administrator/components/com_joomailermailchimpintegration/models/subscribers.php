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

class joomailermailchimpintegrationModelSubscribers extends jmModel {

    private static $data;
    private $pagination = null;
    protected $mainframe, $db;

    public function __construct() {
        parent::__construct();

        $this->mainframe = JFactory::getApplication();
        $this->db = JFactory::getDBO();

        $option = $this->input->getCmd('option');

        // Get pagination request variables
        $limit = $this->mainframe->getUserStateFromRequest('global.list.limit', 'limit', $this->mainframe->getCfg('list_limit'), 'int');
        $limitstart = $this->input->getString('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    private function buildQuery() {
        $filter_type = $this->mainframe->getUserStateFromRequest('filter_type',	'filter_type', 0, 'string');
        $search	     = \Joomla\String\StringHelper::strtolower($this->mainframe->getUserStateFromRequest('search', 'search', '', 'string'));
        $limit	     = $this->mainframe->getUserStateFromRequest('global.list.limit', 'limit', $this->mainframe->getCfg('list_limit'), 'int');
        $limitstart  = $this->mainframe->getUserStateFromRequest('limitstart', 'limitstart', 0, 'int');

        $where = array();
        if (isset($search) && $search != '') {
            $searchEscaped = '"%' . $this->db->escape($search, true) . '%"';
            $where[] = $this->db->qn('username') . ' LIKE ' . $searchEscaped .
                ' OR ' . $this->db->qn('email') . ' LIKE ' . $searchEscaped .
                ' OR ' . $this->db->qn('name') . ' LIKE ' . $searchEscaped;
        }

        if ($filter_type) {
            if ($filter_type == 'Public Frontend') {
                $where[] = $this->db->qn('usertype') . ' IN ("Registered","Author","Editor","Publisher")';
            } else if ($filter_type == 'Public Backend') {
                $where[] = $this->db->qn('usertype') . ' IN ("Manager","Administrator","Super Administrator")';
            } else {
                $where[] = $this->db->qn('usertype') . ' = LOWER(' . $this->db->q($filter_type) . ')';
            }
        }

        $where[] = $this->db->qn('block') . ' = ' . $this->db->q('0');

        $query = $this->db->getQuery(true);
        $query->select($this->db->qn(array('id', 'name', 'username', 'email', 'block', 'usertype')))
            ->from($this->db->qn('#__users'))
            ->where($where)
            ->order($this->db->qn('id'));

        return $query;
    }

    public function getData() {
        if (empty(joomailermailchimpintegrationModelSubscribers::$data)) {
            $query = $this->buildQuery();
            joomailermailchimpintegrationModelSubscribers::$data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return joomailermailchimpintegrationModelSubscribers::$data;
    }

    public function getUser($id) {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('id', 'name', 'username', 'email', 'block', 'usertype')))
            ->from($this->db->qn('#__users'))
            ->where($this->db->qn('id') . ' = ' . $this->db->q($id));

        return $this->_getList($query);
    }

    public function addJoomlaUserData(&$users) {
        if (!count($users)) {
            return;
        }
        $emails = array();
        foreach ($users as $user) {
            $emails[] = $user['email_address'];
        }

        $query = $this->db->getQuery(true);
        $query->select($this->db->qn(array('id', 'email')))
            ->from($this->db->qn('#__users'))
            ->where($this->db->qn('email') . ' IN ("' . implode('","', $emails) . '")');
        $this->db->setQuery($query);
        $res = $this->db->loadObjectList();

        $jUsers = array();
        foreach ($res as $r) {
            $jUsers[$r->email] = $r->id;
        }

        foreach ($users as $index => $user) {
            if (isset($jUsers[$user['email_address']])) {
                $users[$index] = JFactory::getUser($jUsers[$user['email_address']]);
                $users[$index]->timestamp_opt = $user['timestamp_opt'];
                $users[$index]->member_rating = $user['member_rating'];
            } else {
                $tmp = new stdClass();
                $tmp->id = '';
                $tmp->name = '';
                $tmp->email = $user['email_address'];
                $tmp->timestamp_opt = $user['timestamp_opt'];
                $tmp->member_rating = $user['member_rating'];
                $users[$index] = $tmp;
            }
        }
    }

    public function getSubscribed() {
        $query = $this->db->getQuery(true);
        $query->select($this->db->qn('*'))
            ->from($this->db->qn('#__joomailermailchimpintegration'));

        return $this->_getList($query);
    }

    /* not used
    public function getUsers() {
        $query = 'SELECT * FROM #__users';
        $this->data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        return $this->data;
    }*/

    public function getMembers() {
        $listid = $this->input->getString('listid',  0, '', 'string');
        $type = $this->input->getString('type',  's', '', 'string');
        $option = $this->input->getCmd('option');

        $count = $this->mainframe->getUserStateFromRequest('global.list.limit', 'limit', $this->mainframe->getCfg('list_limit'), 'int');
        $offset = $this->mainframe->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');

        switch ($type) {
            case 's':
                $result = $this->getMcObject()->listMembers($listid, 'subscribed', $count, $offset);
                break;
            case 'u':
                $result = $this->getMcObject()->listMembers($listid, 'unsubscribed', $count, $offset);
                break;
            case 'c':
                $result = $this->getMcObject()->listMembers($listid, 'cleaned', $count, $offset);
                break;
        }

        return $result;
    }

    public function getUserDetails($email, $list) {
        return $this->getMcObject()->listMember($list, $email);
    }

    public function getLists() {
        return $this->getModel('lists')->getLists();
    }

    public function unsubscribe($listId, $email) {
        $this->getMcObject()->listMemberUnsubscribe($listId, $email);
    }

    public function delete($listId, $email) {
        $this->getMcObject()->listMemberDelete($listId, $email);
    }

    public function getPagination() {
        // Load the content if it doesn't already exist
        if (empty($this->pagination)) {
            $option = $this->input->getCmd('option');
            $limit = $this->mainframe->getUserStateFromRequest('global.list.limit', 'limit', $this->mainframe->getCfg('list_limit'), 'int');
            $limitstart = $this->mainframe->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
            if ($limit == 0){
                $limit = 15000;
            }
            jimport('joomla.html.pagination');
            $this->pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->pagination;
    }
}
