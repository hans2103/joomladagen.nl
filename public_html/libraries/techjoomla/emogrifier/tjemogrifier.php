<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Emogrifier
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Interface to handle PHP version
 *
 * @package     Joomla.Libraries
 * @subpackage  emogrifier
 * @since       1.0
 */
class InitEmogrifier
{
	/**
	 * The function to get emogrifier file as per PHP version
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function initTjEmogrifier()
	{
		if (version_compare(phpversion(), '5.4', '<'))
		{
			require_once JPATH_ROOT . '/libraries/techjoomla/emogrifier/emogrifier_old.php';
		}
		else
		{
			require_once JPATH_ROOT . '/libraries/techjoomla/emogrifier/emogrifier_new.php';
		}
	}
}
