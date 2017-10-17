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

$document =	JFactory::getDocument();
$document->addStylesheet(JUri::root(true) . '/media/com_jlike/font-awesome/css/font-awesome.css');
$input  = JFactory::getApplication()->input;
$type   = $input->get('type', 'reco', 'STRING');
$assignto   = $input->get('assignto', '', 'STRING');
$user = JFactory::getUser();
$element = $input->get('element','','STRING');
$element_id   = $input->get('id', '', 'INT');

// Include helper file to get todoid and contentid
$path = JPATH_SITE . '/components/com_jlike/helper.php';
$ComjlikeHelper = "";

	if (JFile::exists($path))
	{
		if (!class_exists('ComjlikeHelper'))
		{
			JLoader::register('ComjlikeHelper', $path);
			JLoader::load('ComjlikeHelper');
		}

			$ComjlikeHelper = new ComjlikeHelper;
	}

	// Load jlike model to call api function for assigndetails and other
		$path = JPATH_SITE . '/components/com_jlike/models/recommendations.php';
		$this->JlikeModelRecommendations = "";

	if (JFile::exists($path))
	{
		if (!class_exists('JlikeModelRecommendations'))
		{
			JLoader::register('JlikeModelRecommendations', $path);
			JLoader::load('JlikeModelRecommendations');
		}

		$this->JlikeModelRecommendations = new JlikeModelRecommendations;
	}

$content_id      = $ComjlikeHelper->getContentId($element_id, $element);

if (!empty($content_id) && !empty($user->id))
{
	$this->JlikeModelRecommendations->setState("content_id", $content_id);
	$this->JlikeModelRecommendations->setState("assigned_by", $user->id);
	$this->JlikeModelRecommendations->setState("assigned_to", $user->id);
	$todos = $this->JlikeModelRecommendations->getItems();
}

$date   = JFactory::getDate()->Format(JText::_('COM_JLIKE_DATE_FORMAT'));

?>
<div class="techjoomla-bootstrap setgoal">
	<div class="jlike-wrapper">
		<?php if ($type == 'assign' && $assignto == 'self'): ?>
		<div class="modal-header">
			<h3>
				<?php echo JText::sprintf("COM_JLIKE_FORM_TITLE_SET_GOAL", $this->element['title']); ?>
			</h3>
		</div>
		<form  method="post" name="adminForm" id="adminForm">
			<div class="modal-body">
			<div class="container">

						<div class="span11 jlike-uber-padding pull-right" >
							<div class="span5">
							<?php
							$start_date = '';

							if (!empty($todos[0]->start_date))
							{
								$start_date = $todos[0]->start_date;
							}
							else
							{
								$start_date = JHtml::date('now', 'D d M Y H:i');
							}

								echo JHtml::_('calendar',$start_date,'start_date','start_date','%Y-%m-%d', 'placeholder="' . JText::_("COM_JLIKE_START_DATE") . '" class="required"');
							?>
							</div>

							<div class="span5">
							<?php
							$due_date = '';

							if (!empty($todos[0]->due_date))
							{
								$due_date = $todos[0]->due_date;
							}

							echo JHtml::_('calendar',$due_date,'due_date','due_date','%Y-%m-%d', 'placeholder="' . JText::_("COM_JLIKE_DUE_DATE") . '" class="required"');

							 ?>
							 </div>
						</div>
				</div>
			</div>
			</div>

			 <div class="modal-footer">
				<button
					onclick="closePopUp()"
					class="btn btn-small">
					<i class="fa fa-times"></i>
					<?php echo JText::_('COM_JLIKE_CANCEL_BUTTON'); ?>
				</button>
				<button
					onclick="return recommendation('assignRecommendUsers')"
					name="recommend_friends_send"
					class="btn btn-small btn-primary"
					id="enrol">
						<i class="fa fa-check-square"></i>
						<?php echo JText::_('COM_JLIKE_SETGOAL_LABEL'); ?>
				</button>
				<?php endif; ?>
			</div>
			<input type="hidden" id="recommend_task" name="task" value="assignRecommendUsers" />
			<input type="hidden"  name="option" value="com_jlike" />
			<input type="hidden" name="element" value="<?php echo $input->get('element','','STRING'); ?>" />
			<input type="hidden" name="element_id" value="<?php echo $input->get('id','','INT'); ?>" />
			<input type="hidden" name="plg_name" value="<?php echo $input->get('plg_name','','STRING'); ?>" />
			<input type="hidden" name="plg_type" value="<?php echo $input->get('plg_type','','STRING'); ?>" />
			<input type="hidden" id="task_type" name="type" value="<?php echo $type; ?>" />
			<input type="hidden" id="task_sub_type" name="sub_type" value="<?php echo $assignto; ?>" />
			<input type="hidden" id="todo_id" name="todo_id" value="<?php if(!empty($todos[0]->id)) echo $todos[0]->id; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
		</div>
	</div>
</div>

<script>
	jQuery(window).load(function() {
		jQuery(".input-append").addClass("jlike-calender-div");
});
</script>
<?php

$document = JFactory::getDocument();
$document->addScript(JUri::root(true).'/components/com_jlike/assets/scripts/recommendation.js');
