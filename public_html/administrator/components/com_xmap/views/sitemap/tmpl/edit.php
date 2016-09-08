<?php

/**
 * @author      Guillermo Vargas <guille@vargas.co.cr>
 * @author      Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link        http://www.z-index.net
 * @copyright   (c) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @copyright   (c) 2015 Branko Wilhelm. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
?>
<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task == 'sitemap.cancel' || document.formvalidator.isValid(document.id('sitemap-form'))) {
            <?php echo $this->form->getField('introtext')->save(); ?>
            Joomla.submitform(task, document.getElementById('sitemap-form'));
        }
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_xmap&layout=edit&id=' . $this->item->id); ?>" method="post"
      name="adminForm" id="sitemap-form" class="form-validate">

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'menues')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'menues', JText::_('JOPTION_MENUS', true)); ?>
        <div class="row-fluid">
            <div class="span12">
                <?php echo $this->loadTemplate('menues'); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('JGLOBAL_INTRO_TEXT', true)); ?>
        <div class="row-fluid">
            <div class="span9">
                <fieldset class="adminform">
                    <?php echo $this->form->getInput('introtext'); ?>
                </fieldset>
            </div>
            <div class="span3">
                <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php if ($this->canDo->get('core.admin')) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
            <?php echo $this->form->getInput('rules'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value=""/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>