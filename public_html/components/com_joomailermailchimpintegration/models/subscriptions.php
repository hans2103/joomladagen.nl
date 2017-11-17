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

class joomailermailchimpintegrationModelSubscriptions extends jmModel {

    public function getLists() {
        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/lists.php');
        $listsModel = new joomailermailchimpintegrationModelLists();

        return $listsModel->getLists();
    }

    public function checkListSubscriptions(&$lists, $email) {
        if (empty($lists['total_items'])) {
            return;
        }

        foreach ($lists['lists'] as $index => $list) {
            $lists['lists'][$index]['currentUserIsSubscribed'] = $this->isSubscribed($list['id'], $email);
        }
    }

    public function isSubscribed($listId, $email) {
        try {
            $res = $this->getMcObject()->listMember($listId, $email);
            return ($res['status'] == 'subscribed');
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}
