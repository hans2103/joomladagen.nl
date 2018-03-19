<?php
/**
 * List the order currencies
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
 * Select list form field with order currencies
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Csvij2storeFormFieldOrdercurrencies extends JFormFieldCsviForm
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
		$this->type = 'OrderCurrencies';

		parent::__construct($form);
	}

	/**
	 * Select order currencies.
	 *
	 * @return  array  An array of currencies names.
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
					$this->db->quoteName('j2store_currencies.j2store_currency_id', 'value'),
					$this->db->quoteName('j2store_currencies.currency_code', 'text'))
			)
			->from($this->db->quoteName('#__j2store_currencies', 'j2store_currencies'))
			->rightJoin(
				$this->db->quoteName('#__j2store_orders', 'j2store_orders')
				. ' ON ' . $this->db->quoteName('j2store_orders.currency_id') . ' = ' . $this->db->quoteName('j2store_currencies.j2store_currency_id')
			)
			->order($this->db->quoteName('text'))
			->group($this->db->quoteName('value'));
		$this->db->setQuery($query);
		$orderCurrency = $this->db->loadObjectList();

		if (!$orderCurrency)
		{
			$orderCurrency = array();
		}

		return array_merge(parent::getOptions(), $orderCurrency);
	}
}
