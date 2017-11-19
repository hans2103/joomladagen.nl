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

$title       = JHtml::_('content.prepare', $this->category->title);
$description = ($this->category->description) ? $this->category->description : '';

echo JLayouts::render('template.content.header', array('title' => $title, 'intro' => $description));
?>
<section class="section__wrapper">
    <div class="container container--shift">
		<?php if (!empty($this->items)) : ?>
            <div class="content">
				<?php foreach ($this->items as $key => &$item) : ?>
					<?php
					$this->item = &$item;
					echo $this->loadTemplate('item');
					?>
				<?php endforeach; ?>
            </div>
		<?php endif; ?>
		<?php echo Jlayouts::render('pagination', array('pages' => $this->pagination->getPagesLinks())); ?>
    </div>
</section>