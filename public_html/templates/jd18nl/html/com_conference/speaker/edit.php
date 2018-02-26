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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

$params = JComponentHelper::getParams('com_conference');

Factory::getDocument()->addScriptDeclaration("
        Joomla.submitbutton = function(task)
        {
            var form = document.getElementById('speakerForm');
            
            if (task == 'speaker.cancel' || document.formvalidator.isValid(form))
            {
                    " . $this->form->getField('bio')->save() . "
                    Joomla.submitform(task, form);
            }
        }
");

$this->template = Factory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

$array = array(
	'title' => Text::_('COM_CONFERENCE_FIELD_SPEAKER')
);

echo JLayouts::render('template.content.header', $array);
?>
<section class="section__wrapper">
    <div class="container">
        <div class="article__item">
            <form name="speakerForm" id="speakerForm" class="form form-horizontal form-validate"
                  action="<?php echo Route::_('index.php?option=com_conference&task=speaker.edit&id=' . $this->form->getValue('conference_speaker_id')); ?>"
                  method="post" enctype="multipart/form-data">
				<?php
				echo LayoutHelper::render('toolbar', array(
					'title' => 'COM_CONFERENCE_FIELD_SPEAKER',
					'view'  => 'speaker'
				));
				?>
                <div class="well well-small">
                    <!-- Start row -->
                    <div class="row-fluid">
                        <!-- Start left -->
                        <div class="span12">
							<?php echo $this->form->renderField('title'); ?>

							<?php if (!Factory::getUser()->id): ?>
                                <div class="control-group">
                                    <div class="control-label">
										<?php echo $this->form->getLabel('email'); ?>
                                    </div>
                                    <div class="controls">
										<?php echo $this->form->getInput('email'); ?>
                                        <span class="help-block"><?php echo Text::_('COM_CONFERENCE_FIELD_EMAIL_DESC') ?></span>
                                    </div>
                                </div>
							<?php endif; ?>

                            <div class="control-group">
                                <div class="control-label">
									<?php echo $this->form->getLabel('speakernotes'); ?>
                                </div>
                                <div class="controls">
									<?php echo $this->form->getInput('speakernotes'); ?>
                                    <span class="help-block"><?php echo Text::_('COM_CONFERENCE_FIELD_NOTES_DESC') ?></span>
                                </div>
                            </div>
                            <hr>
							<?php if ($params->get('twitter', 1)): ?>
                                <div class="control-group">
                                    <div class="control-label">
										<?php echo $this->form->getLabel('twitter'); ?>
                                    </div>
                                    <div class="controls">
                                        <div class="input-prepend">
                                            <span class="add-on">@</span>
											<?php echo $this->form->getInput('twitter'); ?>
                                        </div>
                                    </div>
                                </div>
							<?php endif; ?>
							<?php if ($params->get('facebook', 1)): ?>
                                <div class="control-group">
                                    <div class="control-label">
										<?php echo $this->form->getLabel('facebook'); ?>
                                    </div>
                                    <div class="controls">
                                        <div class="input-prepend">
                                            <span class="add-on">https://www.facebook.com/</span>
											<?php echo $this->form->getInput('facebook'); ?>
                                        </div>
                                    </div>
                                </div>
							<?php endif; ?>
							<?php if ($params->get('googleplus', 1)): ?>
                                <div class="control-group">
                                    <div class="control-label">
										<?php echo $this->form->getLabel('googleplus'); ?>
                                    </div>
                                    <div class="controls">
                                        <div class="input-prepend">
                                            <span class="add-on">https://plus.google.com/</span>
											<?php echo $this->form->getInput('googleplus'); ?>
                                        </div>
                                    </div>
                                </div>
							<?php endif; ?>
							<?php if ($params->get('linkedin', 1)): ?>
                                <div class="control-group">
                                    <div class="control-label">
										<?php echo $this->form->getLabel('linkedin'); ?>
                                    </div>
                                    <div class="controls">
                                        <div class="input-prepend">
                                            <span class="add-on">https://www.linkedin.com/in/</span>
											<?php echo $this->form->getInput('linkedin'); ?>
                                        </div>
                                    </div>
                                </div>
							<?php endif; ?>
							<?php if ($params->get('website', 1)): ?>
                                <div class="control-group">
                                    <div class="control-label">
										<?php echo $this->form->getLabel('website'); ?>
                                    </div>
                                    <div class="controls">
                                        <div class="input-prepend">
                                            <span class="add-on">https://</span>
											<?php echo $this->form->getInput('website'); ?>
                                        </div>
                                    </div>
                                </div>
							<?php endif; ?>
                            <hr>
							<?php echo $this->form->renderField('bio'); ?>
                            <hr>
                            <div class="control-group">
                                <div class="control-label">
									<?php echo $this->form->getLabel('image'); ?>
                                </div>
                                <div class="controls">
									<?php echo $this->form->getInput('image'); ?>
                                    <span class="help-block"><?php echo Text::_('COM_CONFERENCE_FIELD_IMAGE_DESC') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="conference_speaker_id"
                       value="<?php echo $this->form->getValue('conference_speaker_id'); ?>"/>
                <input type="hidden" name="return"
                       value="<?php echo Factory::getApplication()->input->get('return', '', 'base64'); ?>"/>
				<?php echo $this->form->renderField('conference_speaker_id'); ?>
				<?php echo $this->form->renderField('enabled'); ?>
				<?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>
    </div>
</section>