<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$attribute = ' ';
$class     = $item->anchor_css ? $item->anchor_css : '';
$title     = $item->anchor_title ? $item->anchor_title : '';
$linktype  = $item->title;

if ($item->menu_image)
{
	$item->params->get('menu_text', 1) ?
		$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
		$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" />';
}

if ($item->parent)
{
	$attribute .= 'aria-haspopup="true" ';
}

$class = $class ? ' class="nav-header ' . $class . '" ' : '';
$title = $title ? ' title="' . $title . '" ' : '';
?>
<span tabindex="0"<?php echo $attribute; ?><?php echo $class; ?><?php echo $title; ?>><?php echo $linktype; ?></span>
