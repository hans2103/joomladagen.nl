<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Create a shortcut for params.
$params  = $displayData->params;
$canEdit = $displayData->params->get('access-edit');
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

echo '<div class="article__title">';

if ($params->get('show_title'))
{
    $headerTag = isset($displayData->item_title_Tag) ? $displayData->item_title_Tag : 'h2';
    echo '<' . $headerTag . '>';
	if($params->get('link_titles') && ($params->get('access-view') || $params->get('show_noauth', '0') == '1'))
	{
		$href = JRoute::_(ContentHelperRoute::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language));
		$text = $this->escape($displayData->title);
		echo JHtml::_('link', $href, $text, array('itemprop' => 'url', 'class' => 'article__title-link'));
	}
	else
	{
		echo $this->escape($displayData->title);
	}
    echo '</' . $headerTag . '>';
}

if($displayData->state == 0)
{
	echo '<span class="label label-warning">' . JText::_('JUNPUBLISHED') . '</span>';
}

if(strtotime($displayData->publish_up) > strtotime(JFactory::getDate()))
{
	echo '<span class="label label-warning">' . JText::_('JNOTPUBLISHEDYET') . '</span>';
}

if ($displayData->publish_down != JFactory::getDbo()->getNullDate()
		&& (strtotime($displayData->publish_down) < strtotime(JFactory::getDate()))
	)
{
	echo '<span class="label label-warning">' . JText::_('JEXPIRED') . '</span>';
}
echo '</div>';
