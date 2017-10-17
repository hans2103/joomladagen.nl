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
$params = JComponentHelper::getParams('com_conference');
?>
<div class="conference">
	<form action="<?php echo JRoute::_('index.php?option=com_conference&layout=edit&conference_speaker_id=' . (int) $this->item->conference_speaker_id); ?>" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
		<!-- Start row -->
		<div class="row-fluid">
			<!-- Start left -->
			<div class="span7">
				<?php echo $this->form->renderField('title'); ?>
				<?php echo $this->form->renderField('slug'); ?>
				<?php echo $this->form->renderField('user_id'); ?>
				<?php echo $this->form->renderField('email'); ?>
				<hr>
				<?php echo $this->form->renderField('conference_event_id'); ?>
				<hr>
				<?php if ($params->get('twitter',1)): ?>
				<div class="control-group">
                    <label for="twitter" class="control-label">
                        <?php echo $this->form->getLabel('twitter'); ?>
                    </label>
					<div class="controls">
						<div class="input-prepend">
							<span class="add-on">@</span>
							<?php echo $this->form->getInput('twitter'); ?>
						</div>
					</div>
				</div>
                <?php endif; ?>
				<?php if ($params->get('facebook',1)): ?>
                <div class="control-group">
                    <label for="facebook" class="control-label">
						<?php echo $this->form->getLabel('facebook'); ?>
                    </label>
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on">http://www.facebook.com/</span>
							<?php echo $this->form->getInput('facebook'); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
				<?php if ($params->get('googleplus',1)): ?>
				<div class="control-group">
					<label for="googleplus" class="control-label">
						<?php echo $this->form->getLabel('googleplus'); ?>
					</label>
					<div class="controls">
						<div class="input-prepend">
							<span class="add-on">http://plus.google.com/</span>
							<?php echo $this->form->getInput('googleplus'); ?>
						</div>
					</div>
				</div>
				<?php endif;?>
				<?php if ($params->get('linkedin',1)): ?>
				<div class="control-group">
					<label for="googleplus" class="control-label">
						<?php echo $this->form->getLabel('linkedin'); ?>
					</label>
					<div class="controls">
						<div class="input-prepend">
							<span class="add-on">http://www.linkedin.com/in/</span>
							<?php echo $this->form->getInput('linkedin'); ?>
						</div>
					</div>
				</div>
				<?php endif;?>
				<?php if ($params->get('website',1)): ?>
				<div class="control-group">
					<label for="website" class="control-label">
						<?php echo $this->form->getLabel('website'); ?>
					</label>
					<div class="controls">
						<div class="input-prepend">
							<span class="add-on">https://</span>
							<?php echo $this->form->getInput('website'); ?>
						</div>
					</div>
				</div>
				<?php endif;?>
				<hr>
				<?php echo $this->form->renderField('bio'); ?>
				<hr>
				<?php echo $this->form->renderField('image'); ?>
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
				</div>

				<div class="well iteminfo">
					<?php echo $this->form->renderField('speakernotes'); ?>
                    <span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_NOTES_SPEAKER_DESC')?></span>
				</div>

				<div class="well iteminfo">
					<?php echo $this->form->renderField('notes'); ?>
                    <span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_NOTES_INTERNAL_DESC')?></span>
				</div>
			</div>
			<!-- End right -->
		</div>
		<!-- End row -->
        <input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
