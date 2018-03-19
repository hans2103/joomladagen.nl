<?php
/**
 * @package     CSVI
 * @subpackage  Maps
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

$loggeduser = JFactory::getUser();
$canEdit   = $this->canDo->get('core.edit');
$canChange = $loggeduser->authorise('core.edit.state', 'com_csvi');
?>
<form action="<?php echo JRoute::_('index.php?option=com_csvi&view=maps'); ?>"
	method="post" name="adminForm" id="adminForm" class="form-horizontal">
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<?php
	// Search tools bar
	echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
			<table class="table table-striped" id="itemsList">
				<thead>
				<tr>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_MAP_TEMPLATE_NAME_LABEL', 'title', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JText::_('COM_CSVI_MAP_OPTIONS'); ?>
					</th>
					</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="15">
						<div class="pull-left">
							<?php
							if ($this->pagination->total > 0)
							{
								echo $this->pagination->getListFooter();
							}
							?>
						</div>
						<div class="pull-right"><?php echo $this->pagination->getResultsCounter(); ?></div>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) : ?>
					<tr>
						<td class="center">
							<?php if ($canEdit || $canChange) : ?>
								<?php echo JHtml::_('grid.id', $i, $item->csvi_map_id); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php
								echo JHtml::_('link', 'index.php?option=com_csvi&view=map&layout=edit&csvi_map_id=' . $item->csvi_map_id, $item->title);
							?>
						</td>
						<td>
							<?php echo JHtml::_('link', 'index.php?option=com_csvi&view=maps',
								JText::_('COM_CSVI_MAP_CREATE_TEMPLATE'), 'onclick="showForm(' . $item->csvi_map_id . '); return false;"'); ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
	<?php endif; ?>
</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="mapid" id="mapid" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php

	$template_name = '<div class="control-group">
						<label class="control-label" for="template_name">
						' . JText::_('COM_CSVI_MAP_TEMPLATE_NAME_LABEL') . '
						</label>
						<div class="controls">
							<input type="text" id="template_name" name="template_name" value="" />
						</div>
					</div>';

	$layout = new JLayoutFile('csvi.modal');
	echo $layout->render(
		array(
			'modal-id' => 'mapsModal',
			'modal-header' => JText::_('COM_CSVI_MAP_TEMPLATE_NAME_LABEL'),
			'modal-body' => $template_name,
			'cancel-button' => true,
			'ok-button' => JText::_('COM_CSVI_MAP_CREATE_TEMPLATE')
		));

	echo $layout->render(
		array(
			'modal-id' => 'createdModal',
			'modal-header' => JText::_('COM_CSVI_INFORMATION'),
			'modal-body' => JText::_('COM_CSVI_TEMPLATE_CREATED'),
			'ok-btn-dismiss' => true,
			'ok-button' => JText::_('COM_CSVI_CLOSE_DIALOG')
		));

	echo $layout->render(
		array(
			'modal-id' => 'notcreatedModal',
			'modal-header' => JText::_('COM_CSVI_INFORMATION'),
			'modal-body' => JText::_('COM_CSVI_TEMPLATE_NOT_CREATED'),
			'ok-btn-dismiss' => true,
			'ok-button' => JText::_('COM_CSVI_CLOSE_DIALOG')
		));
?>
<script type="text/javascript">
	function showForm(mapid)
	{
		jQuery('#mapid').val(mapid);
		jQuery('#mapsModal').modal('show');
		jQuery('#template_name').focus();
	}

	jQuery('#mapsModal .ok-btn').on('click', function(e) {
		e.preventDefault();
		jQuery('#mapsModal').modal('hide');
		var mapid = jQuery('#mapid').val();

		if (mapid > 0)
		{
			jQuery.ajax({
				async: false,
				url: 'index.php',
				dataType: 'json',
				type: 'get',
				data: 'option=com_csvi&task=maps.createtemplate&format=json&id='+mapid+'&templateName='+jQuery('#template_name').val(),
				success: function(data)
				{
					jQuery('#template_name').val('');

					if (data)
					{
						jQuery('#createdModal').modal('show');
					}
					else
					{
						jQuery('notcreatedModal').modal('show');
					}

				},
				error: function(data, status, statusText)
				{
					showMsg(
						'<?php echo JText::_('COM_CSVI_ERROR'); ?>',
						statusText + '<br /><br />' + data.responseText,
						'<?php echo JText::_('COM_CSVI_CLOSE_DIALOG'); ?>'
					);
				}
			});
		}
		else
		{
			showMsg(
				'<?php echo JText::_('COM_CSVI_INFORMATION'); ?>',
				'<?php echo JText::_('COM_CSVI_NO_MAP_ID_FOUND'); ?>',
				'<?php echo JText::_('COM_CSVI_CLOSE_DIALOG'); ?>'
			);
		}
	});
</script>
