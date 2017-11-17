<?php
/**
* Copyright (C) 2009  freakedout (www.freakedout.de)
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined('_JEXEC') or die('Restricted Access');

class joomailermailchimpintegrationViewSubscribers extends jmView {

    public function display($tpl = null) {
        JToolBarHelper::title(  JText::_('JM_NEWSLETTER_SUBSCRIBERS'), $this->getPageTitleClass());

        $document = JFactory::getDocument();
        $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/joomlamailer.subscribers.js');

        $option = $this->input->getCmd('option');
        $this->limit = $this->app->getUserStateFromRequest('global.list.limit', 'limit', $this->app->getCfg('list_limit'), 'int');
        $this->limitstart = $this->app->getUserStateFromRequest($option . '.limitstart', 'limitstart', 0, 'int');

        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();

        if ($MCapi && $JoomlamailerMC->pingMC()) {
            JToolBarHelper::custom('goToLists', 'list-2', 'list-2', 'JM_LISTS', false, false);
            JToolBarHelper::spacer();
            if ($this->input->getString('type') == 's') {
                JToolBarHelper::custom('unsubscribe', 'minus-sign', 'minus-sign', 'JM_UNSUBSCRIBE', true, false);
                JToolBarHelper::spacer();
                JToolBarHelper::custom('delete', 'unpublish', 'unpublish', 'JM_DELETE', true, false);
                JText::script('JM_CONFIRM_USER_DELETE');
                JToolBarHelper::spacer();
            } else if ($this->input->getString('type') == 'u') {
                //JToolBarHelper::custom('resubscribe', 'resubscribe', 'resubscribe', 'Resubscribe', false, false);
            }
        }

        // Get data from the model
        $this->members = $this->get('Members');
        $this->getModel()->addJoomlaUserData($this->members['members']);

        $this->lists = $this->get('Lists');

        jimport('joomla.html.pagination');
        $this->pagination = new JPagination($this->members['total_items'], $this->limitstart, $this->limit);

        parent::display($tpl);
        require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
    }
}
