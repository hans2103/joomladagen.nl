<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( '_JEXEC' ) or die;
defined( 'DS' ) or define( 'DS', DIRECTORY_SEPARATOR );
global $isJ25, $option;
$option = 'com_obsocialsubmit';
$jv     = new JVersion();
$isJ25  = ( $jv->RELEASE == '2.5' );

if ( ! JFactory::getUser()->authorise( 'core.manage', 'com_obsocialsubmit' ) ) {
	return JError::raiseWarning( 404, JText::_( 'JERROR_ALERTNOAUTHOR' ) );
}
$task       = JFactory::getApplication()->input->get( 'task' );
$controller = JControllerLegacy::getInstance( 'Obsocialsubmit' );
if ( $isJ25 ) {
	$task = JRequest::getVar( 'task' );
	$controller->execute( $task );
} else {
	$controller->execute( JFactory::getApplication()->input->get( 'task' ) );
}

$controller->redirect();