<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
/**
 * Module model.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class ObSocialSubmitModelSelect extends JModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
//	protected function populateState($ordering = null, $direction = null)
//	{
//		$app = JFactory::getApplication('administrator');
//
//		// Load the filter state.
//		$clientId = $app->getUserState('com_modules.modules.filter.client_id', 0);
//		$this->setState('filter.client_id', (int) $clientId);
//
//		// Load the parameters.
//		$params	= JComponentHelper::getParams('com_modules');
//		$this->setState('params', $params);
//
//		// Manually set limits to get all modules.
//		$this->setState('list.limit', 0);
//		$this->setState('list.start', 0);
//		$this->setState('list.ordering', 'a.name');
//		$this->setState('list.direction', 'ASC');
//	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string	A prefix for the store id.
	 *
	 * @return	string	A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.client_id');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$type = JRequest::getVar('type');
		$folder = ($type=='connection')?'obss_extern':'obss_intern';

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select','*'
			)
		);
		$query->from($db->quoteName('#__extensions').' AS a');

		// Filter by module
		$query->where('`type` = '.$db->Quote('plugin'));
		$query->where('`folder` = '.$db->Quote($folder));
		// Filter by enabled
		$query->where('`enabled` = 1');
		$query->where('`state` = 0');
		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'ordering')).' '.$db->escape($this->getState('list.direction', 'ASC')));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}

	/**
	 * Method to get a list of items.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	public function &getItems()
	{
		// Get the list of items from the database.
		$items = parent::getItems();
		$client = JApplicationHelper::getClientInfo(0);
		$lang	= JFactory::getLanguage();
		// Loop through the results to add the XML metadata,
		// and load language support.
		foreach ($items as &$item) {
			$path = JPath::clean($client->path.'/plugins/'.$item->folder.'/'.$item->element.'/'.$item->element.'.xml');
			if (file_exists($path)) {
				$item->xml = simplexml_load_file($path);
			} else {
				$item->xml = null;
			}
			// 1.5 Format; Core files or language packs then
			// 1.6 3PD Extension Support
				$lang->load('plg_'.$item->folder.'_'.$item->element, JPATH_ADMINISTRATOR, null, false, false)
			||	$lang->load('plg_'.$item->folder.'_'.$item->element, JPATH_SITE.'/plugins/'.$item->folder.'/'.$item->element, null, false, false )
			||	$lang->load('plg_'.$item->folder.'_'.$item->element, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
			||	$lang->load('plg_'.$item->folder.'_'.$item->element, JPATH_SITE.'/plugins/'.$item->folder.'/'.$item->element, $lang->getDefault(), false, false);
			$item->name	= JText::_($item->name);

			if (isset($item->xml) && $text = trim($item->xml->description)) {
				$item->desc = JText::_($text);
			}
			else {
				$item->desc = JText::_('COM_OBSOCIALSUBMIT_NODESCRIPTION');
			}
		}
		$items = JArrayHelper::sortObjects($items, 'name', 1, true, $lang->getLocale());

		// TODO: Use the cached XML from the extensions table?

		return $items;
	}
}
