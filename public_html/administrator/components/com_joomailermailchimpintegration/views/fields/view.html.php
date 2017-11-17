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

class joomailermailchimpintegrationViewFields extends jmView {

    public function display($tpl = null) {
        $layout = $this->input->getCmd('layout');
        if ($layout == 'form') {
            $cid = $this->input->getString('cid','');
            if ($cid) {
                JToolBarHelper::title(JText::_('JM_NEWSLETTER_EDIT_MERGE_FIELD'), $this->getPageTitleClass());
            } else {
                JToolBarHelper::title(JText::_('JM_NEWSLETTER_NEW_MERGE_FIELD'), $this->getPageTitleClass());
            }

            JToolBarHelper::save();
            JToolBarHelper::apply();
            JToolBarHelper::spacer();
            JToolBarHelper::cancel();

            $this->CBfields = $this->get('CBfields');
            $this->JSfields = $this->get('JSfields');
            $this->VMfields = $this->get('VMfields');

            $this->item = array(
                'listId'   => $this->input->getString('listId'),
                'name'     => '',
                'tag'      => '',
                'type'     => '',
                'required' => 0,
                'choices'  => array()
            );

            $fieldData = array();
            $fieldData[0] = new stdClass();
            $fieldData[0]->dbfield = '';

            if (isset($cid[0])) {
                list($this->item['mergeId'], $this->item['name'], $this->item['tag'], $this->item['type'],
                    $this->item['required'], $this->item['choices']) = explode(';', $cid[0]);
                $this->item['choices'] = explode('||', $this->item['choices']);

                $query = $this->db->getQuery(true)
                    ->select($this->db->qn(array('id', 'grouping_id', 'framework', 'dbfield')))
                    ->from($this->db->qn('#__joomailermailchimpintegration_custom_fields'))
                    ->where($this->db->qn('grouping_id') . ' = ' . $this->db->q($this->item['tag']));
                $fieldData = $this->db->setQuery($query)->loadObjectList();

                if (isset($fieldData[0])) {
                    $fieldId = $fieldData[0]->id;
                    $groupingId = $fieldData[0]->grouping_id;
                    if ($fieldData[0]->framework == 'CB') {
                        $this->CBeditID = $fieldData[0]->dbfield;
                        $JSeditID = '';
                        $VMeditID = '';
                    } else if ($fieldData[0]->framework == 'JS') {
                        $this->CBeditID = '';
                        $JSeditID = $fieldData[0]->dbfield;
                        $VMeditID = '';
                    } else if ($fieldData[0]->framework == 'VM') {
                        $this->CBeditID = '';
                        $JSeditID = '';

                        $query = $this->db->getQuery(true)
                            ->select($this->db->qn('fieldid'))
                            ->from($this->db->qn('#__vm_userfield'))
                            ->where($this->db->qn('name') . ' = ' . $this->db->q($fieldData[0]->dbfield));
                        $VMeditID = $this->db->setQuery($query)->loadResult();
                    }
                } else {
                    $fieldId	= '';
                    $groupingId	= '';
                    $this->CBeditID	= '';
                    $JSeditID	= '';
                    $VMeditID	= '';

                    $fieldData = array();
                    $fieldData[0] = new stdClass();
                    $fieldData[0]->dbfield = '';
                }
            } else {
                $name	    = '';
                $this->CBeditID   = '';
                $JSeditID   = '';
                $VMeditID   = '';
                $fieldId    = '';
                $groupingId = '';
            }

            $types = array(
                array('type' => '0', 'name' => '--- ' . JText::_('JM_SELECT_A_DATA_TYPE') . ' ---'),
                array('type' => 'text','name' => 'text'),
                array('type' => 'email','name' => 'email'),
                array('type' => 'number','name' => 'number'),
                array('type' => 'radio','name' => 'radio'),
                array('type' => 'dropdown','name' => 'dropdown'),
                array('type' => 'date','name' => 'date'),
                array('type' => 'birthday','name' => 'birthday'),
                array('type' => 'address','name' => 'address'),
                array('type' => 'phone','name' => 'phone'),
                array('type' => 'url','name' => 'url'),
                array('type' => 'imageurl','name' => 'imageurl')
            );
            $this->typeDropDown = JHTML::_('select.genericlist', $types, 'type', '', 'type', 'name' , array($this->item['type']));

            $firstoption = new stdClass();
            $firstoption->id = 0;
            $firstoption->name = '--- ' . JText::_('JM_SELECT_FIELD') . ' ---';
            $this->JSDropDown = '';
            if ($this->JSfields) {
                $this->JSfields = array_merge(array($firstoption), $this->JSfields);
                $this->JSDropDown = JHTML::_('select.genericlist', $this->JSfields, 'JSfield', 'id="JSField" style="min-width:303px;"', 'id', 'name', array($fieldData[0]->dbfield));
            }

            $this->VMDropDown = '';
            if ($this->VMfields) {
                $this->VMfields = array_merge(array($firstoption),$this->VMfields);
                $this->VMDropDown = JHTML::_('select.genericlist', $this->VMfields, 'VMfield', 'id="VMfield" style="min-width:303px;"', 'id', 'name', array($VMeditID));
            }
        } else {
            $this->listId = $this->input->getString('listId', '');
            $this->fields = $this->get('MergeFields');
            $this->name = $this->input->getString('name', $this->input->getString('listName', ''));
            $title = ($this->name) ? ' (' . $this->name . ')' : '';

            JToolBarHelper::title(JText::_('JM_NEWSLETTER_CUSTOM_MERGE_FIELDS') . $title, $this->getPageTitleClass());
            JToolBarHelper::custom('goToLists', 'list-2', 'list-2', 'JM_LISTS', false, false);
            JToolBarHelper::spacer();
            JToolBarHelper::addNew();
            JToolBarHelper::spacer();
            JToolBarHelper::editList();
            JToolBarHelper::spacer();
            JToolBarHelper::deleteList(JText::_('JM_ARE_YOU_SURE_TO_DELETE_THIS_MERGE_FIELD'));
        }

        parent::display($tpl);
        require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
    }
}
