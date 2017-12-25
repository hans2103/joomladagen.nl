<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

use Jdideal\Gateway;

defined('_JEXEC') or die;

/**
 * Model for handling the payment.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayModelPay extends JModelForm
{
	/**
	 * Method to get the registration form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since	2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jdidealgateway.pay', 'pay', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Load the checkout form data the user has already entered.
	 *
	 * @return  array  Form data.
	 *
	 * @since   2.0
	 *
	 * @throws  Exception
	 */
	protected function loadFormData()
	{
		$data = (array) JFactory::getApplication()->getUserState('com_jdidealgateway.pay.data', array());

		// Check if we have any data, otherwise try to get it from the URL
		if (0 === count($data))
		{
			$jinput = JFactory::getApplication()->input;
			$data['user_email'] = $jinput->getEmail('email', '');
			$data['amount'] = $jinput->getEmail('amount', '');
			$data['remark'] = $jinput->getEmail('remark', '');
		}

		return $data;
	}

	/**
	 * Set up the payment request.
	 *
	 * @return  object  Data used in the ExtraPayment form.
	 *
	 * @since   2.0
	 *
	 * @throws  Exception
	 */
	public function getIdeal()
	{
		$jinput = JFactory::getApplication()->input;
		$table = $this->getTable();
		$id = $jinput->get('order_id', false);

		if ($id)
		{
			$table->load($id);
			$post = array();
			$post['amount'] = $table->amount;
		}
		else
		{
			$post = $jinput->get('jform', array(), 'array');

			// Add the current date
			jimport('joomla.utilities.date');
			$jnow = new JDate(time());
			$post['cdate'] = $jnow->toSql();

			// Make sure the amount has a period
			if (isset($post['amount']))
			{
				$post['amount'] = str_replace(',', '.', $post['amount']);
			}

			// Store the data in the database
			$table->bind($post);
			$table->store();
		}

		// Set some needed data
		$Itemid = $jinput->get('Itemid', 0);
		$data = array(
			'amount'		=> array_key_exists('amount', $post) ? $post['amount'] : 0,
			'order_number'	=> array_key_exists('order_number', $post) ? $post['order_number'] : $table->id,
			'order_id'		=> $table->id,
			'origin'		=> 'jdidealgateway',
			'return_url'	=> JUri::root() . 'index.php?option=com_jdidealgateway&task=pay.result&Itemid=' . $Itemid,
			'notify_url'	=> '',
			'cancel_url'	=> JUri::root() . 'index.php?option=com_jdidealgateway&task=pay.result&Itemid=' . $Itemid,
			'email'         => $table->user_email,
			'payment_method' => '',
			'profileAlias'  => '',
			'custom_html'   => '',
			'silent'        => false,

		);

		return $data;
	}

	/**
	 * Check the payment result.
	 *
	 * @return  string  The customer message.
	 *
	 * @since   2.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	public function getResult()
	{
		$jinput = JFactory::getApplication()->input;

		// Load the helper
		$jdideal = new Gateway;
		$trans = $jinput->get('transactionId');
		$details = $jdideal->getDetails($trans, 'trans', false, 'jdidealgateway');

		$status = $jdideal->getStatusCode($details->result);

		// Update the order status
		if (is_object($details))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__jdidealgateway_pays'))
				->set($db->quoteName('status') . ' = ' . $db->quote($status))
				->where($db->quoteName('id') . ' = ' . (int) $details->order_id);
			$db->setQuery($query)->execute();
		}

		// Load the message
		return $jdideal->getMessage($details->id);
	}
}
