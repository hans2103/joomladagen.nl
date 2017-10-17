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

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$loggeduser = JFactory::getUser();
$saveOrder  = $listOrder === 'events.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_conference&task=events.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'slotsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_conference&view=slots'); ?>" method="post" id="adminForm" name="adminForm" class="form-horizontal">
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
        <table class="adminlist table table-striped" id="slotsList">
            <thead>
            <tr>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                </th>
                <th width="20">
				    <?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_STATUS', 'slots.enabled', $listDirn, $listOrder); ?>
                </th>
                <th>
				    <?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_DAY', 'days.title', $listDirn, $listOrder) ?>
                </th>
                <th>
				    <?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_EVENT', 'events.title', $listDirn, $listOrder) ?>
                </th>
                <th class="center" width="12%">
				    <?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_TIME_START', 'slots.start_time', $listDirn, $listOrder) ?>
                </th>
                <th class="center" width="12%">
				    <?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_TIME_END', 'slots.end_time', $listDirn, $listOrder) ?>
                </th>
                <th class="center" width="12%">
				    <?php echo JText::_('COM_CONFERENCE_FIELD_GENERAL') ?>
                </th>
                <th class="center" width="12%">
				    <?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS') ?>
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
			    <?php foreach ($this->items as $i => $item) : ?>
				    <?php
				    $canEdit         = $this->canDo->get('core.edit');
				    $canChange       = $loggeduser->authorise('core.edit.state', 'com_conference');
				    $checkedOut      = ($item->locked_by != 0);
				    $ordering        = $listOrder == 'ordering';
				    ?>
                    <tr>
                        <td class="center">
		                    <?php if ($canEdit || $canChange) : ?>
			                    <?php echo JHtml::_('grid.id', $i, $item->conference_slot_id, $checkedOut); ?>
		                    <?php endif; ?>
                        </td>
                        <td class="center">
		                    <?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'slots.', $canChange); ?>
                        </td>
                        <td>
                            <div class="name break-word">
			                    <?php if ($canEdit)
			                    {
				                    echo JHtml::_(
					                    'link',
					                    JRoute::_('index.php?option=com_conference&task=slot.edit&conference_slot_id=' . (int) $item->conference_slot_id),
					                    $this->escape($item->day),
					                    'title="' . JText::sprintf('COM_CONFERENCE_EDIT_EVENT', $this->escape($item->day)) . '"'
				                    );
			                    }
			                    else
			                    {
				                    echo $this->escape($item->day);
			                    }
			                    ?>
                            </div>
                        </td>
                        <td>
		                    <?php if ($canEdit)
		                    {
			                    if ($item->conference_event_id)
			                    {
				                    $url = JRoute::_('index.php?option=com_conference&task=event.edit&conference_event_id=' . $item->conference_event_id);
				                    echo JHtml::_('link', $url, $item->event);
			                    }
		                    }
		                    else
		                    {
			                    echo $item->event;
		                    }
		                    ?>
                        </td>
                        <td class="center">
						    <?php echo JHtml::_('date', $item->start_time, 'H:i'); ?>
                        </td>
                        <td class="center">
						    <?php echo JHtml::_('date', $item->end_time, 'H:i'); ?>
                        </td>
                        <td class="center">
						    <?php if ($item->general): ?>General<?php endif; ?>
                        </td>
                        <td class="center">
                            <?php
			                    $url = JRoute::_('index.php?option=com_conference&view=sessions&filter[conference_slot_id]=' . $item->conference_slot_id);
			                    echo JHtml::_('link', $url, $item->sessions . ' ' . JText::_('COM_CONFERENCE_TABLE_SESSIONS'));
		                    ?>
                        </td>
                    </tr>
			    <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <input type="hidden" name="task" id="task" value=""/>
    <input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
    <input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>
</form>