<?php 
/************************************************************
 * @version			ticketmaster 2.5.5
 * @package			com_ticketmaster
 * @copyright		Copyright � 2009 - All rights reserved.
 * @license			GNU/GPL
 * @author			Robert Dam
 * @author mail		info@rd-media.org
 * @website			http://www.rd-media.org
 *************************************************************/

## Check if the file is included in the Joomla Framework
defined('_JEXEC') or die ('No Acces to this file!');

## Check if the user is logged in.
$user =  JFactory::getUser();

$app = JFactory::getApplication();
$pathway = $app->getPathway();
$pathway->addItem(JText::_( 'COM_TICKETMASTER_CART_DETAILS' ), 'index.php?option=com_ticketmaster&view=cart');
$pathway->addItem(JText::_( 'COM_TICKETMASTER_LOGIN_OR_CREATE_ACCOUNT' ));

$info = $app->getUserState('com_ticketmaster.registration');

## Get document type and add it.
$document = &JFactory::getDocument();

$cssfile = 'components/com_ticketmaster/assets/css-overrides/checkout.css';

## Check if there is a css override.
if (file_exists($cssfile)) {
    $document->addStyleSheet( $cssfile );
} else {
    $document->addStyleSheet( 'components/com_ticketmaster/assets/css/checkout.css' );
}

$document->addStyleSheet( 'components/com_ticketmaster/assets/css/component.css' );
$document->setTitle( JText::_( 'COM_TICKETMASTER_LOGIN_OR_CREATE_ACCOUNT' ) );
$document->addScript( JURI::root(true).'/components/com_ticketmaster/assets/javascripts/moovalid.js');

if ($this->config->load_jquery == 1) {
	$document->addScript('//code.jquery.com/jquery-latest.js');
}elseif ($this->config->load_jquery == 2) {
	$document->addScript( JURI::root(true).'/administrator/components/com_ticketmaster/assets/jquery/jquery.js');
}


## Check if this is Joomla 2.5 or 3.0.+
$isJ30 = version_compare(JVERSION, '3.0.0', 'ge');

if(!$isJ30) {

	if($this->config->load_bootstrap == 1){
		$document->addStyleSheet( JURI::root(true).'/administrator/components/com_ticketmaster/assets/bootstrap/css/bootstrap.css' ); 
		$document->addScript( JURI::root(true).'/administrator/components/com_ticketmaster/assets/bootstrap/js/bootstrap.js');
		$button = 'btn';
	}else{	
		$document->addStyleSheet( 'components/com_ticketmaster/assets/css/bootstrap.css' );
		$button = 'button_rdticketmaster';
	}	
	
}else{

	## We are in J3, load the bootstrap!
	JHtml::_('bootstrap.framework');
	jimport('joomla.html.html.bootstrap');
	$button = 'btn';
	
}


### Create a custom password:
## Generate a random character string for the password.
function password($length = 7, $chars = '1234567890abcdefghijklmnopqrstuvwABCDEFGHIJKLMNOPQRSTUVWXYZ'){
	## Length of character list
	$chars_length = (strlen($chars) - 1);
	## Start our string
	$string = $chars{rand(0, $chars_length)};
	## Generate random string
	for ($i = 1; $i < $length; $i = strlen($string)){
		## Grab a random character from our list
		$r = $chars{rand(0, $chars_length)};
		## Make sure the same two characters don't appear next to each other
		if ($r != $string{$i - 1}) $string .=  $r;
	}
	## Return the string
	return $string;
}

## link to show forgor username/password:

$forgot_pass = JRoute::_( 'index.php?option=com_users&view=reset&tmpl=component');
$forgot_user = JRoute::_( 'index.php?option=com_users&view=remind&tmpl=component');
?>

<script language="javascript">

	var JQ = jQuery.noConflict();

	JQ(document).ready(function() {	
		
		JQ( ".toggleTrigger" ).click(function(event) {
			event.preventDefault();
	  		JQ( ".toggleRegistration" ).toggle();
		});

	});
	
</script>

<div style = "margin-top:1px; margin-bottom: 70px;">

<h2><?php echo JText::_('COM_TICKETMASTER_CREATEACCOUNT_NOW'); ?></h2>

