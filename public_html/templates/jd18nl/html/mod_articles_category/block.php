<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!empty($list))
{
	foreach ($list as $item)
	{
		echo '<div class="grid__item">';
		$images = json_decode($item->images);
		$data   = array(
			'title' => $item->title,
			'image' => $images->image_intro ,
			'link'  => $item->link
		);
		echo Jlayouts::render('template.content.block-item', $data);
		echo '</div>';
	}
}
