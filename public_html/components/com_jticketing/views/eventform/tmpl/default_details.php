<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
?>

<div class="form-group">
	<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label "><?php echo $this->form->getLabel('title');?></div>
	<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12"><?php echo $this->form->getInput('title'); ?></div>
</div>

<div class="form-group">
	<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label "><?php echo $this->form->getLabel('alias');?></div>
	<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12"><?php echo $this->form->getInput('alias'); ?></div>
</div>

<div class="form-group">
	<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label "><?php echo $this->form->getLabel('catid');?></div>
	<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12"><?php echo $this->form->getInput('catid'); ?></div>
</div>

<div class="form-group">
	<?php
		$canState = false;
		$canState = JFactory::getUser()->authorise('core.edit.own','com_jticketing');
	if($this->adminApproval == 0)
	{
		if(!$canState)
		{
		?>
			<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label ">
				<?php echo $this->form->getLabel('state'); ?>
			</div>
				<?php
				$stateString = JText::_('COM_JTICKETING_UNPUBLISH');
				$stateValue = 0;

				if ($this->item->state == 1):
					$stateString = JText::_('COM_JTICKETING_PUBLISH');
					$stateValue = 1;
				endif;
				?>
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12"><?php echo $stateString;?></div>
			<input type="hidden" name="jform[state]" value="<?php echo $stateValue;?>"/>
		<?php
		}
		else
		{
		?>
			<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label ">
				<?php echo $this->form->getLabel('state'); ?>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
				<?php
				$state = $this->form->getValue('state');
				$jtPublish = "checked='checked'";
				$jtUnpublish = "";

				if (empty($state))
				{
					$jtPublish = "";
					$jtUnpublish = "checked='checked'";
				}
				?>
				<label class="radio-inline">
					<input type="radio" class="" <?php echo $jtPublish;?> value="1" id="jform_state1" name="jform[state]" >
					<?php echo JText::_('COM_JTICKETING_YES');?>
				</label>
				<label class="radio-inline">
					<input type="radio" class="" <?php echo $jtUnpublish;?> value="0" id="jform_state0" name="jform[state]" >
					<?php echo JText::_('COM_JTICKETING_NO');?>
				</label>
			</div>
		<?php
		}
	}
	else
	{
	?>
		<input type="hidden" name="jform[state]" id="jform_state" value="0" />
	<?php
	}
	?>
</div>

<div class="form-group">
	<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label"><?php echo $this->form->getLabel('allow_view_attendee'); ?></div>
	<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">

		<?php
			$allowEventOrderToSee = intval($this->form->getValue('allow_view_attendee'));

			if ($allowEventOrderToSee == 0)
			{
				$jtAllowNo = " checked='checked' ";
				$jtAllowYes = "";
			}
			elseif ($allowEventOrderToSee == 1)
			{
				$jtAllowNo = "";
				$jtAllowYes = " checked='checked' ";
			}
		?>
		
		<label class="radio-inline">
			<input type="radio" value="1" name="jform[allow_view_attendee]" class="" <?php echo $jtAllowYes;?> >
			<?php echo JText::_('JYES');?>
		</label>
		<label class="radio-inline">
			<input type="radio" value="0" name="jform[allow_view_attendee]" class="" <?php echo $jtAllowNo;?> >
			<?php echo JText::_('JNO');?>
		</label>
	</div>
</div>

<div class="form-group">
	<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label "><?php echo $this->form->getLabel('access'); ?></div>
	<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12"><?php echo $this->form->getInput('access'); ?></div>
</div>

<div class="form-group">
	<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label"><?php echo $this->form->getLabel('image'); ?></div>
	<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12 controls">
		<?php echo $this->form->getInput('image');?>
		<div class="alert alert-info">
			<?php echo JText::sprintf('COM_JTICKETING_MAIN_IMAGE_SIZE', $this->params->get('large_width'), $this->params->get('large_height')); ?>
		</div>
	</div>
</div>

<div class="form-group">
	<div class="controls ">
<?php
	$mediaId = '';
	$hideDiv = 'hide_jtdiv';
	$this->eventImage = '#';

	if (isset($this->item->image->id))
	{
		$hideDiv = '';
		$this->eventImage = $this->item->image->media_m;
		$mediaId = $this->item->image->id;
	}
?>
		<div class="col-lg-10 col-md-10 col-sm-10 col-xs-12 pull-right controls">
			<ul class="thumbnails <?php echo $hideDiv;?>">
			  <li class="event_media col-sm-3">
				<input type="hidden" name="jform[image][new_image]" id="jform_event_image" value="<?php echo $mediaId;?>" />
				<input type="hidden" name="jform[image][old_image]" id="jform_event_old_image" value="" />
				<div class="thumbnail">
					<img src="<?php echo $this->eventImage ?>" class="event_img_width" id="uploaded_media">
				</div>
			  </li>
			</ul>
		</div>
	</div>
</div>

<div class="form-group">
	<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label "><?php echo $this->form->getLabel('long_description');?></div>
	<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12"><?php echo $this->form->getInput('long_description');?></div>
</div>

