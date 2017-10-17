<?php

/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjlms
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of courses
 *
 * @since  1.0.0
 */
class JFormFieldPaymentGateway extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'paymentGateway';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var		integer
	 * @since	2.2
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		$type = "payment";
		$input = JFactory::getApplication()->input;
		$client = $input->get('client', '', 'STRING');
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('COM_TJVENDOR_PAYMENT_DETAILS_DEFAULT'));

		if (!empty($client))
		{
			$com_params = JComponentHelper::getParams($client);
			$gateways = $com_params['gateways'];

			foreach ($gateways as $detail)
			{
				$options[] = JHtml::_('select.option', $detail, $detail);
			}
		}
		else
		{
			$gateways = JPluginHelper::getPlugin($type, $plugin = null);

			foreach ($gateways as $detail)
			{
				$options[] = JHtml::_('select.option', $detail->name, $detail->name);
			}
		}

		if (!$this->loadExternally)
		{
			// Merge any additional options in the XML definition.
			$options = array_merge(parent::getOptions(), $options);
		}

		return $options;
	}
}
