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
$db         = JFactory::getDbo();
?>

<form action="<?php echo JRoute::_('index.php?option=com_conference&view=sessions'); ?>" method="post" id="adminForm" name="adminForm" class="form-horizontal">
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
        <table class="table table-striped" id="sessionsList">
            <thead>
            <tr>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                </th>
                <th width="20">
					<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_STATUS', 'sessions.enabled', $listDirn, $listOrder); ?>
                </th>
                <th width="20">
					<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_SHOW_LIST', 'sessions.listview', $listDirn, $listOrder); ?>
                </th>
                <th>
					<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_TITLE', 'sessions.title', $listDirn, $listOrder); ?>
                    /
					<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_SPEAKER', 'sessions.conference_speaker_id', $listDirn, $listOrder); ?>
                </th>
                <th width="8%">
					<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_LEVEL', 'sessions.conference_level_id', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
					<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_ROOM', 'sessions.conference_room_id', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
					<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_SLOT', 'slots.start_time', $listDirn, $listOrder); ?>
                </th>
                <th>
					<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_EVENT', 'events.title', $listDirn, $listOrder); ?>
                </th>
                <th class="center" width="7%">
	                <?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_DESCRIPTION', 'sessions.description', $listDirn, $listOrder); ?>
                </th>
                <th class="center" width="7%">
	                <?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_SLIDES', 'sessions.slides', $listDirn, $listOrder); ?>
                </th>
                <th class="center" width="7%">
	                <?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_VIDEO', 'sessions.video', $listDirn, $listOrder); ?>
                </th>
                <th width="7%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_FIELD_MODIFIED_LABEL', 'sessions.modified_on', $listDirn, $listOrder); ?>
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
	                $item->published = $item->enabled;
                    ?>
                    <tr>
                        <td class="center">
		                    <?php if ($canEdit || $canChange) : ?>
			                    <?php echo JHtml::_('grid.id', $i, $item->conference_session_id, $checkedOut); ?>
		                    <?php endif; ?>
                        </td>
                        <td class="center">
							<?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'sessions.', $canChange); ?>
                        </td>
                        <td class="center">
							<?php if($item->listview): ?>
                                <span class="badge badge-success"><span class="icon-checkmark"></span></span>
							<?php else:?>
                                <span class="badge badge-important"><span class="icon-delete"></span></span>
							<?php endif;?>
                        </td>
                        <td align="left">
                            <div class="name break-word">
		                        <?php if ($canEdit)
		                        {
			                        echo JHtml::_(
				                        'link',
				                        JRoute::_('index.php?option=com_conference&task=session.edit&conference_session_id=' . (int) $item->conference_session_id),
				                        $this->escape($item->title),
				                        'title="' . JText::sprintf('COM_CONFERENCE_EDIT_SESSION', $this->escape($item->title)) . '"'
			                        );
		                        }
		                        else
		                        {
			                        echo $this->escape($item->title);
		                        }
		                        ?>
                            </div>
							<?php if ($item->speakers):?>
                                <?php echo JText::_('COM_CONFERENCE_FIELD_SPEAKER'); ?>:
								<?php $i = 0;?>
								<?php foreach($item->speakers as $key => $speaker) :?>
									<?php if($i > 0):?>,<?php endif;?>
                                    <a href="<?php echo JRoute::_('index.php?option=com_conference&task=speaker.edit&conference_speaker_id=' . $speaker->conference_speaker_id) ?>"><?php echo(trim($speaker->title));?></a>
									<?php $i++;?>
								<?php endforeach;?>
							<?php endif;?>
                        </td>
                        <td class="center">
							<?php if ($item->conference_level_id):?>
                                <a href="<?php echo JRoute::_('index.php?option=com_conference&task=level.editl&conference_level_id=' . $item->conference_level_id) ?>">
                                    <span class="label <?php echo $item->level_label ?>"><?php echo $this->escape($item->level) ?></span>
                                </a>
							<?php endif;?>
                        </td>
                        <td>
                            <a href="<?php echo JRoute::_('index.php?option=com_conference&task=room.edit&conference_room_id=' . $item->conference_room_id) ?>">
								<?php echo $this->escape($item->room)?>
                            </a>
                        </td>
                        <td>
							<?php if($item->day):?>
                                <a href="<?php echo JRoute::_('index.php?option=com_conference&task=day.edit&conference_day_id=' . $item->day_id) ?>">
									<?php echo $this->escape($item->day)?>
                                </a><br/>
                                <span aria-hidden="true" class="icon-clock"></span>
                                <a href="<?php echo JRoute::_('index.php?option=com_conference&task=slot.edit&conference_slot_id=' . $item->conference_slot_id) ?>">
									<?php echo JHtml::_('date', $item->start_time, 'H:i')?> - <?php echo JHtml::_('date', $item->end_time, 'H:i')?>
                                </a>
							<?php endif;?>
                        </td>
                        <td align="left">
                            <a href="<?php echo JRoute::_('index.php?option=com_conference&task=event.edit&conference_event_id=' . $item->conference_event_id) ?>" class="conferenceitem">
                                <strong><?php echo $this->escape($item->event) ?></strong>
                            </a>
                        </td>
                        <td class="center">
							<?php if ($item->description): ?>
                                <span class="badge badge-success"><span class="icon-checkmark"></span></span>
							<?php else:?>
                                <span class="badge badge-important"><span class="icon-delete"></span></span>
							<?php endif;?>
                        </td>
                        <td class="center">
							<?php if ($item->slides): ?>
                                <span class="badge badge-success"><span class="icon-checkmark"></span></span>
							<?php else:?>
                                <span class="badge badge-important"><span class="icon-delete"></span></span>
							<?php endif;?>
                        </td>
                        <td class="center">
							<?php if ($item->video): ?>
                                <span class="badge badge-success"><span class="icon-checkmark"></span></span>
							<?php else:?>
                                <span class="badge badge-important"><span class="icon-delete"></span></span>
							<?php endif;?>
                        </td>
                        <td class="nowrap">
							<?php if ($item->modified_on == $db->getNullDate()): ?>
                                &mdash;
							<?php else: ?>
								<?php echo JHtml::_('date',$item->modified_on, JText::_('DATE_FORMAT_LC4')); ?>
							<?php endif; ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
                </table>

		<?php endif; ?>

        <input type="hidden" name="task" id="task" value=""/>
        <input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
        <input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>
    </div>
</form>
