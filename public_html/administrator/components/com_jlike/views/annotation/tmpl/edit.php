<?php
/**
 * @version     1.2
 * @package     com_jlike
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <contact@techjoomla.com> - http://www.techjoomla.com
 */
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

if(JVERSION>3.0)
{
	JHtml::_('formbehavior.chosen', 'select');
}

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_jlike/assets/css/jlike.css');
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function() {

    });

    Joomla.submitbutton = function(task)
    {
        if (task == 'annotation.cancel') {
            Joomla.submitform(task, document.getElementById('annotation-form'));
        }
        else {

            if (task != 'annotation.cancel' && document.formvalidator.isValid(document.id('annotation-form'))) {

                Joomla.submitform(task, document.getElementById('annotation-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jlike&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="annotation-form" class="form-validate">

    <div class="form-horizontal">
		<?php if (JVERSION>=3.0)
		{  ?>
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
			<?php
		} ?>

		<?php if (JVERSION>=3.0) { ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_JLIKE_ANNOTATION', true)); ?>
		<?php } ?>
        <div class="row-fluid">
            <div class="span10 form-horizontal">
                <fieldset class="adminform">

                    			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
			</div>
				<input type="hidden" name="jform[user_id]" value="<?php echo $this->item->user_id; ?>" />
				<input type="hidden" name="jform[content_id]" value="<?php echo $this->item->content_id; ?>" />
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('annotation'); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('annotation'); ?></div>
			</div>
				<input type="hidden" name="jform[privacy]" value="<?php echo $this->item->privacy; ?>" />
				<input type="hidden" name="jform[annotation_date]" value="<?php echo $this->item->annotation_date; ?>" />
				<input type="hidden" name="jform[parent_id]" value="<?php echo $this->item->parent_id; ?>" />


                </fieldset>
            </div>
        </div>
		<?php if (JVERSION>=3.0){ ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php } ?>

		<?php if (JVERSION>=3.0){ ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		<?php } ?>

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>
