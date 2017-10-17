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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');

if (JVERSION >= '3.0')
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}
$document =	JFactory::getDocument();
$document->addStylesheet(JUri::root(true) . '/media/com_tjlms/font-awesome/css/font-awesome.css');
$input  = JFactory::getApplication()->input;
$date   = JFactory::getDate()->Format(JText::_('COM_JLIKE_DATE_FORMAT'));
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<div class="techjoomla-bootstrap recommend-popup-div native-jlike">
	<div class="jlike-wrapper">
		<div class="modal-header">
			<h3>
				<?php echo ($this->type == 'reco') ? JText::sprintf("COM_JLIKE_FORM_TITLE_RECOMMENDATIONS", $this->element['title']) : JText::sprintf("COM_JLIKE_FORM_TITLE_ASSIGN_CONTENT", $this->element['title']); ?>
			</h3>
		</div>
		<div class="modal-body">
			<?php
			$app = JFactory::getApplication();
			$messages = $app->getMessageQueue();
			if (!empty($messages))
			{
				$messageQueue = array('msgList');

				foreach ($messages as $msg){
					$messageQueue['msgList'][$msg['type']] = isset($messageQueue['msgList'][$msg['type']]) ? $messageQueue['msgList'][$msg['type']] : array();
					$messageQueue['msgList'][$msg['type']][] = $msg['message'];
				}
				echo JLayoutHelper::render('joomla.system.message', $messageQueue);
			}
			?>
			<form  method="post" name="adminForm" id="adminForm" class="recomment-popup-form">

				<?php if ($this->type == 'assign'): ?>
					<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'users')); ?>

						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'users', JText::_('COM_JLIKE_TITLE_USERS_ASSIGNMENT', true)); ?>
							<?php echo $this->loadTemplate('users'); ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>

						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'groups', JText::_('COM_JLIKE_TITLE_GROUPS_ASSIGNMENT', true)); ?>
							<?php echo $this->loadTemplate('groups'); ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>

					<?php echo JHtml::_('bootstrap.endTabSet'); ?>
				<?php else: ?>
					<?php echo $this->loadTemplate('users'); ?>
				<?php endif; ?>

				<input type="hidden" name="task" id="recommend_task" value="assignRecommendUsers" />
				<input type="hidden" name="option" value="com_jlike" />
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" name="element" value="<?php echo $input->get('element','','STRING'); ?>" />
				<input type="hidden" name="element_id" value="<?php echo $input->get('id','','INT'); ?>" />
				<input type="hidden" name="plg_name" value="<?php echo $input->get('plg_name','','STRING'); ?>" />
				<input type="hidden" name="plg_type" value="<?php echo $input->get('plg_type','','STRING'); ?>" />
				<input type="hidden" name="type" id="task_type" value="<?php echo $this->type; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
		<div class="modal-footer">
			<button
				onclick="closePopUp()"
				class="btn btn-small">
				<i class="fa fa-times"></i>
				<?php echo JText::_('COM_JLIKE_CANCEL_BUTTON'); ?>
			</button>
			<?php	if (!empty($this->peopleToRecommend)){ ?>
			<button
				onclick="return recommendation('assignRecommendUsers')"
				name="recommend_friends_send"
				class="btn btn-small btn-primary"
				id="enrolUsers">
					<i class="fa fa-pencil-square-o"></i>
					<?php echo ($this->type == 'reco') ? JText::_('COM_JLIKE_RECOMMEND_USERS') : JText::_('COM_JLIKE_ASSIGN_LABEL'); ?>
			</button>
			<?php } ?>
			<button
				onclick="return recommendation('assignRecommendGroups')"
				name="recommend_friends_send"
				class="btn btn-small btn-primary"
				id="enrolGroups">
					<i class="fa fa-pencil-square-o"></i>
					<?php echo ($this->type == 'reco') ? JText::_('COM_JLIKE_RECOMMEND_USERS') : JText::_('COM_JLIKE_ASSIGN_LABEL'); ?>
			</button>
		</div>
	</div>
</div>

<script>
jQuery(window).load(function() {
	jQuery(".input-append").addClass("jlike-calender-div");
	jQuery("html").addClass("recommend-popup");
});
</script>
<?php

$document = JFactory::getDocument();
$document->addScript(JUri::root(true).'/components/com_jlike/assets/scripts/recommendation.js');
