<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
class plgJ2StoreApp_pdfinvoicesInstallerScript {
	function preflight( $type, $parent ) {
		// Only allow to install on Joomla! 2.5.0 or later with PHP 5.3.0 or later
		if(defined('PHP_VERSION')) {
			$version = PHP_VERSION;
		} elseif(function_exists('phpversion')) {
			$version = phpversion();
		} else {
			$version = '5.0.0'; // all bets are off!
		}

		if(!JComponentHelper::isEnabled('com_j2store')) {
			Jerror::raiseWarning(null, 'J2Store is not found. Please install J2Store before installing this plugin');
			return false;
		}

		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
		if(version_compare(J2STORE_VERSION, '3.1.10', 'lt')) {
			Jerror::raiseWarning(null, 'You need at least J2Store Version 3.1.10 for this application to work. Please update your J2Store');
			return false;
		}

		// let us check dompdf library exists
		if(!JFolder::exists(JPATH_LIBRARIES.'/dompdf')){
			Jerror::raiseWarning ( null, 'This app requires dompdf library. Make sure dompdf library installed');
			return false;
		}
	}
}