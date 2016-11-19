<?php
/*
 * @package		Radiobox
 * @copyright	Copyright (c) 2015 Perfect Web Team / perfectwebteam.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class Jlayouts
{
	public static function render($type, $data = '')
	{
		$jlayout = new JLayoutFile($type, JPATH_ROOT .'/templates/perfecttemplate/html/layouts/perfectlayout/template');

		return $jlayout->render($data);
	}

	public static function renderIcon($type, $data = array())
	{
		$jlayout = new JLayoutFile($type, JPATH_ROOT .'/templates/perfecttemplate/html/layouts/perfectlayout/icons');

		return $jlayout->render($data);
	}
}
