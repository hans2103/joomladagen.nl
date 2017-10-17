<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_hierarchy
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Hierarchy records.
 *
 * @since  1.6
 */
class JticketingModelCatimpexp extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 *
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			// #Nothing
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Filtering user_id
		$this->setState('filter.user_id', $app->getUserStateFromRequest($this->context . '.filter.user_id', 'filter_user_id', '', 'string'));

		/* Load the parameters.
		$params = JComponentHelper::getParams('com_hierarchy');
		$this->setState('params', $params);
		*/

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.id AS subuserId, a.name'
			)
		);

		$query->from('`#__users` AS a');

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.name LIKE ' . $search . ' )');
			}
		}

		// Filtering user_id
		$filter_user_id = $this->state->get("filter.user_id");

		if ($filter_user_id)
		{
			$query->where("a.id = '" . $db->escape($filter_user_id) . "'");
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get a list.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Import csv data for category save in database.
	 *
	 * @param   array  $categoryData  csv file data.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function importCategoryCSVdata($categoryData)
	{
		// #load categories model file as it is

		// JLoader::import( 'category', JPATH_ADMINISTRATOR .'/components/com_categories/models' );
		require_once JPATH_ADMINISTRATOR . '/components/com_categories/models/category.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_categories/tables/category.php';
		$categoryModel = new CategoriesModelCategory;

		$db = $this->getDbo();
		$i = 0;

		foreach ($categoryData as $eachCategory => $val)
		{
			$insert_obj[$i] = array();

			foreach ($val as $key => $value)
			{
				switch ($key)
				{
					case 'Id' :
						$insert_obj[$i]['id'] = 0;

						if (!empty ($value))
						{
							$insert_obj[$i]['id'] = $value;
						}

					break;

					case 'ParentId' :
						$insert_obj[$i]['parent_id'] = 1;

						if (!empty($value))
						{
							$insert_obj[$i]['parent_id'] = $value;
						}

					break;

					case 'Category Name' :
						$insert_obj[$i]['title'] = $value;
					break;
				}
			}

			$insert_obj[$i]['extension'] = 'com_jticketing';
			$insert_obj[$i]['published'] = 1;
			$insert_obj[$i]['access'] = 1;
			$insert_obj[$i]['alias'] = '';
			$insert_obj[$i]['language'] = '*';
			$i++;
		}

		$catTitle = 0;
		$idnotfound = 0;
		$parentIdnotfound = 0;
		$bad = 0;
		$success = 0;
		$totalCategory = count($insert_obj);

		foreach ($insert_obj as $cat)
		{
			$catidE = $this->categoryexit($cat['id']);
			$catidParent = $this->categoryParentexit($cat['parent_id']);

			if ($catidParent == 'notExistParentId')
			{
				$parentIdnotfound ++;
			}

			if ($catidE == 'notExistId')
			{
				$idnotfound ++;
			}

			if ($cat['title'] != '')
			{
				$categoryImport = $categoryModel->save($cat);

				if ($categoryImport)
				{
					$success ++;
				}
				else
				{
					$bad ++;
				}
			}
			else
			{
				$catTitle ++;
			}
		}

		if (count($success)==$totalCategory)
		{
			$output['msg'] = JText::sprintf('COM_JTICKETING_CATEGORY_IMPORT_SUCCESS_MSG');
		}

		else
		{
			$output['msg'] = JText::sprintf('COMJTICKETING_CATEGORY_IMPORT_SUCCESS_MSG', $totalCategory, $catTitle, $idnotfound);
			$output['msg1'] = JText::sprintf('COMJTICKETING_CATEGORY_IMPORT_SUCCESS_MSG1', $parentIdnotfound, $bad, $success);
		}

		return $output;
	}

	/**
	 * categoryParentexit.
	 *
	 * @param   integer  $id  id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function categoryParentexit($id)
	{
		$catId = '';

		if ($id)
		{
			$db = $this->getDbo();
			$query = "SELECT id FROM #__categories WHERE id ='{$id}' AND extension = 'com_jticketing'";
			$db->setQuery($query);
			$catId = $db->loadResult();

			if ($catId == '')
			{
				return 'notExistParentId';
			}
		}

		return $catId;
	}

	/**
	 * categoryexit.
	 *
	 * @param   integer  $id  id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function categoryexit($id)
	{
		$catId = '';

		if ($id)
		{
			$db = $this->getDbo();
			$query = "SELECT id FROM #__categories WHERE id ='{$id}' AND extension = 'com_jticketing'";
			$db->setQuery($query);
			$catId = $db->loadResult();

			if ($catId == '')
			{
				return 'notExistId';
			}
		}

		return $catId;
	}
}
