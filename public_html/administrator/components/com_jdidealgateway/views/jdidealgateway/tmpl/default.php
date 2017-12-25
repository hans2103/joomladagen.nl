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

?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<table class="table table-striped table-condensed">
		<thead>
		<tr>
			<th><?php echo JText::_('COM_JDIDEALGATEWAY_ORIGIN'); ?></th>
			<th><?php echo JText::_('COM_JDIDEALGATEWAY_ORDERID'); ?></th>
			<th><?php echo JText::_('COM_JDIDEALGATEWAY_ORDERNR'); ?></th>
			<th><?php echo JText::_('COM_JDIDEALGATEWAY_AMOUNT'); ?></th>
			<th><?php echo JText::_('COM_JDIDEALGATEWAY_ALIAS'); ?></th>
			<th><?php echo JText::_('COM_JDIDEALGATEWAY_CARD'); ?></th>
			<th><?php echo JText::_('COM_JDIDEALGATEWAY_RESULT'); ?></th>
			<th><?php echo JText::_('COM_JDIDEALGATEWAY_TRANSID'); ?></th>
			<th><?php echo JText::_('COM_JDIDEALGATEWAY_DATE'); ?></th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="10"></td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		foreach ($this->items as $i => $entry)
		{
			// Create the link
			$componentLink = '';
			$orderLink = '';

			if (array_key_exists($entry->origin, $this->classes))
			{
				$componentLink = $this->classes[$entry->origin]->getComponentLink();
				$orderLink = $this->classes[$entry->origin]->getAdminOrderLink($entry->order_id);
			}
			?>
			<tr>
				<td><?php echo 0 === count($componentLink) ? $entry->origin : JHtml::_('link', $componentLink, $entry->origin, 'target=_new'); ?></td>
				<td><?php echo 0 === count($orderLink) ? $entry->order_id : JHtml::_('link', $orderLink, $entry->order_id, 'target=_new'); ?></td>
				<td><?php echo $entry->order_number; ?></td>
				<td class="amount">&euro; <?php echo number_format($entry->amount, 2); ?></td>
				<td><?php echo $entry->alias; ?></td>
				<td><?php echo $entry->card; ?></td>
				<td><?php echo $entry->result; ?></td>
				<td><?php echo $entry->trans; ?></td>
				<td><?php echo JHtml::_('date', $entry->date_added, 'd-m-Y H:i:s', true); ?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>
