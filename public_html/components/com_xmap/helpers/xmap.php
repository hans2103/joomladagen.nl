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

JLoader::import('joomla.filesystem.file');

/**
 * Class XmapHelper
 */
abstract class XmapHelper
{
    /**
     * @var array
     */
    protected static $extensions = null;

    /**
     * @var string
     */
    protected static $languageCode = null;

    /**
     * @var array
     */
    public static $instances = array();

    /**
     * there is currently no fu***ing other way to get the short language code in Joomla :(
     *
     * @return string
     */
    public static function getLanguageCode()
    {
        if (is_null(self::$languageCode))
        {
            $languages = JLanguageHelper::getLanguages('lang_code');
            self::$languageCode = $languages[JFactory::getLanguage()->getTag()]->sef;
        }

        return self::$languageCode;
    }

    /**
     * @todo refactor (reduce sql queries)
     *
     * @param $selections
     *
     * @return array
     * @throws Exception
     */
    public static function getMenuItems($selections)
    {
        /** @var JApplicationSite $app */
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $list = array();

        foreach ($selections as $menutype => $menuOptions)
        {
            // Initialize variables.
            // Get the menu items as a tree.
            $query = $db->getQuery(true);
            $query->select(
                'n.id, n.title, n.alias, n.path, n.level, n.link, '
                . 'n.type, n.params, n.home, n.parent_id'
                . ',n.' . $db->quoteName('browserNav')
            );
            $query->from('#__menu AS n');
            $query->join('INNER', ' #__menu AS p ON p.lft = 0');
            $query->where('n.lft > p.lft');
            $query->where('n.lft < p.rgt');
            $query->order('n.lft');

            // Filter over the appropriate menu.
            $query->where('n.menutype = ' . $db->quote($menutype));

            // Filter over authorized access levels and publishing state.
            $query->where('n.published = 1');
            $query->where('n.access IN (' . implode(',', (array)$user->getAuthorisedViewLevels()) . ')');

            // Filter by language
            if ($app->getLanguageFilter())
            {
                $query->where('n.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
            }

            $db->setQuery($query);

            try
            {
                $tmpList = $db->loadObjectList('id');
            } catch (RuntimeException $e)
            {
                JError::raise(E_WARNING, $e->getCode(), $e->getMessage());

                return array();
            }

            $list[$menutype] = array();

            // Set some values to make nested HTML rendering easier.
            foreach ($tmpList as $id => $item)
            {
                $item->items = array();

                $params = new Registry($item->params);
                $item->uid = 'itemid' . $item->id;

                if (preg_match('#^/?index.php.*option=(com_[^&]+)#', $item->link, $matches))
                {
                    $item->option = $matches[1];
                    $componentParams = clone(JComponentHelper::getParams($item->option));
                    $componentParams->merge($params);
                    //$params->merge($componentParams);
                    $params = $componentParams;
                } else
                {
                    $item->option = null;
                }

                $item->params = $params;

                if ($item->type != 'separator')
                {

                    $item->priority = $menuOptions['priority'];
                    $item->changefreq = $menuOptions['changefreq'];

                    self::prepareMenuItem($item);
                } else
                {
                    $item->priority = null;
                    $item->changefreq = null;
                }

                if ($item->parent_id > 1)
                {
                    $tmpList[$item->parent_id]->items[$item->id] = $item;
                } else
                {
                    $list[$menutype][$item->id] = $item;
                }
            }
        }

        return $list;
    }

    /**
     * @return array|null
     */
    public static function getExtensions()
    {
        if (is_null(self::$extensions))
        {
            $db = JFactory::getDbo();

            // init as array so this method called be only once
            self::$extensions = array();

            $query = $db->getQuery(true)
                ->select('e.element')
                ->select('e.folder')
                ->select('e.params')
                ->from('#__extensions AS e')
                ->where('e.folder = ' . $db->quote('xmap'))
                ->where('e.enabled = ' . $db->quote(1));

            $db->setQuery($query);

            try
            {
                $extensions = $db->loadObjectList('element');
            } catch (RuntimeException $e)
            {
                return self::$extensions;
            }

            if (empty($extensions))
            {
                return self::$extensions;
            }

            foreach ($extensions as $element => $extension)
            {
                // file_exists should be not required if extension marked as enabled?!
                if (JFile::exists(JPATH_PLUGINS . '/' . $extension->folder . '/' . $element . '/' . $element . '.php'))
                {
                    require_once(JPATH_PLUGINS . '/' . $extension->folder . '/' . $element . '/' . $element . '.php');
                    $params = new Registry($extension->params);
                    $extension->params = $params->toArray();
                }
            }

            self::$extensions = $extensions;
        }

        return self::$extensions;
    }

    /**
     * Call the function prepareMenuItem of the extension for the item (if any)
     *
     * @param $item stdClass
     *
     * @return bool
     */
    public static function prepareMenuItem(stdClass $item)
    {
        $extensions = self::getExtensions();
        $className = 'xmap_' . $item->option;

        if (empty($extensions[$item->option]))
        {
            return false;
        }

        // create only one instance
        if (!isset(self::$instances[$className]))
        {
            self::$instances[$className] = new $className;
        }

        if (method_exists(self::$instances[$className], 'prepareMenuItem'))
        {
            call_user_func_array(array(self::$instances[$className], 'prepareMenuItem'), array(&$item, &$extensions[$item->option]->params));

            return true;
        }

        return false;
    }

    /**
     * @todo used in xmap_com_content, change com_content plugin to use attached article images
     *
     * @param $text
     * @param $max
     *
     * @return array|null
     */
    public static function getImages($text, $max)
    {
        $urlBase = JUri::base();
        $urlBaseLen = strlen($urlBase);

        $images = null;
        $matches1 = $matches2 = array();
        // Look <img> tags
        preg_match_all('/<img[^>]*?(?:(?:[^>]*src="(?P<src>[^"]+)")|(?:[^>]*alt="(?P<alt>[^"]+)")|(?:[^>]*title="(?P<title>[^"]+)"))+[^>]*>/i', $text, $matches1, PREG_SET_ORDER);
        // Loog for <a> tags with href to images
        preg_match_all('/<a[^>]*?(?:(?:[^>]*href="(?P<src>[^"]+\.(gif|png|jpg|jpeg))")|(?:[^>]*alt="(?P<alt>[^"]+)")|(?:[^>]*title="(?P<title>[^"]+)"))+[^>]*>/i', $text, $matches2, PREG_SET_ORDER);
        $matches = array_merge($matches1, $matches2);
        if (count($matches))
        {
            $images = array();

            $count = count($matches);
            $j = 0;
            for ($i = 0; $i < $count && $j < $max; $i++)
            {
                if (trim($matches[$i]['src']) && (substr($matches[$i]['src'], 0, 1) == '/' || !preg_match('/^https?:\/\//i', $matches[$i]['src']) || substr($matches[$i]['src'], 0, $urlBaseLen) == $urlBase))
                {
                    $src = $matches[$i]['src'];
                    if (substr($src, 0, 1) == '/')
                    {
                        $src = substr($src, 1);
                    }
                    if (!preg_match('/^https?:\//i', $src))
                    {
                        $src = $urlBase . $src;
                    }
                    $image = new stdClass;
                    $image->src = $src;
                    $image->title = (isset($matches[$i]['title']) ? $matches[$i]['title'] : @$matches[$i]['alt']);
                    $images[] = $image;
                    $j++;
                }
            }
        }

        return $images;
    }

    /**
     * @param $text
     * @param $baseLink
     *
     * @return array
     */
    public static function getPagebreaks($text, $baseLink)
    {
        $matches = $subnodes = array();
        if (preg_match_all(
            '/<hr\s*[^>]*?(?:(?:\s*alt="(?P<alt>[^"]+)")|(?:\s*title="(?P<title>[^"]+)"))+[^>]*>/i',
            $text, $matches, PREG_SET_ORDER)
        )
        {
            $i = 2;
            foreach ($matches as $match)
            {
                if (strpos($match[0], 'class="system-pagebreak"') !== false)
                {
                    $link = $baseLink . '&limitstart=' . ($i - 1);

                    if (@$match['alt'])
                    {
                        $title = stripslashes($match['alt']);
                    } elseif (@$match['title'])
                    {
                        $title = stripslashes($match['title']);
                    } else
                    {
                        $title = JText::sprintf('Page #', $i);
                    }
                    $subnode = new stdClass();
                    $subnode->name = $title;
                    $subnode->expandible = false;
                    $subnode->link = $link;
                    $subnodes[] = $subnode;
                    $i++;
                }
            }

        }

        return $subnodes;
    }
}
