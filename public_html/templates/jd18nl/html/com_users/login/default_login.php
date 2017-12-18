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

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

$title       = $this->escape($this->params->get('page_heading'));
$description = $this->params->get('login_description');
echo JLayouts::render('template.content.header', array('title' => $title, 'intro' => $description));

?>
<section class="section__wrapper">
    <div class="container container--shift">
        <div class="content content--shift content__form content__form--login">
            <form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post"
                  class="form-validate form__pwt">

				<?php foreach ($this->form->getFieldset('credentials') as $field) : ?>
					<?php if (!$field->hidden) : ?>
                        <div class="form-group">
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
                        </div>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php if ($this->tfa) : ?>
                    <div class="form-group">
						<?php echo $this->form->getField('secretkey')->label; ?>
						<?php echo $this->form->getField('secretkey')->input; ?>
                    </div>
				<?php endif; ?>
				<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
                    <div class="form-group">
                        <label class="form-label" for="remember">
							<?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME'); ?>
                        </label>
                        <input id="remember" type="checkbox" name="remember" class="form-input inputbox" value="yes"/>
                    </div>
				<?php endif; ?>

                <button type="submit" class="button">
					<?php echo JText::_('JLOGIN'); ?>
                </button>
				<?php $return = $this->form->getValue('return', '', $this->params->get('login_redirect_url', $this->params->get('login_redirect_menuitem'))); ?>
                <input type="hidden" name="return" value="<?php echo base64_encode($return); ?>"/>
				<?php echo JHtml::_('form.token'); ?>
            </form>

            <script>
                setFocusToTextBox('username');
            </script>

            <ul class="form__pwt-menu">
                <li>
                    <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
						<?php echo JText::_('COM_USERS_LOGIN_RESET'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
						<?php echo JText::_('COM_USERS_LOGIN_REMIND'); ?>
                    </a>
                </li>
				<?php $usersConfig = JComponentHelper::getParams('com_users'); ?>
				<?php if ($usersConfig->get('allowUserRegistration')) : ?>
                    <li>
                        <a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
							<?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?>
                        </a>
                    </li>
				<?php endif; ?>
            </ul>
        </div>
    </div>
</section>