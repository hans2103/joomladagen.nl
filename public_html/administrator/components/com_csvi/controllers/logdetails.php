<?php
/**
 * @package     CSVI
 * @subpackage  Logdetails
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Log details controller.
 *
 * @package     CSVI
 * @subpackage  Logdetails
 * @since       6.0
 */
class CsviControllerLogdetails extends JControllerLegacy
{
	/**
	 * Cancel the operation and return to the log view.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *          
	 * @throws  Exception
	 */
	public function cancel()
	{
		$returnUri = $this->input->get('return', '', 'string');

		$redirect = 'index.php?option=com_csvi&view=logs';

		if ('' !== $returnUri)
		{
			$redirect = base64_decode($returnUri);
		}

		$this->setRedirect(JRoute::_($redirect, false))->redirect();
	}
}
