<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
?>
<div class="btn-toolbar">
	<div class="btn-group">
		<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('module.save');">
		<?php echo JText::_('JSAVE');?></button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn" onclick="window.parent.SqueezeBox.close();">
		<?php echo JText::_('JCANCEL');?></button>
	</div>
	<div class="clearfix"></div>
</div>

<?php
$this->setLayout('edit');
echo $this->loadTemplate();
