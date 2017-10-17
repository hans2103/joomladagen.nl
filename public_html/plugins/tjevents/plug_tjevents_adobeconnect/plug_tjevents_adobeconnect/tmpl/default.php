<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal', 'a.modal');

if (!empty($vars->recording_url) && $vars->enddate < $this->currentTime)
{?>
	<div class="tj-adobeconnect">
		<a class="btn btn-info adobe-enter-btn" target="_blank" href="<?php echo trim($vars->recording_url);?>"><span class="editlinktip hasTip" title="<?php echo JText::_('PLG_TJEVENTS_ADOBECONNECT_ENTER_MEETINGS_DESC');?>" ><?php echo JText::_('PLG_TJEVENTS_ADOBECONNECT_VIEW_MEETINGS_RECORDINGS')?></span></a>
	</div>
<?php
}
elseif ($vars->enddate > $this->currentTime)
{?>
	<div class="tj-adobeconnect">
		<a class="btn btn-small btn-danger adobe-enter-btn" target="_blank" href="<?php echo trim($vars->meeting_url);?>"><span class="editlinktip hasTip" title="<?php echo JText::_('PLG_TJEVENTS_ADOBECONNECT_ENTER_MEETINGS_DESC');?>" ><?php echo JText::_('PLG_TJEVENTS_ADOBECONNECT_ENTER_MEETINGS')?></span></a>
	</div>
<?php
}
?>

<style>
.tj-adobeconnect{margin-bottom:5px;}
</style>
<script>
function openSqueezeBox(givenlink)
{
	var width = techjoomla.jQuery(parent.window).width();
	var height = techjoomla.jQuery(parent.window).height();

	var wwidth = width-(width*0.10);
	var hheight = height-(height*0.10);
	parent.SqueezeBox.open(givenlink, { handler: 'iframe', size: {x: wwidth, y: hheight},classWindow: 'tjlms-modal'});
}
</script>
