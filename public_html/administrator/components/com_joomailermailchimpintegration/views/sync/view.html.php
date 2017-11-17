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

class joomailermailchimpintegrationViewSync extends jmView {

    public function display($tpl = null) {
        $document = JFactory::getDocument();
        $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/joomlamailer.sync.js');

        $script = '!function($){
    $(document).ready(function(){
        joomlamailerJS.strings.addSelectedUsers = "' . JText::_('JM_ADD_SELECTED_USERS', true) . '";
        joomlamailerJS.strings.addAllUsers = "' . JText::_('JM_ADD_ALL_USERS', true) . '";
        joomlamailerJS.strings.addAllUsersConfirm = "' . JText::_('JM_ARE_YOU_SURE_TO_ADD_ALL_USERS', true) . '";
        joomlamailerJS.strings.selectAList = "' . JText::_('JM_SELECT_A_LIST_TO_ASSIGN_THE_USERS_TO', true) . '";
        joomlamailerJS.strings.noUsersSelected = "' . JText::_('JM_NO_USERS_SELECTED', true) . '";
        joomlamailerJS.strings.usersAlreadyAdded = "' . JText::_('JM_ALL_USERS_ALREADY_ADDED', true) . '";

        joomlamailerJS.strings.addingUsers = "' . JText::_('JM_ADDING_USERS', true) . '";
        joomlamailerJS.strings.done = "' . JText::_('JM_DONE', true) . '";


    });
}(jQuery);';
        $document->addScriptDeclaration($script);

        $layout = $this->input->getString('layout', 'default');
        if ($layout == 'default') {
            /** DELETE batches */
            /*$res = $this->getModel('sync')->getMcObject()->batches('GET');
            foreach ($res['batches'] as $batch) {
                $this->getModel('sync')->getMcObject()->batches('DELETE', $batch['id']);
            }die;*/

            $option = $this->input->getCmd('option');
            $filter_order = $this->app->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'a.name',    'cmd');
            $filter_order_Dir = $this->app->getUserStateFromRequest($option . 'filter_order_Dir',    'filter_order_Dir',    '',    'word');
            $filter_type = $this->app->getUserStateFromRequest($option . 'filter_type', 'filter_type', 0, 'string');
            $filter_date = $this->app->getUserStateFromRequest($option . 'filter_date', 'filter_date', '', 'string');
            $search = \Joomla\String\StringHelper::strtolower($this->app->getUserStateFromRequest($option . 'search', 'search', '',    'string'));

            $this->filters = array();

            // get list of Groups for dropdown filter
            require_once(JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php');
            $dropdown = '<select name="filter_type" id="filter_type" class="left" onchange="this.form.submit()">';
            $dropdown .= '<option value="">- ' . JText::_('JM_USERGROUP') . ' -</option>';
            $dropdown .=  JHtml::_('select.options', UsersHelper::getGroups(), 'value', 'text', $filter_type);
            $dropdown .= '</select>';
            $this->filters['type'] = $dropdown;

            // table ordering
            $this->filters['order_Dir']	= $filter_order_Dir;
            $this->filters['order']	= $filter_order;

            // search filter
            $this->filters['search'] = $search;

            //date filter
            JHtml::_('behavior.calendar');
            $attr = array('size' => '16', 'style' => 'top:0;', 'placeholder' => JText::_('JM_LAST_VISIT_AFTER'));
            $this->filters['filter_date'] = JHtml::_('calendar', $filter_date, 'filter_date', 'filter_date', '%Y-%m-%d', $attr);

            JToolBarHelper::title(JText::_('JM_NEWSLETTER_ADD_USERS'), $this->getPageTitleClass());
        } else if ($layout=='sugar') {
            JToolBarHelper::title(JText::_('JM_NEWSLETTER_SUGARCRM_CONFIGURATION'), $this->getPageTitleClass());
        } else if ($layout=='highrise') {
            JToolBarHelper::title(JText::_('JM_NEWSLETTER_HIGHRISE_CONFIGURATION'), $this->getPageTitleClass());
        }

        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi  = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();

        if ($MCapi && $JoomlamailerMC->pingMC()) {
            if ($layout == 'default') {
                if ($params->get('params.sugar_name') && $params->get('params.sugar_pwd')){
                    JToolBarHelper::custom('sync_sugar', 'sync_sugar', 'sync_sugar', 'JM_ADD_TO_SUGAR', false, false);
                    JToolBarHelper::spacer();
                    JToolBarHelper::custom('sugar', 'sync_sugar', 'sync_sugar', 'JM_SUGAR_CONFIG', false, false);
                    JToolBarHelper::spacer();
                }
                if ($params->get('params.highrise_url') && $params->get('params.highrise_api_token')){
                    JToolBarHelper::custom('sync_highrise', 'sync_highrise', 'sync_highrise', 'JM_ADD_TO_HIGHRISE', false, false);
                    JToolBarHelper::spacer();
                    JToolBarHelper::custom('highrise', 'sync_highrise', 'sync_highrise', 'JM_HIGHRISE_CONFIG', false, false);
                    JToolBarHelper::spacer();
                }
                JToolBarHelper::custom('mailchimp', 'loop', 'loop', 'JM_ADD_TO_MAILCHIMP', false, false);
                JToolBarHelper::spacer();
            } else if ($layout == 'sugar') {
                JToolBarHelper::custom('cancel', 'back', 'back', 'JM_BACK', false, false);
                JToolBarHelper::spacer();
                JToolBarHelper::custom('setConfig', 'sync_sugar', 'sync_sugar', 'JM_SAVE_CONFIG', false, false);
                JToolBarHelper::spacer();
            } else if ($layout == 'highrise') {
                JToolBarHelper::custom('cancel', 'back', 'back', 'JM_BACK', false, false);
                JToolBarHelper::spacer();
                JToolBarHelper::custom('setConfig', 'sync_highrise', 'sync_highrise', 'JM_SAVE_CONFIG', false, false);
                JToolBarHelper::spacer();
            }
        }

        if ($layout == 'default') {
            // Get data from the model
            $this->items = $this->get('Data');
            $this->setModel($this->getModelInstance('lists'));
            $this->lists = $this->getModel('lists')->getLists();
            $this->groups = $this->get('Groups');
            $this->CRMusers = $this->get('CRMusers');
        } else if ($layout == 'sugar') {
            $this->sugarFields = $this->get('SugarFields');
            $this->JSFields = $this->get('JSFields');
            $this->CBFields = $this->get('CBFields');
            $this->config = $this->getModel('sync')->getConfig('sugar');
        } else if ($layout == 'highrise') {
            $this->JSFields = $this->get('JSFields');
            $this->CBFields = $this->get('CBFields');
            $this->config = $this->getModel('sync')->getConfig('highrise');
        }

        $this->total = $this->get('TotalUsers');

        parent::display($tpl);
        require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
    }
}
