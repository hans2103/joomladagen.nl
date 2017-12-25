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
<form name="adminForm" id="adminForm" method="post" action="index.php?option=com_jdidealgateway&view=emails">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">

		<?php if ($this->canDo->get('core.create')) : ?>
			<?php echo JText::_('COM_JDIDEALGATEWAY_TESTMAIL_ADDRESS'); ?>
			<div id="testmail">
				<input type="text" name="email" value="" size="50" />
			</div>
		<?php endif; ?>

		<table class="table table-striped">
			<thead>
				<tr>
					<th><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_TRIGGER'); ?></th>
					<th><?php echo JText::_('COM_JDIDEALGATEWAY_SUBJECT'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"><?php echo $this->pagination->getListFooter(); ?></td>
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
										JRoute::_('index.php?option=com_jdidealgateway&task=email.edit&id=' . $item->id),
										JText::_('COM_JDIDEALGATEWAY_' . $item->trigger)
									);
								?>
							</td>
							<td><?php echo $item->subject; ?></td>
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
