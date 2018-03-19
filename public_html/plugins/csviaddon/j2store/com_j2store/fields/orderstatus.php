<?php
/**
 * List the order status names
 *
 * @author        RolandD Cyber Produksi
 * @link          https://csvimproved.com
 * @copyright     Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license       GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('CsviForm');

/**
 * Select list form field with order status
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Csvij2storeFormFieldOrderstatus extends JFormFieldCsviForm
{
	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since   7.3.0
	 *
	 * @throws  Exception
	 */
	public function __construct($form = null)
	{
		$this->type = 'OrderStatus';

		parent::__construct($form);

		// Load the J2Store language files
		$jlang = JFactory::getLanguage();
		$jlang->load('com_j2store', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_j2store', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_j2store', JPATH_ADMINISTRATOR, null, true);
	}

	/**
	 * Select order status names.
	 *
	 * @return  array  An array of status names.
	 *
	 * @since   7.3.0
	 *
	 * @throws  Exception
	 * @throws  CsviException
	 * @throws  RuntimeException
	 */
	protected function getOptions()
	{
		$query = $this->db->getQuery(true)
			->select(
				array(
					$this->db->quoteName('j2store_orderstatus_id', 'value'),
					$this->db->quoteName('orderstatus_name', 'text'))
			)
			->from($this->db->quoteName('#__j2store_orderstatuses', 'j2store_orderstatuses'))
			->rightJoin(
				$this->db->quoteName('#__j2store_orders', 'j2store_orders')
				. ' ON ' . $this->db->quoteName('j2store_orders.order_state_id') . ' = ' . $this->db->quoteName('j2store_orderstatuses.j2store_orderstatus_id')
			)
			->order($this->db->quoteName('text'))
			->group($this->db->quoteName('value'));
		$this->db->setQuery($query);
		$orderStatuses = $this->db->loadObjectList();

		if (!$orderStatuses)
		{
			$orderStatuses = array();
		}

		foreach ($orderStatuses as $index => $orderStatus)
		{
			$orderStatus->text = JText::_($orderStatus->text);
			$orderStatuses[$index] = $orderStatus;
		}

		return array_merge(parent::getOptions(), $orderStatuses);
	}
}
