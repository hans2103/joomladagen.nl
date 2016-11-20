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


<div class="newsflash<?php echo $moduleclass_sfx; ?>__item">
	<?php
	if (isset($images->image_intro) && !empty($images->image_intro))
	{
		echo Jlayouts::render('image', $data = array('ratio' => '4-3', 'image' => $images->image_intro, 'class' => 'newsflash' . $moduleclass_sfx . '__image'));
	}

	echo '<div class="newsflash' . $moduleclass_sfx . '__content">';

	echo JLayoutHelper::render('perfectlayout.template.content.create_date', array('date' => $item->created, 'class' => 'newsflash' . $moduleclass_sfx . '', 'format' => 'DATE_FORMAT_LC4'));


	if ($params->get('item_title'))
	{
		echo '<div class="newsflash' . $moduleclass_sfx . '__header">';
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
		switch ($item->category_alias)
		{
			case 'interviews':
				$btnClass = 'btn--ghost--inverse';
				break;

			case 'news':
				$btnClass = 'btn';
				break;

			default:
				$btnClass = 'btn';
		}
		echo JHtml::_('link', $item->link, $item->linkText, array('class' => '' . $btnClass . ''));
	}

	echo '</div>';
	?>
</div>

