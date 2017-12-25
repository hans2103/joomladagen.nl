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
class JdidealFormFieldResult extends JFormFieldList
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
		$this->type = 'Result';

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
			->select($db->quoteName('result', 'value') . ',' . $db->quoteName('result', 'text'))
			->from($db->quoteName('#__jdidealgateway_logs'))
			->group($db->quoteName('result'));
		$db->setQuery($query);

		$options = $db->loadAssocList();

		return array_merge(parent::getOptions(), $options);
	}
}
