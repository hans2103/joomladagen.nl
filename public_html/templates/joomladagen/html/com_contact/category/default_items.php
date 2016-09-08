<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<div class="well well-small">
<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_CONTACT_NO_ARTICLES'); ?>	 </p>
<?php else : ?>

		<table class="table table-striped">
			<thead>
				<tr>
					<th>Naam</th>
					<th>Positie</th>
					<th>E-mail</th>
				</tr>
			</thead>
			<tbody>
			
			<?php foreach ($this->items as $i => $item) : ?>
			<tr>
				<td>
					<a href="<?php echo JRoute::_(ContactHelperRoute::getContactRoute($item->slug, $item->catid)); ?>"><?php echo $item->name; ?></a>
				</td>
				<td>
					<?php echo $item->con_position; ?>
				</td>
				<td>
					<?php echo $item->email_to; ?>
				</td>
			</tr>
			<?php endforeach; ?>
			
		</table>

<?php endif; ?>

</div>