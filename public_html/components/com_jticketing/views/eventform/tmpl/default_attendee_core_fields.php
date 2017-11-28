<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
?>

<table class="table table-bordered">
	<thead>
		<tr>
			<th width="5%">
				<?php echo JText::_('COM_JTICKETING_ATTENDEE_TITLE'); ?>
			</th>
			<th width="5%">
				<?php echo JText::_('COM_JTICKETING_ATTENDEE_TYPE'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php
				foreach ($this->attendeeList as $i => $item)
				{
					?>
					<tr class="row<?php echo $i % 2; ?>">
							<td >
								<?php echo htmlspecialchars(JText::_($item->label), ENT_COMPAT, 'UTF-8'); ?>
							</td>
							<td >
								<?php echo $item->type; ?>
							</td>
						</tr>
				<?php
				}?>
	</tbody>
</table>
<div class="jticketing_params_container">
	<div>
		<?php echo $this->form->getInput('attendeefields');?>
	</div>
</div>
