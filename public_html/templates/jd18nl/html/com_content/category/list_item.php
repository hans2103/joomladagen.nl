<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

if (PWTTemplateHelper::getPath()[0] == 'sponsors')
{
	$this->item->image_ratio = 'media-placeholder--16by9';
}

if (PWTTemplateHelper::getPath()[0] == 'nieuws')
{
	$this->item->image_ratio = 'media-placeholder--4by3';
}

if (PWTTemplateHelper::getPath()[0] == 'interviews')
{
	$this->item->image_ratio = 'media-placeholder--4by3';
}

echo JLayoutHelper::render('template.content.article_item-list', $this->item);