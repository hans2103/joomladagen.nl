<?php
/**
 * @version     1.0.0
 * @package     com_hierarchy
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Parth Lawate <contact@techjoomla.com> - http://techjoomla.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
//~ JHtml::_('formbehavior.chosen', 'select');
JHTML::_('behavior.modal', 'a.modal');
JHtml::_('behavior.keepalive');

$user	= JFactory::getUser();
$userId	= $user->get('id');
?>
<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('#export-submit').on('click', function () {
			document.getElementById('task').value = 'catimpexp.csvexport';
			document.adminForm.submit();
			document.getElementById('task').value = '';
		});
	});
</script>

<?php
//Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar)) {
    $this->sidebar .= $this->extra_sidebar;
}
?>

<form action="<?php echo JUri::base(); ?>index.php?option=com_jticketing&view=catimpexp" id="adminForm" class="form-inline"  name="adminForm" method="post">
<?php if(!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

		<!--div id="filter-bar" class="btn-toolbar">
			<div class="btn-group pull-left">
				<button type="button" class="btn btn-success" id="export-submit"><i class="icon-download icon-white"></i> <?php echo JText::_('COM_JTICKETING_CSV_IMPORT_EXPORT_HELP_TEXT'); ?></button>
			</div>
		</div-->
		<div></div>
		<div class="bs-callout bs-callout-info" id="callout-xref-input-group">
		<p><?php echo JText::_('COM_JTICKETING_CSV_IMPORT_EXPORT_HELP_TEXT'); ?></p>
		<a href="<?php echo JUri::base().'index.php?option=com_categories&view=categories&extension=com_jticketing'; ?>"><?php echo JText::_('COM_JTICKETING_CATEGORY_LINK'); ?></a>
		</div>
		<div class="clearfix"> </div>
		<input type="hidden" id="task" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<div style="display:none">
	<div id="import_category">
		<form action="<?php echo JUri::base(); ?>index.php?option=com_jticketing&task=catimpexp.csvImport&tmpl=component&format=html" id="uploadForm" class="form-inline center"  name="uploadForm" method="post" enctype="multipart/form-data">
			<table>
				<tr>
					<div id="uploadform_cat">
						<fieldset id="upload-noflash" class="actions">
							<label for="upload-file" class="control-label"><?php echo JText::_('COMJTICKETING_UPLOADE_FILE'); ?></label>
							<input type="file" id="upload-file" name="csvfile" id="csvfile" />
							<button class="btn btn-primary" id="upload-submit">
								<i class="icon-upload icon-white"></i>
								<?php echo JText::_('COMJTICKETING_EVENT_IMPORT_CSV'); ?>
							</button>
							<hr class="hr hr-condensed">
							<div class="alert alert-warning" role="alert"><i class="icon-info"></i> 
									<?php
									$link = '<a href="' . JUri::root() . 'media/com_jticketing/samplecsv/categoryImport.csv' . '">' . JText::_("COM_JTICKETING_CSV_SAMPLE") . '</a>';
								echo JText::sprintf('COM_JTICKETING_CSVHELP_CATEGORY', $link);
								?>
							</div>
						</fieldset>
					</div>
				</tr>
			</table>
		</form>
	</div>
</div>
