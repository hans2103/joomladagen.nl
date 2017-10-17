<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
	defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.html.pane');
if (version_compare(phpversion(), '5.4', '>='))
{
require_once(JPATH_SITE."/components/com_jticketing/helpers/emogrifier.php");
}
else
{
require_once(JPATH_SITE."/components/com_jticketing/helpers/emogrifier_old.php");
}
include_once(JPATH_ADMINISTRATOR.DS."components".DS."com_jticketing".DS."email_template.php");
$app = JFactory::getApplication();
$document =JFactory::getDocument();
$document->addStyleSheet(JUri::base().'components/com_jticketing/assets/css/jticketing.css');
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

<form method="POST" name="adminForm" action="" id="adminForm">
<div  class="techjoomla-bootstrap">
	<table border="0" width="100%" cellspacing="10" class="adminlist">
		<tr>
			<td width="50%" align="left" valign="top"><?php

			$emorgdata=$emails_config['message_body'];

			//Code to Read CSS File
			if(!function_exists('mb_convert_encoding'))		// condition to check if mbstring is enabled
			{
			   // echo JText::_("MB_EXT");
			    //$emorgdata=$emails_config['message_body'];
			}
			else
			{
			    //$cssfile = JPATH_SITE.DS."components".DS."com_jticketing".DS."assets".DS."css".DS."email_template.css";
			    //$cssdata = file_get_contents($cssfile);
			    //End Code to Read CSS File

			   // $emogr=new Emogrifier($emails_config['message_body'],$cssdata);
			    //$emorgdata=$emogr->emogrify();
			}

			$emorgdata=$emails_config['message_body'];

			$editor      =JFactory::getEditor();
			echo $editor->display("data[message_body]",stripslashes($emorgdata),670,600,60,20,true);
			?>
			</td>
			<td width="50%" valign="top">
				<table>
<!--
					<tr>
						<td colspan="2"><div class="alert alert-info"><?php echo JText::_('EB_CSS_EDITOR_MSG') ?> <br/></div>
							<textarea name="data[template_css]" rows="10" cols="90"><?php if (isset($cssdata)) echo trim($cssdata); ?></textarea>
						</td>
					</tr>
-->
					<tr>
						<td colspan="2"><div class="alert alert-info"><?php echo JText::_('COM_JTICKETING_EB_TAGS_DESC') ?> <br/></div>
										</tr>

					<tr>
						<td width="30%"><b>&nbsp;&nbsp;[NAME] </b> </td>
						<td><?php echo JText::_('TAGS_NAME'); ?></td>

					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;[BOOKING_DATE]</b></td>
						<td><?php echo JText::_('TAGS_BOOKING_DATE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_IMAGE]</b></td>
						<td><?php echo JText::_('TAGS_EVENT_IMAGE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_NAME]</b></td>
						<td><?php echo JText::_('TAGS_EVENT_NAME'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_URL]</b></td>
						<td><?php echo JText::_('TAGS_EVENT_LINK'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[ST_DATE]</b> </td>
						<td><?php echo JText::_('TAGS_EVENT_START_DATE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EN_DATE]</b></td>
						<td><?php echo JText::_('TAGS_EVENT_END_DATE'); ?></td>

					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_LOCATION]</b> </td>
						<td><?php echo JText::_('TAGS_EVENT_LOCATION'); ?></td>
					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TICKET_ID]</b></td>
						<td><?php echo JText::_('TAGS_TICKET_ID'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TICKET_TYPE]</b></td>
						<td><?php echo JText::_('TAGS_TICKET_TYPE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TICKET_PRICE]</b> </td>
						<td><?php echo JText::_('TAGS_TICKET_PRICE'); ?></td>
					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TOTAL_PRICE]</b></b></td>
						<td><?php echo JText::_('TAGS_TOTAL_PRICE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_DESCRIPTION] </b></td>
						<td><?php echo JText::_('TAGS_EVENT_DESCRIPTION'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[QR_CODE]</b></td>
						<td><?php echo JText::_('TAGS_QC_CODE'); ?></td>
					</tr>



				</table>
			</td>
		</tr>
	</table>
	<?php
	if(JVERSION < "1.6.0"){
	    echo "<strong>".JText::_("EB_EDITOR_NOT_LOADING_NOTICE")."</strong>";
	}


	?>

	<input type="hidden" name="option" value="com_jticketing" />
	<input	type="hidden" name="task" value="save" />
	<input type="hidden"	name="controller" value="email_config" />
	<input type="hidden"	name="view" value="email_config" />
	<?php echo JHtml::_( 'form.token' ); ?>
</div>

</form>
</div>
