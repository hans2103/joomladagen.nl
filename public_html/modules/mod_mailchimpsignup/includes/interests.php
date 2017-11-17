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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class JFormFieldInterests extends JFormField {

    public function getInput() {
        $mainframe = JFactory::getApplication();

        jimport('joomla.filesystem.file');
        jimport('joomla.application.component.helper');
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/joomailermailchimpintegration.php')
            || !JComponentHelper::isEnabled('com_joomailermailchimpintegration', true)) {
            $mainframe->enqueueMessage(JText::_('JM_PLEASE_INSTALL_JOOMLAMAILER'), 'error');
            $mainframe->redirect('index.php?option=com_modules');
        }

        $listId = $this->form->getValue('listid', 'params');
        if (empty($listId)) {
            return '-- ' . JText::_('JM_PLEASE_SELECT_A_LIST') . ' --';
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/helpers/JoomlamailerMC.php');
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();
        if (!$MCapi || !$JoomlamailerMC->pingMC()) {
            $mainframe->enqueueMessage(JText::_('APIKEY ERROR'), 'error');
            $mainframe->redirect('index.php?option=com_joomailermailchimpintegration&view=main');
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/groups.php');
        $groupsModel = new joomailermailchimpintegrationModelGroups();
        $categories = $groupsModel->getListInterestCategories($listId);

        if (empty($categories['total_items'])) {
            return JText::_('JM_NO_INTEREST_GROUPS');
        }

        if (is_string($this->value)) {
            $this->value = preg_replace('/"/', '\"', $this->value);
            $this->value = preg_replace('/^\[\{/', '["{', $this->value);
            $this->value = preg_replace('/\}\]$/', '}"]', $this->value);
            $this->value = preg_replace('/\},\{/', '}","{', $this->value);
            $this->value = json_decode($this->value);
        }

        $options = array();
        foreach ($categories['categories'] as $category) {
            if ($category['type'] != 'hidden') {
                $interests = $groupsModel->getListInterestCategories($listId, $category['id']);

                $value = array(
                    'id'    => $category['id'],
                    'type'  => $category['type'],
                    'title' => $category['title']
                );

                $groups = array();
                if (!empty($interests['total_items'])) {
                    foreach($interests['interests'] as $interest){
                        $groups[] = array(
                            'id'   => $interest['id'],
                            'name' => $interest['name']
                        );
                    }
                }
                $value['groups'] = $groups;

                $options[] = array(
                    'name'  => $category['title'],
                    'value' => json_encode($value)
                );
            }
        }

        return JHtml::_('select.genericlist', $options, 'jform[params][interests][]', 'multiple="multiple"',
                        'value', 'name', $this->value, $this->id);
    }
}
