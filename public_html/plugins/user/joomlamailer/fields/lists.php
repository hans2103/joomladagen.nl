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

defined('JPATH_PLATFORM') or die;

class JFormFieldLists extends JFormField {

    public function getInput()	{
        $this->app = JFactory::getApplication();

        jimport('joomla.filesystem.file');
        jimport('joomla.application.component.helper');
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/joomailermailchimpintegration.php')
            || !JComponentHelper::isEnabled('com_joomailermailchimpintegration', true)) {
            $this->app->enqueueMessage(JText::_('JM_PLEASE_INSTALL_JOOMLAMAILER'), 'error');
            $this->app->redirect('index.php?option=com_modules');
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/helpers/JoomlamailerMC.php');
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();
        if (!$MCapi || !$JoomlamailerMC->pingMC()) {
            $this->app->enqueueMessage(JText::_('APIKEY ERROR'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=main');
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/lists.php');
        $listsModel = new joomailermailchimpintegrationModelLists();
        $lists = $listsModel->getLists();

        $options = array();
        $options[] = array(
            'id'   => '',
            'name' => '-- ' . JText::_('PLG_USER_JOOMLAMAILER_PLEASE_SELECT_A_LIST') . ' --'
        );
        foreach ($lists['lists'] as $list) {
            $options[] = array(
                'id'   => $list['id'],
                'id'   => $list['id'],
                'name' => $list['name']
            );
        }

        $attribs = 'onchange="submitbutton(\'plugin.apply\')"';
        return JHtml::_('select.genericlist', $options, 'jform[params][listid]', $attribs, 'id', 'name', $this->value, $this->id);
    }
}
