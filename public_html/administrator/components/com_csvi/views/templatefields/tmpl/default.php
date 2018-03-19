<?php
/**
 * Templatefields list page
 *
 * @author 		RolandD Cyber Produksi
 * @link 		https://csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: default.php 1924 2012-03-02 11:32:38Z RolandD $
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

$loggeduser = JFactory::getUser();
$saveOrder = $listOrder === 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_csvi&task=templatefields.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'templatefieldsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>
<form action="<?php echo JRoute::_('index.php?option=com_csvi&view=templatefields'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (!$this->items) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="templatefieldsList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_FIELD_NAME', 'a.field_name', $listDirn, $listOrder); ?>
						</th>
						<?php
							if ($this->action === 'export')
							{
								?>
								<?php if (count((array) $this->customTable) > 1)
								{
									?>
								<th>
									<?php echo $this->customTable !== '' ? JText::_('COM_CSVI_TABLE_NAME_LABEL') : ''; ?>
								</th>
									<?php
								}
								?>
								<th>
									<?php echo JText::_('COM_CSVI_COLUMN_HEADER_LABEL'); ?>
								</th>
								<?php
							}

							if ($this->action === 'import')
							{
								?>
								<th>
									<?php echo $this->source === 'fromdatabase' ? JText::_('COM_CSVI_SOURCE_FIELD_LABEL') : JText::_('COM_CSVI_XML_NODE_LABEL'); ?>
								</th>
								<?php
							}
						?>
						<th>
							<?php echo JText::_('COM_CSVI_DEFAULT_VALUE_LABEL'); ?>
						</th>
						<?php if ($this->action === 'export') : ?>
							<th>
								<?php echo JText::_('COM_CSVI_COLUMN_PUBLISHED_LABEL'); ?>
							</th>
						<?php endif; ?>
					</tr>
				<thead>
				<tfoot>
					<tr>
						<td colspan="20">
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
				<?php
					$canEdit   = $this->canDo->get('core.edit');
					$canChange = $loggeduser->authorise('core.edit.state', 'com_csvi');

					foreach ($this->items as $i => $item)
					{
					?>
						<tr class="sortable-group-id="<?php echo $item->csvi_template_id; ?>">
							<td class="order nowrap center hidden-phone">
								<?php
								$iconClass = '';

								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (!$saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler <?php echo $iconClass ?>">
									<span class="icon-menu"></span>
								</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5"
									       value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
								<?php endif; ?>
							</td>
							<td>
								<?php if ($canEdit || $canChange) : ?>
									<?php echo JHtml::_('grid.id', $i, $item->csvi_templatefield_id); ?>
								<?php endif; ?>
							</td>
							<td>
								<div class="name break-word">
									<?php if ($canEdit)
									{
										echo JHtml::_(
											'link',
											JRoute::_('index.php?option=com_csvi&task=templatefield.edit&csvi_templatefield_id=' . (int) $item->csvi_templatefield_id),
											$this->escape($item->field_name),
											'title="' . JText::sprintf('COM_CSVI_EDIT_TEMPLATE', $this->escape($item->field_name)) . '"'
										);
									}
									else
									{
										echo $this->escape($item->template_name);
									}

									if ($item->rules)
									{
										echo '<span class="icon-wand"></span>';
									}
									?>
								</div>
							</td>
						<?php if (count((array) $this->customTable) > 1)
						{
							?>
							<td>
								<?php echo $item->table_name !== '' ?  $item->table_name : ''; ?>
							</td>
							<?php
						}
						?>
							<?php
								if ($this->action === 'export')
								{
									?>
									<td><?php echo $item->column_header; ?></td>
									<?php
								}
								?>

							<?php
								if ($this->action === 'import')
								{
									?>
									<td><?php echo $this->source === 'fromdatabase' ? $item->source_field : $item->xml_node; ?></td>
									<?php
								}
								?>
							<td>
								<?php echo $item->default_value; ?>
							</td>
							<?php
							if ($this->action === 'export')
							{
								?>
								<td>
									<?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'templatefields.', $canChange); ?>
								</td>
							<?php
							}
							?>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<input type="hidden" name="task" value="browse" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<!-- The Quick Add form -->
<div class="modal hide fade" id="availablefieldsModal" style="width: 30%; left: 70%;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_CSVI_QUICK_ADD_FIELDS'); ?></h3>
	</div>
	<div class="modal-body modal-batch" style="overflow-y: scroll; max-height: 400px;">
		<div class="form-horizontal">
			<div class="filter-search btn-group pull-left">
				<input type="search" class="search-table-filter" id="search_fields" data-table="order-table" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>">
			</div>
			<div class="btn-group pull-left">
				<button class="btn clear-table-filter" data-table="order-table">
					<i class="icon-remove clear-table-filter" data-table="order-table"></i>
				</button>
			</div>
			<div class="checkbox pull-left">
				<input type="checkbox" name="idfields" id="idfields" class="idcheck-table-filter" data-table="order-table"/>
				<?php echo JText::_('COM_CSVI_SHOW_IDFIELDS'); ?>
			</div>
		</div>

		<table class="order-table table table-striped table-hover" id="search_table">
			<tbody>
			<?php
				$key_array = array();

				foreach ($this->availableFields as $fieldname)
				{
					// Only when field is not in key array so to remove duplicate
					if (!in_array($fieldname->csvi_name, $key_array, true))
					{
						$key_array[] = $fieldname->csvi_name;

						// By default all the id fields are hidden
						if (strpos($fieldname->csvi_name, 'id') !== false)
						{
							?>
							<tr style="display: none;">
							<?php
						}
						else
						{
							?>
							<tr>
						<?php
						}
							?>
							<td>
								<input type="checkbox" name="quickfields" class="addfield" value="<?php echo $fieldname->csvi_name; ?>" />
							</td>
							<td class="addfield">
								<?php echo $fieldname->csvi_name; ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary show-button" style="display: none;" type="button" onclick="selectFields('uncheck');">
			<?php echo JText::_('COM_CSVI_UNCHECK_ALL_FIELDS'); ?>
		</button>
		<button class="btn btn-primary show-button" style="display: none;" type="submit" onclick="addFields();">
			<?php echo JText::_('COM_CSVI_ADD_FIELDS'); ?>
		</button>
		<button class="btn" type="button" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
	</div>
</div>

<?php
	$layout = new JLayoutFile('csvi.modal');
	echo $layout->render(array('ok-btn-dismiss' => true));
?>

<script type="text/javascript">
Joomla.submitbutton = function(task)
{
	if (task == 'quickadd')
	{
		jQuery('#availablefieldsModal').modal('show');
	}
	else
	{
		Joomla.submitform(task);
	}
};


// Selects a field in the quick add list when user clicks on the name only
jQuery(".addfield").click(function()
{
	if (jQuery(this).is(':radio'))
	{
		var selectbox = jQuery(this);
	}
	else
	{
		var selectbox = jQuery(this).parent().children('td').children('input');

		if (jQuery(selectbox).attr('checked'))
		{
			jQuery(selectbox).attr('checked', false);
		}
		else
		{
			jQuery(selectbox).attr('checked', true);
			jQuery('.show-button').show();
		}
	}

	// Check if there are any checked boxes left
	var checked = 0;

	jQuery("input[name='quickfields'][type='checkbox']").each(function()
	{
		if (jQuery(this).is(':checked'))
		{
			checked = checked + 1;
		}
	});

	if (checked == 0)
	{
		jQuery('.show-button').hide();
	}
});

function selectFields(task)
{
	jQuery("input[name='quickfields'][type='checkbox']").each(function()
	{
		if (task == 'check')
		{
			jQuery(this).attr('checked', 'true');
		}
		else if (task == 'uncheck')
		{
			jQuery(this).removeAttr('checked');

			// Check if there are any checked boxes left
			var checked = 0;

			jQuery("input[name='quickfields'][type='checkbox']").each(function()
			{
				if (jQuery(this).is(':checked'))
				{
					checked = checked + 1;
				}
			});

			if (checked == 0)
			{
				jQuery('.show-button').hide();
			}
		}
	});
}

function addFields()
{
	var fieldnames = [];
	jQuery("input[name='quickfields'][type='checkbox']").each(function()
	{
		if (jQuery(this).is(':checked'))
		{
			fieldnames.push(jQuery(this).val());
		}
	});
	// Send the data to the database
	var template_id = <?php echo $this->template->getId(); ?>;

	if (template_id > 0)
	{
		jQuery.ajax({
			async: false,
			type: 'post',
			url: 'index.php',
			dataType: 'json',
			data: 'option=com_csvi&task=templatefield.storetemplatefield&format=json&template_id='+template_id+'&field_name='+fieldnames.join('~'),
			success: function(data)
			{
				if (data.success)
				{
					window.location = "index.php?option=com_csvi&view=templatefields&template_id=<?php echo $this->getModel()->getState('template_id'); ?>";
				}
				else
				{
					jQuery('#availablefieldsModal').modal('hide');

					showMsg(
						'<?php echo JText::_('COM_CSVI_ERROR'); ?>',
						'<span class="error">' + data.message + '</span>',
						'<?php echo JText::_('COM_CSVI_CLOSE_DIALOG'); ?>'
					);
				}
			},
			error:function (request, status, error)
			{
				jQuery('#availablefieldsModal').modal('hide');

				showMsg(
					'<?php echo JText::_('COM_CSVI_ERROR'); ?>',
					'<span class="error">' + jQuery.trim(request.responseText).substring(0, 2500) + '</span>',
					'<?php echo JText::_('COM_CSVI_CLOSE_DIALOG'); ?>'
				);
			}
		});
	}
	jQuery('#availablefieldsModal').modal('hide');
}

// Source: http://codepen.io/chriscoyier/pen/tIuBL
(function(document) {
	'use strict';

	var LightTableFilter = (function(Arr) {

		var _input;

		var _click;

		var _check;

		function _onInputEvent(e) {
			_input = e.target;
			var tables = document.getElementsByClassName(_input.getAttribute('data-table'));
			Arr.forEach.call(tables, function(table) {
				Arr.forEach.call(table.tBodies, function(tbody) {
					Arr.forEach.call(tbody.rows, _filter);
				});
			});
		}

		function _onClickEvent(e) {
			_click = e.target;
			var tables = document.getElementsByClassName(_click.getAttribute('data-table'));
			Arr.forEach.call(tables, function(table) {
				Arr.forEach.call(table.tBodies, function(tbody) {
					Arr.forEach.call(tbody.rows, _filterClear);
				});
			});

			return false;
		}

		function _onCheckEvent(e) {
			_check = e.target;
			var tables = document.getElementsByClassName(_check.getAttribute('data-table'));
			Arr.forEach.call(tables, function(table) {
				Arr.forEach.call(table.tBodies, function(tbody) {
					Arr.forEach.call(tbody.rows, _showIdRows);
				});
			});

			return false;
		}

		function _filter(row) {
			var text = row.textContent.toLowerCase(), val = _input.value.toLowerCase();
			row.style.display = text.indexOf(val) === -1 ? 'none' : 'table-row';
		}

		function _filterClear(row) {
			document.getElementById('search_fields').value = '';
			row.style.display = 'table-row';
		}

		function _showIdRows(row) {
			if(jQuery('#idfields').is(":checked"))
			{
				row.style.display = 'table-row';
			}
			else
			{
				_check.value = 'id';
				var text = row.textContent.toLowerCase(), val = _check.value.toLowerCase();
				row.style.display = text.indexOf(val) === -1 ? 'table-row' : 'none';
			}
		}

		return {
			init: function() {
				var inputs = document.getElementsByClassName('search-table-filter');
				var clicks = document.getElementsByClassName('clear-table-filter');
				var checks = document.getElementsByClassName('idcheck-table-filter');
				Arr.forEach.call(inputs, function(input) {
					input.oninput = _onInputEvent;
				});
				Arr.forEach.call(clicks, function(click) {
					click.onclick  = _onClickEvent;
				});
				Arr.forEach.call(checks, function(check) {
					check.onchange = _onCheckEvent;
				});
			}
		};
	})(Array.prototype);

	document.addEventListener('readystatechange', function() {
		if (document.readyState === 'complete') {
			LightTableFilter.init();
		}
	});

})(document);
 </script>
