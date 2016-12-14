<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

require_once JPATH_COMPONENT_ADMINISTRATOR .'/builder/classes/ajax.php';
if(!class_exists('SppagebuilderHelperSite')) {
	require_once JPATH_ROOT . '/components/com_sppagebuilder/helpers/helper.php';
}

$user = JFactory::getUser();
$app  = JFactory::getApplication();

$authorised = $user->authorise('core.edit', 'com_sppagebuilder') || ($user->authorise('core.edit.own', 'com_sppagebuilder') && ($this->item->created_by == $user->id));
if ($authorised !== true)
{
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
	$app->setHeader('status', 403, true);

	return false;
}

SppagebuilderHelperSite::loadLanguage();

jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );
$input = JFactory::getApplication()->input;
$action = $input->get('callback', '', 'STRING');

// all settings loading
if ( $action === 'addon' ) {
	require_once JPATH_COMPONENT . '/parser/addon-parser.php';

	$post_data = $_POST['addon'];
	$addon = json_decode(json_encode($post_data));

	$addon_name = $addon->name;
	$class_name = 'SppagebuilderAddon' . ucfirst($addon_name);
	$addon_path = AddonParser::getAddonPath( $addon_name );

	$output = '';
	$output .= JLayoutHelper::render('addon.start', array('addon' => $addon)); // start addon

	require_once $addon_path.'/site.php';

	$assets = array();
	$css = JLayoutHelper::render('addon.css', array('addon' => $addon));

	if ( class_exists( $class_name ) ) {
			$addon_obj = new $class_name($addon);  // initialize addon class
			$output .= $addon_obj->render();

			// css
			if (method_exists($class_name, 'css')) {
				 $css .= $addon_obj->css();
			}

			// js
			if (method_exists($class_name, 'js')) {
					$assets['js'] = $addon_obj->js();
			}

	} else {
		$output .= AddonParser::spDoAddon( AddonParser::generateShortcode($addon, 0, 0));
	}

	if($css) {
		$assets['css'] = $css;
	}

	$output .= JLayoutHelper::render('addon.end'); // end addon

	echo json_encode(array('html' => htmlspecialchars_decode($output), 'status' => 'true', 'assets' => $assets )); die;
}

if ( $action === 'get-page-data' ) {
	$page_path = $_POST['pagepath'];
	if ( JFile::exists( $page_path ) ) {
		$content = file_get_contents( $page_path );
		if (is_array(json_decode($content))) {
			require_once JPATH_COMPONENT_ADMINISTRATOR . '/builder/classes/addon.php';
			$content = SpPageBuilderAddonHelper::__($content, true);
			//$content = SpPageBuilderAddonHelper::getFontendEditingPage($content);

			echo json_encode(array('status'=>true, 'data'=>$content)); die;
		}
	}

	echo json_encode(array('status'=>false, 'data'=>'Something worng there.')); die;
}

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ajax.php';
