<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

jimport( 'joomla.application.module.helper');
$document = JFactory::getDocument();
//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$document->addStyleSheet(JUri::root() . 'media/com_jticketing/css/jticketing.css');
$document->addStyleSheet(JUri::root() . 'modules/mod_jticketing_event/css/jticketing_event.css');
$document->addScript(JUri::root() . 'media/com_jticketing/vendors/js/masonry.pkgd.min.js');

JLoader::import('frontendhelper', JPATH_SITE . '/components/com_jticketing/helpers');
JLoader::import('route', JPATH_SITE . '/components/com_jticketing/helpers');
$jticketingFrontendHelper = new Jticketingfrontendhelper;
$jTRouteHelper = new JTRouteHelper;
$jticketingFrontendHelper->loadjticketingAssetFiles();

$tjClass        = 'JTICKETING_WRAPPER_CLASS ';
JLoader::import('main', JPATH_SITE . '/components/com_jticketing/helpers');
$jticketingmainhelper = new jticketingmainhelper;
$allEventsItemId = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=events&layout=default');

// @model helper object
$modJTicketingHelper = new modJTicketingHelper;
$data                = $modJTicketingHelper->getData($params);
$pin_width           = $params->get('mod_pin_width', '230', 'INT');
$pin_padding         = $params->get('mod_pin_padding', '10', 'INT');
$arraycnt = count($data);
?>
<div id="mod_jticketing_container<?php echo $module->id;?>" class="<?php echo $tjClass.$params->get('moduleclass_sfx'); ?> container-fluid" >
<?php
if ($arraycnt <= 0)
{?>
	<div class="alert alert-warning">
		<?php echo JText::_('MOD_JTICKETING_EVENT_NO_DATA_FOUND');?>
	</div>
<?php
}
else
{
	foreach ($data as $modEventData)
	{
	?>
		<div class="col-sm-3 col-xs-12 jticketing_pin_item">
			<?php
			$eventLink = JUri::root() . substr(
			JRoute::_('index.php?option=com_jticketing&view=event&id=' . $modEventData['event']->id . '&Itemid=' . $allEventsItemId),
			strlen(JUri::base(true)) + 1
			);
			?>
			<div class="jticketing_pin_img">
				<?php
				if(isset($modEventData['image']))
				{
					$imagePath = $modEventData['image'];
				}
				else
				{
					$imagePath = JRoute::_(JUri::base() . 'media/com_jticketing/images/default-event-image.png');
				}
				?>
				<a href="<?php echo $eventLink;?>" title="<?php echo $modEventData['event']->title;?>" style="background:url('<?php echo $imagePath;?>'); background-position: center center; background-size: cover; background-repeat: no-repeat;">
				</a>
			</div>
			<div class="jt-heading">
				<span class="jt-event-ticket-price-text">
					<a href="<?php echo $eventLink;?>" title="<?php echo $modEventData['event']->title;?>">
						<?php
						if (($modEventData['event_max_ticket'] == $modEventData['event_min_ticket']) AND (($modEventData['event_max_ticket'] == 0) AND ($modEventData['event_min_ticket'] == 0)))
						{
						?>
							<strong><?php echo strtoupper(JText::_('MOD_JTICKETING_ONLY_FREE_TICKET_TYPE'));?></strong>
						<?php
						}
						elseif (($modEventData['event_max_ticket'] == $modEventData['event_min_ticket']) AND  (($modEventData['event_max_ticket'] != 0) AND ($modEventData['event_min_ticket'] != 0)))
						{
						?>
							<strong><?php echo $jticketingmainhelper->getFormattedPrice(number_format(($modEventData['event_max_ticket']), 2), $com_params->get('currency'));?></strong>
						<?php
						}
						else
						{
						?>
							<strong>
								<?php
									echo $jticketingmainhelper->getFormattedPrice(number_format(($modEventData['event_min_ticket']), 2), $com_params->get('currency'));
									echo ' - ';
									echo $jticketingmainhelper->getFormattedPrice(number_format(($modEventData['event_max_ticket']), 2), $com_params->get('currency'));
								?>
							</strong>
						<?php
						}
						?>
					</a>
				</span>
			</div>
			<div class="thumbnail">
				<div class="caption">
					<ul class="list-unstyled">
						<li>
							<div>
								<i class="fa fa-calendar" aria-hidden="true"></i>
								<?php
									echo $startDate = JHtml::date($modEventData['event']->startdate, $com_params->get('date_format_show'), true);
								?>
							</div>
						</li>
						<li>
							<?php
							$online = JUri::base() . 'media/com_jticketing/images/online.png';

							if ($modEventData['event']->online_events)
							{?>
								<img src="<?php echo $online; ?>"
								class="img-circle" alt="<?php echo JText::_('MOD_JTICKETING_EVENT_ONLINE')?>"
								title="<?php echo JText::sprintf('MOD_JTICKETING_ONLINE_EVENT', $modEventData['event']->title);?>">
							<?php
							}
							?>
							<b>
								<a href="<?php echo $eventLink;?>" title="<?php echo $modEventData['event']->title;?>">
								<?php
								if (strlen($modEventData['event']->title) > 20)
								{
									echo substr($modEventData['event']->title, 0, 20) . '...';
								}
								else
								{
									 echo $modEventData['event']->title;
								}

								if ($modEventData['event']->featured == 1)
								{
								?>
									<span>
										<i class="fa fa-star pull-right" aria-hidden="true" title="<?php echo JText::sprintf('MOD_JTICKETING_FEATURED_EVENT', $modEventData['event']->title);?>"></i>
									</span>
								<?php
								}
								?>
								</a>
							</b>
						</li>
						<li class="events-pin-location">
							<i class="fa fa-map-marker" aria-hidden="true"></i>
							<?php $location =  $modEventData['event']->location ? $modEventData['event']->location : $modEventData['location'];
							echo substr($location, 0, 20) . '...';
							?>
						</li>
					</ul>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	<?php
	}
}
?>
</div>
<style>
#mod_jticketing_container<?php echo $module->id;?> .jticketing_pin_item { width: <?php echo $pin_width . 'px'; ?> !important; }
</style>

<script type="text/javascript">
	var container = document.querySelector('#mod_jticketing_container<?php echo $module->id;?>');

	var msnry = new Masonry( container, {

	 // options
	 columnWidth: <?php echo $pin_width; ?>,
	 gutter: <?php echo $pin_padding; ?>,
	 itemSelector: '.jticketing_pin_item'
	});

	techjoomla.jQuery(document).ready(function()
	{
		var msnry = new Masonry( container, {
		 // options
		 columnWidth: <?php echo $pin_width; ?>,
		 gutter: <?php echo $pin_padding; ?>,
		 itemSelector: '.jticketing_pin_item'
		});

		setTimeout(function(){
			var container = document.querySelector('#container');
			var msnry = new Masonry( container, {
			  // options
			columnWidth: <?php echo $pin_width; ?>,
			gutter: <?php echo $pin_padding; ?>,
			itemSelector: '.jticketing_pin_item'
			});
		},500);

		setTimeout(function(){
			var container = document.querySelector('#container');
			var msnry = new Masonry( container, {
			  // options
			 columnWidth: <?php echo $pin_width; ?>,
			 gutter: <?php echo $pin_padding; ?>,
			 itemSelector: '.jticketing_pin_item'
			});
		},1000);
	});
</script>
