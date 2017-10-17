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
$rawUrl = JUri::root() . "libraries/techjoomla/dompdf/tmp/" . $this->item->title . "" . $this->item->created_by . ".ics/";
$url = preg_replace('/\s+/', '', $rawUrl);
$googleEventUrl = JUri::root() . "index.php?option=com_jticketing&task=event.addGoogleEvent()&id=" . $this->item->id;
?>
<table class="table table-bordered">
	<thead>
		<tr>
			<th><?php echo JText::_('COM_JTICKETING_EVENT_ADD_TO_CALENDER');?></th>
		</tr>
	 </thead>
	<tbody>
		<tr>
			<td>
				<img  width="10%" src="<?php echo JUri::root();?>/media/com_jticketing/images/outlook.png">
					<a href="<?php echo $url; ?>" download=" <?php echo preg_replace('/\s+/', '', $this->item->title . '.ics') ?>">
						<?php echo JText::_('COM_JTICKETING_EVENT_OUTLOOK_CALENDER')?>
					</a>
			</td>
		</tr>
		<tr>
			<td>
				<img  width="10%" src="<?php echo JUri::root();?>/media/com_jticketing/images/google_calendar_logo.png">
				<a href="<?php echo $googleEventUrl ?>" target="_blank">
					<?php echo JText::_('COM_JTICKETING_EVENT_GOOGLE_CALENDER')?>
				</a>
				<input type="hidden" name="event" value="<?php echo $this->item->id; ?>"/>
			</td>
		</tr>
	  </tbody>
</table>