<h3 class="acc_trigger"><a class="toggleTrigger" href="#"><?php echo JText::_('COM_TICKETMASTER_LOGIN'); ?></a></h3>
<div class="toggleRegistration" style="margin-left:30px; margin-bottom:20px;">
	
	<p style="margin-bottom:15px;"><?php echo JText::_('COM_TICKETMASTER_LOGIN_DESC'); ?></p>
	
	<form action="<?php echo JRoute::_( 'index.php'); ?>" method="post" name="loginForm">
	
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_USERNAME' ); ?></div>
		  <div class="span8">
	              <input type="text" name="username" size="24" alt="<?php echo JText::_( 'Username' ); ?>" 
	                     value="<?php echo JText::_( 'Username' ); ?>" 
	                     onblur="if(this.value=='') this.value='<?php echo JText::_( 'Username' ); ?>';" 
	                     onfocus="if(this.value=='<?php echo JText::_( 'Username' ); ?>') this.value='';" class="inputbox" />  	  
		  </div>
		</div>
		
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_PASSWORD' ); ?></div>
		  <div class="span8">
	                 <input type="password" name="password" size="24" alt="<?php echo JText::_( 'Password' ); ?>" 
	                        value="<?php echo JText::_( 'Password' ); ?>" 
	                        onblur="if(this.value=='') this.value='<?php echo JText::_( 'Password' ); ?>';" 
	                        onfocus="if(this.value=='<?php echo JText::_( 'Password' ); ?>') this.value='';" class="inputbox" />    
		  </div>
		</div>
		
		<div class="row-fluid">
		  <div class="span4"></div>
		  <div class="span8"><input type="submit" value="<?php echo JText::_('COM_TICKETMASTER_LOGIN'); ?>" class="<?php echo $button; ?>"> </div>
		</div>
	
    <input type="hidden" name="option" value="com_ticketmaster" />
    <input type="hidden" name="controller" value="checkout" />
    <input type="hidden" name="task" value="login" />
	<?php echo JHTML::_( 'form.token' ); ?>
	</form>   
	
	<div class="row-fluid">
	  <div class="span4"></div>
	  <div class="span8">
			<a href="<?php echo $forgot_pass; ?>" onclick="window.open('<?php echo $forgot_pass; ?>','jevensternaam',
			'width=800,height=400,scrollbars=no,toolbar=no,location=yes'); return false"><?php echo JText::_('COM_TICKETMASTER_FORGOT_PASS'); ?></a> || 
			<a href="<?php echo $forgot_user; ?>" onclick="window.open('<?php echo $forgot_user; ?>','jevensternaam',
			'width=800,height=400,scrollbars=no,toolbar=no,location=yes'); return false"><?php echo JText::_('COM_TICKETMASTER_FORGOT_USERNAME'); ?></a>	  
	  </div>
	</div>	
							
	
</div>

<?php if ($useractivation ==1){ ?>

<h3 class="acc_trigger"><a class="toggleTriggerActivation" href="#"><?php echo JText::_('COM_TICKETMASTER_CREATEACCOUNT_NOW'); ?></a></h3>
	
	<p><?php echo JText::_('COM_TICKETMASTER_ACTIVATION_DESC'); ?></p>
	
	<form action="<?php echo JRoute::_( 'index.php'); ?>" method="post" name="activateForm">

		<div class="row-fluid">
	  		<div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_ENTER_ACTIVATION_CODE' ); ?></div>
	  		<div class="span8">
                   <input type="text" name="token" size="24" alt="<?php echo JText::_( 'COM_TICKETMASTER_ACTIVATION_CODE' ); ?>" 
                   value="<?php echo JText::_( 'COM_TICKETMASTER_ACTIVATION_CODE' ); ?>" 
                   onblur="if(this.value=='') this.value='<?php echo JText::_( 'Password' ); ?>';" 
                   onfocus="if(this.value=='<?php echo JText::_( 'COM_TICKETMASTER_ACTIVATION_CODE' ); ?>') this.value='';" class="inputbox" />      
			</div>
		</div>

		<div class="row-fluid">
	  		<div class="span4"></div>
	  		<div class="span8"><input type="submit" value="<?php echo JText::_('COM_TICKETMASTER_ACTIVATE'); ?>" class="<?php echo $button; ?>"></div>
		</div>

    <input type="hidden" name="option" value="com_ticketmaster" />
    <input type="hidden" name="controller" value="checkout" />
    <input type="hidden" name="task" value="activate" />
	<?php echo JHTML::_( 'form.token' ); ?>
	</form> 

<?php } ?>


