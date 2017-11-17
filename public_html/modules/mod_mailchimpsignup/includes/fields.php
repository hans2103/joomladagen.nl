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

class JFormFieldFields extends JFormField {

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

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/fields.php');
        $fieldsModel = new joomailermailchimpintegrationModelFields();
        $fields = $fieldsModel->getMergeFields($listId);

        if (empty($this->value)) {
            $this->value = array();
        } else if (is_string($this->value)) {
            // fix json encoding bug introduced in v3.0.0 beta
            $this->value = json_decode($this->value);
            if (count($this->value)) {
                foreach ($this->value as $index => $value) {
                    $this->value[$index] = json_encode($value);
                }
            }
        }

        $options = array();

        // add Email field and force it to be always selected
        $fieldParams = json_encode(array(
             'type' => 'email',
             'tag'  => 'EMAIL',
             'name' => JText::_('JM_EMAIL'),
             'required' => 1
         ), true);
        $options[] = array(
            'name'  => JText::_('JM_EMAIL'),
            'value' => $fieldParams
        );
        $this->value[] = $options[0]['value'];


        if (!empty($fields['total_items'])) {
            foreach ($fields['merge_fields'] as $field) {
                $fieldParams = array(
                    'type'     => $field['type'],
                    'tag'      => $field['tag'],
                    'name'     => $field['name'],
                    'required' => (int)$field['required']
                );
                if (isset($field['options']['choices'])) {
                    $fieldParams['choices'] = $field['options']['choices'];
                }
                $options[] = array(
                    'name'  => $field['name'],
                    'value' => json_encode($fieldParams)
                );
            }
        }

        return JHtml::_('select.genericlist', $options, 'jform[params][fields][]', 'multiple="multiple"', 'value', 'name',
            $this->value, $this->id);
    }
}
