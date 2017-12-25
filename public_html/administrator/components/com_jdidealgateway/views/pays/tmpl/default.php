<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

jimport('joomla.utilities.date');
JHtml::_('formbehavior.chosen');
?>
<form name="adminForm" id="adminForm" method="post" action="index.php?option=com_jdidealgateway&view=pays">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php
		// Render the search tools
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_ORDERID'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_USEREMAIL'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_AMOUNT'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_RESULT'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_REMARK'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_DATE_ADDED'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					foreach ($this->items as $i => $item)
					{
						// Pseudo entry for satisfying Joomla
						$item->checked_out = 0;
						$checked = JHtml::_('grid.checkedout',  $item, $i, 'id');

						?>
						<tr>
							<td><?php echo $checked; ?></td>
							<td><?php echo $item->id; ?></td>
							<td><?php echo $item->user_email; ?></td>
							<td class="amount">&euro; <?php echo number_format($item->amount, 2); ?></td>
							<td><?php echo JText::_('COM_JDIDEALGATEWAY_RESULT_' . $item->status); ?></td>
							<td><?php echo $item->remark; ?></td>
							<td>
								<?php
								$jnow = JFactory::getDate($item->cdate);
								echo $jnow->format('d-m-Y H:i:s');
								?>
							</td>
						</tr>
						<?php
					}
				?>
			</tbody>
		</table>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>
