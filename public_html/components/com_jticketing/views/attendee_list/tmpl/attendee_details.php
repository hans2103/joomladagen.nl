<?php
	/**
	 * @version    SVN: <svn_id>
	 * @package    JTicketing
	 * @author     Techjoomla <extensions@techjoomla.com>
	 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
	 * @license    GNU General Public License version 2 or later.
	 */
	$input        = JFactory::getApplication()->input;
	$attendee_id  = $input->get('attendee_id', '', 'INT');
	$data_present = 0;
?>
		<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">

		<div class="page-header">
			<h3><?php	echo JText::_('COM_JTICKETING_ATTENDEE_DETAILS_HEADER'); ?></h3>
		</div>

		<div class="row well">
			<div class="form-horizontal col-lg-10 col-md-10 col-sm-9 col-xs-12 ">
				<?php

				if (!empty($this->extraFieldslabel))
				{
					foreach ($this->extraFieldslabel as $field_data)
					{
						if (!empty($field_data->attendee_value[$attendee_id]->field_value))
						{
							$data_present = 1;
						?>
							<div class="form-group">
								<label class=" col-lg-2 col-md-2 col-sm-3 col-xs-12  control-label">
									<?php	echo JText::_($field_data->label);?>
								</label>
								<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
									<?php	echo $field_data->attendee_value[$attendee_id]->field_value;?>
								</div>
							</div>
						<?php
						}
					}
				}

				if (!empty($this->customerNote))
				{
				?>
					<div class="form-group">
						<label class=" col-lg-2 col-md-2 col-sm-3 col-xs-12  control-label">
						<?php	echo JText::_('COM_JTICKETING_USER_COMMENT');?>
						</label>
						<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
							<?php	echo $this->customerNote;?>
						</div>
					</div>
				<?php
				}

				if ($data_present == 0)
				{
					echo JText::_('COM_JTICKETING_NO_DATA_PRESENT_ATTENDEE');
				}
				?>
			</div>
		</div>
		<!--ROW_FUILD ENDS-->

<?php
	if (JVERSION < '3.0')
	{
?>
	</div>
	<!--BOOTSTRAP ENDS-->
<?php
	}
