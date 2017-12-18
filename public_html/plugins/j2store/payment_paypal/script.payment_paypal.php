<?php
/*
 * @package		Paypal Standard Plugin for J2Store
* @subpackage	J2Store
* @author    	Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
* @copyright	Copyright (c) 2014 Weblogicx India Ltd. All rights reserved.
* @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
* --------------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgJ2StorePayment_paypalInstallerScript {

	function preflight( $type, $parent ) {

		if(!JComponentHelper::isEnabled('com_j2store')) {
			Jerror::raiseWarning(null, 'J2Store not found. Please install J2Store before installing this plugin');
			return false;
		}
		
		jimport('joomla.filesystem.file');
		$version_file = JPATH_ADMINISTRATOR.'/components/com_j2store/version.php';
		if (JFile::exists ( $version_file )) {
			require_once($version_file);
			// abort if the current J2Store release is older
			if (version_compare ( J2STORE_VERSION, '3.2.19', 'lt' )) {
				Jerror::raiseWarning ( null, 'You are using an old version of J2Store. Please upgrade to the latest version' );
				return false;
			}
		} else {
			Jerror::raiseWarning ( null, 'J2Store not found or the version file is not found. Make sure that you have installed J2Store before installing this plugin' );
			return false;
		}
	}

}