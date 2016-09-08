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

class XmapViewSitemap extends JViewLegacy
{
    /**
     * @var JObject
     */
    protected $item;

    /**
     * @var JForm
     */
    protected $form;

    /**
     * @var JObject
     */
    protected $state;

    /**
     * @var JObject
     */
    protected $canDo;

    public function display($tpl = null)
    {
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');
        $this->state = $this->get('State');
        $this->canDo = JHelperContent::getActions('com_xmap', 'sitemap');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        $this->handleMenues();

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);

        JToolBarHelper::title(JText::_('COM_XMAP_PAGE_' . ($isNew ? 'ADD_SITEMAP' : 'EDIT_SITEMAP')), 'list');

        if ($isNew && $this->canDo->get('core.create'))
        {
            JToolBarHelper::apply('sitemap.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('sitemap.save', 'JTOOLBAR_SAVE');
            JToolBarHelper::save2new('sitemap.save2new');
        } else if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
        {
            JToolBarHelper::apply('sitemap.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('sitemap.save', 'JTOOLBAR_SAVE');
        }

        if ($this->canDo->get('core.create'))
        {
            JToolBarHelper::save2copy('sitemap.save2copy');
        }

        JToolBarHelper::cancel('sitemap.cancel', 'JTOOLBAR_CLOSE');
    }

    protected function handleMenues()
    {
        $menues = $this->get('Menues');

        // remove non existing menutypes from selection
        foreach ($this->item->selections as $menutype => $options)
        {
            if (!isset($menues[$menutype]))
            {
                unset($this->item->selections[$menutype]);
            }
        }

        foreach ($menues as $menu)
        {
            if (isset($this->item->selections[$menu->menutype]))
            {
                $this->item->selections[$menu->menutype]['selected'] = true;
                $this->item->selections[$menu->menutype]['title'] = $menu->title;
                $this->item->selections[$menu->menutype]['menutype'] = $menu->menutype;
            } else
            {
                $this->item->selections[$menu->menutype] = (array)$menu;
                $this->item->selections[$menu->menutype]['selected'] = false;
                $this->item->selections[$menu->menutype]['priority'] = 0.5;
                $this->item->selections[$menu->menutype]['changefreq'] = 'weekly';
            }
        }
    }
}
