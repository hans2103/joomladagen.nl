<?php
/**
* @package	Jticketing
* @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
* @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
* @link http://www.techjoomla.com
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
require_once JPATH_SITE."/components/com_jticketing/helpers/main.php";
$document =JFactory::getDocument();
$input=JFactory::getApplication()->input;
$user=JFactory::getUser();

if(empty($user->id))
{
	return;
}

$jticketingmainhelper=new jticketingmainhelper();
$com_params=JComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');
$jticketingfrontendhelper = JPATH_ROOT .'/components/com_jticketing/helpers/frontendhelper.php';

if (!class_exists('jticketingfrontendhelper'))
{
	JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
	JLoader::load('jticketingfrontendhelper');
}

// Load assets
$jticketingfrontendhelperclass =new jticketingfrontendhelper;
$jticketingfrontendhelperclass->loadjticketingAssetFiles();
$tjClass = 'JTICKETING_WRAPPER_CLASS ';
$Itemid=$input->get('Itemid','','INT');

if(!empty($Itemid))
{
	$Session=JFactory::getSession();
	$Session->set("JT_Menu_Itemid",$Itemid);
}

if (!empty($eventid))
{
	$eventdata = $jticketingmainhelper->getEventDetails($eventid);
	$eventtickets = $jticketingmainhelper->getEventInfo($eventid);
	$available_tickets = $jticketingmainhelper->getAvailableTickets($eventid);
	$eventowner = $jticketingmainhelper->getEventCreator($eventid);
	$user=JFactory::getUser();
}
?>
	<div class="<?php echo $tjClass.$params->get('moduleclass_sfx'); ?>">
			<div class="row-fluid">
				<div class="tj-list-group">
					<!-- Event Menu List -->
					<!--added for jticketing menu -->
						<ul class="">
						<?php
						if(!empty($eventid))
						{
							$eventlink = '&event='.$eventid;
						}
						else
						{
							$eventlink = "";

						}
						if (empty($eventid) or (($eventowner==$user->id) and !empty($eventid)))
						{

						?>

								<li class="tj-list-group-item">
									<a class="" href="<?php echo JRoute::_(JUri::root() .'index.php?option=com_jticketing&view=allticketsales&Itemid='.$Itemid.$eventlink);?>"><?php 	echo JText::_( 'TICK_SALES'); ?></a>
								</li>
								<li class="tj-list-group-item">
									<a class="" href="<?php echo JRoute::_(JUri::root() .'index.php?option=com_jticketing&view=attendee_list&Itemid='.$Itemid.$eventlink);?>"><?php 	echo JText::_( 'ATTENDEE_LIST'); ?></a>
								</li>

								<li class="tj-list-group-item">
							<?php if($integration==3){ ?>
									<a class="" href="<?php echo JRoute::_(JUri::root() .'index.php?option=com_jticketing&view=mypayouts&Itemid='.$Itemid.$eventlink);?>"><?php echo JText::_( 'MY_PAYOUT');?></a>
							<?php } else{	?>
									<a class="" href="<?php echo JRoute::_(JUri::root() .'index.php?option=com_jticketing&view=mypayouts&Itemid='.$Itemid.$eventlink);?>"><?php echo JText::_( 'MY_PAYOUT');?></a>
							<?php } ?>

								</li>
						<?php
						}
						?>
								<li class="tj-list-group-item">
									<a class="" href="<?php echo JRoute::_(JUri::root() .'index.php?option=com_jticketing&view=orders&layout=my&Itemid='.$Itemid.$eventlink);?>"><?php echo JText::_( 'MY_ORDERS');?></a>
								</li>

								<li class="tj-list-group-item">
									<a class="" href="<?php echo JRoute::_(JUri::root() .'index.php?option=com_jticketing&view=mytickets&Itemid='.$Itemid.$eventlink); ?>" ><?php echo JText::_( 'MY_TICKET');?></a>
								</li>
						</ul>
		</div>
	</div>
	</div>
