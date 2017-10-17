<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.utilities.date');
jimport('joomla.filter.output');
jimport('joomla.html.html.bootstrap');

require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
$jticketingMainHelper     = new jticketingmainhelper;
$jticketingFrontendHelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

if (!class_exists('jticketingfrontendhelper'))
{
	JLoader::register('jticketingfrontendhelper', $jticketingFrontendHelper);
	JLoader::load('jticketingfrontendhelper');
}

// Load assets
$jticketingFrontendHelperClass = new jticketingfrontendhelper;
$jticketingFrontendHelperClass->loadjticketingAssetFiles();
$document           = JFactory::getDocument();
$bootstrapClass     = "";
$tableClass         = "admintable";
$buttonClass        = "button";
$buttonClassPrimary = "button";
$comParams          = JComponentHelper::getParams('com_jticketing');
$integration        = $comParams->get('integration');
$currency           = $comParams->get('currency');
$eventOwnerBuy      = $comParams->get('eventowner_buy');
$module             = JModuleHelper::getModule('mod_jticketing_buy');
$moduleParams       = json_decode($module->params);
$tableClass         = "admintable table table-striped table-hover";
$buttonClass        = "btn";
$buttonClassPrimary = "btn btn-default btn-primary";
$input              = JFactory::getApplication()->input;
$document           = JFactory::getDocument();
$user               = JFactory::getUser();
$session            = JFactory::getSession();
$backlink           = JUri::current();
$session->set('backlink', $backlink);
$eventid            = '';
$eventid            = $input->get('eventid', '', 'INT');

if ($integration == 3)
{
	$eventid = $input->get('eventid');

	if (!$eventid)
	{
		$view = $input->get('view');
		$task = $input->get('task');

		if ($task == 'icalevent.detail')
		{
			$eventid = $input->get('evid', '', 'INT');
		}
		elseif ($view == 'icalrepeat' or $task == 'icalrepeat.detail')
		{
			$rp_id = $input->get('evid', '', 'INT');

			if ($rp_id)
			{
				$eventid = $jticketingMainHelper->getEventDetailsid($rp_id);
			}
			else
			{
				return;
			}
		}
		else
		{
			return;
		}
	}
	else
	{
		$eventid = $jticketingMainHelper->getEventDetailsid($eventid);
	}
}

if ($integration == 2 || $integration == 4)
{
	$eventid = $input->get('id', '', 'INT');
}

$eventdata = $jticketingMainHelper->getEventDetails($eventid);
$eventTickets = $jticketingMainHelper->getEventInfo($eventid);

if (!$eventTickets)
{
	return false;
}

if ($eventTickets[0]->enddate == '0000-00-00')
{
	$eventTickets[0]->enddate = $eventTickets[0]->startdate;
}

$todaysDate = date("Y-m-d");
$eventTime   = strtotime($eventTickets[0]->enddate);
$todayTime   = strtotime($todaysDate);

require_once JPATH_SITE . "/components/com_jticketing/models/events.php";
$JticketingModelEvents = new JticketingModelEvents;
$eventData = $JticketingModelEvents->getTJEventDetails($eventid);

if ($eventTime >= $todayTime)
{
	$valid = "yes";
}
else
{
	$valid = "no";
}