<h3 class="acc_trigger"><a class="toggleTrigger" href="#"><?php echo JText::_('COM_TICKETMASTER_CREATEACCOUNT_NOW'); ?></a></h3>
<div class="toggleRegistration" style="margin-left:30px; ">
	
	<p style="margin-bottom:15px;"><?php echo JText::_('COM_TICKETMASTER_FREE_ACCOUNT'); ?></p>
	
	<form id="general" action="index.php?option=com_ticketmaster&controller=checkout" method="post" name="general">
	
	<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_ADDRESS2' ); ?></div>
		  <div class="span8"><input name="address2" type="text" id="address2" class="inputbox" value="<?php echo $info['address2']; ?>" size="25" /></div>
		</div>		

		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_ADDRESS3' ); ?></div>
		  <div class="span8"><input name="address3" type="text" id="address3" class="inputbox" value="<?php echo $info['address3']; ?>" size="25" /></div>
		</div>		
	
	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_GENDER' ); ?>*</div>
	  <div class="span8"><?php echo $this->lists['gender']; ?></div>
	</div>
	
	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_FIRSTNAME' ); ?>*</div>
	  <div class="span8"><input name="firstname" type="text" id="firstname" class="inputbox" value="<?php echo $info['firstname']; ?>" size="25" /></div>
	</div>	
	
	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_LASTNAME' ); ?>*</div>
	  <div class="span8"><input name="name" type="text" id="name" class="inputbox" value="<?php echo $info['name']; ?>" size="25" /></div>
	</div>			

	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_ADDRESS' ); ?>*</div>
	  <div class="span8"><input name="address" type="text" id="address" class="inputbox" value="<?php echo $info['address']; ?>" size="25" /></div>
	</div>		

	<?php if($this->config->show_zipcode != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_ZIPCODE' ); ?>*</div>
		  <div class="span8"><input name="zipcode" type="text" id="zipcode" class="inputbox" value="<?php echo $info['zipcode']; ?>" size="25" /></div>
		</div>		
	<?php } ?> 
	
	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_CITY' ); ?>*</div>
	  <div class="span8"><input name="city" type="text" id="city" class="inputbox" value="<?php echo $info['city']; ?>" size="25" /></div>
	</div>				

	<?php if($this->config->show_country != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_COUNTRY' ); ?>*</div>
		  <div class="span8"><?php echo $this->lists['country']; ?></div>
		</div>		
	<?php } ?> 	

	<?php if($this->config->show_phone != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_PHONE' ); ?>*</div>
		  <div class="span8"><input name="phonenumber" type="text" id="phonenumber" class="inputbox" value="<?php echo $info['phonenumber']; ?>" size="25" /></div>
		</div>		
	<?php } ?> 	
	
	<?php if($this->config->show_birthday != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_BIRTHDAY' ); ?></div>
		  <div class="span8"><?php echo $this->lists['day']; ?>&nbsp;<?php echo $this->lists['month']; ?>&nbsp;<?php echo $this->lists['year']; ?></div>
		</div>		
	<?php } ?> 			

	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_EMAIL' ); ?>*</div>
	  <div class="span8"><input name="emailaddress" type="text" id="emailaddress" class="inputbox" value="<?php echo $info['emailaddress']; ?>" size="25" /></div>
	</div>		

	<div class="row-fluid">
	  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_RETYPE_EMAIL' ); ?>*</div>
	  <div class="span8"><input name="email2" type="text" id="email2" class="inputbox" size="25" value="<?php echo $info['email2']; ?>"/></div>
	</div>
		
	<?php if($this->config->show_mailchimps != 0 ){ ?>
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_NEWSLETTER_SIGNUP' ); ?></div>
		  <div class="span8">
                <input type="radio" name="emailUpdates" value="Yes" /> <?php echo JText::_( 'COM_TICKETMASTER_YES' ); ?>
                <input type="radio" name="emailUpdates" value="No" checked="checked" /> <?php echo JText::_( 'COM_TICKETMASTER_NO' ); ?>		  
		  </div>
		</div>
	<?php } ?>	
	
	<?php if($this->config->auto_username == 1 ){ ?>	
		
		<div class="row-fluid">
		  <div class="span4"></div>
		  <div class="span8"><input type="submit" value="<?php echo JText::_('COM_TICKETMASTER_REGISTER_NOW'); ?>" class="<?php echo $button; ?>"></div>
		</div>	
		
	<?php } else {?>
	
		<h2><?php echo JText::_('COM_TICKETMASTER_REGISTER_USERINFO'); ?></h2> 

		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_USERNAME' ); ?>*</div>
		  <div class="span8"><input name="username" type="text" id="username"  class="inputbox" value="<?php echo $info['username']; ?>" size="25" /></div>
		</div>			

		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_PASSWORD' ); ?>*</div>
		  <div class="span8"><input name="password" type="password" id="password" class="inputbox" value="" size="25" /></div>
		</div>
		
		<div class="row-fluid">
		  <div class="span4"><?php echo JText::_( 'COM_TICKETMASTER_YOUR_PASSWORD_2' ); ?>*</div>
		  <div class="span8"><input name="password2" type="password" id="password2" class="inputbox" value="" size="25" /></div>
		</div>	
		
		<div class="row-fluid">
		  <div class="span4"></div>
		  <div class="span8"><input type="submit" value="<?php echo JText::_('COM_TICKETMASTER_REGISTER_NOW'); ?>" class="<?php echo $button; ?>"></div>
		</div>									
		
	 <?php } ?>	
	 
        <div style="clear:both; margin-top:10px; padding-top:15px; margin-bottom:5px;">&nbsp;</div>
 
		<?php 
		$params = JComponentHelper::getParams('com_users');
		
		## Check what type of registration it is in config.
		$useractivation = $params->get('useractivation');
		?>
        
        <?php if ($useractivation ==1){ ?>
        	
            <?php if(!$isJ30) { ?>
                <div style="padding:5px; border:1px #FF0000 solid; margin-bottom: 15px; font-weight:bold; width:75%; color:#FF0000; text-align:center;">
                    <?php echo JText::_('COM_TICKETMASTER_FREE_ACCOUNT_ACTIVATION_INFO'); ?>
                </div>
            <?php }else{ ?>
                <div class="alert alert-block">
                  <?php echo JText::_('COM_TICKETMASTER_FREE_ACCOUNT_ACTIVATION_INFO'); ?>
                </div>            	
            <?php } ?>
        
		<?php } ?> 	 

        <?php if($this->config->show_country == 0 ){ ?>
        	<input type="hidden" id="country_id" name="country_id" value="1" />
        <?php } ?> 
        <?php if($this->config->show_birthday == 0 ){ ?>
        	<input type="hidden" id="birthday" name="birthday" value="1910-01-01" />
        <?php } ?>            
        <input type="hidden" name="option" value="com_ticketmaster" />
        <input type="hidden" name="controller" value="checkout" />
        <input type="hidden" name="task" value="save" />  
        <?php echo JHTML::_( 'form.token' ); ?>     
        
        </form>		
		
