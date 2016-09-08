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
 * Class XmapDisplayerHtml
 */
class XmapDisplayerHtml extends XmapDisplayerAbstract
{
    /**
     * @var string
     */
    public $view = 'html';

    /**
     * @var int
     */
    protected $level = -1;

    /**
     * @var string
     */
    protected $openList = '';

    /**
     * @var string
     */
    protected $closeItem = '';

    /**
     * @var array
     */
    protected $childs = array();

    /**
     * @var integer
     */
    protected $width = 0;

    /**
     * @var array
     */
    protected $parent_children = array();

    /**
     * @var array
     */
    protected $last_child = array();

    /**
     * @var bool
     */
    protected $canEdit = false;

    /**
     * @param stdClass $sitemap
     * @param array $items
     * @param array $extensions
     */
    public function __construct(stdClass $sitemap, array &$items, array &$extensions)
    {
        parent::__construct($sitemap, $items, $extensions);

        $columns = $this->sitemap->params->get('columns', 0);
        if ($columns > 1)
        { // calculate column widths
            $total = count($this->items);
            $columns = $total < $columns ? $total : $columns;
            $this->width = (100 / $columns) - 1;
            $this->sitemap->params->set('columns', $columns);
        }
    }

    /**
     * @return string
     */
    public function printSitemap()
    {
        foreach ($this->items as $menutype => &$items)
        {

            $node = new stdClass;
            $node->uid = 'menu-' . $menutype;
            $node->menutype = $menutype;
            $node->priority = null;
            $node->changefreq = null;
            $node->browserNav = 3;
            $node->type = 'separator';

            // TODO allow the user to provide the module used to display that menu, or some other workaround
            $node->name = $this->getMenuTitle($menutype);

            $this->startMenu($node);
            $this->printMenuTree($items);
            $this->endMenu($node);
        }

        return $this->output;
    }

    /**
     * @param stdClass $node
     *
     * @return bool
     */
    function printNode(stdClass $node)
    {
        $out = '';

        if ($this->isExcluded($node->id, $node->uid) && !$this->canEdit)
        {
            return false;
        }

        // To avoid duplicate children in the same parent
        if (!empty($this->parent_children[$this->level][$node->uid]))
        {
            return false;
        }

        $this->parent_children[$this->level][$node->uid] = true;

        $out .= $this->closeItem;
        $out .= $this->openList;
        $this->openList = "";

        $out .= '<li>';

        if (!isset($node->browserNav))
            $node->browserNav = 0;

        if ($node->browserNav != 3)
        {
            $link = JRoute::_($node->link, true, @$node->secure);
        } else
        {
            $link = $node->link;
        }

        $attributes = array('title' => $node->name);

        switch ($node->browserNav)
        {
            case 1: // open url in new window
            case 2: // open url in javascript popup window
                $attributes['target'] = '_blank';
                $out .= JHtml::_('link', $link, $node->name, $attributes);
                break;

            case 3: // no link
                $out .= '<span>' . $node->name . '</span>';
                break;

            default: // open url in parent window
                $out .= JHtml::_('link', $link, $node->name, $attributes);
                break;
        }

        $this->closeItem = '</li>' . PHP_EOL;
        $this->childs[$this->level]++;

        $this->output .= $out;

        if ($this->canEdit)
        {
            if ($this->isExcluded($node->id, $node->uid))
            {
                $title = JText::_('JUNPUBLISHED');
                $class = 'icon-remove-sign';
            } else
            {
                $class = 'icon-ok-sign';
                $title = JText::_('JPUBLISHED');
            }
            $this->output .= '&nbsp;<i data-id="' . $this->sitemap->id . '" data-uid="' . $node->uid . '" data-itemid="' . $node->id . '" class="hasTooltip ' . $class . '" title="' . $title . '"></i>';
        }
        $this->count++;

        $this->last_child[$this->level] = $node->uid;

        return true;
    }

    /**
     * @param int $level
     *
     * @return void
     */
    public function changeLevel($level)
    {
        if ($level > 0)
        {
            # We do not print start ul here to avoid empty list, it's printed at the first child
            $this->level += $level;
            $this->childs[$this->level] = 0;
            $this->openList = PHP_EOL . '<ul class="level_' . $this->level . '">' . PHP_EOL;
            $this->closeItem = '';

            // If we are moving up, then lets clean the children of this level
            // because for sure this is a new set of links
            if (
                empty ($this->last_child[$this->level - 1])
                || empty ($this->parent_children[$this->level]['parent'])
                || $this->parent_children[$this->level]['parent'] != $this->last_child[$this->level - 1]
            )
            {
                $this->parent_children[$this->level] = array();
                $this->parent_children[$this->level]['parent'] = @$this->last_child[$this->level - 1];
            }
        } else
        {
            if ($this->childs[$this->level])
            {
                $this->output .= $this->closeItem . '</ul>' . PHP_EOL;
            }

            $this->closeItem = '</li>' . PHP_EOL;
            $this->openList = '';
            $this->level += $level;
        }
    }

    /**
     * Function called before displaying the menu
     *
     * @param stdClass $node The menu node item
     *
     * @return boolean
     */
    protected function startMenu(stdClass $node)
    {
        if ($this->sitemap->params->get('columns') > 1)
        {
            $this->output .= '<div style="float:left;width:' . $this->width . '%;">' . PHP_EOL;
        }

        if ($this->sitemap->params->get('show_menutitle'))
        {
            $this->output .= '<h2 class="menutitle">' . $node->name . '</h2>' . PHP_EOL;
        }
    }

    /**
     * Function called after displaying the menu
     *
     * @param stdClass $node The menu node item
     *
     * @return boolean
     */
    protected function endMenu(stdClass $node)
    {
        $this->closeItem = '';
        if ($this->sitemap->params->get('columns') > 1)
        {
            $this->output .= '</div>' . PHP_EOL;
        }
    }

    /**
     * @param bool $val
     */
    public function setCanEdit($val)
    {
        $this->canEdit = (bool)$val;
    }

    /**
     * @param string $menutype
     *
     * @return null|string
     * @throws Exception
     */
    protected function getMenuTitle($menutype)
    {
        static $modules = null;

        if (is_null($modules))
        {
            /** @var JApplicationSite $app */
            $app = JFactory::getApplication();
            $db = JFactory::getDbo();
            $user = JFactory::getUser();
            $modules = array();

            $query = $db->getQuery(true)
                ->select('m.params')
                ->select('m.title')
                ->from('#__modules AS m')
                ->where('m.module = ' . $db->quote('mod_menu'))
                ->where('m.published = ' . $db->quote(1))
                ->where('m.client_id = ' . $db->quote(0))
                ->where('m.access IN(' . $db->quote(implode(',', $user->getAuthorisedViewLevels())) . ')');

            if ($app->getLanguageFilter())
            {
                $query->where('m.language IN(' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
            }

            $db->setQuery($query);

            $result = $db->loadObjectList();

            if (!empty($result))
            {
                foreach ($result as $module)
                {
                    $module->params = new Registry($module->params);
                    $module->menutype = $module->params->get('menutype');
                    $modules[$module->menutype] = $module;
                }
            }
        }

        if (isset($modules[$menutype]))
        {
            return $modules[$menutype]->title;
        }

        return null;
    }
}
