<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://joomladagen.nl
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen');
?>

<div class="conference">
    <form action="index.php?option=com_conference&view=session" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="conference_session_id" value="<?php echo $this->item->conference_session_id ?>" />
        <input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
	
		<!-- Start row -->
		<div class="row-fluid">
			<!-- Start left -->
			<div class="span7">
				<?php echo $this->form->renderField('title'); ?>
				<?php echo $this->form->renderField('slug'); ?>
				<?php echo $this->form->renderField('conference_speaker_id'); ?>
				<?php echo $this->form->renderField('conference_level_id'); ?>
				<?php echo $this->form->renderField('conference_room_id'); ?>
				<?php echo $this->form->renderField('conference_slot_id'); ?>
				<?php echo $this->form->renderField('language'); ?>
				<hr>
				<?php echo $this->form->renderField('description'); ?>
				<hr>
				<?php echo $this->form->renderField('slides'); ?>
                <span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_SLIDES_DESC')?></span>
				<hr>
				<?php echo $this->form->renderField('video'); ?>
                <span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_VIDEO_DESC')?></span>
			</div>
			<!-- End left -->
			
			<!-- Start right -->
			<div class="span5">
				<div class="well iteminfo">
                    <?php echo $this->form->renderField('enabled'); ?>
                    <?php echo $this->form->renderField('created_by'); ?>
                    <?php echo $this->form->renderField('created_on'); ?>
                    <?php echo $this->form->renderField('modified_by'); ?>
                    <?php echo $this->form->renderField('modified_on'); ?>
                    <?php echo $this->form->renderField('listview'); ?>
				</div>
				
				<div class="well iteminfo">
					<?php echo $this->form->renderField('notes'); ?>
                    <span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_NOTES_INTERNAL_DESC')?></span>
				</div>
			</div>
			<!-- End right -->
		</div>
		<!-- End row -->
	</form>
</div>