</div>

</div>



<?php 
$params = JComponentHelper::getParams('com_users');

## Check what type of registration it is in config.
$useractivation = $params->get('useractivation');

if ($useractivation ==1){

?>
<h3 class="acc_trigger"><a href="#"><?php echo JText::_('COM_TICKETMASTER_ACTIVATE_ACCOUNT'); ?></a></h3>
<div class="acc_container">
    <div class="block">
		
        <form action="<?php echo JRoute::_( 'index.php'); ?>" method="post" name="activateForm">
        <?php echo JText::_('COM_TICKETMASTER_ACTIVATION_DESC'); ?>

        <div style="width:75%; margin-top:20px;">
            <table class="table">               
                    
                <tr>
                    <td width="40%"><?php echo JText::_( 'COM_TICKETMASTER_ENTER_ACTIVATION_CODE' ); ?></td>
                    <td width="60%">
                        <input type="text" name="token" size="24" alt="<?php echo JText::_( 'COM_TICKETMASTER_ACTIVATION_CODE' ); ?>" 
                        value="<?php echo JText::_( 'COM_TICKETMASTER_ACTIVATION_CODE' ); ?>" 
                        onblur="if(this.value=='') this.value='<?php echo JText::_( 'Password' ); ?>';" 
                        onfocus="if(this.value=='<?php echo JText::_( 'COM_TICKETMASTER_ACTIVATION_CODE' ); ?>') this.value='';" class="inputbox" />                
                    </td>
                </tr>  
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value="<?php echo JText::_('COM_TICKETMASTER_ACTIVATE'); ?>" class="<?php echo $button; ?>"></td>
                </tr>                                           
            
            </table>
        </div>
      
        <input type="hidden" name="option" value="com_ticketmaster" />
        <input type="hidden" name="controller" value="checkout" />
        <input type="hidden" name="task" value="activate" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form> 
                  
    </div>
</div>

<?php } ?>


