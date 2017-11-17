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

class joomailermailchimpintegrationModelSubscriber extends jmModel {

    private static $data;
    protected $_total = null;
    protected $pagination = null;

    public function __construct() {
        parent::__construct();

        // Get pagination request variables
        $limit = $this->app->getUserStateFromRequest('global.list.limit', 'limit', $this->app->getCfg('list_limit'), 'int');
        $limitstart = $this->input->getUint('limitstart', 0);

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    private function buildQuery() {
        $filter_type = $this->app->getUserStateFromRequest('filter_type', 'filter_type', 0, 'string');
        $search    = \Joomla\String\StringHelper::strtolower($this->app->getUserStateFromRequest('search', 'search', '', 'string'));

        $where = array();
        if ($search != '') {
            $searchEscaped = '"%' . $this->db->getEscaped($search, true) . '%"';
            $where[] = $this->db->qn('username') . ' LIKE ' . $this->db->q($searchEscaped)
                . ' OR ' . $this->db->qn('email') . ' LIKE ' . $this->db->q($searchEscaped)
                . ' OR ' . $this->db->qn('name') . ' LIKE ' . $this->db->q($searchEscaped);
        }

        if ($filter_type) {
            if ($filter_type == 'Public Frontend') {
                $where[] = $this->db->qn('usertype') . ' = ' . $this->db->q('Registered')
                    . ' OR ' . $this->db->qn('usertype') . ' = ' . $this->db->q('Author')
                    . ' OR ' . $this->db->qn('usertype') . ' = ' . $this->db->q('Editor')
                    . ' OR ' . $this->db->qn('usertype') . ' = ' . $this->db->q('Publisher');
            }
            else if ($filter_type == 'Public Backend') {
                $where[] = $this->db->qn('usertype') . ' = ' . $this->db->q('Manager')
                    . ' OR ' . $this->db->qn('usertype') . ' = ' . $this->db->q('Administrator')
                    . ' OR ' . $this->db->qn('usertype') . ' = ' . $this->db->q('Super Administrator');
            } else {
                $where[] = $this->db->qn('usertype') . ' = LOWER(' . $this->db->q($filter_type) . ')';
            }
        }

        $where[] = $this->db->qn('block') . ' = "0"';

        $where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('id', 'name', 'username', 'email', 'block', 'usertype')))
            ->from($this->db->qn('#__users'))
            ->where($where)
            ->order($this->db->qn('id'));

