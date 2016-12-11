<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( '_JEXEC' ) or die;
global $isJ25;
JHtml::addIncludePath( JPATH_COMPONENT . '/helpers/html' );

$class = '';
if ( ! $isJ25 ) {
	JHtml::_( 'bootstrap.popover' );
	$class = 'hide';
}
$document = JFactory::getDocument();
$class_modal = $isJ25 ? '' : 'modal ' . $class . ' fade';
?>
<?php if ($isJ25): ?>
<div style="display:none;">
	<?php endif; ?>
	<div class="<?php echo $class_modal; ?>" id="selectModalconnect">
		<div class="modal-header">
			<h2><?php echo JText::_( 'COM_OBSOCIALSUBMIT_CONNECTION_TYPE_CHOOSE' ) ?></h2>
		</div>
		<div class="modal-body">
			<?php echo JText::_("COM_OBSOCIALSUBMIT_THIS_FEATURE_ONLY_AVAILABLE_ON_MI_EDITION"); ?>
		</div>
	</div>
	<?php if ($isJ25): ?>
</div>
<?php endif; ?>
<div class="clr"></div>
