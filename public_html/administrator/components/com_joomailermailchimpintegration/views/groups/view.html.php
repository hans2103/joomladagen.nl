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

class joomailermailchimpintegrationViewGroups extends jmView {

	public function display($tpl = null) {
        $layout = $this->input->getCmd('layout', 'default');
        $this->listId = $this->input->getString('listId');

        $this->setModel($this->getModelInstance('lists'));
		$this->interestCategories = $this->getModel('lists')->getListInterestCategories($this->listId);
        if ($this->interestCategories['total_items'] > 0) {
            foreach ($this->interestCategories['categories'] as $index => $category) {
                $res = $this->getModel('lists')->getListInterestCategories($this->listId, $category['id']);
                if ($res['total_items'] > 0) {
                    $this->interestCategories['categories'][$index]['options'] = array();
                    foreach ($res['interests'] as $interest) {
                        $this->interestCategories['categories'][$index]['options'][] = $interest['name'];
                    }
                }
            }
        }

        if ($layout != 'form' && !$this->interestCategories) {
            $this->app->enqueueMessage(JText::_('JM_NO_CUSTOM_FIELDS'), 'notice');
        }

		$this->listName = $this->input->getString('name', $this->input->getString('listName', ''));
        $title = ($this->listName) ? " ({$this->listName})" : '';

		if ($layout == 'form') {
			JToolBarHelper::title(JText::_('JM_NEWSLETTER_NEW_CUSTOM_FIELD') . $title, $this->getPageTitleClass());
			JToolBarHelper::save();
			JToolBarHelper::spacer();
			JToolBarHelper::cancel();

            $this->CBfields = $this->get('CBfields');
            $this->JSfields = $this->get('JSfields');
            $this->VMfields = $this->get('VMfields');

            $this->name = '';
            $this->CBeditID = '';
            $this->JSeditID = '';
            $this->fieldId = '';
            $this->groupingId = '';

            $cid = $this->input->getString('cid', '');
            if (isset($cid[0])){
                $query = $this->db->getQuery(true)
                    ->select('*')
                    ->from('#__joomailermailchimpintegration_custom_fields')
                    ->where($this->db->qn('grouping_id') . ' = ' . $this->db->q($cid[0]));
                $fieldData = $this->db->setQuery($query)->loadObject();

                if ($fieldData) {
                    $this->fieldId = $fieldData->id;
                    $this->groupingId = $fieldData->grouping_id;
                    if ($fieldData->framework == 'CB') {
                        $this->CBeditID = $fieldData->dbfield;
                        $this->JSeditID = '';
                    } else if ($fieldData->framework == 'JS'){
                        $this->CBeditID = '';
                        $this->JSeditID = $fieldData->dbfield;
                    }
                    $this->name = '';
                    foreach($this->interestCategories['categories'] as $f) {
                        if ($f['id'] == $fieldData->grouping_id) {
                            $this->name = $f['title'];
                            break;
                        }
                    }
                }
            }
		} else {
			JToolBarHelper::title(JText::_('JM_NEWSLETTER_CUSTOM_FIELDS') . $title, $this->getPageTitleClass());
			JToolBarHelper::custom('goToLists', 'list-2', 'list-2', 'JM_LISTS', false, false);
			JToolBarHelper::spacer();
			JToolBarHelper::addNew();
            /*JToolBarHelper::spacer();
            JToolBarHelper::editList();*/ /** Editing interest categories (groups) not supported by MailChimp API **/
			JToolBarHelper::spacer();
			JToolBarHelper::deleteList();
		}

		parent::display($tpl);
		require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
	}
}
