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

class joomailermailchimpintegrationModelCreate extends jmModel {

    public static $sefLinks = array();

    public function __construct(){
        parent::__construct();
    }

    public function getK2Installed() {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn('extension_id'))
            ->from($this->db->qn('#__extensions'))
            ->where($this->db->qn('type') . ' = ' . $this->db->q('component'))
            ->where($this->db->qn('element') . ' = ' . $this->db->q('com_k2'))
            ->where($this->db->qn('enabled') . ' = ' . $this->db->q(1));

        return ($this->db->setQuery($query)->loadResult()) ? true : false;
    }

    public function getCategories() {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('id', 'title', 'parent_id', 'level'), array('cid', 'ctitle', 'parent_id', 'level')))
            ->from($this->db->qn('#__categories'))
            ->where($this->db->qn('extension') . ' = ' . $this->db->q('com_content'))
            ->order('lft');

        return $this->db->setQuery($query)->loadObjectList();
    }

    public function getK2cat($catid = 0) {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('id', 'name')))
            ->from($this->db->qn('#__k2_categories'));
        if ($catid > 0) {
            $query->where($this->db->qn('id') . ' = ' . $this->db->q($catid));
        }

        return $this->db->setQuery($query)->loadObjectList();
    }

    /**
     * Get merge fields for all lists
     * @return array
     */
    public function getMergeFieldsAll() {
        return $this->getModel('fields')->getMergeFieldsAll();
    }


    public static function getSefLink($data, $component = 'com_content', $view = '') {
        if (is_string($data)) {
            $data = array($data);
        }

        $key = implode('', $data);

        if (!isset(self::$sefLinks[$component][$key])) {
            $config = JFactory::getConfig();
            $db = JFactory::getDBO();
            $app = JFactory::getApplication();
            $router = $app::getRouter('site', array('mode' => $config->get('sef')));

            switch ($component) {
                case 'com_content':
                    $link = 'index.php?option=com_content&view=article&id=%d:%s&catid=%d';

                    $query = $db->getQuery(true)
                        ->select(array($db->qn('alias'), $db->qn('catid')))
                        ->from($db->qn('#__content'))
                        ->where($db->qn('id') . ' = ' . $db->q($data[0]));
                    $itemData = $db->setQuery($query)->loadAssoc();
                    $data = array_merge($data, $itemData);
                    break;
                case 'com_k2':
                    $link = 'index.php?option=com_k2&view=item&layout=item&id=%d';
                    break;
                case 'com_virtuemart':
                    $link = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=%d&virtuemart_category_id=%d';
                    break;
                case 'com_community':
                    if ($view == 'profile') {
                        $link = 'index.php?option=com_community&view=profile&userid=%d';
                    } else if ($view == 'discussion') {
                        $link = 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=%d&topicid=%d';
                    }
            }
            $link = vsprintf($link, $data);

            // check if we have a menu item pointing to this article
            $query = $db->getQuery(true)
                ->select($db->qn('id'))
                ->from($db->qn('#__menu'))
                ->where($db->qn('link') . ' = ' . $db->q($link));
            $itemId = $db->setQuery($query)->loadResult();

            // use first jomsocial menu we can find
            if (!$itemId && $component == 'com_community') {
                $query = 'SELECT ' . $db->qn('id') . ' FROM ' . $db->qn('#__menu') . ' WHERE '
                        . $db->qn('link') . ' LIKE ' . $db->q('%com_community%')
                        . 'AND ' . $db->qn('published') . '=' . $db->q(1) . ' '
                        . 'AND ' . $db->qn('menutype') . '!=' . $db->q($config->get('toolbar_menutype')) . ' '
                        . 'AND ' . $db->qn('type') . '=' . $db->q('component');
                $res = $db->setQuery($query)->loadResult();

                if ($res) {
                    $link .= '&Itemid=' . $res;
                }
            }

            if ($itemId && strpos($link, 'Itemid=') === false) {
                $link .= '&Itemid=' . $itemId;
            }

            $link = str_replace('/administrator/', '/', $router->build(JURI::root() . $link));

            self::$sefLinks[$component][$key] = JFilterOutput::ampReplace(htmlspecialchars($link));
        }

        return self::$sefLinks[$component][$key];
    }
}
