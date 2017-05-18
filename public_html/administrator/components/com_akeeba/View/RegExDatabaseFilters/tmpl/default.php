<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2017 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

/** @var \Akeeba\Backup\Admin\View\RegExDatabaseFilters\Html $this */

$ajaxUrl = addslashes(JUri::base().'index.php?option=com_akeeba&view=RegExDatabaseFilters&task=ajax');
$this->json = addcslashes($this->json, "'\\");
$js = <<< JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
akeeba.System.documentReady(function(){
    akeeba.System.params.AjaxURL = '$ajaxUrl';
	var data = JSON.parse('{$this->json}');
    akeeba.Regexdbfilters.render(data);
});

JS;

$this->getContainer()->template->addJSInline($js);

?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ErrorModal'); ?>

<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ProfileName'); ?>

<div class="well form-inline">
	<label><?php echo \JText::_('COM_AKEEBA_DBFILTER_LABEL_ROOTDIR'); ?></label>
	<span id="ak_roots_container_tab">
		<span><?php echo $this->root_select; ?></span>
	</span>
</div>

<div>
	<div id="ak_list_container">
		<table id="table-container" class="adminlist table table-striped">
			<thead>
				<tr>
					<td width="90px">&nbsp;</td>
					<td width="250px"><?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_TYPE'); ?></td>
					<td><?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_FILTERITEM'); ?></td>
				</tr>
			</thead>
			<tbody id="ak_list_contents" class="table-container">
			</tbody>
		</table>
	</div>
</div>