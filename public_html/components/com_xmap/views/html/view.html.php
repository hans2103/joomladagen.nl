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

/**
 * Class XmapViewHtml
 */
class XmapViewHtml extends JViewLegacy
{
    /**
     * @var JObject
     */
    protected $state;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    /**
     * @var XmapDisplayerHtml
     */
    protected $displayer;

    /**
     * @var stdClass
     */
    public $item;

    /**
     * @var array
     */
    public $items;

    /**
     * @var array
     */
    protected $extensions;

    /**
     * @var bool
     */
    protected $canEdit;

    /**
     * @param null $tpl
     *
     * @return bool
     */
    function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->params = $this->state->get('params');
        $this->item = $this->get('Item');
        $this->items = $this->get('Items');
        $this->extensions = $this->get('Extensions');

        $this->canEdit = JFactory::getUser()->authorise('core.edit', 'com_xmap.sitemap.' . $this->item->id);

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseWarning(500, implode("\n", $errors));

            return false;
        }

        $this->displayer = new XmapDisplayerHtml($this->item, $this->items, $this->extensions);
        $this->displayer->setCanEdit($this->canEdit);

        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        $this->prepareDocument();

        parent::display($tpl);

        $this->getModel()->hit($this->displayer->getCount());
    }

    /**
     * @throws Exception
     */
    protected function prepareDocument()
    {
        $app = JFactory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu)
        {
            $this->params->def('page_heading', $this->params->get('page_title', $this->item->title));
        } else
        {
            $this->params->def('page_heading', $this->item->title);
        }

        $title = $this->params->get('page_title', '');

        if (empty($title))
        {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1)
        {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2)
        {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description'))
        {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords'))
        {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots'))
        {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }
}
