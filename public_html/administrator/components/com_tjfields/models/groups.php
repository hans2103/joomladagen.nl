<?php

/**
 * @version     1.0.0
 * @package     com_tjfields
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      TechJoomla <extensions@techjoomla.com> - www.techjoomla.com
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Tjfields records.
 */
class TjfieldsModelGroups extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'name', 'a.name',
                'client', 'a.client',
                'client_type', 'a.client_type',

            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null) {
        // Initialise variables.
        $app = JFactory::getApplication('administrator');

        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);



        // Load the parameters.
        $params = JComponentHelper::getParams('com_tjfields');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.name', 'asc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param	string		$id	A prefix for the store id.
     * @return	string		A store id.
     * @since	1.6
     */
    protected function getStoreId($id = '') {
        // Compile the store id.
        $id.= ':' . $this->getState('filter.search');
        $id.= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() {
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$input=jFactory::getApplication()->input;
		// Select the required fields from the table.
		$query->select(
				$this->getState(
						'list.select', 'a.*'
				)
		);
		$query->from('`#__tjfields_groups` AS a');


		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
		$query->where('a.client="'.$input->get('client','','STRING').'"');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = '.(int) $published);
		} else if ($published === '') {
			$query->where('(a.state IN (0, 1))');
		}


		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.name LIKE '.$search.'  OR  a.client LIKE '.$search.' )');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol && $orderDirn) {
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

        return $query;
    }

    public function getItems() {
        $items = parent::getItems();

        return $items;
    }

	/**
	 * To plublish and unpublish groups.
	 *
	 */
	function setItemState( $items, $state )
	{
		$db=JFactory::getDBO();

		if(is_array($items))
		{

			foreach ($items as $id)
			{
				$db=JFactory::getDBO();
				$query = "UPDATE  #__tjfields_groups SET state = $state where id=".$id;
				$db->setQuery( $query );
				if (!$db->execute()) {
						$this->setError( $this->_db->getErrorMsg() );
						return false;
				}
			}
		}
		// clean cache
		return true;
	}

	function deletegroup($id)
	{

		if(count($id)>1)
		{
			$group_to_delet = implode(',',$id);
			$db = JFactory::getDBO();
			$query = "DELETE FROM #__tjfields_groups where id IN (".$group_to_delet.")";
			$db->setQuery( $query );
			if (!$db->execute()) {
					$this->setError( $this->_db->getErrorMsg() );
					return false;
			}
		}
		else
		{
				$db=JFactory::getDBO();
				$query = "DELETE FROM #__tjfields_groups where id =". $id[0];
				$db->setQuery( $query );
				if (!$db->execute()) {
						$this->setError( $this->_db->getErrorMsg() );
						return false;
				}
		}
				return true;
	}


}
