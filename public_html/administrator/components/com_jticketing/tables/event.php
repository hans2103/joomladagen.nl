<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Hello Table class
 *
 * @since  0.0.1
 */
class JTicketingTableEvent extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		$this->setColumnAlias('published', 'state');
		parent::__construct('#__jticketing_events', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return bool
	 */
	public function check()
	{
		$db = JFactory::getDbo();
		$errors = array();

		// Validate and create alias if needed
		$this->alias = trim($this->alias);

		if (!$this->alias)
		{
			$this->alias = $this->title;
		}

		if ($this->alias)
		{
			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$this->alias = JFilterOutput::stringURLUnicodeSlug($this->alias);
			}
			else
			{
				$this->alias = JFilterOutput::stringURLSafe($this->alias);
			}
		}

		// Check if event with same alias is present
		$table = JTable::getInstance('Event', 'JticketingTable', array('dbo', $db));

		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0))
		{
			$msg = JText::_('COM_JTICKETING_SAVE_ALIAS_WARNING');

			while ($table->load(array('alias' => $this->alias)))
			{
				$this->alias = JString::increment($this->alias, 'dash');
			}

			JFactory::getApplication()->enqueueMessage($msg, 'warning');
		}

		// Make sure creator is set
		if (!JFactory::getUser($this->created_by)->id)
		{
			$errors['created_by'] = JText::_('COM_JTICKETING_EVENT_CREATOR_ERROR');
		}

		// End date should be later than start
		if (strtotime($this->startdate) > strtotime($this->enddate))
		{
			$errors['event_end'] = JText::_('COM_JTICKETING_EVENT_EVENT_END_DATE_ERROR');
		}

		if (strtotime($this->booking_start_date) > strtotime($this->booking_end_date))
		{
			$errors['event_end'] = JText::_('COM_JTICKETING_EVENT_BOOKING_END_DATE_ERROR');
		}

		if (count($errors))
		{
			$this->setError(implode($errors, ', '));

			return false;
		}

		return parent::check();
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 *
	 * @return string The asset name
	 *
	 * @see JTable::_getAssetName
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_jticketing.event.' . (int) $this->$k;
	}

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 *
	 * @param   JTable  $table  database name
	 * @param   string  $id     1 0r 0
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent   = JTable::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_jticketing');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array   $array   Named array
	 * @param   string  $ignore  string
	 *
	 * @return   null|string    null is operation was satisfactory, otherwise returns an error
	 *
	 * @since  1.0.0
	 */
	public function bind($array, $ignore = '')
	{
		$input = JFactory::getApplication()->input;
		$task  = $input->getString('task', '');
		$user = JFactory::getUser();

		if (($task == 'save' || $task == 'apply') && (!$user->authorise('core.edit.state', 'com_jticketing.event.' . $array['id']) && $array['state'] == 1))
		{
			$array['state'] = 0;
		}

		$files = $input->files->get('jform');

		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}
}
