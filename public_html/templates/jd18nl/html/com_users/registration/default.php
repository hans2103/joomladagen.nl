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

$this->form->reset(true);
$this->form->loadFile(dirname(__FILE__) . "/../models/forms/registration.xml");

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

$title       = $this->escape($this->params->get('page_heading'));
$description = $this->params->get('login_description');
echo JLayouts::render('template.content.header', array('title' => $title, 'intro' => $description));

?>
<section class="section__wrapper">
    <div class="container container--shift">
        <div class="content content--small content__form content__form--registration">

            <form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="form-validate form__pwt" enctype="multipart/form-data">
				<?php // Iterate through the form fieldsets and display each one. ?>
				<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
					<?php $fields = $this->form->getFieldset($fieldset->name); ?>
					<?php if (count($fields)) : ?>
						<?php // If the fieldset has a label set, display it as the legend. ?>
						<?php if (isset($fieldset->label)) : ?>
                            <legend><?php echo JText::_($fieldset->label); ?></legend>
						<?php endif; ?>
						<?php // Iterate through the fields in the set and display them. ?>
						<?php foreach ($fields as $field) : ?>
							<?php // If the field is hidden, just display the input. ?>
							<?php if ($field->hidden) : ?>
								<?php echo $field->input; ?>
							<?php else : ?>
                                <div class="form-group">
									<?php echo $field->label; ?>
									<?php if (!$field->required && $field->type !== 'Spacer') : ?>
                                        <span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL'); ?></span>
									<?php endif; ?>
									<?php echo $field->input; ?>
                                </div>
							<?php endif; ?>
						<?php endforeach; ?>
                        </fieldset>
					<?php endif; ?>
				<?php endforeach; ?>
                <button type="submit" class="button validate">
					<?php echo JText::_('JREGISTER'); ?>
                </button>
                <a class="button button--secondary" href="<?php echo JRoute::_(''); ?>" title="<?php echo JText::_('JCANCEL'); ?>">
					<?php echo JText::_('JCANCEL'); ?>
                </a>
                <input type="hidden" name="option" value="com_users"/>
                <input type="hidden" name="task" value="registration.register"/>

				<?php echo JHtml::_('form.token'); ?>
            </form>
        </div>
    </div>
</section>