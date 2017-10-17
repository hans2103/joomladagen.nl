<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die('Restricted access');


/**
 * Tablemypayouts
 *
 * @since  0.0.1
 */
class Tablemypayouts extends JTable
{
	/*var $id 			= 0;

	var $published		= null;

	var $user_id		= null;

	var $payee_name 	= null;

	var $date 			= null;

	var $transction_id	= null;

	var $payee_id		= null;

	var $amount 		= null;

	var $status 		= null;

	var $ip_address  	= null;

	var $type   		= null;*/

	/**
	 * Tablemypayouts
	 *
	 * @param   object  &$database  connector  object
	 *
	 * @return void
	 *
	 * since 2.0
	 */
	public function Tablemypayouts (&$database)
	{
		parent::__construct('#__jticketing_ticket_payouts', 'id', $database);
	}
}
