<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die(';)');

require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php';

/**
 * Model for buy for creating order and other
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelUser extends JModelAdmin
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->jticketingfrontendhelper = new jticketingfrontendhelper;
		$this->JticketingCommonHelper = new JticketingCommonHelper;

		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;
		$this->_db         = JFactory::getDBO();
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm   A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.user', 'user', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
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
	public function getTable($type = 'User', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

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
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   data  $data  TO  ADD
	 * 
	 * @return void
	 *
	 * @since    1.6
	 */
	public function save($data = '')
	{
		$this->jticketingmainhelper = new jticketingmainhelper;
		$com_params  = JComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');
		$socialIntegration = $com_params->get('integrate_with', 'none');
		$streamBuyTicket = $com_params->get('streamBuyTicket', 0);
		$session    = JFactory::getSession();

		// Save customer note
		$odata                   = new StdClass;
		$odata->id               = $data['order_id'];
		$odata->customer_note    = $data['comment'];

		if (!$this->_db->updateObject('#__jticketing_order', $odata, 'id'))
		{
		}

		$orderId = $data['order_id'];

		// Guest chckout.
		if ($data['checkout_method'] == 'guest')
		{
			if ($orderId)
			{
				$orderInfo = array();
				$orderInfo['id'] = $orderId;
				$orderInfo['email'] = $data['email1'];

				// Update the order details.
				JLoader::import('components.com_jticketing.models.order', JPATH_SITE);
				$orderModel = JModelLegacy::getInstance('Order', 'JticketingModel');
				$orderModel->updateOrderDetails($orderId, $orderInfo);

				// Get order items for this order.
				JLoader::import('components.com_jticketing.models.orderitem', JPATH_SITE);
				$ordrItemModel = JModelLegacy::getInstance('Orderitem', 'JticketingModel');
				$orderItems = $ordrItemModel->getOrderItems($orderId);

				// Update attendees with owner_email
				if (count($orderItems))
				{
					foreach ($orderItems as $orderItem)
					{
						if (!empty($com_params->collect_attendee_info_checkout))
						{
							JLoader::import('components.com_jticketing.models.attendees', JPATH_SITE);
							$attendeesModel = JModelLegacy::getInstance('Attendees', 'JticketingModel');
							$attendeesModel->updateAttendeeOwner($orderItem->attendee_id, $ownerId = 0, $ownerEmail = $data['email1']);
						}
					}
				}
			}
		}

		// Register new account.
		elseif ($data['checkout_method'] == 'register')
		{
			$userid = 0;
			$userid = $this->registerUser($data);

			if (!$userid)
			{
				return false;
			}
			else
			{
				if ($orderId)
				{
					$orderInfo = array();
					$orderInfo['id'] = $orderId;
					$orderInfo['user_id'] = isset($data['user_id']) ? JFactory::getUser()->id : $data['user_id'];

					// Update the order details.
					JLoader::import('components.com_jticketing.models.order', JPATH_SITE);
					$orderModel = JModelLegacy::getInstance('Order', 'JticketingModel');
					$orderModel->updateOrderDetails($orderId, $orderInfo);

					// Get order items for this order.
					JLoader::import('components.com_jticketing.models.orderitem', JPATH_SITE);
					$ordrItemModel = JModelLegacy::getInstance('Orderitem', 'JticketingModel');
					$orderItems = $ordrItemModel->getOrderItems($orderId);

					// Update attendees with owner_email
					if (count($orderItems))
					{
						foreach ($orderItems as $orderItem)
						{
							if (!empty($com_params->collect_attendee_info_checkout))
							{
								JLoader::import('components.com_jticketing.models.attendees', JPATH_SITE);
								$attendeesModel = JModelLegacy::getInstance('Attendees', 'JticketingModel');
								$attendeesModel->updateAttendeeOwner($orderItem->attendee_id, $ownerId = $data['user_id'], $ownerEmail = '');
							}
						}
					}
				}
			}
		}

		if ($orderId)
		{
			$this->billingaddr($data['user_id'], $data, $orderId);
		}
	}

	/**
	 * Register new user while creating order
	 *
	 * @param   ARRAY  $regdata1  regdata1 contains user entered email
	 *
	 * @return  int    $userid
	 *
	 * @since   1.0
	 */
	public function registerUser($regdata1)
	{
		$regdata['fnam']       = $regdata1['fnam'];
		$regdata['user_name']  = $regdata1['email1'];
		$regdata['user_email'] = $regdata1['email1'];
		$registrationModel = JPATH_ROOT . '/components/com_jticketing/models/registration.php';

		if (!class_exists('jticketingModelregistration'))
		{
			JLoader::register('jticketingModelregistration', $registrationModel);
			JLoader::load('jticketingModelregistration');
		}

		$jticketingModelregistration = new jticketingModelregistration;

		if (!$jticketingModelregistration->store($regdata))
		{
			return false;
		}

		$user = JFactory::getUser();

		return $userid = $user->id;
	}

	/**
	 * Check if joomla user exists
	 *
	 * @return  Boolean true or false
	 *
	 * @since   1.0
	 */
	public function getUpdatedBillingInfo()
	{
		// First update the current orderid with new logged in user id.
		$session  = JFactory::getSession();
		$orderId  = $session->get('JT_orderid');

		if ($orderId)
		{
			$orderInfo = array();
			$orderInfo['user_id'] = JFactory::getUser()->id;

			// Update the order details.
			JLoader::import('components.com_jticketing.models.order', JPATH_SITE);
			$orderModel = JModelLegacy::getInstance('Order', 'JticketingModel');
			$orderModel->updateOrderDetails($orderId, $orderInfo);

			// Get order items for this order.
			JLoader::import('components.com_jticketing.models.orderitem', JPATH_SITE);
			$ordrItemModel = JModelLegacy::getInstance('Orderitem', 'JticketingModel');
			$orderItems = $ordrItemModel->getOrderItems($orderId);

			// Update attendees.
			if (count($orderItems))
			{
				foreach ($orderItems as $oi)
				{
					JLoader::import('components.com_jticketing.models.attendees', JPATH_SITE);
					$attendeesModel = JModelLegacy::getInstance('Attendees', 'JticketingModel');
					$attendeesModel->updateAttendeeOwner($oi->attendee_id, $owner_id = JFactory::getUser()->id);
				}
			}
		}

		// Let's figure out and collect all data needed to return view layout HTML.
		$billPath = $this->JticketingCommonHelper->getViewPath('order', 'default_billing');
		$this->user     = JFactory::getUser();
		$this->country  = $this->TjGeoHelper->getCountryList();
		$this->userdata = $this->getUserData();

		$this->params   = JComponentHelper::getParams('com_jticketing');
		$this->enable_bill_vat = $this->params->get('enable_bill_vat');
		$this->default_country = $this->params->get('default_country');
		$profileImport         = $this->params->get('profile_import');
		$this->tnc             = $this->params->get('tnc');
		$this->article         = $this->params->get('article');
		$JTicketingIntegrationsHelper = new JTicketingIntegrationsHelper;
		$cdata                = '';

		if ($profileImport)
		{
			$cdata = $JTicketingIntegrationsHelper->profileImport();
		}

		// Added by manoj
		$this->userbill = array();

		if (isset($this->userdata['BT']))
		{
			$this->userbill = $this->userdata['BT'];
		}
		elseif (is_array($cdata))
		{
			$this->userbill = $cdata['userbill'];
		}

		// Get HTML.
		ob_start();
		include $billPath;
		$html = ob_get_contents();
		ob_end_clean();

		$data                 = array();
		$data['billing_html'] = $html;

		echo json_encode($data);
		jexit();
	}

	/**
	 * Get billing data
	 *
	 * @param   integer  $orderId  country id
	 *
	 * @return  array state list
	 *
	 * @since   1.0
	 */
	public function getUserData($orderId = 0)
	{
		$params   = JComponentHelper::getParams('com_jticketing');
		$user     = JFactory::getUser();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		$userdata = array();

		if ($orderId and !($user->id))
		{
			$query->select('u.*');
			$query->from($db->quoteName('#__jticketing_users', 'u'));
			$query->where($db->quoteName('u.order_id') . ' = ' . $db->quote($orderId));
			$query->order($db->quoteName('u.id') . ' DESC');
		}
		else
		{
			$query->select('u.*');
			$query->from($db->quoteName('#__jticketing_users', 'u'));
			$query->where($db->quoteName('u.user_id') . ' = ' . $db->quote($user->id));
			$query->order($db->quoteName('u.id') . ' DESC');
		}

		$db->setQuery($query, 0, 1);
		$result = $db->loadObjectList();

		if (!empty($result))
		{
			if ($result[0]->address_type == 'BT')
			{
				$userdata['BT'] = $result[0];
			}
			elseif (!empty($result[1]->address_type) == 'BT')
			{
				$userdata['BT'] = $result[1];
			}
		}
		else
		{
			$row             = new stdClass;
			$row->user_email = $user->email;
			$userdata['ST']  = $row;
		}

		return $userdata;
	}

	/**
	 * Update billing address while creating order
	 *
	 * @param   ARRAY  $userId         userid of order creator
	 * @param   ARRAY  $billingArr     billing data
	 * @param   ARRAY  $insertOrderId  order id to update billing data
	 *
	 * @return  int    $userid
	 *
	 * @since   1.0
	 */
	public function billingaddr($userId, $billingArr, $insertOrderId)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('order_id'));
		$query->from($db->quoteName('#__jticketing_users'));
		$query->where($db->quoteName('order_id') . ' = ' . $db->quote($insertOrderId));
		$db->setQuery($query);
		$orderId = (string) $db->loadResult();

		if ($orderId)
		{
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__jticketing_users'));
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($insertOrderId));
			$db->setQuery($query);

			if (!$db->execute())
			{
			}
		}

		$row          = new stdClass;
		$row->user_id = $userId;

		if ($billingArr['email1'])
		{
			$row->user_email = $billingArr['email1'];
		}

		$row->address_type = 'BT';

		if ($billingArr['fnam'])
		{
			$row->firstname = $billingArr['fnam'];
		}

		$row->lastname = empty($billingArr['lnam']) ? '' : $billingArr['lnam'];

		if (!empty($billingArr['country']))
		{
			$row->country_code = $billingArr['country'];
		}

		if (!empty($billingArr['vat_num']))
		{
			$row->vat_number = $billingArr['vat_num'];
		}

		if (!empty($billingArr['addr']))
		{
			$row->address = $billingArr['addr'];
		}

		if (!empty($billingArr['city']))
		{
			$row->city = $billingArr['city'];
		}

		if (!empty($billingArr['state']))
		{
			$row->state_code = $billingArr['state'];
		}

		if (!empty($billingArr['zip']))
		{
			$row->zipcode = $billingArr['zip'];
		}

		if (!empty($billingArr['country_mobile_code']))
		{
			$row->country_mobile_code = $billingArr['country_mobile_code'];
		}

		if (!empty($billingArr['phon']))
		{
			$row->phone = $billingArr['phon'];
		}

		$row->approved = '1';
		$row->order_id = $insertOrderId;

		if (!$this->_db->insertObject('#__jticketing_users', $row, 'id'))
		{
			echo $this->_db->stderr();
		}

		$params = JComponentHelper::getParams('com_jticketing');

		// Save customer note in order table
		$order = new stdClass;

		if ($insertOrderId)
		{
			$order->id            = $insertOrderId;
			$order->customer_note = empty($billingArr['comment']) ? '' : $billingArr['comment'];

			if ($userId)
			{
				$order->name  = JFactory::getUser($userId)->name;
				$order->email = JFactory::getUser($userId)->email;
			}
			else
			{
				$order->name  = $billingArr['fnam'] . " " . $billingArr['lnam'];
				$order->email = $billingArr['email1'];
			}

			if (!$this->_db->updateObject('#__jticketing_order', $order, 'id'))
			{
				echo $this->_db->stderr();
			}
		}

		// TRIGGER After Billing data save
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result = $dispatcher->trigger('jt_OnAfterBillingsaveData', array($billingArr, $_POST, $row->order_id, $userId));

		return $row->user_id;
	}

	/**
	 * Insert registered user data into jticketing users.
	 *
	 * @param   int     $userID   userID
	 * @param   string  $orderID  orderID
	 *
	 * @return  void
	 * 
	 * @since   1.0
	 */
	public function addRegisterdUserIntoJtuser($userID, $orderID)
	{
		if (!empty($userID))
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('id') . ' = ' . $userID);
			$db->setQuery($query);
			$regUserData = $db->loadObject();

			$obj = new stdClass;
			$obj->user_id    = $userID;
			$obj->order_id   = $orderID;
			$obj->firstname  = $regUserData->name;
			$obj->user_email = $regUserData->email;
			$obj->address_type = 'BT';

			// Insert registered user data into jticketing users.
			if (!$db->insertObject('#__jticketing_users', $obj, 'id'))
			{
				echo $db->stderr();

				return false;
			}
		}
	}
}
