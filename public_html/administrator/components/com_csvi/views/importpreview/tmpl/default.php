<?php
/**
 * @package     CSVI
 * @subpackage  Imports
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

$this->step = 3;

// Load the extra help settings
$db = JFactory::getDbo();
$settings = new CsviHelperSettings($db);
$extraHelp = $settings->get('extraHelp', true);
?>
<div class="row-fluid">
	<div class="span2">
		<?php
			$layout = new JLayoutFile('csvi.import.steps');
			echo $layout->render(array('step' => $this->step));
		?>
	</div>
	<div class="span10">
		<?php if ($extraHelp) : ?>
			<div class="notice">
				<?php echo JText::_('COM_CSVI_IMPORT_PREVIEW_NOTICE'); ?>
			</div>
			<hr />
		<?php endif; ?>
		<?php
			echo JText::sprintf('COM_CSVI_IMPORTFILE_LINE_COUNT', $this->linecount);
		?>
		<form action="index.php?option=com_csvi&view=import" id="adminForm" name="adminForm" method="post" class="form-horizontal">
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="runId" value="<?php echo $this->input->getInt('runId', 0); ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
		<div id="preview">
			<?php
			if (empty($this->item[0]))
			{
				?><div class="error"><?php echo JText::_('COM_CSVI_NO_SUPPORTED_FIELDS_FOUND'); ?></div><?php
			}
			else
			{
				?>
				<table id="tablepreview" class="table table-striped table-condensed" style="empty-cells: show;">
					<thead>
					<tr>
						<?php
						foreach ($this->item[0] as $header)
						{
						?>
							<th>
								<?php echo $header; ?>
							</th>
						<?php
						}
						?>
					</tr>
					</thead>
					<tfoot></tfoot>
					<tbody>
					<?php
					foreach ($this->item as $key => $lines)
					{
						if ($key > 0)
						{
							?><tr><?php
							foreach ($lines as $fields)
							{
								foreach ($fields as $field)
								{
									?><td>
									<?php echo htmlentities($field->value, ENT_COMPAT, 'UTF-8'); ?>
									</td>
								<?php
								}
							}
							?></tr>
						<?php
						}
					}
					?>
					</tbody>
				</table>
			<?php
			}
			?>
		</div>
	</div>
</div>
