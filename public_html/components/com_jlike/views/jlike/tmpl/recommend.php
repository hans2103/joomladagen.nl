<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jlike
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;
jimport( 'joomla.filesystem.folder' );

$document =JFactory::getDocument();
$document->addStyleSheet(JUri::root(true).'/media/com_tjlms/bootstrap3/css/bootstrap.min.css');
$document->addStyleSheet(JUri::root(true).'/media/techjoomla_strapper/css/bootstrap.j3.css');
$input = JFactory::getApplication()->input;
?>
<script type="text/javascript">

	techjoomla.jQuery(document).ready(function(){
		techjoomla.jQuery(".allUserAvaiable").click(function() {
				var li_id = techjoomla.jQuery(this).attr('id');

				var isChecked = techjoomla.jQuery('#'+li_id+' .contacts_check').is(":checked");

				if (isChecked == false)
				{
					techjoomla.jQuery('#'+li_id+' .thumbnail').css('border','1px solid orange');
					techjoomla.jQuery('#'+li_id+' .thumbnail').css('box-shadow','2px 2px 3px orange');
					techjoomla.jQuery('#'+li_id+' .contacts_check').prop('checked', true);
				}
				else
				{
					techjoomla.jQuery('#'+li_id+' .thumbnail').css('border','1px solid #ddd');
					techjoomla.jQuery('#'+li_id+' .thumbnail').css('box-shadow','none');
					techjoomla.jQuery('#'+li_id+' .contacts_check').prop('checked', false);
				}
			});

			var courseTitle = techjoomla.jQuery("<div/>").html(title).text();

			techjoomla.jQuery('#courseTitle').val(courseTitle);
	});



	function closerecommend()
	{
		window.parent.SqueezeBox.close();
	}

	function recommendation(likecontainerid,success_msg)
	{
		var task = techjoomla.jQuery('#recommend_task').val();

		if(techjoomla.jQuery('#recommendcoursecontainer input[type=checkbox]:checked').length)
		{
			Joomla.submitform('send_recommendation');
		}
		else
		{
			alert("<?php echo JText::_('COM_JLIKE_SELECT_USER_TO_RECOMMEND'); ?>");
			return false;
		}
	}

</script>

<?php

if($this->friendsToRecommend)
{
?>
	<form class="form-horizontal"  id="recommendcourse_form" name="recommendcourse_form"  method="post" enctype="multipart/form-data">
		<div id="recommendcoursecontainer">
			<div class="alert alert-info">
				<span><em><?php echo JText::_('COM_JLIKE_SELECT_FRIENDS_TO_RECOMMEND_COURSE'); ?></em></span>
			</div>
			<div class="help-block">
				<span>
					<em>
						<?php echo JText::_('COM_JLIKE_FRIENDS_HELPTEXT'); ?>
					</em>
				</span>
			</div>
			<hr class="hr hr-condensed">

			<div class="container">
				<div class="row-fluid">
					<div >
						<ul class="">
							<?php foreach( $this->friendsToRecommend as $friend ):	?>
									<?php if($this->oluser->id == $friend->id): ?>
										<?php continue; ?>
									<?php endif; ?>

									<?php if($this->oluser and $this->tjlmsparams->get('social_integration', '', 'STRING')=='jomsocial'): ?>
										<?php $onclick = "joms.invitation.selectMember('#recommend_friends-".$friend->id."');"; ?>
									<?php endif; ?>


									<li id="rocommenToUser<?php echo $friend->id; ?>" class="span3 clearfix allUserAvaiable">
									  <div class="thumbnail clearfix">
										<img src="<?php echo $friend->avatar;?>" alt="<?php echo $friend->name;?>" class="pull-left span5 clearfix" style='margin-right:10px'>
										<input type="checkbox" id="recommend_friends-<?php echo $friend->id; ?>" style="visibility:hidden" name="recommend_friends[]" value="<?php echo $friend->id?>" onclick="<?php if(!empty($onclick)) echo $onclick;?>"  class="thCheckbox contacts_check " />
										<div class="">
											<strong>
												<em><?php echo $friend->name;?></em>
											</strong>
										</div>
									  </div>
									</li>


							<?php endforeach; ?>
						</ul>
					</div>
				</div>

				<div class="clearfix"></div>

				<div class="form-actions recommendActionBtn">
					<button class="btn btn-primary" type="submit" name="recommend_friends_send" onclick="return recommendation('<?php echo $likecontainerid;?>','Recommended successfully');"><?php echo JText::_('COM_JLIKE_RECOMMEND_FRIENDS');?></button>
					<button class="btn btn-danger" name="recommend_friends_close" onclick="closerecommend();"><?php echo JText::_('COM_JLIKE_RECOMMEND_CLOSE');?></button>
				</div>

				<input type="hidden" id="recommend_task" name="task" value="send_recommendation" />
				<input type="hidden"  name="option" value="com_jlike" />
				<input type="hidden"  name="controller" value="" />
				<input type="hidden"  name="url" value="<?php echo $this->urldata->url;?>" />
				<input type="hidden"  name="element" value="<?php echo $this->urldata->element;?>" />
				<input type="hidden"  name="element_id" value="<?php echo $this->urldata->cont_id;?>" />
				<input type="hidden"  id="courseTitle" name="title" value=""/>
				<input type="hidden"   name="course_id" value="<?php echo $input->get('course_id', '', 'INT');?>"/>
			</div>
		</div>
	</form>

<?php
}
else
{
?>
	<div class="alert alert-error">
		<?php echo JText::_('COM_JLIKE_NO_FRIENDS');?>
	</div>
	<div class="help-block">
		<span>
			<em>
				<?php echo JText::_('COM_JLIKE_NO_FRIENDS_HELPTEXT'); ?>
			</em>
		</span>
	</div>
</div>

<?php
}


