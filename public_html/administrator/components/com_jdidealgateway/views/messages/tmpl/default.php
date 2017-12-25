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
<form name="adminForm" id="adminForm" method="post" action="index.php?option=com_jdidealgateway&view=messages">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<table class="table table-striped">
			<thead>
				<tr>
					<th><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_SUBJECT'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_STATUS_LABEL'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_MESSAGE_PSP_LABEL'); ?></th>
					<th><?php echo JText::_('JFIELD_LANGUAGE_LABEL'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="5"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					foreach ($this->items as $i => $item)
					{
					?>
						<tr>
							<td>
								<?php echo JHtml::_('grid.checkedout',  $item, $i, 'id'); ?>
							</td>
							<td>
								<?php
									echo JHtml::_(
										'link',
										JRoute::_('index.php?option=com_jdidealgateway&task=message.edit&id=' . $item->id),
										$item->subject
									);
								?>
							</td>
							<td> <?php echo JText::_('COM_JDIDEALGATEWAY_STATUS_' . $item->orderstatus); ?> </td>
							<td><?php echo $item->name; ?></td>
							<td><?php echo $item->language; ?></td>
						</tr>
				<?php
					}
				?>
			</tbody>
		</table>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
