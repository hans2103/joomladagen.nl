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
 * @since    2.0
 */
class JdidealgatewayControllerPays extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name.
	 * @param   string  $prefix  The model prefix.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JdidealgatewayModelPay  An instance of the JdidealgatewayModelPay class.
	 *
	 * @since   2.0
	 */
	public function getModel($name = 'Pay', $prefix = 'JdidealgatewayModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
}
