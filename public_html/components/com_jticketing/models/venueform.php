<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');
require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/models/vendor.php';
require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php';
use Joomla\Utilities\ArrayHelper;
/**
 * Jticketing model.
 *
 * @since  1.6
 */
class JticketingModelVenueForm extends JModelAdmin
{
	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($id = null)
	{
		$this->item = parent::getItem($id);

		return $this->item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Check admin and load admin form in case of admin venue form
		if ($app->isAdmin())
		{
			// Get the form.
			$form = $this->loadForm('com_jticketing.venue', 'venue', array('control' => 'jform', 'load_data' => $loadData));
		}
		else
		{
			// Get the form.
			$form = $this->loadForm('com_jticketing.venuform', 'venueform', array('control' => 'jform', 'load_data' => $loadData));
		}

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Check admin and load admin form in case of admin venue form
		if ($app->isAdmin())
		{
			// Check the session for previously entered form data.
			$data = JFactory::getApplication()->getUserState('com_jticketing.edit.venue.data', array());
		}
		else
		{
			$data = JFactory::getApplication()->getUserState('com_jticketing.edit.venueform.data', array());
		}

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Get an instance of JTable class
	 *
	 * @param   string  $type    Name of the JTable class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 */
	public function getTable($type = 'Venue', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 *
	 * @param   string  $alias  Item alias
	 *
	 * @return  mixed
	 */
	public function getItemIdByAlias($alias)
	{
		$table = $this->getTable();

		$table->load(array('alias' => $alias));

		return $table->id;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('venue.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = JFactory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Get the name of a category by id
	 *
	 * @param   int  $id  Category id
	 *
	 * @return  Object|null	Object if success, null in case of failure
	 */
	public function getCategoryName($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('title')
			->from('#__categories')
			->where('id = ' . $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Publish the element
	 *
	 * @param   int  &$id    Item id
	 * @param   int  $state  Publish state
	 *
	 * @return  boolean
	 */
	public function publish(&$id, $state=1)
	{
		$table = $this->getTable();
		$table->load($id);
		$table->state = $state;

		return $table->store();
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   data  $data  TO  ADD
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		if (empty($data['created_by']))
		{
			$data['created_by'] = JFactory::getUser()->id;
		}

		$app = JFactory::getApplication();
		$licence = (object) $data['plugin'];
		$data['params'] = json_encode($data['plugin']);
		$online_provider = ltrim($data['online_provider'], "plug_tjevents_");
		$online_provider = ucfirst($online_provider);

		if ($data['online'] == 1)
		{
			if (!empty($licence))
			{
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('tjevents');

				$result = $dispatcher->trigger('get' . $online_provider . 'CommonInfo', array
				($licence)
				);
				$res = $result['0'];

				if ($res['error_message'])
				{
					$this->setError($res['error_message']);

					return false;
				}
			}
		}

		$enforceVendorParams = JComponentHelper::getParams('com_jticketing');
		$enforceVendor = $enforceVendorParams->get('enforce_vendor');
		$siteCall = $app->isSite();

		if ($enforceVendor && $siteCall)
		{
			$user_id = JFactory::getUser()->id;
			$data['created_by'] = $user_id;
			$data['vendor_id'] = $data['vendor_id'];
		}
		else
		{
			$tJvendorsHelper = new TjvendorsHelpersTjvendors;
			$getVendorId = $tJvendorsHelper->getVendorId($data['created_by']);

			if (empty($getVendorId))
			{
				$vendorData['vendor_client'] = "com_jticketing";
				$vendorData['user_id'] = $data['created_by'];
				$vendorData['vendor_title'] = $data['userName'];
				$vendorData['state'] = "1";
				$vendorData['params'] = null;
				$data['vendor_id'] = $tJvendorsHelper->addVendor($vendorData);
			}
			else
			{
				$data['vendor_id'] = $getVendorId;
			}
		}

		parent::save($data);
		$id = (int) $this->getState($this->getName() . '.id');

		return $id;
	}

	/**
	 * Method to Get User Current Location.
	 *
	 * @param   Array  $post  Array of data
	 *
	 * @return Array
	 *
	 * @since   1.0
	 */
	public function getCurrentLocation($post)
	{
		$longitude = $post->get('longitude');
		$latitude  = $post->get('latitude');

		$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($latitude) . ',' .
		trim($longitude) . '&sensor=false';

		$json = @file_get_contents($url);
		$data = json_decode($json);
		$status = $data->status;

		$location = array();
		$locationLogLat = array();
		$locationLogLat = $data->results[0]->geometry;
		$longitude = $locationLogLat->location->lng;
		$latitude = $locationLogLat->location->lat;

		if ($status == "OK")
		{
			// Get address from json data
			$location['location'] = $data->results[0]->formatted_address;
			$location['latitude'] = $latitude;
			$location['longitude'] = $longitude;
		}
		else
		{
			$location['location'] = '';
			$location['latitude'] = '';
			$location['longitude'] = '';
		}

		return $location;
	}

	/**
	 * To return a Used Venues
	 *
	 * @param   integer  $venueCodes  Venue Codes
	 *
	 * @return  integer on success
	 *
	 * @since  1.6
	 */
	public function usedVenues($venueCodes)
	{
		$venueCode = implode(", ", $venueCodes);

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('venue');
		$query->from('`#__jticketing_events`');
		$query->where('`venue` IN (' . $venueCode . ')');

		$db->setQuery($query);
		$used = $db->loadColumn();

		if ($used)
		{
			return $used;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check if data can be saved
	 *
	 * @return bool
	 */
	public function getCanSave()
	{
		$table = $this->getTable();

		return $table !== false;
	}
}
