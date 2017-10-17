<?php
/**
* @version    SVN: <svn_id>
* @package    JTicketing
* @author     Techjoomla <extensions@techjoomla.com>
* @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
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

$jteventHelper         = new jteventHelper;
$JticketingModelEvents = new JticketingModelEvents;
$jticketingmainhelper  = new jticketingmainhelper;

$ordering_options           = $JticketingModelEvents->getOrderingOptions();
$ordering_direction_options = $JticketingModelEvents->getOrderingDirectionOptions();
$creator                    = $JticketingModelEvents->getCreator();
$locations                  = $JticketingModelEvents->getLocation();
$event_types                = $jteventHelper->getEventType();
$cat_options                = $jteventHelper->getEventCategories();
$events_to_show             = $jteventHelper->eventsToShowOptions();
$url = 'index.php?option=com_jticketing&view=events&layout=default';

// Get itemid
$singleEventItemid = $jticketingmainhelper->getItemId($url);

if (empty($singleEventItemid))
{
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
?>

<!--Quick Search-->
<div class="tj-filterhrizontal pull-left col-xs-12 col-sm-3" >
	<div>
		<p class="text-muted"><?php echo JText::_('COM_JTICKETING_EVENTS_TO_SHOW');?></p>
	</div>
	<?php
	$selected = $jinput->get('events_to_show', '', 'string');
	$quick_search_url='index.php?option=com_jticketing&view=events&layout=default&events_to_show=&Itemid='.$singleEventItemid;
	$quick_search_url=JUri::root().substr(JRoute::_($quick_search_url),strlen(JUri::base(true))+1);
	?>
	<div class="<?php echo empty($selected) ? 'active': ''; ?>">
		<label>
			<input type="radio" class="" name="<?php echo "quick_search[]";?>"
				id="quicksearch" value="<?php echo JText::_('COM_JTICKETING_RESET_FILTER_TO_ALL'); ?>"
				<?php echo empty($selected) ? 'checked': ''; ?>
				onclick='window.location.assign("<?php echo $quick_search_url;?>")'/>
			<?php echo JText::_('COM_JTICKETING_RESET_FILTER_TO_ALL'); ?>
		</label>
	</div>
	<?php
	for ($i = 1; $i < count($events_to_show); $i ++)
	{
		$check = "";
		$selected = $events_to_show[$i]->value;

		$quick_search_url='index.php?option=com_jticketing&view=events&layout=default&events_to_show=' . $selected . '&Itemid='.$singleEventItemid;
		$quick_search_url=JUri::root().substr(JRoute::_($quick_search_url),strlen(JUri::base(true))+1);

		if ($lists['events_to_show'] == $selected)
		{
			$class = "active";
			$check = "checked";
		}
		else
		{
			$class = "";
		}
	?>
		<div class="<?php echo $class; ?>">
			<label>
				<input type="radio" class=""
				name="<?php echo 'quick_search[]';?>"
				id="quicksearchfields" <?php echo $check;?>
				value="<?php echo $events_to_show[$i]->text; ?>"
				onclick='window.location.assign("<?php echo $quick_search_url;?>")'/>
				<?php echo $events_to_show[$i]->text; ?>
			</label>
		</div>
	<?php
	}
	?>
</div>
<!--Quick Search end here-->

<form action="" method="post" name="adminForm3" id="adminForm3">
	<!--Event Tpye filter-->
	<?php
	if ($online_events_enable == '1')
	{
		if($params->get('show_event_filter'))
		{
			?>
			<div class="tj-filterhrizontal pull-left col-xs-12 col-sm-3" >
				<div>
					<p class="text-muted"><?php echo JText::_('COM_JTICKETING_EVENT_TYPE');?></p>
				</div>
				<?php
				foreach ($event_types as $event_type)
				{
					$check = "";
					$selected = $event_type->value;

					if ($lists['online_events'] == $selected)
					{
						$class = "active";
						$check = "checked";
					}
					else
					{
						$class = "";
					}
				?>
					<div class="<?php echo $class; ?>">
						<label>
							<input type="radio" class=""
							name="<?php echo 'online_events';?>"
							id="online_events" <?php echo $check;?>
							value="<?php echo $event_type->value; ?>"
							onclick='this.form.submit();'/>
							<?php echo $event_type->text; ?>
						</label>
					</div>
				<?php
				}?>
			</div>
		<?php
		}
	}
	?>
	<!--Event Tpye filter end-->

	<?php
	if ($params->get('show_creator_filter') or $params->get('show_location_filter'))
	{?>
		<div class="tj-filterhrizontal pull-left col-xs-12 col-sm-3" >
			<div class="tj-filterhrizontal col-xs-12 col-sm-12">
				<div><p class="text-muted"><?php echo JText::_('COM_JTICKETING_EVENT_LOCATION');?></p></div>
				<div>
					<?php echo JHtml::_('select.genericlist', $locations, "filter_location", 'class="form-control" size="1" onchange="this.form.submit();" name="filter_location"',"value", "text",$lists['filter_location']);?>
				</div>
			</div>

			<div class="tj-filterhrizontal col-xs-12 col-sm-12">
				<div><p class="text-muted"><?php echo JText::_('COM_JTICKETING_EVENT_CREATOR');?></p></div>
				<div>
					<?php
						$creator_filter_on=0;
						if ($params->get('show_creator_filter'))
						{
							$creator_filter_on=1;
							echo JHtml::_('select.genericlist', $creator, "filter_creator", ' size="1"
								onchange="this.form.submit();" class="form-control" name="filter_creator"',"value", "text", $lists['filter_creator']);
						}
						else
						{
							$input=JFactory::getApplication()->input;
							$filter_creator=$input->get('filter_creator','','INT');
							if (!empty($filter_user))
							{
								$creator_filter_on=0;
							}
						}
					?>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	<?php
	}
	?>
	<input type="hidden" name="option" value="com_jticketing" />
	<input type="hidden" name="view" value="events" />
	<input type="hidden" name="layout" value="default" />
</form>
