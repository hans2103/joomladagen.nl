<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class xmap_com_tags
{
    /**
     * @var array
     */
    private static $views = array('tags', 'tag');

    /**
     * @var bool
     */
    private static $enabled = false;

    public function __construct()
    {
        self::$enabled = JComponentHelper::isEnabled('com_tags');

        JLoader::register('TagsHelperRoute', JPATH_SITE . '/components/com_tags/helpers/route.php');
    }

    /**
     * @param XmapDisplayerInterface $xmap
     * @param stdClass $parent
     * @param array $params
     */
    public static function getTree($xmap, stdClass $parent, array &$params)
    {
        $uri = new JUri($parent->link);

        if (!self::$enabled || !in_array($uri->getVar('view'), self::$views))
        {
            return;
        }

        $params['groups'] = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

        $params['language_filter'] = $uri->getVar('tag_list_language_filter');

        $params['include_tags'] = JArrayHelper::getValue($params, 'include_tags', 1);
        $params['include_tags'] = ($params['include_tags'] == 1 || ($params['include_tags'] == 2 && $xmap->view == 'xml') || ($params['include_tags'] == 3 && $xmap->view == 'html'));

        $params['show_unauth'] = JArrayHelper::getValue($params, 'show_unauth', 0);
        $params['show_unauth'] = ($params['show_unauth'] == 1 || ($params['show_unauth'] == 2 && $xmap->view == 'xml') || ($params['show_unauth'] == 3 && $xmap->view == 'html'));

        $params['tag_priority'] = JArrayHelper::getValue($params, 'tag_priority', $parent->priority);
        $params['tag_changefreq'] = JArrayHelper::getValue($params, 'tag_changefreq', $parent->changefreq);

        if ($params['tag_priority'] == -1)
        {
            $params['tag_priority'] = $parent->priority;
        }

        if ($params['tag_changefreq'] == -1)
        {
            $params['tag_changefreq'] = $parent->changefreq;
        }

        switch ($uri->getVar('view'))
        {
            case 'tags':
                self::getTagsTree($xmap, $parent, $params, $uri->getVar('parent_id', 0));
                break;

            case 'tag':
                self::getTagTree($xmap, $parent, $params, (array)$uri->getVar('id'), (array)$uri->getVar('types'));
                break;
        }
    }

    /**
     * @param XmapDisplayerInterface $xmap
     * @param stdClass $parent
     * @param array $params
     * @param int $parent_id
     */
    private static function getTagsTree($xmap, stdClass $parent, array &$params, $parent_id)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select(array('t.id', 't.title', 't.alias', 't.parent_id'))
            ->from('#__tags AS t')
            ->where('t.parent_id = ' . $db->quote($parent_id))
            ->where('t.published = 1')
            ->order('t.title');

        if (!$params['show_unauth'])
        {
            $query->where('t.access IN(' . $params['groups'] . ')');
        }

        if ($params['language_filter'])
        {
            $query->where('t.language IN(' . $db->quote($params['language_filter']) . ', ' . $db->quote('*') . ')');
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
            $node->uid = $parent->uid . '_tid_' . $row->id;
            $node->browserNav = $parent->browserNav;
            $node->priority = $params['tag_priority'];
            $node->changefreq = $params['tag_changefreq'];
            $node->pid = $row->parent_id;
            $node->link = TagsHelperRoute::getTagRoute($row->id . ':' . $row->alias);

            // workaround
            if (strpos($node->link, '&Itemid=') === false)
            {
                $node->link .= '&Itemid=' . $parent->id;
            }

            if ($xmap->printNode($node) !== false)
            {
                self::getTagsTree($xmap, $parent, $params, $row->id);
                if ($params['include_tags'])
                {
                    self::getTagTree($xmap, $parent, $params, array($row->id));
                }
            }
        }

        $xmap->changeLevel(-1);
    }

    /**
     * @param XmapDisplayerInterface $xmap
     * @param stdClass $parent
     * @param array $params
     * @param array $tagIds
     * @param array $typesr
     */
    private static function getTagTree($xmap, stdClass $parent, array &$params, array $tagIds, array $typesr = null)
    {
        $db = JFactory::getDbo();
        $rows = array();

        foreach ($tagIds as $tagId)
        {
            $listQuery = New JHelperTags;
            $query = $listQuery->getTagItemsQuery($tagId, $typesr, false, 'c.core_title', 'ASC', true, $params['language_filter']);
            $db->setQuery($query);
            $result = $db->loadObjectList();
            if (is_array($result))
            {
                $rows += $result;
            }
        }

        if (empty($rows))
        {
            return;
        }

        $xmap->changeLevel(1);

        foreach ($rows as $row)
        {
            $node = new stdclass;
            $node->id = $parent->id;
            $node->name = $row->core_title;
            $node->uid = $parent->uid . '_' . $row->content_item_id;
            $node->browserNav = $parent->browserNav;
            $node->priority = $params['tag_priority'];
            $node->changefreq = $params['tag_changefreq'];
            $node->link = TagsHelperRoute::getItemRoute($row->content_item_id, $row->core_alias, $row->core_catid, $row->core_language, $row->type_alias, $row->router);

            $xmap->printNode($node);
        }

        $xmap->changeLevel(-1);
    }
}