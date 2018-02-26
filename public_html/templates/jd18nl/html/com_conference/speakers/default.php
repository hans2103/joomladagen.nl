<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://stichtingsympathy.nl
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$params = JComponentHelper::getParams('com_conference');

$this->template = Factory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

$array = array(
	'title' => Text::_('COM_CONFERENCE_TITLE_SPEAKERS')
);

echo JLayouts::render('template.content.header', $array);
?>
<section class="section__wrapper">
    <div class="container container--shift">
		<?php if (!empty($this->items)) : ?>
            <div class="article__item">
                <div class="grid grid--flex grid--1-1-1-1">
				<?php foreach ($this->items as $key => &$item) : ?>
                    <div class="grid__item grid__item--flex">
                    <?php
                        $data   = array(
                            'title'               => $item->title,
                            'image'               => $item->image ? $item->image : 'http://placehold.it/200x200',
                            'link'                => Route::_('index.php?option=com_conference&view=speaker&conference_speaker_id=' . $item->conference_speaker_id),
                            'image_ratio'         => 'media-placeholder--1by1',
                            'linktext'            => 'meer info'
                        );
                        echo Jlayouts::render('template.content.block-item', $data); ?>
                    </div>
				<?php endforeach; ?>
                </div>
            </div>
		<?php endif; ?>
		<?php echo Jlayouts::render('pagination', array('pages' => $this->pagination->getPagesLinks())); ?>
    </div>
</section>

