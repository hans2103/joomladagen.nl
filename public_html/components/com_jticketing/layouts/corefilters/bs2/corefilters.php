<?php
	/**
	* @version    SVN: <svn_id>
	* @package    JTicketing
	* @author     Techjoomla <extensions@techjoomla.com>
	* @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
	* @license    GNU General Public License version 2 or later.
	*/

	// No direct access
	defined('_JEXEC') or die('Restricted access');
	$jinput = JFactory::getApplication();
	$params = $jinput->getParams('com_jticketing');

	// Get the online_events value if it's enable then display event filter
	$online_events_enable = $params->get('enable_online_events', '', 'INT');

	$jticketingParams = JComponentHelper::getParams('com_jticketing');
	require_once JPATH_SITE . '/components/com_jticketing/helpers/event.php';
	require_once JPATH_SITE . '/components/com_jticketing/models/events.php';

	$JticketingModelEvents      = new JticketingModelEvents;
	$ordering_options           = $JticketingModelEvents->getOrderingOptions();
	$ordering_direction_options = $JticketingModelEvents->getOrderingDirectionOptions();
	$creator                    = $JticketingModelEvents->getCreator();
	$location                   = $JticketingModelEvents->getLocation();

	$online_events   = array();
	$online_events[] = JHtml::_('select.option', '', JText::_('COM_JTK_FILTER_SELECT_EVENT_DEFAULT'));
	$online_events[] = JHtml::_('select.option', '0', JText::_('COM_JTK_FILTER_SELECT_EVENT_OFFLINE'));
	$online_events[] = JHtml::_('select.option', '1', JText::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE'));

	foreach ($online_events as $value) {
		$online_event = $value->text;
	}

	$jteventHelper  = new jteventHelper;
	$cat_options    = $jteventHelper->getEventCategories();
	$events_to_show = $jteventHelper->eventsToShowOptions();
	$events_to_show = $events_to_show;

	$jticketingmainhelper = new jticketingmainhelper;

	$url = 'index.php?option=com_jticketing&view=events&layout=default';

	// Get itemid
	$singleEventItemid = $jticketingmainhelper->getItemId($url);

	if (empty($singleEventItemid)) {
		$singleEventItemid = JFactory::getApplication()->input->get('Itemid');
	}

	// Get filter value and set list
	$defualtCatid               = $params->get('defualtCatid');
	$filter_event_cat           = $jinput->getUserStateFromRequest('com_jticketing.filter_events_cat', 'filter_events_cat', $defualtCatid, 'INT');
	$lists['filter_events_cat'] = $filter_event_cat;

	// Ordering option
	$default_sort_by_option = $params->get('default_sort_by_option');
	$filter_order_Dir       = $params->get('filter_order_Dir');
	$filter_order           = $jinput->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', $default_sort_by_option, 'string');
	$filter_order_Dir       = $jinput->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', $filter_order_Dir, 'string');

	// Get creator and location filter
	$filter_creator  = $jinput->getUserStateFromRequest('com_jticketing' . 'filter_creator', 'filter_creator');
	$filter_location = $jinput->getUserStateFromRequest('com_jticketing' . 'filter_location', 'filter_location');
	$online_event = $jinput->getUserStateFromRequest('com_jticketing' . 'online_events', 'online_events');

	// Set all filters in list
	$lists['filter_order']     = $filter_order;
	$lists['filter_order_Dir'] = $filter_order_Dir;
	$lists['filter_creator']   = $filter_creator;
	$lists['filter_location']  = $filter_location;
	$lists['online_events']    = $online_event;
	$lists                     = $lists;

	// Search and filter
	$filter_state            = $jinput->getUserStateFromRequest('com_jticketing' . 'search', 'search', '', 'string');
	$filter_events_to_show   = $jinput->getUserStateFromRequest('com_jticketing' . 'events_to_show', 'events_to_show');
	$lists['search']         = $filter_state;
	$lists['events_to_show'] = $filter_events_to_show;
	/*$this->jt_itemid         = $this->assignRef('lists', $lists);
	$this->assignRef('date', $date);*/
?>

<div class="">
	<form action="" method="post" name="adminForm3" id="adminForm3">
		<input type="hidden" name="option" value="com_jticketing" />
		<input type="hidden" name="view" value="events" />
		<input type="hidden" name="layout" value="default" />
		<?php
			if ($params->get('show_creator_filter') or $params->get('show_location_filter'))
				{	?>
					<div><b><?php echo JText::_('COM_JTICKETING_FILTER_EVENTS');?></b></div>
					<div class="control-group">
						<?php
							$creator_filter_on=0;
							if ($params->get('show_creator_filter'))
							{
								$creator_filter_on=1;
								echo JHtml::_('select.genericlist', $creator, "filter_creator", ' size="1"
								onchange="this.form.submit();" class="input-medium" name="filter_creator"',"value", "text", $lists['filter_creator']);
							}
							else
							{
								$input=JFactory::getApplication()->input;
								$filter_creator=$input->get('filter_creator','','INT');
								if (!empty($filter_user))
								{
									$creator_filter_on=0;
								}
							}?>
					</div>
					<div class="control-group">
					<?php
					if($params->get('show_location_filter'))
					{
						echo JHtml::_('select.genericlist', $location, "filter_location", 'class="input-medium" size="1"
								onchange="this.form.submit();" name="filter_location"',"value", "text",$lists['filter_location']);
					}
					?>
					</div>
					<div class="control-group">
						<?php if ($online_events_enable == '1')
						{
							if ($params->get('show_event_filter'))
							{
								echo JHtml::_('select.genericlist', $online_events, "online_events", ' size="1"
									onchange="this.form.submit();" class="input-medium" name="online_events"',"value", "text", $lists['online_events']);
							}
						}	?>
					</div>
		<?php	}
				if ($params->get('show_search_filter'))
				{
				?>
					<div><b><?php echo JText::_('COM_JTICKETING_EVENTS_TO_SHOW');?></b></div>
					<ul class="inline">
						<?php
							$cat_url='index.php?option=com_jticketing&view=events&layout=default&events_to_show=&Itemid='.$singleEventItemid;
							$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);

							for($i=1;$i<count($events_to_show);$i++)
							{
								$check = '';
								$cat_url='index.php?option=com_jticketing&view=events&layout=default&events_to_show='.$events_to_show[$i]->value.'&Itemid='.$singleEventItemid;
								$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);

								if ($lists['events_to_show']==$events_to_show[$i]->value)
								{
									$class = "active";
									$check = "checked";
								}
								else
								{
									$class = "";
								}
								?>
								<li>
									<label><input type="radio" class="<?php echo $class;?>" name="<?php echo $events_to_show[$i]->text;?>"
									id="event_type" value="" <?php echo $check;?>
									onclick='window.location.assign("<?php echo $cat_url;?>")'/>
									<?php echo $events_to_show[$i]->text; ?>
									</label>
								</li></br>
					<?php	}	?>
					</ul>
		<?php	}	?>
		<br />
	</form>
</div>
