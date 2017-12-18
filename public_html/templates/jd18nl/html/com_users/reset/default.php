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
$this->form->loadFile( dirname(__FILE__) . "/../models/forms/reset_request.xml");

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

$title       = $this->escape($this->params->get('page_heading'));
$description = $this->params->get('login_description');
echo JLayouts::render('template.content.header', array('title' => $title, 'intro' => $description));

?>
<section class="section__wrapper">
    <div class="container container--shift">
        <div class="content content--shift content__form content__form--reset">

            <form id="user-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=reset.request'); ?>" method="post" class="form-validate form__pwt">
				<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
                        <p><?php echo JText::_($fieldset->label); ?></p>
						<?php foreach ($this->form->getFieldset($fieldset->name) as $name => $field) : ?>
							<?php if ($field->hidden === false) : ?>
                                <div class="form-group">
									<?php echo $field->label; ?>
									<?php echo $field->input; ?>
                                </div>
							<?php endif; ?>
						<?php endforeach; ?>
				<?php endforeach; ?>
                <button type="submit" class="button validate">
					<?php echo JText::_('JSUBMIT'); ?>
                </button>
				<?php echo JHtml::_('form.token'); ?>
            </form>
        </div>
    </div>
</section>