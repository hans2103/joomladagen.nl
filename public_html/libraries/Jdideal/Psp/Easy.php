<?php
/**
 * @package     JDiDEAL
 * @subpackage  Lite
 *
 * @author      Roland Dalmulder <contact@jdideal.nl>
 * @copyright   Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://jdideal.nl
 */

namespace Jdideal\Psp;

use Jdideal\Gateway;

defined('_JEXEC') or die;

/**
 * Lite processor.
 *
 * @package     JDiDEAL
 * @subpackage  Easy
 * @since       4.0
 */
class Easy
{
	/**
	 * Live URL
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $liveUrl = 'https://internetkassa.abnamro.nl/ncol/prod/orderstandard.asp';

	/**
	 * Test URL
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $testUrl = 'https://internetkassa.abnamro.nl/ncol/test/orderstandard.asp';

	/**
	 * Return the live URL.
	 *
	 * @return  string  The live URL.
	 *
	 * @since   4.0
	 */
	public function getLiveUrl()
	{
		return $this->liveUrl;
	}

	/**
	 * Return the test URL.
	 *
	 * @return  string  The test URL.
	 *
	 * @since   4.0
	 */
	public function getTestUrl()
	{
		return $this->testUrl;
	}

	/**
	 * Prepare data for the form.
	 *
	 * @param   Gateway  $jdideal  An instance of JdidealGateway.
	 * @param   object   $data     An object with transaction information.
	 *
	 * @return  array  The data for the form.
	 *
	 * @since   2.13
	 *
	 * @throws   \RuntimeException
	 * @throws   \InvalidArgumentException
	 */
	public function getForm(Gateway $jdideal, $data)
	{
		$data->merchantID = $jdideal->get('merchantId');
		$data->language = $jdideal->get('language');
		$data->currency = strtoupper($jdideal->get('currency'));

		// Replace some predefined values
		$find = array();
		$find[] = '{KLANTNR}';
		$find[] = '{ORDERNR}';
		$replace = array();
		$user = \JFactory::getUser();
		$replace[] = $user->id;
		$replace[] = $data->order_number;
		$data->description = substr(str_ireplace($find, $replace, $jdideal->get('description')), 0, 32);
		$data->amount = sprintf('%.2f', $data->amount) * 100;
		$data->orderNumber = $data->order_number;

		return $data;
	}
}
