<?php
/**
 * @version    SVN:
 * @package    Com_Tjvendors
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright  2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
JLoader::import('tjvendors', JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers');

/**
 * Tjvendors model.
 *
 * @since  1.6
 */
class TjvendorsModelVendor extends JModelAdmin
{
	/**
	 * @var    string  client data
	 * @since  1.6
	 */
	private $vendor_client = '';

	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_TJVENDORS';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_tjvendors.vendor';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'Vendor', $prefix = 'TjvendorsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_tjvendors.vendor', 'vendor',
			array('control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$client = $input->get('client', '', 'STRING');

		$data = JFactory::getApplication()->getUserState('com_tjvendors.edit.vendor.data', array());
		$client = $app->getUserStateFromRequest('vendor.client', 'vendor.client');

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			if (!empty($this->item->vendor_id))
			{
				if (!empty($client))
				{
					$tjvendorsHelpersTjvendors = new TjvendorsHelpersTjvendors;
					$gatewayDetails = $tjvendorsHelpersTjvendors->getPaymentDetails($this->item->vendor_id, $client);

					if (!empty($gatewayDetails))
					{
						$this->item->payment_gateway = $gatewayDetails->payment_gateway;
					}
				}
			}

			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		return $item;
	}

	/**
	 * Method for save vendor information
	 *
	 * @param   Integer  $vendor_id        used for inserting data for that vendor
	 *
	 * @param   Integer  $payment_gateway  used for inserting data for that vendor
	 *
	 * @param   Integer  $paymentDetails   payment details
	 *
	 * @return id
	 */
	public function addMultiVendor($vendor_id,$payment_gateway, $paymentDetails)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('max(' . $db->quoteName('id') . ')');
		$query->from($db->quoteName('#__vendor_client_xref'));
		$db->setQuery($query);
		$res = $db->loadResult();
		$fields = array($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id),
		$db->quoteName('payment_gateway') . ' = ' . $db->quote($payment_gateway),
		$db->quoteName('params') . ' = ' . $db->quote($paymentDetails),
		);

		// Conditions for which records should be updated.
		$conditions = array($db->quoteName('id') . ' = ' . $res);

		$query->update($db->quoteName('#__vendor_client_xref'))->set($fields)->where($conditions);
		$db->setQuery($query);

		$result = $db->execute();

		return true;
	}

	/**
	 * Method to check duplicate user.
	 *
	 * @param   integer  $user_id  user name.
	 *
	 * @return   array rows
	 *
	 * @since    1.6
	 */
	public function checkDuplicateUser($user_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__tjvendors_vendors'));

		if (!empty($user_id))
		{
			$query->where($db->quoteName('user_id') . ' = ' . $user_id);
		}

		$db->setQuery($query);
		$rows = $db->loadAssoc();

		if ($rows)
		{
			return $rows;
		}
	}

	/**
	 * Method to generate payment gateway fields.
	 *
	 * @param   string  $payment_gateway  payment gateway.
	 *
	 * @return   array result
	 *
	 * @since    1.6
	 */
	public function generateGatewayFields($payment_gateway)
	{
		$app = JFactory::getApplication();
		$client = $app->getUserStateFromRequest('vendor.client', 'vendor.client');
		$vendor_id = $app->getUserStateFromRequest('vendor.vendor_id', 'vendor.vendor_id');
		$tjvendorsHelpersTjvendors = new TjvendorsHelpersTjvendors;
		$vendorDetails = $tjvendorsHelpersTjvendors->getPaymentDetails($vendor_id, $client);

		if (!empty($vendorDetails))
		{
			$paymentDetailsArray = json_decode($vendorDetails->params);
		}

		$form_path = JPATH_SITE . '/plugins/payment/' . $payment_gateway . '/' . $payment_gateway . '/form/' . $payment_gateway . '.xml';
		$test = $payment_gateway . '_' . 'plugin';

		if (jFile::exists($form_path))
		{
			$form = JForm::getInstance($test, $form_path, array('control' => 'jform[payment_fields]'));

			if (!empty($vendor_id))
			{
				$paymentDetails = array();

				if (!empty($paymentDetailsArray))
				{
					foreach ($paymentDetailsArray as $key => $detail)
					{
						if ($key != "payment_gateway")
						{
							$paymentDetails[$key] = $detail;
						}
					}
				}

				$form->bind($paymentDetails);
			}

			$fieldSet = $form->getFieldset('payment_gateway');
			$html = array();

			foreach ($fieldSet as $field)
			{
				$html[] = $field->renderField();
			}

			return $html;
		}

		return false;
	}

	/**
	 * Method for save user specific %commission, flat commission, client
	 *
	 * @param   Array  $data  Data
	 *
	 * @return id
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();
		$table = $this->getTable();
		$input = JFactory::getApplication()->input;
		$layout = $input->get('layout', '', 'STRING');
		$site = $app->isSite();

		if (!empty($data['paymentForm']))
		{
			foreach ($data['paymentForm']['payment_fields'] as $key => $field)
			{
				$paymentDetails[$key] = $field;
			}

			foreach ($data['paymentForm'] as $key => $detail)
			{
				$paymentPrefix = 'payment_';

				if (strpos($key, $paymentPrefix) !== false)
				{
					if ($key != 'payment_fields')
					{
						$paymentDetails[$key] = $detail;
					}
				}
			}
		}

		if (!empty($paymentDetails))
		{
			$data['paymentDetails'] = json_encode($paymentDetails);
		}

		if (empty($data['vendor_client']))
		{
			$data['params'] = $data['paymentDetails'];
			$data['payment_gateway'] = $paymentForm['payment_gateway'];
		}
		else
		{
			$data['payment_gateway'] = '';
			$data['params'] = '';
		}

		if ($data['user_id'] != 0)
		{
			// To check if editing in registration form
			if ($data['vendor_id'])
			{
				$table->save($data);
				$TjvendorFrontHelper = new TjvendorFrontHelper;
				$vendorClients = $TjvendorFrontHelper->getClientsForVendor($data['vendor_id']);
				$count = 0;

				foreach ($vendorClients as $client)
				{
					if ($client == $data['vendor_client'])
					{
						$count++;
					}
				}

				if ($layout == "edit" && (!empty($data['vendor_client']) && $site != 1 || $site == 1 && $count == 0))
				{
					$tjvendorsHelpersTjvendors = new TjvendorsHelpersTjvendors;
					$checkForDuplicateClient = $tjvendorsHelpersTjvendors->checkForDuplicateClient($data['vendor_id'], $data['vendor_client']);

					if ($checkForDuplicateClient != $data['vendor_client'])
					{
						$vendor_id = (int) $this->getState($this->getName() . '.id');
						$client_entry = new stdClass;
						$client_entry->client = $data['vendor_client'];
						$client_entry->vendor_id = $data['vendor_id'];
						$client_entry->payment_gateway = $paymentDetails['payment_gateway'];
						$client_entry->params = $data['paymentDetails'];

						// Insert the object into the user profile table.
						$result = JFactory::getDbo()->insertObject('#__vendor_client_xref', $client_entry);
					}
					else
					{
						$app->enqueueMessage(JText::_('COM_TJVENDORS_DUPLICATE_CLIENT_ERROR'), 'warning');

						return false;
					}
				}
				elseif (($layout == "update" && !empty($data['vendor_client'])) || ($site == 1 & $layout == "edit"))
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Fields to update.
					$fields = array(
						$db->quoteName('params') . ' = ' . $db->quote($data['paymentDetails']),
						$db->quoteName('payment_gateway') . ' = ' . $db->quote($paymentDetails['payment_gateway']),
					);

					// Conditions for which records should be updated.
						$conditions = array(
						$db->quoteName('vendor_id') . ' = ' . $db->quote($data['vendor_id']),
						$db->quoteName('client') . ' = ' . $db->quote($data['vendor_client'])
					);

					$query->update($db->quoteName('#__vendor_client_xref'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
				}

				return true;
			}
			else
			{
				if ($table->save($data) === true)
				{
					$data['vendor_id'] = $table->vendor_id;

					if (!empty($data['vendor_client']))
					{
						if (!empty($paymentForm['payment_gateway']))
						{
						$data['payment_gateway'] = $paymentForm['payment_gateway'];
						}

						$payment_gateway = $paymentDetails['payment_gateway'];
						$client_entry = new stdClass;
						$client_entry->client = $data['vendor_client'];
						$client_entry->vendor_id = $data['vendor_id'];
						$client_entry->payment_gateway = $payment_gateway;
						$client_entry->params = $data['paymentDetails'];

						// Insert the object into the user profile table.
						$result = JFactory::getDbo()->insertObject('#__vendor_client_xref', $client_entry);
						$this->addMultiVendor($data['vendor_id'], $payment_gateway, $data['paymentDetails']);
					}

					return true;
				}

				return false;
			}
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TJVENDORS_SELECT_USER'), 'warning');

			return false;
		}

		return false;
	}

	/**
	 * Set the onject values
	 *
	 * @param   string  $client  client value
	 *
	 * @return mixed
	 */
	public function setClient($client)
	{
		$this->vendor_client = $client;
	}

	/**
	 * Get an client value
	 *
	 * @return mixed
	 */
	public function getClient()
	{
		return $this->vendor_client;
	}

	/**
	 * This function will return the vendor details based on current user and client.
	 *
	 * @param   INT     $user    User id
	 * @param   STRING  $client  Component client
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function getVendorDetails($user = "", $client = "")
	{
		$result = array();
		$user = empty($user) ? JFactory::getUser() : JFactory::getUser($user);

		if ($user->id)
		{
			// Load vendor details based on client
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select("*");
			$query->from($db->qn("#__tjvendors_vendors"));
			$query->where($db->qn("user_id") . " = " . $user->id);

			if (! empty($client))
			{
				$query->where($db->qn("vendor_client") . " = " . $db->q($client));
			}

			$db->setQuery($query);
			$result = $db->loadAssoc();

			// Load default entry if available
			if (empty($result) && !empty($client))
			{
				$query = $db->getQuery(true);
				$query->select("*");
				$query->from($db->qn("#__tjvendors_vendors"));
				$query->where($db->qn("user_id") . " = " . $user->id);
				$query->where($db->qn("vendor_client") . "  = ''");

				$db->setQuery($query);
				$result = $db->loadAssoc();
			}
		}

		return $result;
	}
}
