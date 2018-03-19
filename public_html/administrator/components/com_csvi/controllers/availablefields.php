<?php
/**
 * @package     CSVI
 * @subpackage  AvailableFields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Available fields controller.
 *
 * @package     CSVI
 * @subpackage  AvailableFields
 * @since       6.0
 */
class CsviControllerAvailableFields extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModel
	 *
	 * @since   6.6.0
	 */
	public function getModel($name = 'Availablefields', $prefix = 'CsviModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Redirect to maintenance to update the available fields.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function updateavailablefields()
	{
		$this->setRedirect('index.php?option=com_csvi&task=maintenance.read&component=com_csvi&operation=updateavailablefields');
	}
}
