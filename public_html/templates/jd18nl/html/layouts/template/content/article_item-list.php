<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

// Create a shortcut for params.
$params  = $displayData->params;
$images = json_decode($displayData->images);
?>
<article class="article__item article__item--list">
	<?php if (isset($images->image_intro) && !empty($images->image_intro)) : ?>
		<div class="article__image">
            <?php
                $mediaPlaceholderSize = isset($displayData->image_ratio) ? $displayData->image_ratio: 'media-placeholder--1by1';
            ?>
			<div class="media-placeholder <?php echo $mediaPlaceholderSize; ?>">
				<?php $src = $images->image_intro; ?>
				<?php $alt = $images->image_intro_alt ? $images->image_intro_alt : ''; ?>
				<?php echo JLayouts::render('template.image', array('img' => $src, 'alt' => $alt)); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="article__body">
		<div class="article__info">
			<?php if ($params->get('show_category')) : ?>
				<span class="article__category"><?php echo $this->escape($displayData->category_title); ?></span>
			<?php endif; ?>
			<?php if ($params->get('show_create_date')) : ?>
				<?php echo JLayoutHelper::render('template.content.create_date', array('date' => $displayData->created, 'format' => 'DATE_FORMAT_CC1', 'class' => 'article__date')); ?>
			<?php endif; ?>
		</div>

		<?php echo JLayoutHelper::render('template.content.blog_title', $displayData); ?>

		<div class="article__content">
			<?php echo $displayData->introtext; ?>

			<?php echo JLayoutHelper::render('template.content.readmore', array('item' => $displayData, 'params' => $params)); ?>
		</div>
	</div>
</article>
