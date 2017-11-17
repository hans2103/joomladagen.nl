<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
$attribute = ' role="menuitem" ';
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

$class = $class ? ' class="' . $class . '" ' : '';
$title = $title ? ' title="' . $title . '" ' : '';

switch ($item->browserNav)
{
	default:
	case 0:
		?>
		<a<?php echo $attribute; ?><?php echo $class; ?>href="<?php echo $item->flink; ?>" <?php echo $title; ?>><?php echo $linktype; ?></a><?php
		break;
	case 1:
		// _blank
		?><a<?php echo $attribute; ?><?php echo $class; ?>href="<?php echo $item->flink; ?>"
		target="_blank" <?php echo $title; ?>><?php echo $linktype; ?></a><?php
		break;
	case 2:
		// Use JavaScript "window.open"
		?><a<?php echo $attribute; ?><?php echo $class; ?>href="<?php echo $item->flink; ?>"
		onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;" <?php echo $title; ?>><?php echo $linktype; ?></a>
		<?php
		break;
}
