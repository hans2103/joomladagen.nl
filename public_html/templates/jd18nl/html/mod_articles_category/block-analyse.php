<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>

<?php if (!empty($list)) : ?>
		<?php foreach ($list as $item) : ?>
		<div class="block block--analyse">
				<div class="analyse__title">
					<?php echo Jlayouts::icon('logo-analyse'); ?>
				</div>
				<div class="analyse__cover">
					<div class="media-placeholder media-placeholder--A4">
						<?php $images = json_decode($item->images); ?>
						<?php $src = $images->image_intro; ?>
						<?php $alt = $images->image_intro_alt ? $images->image_intro_alt : ''; ?>
						<?php echo JLayouts::render('template.image', array('img' => $src, 'alt' => $alt)); ?>

					</div>
				</div>
				<div class="analyse__meta">
					<div class="analyse__release-number">Nummer 1</div>
					<div class="analyse__release-data">Februari 2017</div>
				</div>
				<div class="analyse__content">
					<p>In dit nummer:</p>
					<ul>
						<li>Lorem ipsum dolor</li>
						<li>Sit amet, consectetur</li>
						<li>Adipiscing elit aenean</li>
						<li>Euismod bibendum</li>
						<li>Lorem ipsum dolor</li>
						<li>Sit amet, consectetur</li>
						<li>Lorem ipsum dolor</li>
						<li>Sit amet, consectetur</li>
					</ul>
				</div>
				<div class="analyse__action">
					<?php echo JHtml::_('link', 'https://nu.nl', 'Lees meer', array('class' => 'button')); ?>
				</div>
			</div>



			<?php
			//$item->item_title_Tag = 'h3';
			//echo JLayoutHelper::render('template.content.article_item-list', $item);
			?>
		<?php endforeach; ?>
<?php endif; ?>

