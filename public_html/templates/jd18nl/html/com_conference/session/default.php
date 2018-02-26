<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://stichtingsympathy.nl
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$params = JComponentHelper::getParams('com_conference');

$this->template = Factory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

$label = '';
if ($this->item->conference_level_id):
    $label = '&nbsp;<span class="label ' . $this->item->level->label . '">' . $this->item->level->title . '</span>';
endif;

$array = array(
	'title' => $this->escape($this->item->title) . $label
);

echo JLayouts::render('template.content.header', $array);
?>
<section class="section__wrapper">
    <div class="container">
        <div class="article__item">
            <div class="article__image">
                <p><?php
                $url  = Route::_('index.php?option=com_conference&view=days');
                $text =  '<i class="fa fa-chevron-left"> </i> ' . Text::_('TPL_LINK_BACK2PROGRAM_LABEL');
                echo HTMLHelper::_('link', $url, $text, array('class' => 'button button--back'));
                ?></p>
	            <?php
                    if ($this->item->conference_speaker_id):
		            foreach($this->item->speakers as $speaker):
                        ?>
                        <div class="media-placeholder media-placeholder--1by1">
				            <?php $src = $speaker->image ? $speaker->image : 'http://placehold.it/200x200'; ?>
				            <?php $alt = 'foto van spreker ' . $this->escape($speaker->title); ?>
				            <?php echo JLayouts::render('template.image', array('img' => $src, 'alt' => $alt)); ?>
                        </div>
                        <?php
		            endforeach;
	            endif;
	            ?>
            </div>
            <div class="article__body">

	            <?php echo ($this->item->description)?>

	            <?php if ($this->item->slides) : ?>
                    <div class="article__slides">
			            <?php echo $this->item->slides; ?>
                    </div>
	            <?php endif; ?>

                <?php if ($this->item->video) : ?>
                    <div class="article__video">
			            <?php echo $this->item->video; ?>
                    </div>
	            <?php endif; ?>

                <div class="article__meta">
	                <?php
	                $sessionspeakers = array();
	                foreach($this->item->speakers as $speaker) :
		                if($speaker->enabled):
		                    echo HTMLHelper::_('link', 'index.php?option=com_conference&view=speaker&id=' . $speaker->conference_speaker_id, trim($speaker->title), array('class'=>'article__meta-item'));
		                else:
		                    echo '<span class="article__meta-item">' . Text::_(trim($speaker->title)) . '</span>';
		                endif;
	                endforeach;

	                if($this->item->conference_room_id):
	                    echo '<span class="article__meta-item">' . Text::_($this->item->room) . '</span>';
	                endif;

	                if($this->item->conference_slot_id):
	                    echo HTMLHelper::_('link', Route::_('index.php?option=com_conference&view=days'), '<span>' . $this->item->slot . '</span>', array('class'=>'article__meta-item'));
	                endif;

	                if($this->item->conference_level_id):
	                    echo HTMLHelper::_('link', Route::_('index.php?option=com_conference&view=levels'), '<span>' . $this->item->level->title . '</span>', array('class'=>'article__meta-item'));
	                endif;

	                if (($params->get('language',0)) && ($this->item->language)):
                        echo '<span class="article__meta-item">' . Text::_($this->item->language) . '</span>';
	                endif;
	                ?>
                </div>
            </div>
        </div>
    </div>
</section>