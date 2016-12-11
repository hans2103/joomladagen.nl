<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');
require_once dirname(__FILE__).DS.'class.addon.php';

class OBSSExAddon extends OBSSAddon {
	public $config 	= null;
	public $data 	= null;
	
	function __construct($data=null) {
		$this->data = $data;
		//		Load the core and/or local language file(s).
		$lang 	= JFactory::getLanguage();
		$classname = strtolower(get_class($this));
		$addon 	= str_replace( 'obssexaddon', '', $classname );
		$lang->load('plg_obss_extern_'.$addon, JPATH_ADMINISTRATOR, null, false, false)
			|| $lang->load( 'plg_obss_extern_'.$addon, JPATH_SITE . DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'obss_extern'.DIRECTORY_SEPARATOR.$addon, null, false, false)
			|| $lang->load( 'plg_obss_extern_'.$addon, JPATH_SITE, $lang->getDefault(), false, false)
			|| $lang->load( 'plg_obss_extern_'.$addon, JPATH_SITE .DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'obss_extern'.DIRECTORY_SEPARATOR. $addon, $lang->getDefault(), false, false);
	}
}