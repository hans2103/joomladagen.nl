<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticketing
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Jticketing is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;

/**
 * jticketing Model
 *
 * @since  0.0.1
 */
class JTicketingModelTickettypes extends JModelAdmin
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_JTICKETING';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_jticketing.tickettypes';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_jticketing.tickettypes',
			'tickettypes',
			array(
				'control' => 'jform',
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
	 * Method to get the record form.
	 *
	 * @param   string  $type    Data for the form.
	 * @param   string  $prefix  True if the form is to load its own data (default case), false if not.
	 * @param   array   $config  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  table 
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Tickettypes', $prefix = 'JticketingTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($id = null)
	{
		$this->item = parent::getItem($id);

		return $this->item;
	}
}