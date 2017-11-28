<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
jimport('techjoomla.tjmoney.tjmoney');

/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldPrice extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'price';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	protected function getInput()
	{
		$input = parent::getInput();
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_jticketing');

		$currencyCode = $params->get('currency', '', 'STRING');
		$currencyCodeOrSymbol = $params->get('currency_code_or_symbol', 'code', 'STRING');

		$tjCurrency   = new TjMoney($currencyCode);
		$symbolOrCode = $tjCurrency->getSymbol();

		if ($currencyCodeOrSymbol === 'code')
		{
			$symbolOrCode = $tjCurrency->getCode();
		}

		// Initialize variables.
		$html = array();

		if ($app->isAdmin())
		{
			$html[] = "<div class='input-prepend'>";
			$html[] = $input;

			if ($symbolOrCode)
			{
				$html[] = "<span class='add-on'>" . $symbolOrCode . "</span></div>";
			}
		}
		else
		{
			$html[] = "<div class='input-group'>";
			$html[] = $input;

			if ($symbolOrCode)
			{
				$html[] = "<span class='input-group-addon'>" . $symbolOrCode . "</span></div>";
			}
		}

		return implode($html);
	}
}
