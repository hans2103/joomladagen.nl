<div class="control-group" style="display:none">
	<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
</div>

<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
</div>

<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('catid'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('catid'); ?></div>
</div>
<div class="control-group">
	<?php
	$canState = false;
	$canState = $canState = JFactory::getUser()->authorise('core.edit.state','com_jticketing');

	if(!$canState): ?>
		<div class="control-label">
			<?php echo $this->form->getLabel('state'); ?>
		</div>
		<?php
		$state_string = JText::_('COM_JTICKETING_UNPUBLISH');
		$state_value = 0;

		if ($this->item->state == 1):
			$state_string = JText::_('COM_JTICKETING_PUBLISH');
			$state_value = 1;
		endif;
		?>
		<div class="controls"><?php echo $state_string; ?></div>
		<input type="hidden" name="jform[state]" value="<?php echo $state_value; ?>" />
		<?php
	else: ?>
		<div class="control-label">
			<?php echo $this->form->getLabel('state'); ?>
		</div>
		<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
		<?php
	endif; ?>
</div>
<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('allow_view_attendee'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('allow_view_attendee'); ?></div>
</div>

<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('image');?></div>
	<div class="controls">
		<?php echo $this->form->getInput('image'); ?>
		<p class="alert alert-info">
			<?php echo JText::sprintf('COM_JTICKETING_MAIN_IMAGE_SIZE', $this->params->get('large_width'), $this->params->get('large_height')); ?>
		</p>
	</div>
</div>
<div class="control-group">
	<div class="controls ">
<?php
	$mediaId = '';
	$hideDiv = 'hide_jtdiv';
	$this->eventImage = '#';

	if (isset($this->item->image->id))
	{
		$hideDiv = '';
		$this->eventImage = $this->item->image->{$this->eventMainImage};
		$mediaId = $this->item->image->id;
	}
?>
		<div class="row-fluid">
			<ul class="thumbnails <?php echo $hideDiv;?>">
			  <li class="event_media span3">
				<input type="hidden" name="jform[image][new_image]" id="jform_event_image" 
				value="<?php echo $mediaId;?>" />
				<input type="hidden" name="jform[image][old_image]" id="jform_event_old_image" 
				value="" />
				<div class="thumbnail">
				  <img src="<?php echo $this->eventImage ?>"
					class="event_img_width" id="uploaded_media">
				</div>
			  </li>
			</ul>
		</div>
	</div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('long_description'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('long_description'); ?></div>
</div>
