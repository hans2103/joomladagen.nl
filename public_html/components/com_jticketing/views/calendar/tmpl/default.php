<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHTML::_('behavior.modal', 'a.modal');
$document = JFactory::getDocument();
$root_url = JUri::root();
$document->addStyleSheet($root_url . 'components/com_jticketing/assets/calendars/css/calendar.css');
$document->addStyleSheet($root_url . 'components/com_jticketing/assets/css/calendar.css');
$document->addStyleSheet($root_url . 'components/com_jticketing/assets/font-awesome-4.1.0/css/font-awesome.min.css');
?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">
<div class="fluid-container jtcalendar">
	<div class="row">
		<form method="post" name="adminForm" id="adminForm">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

				<div class="pull-right form-inline">
				<?php
				$filter = $this->state->get('filter.filter_evntCategory');
				$class = 'class="form-control input-medium" size="1" onchange="document.adminForm.submit();" name="filter_evntCategory"';
					echo JHtml::_('select.genericlist', $this->cat_options, "filter_evntCategory", $class, "value", "text", $filter);
				?>
				</div>
			</div>

			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

				<div class="jt-page-header">
					<div class="form-inline">
						<div class="btn-group">
							<span class="input-group-btn    pull-left">
							<button class="btn      btn-sm-jt" style="margin-right:4px;" id="pre_year_button" data-calendar-nav="prev-year">&nbsp;
							<i class="fa fa-backward"></i>&nbsp;
							</button>
							<button class="btn     btn-sm-jt" id="pre_button" data-calendar-nav="prev">&nbsp;
							<i class="fa fa-chevron-left"></i>&nbsp;</button>
							<div class="btn    " id="month_button" data-calendar-nav=""><span id="month_text"></span></div>
							<button class="btn     btn-sm-jt" id="nex_button" data-calendar-nav="next">&nbsp;
							<i class="fa fa-chevron-right"></i>&nbsp;</button>
							<button class="btn     btn-sm-jt" id="nex_year_button" data-calendar-nav="next-year">&nbsp;
							<i class="fa fa-forward"></i>&nbsp;</button>
							</div>
							</span>

						<div class="btn-group" id="rt_align">
							<button class="btn    " data-calendar-view="year" style="border-bottom-left-radius:4px;border-top-left-radius:4px">
								<?php echo JText::_('COM_JTICKETING_CALENDAR_YEAR')?>
								</button>
							<button class="btn    " id="but-today"data-calendar-nav="today" style="display:none;">
								<?php echo JText::_('COM_JTICKETING_CALENDAR_TODAY')?></button>
							<button class="btn     active" data-calendar-view="month"><?php echo JText::_('COM_JTICKETING_CALENDAR_MONTH')?></button>
							<button class="btn    " data-calendar-view="week"><?php echo JText::_('COM_JTICKETING_CALENDAR_WEEK')?></button>
							<button class="btn    " data-calendar-view="day"><?php echo JText::_('COM_JTICKETING_CALENDAR_DAY')?></button>
						</div>
					</div>

				</div>
			</div>

			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div id="calendar"></div>
			</div>
			<?php
			//$document->addScript($root_url . 'components/com_jticketing/assets/calendars/components/underscore/underscore-min.js');
			//$document->addScript($root_url . 'components/com_jticketing/assets/calendars/components/bootstrap3/js/bootstrap.min.js');
			//$document->addScript($root_url . 'components/com_jticketing/assets/calendars/components/jstimezonedetect/jstz.min.js');
			//$document->addScript($root_url . 'components/com_jticketing/assets/calendars/js/calendar.js');
			//$document->addScript($root_url . 'components/com_jticketing/assets/calendars/js/app.js');
			?>
			<script type="text/javascript">
			var root_url="<?php  echo $root_url; ?>";
			</script>
			<script type="text/javascript" src="<?php echo $root_url . 'components/com_jticketing/assets/calendars/components/underscore/underscore-min.js';?>">
			</script>
			<script type="text/javascript" src="<?php echo $root_url . 'components/com_jticketing/assets/calendars/components/jstimezonedetect/jstz.min.js';?>">
			</script>
			<script type="text/javascript" src="<?php echo $root_url . 'components/com_jticketing/assets/calendars/js/calendar.js';?>">
			</script>
			<script type="text/javascript" src="<?php echo $root_url . 'components/com_jticketing/assets/calendars/js/app.js';?>">
			</script>

			<input type="hidden" name="template_path_calendar" id="template_path_calendar" value="<?php echo $root_url;?>components/com_jticketing/assets/calendars/tmpls/">
		<script type="text/javascript">
		jQuery("#adminForm").submit(function(e)
		{
			e.preventDefault();
		});
		</script>
		</form>
	</div>
</div>
</div>
