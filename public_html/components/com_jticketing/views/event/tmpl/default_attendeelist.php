<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
?>
<tr>
	<td data-title="<?php echo JText::_("COM_JTICKETING_ATTENDER_NAME"); ?>">
		<?php echo $this->eventAttendeeInfo->name ? ucfirst($this->eventAttendeeInfo->name) : JText::_('COM_JTICKETING_GUEST');?>
	</td>
	<td data-title="<?php echo JText::_("COM_JTICKEING_BOUGHTON"); ?>">
		<?php 
			$jdate = new JDate($this->eventAttendeeInfo->cdate);
			echo  str_replace('00:00:00', '', $jdate->Format('d-m-Y'));
		?>
	</td>
</tr>
