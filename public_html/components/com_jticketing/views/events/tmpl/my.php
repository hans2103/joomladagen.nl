<?php
/**
 * @version     1.5
 * @package     com_jticketing
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <contact@techjoomla.com> - http://techjoomla.com
 */
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');

if(JVERSION >= '3.0')
{
	JHtml::_('bootstrap.tooltip');
}

if($this->integration != 2)//NATIVE EVENT MANAGER
{
	?>
		<div class="alert alert-info alert-help-inline">
			<?php	echo JText::_('COMJTICKETING_INTEGRATION_NATIVE_NOTICE');	?>
		</div>
	<?php

	return false;
}
?>

<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">

	<div class="event-form<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		</div>
	<?php endif; ?>
	</div>

	<div id="jticketing_events">
		<div class="row">
			<div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<form method="post" name="adminForm" id="adminForm" class="form-inline">
					<div class="row">
						<div class=" col-lg-4 col-md-6 col-sm-6 col-xs-12">
							<div class="input-group">
								<input type="text" placeholder="<?php echo JText::_('COM_JTICKETING_ENTER_EVENTS_NAME'); ?>" name="search" id="search" value="<?php echo $srch = ($this->lists['search'])?$this->lists['search']:''; ?>" class="form-control" onchange="document.adminForm.submit();" />
								<span class="input-group-btn">
									<button type="button" onclick="this.form.submit();" class="btn btn-success tip hasTooltip" data-original-title="Search"><i class="fa fa-search"></i></button>
									<button type="button" onclick="document.getElementById('search').value='';this.form.submit();" class="btn btn-primary tip hasTooltip" data-original-title="Clear"><i class="fa fa-remove"></i></button>
								</span>
							</div>
						</div>
						<div class=" col-lg-8 col-md-6 col-sm-6 col-xs-12">
							<div class="btn-group clearfix pull-right hidden-xs">
								<label for="limit" class="element-invisible">
									<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
								</label>
								<?php echo $this->pagination->getLimitBox(); ?>
							</div>
							<div class="clearfix"><br/>&nbsp;</div>
							<input type="hidden" name="filter_order" value="<?php echo $this->filter_order; ?>" />
							<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter_order_Dir; ?>" />
							<input type="hidden" name="option" value="com_jticketing" />
							<input type="hidden" name="view" value="events" />
							<input type="hidden" name="controller" value="" />
							<input type="hidden" name="task" value="" />
							<input type="hidden" name="boxchecked" value="0" />
							<?php echo JHtml::_( 'form.token' ); ?>
						</div>
					</div><!--row-->
					<div class="row">
						<div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<?php
							if (empty($this->items)): ?>
								<div class="clearfix">&nbsp;</div>
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pull-right alert alert-info jtleft"><?php echo JText::_('COM_JTICKETING_EVENTS_NO_EVENTS_FOUND');?></div>
							<?php
							else : ?>
								<div id='no-more-tables'>
									<table class="table table-striped table-bordered table-hover">
										<thead>
											<tr>
												<th>
													<?php echo JHtml::_('grid.sort', 'COM_JTICKETING_EVENTS_TITLE', 'title', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>

												<th class="center nowrap com_jticketing_width10">
													<?php echo JHtml::_('grid.sort', 'COM_JTICKETING_EVENTS_CREATED', 'created', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>

												<!--
												<th class="center hidden-xm com_jticketing_width5">
													<?php echo JHtml::_('grid.sort', 'COM_JTICKETING_EVENTS_PUBLISHED', 'state', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>
												-->

												<th class="com_jticketing_width15">
													<?php echo JHtml::_('grid.sort', 'COM_JTICKETING_EVENTS_CATEGORY', 'category', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>


												<th class="center nowrap com_jticketing_width10">
													<?php echo JHtml::_('grid.sort', 'COM_JTICKETING_EVENTS_STARTDATE', 'startdate', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>

												<th class="center nowrap com_jticketing_width10">
													<?php echo JHtml::_('grid.sort', 'COM_JTICKETING_EVENTS_ENDDATE', 'enddate', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>

												<th class="com_jticketing_width15">
													<?php echo JHtml::_('grid.sort', 'COM_JTICKETING_EVENTS_LOCATION', 'location', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$n = count($this->items);
											for ($i=0; $i < $n ; $i++)
											{
												$row  = $this->items[$i];
												$link = JRoute::_('index.php?option=com_jticketing&task=eventform.edit&id=' . $row->id . '&Itemid=' . $this->create_event_itemid, false);

												$jticketingMainHelper = new jticketingmainhelper;
												$eventDetailUrl = $jticketingMainHelper->getEventlink($row->id);
												?>

												<tr>
													<td data-title="<?php echo JText::_('COM_JTICKETING_EVENTS_TITLE'); ?>">
														<a href="<?php echo $eventDetailUrl; ?>" title="<?php echo $row->title; ?>">
															<?php echo $row->title; ?>
															
														</a>
														<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_JTICKETING_EDIT_EVENT'); ?>">
															<i class="fa fa-pencil-square-o pull-right" aria-hidden="true"></i>
														</a>
													</td>

													<td class="center small nowrap hidden-xm com_jticketing_width10" data-title="<?php echo JText::_('COM_JTICKETING_EVENTS_CREATED');?>">
														<span class="badge badge-info">
															<?php echo JFactory::getDate($row->created)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT')); ?>
														</span>
													</td>

													<!--
													<td class="small center hidden-xm com_jticketing_width5">
														<a class="btn  btn-default  btn-micro active hasTooltip"
														href="javascript:void(0);"
														title="<?php echo ( $row->state ) ? JText::_('COM_JTICKETING_UNPUBLISH') : JText::_('COM_JTICKETING_PUBLISH'); ;?>"
														onclick="document.adminForm.boxchecked.value=1; Joomla.submitbutton('<?php echo ( $row->state ) ? 'tests.unpublish' : 'tests.publish';?>');">
															<i class=<?php echo ( $row->state ) ? "icon-publish" : "icon-unpublish";?> > </i>
														<a/>
													</td>
													-->

													<td class="small com_jticketing_width15"  data-title="<?php echo JText::_('COM_JTICKETING_EVENTS_CATEGORY');?>">
													<?php echo ($row->category == NULL) ? "-" : $row->category; ?>
													</td>

													<td class="center small nowrap com_jticketing_width10" data-title="<?php echo JText::_('COM_JTICKETING_EVENTS_STARTDATE');?>">
														<?php
														$statrtDate = JFactory::getDate($row->startdate)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
														$startDate =  JHtml::date($row->startdate, JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'), true);
														echo $startDate; ?>
														<br/>
														<?php
														$statrtTime = JFactory::getDate($row->startdate)->Format(JText::_('COM_JTICKETING_TIME_FORMAT_SHOW_AMPM'));
														$statrtTime =  JHtml::date($row->startdate, JText::_('COM_JTICKETING_TIME_FORMAT_SHOW_AMPM'), true);
														echo $statrtTime;
														?>
													</td>

													<td class="center small nowrap com_jticketing_width10" data-title="<?php echo JText::_('COM_JTICKETING_EVENTS_ENDDATE');?>">
														<?php
														$endDate = JFactory::getDate($row->enddate)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
														$endDate =  JHtml::date($row->enddate, JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'), true);
														echo $endDate; ?>
														<br/>
													<?php
														$endTime = JFactory::getDate($row->enddate)->Format(JText::_('COM_JTICKETING_TIME_FORMAT_SHOW_AMPM'));
														$endTime =  JHtml::date($row->enddate, JText::_('COM_JTICKETING_TIME_FORMAT_SHOW_AMPM'), true);
														echo $endTime;
													?>
													</td>

													<td class="small com_jticketing_width15" data-title="<?php echo JText::_('COM_JTICKETING_EVENTS_LOCATION');?>">
														<?php echo $row->location; ?>
													</td>
												</tr>
											<?php
											}
											?>
										</tbody>
									</table>
								</div>
							<?php
							endif;?>
						</div><!--col-lg-12 col-md-12 col-sm-12 col-xs-12-->
					</div><!--row-->
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<?php if (JVERSION >= '3.0'): ?>
								<?php echo $this->pagination->getListFooter(); ?>
							<?php else: ?>
								<div class="pager">
									<?php echo $this->pagination->getListFooter(); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</form>
			</div><!-- col-lg-12 col-md-12 col-sm-12 col-xs-12-->
		</div><!--row-->
	</div><!--row-->
</div>
