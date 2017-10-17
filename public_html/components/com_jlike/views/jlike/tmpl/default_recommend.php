<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jlike
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Jlike is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access.
defined('_JEXEC') or die;
jimport('joomla.filesystem.file');

?>
<script>
function openRecommendAssignPopup(recommendUrl, likecontainerid, height)
{
	var width = techjoomla.jQuery(window).width();
	if (!height){
		var height = techjoomla.jQuery(window).height();
	}

	SqueezeBox.open(recommendUrl, {
		handler: 'iframe',
		closable:true,
		size: {x: (width-(width*0.20)), y: (height-(height*0.20))},
		onClose: function()
		{
			window.parent.document.location.reload(true);
		},
		onUpdate: function()
		{
			//make box height smaller for small devices
			jQuery(this.content).parent('#sbox-window').css('max-height',height-(height*0.20));
			var boxHeight = jQuery(this.content).parent('#sbox-window').css('height');
			boxHeight = parseInt(boxHeight,10);
			jQuery(this.content).parent('#sbox-window').find('iframe').attr('height',boxHeight);
		}
	});
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jlike&view=recommend'); ?>"  class="form-horizontal"  id="recommendassigne_form" name="recommendassigne_form"  method="post" enctype="multipart/form-data">

		<?php if($this->urldata->showrecommendbtn == '1') { ?>
			<?php $recommend_link = JRoute::_( 'index.php?option=com_jlike&view=recommend&tmpl=component&id=' . $this->urldata->cont_id . '&plg_name=' . $this->urldata->plg_name . '&plg_type=' . $this->urldata->plg_type . '&element=' . $this->urldata->element . '&type=reco'); ?>

			<?php $onclick = "openRecommendAssignPopup('" . addslashes($recommend_link) . "', '" . $likecontainerid . "');"; ?>

				<a title="<?php echo JText::_('COM_JLIKE_RECOMMEND_USER_TOOLTIP'); ?>"
					class="btn recommend-btn btn-small btn-primary"
					onclick="<?php echo $onclick; ?>" >
						<?php echo JText::_('COM_JLIKE_RECOMMEND_LABEL') ?>
				</a>
		<?php } ?>

		<?php if($this->urldata->showassignbtn == '1') { ?>

		<?php $assign_link = JRoute::_( 'index.php?option=com_jlike&view=recommend&tmpl=component&id=' . $this->urldata->cont_id . '&plg_name=' . $this->urldata->plg_name . '&plg_type=' . $this->urldata->plg_type . '&element=' . $this->urldata->element . '&type=assign'); ?>

		<?php $onclick = "openRecommendAssignPopup('" . addslashes($assign_link) . "', '" . $likecontainerid . "');"; ?>

			<a title="<?php echo JText::_('COM_JLIKE_ASSIGN_USER_TOOLTIP'); ?>"
				class="btn btn-small btn-primary assign-btn"
				onclick="<?php echo $onclick; ?>" >
					<?php echo JText::_('COM_JLIKE_ASSIGN_LABEL') ?>
			</a>

		<?php } ?>

		<?php if($this->urldata->showsetgoalbtn == '1') { ?>
		<?php
		 $assign_link = JRoute::_( 'index.php?option=com_jlike&view=recommend&layout=default_setgoal&tmpl=component&id=' . $this->urldata->cont_id . '&plg_name=' . $this->urldata->plg_name . '&plg_type=' . $this->urldata->plg_type . '&element=' . $this->urldata->element . '&type=assign&assignto=self'); ?>

		<?php $onclick = "openRecommendAssignPopup('" . addslashes($assign_link) . "', '" . $likecontainerid . "',400);"; ?>

		<?php
		if (empty($this->goaldetails) || $this->goaldetails->assigned_by == JFactory::getUser()->id)
		{	?>
			<a title="<?php echo JText::_('COM_JLIKE_SETGOAL_USER_TOOLTIP'); ?>"
				class="btn btn-large btn-block btn-success tjlms-btn-flat setgoal"
				onclick="<?php echo $onclick; ?>" >

					<?php
					if($this->goaldetails)
					{
						echo JText::_('COM_JLIKE_UPDATEGOAL_LABEL');
					}
					else
					{
						echo JText::_('COM_JLIKE_SETGOAL_LABEL');
					} ?>
			</a>
		<?php
		}
	} ?>

</form>
