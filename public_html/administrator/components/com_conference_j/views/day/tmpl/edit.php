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
    <form action="index.php?option=com_conference&view=day" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="conference_day_id" value="<?php echo $this->item->conference_day_id ?>" />
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
		
		<!-- Start row -->
		<div class="row-fluid">
			<!-- Start left -->
			<div class="span7">
				<?php echo $this->form->renderField('title'); ?>
				<?php echo $this->form->renderField('conference_event_id'); ?>
				<?php echo $this->form->renderField('date'); ?>
				<?php echo $this->form->renderField('description'); ?>
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
			</div>
			<!-- End right -->
		</div>
		<!-- End row -->
	</form>
</div>