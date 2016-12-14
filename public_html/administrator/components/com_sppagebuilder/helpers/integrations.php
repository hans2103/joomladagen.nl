<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderHelperIntegrations {

	public static function integrations() {
		return array( 'com_content', 'com_k2' );
	}

	public static function integrations_list() {

		$components = json_decode(file_get_contents('https://www.joomshaper.com/updates/pagebuilder/integrations.json'));
		$integrations = new stdClass;

		foreach ($components as $key => $component) {
			if(in_array($key, self::integrations())) {
				$integrations->$key = $component;
			}
		}

		return $integrations;

	}
}
