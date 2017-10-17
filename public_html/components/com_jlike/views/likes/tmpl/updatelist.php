<?php
/**
 * @package    Jlike
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

?>
<script type="text/javascript">
	var root_url="<?php echo JUri::root(); ?>";

/** This function adds in zone
 */

function jlike_updateMyLikeLables()
{
	var fromdata = jLike.jQuery('#jlikeUpdateLablesForm').serialize();

	jLike.jQuery.ajax({
		url: root_url+'index.php?option=com_jlike&controller=likes&task=updateMyLikeLables&ajaxCall=1',
		type: 'POST',
		//async:false,
		data:fromdata,
		dataType: 'json',
		beforeSend: function()
		{
			jLike.jQuery('#Jlike_processImg').show();
		},
		complete: function()
		{
			jLike.jQuery('#Jlike_processImg').hide();
		},
		success: function(msg)
		{
			if (msg.status)
			{
				window.parent.location.reload();
			}
			else
			{
				techjoomla.jQuery('.jlikeError').fadeIn();
			}
		},
		error: function(response)
		{
			jLike.jQuery('.jlikeError').show('slow');

		}
	});

}


</script>


<div class="techjoomla-bootstrap">
	<div class="form-horizontal">

		<form method="post" enctype="multipart/form-data" name="jlikeUpdateLablesForm" id="jlikeUpdateLablesForm" class="form-validate">
			<legend>
				<?php echo JText::_('COM_LIKE_UPDATE_LIST'); ?>
			</legend>

			<input type="hidden" name="content_id" id="" value="<?php echo $this->content_id ?>" />
			<?php
			if (!empty($this->allLables))
			{
				foreach($this->allLables as $lable)
				{
				?>
					<div class="row-fluid" >
						<label class="checkbox" class="">
							<input type="checkbox" class='label-check' value="<?php echo $lable->id;?>" name="labelList[]"<?php echo ($lable->checked === 1) ? 'checked' : '' ?> >
								<?php echo $lable->title;  ?>
						</label>
					</div>
				<?php
				}
				?>

				<button class="btn btn-success" type="button" title="<?php echo JText::_('COM_JLIKE_UPDATE')?>" onclick="jlike_updateMyLikeLables();"><?php echo JText::_('COM_JLIKE_UPDATE')?></button>
				<span id='Jlike_processImg' style="display:none;">
					<img class="" src="<?php echo JUri::root() ?>components/com_jlike/assets/images/ajax-loading.gif" height="15" width="15">
				</span>
				<?php
			}
			?>

			<div class="error alert alert-danger jlikeError" style="display: none;">
				<?php echo JText::_('COM_JLIKE_ERROR'); ?>
				<i class="icon-cancel pull-right" style="align: right;"
					onclick="jlike.jQuery(this).parent().fadeOut();"> </i> <br />
				<hr />
				<div id="JlikeErrorContentDiv">
					<?php echo JText::_('COM_JLIKE_MY_LIKE_ERROR_MSG')?>
				</div>
			</div>


		</form>
		<?php echo JHtml::_('form.token'); ?>

	</div>
</div>
