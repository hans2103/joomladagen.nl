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
		$urls = json_decode($item->urls);
		echo '<div class="block__title"><h2>' . $item->title . '</h2></div>';
		echo '<div class="block__subtitle">' . $item->introtext . '</div>';
		echo '<div class="block__content">' . $item->fulltext . '</div>';
		echo '<div class="block__action">' . JHtml::_('link', $urls->urla, JText::_('TPL_NVML_HOMEPAGE_LIDWORDEN_BUTTON_LABEL'), array('class' => 'button')) . '</div>';
	}
}
