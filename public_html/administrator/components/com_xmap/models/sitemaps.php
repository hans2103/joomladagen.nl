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
use Joomla\Utilities\ArrayHelper;

/**
 * Class XmapModelSitemaps
 */
class XmapModelSitemaps extends JModelList
{
    /**
     * array of search engines providers who allow to submit sitemaps
     *
     * @var array
     */
    protected $pings = array(
        'google' => 'http://www.google.com/webmasters/sitemaps/ping?sitemap=%s',
        'bing'   => 'http://www.bing.com/webmaster/ping.aspx?siteMap=%s',
    );

    /**
     * @var array
     */
    protected $types = array(
        'xml'    => true,
        'news'   => false,
        'images' => false,
        'videos' => false,
        'mobile' => false,
    );

    /**
     * @param array $config
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'state', 'a.published',
                'access', 'a.access', 'access_level',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'hits', 'a.hits',
            );
        }

        parent::__construct($config);
    }

    /**
     * @param null $ordering
     * @param null $direction
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
        $this->setState('filter.access', $access);

        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '');
        $this->setState('filter.state', $published);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // List state information.
        parent::populateState('a.title', 'asc');
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.published');

        return parent::getStoreId($id);
    }

    /**
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*')
        );
        $query->from('#__xmap_sitemap AS a');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Filter by access level.
        if ($access = $this->getState('filter.access'))
        {
            $query->where('a.access = ' . (int)$access);
        }

        // Filter by published state
        $state = $this->getState('filter.state');
        if (is_numeric($state))
        {
            $query->where('a.published = ' . (int)$state);
        } elseif ($state === '')
        {
            $query->where('(a.published IN (0, 1))');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            if (stripos($search, 'id:') === 0)
            {
                $query->where('a.id = ' . (int)substr($search, 3));
            } else
            {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $query->order($db->escape($this->state->get('list.ordering', 'a.title')) . ' ' . $db->escape($this->state->get('list.direction', 'ASC')));

        return $query;
    }

    /**
     * @return mixed
     */
    public function getUnpublishedPlugins()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('e.element')
            ->from('#__extensions AS e')
            ->join('INNER', '#__extensions AS p ON (e.element = p.element)')
            ->where('p.enabled = ' . $db->quote(0))
            ->where('e.enabled = ' . $db->quote(1))
            ->where('e.type = ' . $db->quote('component'))
            ->where('p.type = ' . $db->quote('plugin'))
            ->where('p.folder = ' . $db->quote('xmap'));

        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * @param array $ids integers
     *
     * @return bool|array
     */
    public function getItemsByIds(array $ids)
    {
        $db = JFactory::getDbo();

        $ids = ArrayHelper::toInteger($ids);
        $ids = array_filter($ids);

        if (empty($ids))
        {
            return false;
        }

        $query = $this->getListQuery();
        $query->where('a.id IN(' . implode(',', $ids) . ')');

        $db->setQuery($query);

        try
        {
            $result = $db->loadObjectList();

        } catch (Exception $e)
        {
            return false;
        }

        if (empty($result))
        {
            return false;
        }

        foreach ($result as $item)
        {
            $item->params = new Registry($item->params);
        }

        return $result;
    }

    /**
     * @param array $ids
     *
     * @return bool
     */
    public function ping(array $ids)
    {
        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_xmap');

        $items = $this->getItemsByIds($ids);

        if (empty($items))
        {
            $app->enqueueMessage(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'error');

            return false;
        }

        foreach ($this->pings as $engine => $ping)
        {
            // skip disabled search engines
            if (!$params->get('ping_' . $engine, 1))
            {
                continue;
            }

            foreach ($items as $item)
            {
                if ($item->published != 1)
                {
                    $message = JText::sprintf('COM_XMAP_PING_ITEM_NOT_PUBLISHED', $item->title);
                    $app->enqueueMessage($message, 'warning');
                    continue;
                }

                foreach ($this->types as $type => $type_default)
                {
                    // skip disabled types
                    if (!$item->params->get('ping_' . $type, $type_default))
                    {
                        continue;
                    }

                    try
                    {
                        $url = urlencode(JUri::root() . 'index.php?option=com_xmap&view=xml&id=' . $item->id);
                        $result = JHttpFactory::getHttp()->get(sprintf($ping, $url), null, $params->get('ping_timeout', 10));
                    } catch (Exception $e)
                    {
                        $app->enqueueMessage($e->getMessage(), 'error');
                        continue;
                    }

                    $engine = JText::_('COM_XMAP_PING_' . strtoupper($engine) . '_LABEL');

                    if ($result->code == 200)
                    {
                        $type = JText::_('COM_XMAP_' . strtoupper($type) . '_LINK');
                        $message = JText::sprintf('COM_XMAP_PING_PINGED_SUCCESS', $type, $item->title, $engine);
                        $app->enqueueMessage($message);

                    } else
                    {
                        $message = JText::sprintf('COM_XMAP_PING_PINGED_FAILED', $item->title, $engine, $result->code);
                        $app->enqueueMessage($message, 'warning');
                    }
                }
            }
        }

        return true;
    }
}
