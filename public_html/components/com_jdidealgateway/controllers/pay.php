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
 * Payment page controller.
 *
 * @package  JDiDEAL
 * @since    3.0
 */
class JdidealgatewayControllerPay extends JControllerLegacy
{
	/**
	 * Show the iDEAL payment page.
	 *
	 * @return  void.
	 *
	 * @since   2.0
	 *
	 * @throws  Exception
	 */
	public function sendmoney()
	{
		// Create the view
		$view = $this->getView('pay', 'html');

		// Add the export model
		/** @var JdidealgatewayModelPay $payModel */
		$payModel = $this->getModel('pay', 'JdidealgatewayModel');
		$view->setModel($payModel, true);

		// Set the layout
		$view->setLayout('ideal');

		// Display it all
		$view->display();
	}

	/**
	 * Check the payment result.
	 *
	 * @return  void.
	 *
	 * @since   2.0
	 *
	 * @throws  Exception
	 */
	public function result()
	{
		// Create the view
		$view = $this->getView('pay', 'html');

		// Add the export model
		/** @var JdidealgatewayModelPay $payModel */
		$payModel = $this->getModel('pay', 'JdidealgatewayModel');
		$view->setModel($payModel, true);

		// Set the layout
		$view->setLayout('result');

		// Display it all
		$view->display();
	}
}
