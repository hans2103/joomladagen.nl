<?php
/**
 * @package     CSVI
 * @subpackage  About
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;
?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<table class="table table-condensed table-striped">
		<thead>
			<tr>
				<th width="650"><?php echo JText::_('COM_CSVI_FOLDER'); ?></th>
				<th><?php echo JText::_('COM_CSVI_FOLDER_STATUS'); ?></th>
				<th><?php echo JText::_('COM_CSVI_FOLDER_OPTIONS'); ?></th>
			</tr>
		</thead>
		<tfoot>
		</tfoot>
		<tbody>
			<?php
			$i = 1;
				foreach ($this->folders as $name => $access) { ?>
			<tr>
				<td><?php echo $name; ?></td>
				<td><?php if ($access) {
					echo '<span class="writable">'.JText::_('COM_CSVI_WRITABLE').'</span>';
				} else { echo '<span class="not_writable">'.JText::_('COM_CSVI_NOT_WRITABLE').'</span>';
	} ?>

				<td><?php if (!$access) { ?>
					<form action="index.php?option=com_csvi&view=about">
						<input type="button" class="button"
							onclick="Csvi.createFolder('<?php echo $name; ?>', 'createfolder<?php echo $i; ?>'); return false;"
							name="createfolder"
							value="<?php echo JText::_('COM_CSVI_FOLDER_CREATE'); ?>" />
					</form>
					<div id="createfolder<?php echo $i;?>"></div> <?php } ?>
				</td>
			</tr>
			<?php $i++;
				} ?>
		</tbody>
	</table>
	<div class="clr"></div>
	<table class="adminlist table table-condensed table-striped">
		<thead>
			<tr>
				<th><?php echo JText::_('COM_CSVI_ABOUT_SETTING'); ?></th>
				<th><?php echo JText::_('COM_CSVI_ABOUT_VALUE'); ?></th>
			</tr>
		</thead>
		<tfoot></tfoot>
		<tbody>
			<tr>
				<td style="width: 25%"><?php echo JText::_('COM_CSVI_ABOUT_DISPLAY_ERRORS'); ?></td>
				<td><?php echo (ini_get('display_errors')) ? JText::_('COM_CSVI_YES') : JText::_('COM_CSVI_NO'); ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_CSVI_ABOUT_PHP'); ?></td>
				<td><?php echo PHP_VERSION; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_CSVI_ABOUT_JOOMLA'); ?></td>
				<td><?php echo JVERSION; ?></td>
			</tr>
		</tbody>
	</table>
	<form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_csvi&view=about', false); ?>" method="post">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
	</form>

	<h2>What is CSV Improved?</h2>
	CSV Improved enables you can import and export data into and from different Joomla! components and even other databases. Every component features its own import and export types to allow control over every part of the component. Using a system that works based on a set of pre-defined fields you can match your fields with the CSV Improved fields to ensure a correct import. This way you can import all kinds of files from different kinds of sources, for example from your supplier. Using the same set of pre-defined fields you can set your own export fields to export for an accounting package, order list, order picking list and many other uses.<br />
	<br />
	The following components are supported by CSV Improved:<br />
	<ul>
		<li>Joomla! Categories</li>
		<li>Joomla! Contacts</li>
		<li>Joomla! Content
			<ul>
				<li>K2 Styleware Google maps</li>
			</ul>
		</li>
		<li>Joomla! Categories
			<ul>
				<li>K2 Styleware Google maps</li>
			</ul>
		</li>
		<li>Joomla! Custom Fields</li>
		<li>Joomla! Menus</li>
		<li>Joomla! Modules</li>
		<li>Joomla! Users</li>
		<li>J2Store</li>
		<li>VirtueMart
			<ul>
				<li>Custom Filters</li>
				<li>Product Builder</li>
				<li>Fastseller</li>
				<li>Custom Fields For All</li>
				<li>Stockable Custom Fields</li>
				<li>Related articles custom field</li>
				<li>Product Filter by Custom Fields</li>
			</ul>
		</li>
		<li>HikaShop
			<ul>
				<li>HikaShop CSVI Export</li>
			</ul>
		</li>
		<li>Form2Content</li>
		<li>K2
			<ul>
				<li>K2 Styleware Google maps</li>
			</ul>
		</li>
		<li>AWO Coupon</li>
		<li>RSForm! Pro</li>
	</ul>
	<h2>Support</h2>
	We provide support via <a href="https://csvimproved.com/forum">our forum</a> where we can assist you with any enquiries you may have.<br /><br />
	Read the <a href="https://csvimproved.com/support/getting-started" target="_blank">Getting started</a> document first and make sure you checked all the steps.<br />
	<br />
	Don't tell us it does not work but describe in detail what is happening and add the following information:<br />
	<ul>
		<li>Version of the extension you are using</li>
		<li>CSVI version [version]</li>
		<li>Sample of the file being imported</li>
		<li>A copy of the template you are using. Use the <a href="index.php?option=com_csvi&view=maintenance">Maintenance menu</a> to create a backup of your template.</li>
		<li>Collected debug information</li>
	</ul>
	<br />
	Without this information you won't get a useful answer. If no debug log is attached, you will get a request to post it.<br />
	<br />
	Where to get the debug information?<br />
	<br />
	The tutorial <a href="https://csvimproved.com/support/questions-and-answers/390-how-to-collect-debug-information">How to collect debug information</a> has been written to help you on how collect your debug information.<br />
	<br />
	An active subscription is required to be able to post on the forum.<br />
</div>
