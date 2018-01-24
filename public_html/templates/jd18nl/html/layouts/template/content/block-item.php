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

?>
<div class="block__item block__item--flex">
    <?php $link = ''; ?>

	<?php if(isset($displayData['link'])) : ?>
	<?php $link = $displayData['link']; ?>
	<?php endif; ?>
	<?php if (isset($displayData['image'])) : ?>
        <a href="<?php echo $link;?>" class="block__image">
			<?php
			$mediaPlaceholderSize = isset($displayData->image_ratio) ? $displayData->image_ratio: 'media-placeholder--16by9';
			?>
            <div class="media-placeholder <?php echo $mediaPlaceholderSize; ?>">
				<?php $src = $displayData['image']; ?>
				<?php $alt = '' /*$images->image_intro_alt ? $images->image_intro_alt : ''*/; ?>
				<?php echo JLayouts::render('template.image', array('img' => $src, 'alt' => $alt)); ?>
            </div>
        </a>
	<?php endif; ?>



	<div class="block__title">
		<h3><?php echo $displayData['title']; ?></h3>
	</div>
	<?php if(isset($displayData['link'])) : ?>
	<?php $link = $displayData['link']; ?>
	<?php $text = isset($displayData['linktext']) ? $displayData['linktext'] : 'Lees artikel'; ?>
	<div class="block__action">
		<p class="readmore"><?php echo JHtml::_('link', $link, $text, array('class' => 'readmore__link')); ?></p>
	</div>
	<?php endif; ?>

	<?php if(isset($displayData['displayCategoryLink'])) : ?>
    <div class="block__action">
	    <?php $link = $displayData['displayCategoryLink']; ?>
	    <?php $text = isset($displayData['category_title']) ? 'Meer ' . strtolower($displayData['category_title']) : 'Naar overzicht'; ?>
        <p class="readmore"><?php echo JHtml::_('link', $link, $text, array('class' => 'readmore__link')); ?></p>
    </div>
	<?php endif; ?>
</div>