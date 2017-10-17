<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://joomladagen.nl
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

$loggeduser = JFactory::getUser();
$saveOrder = $listOrder === 'events.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_conference&task=events.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'eventsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_conference&view=events'); ?>" method="post" id="adminForm" name="adminForm" class="form-horizontal">
    <div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
		<?php else : ?>
            <table class="table table-striped" id="eventsList">
                <thead>
                <tr>
                    <th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'events.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                    </th>
                    <th width="20">
                        <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                    </th>
                    <th width="1%" class="nowrap center">
		                <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'events.enabled', $listDirn, $listOrder); ?>
                    </th>
                    <th class="title">
						<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_TITLE', 'events.title', $listDirn, $listOrder); ?>
                    </th>
                    <th class="title">
						<?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS'); ?>
                    </th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="20">
                        <div class="pull-left">
							<?php
							if ($this->pagination->total > 0)
							{
								echo $this->pagination->getListFooter();
							}
							?>
                        </div>
                        <div class="pull-right"><?php echo $this->pagination->getResultsCounter(); ?></div>
                    </td>
                </tr>
                </tfoot>
                <tbody>
				<?php
				$canEdit   = $this->canDo->get('core.edit');
				$canChange = $loggeduser->authorise('core.edit.state', 'com_conference');

				foreach ($this->items as $i => $item) :
					?>
                    <tr>
                        <td class="order nowrap center hidden-phone">
							<?php
							$iconClass = '';

							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
                            elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
							}
							?>
                            <span class="sortable-handler <?php echo $iconClass ?>">
									<span class="icon-menu"></span>
								</span>
							<?php if ($canChange && $saveOrder) : ?>
                                <input type="text" style="display:none" name="order[]" size="5"
                                       value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
							<?php endif; ?>
                        </td>
                        <td class="center">
							<?php if ($canEdit || $canChange) : ?>
								<?php echo JHtml::_('grid.id', $i, $item->conference_event_id); ?>
							<?php endif; ?>
                        </td>
                        <td class="center">
                            <?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'events.', $canChange); ?>
                        </td>
                        <td>
                            <div class="name break-word">
								<?php if ($canEdit)
								{
									echo JHtml::_(
										'link',
										JRoute::_('index.php?option=com_conference&task=event.edit&conference_event_id=' . (int) $item->conference_event_id),
										$this->escape($item->title),
										'title="' . JText::sprintf('COM_CONFERENCE_EDIT_EVENT', $this->escape($item->title)) . '"'
									);
								}
								else
								{
									echo $this->escape($item->title);
								}
								?>
                            </div>
                        </td>
                        <td>
							<?php echo $item->sessions; ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
		<?php endif; ?>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>
