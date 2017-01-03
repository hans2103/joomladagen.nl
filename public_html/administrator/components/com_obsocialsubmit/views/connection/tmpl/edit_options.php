<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */
 
defined('_JEXEC') or die;
ob_start();
$tab_navs = array();
$tab_index = 0;
foreach ($this->fieldsets as $name => $fieldset) :
		$hidden_fields = '';
		$active = '';
		$label      = !empty($fieldset->label) ? $fieldset->label : 'COM_OBSOCIALSUBMIT_'.$name.'_FIELDSET_LABEL';
		if($tab_index == 0) {
			$active = ' active';
			$tab_navs[] ='<li class="active"><a href="#'.$name.'" data-toggle="tab">'.JText::_($label).'</a></li>';
		} else {
			$tab_navs[] ='<li><a href="#'.$name.'" data-toggle="tab">'.JText::_($label).'</a></li>';
		}
		$tab_index++;
		if (isset($fieldset->description) && trim($fieldset->description)) {
			echo '<p class="tip">'.$this->escape(JText::_($fieldset->description)).'</p>';
		}
?>
		<div class="tab-pane<?php echo $active;?>" id="<?php echo $name;?>">
		<?php foreach ($this->form->getFieldset($name) as $field) : ?>
			<?php if (!$field->hidden) : ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php else :?>
				<?php $hidden_fields .= $field->input; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php echo $hidden_fields; ?>
	</div>
<?php 
endforeach;
$tab_contens = ob_get_contents();
ob_end_clean();
?>
<div id="help-content">
	<?php if ($this->item->xml) : ?>
		<?php if ($text = trim($this->item->xml->help)) : ?>
			<p><?php echo JText::_($text); ?></p>
		<?php endif; ?>
	<?php else : ?>
		<p class="alert alert-error"><?php echo JText::_('COM_OBSOCIALSUBMIT_ERR_XML'); ?></p>
	<?php endif; ?>
</div>
<ul class="nav nav-tabs">
    <?php echo implode('',$tab_navs);?></li>
</ul>
<div class="tab-content">
    <?php echo $tab_contens;?>
</div>