<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2016 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');
?>
<h3 id="transform-resize" data-action="resize">
<a href="#">
<?php echo WFText::_('WF_MANAGER_TRANSFORM_RESIZE', 'Resize');?>
</a>
</h3>
<div class="uk-form">

	<div class="uk-grid uk-grid-small">
			<label for="resize_width" class="uk-width-2-10">
				<?php echo WFText::_('WF_LABEL_WIDTH');?>
			</label>
			<input type="text" id="resize_width" value="" class="uk-width-2-10" />

			<label class="uk-text-bold">&times;</label>

      <label for="resize_height" class="uk-width-2-10">
        <?php echo WFText::_('WF_LABEL_HEIGHT');?>
      </label>

			<input type="text" id="resize_height" value="" class="uk-width-2-10" />

			<input type="checkbox" id="resize_constrain" checked />
	</div>

	<div class="uk-grid uk-grid-small">
		<label for="resize_presets" class="uk-width-1-5">
			<?php echo WFText::_('WF_LABEL_PRESETS');?>
		</label>
		<div class="uk-width-4-5">
			<select id="resize_presets" class="uk-width-1-1">
				<option value="1:1"><?php echo WFText::_('WF_MANAGER_TRANSFORM_PRESET_ORIGINAL');?></option>
				<?php foreach ($this->lists['resize'] as $option):?>
					<option value="<?php echo $option;?>"><?php echo $option;?></option>
				<?php endforeach;?>
			</select>
		</div>
	</div>
	<div class="uk-form-row uk-align-right uk-margin-top uk-margin-bottom">
		<button id="resize_apply" class="uk-button apply" data-function="resize">
			<?php echo WFText::_('WF_LABEL_APPLY');?>
		</button>
		<button id="resize_reset" class="uk-button reset" data-function="resize">
			<?php echo WFText::_('WF_LABEL_RESET');?>
		</button>
	</div>
</div>
