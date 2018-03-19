<?php
/**
 * @package     CSVI
 * @subpackage  Log
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

// Check if there is a log
if (!isset($this->logdetails) || empty($this->logdetails))
{
	echo '<span class="error">' . JText::sprintf('COM_CSVI_NO_LOG_FOUND', $this->logfile) . '</span>';
}
else
{
	?>
	<table class="table table-condensed table-striped">
		<thead>
			<tr>
				<th colspan="2"><?php echo JText::_('COM_CSVI_DETAILS'); ?></th>
			</tr>
		</thead>
		<tfoot>
		</tfoot>
		<tbody>
			<tr>
				<td><?php echo JText::_('COM_CSVI_DATE'); ?></td>
				<td><?php echo $this->logdetails['date']; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_CSVI_SOFTWARE'); ?></td>
				<td><?php echo $this->logdetails['joomla']; ?></td>
			</tr>
		</tbody>
	</table>
	<table class="table table-condensed table-striped">
		<thead>
			<tr>
				<?php foreach ($this->logdetails['fields'] as $title) { ?>
					<th><?php echo JText::_('COM_CSVI_' . strtoupper(trim($title))); ?></th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr><td colspan="<?php echo count($this->logdetails['fields']); ?>"><?php echo sprintf(JText::_('COM_CSVI_LOG_LINES'), count($this->logdetails['entries'])); ?></td></tr>
		</tfoot>
		<tbody>
			<?php foreach ($this->logdetails['entries'] as $entry) { ?>
				<tr>
					<?php foreach ($entry as $value) { ?>
						<td><?php echo $value; ?></td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
<?php
}
