<?php
/*
 * @package		template
 * @copyright	Copyright (c) 2015 Perfect Web Team / perfectwebteam.nl
 * @license		GNU General Public License version 3 or later
 */
// No direct access.
use Joomla\CMS\Factory;

defined('_JEXEC') or die;
class Jlayouts
{
	public static function render($type, $data = '')
	{
		$template = Factory::getApplication()->getTemplate();
		$jlayout = new JLayoutFile($type, JPATH_THEMES . '/' . $template . '/html/layouts/template');
		
		return $jlayout->render($data);
	}
	
	public static function icon($type)
	{
		$template = Factory::getApplication()->getTemplate();
		include(JPATH_THEMES . '/' . $template . '/icons/' . $type . '.svg');
	}
}
