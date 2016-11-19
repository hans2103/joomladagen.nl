<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$item_heading = $params->get('item_heading', 'h4');
$images       = json_decode($item->images);
?>


<div class="newsflash__item">
	<?php
	if (isset($images->image_intro) && !empty($images->image_intro))
	{
	    echo Jlayouts::render('image', $data = array('ratio' => '4-3', 'image' => $images->image_intro, 'class' => 'newsflash__image'));
	}

	echo '<div class="newsflash__content">';

    echo JLayoutHelper::render('perfectlayout.template.content.create_date', array('date' => $item->created, 'class' => 'newsflash', 'format' => 'DATE_FORMAT_LC4'));


	if ($params->get('item_title'))
	{
		echo '<div class="newsflash__header">';
		echo '  <' . $item_heading . ' class="newsflash__title' . $params->get('moduleclass_sfx') . '">';
		echo '      ' . JHtml::_('link', $item->link, $item->title, array('itemprop' => 'url', 'class' => 'newsflash__link'));
		echo '  </' . $item_heading . '>';
		echo '</div>';
	}

	if (!$params->get('intro_only'))
	{
		echo $item->afterDisplayTitle;
	}

	echo $item->beforeDisplayContent;

	echo $item->introtext;

	echo $item->afterDisplayContent;

	if (isset($item->link) && $item->readmore != 0 && $params->get('readmore'))
	{
		echo JHtml::_('link', $item->link, $item->linkText, array('class' => 'btn'));
	}

	echo '</div>';
	?>
</div>
