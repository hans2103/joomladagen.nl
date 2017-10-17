<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Jticketing model.
 *
 * @since  1.6
 */
class JticketingModelCoupon extends JModelAdmin
{
	/**
	 * @var   string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_JTICKETING';
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable   A database object
	 *
	 * @since  1.6
	 */
	public function getTable($type = 'Coupon', $prefix = 'JticketingTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm   A JForm object on success, false on failure
	 *
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.coupon', 'coupon', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since  1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_jticketing.edit.coupon.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed   Object on success, false on failure.
	 *
	 * @since  1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   integer  $table  The id of table is passed
	 *
	 * @return  integer on success
	 *
	 * @since  1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set

			if (@$table->ordering === '')
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__jticketing_coupon');
				$max = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * To return a code
	 *
	 * @param   integer  $code  code get
	 *
	 * @return  integer on success
	 *
	 * @since  1.6
	 */
	public function getcode($code)
	{
		$db = JFactory::getDBO();

		$qry = "SELECT `id` FROM #__jticketing_coupon WHERE `code` = " . $db->quote($db->escape(trim($code)));
		$db->setQuery($qry);
		$exists = $db->loadResult();

		if ($exists)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * To check for code
	 *
	 * @param   integer  $code  code
	 * @param   integer  $id    The id of table is passed
	 *
	 * @return  integer on success
	 *
	 * @since  1.6
	 */
	public function getselectcode($code,$id)
	{
		$db	= JFactory::getDBO();

		$qry = "SELECT `code` FROM #__jticketing_coupon WHERE id<>'{$id}' AND `code` = " . $db->quote($db->escape(trim($code)));
		$db->setQuery($qry);
		$exists = $db->loadResult();

		if ($exists)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
}
