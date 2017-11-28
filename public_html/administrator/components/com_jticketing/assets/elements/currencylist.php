<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.form.helper');
jimport('joomla.filesystem.file');
jimport('techjoomla.tjmoney.tjmoney');

JFormHelper::loadFieldClass('list');

/**
 * JFormFieldCurrencyList class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */

class JFormFieldCurrencyList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 1.6
	 */
	protected $type = 'currencylist';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var   integer
	 * @since 2.2
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		$options = array();
		$currencies = TjMoney::getCurrencies();

		if (!empty($currencies))
		{
			foreach ($currencies as $key => $currency)
			{
				$currencyCode    = $currency['alphabeticCode'];
				$currencyTitle   = $currency['currency'];

				$options[] = JHtml::_('select.option', $currencyCode, $currencyTitle);
			}
		}

		return $options;
	}
}
