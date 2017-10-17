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
    <form action="<?php echo JRoute::_('index.php?option=com_conference&layout=edit&conference_slot_id=' . (int) $this->item->conference_slot_id); ?>" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
        <!-- Start row -->
        <div class="row-fluid">
            <!-- Start left -->
            <div class="span7">
				<?php echo $this->form->renderField('conference_day_id'); ?>
				<?php echo $this->form->renderField('start_time'); ?>
				<?php echo $this->form->renderField('end_time'); ?>
				<?php echo $this->form->renderField('general'); ?>
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
        <input type="hidden" name="task" value="" />
	    <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
