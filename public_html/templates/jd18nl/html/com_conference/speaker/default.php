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
	'title' => $this->escape($this->item->title)
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
                <div class="media-placeholder media-placeholder--1by1">
					<?php $src = $this->item->image ? $this->item->image : 'http://placehold.it/200x200'; ?>
					<?php $alt = 'foto van spreker ' . $this->escape($this->item->title); ?>
					<?php echo JLayouts::render('template.image', array('img' => $src, 'alt' => $alt)); ?>
                </div>
            </div>
            <div class="article__body">

				<?php echo($this->item->bio) ?>


                <div class="article__social">
					<?php
					if (($this->item->twitter) && ($params->get('twitter'))):
						$src  = 'https://twitter.com/' . $this->item->twitter;
						$text = '<span class="icon conference-twitter"></span>' . $this->item->twitter;
						echo HTMLHelper::_('link', $src, $text);
					endif;

					if (($this->item->facebook) && ($params->get('facebook'))):
						$src  = 'https://facebook.com/' . $this->item->facebook;
						$text = '<span class="icon conference-facebook"></span>' . $this->item->facebook;
						echo HTMLHelper::_('link', $src, $text);
					endif;

					if (($this->item->googleplus) && ($params->get('googleplus'))):
						$src  = 'https://plus.google.com/' . $this->item->googleplus;
						$text = '<span class="icon conference-google-plus"></span>' . $this->item->title;
						echo HTMLHelper::_('link', $src, $text);
					endif;

					if (($this->item->linkedin) && ($params->get('linkedin'))):
						$src  = 'https://www.linkedin.com/in/' . $this->item->linkedin;
						$text = '<span class="icon conference-linkedin"></span>' . $this->item->linkedin;
						echo HTMLHelper::_('link', $src, $text);
					endif;

					if (($this->item->website) && ($params->get('website'))):
						$src  = 'http://' . $this->item->website;
						$text = '<span class="icon conference-earth"></span>' . $this->item->website;
						echo HTMLHelper::_('link', $src, $text);
					endif;
					?>
                </div>

				<?php if ($this->item->sessions): ?>
                    <h2><?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS') ?></h2>
                    <table class="table table-striped">
                        <tbody>
						<?php foreach ($this->item->sessions as $session): ?>
                            <tr>
                                <td>
									<?php if ($session->listview): ?>
                                        <a href="<?php echo JRoute::_('index.php?option=com_conference&view=sessions&id=' . $session->conference_session_id) ?>">
											<?php echo($session->title) ?>
                                        </a>
									<?php else : ?>
										<?php echo($session->title) ?>
									<?php endif; ?>
                                </td>
                            </tr>
						<?php endforeach; ?>
                        </tbody>
                    </table>
				<?php endif; ?>
            </div>
        </div>
    </div>
</section>