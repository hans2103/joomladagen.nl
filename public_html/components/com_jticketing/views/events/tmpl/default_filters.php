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

$document = JFactory::getDocument();
$renderer = $document->loadRenderer('module');
$com_params   = JComponentHelper::getParams('com_jticketing');
$modules  = JModuleHelper::getModules('tj-filters-mod-pos');

if ($modules)
{
	$moduleParams = new JRegistry($modules['0']->params);
	$params   = array();
	if ($this->params->get('show_filters') == 1)
	{
		if ($moduleParams->get('client_type') == "com_jticketing.event")
		{
			foreach ($modules as $module)
			{
				echo $renderer->render($module, $params);
			}
		}
		else
		{
			echo JText::_('COM_JTICKETING_EVENT_NOTICE');
		}
	}
}
elseif ($this->params->get('show_filters') == 1)
{
?>
	<form action="" name="eventFilterform" method="post" id="eventFilterform">
		<!--Category Filter code for core filters-->
		<?php
			if ($this->params->get('show_category_filter'))
			{?>
				<div class="col-xs-12 col-sm-3 eventsCatFilterwrapper">
					<p class="text-muted"><?php echo JText::_('COM_JTICKETING_FILTER_EVNT_CAT');?></p>
					<?php
						$cat_url='index.php?option=com_jticketing&view=events&filter_events_cat=&Itemid=' . $this->allEventsItemid;
						$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);

						if ($this->lists['filter_events_cat'] == 0)
						{
							$class = "active";
							$checkVal = "checked";
						}
						else
						{
							$class = "";
							$checkVal = "";
						}
					?>

					<div class="<?php echo $class; ?>">
						<label>
							<input type="radio" class=""
							name="<?php echo 'filter_events_cat';?>"
							id="filter_events_cat" <?php echo $checkVal;?>
							value="0"
							onclick='window.location.assign("<?php echo $cat_url;?>")'/>
							<?php echo JText::_('COM_JTICKETING_RESET_FILTER_TO_ALL');?>
						</label>
					</div>
					<?php

					foreach ($this->cat_options as $category)
					{
						$check = "";
						$selected = $category->value;

						$cat_url='index.php?option=com_jticketing&view=events&filter_events_cat='.$category->value.'&Itemid=' . $this->allEventsItemid;
						$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);

						if($this->lists['filter_events_cat']==$selected)
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
									name="<?php echo 'filter_events_cat';?>"
									id="filter_events_cat" <?php echo $check;?>
									value="<?php echo $category->value; ?>"
									onclick='window.location.assign("<?php echo $cat_url;?>")'/>
									<?php echo $category->text; ?>
								</label>
							</div>
					<?php
					}
					?>
				</div>
			<?php
			}

			/* Core Category Filter code ended*/

			$basePath = JPATH_SITE . '/components/com_jticketing/layouts/corefilters/horizontal/bs3';
			$layout = new JLayoutFile('corefilters', $basePath);
			$data = "";
			echo $layout->render($data);
			$selected = null;
		?>
		<div class="clearfix"></div>
		<div class="row">
			<?php
				$eventsResetFilterUrl = 'index.php?option=com_jticketing&view=events&layout=all&filter_events_cat=' . $selected . '&events_to_show=' . $selected . '&filter_location=' . $selected . '&filter_creator=' . $selected . '&online_events' . $selected . '&Itemid=' . $this->allEventsItemid;
				$eventsResetFilterUrl = JUri::root() . substr(JRoute::_($eventsResetFilterUrl), strlen(JUri::base(true)) + 1);
			?>
			<div class="col-xs-12 col-sm-12">
				<a class="pull-right" onclick='window.location.assign("<?php echo $eventsResetFilterUrl;?>")' href="javascript:void(0);">
					<i class="fa fa-repeat" aria-hidden="true"></i>
					<?php echo JText::_('COM_JTICKETING_REST_FILTERS');?>
				</a>
			</div>
		</div>
		<div class="clearfix"></div>
	</form>
<?php
}
