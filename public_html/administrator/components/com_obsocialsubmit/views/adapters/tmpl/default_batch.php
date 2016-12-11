<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
global $isJ25;
if( $isJ25 ) { ?>

<?php 
} else {
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">x</button>
		<h3><?php echo JText::_('COM_OBSOCIALSUBMIT_BATCH_OPTIONS');?></h3>
	</div>
	<div class="modal-body">
		<p><?php echo JText::_('COM_OBSOCIALSUBMIT_BATCH_TIP'); ?></p>
		<div class="control-group">
			<div class="controls">
    			<label><?php echo JText::_(''); ?></label>
    			<?php echo JHtml::_('obsocialsubmit.instances','batch[connections_id][]','extern','','batch-connections-id'); ?>
	        </div>
		<?php echo JHtml::_('obsocialsubmit.batch_action'); ?>
    	</div>
    	<div class="modal-footer">
    		<button class="btn" type="button" onclick="alert(document.id('batch-connections-id').value='')" data-dismiss="modal">
    			<?php echo JText::_('JCANCEL'); ?>
    		</button>
    		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('adapter.batch');">
    			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
    		</button>
    	</div>
    </div>
</div>
<?php } ?>