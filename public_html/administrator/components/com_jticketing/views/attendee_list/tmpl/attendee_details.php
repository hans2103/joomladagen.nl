<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
$input=JFactory::getApplication()->input;
$attendee_id = $input->get('attendee_id','','INT');
$data_present = 0;
?>

<?php if (JVERSION < '3.0'): ?>
	<div class="techjoomla-bootstrap">
<?php endif; ?>

		<div class="page-header">
			<h3><?php echo JText::_('COM_JTICKETING_ATTENDEE_DETAILS_HEADER'); ?></h3>
		</div>

		<div class="row-fuild jticketing-controls well">
			<div class="form-horizontal ">
				<?php
				if (!empty($this->extraFieldslabel))
				{
					foreach ($this->extraFieldslabel as $field_data)
					{
						if (!empty($field_data->attendee_value[$attendee_id]->field_value))
						{
							$data_present = 1;
							?>
							<div class="control-group">
								<label class="control-label">
									<?php echo JText::_($field_data->label);?>
								</label>
								<div class="controls">
									<?php echo $field_data->attendee_value[$attendee_id]->field_value;?>
								</div>
							</div>
							<?php
						}
					}
				}

				if (!empty($this->customerNote))
				{
					?>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('COM_JTICKETING_USER_COMMENT'); ?>
						</label>
						<div class="controls">
							<?php echo $this->customerNote; ?>
						</div>
					</div>
					<?php
				}

				if ($data_present == 0)
				{
					echo JText::_('COM_JTICKETING_NO_DATA_PRESENT_ATTENDEE');;
				}
				?>
			</div>
		</div>
		<!--ROW_FUILD ENDS-->

<?php if (JVERSION < '3.0'): ?>
	</div>
	<!--BOOTSTRAP ENDS-->
<?php endif; ?>

