<?php
/**
 * @version    SVN: <svn_id>
 * @package    JLike
 * @copyright  Copyright (C) 2005 - 2015. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;
$format = JText::_('COM_JLIKE_DATE_FORMAT_PER');
?>
<div class="container">
	<div class="row-fluid row">
		<div class="control-group span6">
			<div class="controls">
				<label class="checkbox" for="update_existing_users">
				<input class="update-existing-users" type="checkbox" name="update_existing_users" value="1" id="update_existing_users">
				<?php echo JText::_("COM_JLIKE_GROUP_ASSIGNMENT_EXISTING_UPDATE") ?>
				</label>
			</div>
		</div>
		<div class="control-group span6">
			<div class="controls">
			<?php
			$input = $this->filterForm->getInput('subuserfilter','list');
			$input = str_replace('list[subuserfilter]', 'onlysubuser', $input);//replace name
			$input = str_replace('list_subuserfilter', 'onlysubuser', $input);//replace id
			$input = str_replace('onchange="this.form.submit();"', '', $input);//remove onchange
			$input = str_replace('selected="selected"', '', $input);//remove selected option
			echo $input;
			?>
			</div>
		</div>
	</div>
	<div class="row-fluid row">
		<div class="span6 col-sm-6 jlike-uber-padding say-something-for-recomendation">
			<textarea name="group_sender_msg" rows="3" class="jlike-commentbox-height" placeholder="<?php echo JText::_("COM_JLIKE_SAY_SOMETHING");?>"></textarea>
		</div>
		<?php if ($this->type == 'assign'): ?>
			<div class="span6 col-sm-6 jlike-uber-padding pull-right" >
				<?php
				echo JHtml::_('calendar', '','group_start_date','group_start_date', $format, 'placeholder="' . JText::_("COM_JLIKE_START_DATE") . '" class="required"');

				echo JHtml::_('calendar', '','group_due_date','group_due_date', $format, 'placeholder="' . JText::_("COM_JLIKE_DUE_DATE") . '" class="required"');
				 ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="row-fluid row">
		<?php
		$groups = $this->element['groups'];
		foreach ($groups as $group)
		{
			?>
			<div class="control-group">
				<div class="controls">
					<label class="checkbox" for="group_<?php echo $group->id ?>">
					<input class="user_groups" type="checkbox" name="user_groups[]" value="<?php echo $group->id ?>" id="group_<?php echo $group->id ?>">
					<?php echo $group->title ?>
					</label>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>
