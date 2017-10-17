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

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

/**
 * Jticketing model.
 *
 * @since  1.6
 */
class JticketingModelOrderItem extends JModelAdmin
{
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
		$form = $this->loadForm('com_jticketing.orderitem', 'orderitem', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Orderitem', $prefix = 'JTicketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 *
	 * @since	2.0
	 */
	public function getItem($pk = null)
	{
		if ($pk)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select(array('o.*', 'oi.*'));
			$query->from($db->quoteName('#__jticketing_order', 'o'));
			$query->join('INNER',
			$db->quoteName('#__jticketing_order_items', 'oi') . ' ON (' . $db->quoteName('o.id') . ' = ' . $db->quoteName('oi.order_id') . ')');
			$query->where($db->quoteName('oi.id') . '=' . (int) $pk);
			$db->setQuery($query);

			return $db->loadObject();
		}

		return false;
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
		$session = JFactory::getSession();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id','order_id','attendee_id')));
		$query->from($db->quoteName('#__jticketing_order_items'));
		$query->where($db->quoteName('order_id') . ' = ' . $db->quote($data['order_id']));
		$db->setQuery($query);
		$orderitems = $db->loadObjectlist();

		JLoader::import('components.com_jticketing.models.event', JPATH_SITE);
		$ticketTypesModel = JModelLegacy::getInstance('Tickettypes', 'JticketingModel');

		// Firstly Delete ticket types in order items that are removed
		if (!empty($orderitems))
		{
			if (!empty($data['removed_ticket_types']))
			{
				foreach ($data['removed_ticket_types'] as $key => $count)
				{
					if ($count > 0)
					{
						$query = $db->getQuery(true);
						$query->delete($db->quoteName('#__jticketing_order_items'));
						$query->where($db->quoteName('order_id') . ' = ' . $db->quote($data['order_id']));
						$query->where($db->quoteName('type_id') . ' = ' . $db->quote($key));
						$query->setLimit($count);
						$db->setQuery($query);
					}
				}
			}
		}

		foreach ($data['type_ticketcount'] as $key => $multipleTickets)
		{
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__jticketing_order_items'));
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($data['order_id']));
			$query->where($db->quoteName('type_id') . ' = ' . $db->quote($key));
			$db->setQuery($query);
			$orderitemIdArray           = $db->loadAssoclist();
			$resdetails                 = new stdClass;
			$resdetails->id             = '';
			$resdetails->order_id       = $data['order_id'];
			$resdetails->ticketcount    = 1;
			$resdetails->type_id        = $key;
			$resdetails->payment_status = 'P';
			$ticketTypes = $ticketTypesModel->getItem($resdetails->type_id);
			$resdetails->ticket_price   = $ticketTypes->price;

			// @TODO For Deposit Change This to deposit Fee.
			$resdetails->amount_paid    = $resdetails->ticket_price;
			$totalUpdatedCount = 0;

			// Now update order items that already present
			if (!empty($orderitemIdArray))
			{
				foreach ($orderitemIdArray AS $key => $value)
				{
					$resdetails->id = $value['id'];

					if (!$db->updateObject('#__jticketing_order_items', $resdetails, 'id'))
					{
						echo $db->stderr();
					}

					$totalUpdatedCount++;
				}
			}

			if ($totalUpdatedCount)
			{
				$multipleTickets = $multipleTickets - $totalUpdatedCount;
			}

			// Insert Newly Created order items
			for ($i = 0; $i < $multipleTickets; $i++)
			{
				$resdetails->id = '';

				if (!$db->insertObject('#__jticketing_order_items', $resdetails, 'id'))
				{
					echo $db->stderr();
				}
			}
		}
	}

	/**
	 * Function for to get and order items data.
	 *
	 * @param   orderID  $orderID  TO  ADD
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function getOrderItems($orderID)
	{
		$orderItems = '';
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(array('*'));
		$query->from($db->quoteName('#__jticketing_order_items'));
		$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderID));
		$db->setQuery($query);
		$orderItems = $db->loadObjectlist();

		return $orderItems;
	}

	/**
	 * Function to update order item from the attendee model
	 *
	 * @param   data  $data  TO  ADD
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function updateorderItems($data)
	{
		$res              = new StdClass;
		$res->id          = '';
		$res->attendee_id = $data['attendee_id'];

		$db    = JFactory::getDBO();

		// If order items id present update it
		if ($data['order_items_id'])
		{
			$currentOrderItems[] = $data['order_items_id'];
			$res->id             = $data['order_items_id'];
			$insertOrderItemsId  = $data['order_items_id'];

			if (!$db->updateObject('#__jticketing_order_items', $res, 'id'))
			{
				echo $db->stderr();

				return false;
			}
		}
		else
		{
			return false;
		}
	}
}
