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
 * Logs controller.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayControllerLogs extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModel  Object of a database model.
	 *
	 * @since   1.0
	 */
	public function getModel($name = 'Log', $prefix = 'JdidealgatewayModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Read a logfile and show it in a popup.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 *
	 * @throws  Exception
	 */
	public function history()
	{
		// Create the view object
		$view = $this->getView('logs', 'html');

		// Standard model
		$logsModel = $this->getModel('Logs', 'JdidealgatewayModel');
		$view->setModel($logsModel, true);
		$view->setLayout('history');

		// Now display the view
		$view->display();
	}
}
