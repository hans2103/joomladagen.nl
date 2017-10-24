<?php
/*
 * @package		template
 * @copyright	Copyright (c) 2015 Perfect Web Team / perfectwebteam.nl
 * @license		GNU General Public License version 3 or later
 */
// No direct access.
defined('_JEXEC') or die;
class Jlayouts
{
	public static function render($type, $data = '')
	{
		$template = JFactory::getApplication()->getTemplate();
		$jlayout = new JLayoutFile($type, JPATH_THEMES . '/' . $template . '/html/layouts/template');
		
		return $jlayout->render($data);
	}
	
	public static function icon($type)
	{
		$template = JFactory::getApplication()->getTemplate();
		include(JPATH_THEMES . '/' . $template . '/icons/' . $type . '.svg');
	}
}
