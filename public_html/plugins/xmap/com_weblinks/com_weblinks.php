<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class xmap_com_weblinks
{
    /**
     * @var array
     */
    private static $views = array('categories', 'category');

    /**
     * @var bool
     */
    private static $enabled = false;

    public function __construct()
    {
        self::$enabled = JComponentHelper::isEnabled('com_weblinks');

        JLoader::register('WeblinksHelperRoute', JPATH_SITE . '/components/com_weblinks/helpers/route.php');
    }

    /**
     * @param XmapDisplayerInterface $xmap
     * @param stdClass $parent
     * @param array $params
     *
     * @throws Exception
     */
    public static function getTree($xmap, stdClass $parent, array &$params)
    {
        $uri = new JUri($parent->link);

        if (!self::$enabled || !in_array($uri->getVar('view'), self::$views))
        {
            return;
        }

        $params['groups'] = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

        $params['language_filter'] = JFactory::getApplication()->getLanguageFilter();

        $params['include_links'] = JArrayHelper::getValue($params, 'include_links', 1);
        $params['include_links'] = ($params['include_links'] == 1 || ($params['include_links'] == 2 && $xmap->view == 'xml') || ($params['include_links'] == 3 && $xmap->view == 'html'));

        $params['show_unauth'] = JArrayHelper::getValue($params, 'show_unauth', 0);
        $params['show_unauth'] = ($params['show_unauth'] == 1 || ($params['show_unauth'] == 2 && $xmap->view == 'xml') || ($params['show_unauth'] == 3 && $xmap->view == 'html'));

        $params['category_priority'] = JArrayHelper::getValue($params, 'category_priority', $parent->priority);
        $params['category_changefreq'] = JArrayHelper::getValue($params, 'category_changefreq', $parent->changefreq);

        if ($params['category_priority'] == -1)
        {
            $params['category_priority'] = $parent->priority;
        }

        if ($params['category_changefreq'] == -1)
        {
            $params['category_changefreq'] = $parent->changefreq;
        }

        $params['link_priority'] = JArrayHelper::getValue($params, 'link_priority', $parent->priority);
        $params['link_changefreq'] = JArrayHelper::getValue($params, 'link_changefreq', $parent->changefreq);

        if ($params['link_priority'] == -1)
        {
            $params['link_priority'] = $parent->priority;
        }

        if ($params['link_changefreq'] == -1)
        {
            $params['link_changefreq'] = $parent->changefreq;
        }

        switch ($uri->getVar('view'))
        {
            case 'categories':
                self::getCategoryTree($xmap, $parent, $params, $uri->getVar('id'));
                break;

            case 'category':
                self::getlinks($xmap, $parent, $params, $uri->getVar('id'));
                break;
        }
    }

    /**
     * @param XmapDisplayerInterface $xmap
     * @param stdClass $parent
     * @param array $params
     * @param int $parent_id
     */
    private static function getCategoryTree($xmap, stdClass $parent, array &$params, $parent_id)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select(array('c.id', 'c.alias', 'c.title', 'c.parent_id'))
            ->from('#__categories AS c')
            ->where('c.parent_id = ' . $db->quote($parent_id ? $parent_id : 1))
            ->where('c.extension = ' . $db->quote('com_weblinks'))
            ->where('c.published = 1')
            ->order('c.lft');

        if (!$params['show_unauth'])
        {
            $query->where('c.access IN(' . $params['groups'] . ')');
        }

        if ($params['language_filter'])
        {
            $query->where('c.language IN(' . $db->quote(JFactory::getLanguage()->getTag()) . ', ' . $db->quote('*') . ')');
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (empty($rows))
        {
            return;
        }

        $xmap->changeLevel(1);

        foreach ($rows as $row)
        {
            $node = new stdclass;
            $node->id = $parent->id;
            $node->name = $row->title;
            $node->uid = $parent->uid . '_cid_' . $row->id;
            $node->browserNav = $parent->browserNav;
            $node->priority = $params['category_priority'];
            $node->changefreq = $params['category_changefreq'];
            $node->pid = $row->parent_id;
            $node->link = WeblinksHelperRoute::getCategoryRoute($row->id);

            if ($xmap->printNode($node) !== false)
            {
                self::getlinks($xmap, $parent, $params, $row->id);
            }
        }

        $xmap->changeLevel(-1);
    }

    /**
     * @param XmapDisplayerInterface $xmap
     * @param stdClass $parent
     * @param array $params
     * @param int $catid
     */
    private static function getlinks($xmap, stdClass $parent, array &$params, $catid)
    {
        self::getCategoryTree($xmap, $parent, $params, $catid);

        if (!$params['include_links'])
        {
            return;
        }

        $db = JFactory::getDbo();
        $now = JFactory::getDate('now', 'UTC')->toSql();

        $query = $db->getQuery(true)
            ->select(array('w.id', 'w.alias', 'w.title'))
            ->from('#__weblinks AS w')
            ->where('w.catid = ' . $db->Quote($catid))
            ->where('w.state = 1')
            ->where('(w.publish_up = ' . $db->quote($db->getNullDate()) . ' OR w.publish_up <= ' . $db->quote($now) . ')')
            ->where('(w.publish_down = ' . $db->quote($db->getNullDate()) . ' OR w.publish_down >= ' . $db->quote($now) . ')')
            ->order('w.ordering');

        if (!$params['show_unauth'])
        {
            $query->where('w.access IN(' . $params['groups'] . ')');
        }

        if ($params['language_filter'])
        {
            $query->where('w.language IN(' . $db->quote(JFactory::getLanguage()->getTag()) . ', ' . $db->quote('*') . ')');
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (empty($rows))
        {
            return;
        }

        $xmap->changeLevel(1);

        foreach ($rows as $row)
        {
            $node = new stdclass;
            $node->id = $parent->id;
            $node->name = $row->title;
            $node->uid = $parent->uid . '_' . $row->id;
            $node->browserNav = $parent->browserNav;
            $node->priority = $params['link_priority'];
            $node->changefreq = $params['link_changefreq'];
            $node->link = WeblinksHelperRoute::getWeblinkRoute($row->id . ':' . $row->alias, $catid);

            $xmap->printNode($node);
        }

        $xmap->changeLevel(-1);
    }
}