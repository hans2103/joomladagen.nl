<?php
/**
 * @version		3.2.1
 * @package		ticketmaster
 * @copyright	Copyright © 2009 - All rights reserved.
 * @license		GNU General Public License, version 2 
 * @author		Robert Dam
 * @author mail	info@rd-media.org
 * @website		http://www.rd-media.org   
 */

## no direct access
defined('_JEXEC') or die('Restricted access');

$session =& JFactory::getSession();
## Gettig the orderid if there is one.
$redirect = $session->get('redirect');

## Check if the user is logged in.
$user = & JFactory::getUser();

## Adding the AJAX part
JHTML::script('ajax.js','components/com_ticketmaster/assets/', true);

$app = JFactory::getApplication();
$pathway = $app->getPathway();
$pathway->addItem(JText::_( 'COM_TICKETMASTER_PROFILE' ), 'index.php?option=com_ticketmaster&view=profile');
$pathway->addItem(JText::_( 'COM_TICKETMASTER_REVIEW_PROFILE' ));

## Get document type and add it.
$document = JFactory::getDocument();

$cssfile = 'components/com_ticketmaster/assets/css-overrides/checkout.css';

## Check if there is a css override.
if (file_exists($cssfile)) {
    $document->addStyleSheet( $cssfile );
} else {
    $document->addStyleSheet( 'components/com_ticketmaster/assets/css/checkout.css' );
}

$document->addStyleSheet( 'components/com_ticketmaster/assets/css/component.css' );
$document->setTitle( JText::_( 'COM_TICKETMASTER_REVIEW_PROFILE' ) );
$document->addScript( JURI::root(true).'/components/com_ticketmaster/assets/javascripts/moovalid.js');

if ($this->config->load_jquery == 1) {
	$document->addScript('http://code.jquery.com/jquery-latest.js');
}elseif ($this->config->load_jquery == 2) {
	$document->addScript( JURI::root(true).'/administrator/components/com_ticketmaster/assets/jquery/jquery.js');
}


if ($this->data->clientid != ''){
	$update = JText::_( 'COM_TICKETMASTER_REVIEW_PROFILE' );
	$formtext = JText::_( 'COM_TICKETMASTER_REVIEW_PROFILE' );
	$button = JText::_( 'COM_TICKETMASTER_REVIEW_PROFILE_CONTINUE' );
	$clientupdate = 1;
}else{
	$update = JText::_( 'COM_TICKETMASTER_MISSING_PROFILE' );
	$formtext = JText::_( 'COM_TICKETMASTER_MISSING_PROFILE' );
	$button = JText::_( 'COM_TICKETMASTER_CREATE_MY__PROFILE' );
	$clientupdate = 0;
}

## Check if this is Joomla 2.5 or 3.0.+
$isJ30 = version_compare(JVERSION, '3.0.0', 'ge');

if(!$isJ30) {

	JHTML::_( 'behavior.mootools' );

	if($this->config->load_bootstrap == 1){
		## Adding mootools for J!2.5
		JHTML::_('behavior.modal');
		## Include the tooltip behaviour.
		JHTML::_('behavior.tooltip', '.hasTip');

		$document->addStyleSheet( JURI::root(true).'/administrator/components/com_ticketmaster/assets/bootstrap/css/bootstrap.css' ); 
		$document->addScript( JURI::root(true).'/administrator/components/com_ticketmaster/assets/bootstrap/js/bootstrap.js');
		$button = 'btn';
	}else{	
		$document->addStyleSheet( 'components/com_ticketmaster/assets/css/bootstrap.css' );
		$button = 'button_rdticketmaster';
	}	
}else{

	## We are in J3, load the bootstrap!
	jimport('joomla.html.html.bootstrap');
	$button = 'btn';
	
}
?>


<h2><?php echo $formtext; ?></h2>	

<p style="margin-bottom:25px;"><?php echo JText::_('COM_TICKETMASTER_CHECK_PROFILE_DESC'); ?></p>

<form id="general" action="index.php" method="post">

	<?php if($this->config->show_secondaddress != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_ADDRESS2' ); ?></div>
		  <div class="span8"><input name="address2" type="text" id="address2" class="inputbox" value="<?php echo $this->data->address2; ?>" size="25" /></div>
		</div>		
	<?php } ?> 

	<?php if($this->config->show_thirdaddress != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_ADDRESS3' ); ?></div>
		  <div class="span8"><input name="address3" type="text" id="address3" class="inputbox" value="<?php echo $this->data->address3; ?>" size="25" /></div>
		</div>		
	<?php } ?> 	

	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_GENDER' ); ?></div>
	  <div class="span8"><?php echo $this->lists['gender']; ?></div>
	</div>
	
	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_FIRSTNAME' ); ?></div>
	  <div class="span8"><input name="firstname" type="text" id="firstname" class="inputbox" value="<?php echo $this->data->firstname; ?>" size="25" /></div>
	</div>	
	
	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_LASTNAME' ); ?></div>
	  <div class="span8"><input name="name" type="text" id="name" class="inputbox" value="<?php echo $this->data->name; ?>" size="25" /></div>
	</div>			

	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_ADDRESS' ); ?></div>
	  <div class="span8"><input name="address" type="text" id="address" class="inputbox" value="<?php echo $this->data->address; ?>" size="25" /></div>
	</div>		

	<?php if($this->config->show_zipcode != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_ZIPCODE' ); ?></div>
		  <div class="span8"><input name="zipcode" type="text" id="zipcode" class="inputbox" value="<?php echo $this->data->zipcode; ?>" size="25" /></div>
		</div>		
	<?php } ?> 
	
	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_CITY' ); ?></div>
	  <div class="span8"><input name="city" type="text" id="city" class="inputbox" value="<?php echo $this->data->city; ?>" size="25" /></div>
	</div>				

	<?php if($this->config->show_country != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_COUNTRY' ); ?></div>
		  <div class="span8"><?php echo $this->lists['country']; ?></div>
		</div>		
	<?php } ?> 	

	<?php if($this->config->show_phone != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_PHONE' ); ?></div>
		  <div class="span8"><input name="phonenumber" type="text" id="phonenumber" class="inputbox" value="<?php echo $this->data->phonenumber; ?>" size="25" /></div>
		</div>		
	<?php } ?> 	
	
	<?php if($this->config->show_birthday != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_BIRTHDAY' ); ?></div>
		  <div class="span8"><?php echo $this->lists['day']; ?>&nbsp;<?php echo $this->lists['month']; ?>&nbsp;<?php echo $this->lists['year']; ?></div>
		</div>		
	<?php } ?> 			

	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_EMAIL' ); ?></div>
	  <div class="span8"><input name="emailaddress" type="text" id="emailaddress" class="inputbox" value="<?php echo $this->data->emailaddress; ?>" size="25" /></div>
	</div>	
	
	<div class="row-fluid">
	  <div class="span4"></div>
	  <div class="span8"><input type="submit" value="<?php echo JText::_('COM_TICKETMASTER_PLEASE_PROCESS_ORDER'); ?>" class="<?php echo $button; ?> btn-success"></div>
	</div>			

	

	<input type="hidden" name="option" value="com_ticketmaster" />
	<input type="hidden" name="controller" value="profile" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="userid" value="<?php echo $user->id; ?>" />
	<input type="hidden" name="gid" value="0" /> 
	<?php if ($clientupdate == 1){ ?>
	    <input type="hidden" name="clientid" value="<?php echo $this->data->clientid; ?>" />
	<?php } ?> 
	<?php echo JHTML::_( 'form.token' ); ?>   
	    
</form> 
