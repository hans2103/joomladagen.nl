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
$app = JFactory::getApplication();
$document =JFactory::getDocument();
?>
<?php if(JVERSION<3.0){ ?>
<div class="techjoomla-bootstrap" >
<?php } ?>

<form method="POST" name="adminForm" action="" id="adminForm">
<?php if(!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<table border="0" width="100%" cellspacing="10" class="adminlist">
		<?php

		?>
		<tr>
			<td align="left" valign="top"><?php
			//Code to Read  File
			$valtoinsert='';
			    $fileneme = JPATH_ROOT.'/'."components".'/'."com_jlike".'/'."classification.ini";
			    $data = file_get_contents($fileneme);
			   //End Code to Read CSS File
				$classifications = parse_ini_file($fileneme);
				foreach($classifications as $key=>$val)
				{
					$valtoinsert.=$key."=".$val."<br/>";
				}
				///$editor      =JFactory::getEditor();
			//echo $editor->display("data[classifiactionlist]",stripslashes($valtoinsert),500,500,40,20,true);
			?>
			<textarea name="data[classifiactionlist]" rows="25" style="width:90%"><?php echo $data; ?></textarea>
			</td>
			<td width="40%" valign="top">
				<div class="alert alert-info">
						<?php echo  JText::_('COM_JLIKE_LIKE_ELEMENT_DESCRIPTION');?>
				</div>
			</td>

	</table>



	<input type="hidden" name="option" value="com_jlike" />
	<input	type="hidden" name="task" value="save" />
	<input type="hidden"	name="controller" value="element_config" />
	<input type="hidden"	name="view" value="element_config" />
	<?php echo JHTML::_( 'form.token' ); ?>
	</div>
</form>
<?php if(JVERSION<3.0){ ?>
</div>
<?php } ?>

