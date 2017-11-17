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

class JFormFieldInterests extends JFormField {

    public function getInput() {
        $this->app = JFactory::getApplication();

        jimport('joomla.filesystem.file');
        jimport('joomla.application.component.helper');
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/joomailermailchimpintegration.php')
            || !JComponentHelper::isEnabled('com_joomailermailchimpintegration', true)) {
            $this->app->enqueueMessage(JText::_('JM_PLEASE_INSTALL_JOOMLAMAILER'), 'error');
            $this->app->redirect('index.php?option=com_plugins');
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/helpers/JoomlamailerMC.php');
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();
        if (!$MCapi || !$JoomlamailerMC->pingMC()) {
            $this->app->enqueueMessage(JText::_('APIKEY ERROR'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=main');
        }

        $listId = $this->form->getValue('listid', 'params');
        if (empty($listId)) {
            return JText::_('PLG_USER_JOOMLAMAILER_PLEASE_SELECT_A_LIST');
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/groups.php');
        $groupsModel = new joomailermailchimpintegrationModelGroups();
        $interests = $groupsModel->getListInterestCategories($listId);

        if (!empty($interests['total_items'])) {
            $options = array();
            foreach ($interests['categories'] as $interest) {
                if ($interest['type'] != 'hidden') {
                    $options[] = array(
                        'id'   => $interest['id'],
                        'title' => $interest['title']
                    );
                }
            }
            if (count($options)) {
                return JHtml::_('select.genericlist', $options, 'jform[params][interests][]', 'multiple="multiple"',
                    'id', 'title', $this->value, $this->id);
            }
        }

        return JText::_('PLG_USER_JOOMLAMAILER_NO_INTEREST_GROUPS');
    }
}
