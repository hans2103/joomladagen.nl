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
 * Class XmapViewXml
 */
class XmapViewXml extends JViewLegacy
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
     * @var XmapDisplayerXml
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
    protected $sitemapItems;

    /**
     * @var array
     */
    protected $extensions;

    /**
     * @param null $tpl
     *
     * @return bool
     * @throws Exception
     */
    function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->params = $this->state->get('params');
        $this->item = $this->get('Item');
        $this->items = $this->get('Items');
        $this->sitemapItems = $this->get('SitemapItems');
        $this->extensions = $this->get('Extensions');

        $input = JFactory::getApplication()->input;

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseWarning(500, implode("\n", $errors));

            return false;
        }

        $app = JFactory::getApplication();
        $app->clearHeaders();
        $app->setHeader('Content-Type', 'application/xml; charset=UTF-8');
        $app->sendHeaders();

        $this->displayer = new XmapDisplayerXml($this->item, $this->items, $this->extensions);
        $this->displayer->displayAsNews($input->getBool('news'));
        $this->displayer->displayAsImages($input->getBool('images'));
        $this->displayer->displayAsVideos($input->getBool('videos'));
        $this->displayer->displayAsMobile($input->getBool('mobile'));
        $this->displayer->setSitemapItems($this->sitemapItems);

        parent::display($tpl);

        $this->getModel()->hit($this->displayer->getCount());

        $app->close();
    }
}
