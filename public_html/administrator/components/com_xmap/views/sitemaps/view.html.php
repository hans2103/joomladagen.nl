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

class XmapViewSitemaps extends JViewLegacy
{
    /**
     * @var JObject
     */
    protected $state;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var JPagination
     */
    protected $pagination;

    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        if ($extensions = $this->get('UnpublishedPlugins'))
        {
            $message = JText::sprintf('COM_XMAP_MESSAGE_EXTENSIONS_DISABLED', implode(', ', $extensions));
            JFactory::getApplication()->enqueueMessage($message, 'notice');
        }

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $state = $this->get('State');

        JToolBarHelper::title(JText::_('COM_XMAP_SITEMAPS_TITLE'), 'list');

        $canDo = JHelperContent::getActions('com_xmap', 'sitemap');

        JToolBarHelper::addNew('sitemap.add');
        JToolbarHelper::editList('sitemap.edit');

        if ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::publish('sitemaps.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('sitemaps.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        }

        if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
        {
            JToolbarHelper::deleteList('', 'sitemaps.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::trash('sitemaps.trash');
        }

        if ($canDo->get('core.manage'))
        {
            JToolbarHelper::custom('sitemaps.ping', 'heart', 'heart', JText::_('COM_XMAP_TOOLBAR_PING'));
        }

        if (JFactory::getUser()->authorise('core.admin'))
        {
            JToolbarHelper::preferences('com_xmap');
        }

        if (JHelperContent::getActions('com_plugins')->get('core.edit.state'))
        {
            JToolbarHelper::custom('sitemaps.plugins', 'power-cord', 'power-cord', JText::_('COM_XMAP_TOOLBAR_PLUGINS'), false);
        }

        JHtmlSidebar::setAction('index.php?option=com_xmap&view=sitemaps');

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_state',
            JHtml::_('select.options', XmapHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'))
        );

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_ACCESS'),
            'filter_access',
            JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
        );

        $this->sidebar = JHtmlSidebar::render();
    }

    protected function getSortFields()
    {
        return array(
            'a.published' => JText::_('JSTATUS'),
            'a.title'  => JText::_('JGLOBAL_TITLE'),
            'a.access' => JText::_('JGRID_HEADING_ACCESS'),
            'a.id'     => JText::_('JGRID_HEADING_ID')
        );
    }
}
