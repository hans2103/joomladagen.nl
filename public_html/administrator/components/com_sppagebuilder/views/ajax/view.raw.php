<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');
require_once ( JPATH_COMPONENT .'/builder/builder_layout.php' );
require_once ( JPATH_COMPONENT .'/builder/settings/addon-loader.php' );

jimport( 'joomla.filesystem.file' );

$input = JFactory::getApplication()->input; // GET INPUT

// ADDON ADD AND EDIT ACTION
if ( $input->get('action') === 'addon-load' ) {
	$addon_name 	= $_POST['addon_name'];
	$action_role = $input->get('action_role','addnew');

	if ( isset( $_POST['addon_settings'] ) ) {
		$addon_settings = $_POST['addon_settings'];
	} else {
		$addon_settings = '';
	}

	if (!$addon_name) return;
	$loader = new AddonLoader( $addon_name, $addon_settings, $action_role );
	echo $loader->getAddonHtml();
	die();
}

// COPY ADDON DATA
if ( $input->get('action') === 'copy' ) {
	$copy_text = $_POST['copyText'];
	$decode_text = $copy_text;

	echo base64_encode($decode_text);
	die();
}

// PASTE ADDON DATA
if ( $input->get('action') === 'paste' ) {
	$copy_text 		= $_POST['pasteText'];
	$decode_text 	= $copy_text;

	echo base64_decode($decode_text);
	die();
}

// IMPORT UPLOADED TEMPLATE/PAGE
if ( $input->get('action') === 'local-upload' ) {
	$file 				= $input->files->get('importLayout');

	if($file) {
		$content = JFile::read($file['tmp_name']);

		$import_html 	= '';
		$import_html 	.= '<div class="clearfix">';
		$import_html 	.= '<div class="page-builder-area">';
		$import_html 	.=  dataLayoutBuilder(json_decode( $content ));
		$import_html 	.= '</div>';
		$import_html 	.= '</div>';

		echo $import_html;
	} else {
		echo '<h1>There is no such template</h1>';
	}
	die();
}

// LOAD PREDEFINED TEMPLATE/PAGE
if( $input->get('action') === 'load-template' ) {
	$template_name = $_POST['template'];
	$path = JPATH_COMPONENT.'/builder/templates/'.$template_name.'.json';
	if (JFile::exists($path))
	{
		$content = JFile::read($path);

		$import_html 	= '';
		$import_html 	.= '<div class="clearfix">';
		$import_html 	.= '<div class="page-builder-area">';
		$import_html 	.=  dataLayoutBuilder(json_decode( $content ));
		$import_html 	.= '</div>';
		$import_html 	.= '</div>';

		echo $import_html;
	} else {
		echo '<h1>There is no such template</h1>';
	}
	die();
}

die();
