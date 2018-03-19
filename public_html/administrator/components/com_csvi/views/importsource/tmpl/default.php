<?php
/**
 * @package     CSVI
 * @subpackage  View
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

?>
<div class="row-fluid">
	<div class="span2">
		<?php
		$layout = new JLayoutFile('csvi.import.steps');
		echo $layout->render(array('step' => $this->step));
		?>
	</div>
	<div class="span10">
		<table class="table table-striped">
			<caption><?php echo $this->template->getName(); ?></caption>
			<thead></thead>
			<tfoot></tfoot>
			<tbody>
			<tr>
				<td><?php echo JText::_('COM_CSVI_JFORM_ACTION_LABEL'); ?></td>
				<td><?php echo JText::_('COM_CSVI_' . $this->template->get('action')); ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_CSVI_JFORM_COMPONENT_LABEL') ?></td>
				<td><?php echo JText::_('COM_CSVI_' . $this->template->get('component')); ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_CSVI_JFORM_OPERATION_LABEL'); ?></td>
				<td><?php echo JText::_('COM_CSVI_' . $this->template->get('component') . '_' . $this->template->get('operation')); ?></td>
			</tr>
			</tbody>
		</table>
		<form action="index.php?option=com_csvi&view=importsource" id="adminForm" name="adminForm" method="post" class="form-horizontal" enctype="multipart/form-data">
			<?php
			switch ($this->template->get('source', 'fromupload'))
			{
				case 'fromupload':
					echo JText::_('COM_CSVI_IMPORT_UPLOAD_FILE_LABEL');
					?>
					<input type="file" name="import_file" id="import_file" />
					<?php
					break;
				case 'fromserver':
					echo JText::_('COM_CSVI_IMPORT_FROM_SERVER_LABEL');
					echo $this->template->get('local_csv_file');
					break;
				case 'fromurl':
					echo JText::_('COM_CSVI_IMPORT_FROM_URL_LABEL');
					echo $this->template->get('urlfile');
					break;
				case 'fromftp':
					echo JText::_('COM_CSVI_IMPORT_FROM_FTP_LABEL');
					echo $this->template->get('ftphost');
					break;
				case 'fromtextfield':
					?>
					<label for id="textfieldcontent"><?php echo JText::_('COM_CSVI_IMPORT_FROM_TEXTFIELD_DESC'); ?></label>
					<textarea name="textfieldcontent" id="textfieldcontent" class="input-xxlarge"
						rows="10" title="<?php echo JText::_('COM_CSVI_IMPORT_FROM_TEXTFIELD_DESC'); ?>"></textarea>
					<?php
					break;
				case 'fromdatabase':
					echo JText::sprintf('COM_CSVI_IMPORT_FROM_DATABASE_NAME_LABEL', $this->template->get('database_name'));
					break;
			}

			?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="runId" value="<?php echo $this->input->getInt('runId'); ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
	</div>
</div>
