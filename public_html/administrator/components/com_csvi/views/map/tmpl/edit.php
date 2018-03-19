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

use Joomla\Utilities\ArrayHelper;

// Load some needed behaviors
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen');

// Convert object to array so it is easy to search
$availableFieldsArray = json_decode(json_encode($this->availableFields), true);
$availableFieldsValue = ArrayHelper::getColumn($availableFieldsArray, 'value');
$templateHeaderValue  = array();

if (isset($this->item->headers))
{
	$headersArray        = json_decode(json_encode($this->item->headers), true);
	$templateHeaderValue = ArrayHelper::getColumn($headersArray, 'templateheader');
}

$class = 'span12';

if ($this->extraHelp)
{
	$class = 'span11';
}
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_csvi&view=map&csvi_map_id=' . $this->item->csvi_map_id); ?>"
	method="post"
	name="adminForm"
	id="adminForm"
	class="form-validate form-horizontal"
	enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="<?php echo $class; ?>">
			<div class="span6">
				<?php echo $this->form; ?>
			</div>
			<div class="span6">
				<table id="fieldmap" class="table table-condensed table-striped">
					<thead>
						<tr><th><?php echo JText::_('COM_CSVI_FILEHEADER'); ?></th><th><?php echo JText::_('COM_CSVI_TEMPLATEHEADER')?></th></tr>
						<?php if ((int) $this->item->csvi_map_id === 0) :?>
							<tr>
								<th colspan="2" class="label label-important">
									<?php echo JText::_('COM_CSVI_SAVE_MAP_FIRST'); ?>
								</th>
							</tr>
						<?php endif;?>
					</thead>
					<tbody></tbody>
					<tbody>
						<?php if (isset($this->item->headers)) :

							// Render the select boxes
							foreach ($this->item->headers as $header) :

								$autoSelect = false;

								// If the field name matches, pre-select it in header fields
								if (in_array($header->csvheader, $availableFieldsValue, true) && !array_filter($templateHeaderValue))
								{
									$header->templateheader = $header->csvheader;
									$autoSelect = true;
								}
						?>
								<tr>
									<td>
										<?php echo $header->csvheader; ?>
									</td>
									<td>
										<?php
											// Create the ID field
											$id = str_replace(array('[', ']', ' ', '&', '/', '.'), '', $header->templateheader);

											echo JHtml::_(
											'select.genericlist',
											$this->availableFields,
											'templateheader[' . $header->csvheader . ']',
											'class="advancedSelect"',
											'value',
											'text',
											$header->templateheader,
											$id
										);
										?>
										<?php if ($autoSelect) : ?>
											<span class="label label-success"><?php echo JText::_('COM_CSVI_MAP_FIELDS_AUTO_SELECTED'); ?></span>
										<?php endif; ?>
									</td>
								</tr>
							<?php
								endforeach;
							?>
						<?php
							endif;
						?>
					</tbody>
				</table>
			</div>
		</div>
			<?php
			if ($this->extraHelp)
			{
				$layout = new JLayoutFile('csvi.help-arrow');
				echo $layout->render((object) array(new stdClass));
			}
			?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="csvi_map_id" value="<?php echo $this->item->csvi_map_id; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
<script type="text/javascript">
	jQuery(document).ready(function ()
	{
		// Turn off the help texts
		jQuery('.help-block').hide();
	});

	Joomla.submitbutton = function(task) {
		if (task == 'hidetips')
		{
			jQuery('.help-block').toggle();
			return false;
		}
		else {
			if (task == 'map.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
				Joomla.submitform(task, document.getElementById('adminForm'));
			} else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
			}
		}
	}
</script>
