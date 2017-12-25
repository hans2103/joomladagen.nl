<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * List of payment providers.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class JdidealFormFieldPaystatus extends JFormFieldList
{
	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since   4.0
	 */
	public function __construct($form = null)
	{
		$this->type = 'Paystatus';

		parent::__construct();
	}

	/**
	 * Build a list of payment results.
	 *
	 * @return  array  List of payment results.
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	public function getOptions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('status', 'value') . ',' . $db->quoteName('status', 'text'))
			->from($db->quoteName('#__jdidealgateway_pays'))
			->group($db->quoteName('status'));
		$db->setQuery($query);

		$options = $db->loadAssocList();

		foreach ($options as $index => $option)
		{
			$option['text'] = JText::_('COM_JDIDEALGATEWAY_RESULT_' . $option['value']);

			$options[$index] = $option;
		}

		return array_merge(parent::getOptions(), $options);
	}
}