        return $query;
    }

    public function getData() {
        // Lets load the data if it doesn't already exist
        if (empty(joomailermailchimpintegrationModelSubscriber::$data)) {
            //$this->db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
            //$this->data = $this->db->loadObjectList();

            //$this->data = $this->getList( $query );
            $query = $this->buildQuery();
            joomailermailchimpintegrationModelSubscriber::$data =
                $this->getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }
        return joomailermailchimpintegrationModelSubscriber::$data;
    }

    public function getUser ($id) {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('id', 'name', 'username', 'email', 'block', 'usertype')))
            ->from($this->db->qn('#__users'))
            ->where($this->db->qn('id') . ' = ' . $this->db->q($id));

        return $this->getList($query);
    }

    public function getListsForEmail($email) {
        $email = str_replace(' ', '+', $email);

        return $this->getModel('lists')->getListsWithFilter(array('email' => $email));
    }

    public function getListMemberInfo($listId, $email) {
        return $this->getMcObject()->listMember($listId, $email);
    }

    public function getSubscribed() {
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from($this->db->qn('#__joomailermailchimpintegration'));
        $res = $this->getList($query);

        return $res;
    }

    public function getUsers() {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from($this->db->qn('#__users'));
        $res = $this->getList($query);

        return $res;
    }

    public function reportsEmailActivity($listId, $email) {
        return $this->getMcObject()->reportsEmailActivity($listId, $email);
    }

    public function getCampaignsSince($date) {
        return $this->getModel('campaigns')->getCampaigns(array('since_send_time' => $date, 'status' => 'sent'));
    }

    public function getAmbraPayments() {
        $userId = $this->input->getUint('uid');

        $query = $this->db->getQuery(true);
        $query->select($this->db->qn(array('u.created_datetime', 't.title', 't.value'), array('created_datetime', 'title', 'price')))
            ->from($this->db->qn('#__ambrasubs_users2types') . ' AS u')
            ->join('LEFT', $this->db->qn('#__ambrasubs_types') . ' AS t ON ' . $this->db->qn('u.typeid') . ' = ' . $this->db->qn('t.id'))
            ->where($this->db->qn('u.userid') . ' = ' . $this->db->q($userId));
        $this->db->setQuery($query);

        return $this->db->loadObjectList();
    }

    /**
    * Get either a Gravatar URL or complete image tag for a specified email address.
    *
    * @param boole $img True to return a complete IMG tag False for just the URL
    * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
    * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
    * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
    * @param array $atts Optional, additional key/value attributes to include in the IMG tag
    * @return String containing either just a URL or a complete image tag
    * @source http://gravatar.com/site/implement/images/php/
    */
    public function getGravatar($default = '', $img = false, $s = 155, $d = 'mm', $r = 'g', $atts = array()) {
        $email = str_replace(' ', '+', $this->input->getString('email'));
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($default) {
            $url .= '&amp;default=' . urlencode($default);
        }
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }

        return $url;
    }

    public function getJomSocialGroups() {
        $userId = $this->input->getUint('uid');

        if ($this->isJomSocialInstalled()) {
            $query = $this->db->getQuery(true);
            $query->select($this->db->qn(array('g.id', 'g.name')))
                ->from($this->db->qn('#__community_groups_members') . ' AS m')
                ->join('LEFT', $this->db->qn('#__community_groups') . ' AS g ON ' . $this->db->q('m.groupid') . ' = ' . $this->db->q('g.id'))
                ->where($this->db->qn('m.memberid') . ' = ' . $this->db->q($userId));
            $this->db->setQuery($query);

            return $this->db->loadObjectList();
        }

        return '';
    }

    public function getRecentJomSocialDiscussions() {
        $userId = $this->input->getUint('uid');

        if ($this->isJomSocialInstalled()) {
            $query = $this->db->getQuery(true);
            $query->select($this->db->qn(array('id', 'title', 'groupid')))
                ->from($this->db->qn('#__community_groups_discuss'))
                ->where($this->db->qn('creator') . ' = ' . $this->db->q($userId))
                ->order($this->db->qn('created DESC'));

            $this->db->setQuery($query, 0, 5);

            return $this->db->loadObjectList();
        }

        return '';
    }

    public function getTotalJomSocialDiscussionsOfUser() {
        $userId = $this->input->getUint('uid');

        if ($this->isJomSocialInstalled()) {
            $query = $this->db->getQuery(true);
            $query->select($this->db->qn('COUNT(*)', 'count'))
                ->from($this->db->qn('#__community_groups_discuss'))
                ->where($this->db->qn('creator') . ' = ' . $this->db->q($userId));
            $this->db->setQuery($query);

            return $this->db->loadObject()->count;
        }

        return '';
    }

    public function getKloutScore() {
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $kloutAPIkey = $params->get('params.KloutAPI');
        $twitterName = $this->getTwitterName();
        $kscore = 0;

        if ($twitterName != '') {
            $kloutXML = new DOMDocument();
            $kloutDataString = @file_get_contents('http://api.klout.com/1/klout.xml?key=' . $kloutAPIkey . '&users=' . $twitterName);
            if ($kloutDataString) {
                $kloutXML->loadXML($kloutDataString);
                $kscore = (int)$kloutXML->getElementsByTagName('kscore')->item(0)->nodeValue;
            }
        } else {
            $kscore = false;
        }

        return $kscore;
    }

    /**
     * Get Twitter name from Jomsocial profile field
     * @return bool
     */
    public function getTwitterName() {
        $userId = $this->input->getUint('uid');
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $twitterNameField = $params->get('params.jomsocial_twitter_name');

        if ($twitterNameField != '' && $this->isJomSocialInstalled()) {
            $query = $this->db->getQuery(true);
            $query->select($this->db->qn('v.value'))
                ->from($this->db->qn('#__community_fields') . ' AS f')
                ->join('LEFT', $this->db->qn('#__community_fields_values') . ' AS v ON ' . $this->db->qn('f.id') . ' = ' . $this->db->qn('v.field_id'))
                ->where($this->db->qn('fieldcode') . ' = ' . $this->db->q($twitterNameField))
                ->where($this->db->qn('v.user_id') . ' = ' . $this->db->q($userId));
            $this->db->setQuery($query);

            return $this->db->loadObject()->value;
        } else {
            return false;
        }
    }

    public function getFacebookName() {
        $userId = $this->input->getUint('uid');

        if($this->isJomSocialInstalled()) {
            $query = $this->db->getQuery(true);
            $query->select($this->db->qn('connectid'))
                ->from($this->db->qn('#__community_connect_users'))
                ->where($this->db->qn('userid') . ' = ' . $this->db->q($userId));
            $this->db->setQuery($query);
            $result = $this->db->loadObject();

            return ($result != NULL) ? $result->connectid : '';
        }

        return '';
    }

    public function isJomSocialInstalled() {
        return JFile::exists(JPATH_ADMINISTRATOR . '/components/com_community/admin.community.php');
    }
}
