<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticketing
 * @copyright  Copyright (C) 2005 - 2017. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Jticketing is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access.
defined('_JEXEC') or die;


jimport('joomla.application.component.modeladmin');

/**
 * Methods supporting a jticketing media integration.
 *
 * @since  2.0.0
 */
class JticketingModelMediaXref extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	 *
	 * @since	2.0
	 */
	public function getTable($type = 'mediaxref', $prefix = 'JticketingTable', $config = array())
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return JTable::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

			return JTable::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.mediaxref', 'mediaxref', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since  2.0
	 */
	public function save($data)
	{
		if (!$data)
		{
			return false;
		}

		$result    = $this->getTable('mediaxref');
		$result->load(array('media_id' => (int) $data['media_id'], 'client_id' => (int) $data['client_id'], 'client' => $data['client']));

		if ($result->id)
		{
			return;
		}

		if ($returnData = parent::save($data))
		{
			return $returnData;
		}

		return false;
	}

	/**
	 * Method to get a event's media files from media xref.
	 *
	 * @param   INT  $clientId    event id
	 *
	 * @param   INT  $clientName  clientName
	 *
	 * @param   INT  $isGallery   isGallery
	 *
	 * @return Array.
	 *
	 * @since	2.0
	 */
	public function getEventMedia($clientId, $clientName, $isGallery = 0)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'media_id', 'client_id')));
		$query->from($db->quoteName('#__media_files_xref'));
		$query->where($db->quoteName('client_id') . '=' . (int) $clientId);
		$query->where($db->quoteName('is_gallery') . '=' . (int) $isGallery);
		$query->where($db->quoteName('client') . '=' . $db->quote($clientName));
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
