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

JHtml::_('behavior.modal');

// Add check script
JHtml::script('com_jdidealgateway/result.js', false, true);

JHtml::_('formbehavior.chosen', '#filter_origin', null, array('placeholder_text_single' => JText::_('COM_JDIDEALGATEWAY_SELECT_ORIGIN')));
JHtml::_('formbehavior.chosen', '#filter_psp', null, array('placeholder_text_single' => JText::_('COM_JDIDEALGATEWAY_SELECT_PSP')));
JHtml::_('formbehavior.chosen', '#filter_card', null, array('placeholder_text_single' => JText::_('COM_JDIDEALGATEWAY_SELECT_CARD')));
JHtml::_('formbehavior.chosen', '#filter_result', null, array('placeholder_text_single' => JText::_('COM_JDIDEALGATEWAY_SELECT_RESULT')));

// Supported payment providers for checking result
$noPaymentProviders = array('easy', 'internetkassa', 'lite', 'omnikassa', 'ogone');

?>
<form name="adminForm" id="adminForm" method="post" action="index.php?option=com_jdidealgateway&view=logs" class="form-horizontal">

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
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_ORIGIN'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_ORDERID'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_ORDERNR'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_AMOUNT'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_ALIAS'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_CARD'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_RESULT'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_TRANSID'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_DATE'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_HISTORY'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					foreach ($this->items as $i => $entry)
					{
						// Pseudo entry for satisfying Joomla
						$entry->checked_out = 0;
						$checked = JHtml::_('grid.checkedout',  $entry, $i, 'id');

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
							<td><?php echo $checked; ?></td>
							<td><?php echo 0 === count($componentLink) ? $entry->origin : JHtml::_('link', $componentLink, $entry->origin, 'target=_new'); ?></td>
							<td><?php echo 0 === count($orderLink) ? $entry->order_id : JHtml::_('link', $orderLink, $entry->order_id, 'target=_new'); ?></td>
							<td><?php echo $entry->order_number; ?></td>
							<td class="amount">&euro; <?php echo number_format($entry->amount, 2, ',', '.'); ?></td>
							<td><?php echo $entry->alias; ?></td>
							<td><?php echo $entry->card; ?></td>
							<td>
								<?php
									if ($entry->trans
										&& !in_array(strtolower($entry->psp), $noPaymentProviders, true)
										&& in_array(strtoupper($entry->result), array('OPEN', 'TRANSFER', ''), true)
									)
									{
										echo JHtml::_(
											'link',
											'index.php?option=com_jdidealgateway&view=logs',
											'<span class="icon-refresh"></span>',
											'onclick="checkResult(' . $entry->id . ',\'' . JSession::getFormToken() . '\'); return false;"'
										);
									}

									echo '<span id="paymentResult' . $entry->id . '">' . $entry->result . '</span>';
								?>
							</td>
							<td><?php echo $entry->trans; ?></td>
							<td>
								<?php
									echo JHtml::_('date', $entry->date_added, 'd-m-Y H:i:s', true);
								?>
							</td>
							<td>
								<?php
									$attribs = 'class="modal" onclick="" rel="{handler: \'iframe\', size: {x: 950, y: 500}}"';
									echo JHtml::_(
										'link',
										JRoute::_('index.php?option=com_jdidealgateway&task=logs.history&tmpl=component&log_id=' . $entry->id),
										JText::_('COM_JDIDEALGATEWAY_VIEW'),
										$attribs
									);
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
