<?php
/**
 * @version    SVN: <svn_id>
 * @package    JLike
 * @copyright  Copyright (C) 2005 - 2015. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="container">
	<div class="row-fluid row">
		<div id="filter-bar" class="btn-toolbar assignUser">
			<div class="filter-search btn-group pull-left">
				<input type="text"
					name="filter_search"
					id="filter_search"
					placeholder="<?php echo JText::_('COM_JLIKE_SEARCH_FILTER'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					data-placement="bottom"
					title="<?php echo JText::_('COM_JLIKE_SEARCH_FILTER'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button
					class="btn_jlike_style btn hasTooltip"
					type="submit"
					data-placement="bottom"
					title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
						<i class="fa fa-search" aria-hidden="true"></i>
				</button>
				<button
					class="btn_jlike_style btn hasTooltip"
					type="button"
					data-placement="bottom"
					title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
					onclick="document.id('filter_search').value='';document.adminForm.submit();">
						<i class="fa fa-remove" aria-hidden="true"></i>
				</button>
			</div>
			<?php if (JVERSION >= '3.0'): ?>
				<div class="btn-group pull-right hidden-phone">
					<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this,'options'=>array('filterButton'=>false)));
						?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="row-fluid row">
		<div class="span6 col-sm-6 jlike-uber-padding say-something-for-recomendation">
			<textarea name="sender_msg" rows="3" class="jlike-commentbox-height" placeholder="<?php echo JText::_("COM_JLIKE_SAY_SOMETHING");?>"></textarea>
		</div>

		<?php if ($this->type == 'assign'): ?>
			<div class="span6 col-sm-6 jlike-uber-padding pull-right" >
				<?php
				echo JHtml::_('calendar', '','start_date','start_date',JText::_('COM_JLIKE_DATE_FORMAT_PER'), 'placeholder="' . JText::_("COM_JLIKE_START_DATE") . '" class="required"');

				echo JHtml::_('calendar', '','due_date','due_date',JText::_('COM_JLIKE_DATE_FORMAT_PER'), 'placeholder="' . JText::_("COM_JLIKE_DUE_DATE") . '" class="required"');
				 ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="row-fluid row">
		<?php
		if (empty($this->peopleToRecommend))
		{ ?>
				<div class="alert alert-warning">
					<?php
						echo JText::_('COM_JLIKE_NO_FRIENDS_FOUND');
					?>
				</div>
			<?php
		}
		else
		{ ?>
		<div class="usersContainer">
			<ul id="jlike-users-list" class="jlike-users-list">
				<?php foreach ($this->peopleToRecommend as $i => $item): ?>
					<li id="rocommenToUser<?php echo $item->friendid; ?>"
						class="allUserAvaiable span3 col-sm-3 jlike-uber-padding" >

						<div class="thumbnail clearfix jlike-thumbnail-margin">

							<img src="<?php echo $item->avatar;?>"
								alt="<?php echo $item->name;?>"
								class="pull-left user_avatar span5 clearfix"
							/>

							<input type="checkbox"
								id="recommend_friends-<?php echo $item->friendid; ?>"
								name="recommend_friends[]"
								value="<?php echo $item->friendid?>"
								onclick="<?php if(!empty($onclick)) echo $onclick;?>"
								class="thCheckbox contacts_check "
							/>

							<div class="recousername">
								<strong>
									<em><?php echo $item->name;?></em>
								</strong>
							</div>

						</div>
					</li>
				<?php endforeach; ?>
				<li id="jlike_pagination" class="span12">
					<?php if (JVERSION < 3.0) : ?>
						<div class="clearfix">&nbsp;</div>
					<?php endif;?>
					<div class="pager">
					<?php echo $this->pagination->getListFooter(); ?>
					</div>
				</li>
			</ul>
		</div>
		<?php } ?>
	</div>
</div>
