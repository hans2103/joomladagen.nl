<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Banner table
 *
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @since       1.5
 */
class ObSocialSubmitTableInstances extends JTable
{
	/**
	 * Constructor
	 *
	 * @since	1.5
	 */
	public function __construct(&$_db)
	{
		parent::__construct('#__obsocialsubmit_instances', 'id', $_db);
		$date = JFactory::getDate();
		$this->created = $date->toSql();
	}
}
