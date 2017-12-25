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

/**
 * Pay view.
 *
 * @package  JDiDEAL
 * @since    2.14
 */
class JdidealgatewayViewCheckout extends JViewLegacy
{
	/**
	 * An array with form data to initiate a payment
	 *
	 * @var    array
	 * @since  2.14
	 */
	public $data = array();

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   2.14
	 */
	public function display($tpl = null)
	{
		$this->data = $this->get('Ideal');

		return parent::display($tpl);
	}
}
