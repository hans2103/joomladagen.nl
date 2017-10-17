<?php
/**
 * @version     1.5
 * @package     com_jticketing
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <extensions@techjoomla.com> - http://techjoomla.com
 */

// No direct access.
defined('_JEXEC') or die;
?>

<div class="table-responsive">
				<?php if (empty($this->ticketypes)) : ?>
				<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo JText::_('NODATA'); ?>
				</div>
			<?php
			else : ?>
				<table class="table table-striped" width="80%" id="eventList">
						<tr>
							<th  class="">
								<?php echo JText::_('COM_JTICKETING_TICKET_TYPE_ID'); ?>
							</th>
							<th  class="">
								<?php echo JText::_('JT_TICKET_TYPE_TITLE'); ?>
							</th>
							<th  class="">
								<?php echo JText::_('JT_TICKET_TYPE_DESC'); ?>
							</th>
							<th  class="">
								<?php echo JText::_('JT_TICKET_TYPE_PRICE'); ?>
							</th>
							<th  class="">
								<?php echo  JText::_('JT_TICKET_TYPE_AVAILABLE');?>
							</th>

						</tr>

					<tbody>
					<?php
					$i =0;
					foreach ($this->ticketypes as $i => $item) :

						?>

						<tr class="row<?php echo $i % 2; ?>">
							<td><?php  echo $item->id?></td>
							<td><?php  echo $item->title?></td>
							<td><?php  echo $item->desc?></td>
							<td><?php  echo $item->price?></td>
							<td><?php  if($item->unlimited_seats==1) echo JText::_('COM_JTICKETING_UNLIMITED_SEATS_YES'); else echo $item->count?></td>
						</tr>
					<?php
					$i++;
					endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
