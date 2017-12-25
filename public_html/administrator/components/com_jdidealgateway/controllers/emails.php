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
 * JD iDEAL Emails controller.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayControllerEmails extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name.
	 * @param   string  $prefix  The model prefix.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JdidealgatewayModelEmail  An instance of the JdidealgatewayModelEmail class.
	 *
	 * @since   2.0
	 */
	public function getModel($name = 'Email', $prefix = 'JdidealgatewayModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Send a test email.
	 *
	 * @return  void.
	 *
	 * @since   2.8.2
	 *
	 * @throws  Exception
	 */
	public function testEmail()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		$result = $model->testEmail();
		$app = JFactory::getApplication();
		$app->redirect('index.php?option=com_jdidealgateway&view=emails', $result['msg'], $result['state']);
	}
}
