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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JPluginHelper::importPlugin('content');

// Create shortcuts to some parameters.
$params      = $this->item->params;
$images      = json_decode($this->item->images);
$urls        = json_decode($this->item->urls);
$title       = JHtml::_('content.prepare', $this->item->title);
$description = ($this->item->introtext) ? $this->item->introtext : '';

echo JLayouts::render('template.content.header', array('title' => $title));
?>
<section class="section__wrapper">
    <div class="container">
        <div class="article__item">
			<?php if (isset($images->image_intro) && !empty($images->image_intro)) : ?>
                <div class="article__image">
                    <div class="media-placeholder media-placeholder--1by1">
						<?php $src = $images->image_intro; ?>
						<?php $alt = $images->image_intro_alt ? $images->image_intro_alt : ''; ?>
						<?php echo JLayouts::render('template.image', array('img' => $src, 'alt' => $alt)); ?>
                    </div>
                </div>
			<?php endif; ?>
            <div class="article__body">
				<?php echo $this->item->event->beforeDisplayContent; ?>
				<?php echo $this->item->text; ?>
				<?php echo $this->item->event->afterDisplayContent; ?>
            </div>
        </div>
    </div>
</section>