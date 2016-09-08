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
 * Class XmapDisplayerAbstract
 */
abstract class XmapDisplayerAbstract implements XmapDisplayerInterface, XmapDisplayer
{
    /**
     * @var string
     */
    public $view = '';

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var stdClass
     */
    protected $sitemap;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var array
     */
    protected $extensions;

    /**
     * @var string
     */
    protected $output = '';

    /**
     * @var bool
     */
    protected $isNews = false;

    /**
     * @var bool
     */
    protected $isImages = false;

    /**
     * @var bool
     */
    protected $isVideos = false;

    /**
     * @var bool
     */
    protected $isMobile = false;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @param stdClass $sitemap
     * @param array $items
     * @param array $extensions
     */
    public function __construct(stdClass $sitemap, array &$items, array &$extensions)
    {
        $this->sitemap = $sitemap;
        $this->items = $items;
        $this->extensions = $extensions;

        $this->params = JComponentHelper::getParams('com_xmap');
    }

    /**
     * @todo refactor
     *
     * @param array $items
     *
     * @throws Exception
     */
    protected function printMenuTree(array &$items)
    {
        $this->changeLevel(1);

        $router = JFactory::getApplication()->getRouter();

        foreach ($items as $i => $item)
        {                   // Add each menu entry to the root tree.
            $excludeExternal = false;

            $node = new stdClass;

            $node->id = $item->id;
            $node->uid = $item->uid;
            $node->name = $item->title;                             // displayed name of node
            $node->browserNav = $item->browserNav;                  // how to open link
            $node->priority = $item->priority;
            $node->changefreq = $item->changefreq;
            $node->type = $item->type;                              // menuentry-type
            $node->home = $item->home;                              // If it's a home menu entry
            $node->link = $item->link;
            $node->option = $item->option;
            $node->modified = @$item->modified;
            $node->secure = $item->params->get('secure');

            // New on Xmap 2.0: send the menu params
            $node->params = &$item->params;

            if ($node->home == 1)
            {
                // Correct the URL for the home page.
                $node->link = JUri::base();
            }
            switch ($item->type)
            {
                case 'separator':
                case 'heading':
                    $node->browserNav = 3;
                    break;
                case 'url':
                    if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
                    {
                        // If this is an internal Joomla link, ensure the Itemid is set.
                        $node->link = $node->link . '&Itemid=' . $node->id;
                    } else
                    {
                        $excludeExternal = ($this->view == 'xml');
                    }
                    break;
                case 'alias':
                    // If this is an alias use the item id stored in the parameters to make the link.
                    $node->link = 'index.php?Itemid=' . $item->params->get('aliasoptions');
                    break;
                default:
                    if ($router->getMode() == JROUTER_MODE_SEF)
                    {
                        $node->link = 'index.php?Itemid=' . $node->id;
                    } elseif (!$node->home)
                    {
                        $node->link .= '&Itemid=' . $node->id;
                    }
                    break;
            }

            if ($excludeExternal || $this->printNode($node))
            {

                //Restore the original link
                $node->link = $item->link;
                $this->printMenuTree($item->items);

                if (isset($node->option) && !empty($this->extensions[$node->option]))
                {
                    $node->uid = $node->option;
                    call_user_func_array(array('xmap_' . $node->option, 'getTree'), array(&$this, &$node, &$this->extensions[$node->option]->params));
                }
            }
        }
        $this->changeLevel(-1);
    }

    /**
     * Called on every level change
     *
     * @param integer $level
     *
     * @return boolean
     */
    public function changeLevel($level)
    {
        return true;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @todo refactor
     *
     * @return array
     */
    public function getExcludedItems()
    {
        static $excluded_items;

        if (!isset($excluded_items))
        {
            $excluded_items = array();
            $registry = new Registry;
            $registry->loadString($this->sitemap->excluded_items);
            $excluded_items = $registry->toArray();
        }

        return $excluded_items;
    }

    /**
     * @todo refactor
     *
     * @param $itemid
     * @param $uid
     *
     * @return bool
     */
    public function isExcluded($itemid, $uid)
    {
        $excludedItems = $this->getExcludedItems();
        $items = null;

        if (!empty($excludedItems[$itemid]))
        {
            if (is_object($excludedItems[$itemid]))
            {
                $excludedItems[$itemid] = (array)$excludedItems[$itemid];
            }
            $items =& $excludedItems[$itemid];
        }

        if (!$items)
        {
            return false;
        }

        return in_array($uid, $items);
    }

    /**
     * @param $var
     *
     * @return mixed
     */
    public function __get($var)
    {
        if (!in_array($var, array('isNews', 'isImages', 'isVideos', 'isMobile')))
        {
            throw new InvalidArgumentException(JText::_('ERROR'));
        }

        return $this->$var;
    }
}