if ($valid == "yes")
{
	$availableTickets  = '';
	$availableTickets  = $jticketingMainHelper->getAvailableTickets($eventid);
	$eventOwner         = $jticketingMainHelper->getEventCreator($eventid);
	$isPaidEvent        = $jticketingMainHelper->isPaidEvent($eventid);

	require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
	$jticketingMainHelper   = new jticketingmainhelper;
	$eventDetails           = $jticketingMainHelper->getAllEventDetails($eventid);
	$isEventbought          = $jticketingMainHelper->isEventbought($eventid, $user->id);
	$js_key = '
	var availabletickets="' . $availableTickets . '";
	var ispaidevent="' . $isPaidEvent . '";
	var isEventbought="' . $isEventbought . '";

	function calprice(val,tp)
	{
		var tprice = val*tp;
		document.getElementById("totalprice").value = tprice;
		document.getElementById("totalpricespan").innerHTML = tprice;

	}
	function checkforalpha(el)
		{
			var i =0 ;
			for(i=0;i<el.value.length;i++){
			  if((el.value.charCodeAt(i) > 64 && el.value.charCodeAt(i) < 92) || (el.value.charCodeAt(i) > 96 && el.value.charCodeAt(i) < 123))
			  { alert("Please Enter Numerics"); el.value = el.value.substring(0,i); break;}

			}
		}

	';

	// If Event with unlimited seats then ignore if all event type avalaible count is  0 otherwise hide module
	$paidEvenType    = 0;
	$showBuyButton = $jticketingMainHelper->showbuybutton($eventid);

	if ($integration == 1 || $integration == 4)
	{
		if ($integration == 1)
		{
			$xml       = JFactory::getXML(JPATH_ROOT . '/administrator/components/com_community/community.xml');

			if ($xml->version)
			{
				$jsversion = (float) $xml->version;
			}
		}

		if (empty($user->id) and $paidEvenType == 1 and $integration == 1)
		{
			$js_key .= 'jQuery(document).ready(function(){	 jQuery(".cEvent-Rsvp").html("' . JText::_('TICKET_RSVP_BUY') . '")	});';
			$js_key .= 'jQuery(document).ready(function(){	 jQuery(".joms-focus__actions--desktop a:first").remove()	});';
			$js_key .= 'jQuery(document).ready(function(){	 jQuery(".joms-focus__actions--desktop :last").before("' . JText::_('TICKET_RSVP_BUY') . '")	});';
			$document->addScriptDeclaration($js_key);
		}

		if (!$showBuyButton and $paidEvenType == 1)
		{
			$js_key .= 'jQuery(document).ready(function(){	 jQuery(".cEvent-Rsvp").html("' . JText::_('TICKET_RSVP_BUY') . '")	});';
			$js_key .= 'jQuery(document).ready(function(){	 jQuery(".joms-focus__actions--desktop a:first").remove()	});';
			$js_key .= 'jQuery(document).ready(function(){	 jQuery(".joms-focus__actions--desktop :last").before("' . JText::_('TICKET_RSVP_BUY') . '")	});';
		}
		elseif ($showBuyButton and empty($isEventbought))
		{
			if ($integration == 4)
			{
				$js_key .= 'jQuery(document).ready(function(){	 jQuery(".media-meta").html("' . JText::_('TICKET_RSVP_BUY') . '")	});';
			}

			elseif ($integration == 1)
			{
				$js_key .= 'jQuery(document).ready(function(){	 jQuery(".cEvent-Rsvp").html("' . JText::_('TICKET_RSVP_BUY') . '")	});';
				$js_key .= 'jQuery(document).ready(function(){	 jQuery(".joms-focus__actions--desktop a:first").remove()	});';
				$js_key .= 'jQuery(document).ready(function(){	 jQuery(".joms-focus__actions--desktop :last").before("' . JText::_('TICKET_RSVP_BUY') . '")	});';
			}
		}

		$document->addScriptDeclaration($js_key);
	}

	if (!$integration == 3)
	{
		if ($eventOwner == $user->id and !empty($eventOwner) and !empty($user->id))
		{
			if ($eventOwnerBuy == 0)
			{
				echo '<div  class="cModule	app-box-content"><b>' . JText::_('MOD_JTICKETING_BUY_EVENT_OWNER_CANT_BUY') . '</b></div>';

				return;
			}
		}
	}

	if (($integration == 1) OR ($integration == 3))
	{
		if (!$showBuyButton and $isPaidEvent == 1)
		{
			echo '<div  class="cModule	app-box-content">
			<img class="soldout" src="' . JUri::base() . 'modules/mod_jticketing_buy/images/sold.png" />
			<br/>
			<b>' . JText::_('MOD_JTICKETING_BUY_TICKET_UNAVAILABLE') . '</b>
			</div>';

			return;
		}
	}

	$countExists = count($eventdata);

	if ($countExists == 1)
	{
		if ($eventdata[0]->hide_ticket_type == 1)
		{
			return;
		}
	}
	elseif($countExists < 1)
	{
		return;
	}

	// If Event with unlimited seats then ignore if all event type avalaible count is  0 otherwise hide module
	if ($showBuyButton)
	{
		$loadBootstrap = $comParams->get('load_bootstrap');

		if ($loadBootstrap)
		{
			// Load bootstrap CSS and JS.
			JHtml::_('bootstrap.loadcss');
			JHtml::_('bootstrap.framework');
		}
		?>

		<div  class="cModule cEvent-Extra app-box <?php		echo JTICKETING_WRAPPER_CLASS . " " . $params->get('moduleclass_sfx');?>">
			<div class="">
				<div class="app-box-content">
					<div class="row small">
						<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
							<strong>
								<?php echo JText::_('MOD_JTICKETING_BUY_TICKET_TYPE_TITLE');?>
							</strong>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 center">
							<strong>
								<?php echo JText::_('MOD_JTICKETING_BUY_TICKET_TYPE_PRICE');?>
							</strong>
						</div>
						<?php
						if (!empty($moduleParams->show_available))
						{
							?>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 center">
								<strong>
									<?php echo JText::_('MOD_JTICKETING_BUY_TICKET_TYPE_AVAILABLE');?>
								</strong>
							</div>
							<?php
						}
						?>
					</div>
					<hr class="hr hr-condensed"/>
					<?php

					foreach ($eventdata as $type)
					{
						if (((isset($type->count) and $type->count > 0 and $type->unlimited_seats == 0) or $type->unlimited_seats == 1) and !($type->hide_ticket_type))
						{
							?>
							<div class="row small">
								<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
									<?php echo $type->title;?>
								</div>
								<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 center">
									<?php
									if ($type->price == 0)
									{
										echo JText::_('MOD_JTICKETING_BUY_FREE_TICKET');
									}
									else
									{
										echo $jticketingMainHelper->getFromattedPrice(number_format(($type->price), 2), $currency);
									}
									?>
								</div>

								<?php
								if (!empty($moduleParams->show_available))
								{
									?>
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 center">
										<?php
										if ($type->unlimited_seats)
										{
											echo JText::_('MOD_JTICKETING_BUY_UNLIM_SEATS');
										}
										else
										{
											echo $type->count . '/' . $type->available;
										}
										?>
									</div>
									<?php
								}
								?>
							</div>
							<hr class="hr hr-condensed"/>
							<?php
						}
					}

					if ($integration == 2)
					{
						// Check if button to show.
						if (($showBuyButton && $eventDetails->online_events != 1) || ($showBuyButton && $eventDetails->online_events == 1 && empty($isEventbought)))
						{
							?>
							<div class="center app-box-footer">
								<?php
								if (array_key_exists('enrol_button', $eventData))
								{
									echo $eventData['enrol_button'];
								}

								if (array_key_exists('buy_button', $eventData))
								{
									echo $eventData['buy_button'];
								}

								elseif (array_key_exists('adobe_connect', $eventData))
								{
									echo $eventData['adobe_connect'];
								}

								if (array_key_exists('guest_meeting_btn', $eventData))
								{
									echo $eventData['guest_meeting_btn'];
								}
								?>
							</div>
						<?php
						}
					}
					elseif ($showBuyButton)
					{
						?>
						<div class="center app-box-footer">
							<?php
							if (array_key_exists('enrol_button', $eventData))
							{
								echo $eventData['enrol_button'];
							}

							if (array_key_exists('buy_button', $eventData))
							{
								echo $eventData['buy_button'];
							}
							?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}
}
elseif (isset($isPaidEvent) and $isPaidEvent == 1)
{
	echo '<div  class="cModule	app-box-content">
			<b>' . JText::_('MOD_JTICKETING_BUY_EVENT_CANT_BUY') . '</b>
		</div>';

	return;
}
