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
 * Handle the payments.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayModelCheckout extends JModelLegacy
{
	/**
	 * Retrieve the payment data from the session.
	 *
	 * @return  object  Data used in the ExtraPayment form.
	 *
	 * @since   2.0
	 */
	public function getIdeal()
	{
		// Get the data for the transaction
		$data = json_decode(base64_decode(JFactory::getApplication()->input->post->getBase64('vars', '')));

		return $data;
	}
}
