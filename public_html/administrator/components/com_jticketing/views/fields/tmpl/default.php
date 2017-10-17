<?php
/**
 * @version     1.0.0
 * @package     com_tjfields
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      TechJoomla <extensions@techjoomla.com> - http://www.techjoomla.com
 */
// no direct access
defined('_JEXEC') or die;
?>
<?php

if(JVERSION>=3.0):

	if(!empty( $this->sidebar)): ?>
	<div id="sidebar">
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>

	</div>

		<div id="j-main-container" class="span10">

	<?php else : ?>
		<div id="j-main-container">
	<?php endif;
endif;
?>
<div class="techjoomla-bootstrap">
	<a class="btn btn-primary" href="<?php echo JUri::base().'index.php?option=com_tjfields&view=groups&client=com_jticketing.event' ?>"><?php	echo JText::_('COM_JTICKETING_EVENT_GROUP'); ?></a>
	<a class="btn btn-primary" href="<?php echo JUri::base().'index.php?option=com_tjfields&view=fields&client=com_jticketing.event' ?>"><?php	echo JText::_('COM_JTICKETING_EVENT_FIELD'); ?></a>
	<a class="btn btn-primary" href="<?php echo JUri::base().'index.php?option=com_tjfields&view=groups&client=com_jticketing.ticket' ?>"><?php	echo JText::_('COM_JTICKETING_TICKET_GROUP'); ?></a>
	<a class="btn btn-primary" href="<?php echo JUri::base().'index.php?option=com_tjfields&view=fields&client=com_jticketing.ticket' ?>"><?php	echo JText::_('COM_JTICKETING_TICKET_FIELD'); ?></a>
</div><!--techjoomla ends-->
