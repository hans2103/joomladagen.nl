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
	'title' => Text::_('COM_CONFERENCE_TITLE_SESSIONS')
);

echo JLayouts::render('template.content.header', $array);
?>
<section class="section__wrapper">
    <div class="container container--shift">
		<?php if (!empty($this->items)) : ?>
            <div class="article__item grid grid--flex grid--1-1-1-1">
	            <?php foreach ($this->items as $key => $item) : ?>
                    <?php if($item->level) : ?>

                    <article class="grid__item">
                            <div class="article__info">
					            <?php
                                if ($item->level): ?>
                                    <span class="article__meta-item label <?php echo $item->level_label ?>">
						                <?php echo $item->level ?>
					                </span><?php
                                endif;

					            if(!empty($item->speakers))
					            {
                                    foreach ($item->speakers as $speaker)
                                    {
	                                    echo '<span class="article__meta-item">' . Text::_(trim($speaker->title)) . '</span>';
                                    }
					            }
					            ?>

                            </div>

                            <div class="article__title">
                                <p><strong><?php
						            $url  = Route::_('index.php?option=com_conference&view=sessions&id=' . $item->conference_session_id);
						            $text = Text::_($this->escape($item->title));
						            echo HTMLHelper::_('link', $url, $text);
						            ?></strong></p>
                            </div>
                    </article>
                <?php endif; ?>
	            <?php endforeach; ?>
            </div>
		<?php endif; ?>

        <form id="conference-pagination" name="conference-pagination"
              action="<?php echo JRoute::_('index.php?option=com_conference&view=sessions'); ?>" method="post">
            <input type="hidden" name="option" value="com_conference"/>
            <input type="hidden" name="view" value="speakers"/>
			<?php if ($this->pageparams->get('show_pagination', 1)) : ?>
				<?php if ($this->pagination->get('pages.total') > 1): ?>
                    <div class="pagination">
						<?php if ($this->pageparams->get('show_pagination_results', 1)) : ?>
                            <p class="counter">
								<?php echo $this->pagination->getPagesCounter(); ?>
                            </p>
						<?php endif; ?>
						<?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
        </form>

		<?php echo Jlayouts::render('pagination', array('pages' => $this->pagination->getPagesLinks())); ?>
    </div>
</section>