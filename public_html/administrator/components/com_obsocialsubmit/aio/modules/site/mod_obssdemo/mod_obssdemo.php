<?php
/**
 * @package        obsocialsubmit
 * @subpackage     mod_obssdemo
 * @author         Tung Pham - foobla.com
 * @copyright      Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

jimport( 'joomla.application.component.model' );
require_once( JPATH_ADMINISTRATOR . '/components/com_obsocialsubmit/models/adapters.php' );
$model           = new ObsocialSubmitModelAdapters();
$show_unpublish  = $params->get( 'show_type', 0 );
$use_awesome     = $params->get( 'use_awesome', 1 );
$class_button    = $params->get( 'button_class', 'center' );
$moduleclass_sfx = htmlspecialchars( $params->get( 'moduleclass_sfx' ) );
$connections     = $model->getConnections();
if ( $use_awesome ) {
	$document = JFactory::getDocument();
	$document->addStyleSheet( '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
}
require JModuleHelper::getLayoutPath( 'mod_obssdemo', $params->get( 'layout', 'default' ) );