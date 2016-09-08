<?php

/**
 * @author      Guillermo Vargas <guille@vargas.co.cr>
 * @author      Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link        http://www.z-index.net
 * @copyright   (c) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @copyright   (c) 2015 Branko Wilhelm. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Class XmapModelSitemap
 */
class XmapModelSitemap extends JModelItem
{
    /**
     * @var string
     */
    protected $_context = 'com_xmap.sitemap';

    /**
     * @var null
     */
    protected static $items = null;

    /**
     * @throws Exception
     */
    protected function populateState()
    {
        $params = JFactory::getApplication()->getParams('com_xmap');

        $pk = JFactory::getApplication()->input->getInt('id');
        $this->setState('sitemap.id', $pk);
        $this->setState('params', $params);
    }

    /**
     * @return array|mixed|object
     */
    public function getItem()
    {
        if (is_null($this->_item))
        {
            $id = $this->getState('sitemap.id');
            $db = $this->getDbo();
            $groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

            $query = $db->getQuery(true)
                ->select($this->getState('item.select', 's.*'))
                ->from('#__xmap_sitemap AS s')
                ->where('s.published = ' . $db->quote(1))
                ->where('s.access IN (' . $groups . ')')
                ->where('s.id = ' . $db->quote($id));

            $db->setQuery($query);

            try
            {
                $db->execute();
            } catch (RuntimeException $e)
            {
                JError::raiseError(500, $e->getMessage());
            }

            $this->_item = $db->loadObject();

            if (!empty($this->_item))
            {
                $this->_item->params = new Registry($this->_item->params);
                $this->_item->selections = new Registry($this->_item->selections);
                $this->_item->selections = $this->_item->selections->toArray();
            }
        }

        if (empty($this->_item))
        {
            return JError::raiseError(404, JText::_('COM_XMAP_ERROR_SITEMAP_NOT_FOUND'));
        }

        return $this->_item;
    }

    /**
     * @return array|bool
     */
    public function getItems()
    {
        if ($item = $this->getItem())
        {
            return XmapHelper::getMenuItems($item->selections);
        }

        return false;
    }

    /**
     * @return array|mixed
     */
    public function getExtensions()
    {
        return XmapHelper::getExtensions();
    }

    /**
     * @param $count
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function hit($count)
    {
        $view = JFactory::getApplication()->input->getCmdn('view');
        $id = $this->getState('sitemap.id');
        $now = JFactory::getDate()->toUnix();

        if ($view != 'xml' && $view != 'html')
        {
            return false;
        }

        $query = $this->_db->getQuery(true)
            ->update('#__xmap_sitemap')
            ->set('views_' . $view . ' = views_' . $view . ' + 1')
            ->set('count_' . $view . ' = ' . $this->_db->quote($count))
            ->set('lastvisit_' . $view . ' = ' . $this->_db->quote($now))
            ->where('id = ' . $this->_db->quote($id));

        $this->_db->setQuery($query);

        try
        {
            return $this->_db->execute();
        } catch (RuntimeException $e)
        {
            return false;
        }
    }

    /**
     * @param null $view
     *
     * @return null
     * @throws Exception
     */
    public function getSitemapItems($view = null)
    {
        if (!isset($view))
        {
            $view = JFactory::getApplication()->input->getCmd('view');
        }

        $db = JFactory::getDbo();
        $pk = $this->getState('sitemap.id');

        if (self::$items !== null && isset(self::$items[$view]))
        {
            return null;
        }

        $query = $db->getQuery(true)
            ->select('i.*')
            ->from('#__xmap_items AS i')
            ->where('i.view = ' . $db->quote($view))
            ->where('i.sitemap_id = ' . $db->quote($pk));

        $db->setQuery($query);

        $rows = $db->loadObjectList();

        self::$items[$view] = array();

        foreach ($rows as $row)
        {
            self::$items[$view][$row->itemid] = array();
            self::$items[$view][$row->itemid][$row->uid] = array();

            $pairs = explode(';', $row->properties);

            foreach ($pairs as $pair)
            {
                if (strpos($pair, '=') !== false)
                {
                    list($property, $value) = explode('=', $pair);
                    self::$items[$view][$row->itemid][$row->uid][$property] = $value;
                }
            }
        }

        return self::$items;
    }

    /**
     * @param $uid
     * @param $itemid
     * @param $view
     * @param $property
     * @param $value
     *
     * @return bool
     */
    public function chageItemPropery($uid, $itemid, $view, $property, $value)
    {
        $db = JFactory::getDbo();
        $items = $this->getSitemapItems($view);
        $pk = $this->getState('sitemap.id');

        if (empty($items[$view][$itemid][$uid]))
        {
            $items[$view][$itemid][$uid] = array();
            $isNew = true;
        } else
        {
            $isNew = false;
        }

        $items[$view][$itemid][$uid][$property] = $value;

        $sep = $properties = '';
        foreach ($items[$view][$itemid][$uid] as $k => $v)
        {
            $properties .= $sep . $k . '=' . $v;
            $sep = ';';
        }

        $object = new stdClass;
        $object->uid = $uid;
        $object->itemid = $itemid;
        $object->view = $view;
        $object->sitemap_id = $pk;
        $object->properties = $properties;

        if (!$isNew)
        {
            return $db->updateObject('#__xmap_items', $object, array('uid', 'itemid', 'view', 'sitemap_id'));
        } else
        {
            return $db->insertObject('#__xmap_items', $object);
        }
    }

    /**
     * @param $uid
     * @param $itemid
     *
     * @return int
     */
    public function toggleItem($uid, $itemid)
    {
        $sitemap = $this->getItem();
        $items = $this->getItems();
        $extensions = $this->getExtensions();

        $displayer = new XmapDisplayerHtml($sitemap, $items, $extensions);

        $excludedItems = $displayer->getExcludedItems();
        if (isset($excludedItems[$itemid]))
        {
            $excludedItems[$itemid] = (array)$excludedItems[$itemid];
        }
        if (!$displayer->isExcluded($itemid, $uid))
        {
            $excludedItems[$itemid][] = $uid;
            $state = 0;
        } else
        {
            if (is_array($excludedItems[$itemid]) && count($excludedItems[$itemid]))
            {
                // TODO refactor, create_function is bad
                $excludedItems[$itemid] = array_filter($excludedItems[$itemid], create_function('$var', 'return ($var != \'' . $uid . '\');'));
            } else
            {
                unset($excludedItems[$itemid]);
            }
            $state = 1;
        }

        $registry = new Registry;
        $registry->loadArray($excludedItems);
        $str = $registry->toString();

        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->update('#__xmap_sitemap AS s')
            ->set('s.excluded_items = ' . $db->quote($str))
            ->where('s.id = ' . $db->quote($sitemap->id));

        $db->setQuery($query);

        $db->execute();

        return $state;
    }
}
