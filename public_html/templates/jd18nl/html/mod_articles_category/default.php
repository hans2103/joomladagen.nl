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
		$item->item_title_Tag = 'h3';
		$item->image_ratio = isset($params['image_ratio']) ? $params['image_ratio'] : null;
		echo JLayoutHelper::render('template.content.article_item-list', $item);
	}
}