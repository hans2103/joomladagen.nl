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

?>
<form action="<?php echo JRoute::_('index.php?option=com_conference&view=levels'); ?>" method="post" id="adminForm" name="adminForm" class="form-horizontal">
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
            <table class="table table-striped" id="levelsList">
                <thead>
                <tr>
                    <th width="20">
                        <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                    </th>
                    <th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_STATUS', 'levels.enabled', $listDirn, $listOrder); ?>
                    </th>
                    <th class="title">
						<?php echo JHtml::_('searchtools.sort', 'COM_CONFERENCE_FIELD_TITLE', 'levels.title', $listDirn, $listOrder); ?>
                    </th>
                    <th class="center" width="12%">
		                <?php echo JText::_('COM_CONFERENCE_FIELD_LABEL') ?>
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
				$canEdit    = $this->canDo->get('core.edit');
				$canChange  = $loggeduser->authorise('core.edit.state', 'com_conference');

				foreach ($this->items as $i => $item) :
					$checkedOut = ($item->locked_by != 0);
					?>
                    <tr>
                        <td class="center">
							<?php if ($canEdit || $canChange) : ?>
								<?php echo JHtml::_('grid.id', $i, $item->conference_level_id, $checkedOut); ?>
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
										JRoute::_('index.php?option=com_conference&task=level.edit&conference_level_id=' . (int) $item->conference_level_id),
										$this->escape($item->title),
										'title="' . JText::sprintf('COM_CONFERENCE_EDIT_LEVEL', $this->escape($item->title)) . '"'
									);
								}
								else
								{
									echo $this->escape($item->title);
								}
								?>
                            </div>
                        </td>
                        <td class="center">
                            <span class="label <?php echo $item->label ?>"><?php echo $this->escape($item->title) ?></span>
                        </td>
                        <td>
	                        <?php
	                        $url = JRoute::_('index.php?option=com_conference&view=sessions&filter[conference_level_id]=' . $item->conference_level_id);
	                        echo JHtml::_('link', $url, $item->sessions . ' ' . JText::_('COM_CONFERENCE_TABLE_SESSIONS'));
	                        ?>
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
