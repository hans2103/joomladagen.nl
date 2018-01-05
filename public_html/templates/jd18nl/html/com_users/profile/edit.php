<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

// Load user_profile plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_user_profile', JPATH_ADMINISTRATOR);

//$this->form->reset(true);
$this->form->loadFile( dirname(__FILE__) . "/../models/forms/profile.xml");

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

$title       = $this->escape($this->params->get('page_heading'));
$description = $this->params->get('login_description');
echo JLayouts::render('template.content.header', array('title' => $title, 'intro' => $description));

?>
<section class="section__wrapper">
    <div class="container container--shift">
        <div class="content content--small content__profile content__profile-edit">
            <script type="text/javascript">
                Joomla.twoFactorMethodChange = function (e) {
                    var selectedPane = 'com_users_twofactor_' + jQuery('#jform_twofactor_method').val();

                    jQuery.each(jQuery('#com_users_twofactor_forms_container>div'), function (i, el) {
                        if (el.id != selectedPane) {
                            jQuery('#' + el.id).hide(0);
                        }
                        else {
                            jQuery('#' + el.id).show(0);
                        }
                    });
                }
            </script>
            <form id="member-profile" action="<?php echo JRoute::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate form__pwt" enctype="multipart/form-data">
				<?php // Iterate through the form fieldsets and display each one. ?>
				<?php foreach ($this->form->getFieldsets() as $group => $fieldset) : ?>
					<?php $fields = $this->form->getFieldset($group); ?>
					<?php if (count($fields)) : ?>
						<?php // If the fieldset has a label set, display it as the legend. ?>
						<?php if (isset($fieldset->label)) : ?>
                            <legend>
								<?php echo JText::_($fieldset->label); ?>
                            </legend>
						<?php endif; ?>
						<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
                            <p>
								<?php echo $this->escape(JText::_($fieldset->description)); ?>
                            </p>
						<?php endif; ?>
						<?php // Iterate through the fields in the set and display them. ?>
						<?php foreach ($fields as $field) : ?>
							<?php // If the field is hidden, just display the input. ?>
							<?php if ($field->hidden) : ?>
								<?php echo $field->input; ?>
							<?php else : ?>
                                <div class="form-group">
									<?php echo $field->label; ?>
									<?php if (false && !$field->required && $field->type !== 'Spacer') : ?>
                                        <span class="optional">
											<?php echo JText::_('COM_USERS_OPTIONAL'); ?>
										</span>
									<?php endif; ?>
									<?php if ($field->fieldname === 'password1') : ?>
										<?php // Disables autocomplete ?>
                                        <input type="password" style="display:none">
									<?php endif; ?>
									<?php echo $field->input; ?>

                                </div>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if (count($this->twofactormethods) > 1) : ?>
                    <fieldset>
                        <legend><?php echo JText::_('COM_USERS_PROFILE_TWO_FACTOR_AUTH'); ?></legend>
                        <div class="form-group">
                            <label id="jform_twofactor_method-lbl" for="jform_twofactor_method"
                                   class="form-label hasTooltip"
                                   title="<?php echo '<strong>' . JText::_('COM_USERS_PROFILE_TWOFACTOR_LABEL') . '</strong><br />' . JText::_('COM_USERS_PROFILE_TWOFACTOR_DESC'); ?>">
								<?php echo JText::_('COM_USERS_PROFILE_TWOFACTOR_LABEL'); ?>
                            </label>
							<?php echo JHtml::_('select.genericlist', $this->twofactormethods, 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false); ?>
                        </div>
                        <div id="com_users_twofactor_forms_container">
							<?php foreach ($this->twofactorform as $form) : ?>
								<?php $style = $form['method'] == $this->otpConfig->method ? 'display: block' : 'display: none'; ?>
                                <div id="com_users_twofactor_<?php echo $form['method']; ?>"
                                     style="<?php echo $style; ?>">
									<?php echo $form['form']; ?>
                                </div>
							<?php endforeach; ?>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>
							<?php echo JText::_('COM_USERS_PROFILE_OTEPS'); ?>
                        </legend>
                        <div class="alert alert-info">
							<?php echo JText::_('COM_USERS_PROFILE_OTEPS_DESC'); ?>
                        </div>
						<?php if (empty($this->otpConfig->otep)) : ?>
                            <div class="alert alert-warning">
								<?php echo JText::_('COM_USERS_PROFILE_OTEPS_WAIT_DESC'); ?>
                            </div>
						<?php else : ?>
							<?php foreach ($this->otpConfig->otep as $otep) : ?>
                                <span class="span3">
							<?php echo substr($otep, 0, 4); ?>-<?php echo substr($otep, 4, 4); ?>
                                    -<?php echo substr($otep, 8, 4); ?>-<?php echo substr($otep, 12, 4); ?>
						</span>
							<?php endforeach; ?>
                            <div class="clearfix"></div>
						<?php endif; ?>
                    </fieldset>
				<?php endif; ?>

                <button type="submit" class="button validate">
					<?php echo JText::_('JSUBMIT'); ?>
                </button>
                <a class="button button--secondary"
                   href="<?php echo JRoute::_('index.php?option=com_users&view=profile'); ?>"
                   title="<?php echo JText::_('JCANCEL'); ?>">
					<?php echo JText::_('JCANCEL'); ?>
                </a>
                <input type="hidden" name="option" value="com_users"/>
                <input type="hidden" name="task" value="profile.save"/>

				<?php echo JHtml::_('form.token'); ?>
            </form>
        </div>

    </div>
</section>