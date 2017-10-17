<?php
/**
 * @package    JTicketing
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com*
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('bootstrap.tooltip');

$document               = JFactory::getDocument();
$document->addScript(JUri::root(true) . '/media/com_jticketing/js/jticketing.js');


// Import helper for declaring language constant
JLoader::import('Jticketingmainhelper', JUri::root() . 'components/com_jticketing/helpers/main.php');

// Call helper function
JticketingCommonHelper::getLanguageConstant();

// Native Event Manager
if ($this->integration != 2 and $this->integration != 4)
{
?>
	<div class="alert alert-info alert-help-inline">
		<?php	echo JText::_('COMJTICKETING_INTEGRATION_NATIVE_NOTICE');?>
	</div>
<?php
	return false;
}

echo '<div id="fb-root"></div>';
$fblike_tweet = JUri::root() . 'media/com_jticketing/js/fblike.js';
echo "<script type='text/javascript' src='" . $fblike_tweet . "'></script>";

$setdata = JRequest::get('get');

$core_js = JUri::root() . 'media/system/js/core.js';
$flg = 0;

foreach ($document->_scripts as $name => $ar)
{
	if ($name == $core_js)
	$flg = 1;
}

if ($flg == 0)
echo "<script type='text/javascript' src='" . $core_js . "'></script>";

$params = JComponentHelper::getParams('com_jticketing');
?>

<div class="<?php echo JTICKETING_WRAPPER_CLASS;?> jtick-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-12 col-md-3">
				<ul class="list-inline">
					<li><h5><strong><?php echo strtoupper(JText::_('JT_ALL_CAMP'));?></strong></h5></li>
				</ul>
			</div>
			<div class="col-xs-12 col-md-9 ">
				<form action="" method="post" name="eventsForm" id="eventsForm">
					<ul class="pull-right list-inline">
						<li>
							<?php
								$launch_event_url = JRoute::_('index.php?option=com_jticketing&view=eventform&Itemid=' . $this->create_event_itemid);
							?>
								<a href="<?php echo $launch_event_url;?>" title="<?php echo JText::_('COM_JTICKETING_EVENTS_CREATE_NEW_EVENT')?>">
									<i class="fa fa-paper-plane-o" aria-hidden="true"></i>
									<?php echo JText::_('COM_JTICKETING_EVENTS_CREATE_EVENT');?>
								</a>
						</li>
						<li>|</li>
						<?php
						if ($this->params->get('show_search_filter'))
						{
						?>
							<li>
								<a id="searchEventBtn" href="javascript:void(0)" onclick="jtSite.events.toggleDiv('searchFilterInputBox');" title="<?php echo JText::_('COM_JTICKETING_SEARCH_EVENT')?>">
									<i class="fa fa-search" ></i>
								</a>
								<span class="pull-left searchEvents" id="searchFilterInputBox">
									<input
										type="text"
										placeholder="<?php echo JText::_('COM_JTICKETING_ENTER_EVENTS_NAME');?>"
										name="search"
										id="search"
										value="<?php echo $srch = ($this->lists['search'])?$this->lists['search']:''; ?>"
										class="form-control col-xs-3"
										onchange="this.form.submit();"/>
									<button
										type="button"
										onclick="this.form.submit();"
										class="btn btn-mini tip hasTooltip col-xs-1"
										data-original-title="Search">
										<i class="fa fa-search"></i>
									</button>
									<button
										type="button"
										onclick="document.getElementById('search').value='';this.form.submit();"
										class="btn btn-mini tip hasTooltip"
										data-original-title="Clear">
										<i class="fa fa-remove"></i>
									</button>
								</span>
							</li>
							<li>|</li>
						<?php
						}

						if ($this->params->get('show_filters'))
						{
						?>
							<?php
							if ($this->params->get('show_sorting_options'))
							{
							?>
								<li>
									<div class="dropdown">
										<a class="dropdown-toggle" type="" data-toggle="dropdown"><i class="fa fa-sort"></i></a>
										<ul class="dropdown-menu">
											<?php
											echo JHtml::_('select.genericlist', $this->ordering_options, "filter_order", ' size="1"
											onchange="this.form.submit();"
											class="form-control" name="filter_order"',"value", "text", $this->lists['filter_order']);
										?>
										</ul>
									</div>
								</li>
								<li>|</li>
							<?php
							}?>
							<li>
								<a id="displayFilter" href="javascript:void(0)" onclick="jtSite.events.toggleDiv('displayFilterText');" title="<?php echo JText::_('COM_JTICKETING_FILTER_EVENT')?>">
									<i class="fa fa-filter"></i>
								</a>
							</li>
							<li>|</li>
						<?php
						}?>
						<li><?php echo $this->pagination->getLimitBox();?></li>
					</ul>
					<input type="hidden" name="option" value="com_jticketing" />
					<input type="hidden" name="view" value="events" />
					<input type="hidden" name="layout" value="default" />
				</form>
			</div>
			<div class="col-xs-12 eventsFilterDefaultDis" id="displayFilterText">
				<?php
					echo $this->loadTemplate("filters");
				?>
				<div class="clearfix">&nbsp;</div>
			</div>
		</div>
		<?php
		if (empty($this->items))
		{
		?>
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pull-right alert alert-info jtleft"><?php echo JText::_('COM_JT_NOT_FOUND_EVENT');?></div>
		<?php
		}
		else
		{
		?>
			<div class="row">
				<?php echo $this->loadTemplate("pin");?>
			</div>
			<div>
				<div class="pull-right">
					<?php echo $this->pagination->getListFooter(); ?>
				</div>
			</div>
		<?php
		}
		?>
	</div>
</div>
