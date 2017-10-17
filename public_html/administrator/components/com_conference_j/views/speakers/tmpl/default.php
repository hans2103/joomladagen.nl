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
$params     = JComponentHelper::getParams('com_conference');
$db         = JFactory::getDbo();
$loggeduser = JFactory::getUser();
?>
<form action="<?php echo JRoute::_('index.php?option=com_conference&view=speakers'); ?>" method="post" id="adminForm" name="adminForm" class="form-horizontal">
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
		<table class="table table-striped" id="speakersList">
			<thead>
				<tr>
                    <th width="20">
                        <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                    </th>
					<th width="20">
						<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_STATUS', 'speakers.enabled', $listDirn, $listOrder); ?>
					</th>
					<th colspan="2">
						<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_NAME', 'speakers.title', $listDirn, $listOrder); ?>
					</th>
					<th class="center" width="">
					
					</th>
		
					<th class="center" width="">
					
					</th>
					<th class="center" width="7%">
						<?php echo JText::_('COM_CONFERENCE_FIELD_BIO') ?>
					</th>
					<th class="center" width="7%">
						<?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS') ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_EVENT', 'events.title', $listDirn, $listOrder); ?>
					</th>
					<th width="7%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_FIELD_MODIFIED_LABEL', 'speakers.modified_on', $listDirn, $listOrder) ?>
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
					$canEdit    = $this->canDo->get('core.edit');
					$canChange  = $loggeduser->authorise('core.edit.state', 'com_conference');
					$checkedOut = ($item->locked_by != 0);
					$ordering   = $listOrder == 'ordering';
				?>
				<tr>
                    <td class="center">
						<?php if ($canEdit || $canChange) : ?>
							<?php echo JHtml::_('grid.id', $i, $item->conference_speaker_id, $checkedOut); ?>
						<?php endif; ?>
                    </td>
                    <td class="center">
						<?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'speakers.', $canChange); ?>
                    </td>
                    <td align="center" width="60">
                        <div class="pull-left">
	                        <?php if ($item->image): ?>
                                <img width="50" class="thumbnail" src="<?php echo JUri::root() . $item->image?>">
	                        <?php else:?>
                                <img width="50" class="thumbnail" src="//www.placehold.it/50x50/EFEFEF/AAAAAA&text=no+image" />
	                        <?php endif;?>
                        </div>
                    </td>
                    <td>
                        <?php if ($canEdit)
                        {
                            echo JHtml::_(
                                'link',
                                JRoute::_('index.php?option=com_conference&task=speaker.edit&conference_speaker_id=' . (int) $item->conference_speaker_id),
                                $this->escape($item->title),
                                'title="' . JText::sprintf('COM_CONFERENCE_EDIT_SPEAKER', $this->escape($item->title)) . '"'
                            );
                        }
                        else
                        {
                            echo $this->escape($item->title);
                        }
                        ?>
                        <br />
	                    <?php echo JText::_('JGLOBAL_USERNAME') ?>: <a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . $item->user_id)?>"><?php echo JFactory::getUser($item->user_id)->username ?></a>
                    </td>
					<td class="left">
						<?php if ($params->get('twitter',0) && $item->twitter): ?>
							<a class="btn" target="_blank" href="//twitter.com/<?php echo $this->escape($item->twitter) ?>"><span class="icon conference-twitter"></span></a>
						<?php endif;?>
						<?php if ($params->get('facebook',0) && $item->facebook): ?>
							<a class="btn" target="_blank" href="//facebook.com/<?php echo $this->escape($item->facebook) ?>"><span class="icon conference-facebook"></span></a>
						<?php endif;?>
						<?php if ($params->get('googleplus',0) && $item->googleplus): ?>
							<a class="btn" target="_blank" href="//plus.google.com/<?php echo $this->escape($item->googleplus) ?>"><span class="icon conference-google-plus"></span></a>
						<?php endif;?>
						<?php if ($params->get('linkedin',0) && $item->linkedin): ?>
							<a class="btn" target="_blank" href="//linkedin.com/<?php echo $this->escape($item->linkedin) ?>"><span class="icon conference-linkedin"></span></a>
						<?php endif;?>
						<?php if ($params->get('website',0) && $item->website): ?>
							<a class="btn" target="_blank" href="http://<?php echo $this->escape($item->website) ?>"><span class="icon conference-earth"></span></a>
						<?php endif;?>
					</td>
					<td>
						<?php if ($item->speakernotes): ?>
							<a href="#" class="btn btn-info hasPopover" data-placement="top" data-content="<?php echo($item->speakernotes); ?>"><?php echo JText::_('COM_CONFERENCE_FIELD_NOTES_SPEAKER'); ?></a>
						<?php endif;?>	
					</td>
					<td class="center">
						<?php if ($item->bio): ?>
						<span class="badge badge-success"><i class="icon-checkmark"></i></span>
						<?php else:?>
						<span class="badge badge-important"><i class="icon-delete"></i></span>
						<?php endif;?>
					</td>
					<td class="center">
						<a href="<?php echo JRoute::_('index.php?option=com_conference&view=sessions&filter[conference_speaker_id][]=' . $item->conference_speaker_id); ?>">
						    <?php echo $item->sessions . ' ' . JText::_('COM_CONFERENCE_TABLE_SESSIONS') ?>
						</a>
					</td>
					<td align="left">
						<?php foreach ($item->events as $key => $event) :?>
							<?php if ($key > 0):?><br/><?php endif;?>
							<a href="<?php echo JRoute::_('index.php?option=com_conference&task=event.edit&conference_event_id=' . $event->conference_event_id) ?>"><?php echo(trim($event->title));?></a>
						<?php endforeach;?>
					</td>
					<td class="nowrap">
						<?php if ($item->modified_on === $db->getNullDate()): ?>
							&mdash;
						<?php else: ?>
							<?php echo JHtml::_('date', $item->modified_on, JText::_('DATE_FORMAT_LC4')); ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
        <?php endif; ?>
    </div>
    <input type="hidden" name="task" id="task" value=""/>
    <input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
