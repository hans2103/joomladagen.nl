<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('BannerHelper', JPATH_ROOT . '/components/com_banners/helpers/banner.php');

echo '<div class="grid grid--1-1-1">';
foreach ($list as $item) :
	echo '<div class="grid__item">';
	$link = JRoute::_('index.php?option=com_banners&task=click&id=' . $item->id);
	if ($item->type == 1) :
		// Text based banners
		echo str_replace(array('{CLICKURL}', '{NAME}'), array($link, $item->name), $item->custombannercode);
	else :
		$imageurl = $item->params->get('imageurl');
		if (BannerHelper::isImage($imageurl)) :
			echo '<div class="article__image">';
			echo '<div class="media-placeholder media-placeholder--4by2">';

			// Image based banner
			$baseurl = strpos($imageurl, 'http') === 0 ? '' : JUri::base();
			$alt     = $item->params->get('alt');
			$alt     = $alt ?: $item->name;
			$alt     = $alt ?: JText::_('MOD_BANNERS_BANNER');
			if ($item->clickurl) :
				// Wrap the banner in a link
				$target = $params->get('target', 1);
				$img    = JLayouts::render('template.image', array('img' => $baseurl . $imageurl, 'alt' => $alt));
				if ($target == 1) :
					// Open in a new window
					$array = array(
						'target' => '_blank',
						'rel'    => 'noopener noreferrer',
						'title'  => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8')
					);
				elseif ($target == 2) :
					// Open in a popup window
					$array = array(
						'onclick' => 'window.open(this.href, \'\', \'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550\');return false',
						'title'   => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8')
					);
				else :
					// Open in parent window
					$array = array(
						'title' => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8')
					);
				endif;
				echo JHtml::_('link', $link, $img, $array);
			else :
				// Just display the image if no link specified
				echo JLayouts::render('template.image', array('img' => $baseurl . $imageurl, 'alt' => $alt));
			endif;
			echo '</div>';
			echo '</div>';
		endif;
	endif;
	echo '</div >';
endforeach;

if ($footerText) :
	echo '<div class="bannerfooter" >';
	echo $footerText;
	echo '</div >';
endif;
echo '</div >';
