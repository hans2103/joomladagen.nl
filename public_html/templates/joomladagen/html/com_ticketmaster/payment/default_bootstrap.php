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

$isJ30 = version_compare(JVERSION, '3.0.0', 'ge');

$app = JFactory::getApplication();
$pathway = $app->getPathway();
$pathway->addItem(JText::_( 'COM_TICKETMASTER_EVENTS' ), 'index.php?option=com_ticketmaster');
$pathway->addItem(JText::_( 'COM_TICKETMASTER_CART' ), 'index.php?option=com_ticketmaster&view=cart');
$pathway->addItem(JText::_( 'COM_TICKETMASTER_PAYMENTPAGE' ));

## Obtain user information.
$user = & JFactory::getUser();
$userid = $user->id;
## Get document type and add it.
$document = &JFactory::getDocument();
$document->addStyleSheet( 'components/com_ticketmaster/assets/component.css' );

$cssfile = 'components/com_ticketmaster/assets/css-overrides/cart.css';

## Check if there is a css override.
if (file_exists($cssfile)) {
    $document->addStyleSheet( $cssfile );
} else {
    $document->addStyleSheet( 'components/com_ticketmaster/assets/css/cart.css' );
}

$document->setTitle( JText::_('COM_TICKETMASTER_PAYMENTPAGE') );
## Adding the lightbox functionality
$document->addScript('//code.jquery.com/jquery-latest.js');
## Adding the mediabox functions.

if(!$isJ30){
	JHtml::_('behavior.framework', true);
}

$document->addScript( JURI::root(true).'/administrator/components/com_ticketmaster/assets/lightbox/mediabox.js');
$document->addStyleSheet( JURI::root(true).'/administrator/components/com_ticketmaster/assets/lightbox/mediabox.css' );

## Getting the global DB session
$session =& JFactory::getSession();
## Gettig the orderid if there is one.
$ordercode = $session->get('ordercode');

include_once( 'components/com_ticketmaster/assets/functions.php' );

## Including required paths to calculator.
$path_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'helpers'.DS.'get.amount.php';
include_once( $path_include );	

## Including required paths to calculator.
$path_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'helpers'.DS.'encrypt.ordercode.php';
include_once( $path_include );	

## Getting the total to pay :)
$ordertotal = _getAmount($ordercode);
$fees = _getFees($ordercode);
$discount = _getDiscount($ordercode);

$count = count($this->items);

## Check if this is Joomla 2.5 or 3.0.+


if(!$isJ30) {

	JHTML::_( 'behavior.mootools' );

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
	//$document->addStyleSheet( JURI::root(true).'/administrator/components/com_ticketmaster/assets/bootstrap/css/bootstrap.css' ); 
	$document->addScript( JURI::root(true).'/administrator/components/com_ticketmaster/assets/bootstrap/js/bootstrap.js');	
	jimport('joomla.html.html.bootstrap');
	$button = 'btn';
	
}
## Loading the lightbox script.
$document->addScript( JURI::root(true).'/components/com_ticketmaster/assets/javascripts/checkout-lightbox.js');
?>

<script language="javascript">

var JQ = jQuery.noConflict();

JQ(document).ready(function(){
		
	//Hide div w/id extra
   JQ("#paymentmethods").css("display","none");

	// Add onclick handler to checkbox w/id checkme
   JQ("#checkme").click(function(){

		// If checked
		if (JQ("#checkme").is(":checked")){
			//show the hidden div
			JQ("#paymentmethods").show("slow");
		}else{
			//otherwise, hide it
			JQ("#paymentmethods").hide("slow");
		}
	
  });

});
	 

</script>

<?php if ($count == 0 && $this->waitlist->total == 0 ){ ?>

    <div class="alert alert-error">
      <?php $overview = JRoute::_('index.php?option=com_ticketmaster&view=upcoming'); ?>
      <h4><?php echo JText::_('COM_TICKETMASTER_YOUR_CART_EMPTY'); ?></h4>
      <a href="<?php echo $overview; ?>" class="btn btn-small btn-danger pull-right"><?php echo JText::_('COM_TICKETMASTER_TO_OVERVIEW'); ?></a>
      <p style="margin-top: 8px;"><?php echo JText::_('COM_TICKETMASTER_YOUR_CART_EMPTY_DESC'); ?></p>
    </div>   

<?php }else{  ?>

	<?php if($this->waitlist->total != 0) { ?> 
        <?php if ($count != 0) { ?>
       
            <div class="alert">
              <h4><?php echo JText::_('COM_TICKETMASTER_PLEASE_CONFIRM_WAITINGLIST_TIKETS'); ?></h4>
              <p style="margin-top: 8px;"><?php echo JText::_('COM_TICKETMASTER_PLEASE_CONFIRM_WAITINGLIST_TIKETS_DESC'); ?></p>
            </div>
       
	   <?php }else{ ?>
        	
            <h2><?php echo $this->msg->mailsubject; ?></h2>
            <?php echo $this->msg->mailbody; ?>
            
        <?php } ?>  		
	<?php } ?>
    
	<?php if($count > 0) { ?>
    
        <h2><?php echo JText::_('COM_TICKETMASTER_PAYMENT_ORDER'); ?></h2>
        
        <div id = "tm-cart-text">
            <?php echo JText::_( 'COM_TICKETMASTER_YOUR_PAYMENT_TEXT' ); ?>
        </div> 
    
        <div class="row-fluid">
          <div class="span8"><h4><?php echo JText::_( 'COM_TICKETMASTER_ORDER_INFORMATION' ).' '.$ordercode; ?></h4></div>
          <div class="span4"><h4><?php echo JText::_( 'COM_TICKETMASTER_PAYMENT' ); ?></h4></div>
        </div> 
    
        <div class="row-fluid">
          <div class="span8">
          
            <table class="table table-hover" id="cart">               
                
                <?php 
                   
                   $k = 0;
                   for ($i = 0, $n = count($this->items); $i < $n; $i++ ){
                    
                    ## Give give $row the this->item[$i]
                    $row        = &$this->items[$i];
                    $published 	= JHTML::_('grid.published', $row, $i );
                    $checked    = JHTML::_('grid.id', $i, $row->orderid );
                    
            
                ?>                 
                
                <tr id="row-<?php echo $row->orderid; ?>">
                    <td width="75%">
                        <?php echo $row->eventname; ?> - <?php echo $row->ticketname; ?>
                        <?php if($row->seat_sector != 0){ echo ' - '.JText::_( 'COM_TICKETMASTER_SEATNUMBER' ).': '.checkSeat($row->orderid, $this->coords); } ?>
                        <br/><?php echo JText::_( 'COM_TICKETMASTER_DATE' ); ?>: <?php echo date ($this->config->dateformat, strtotime($row->ticketdate)); ?>
			                <?php if ( $row->show_end_date == 1 ){?>
			                  - <?php echo date ($this->config->dateformat, strtotime($row->end_date)); ?>
			                <?php } ?>                                           
                    </td>
                    <td width="25%">
                        <div align="center"><?php echo showprice($this->config->priceformat ,$row->ticketprice,$this->config->valuta); ?></div>
                    </td>                   
                </tr>
                
                
                
                <?php
                  $k=1 - $k;
                  }
                ?>                                     
                
            </table>
            
            <div class="row-fluid">
              <div class="span9">
                    <div align="right"><?php echo JText::_('COM_TICKETMASTER_SUBTOTAL'); ?></div>        
              </div>
              <div class="span3">
                    <div align="center">
                        <?php echo showprice($this->config->priceformat , ($ordertotal+$discount)-$fees, $this->config->valuta); ?>
                    </div>
              </div>
            </div>
            
            <?php if($discount != 0){ ?>
                <div class="row-fluid">
                  <div class="span9">
                        <div align="right"><?php echo JText::_('COM_TICKETMASTER_DISCOUNT'); ?></div>       
                  </div>
                  <div class="span3">
                        <div align="center"><?php echo showprice($this->config->priceformat , $discount, $this->config->valuta); ?></div>
                  </div>
                </div> 
            <?php } ?> 
            
            <div class="row-fluid">
              <div class="span9">
                    <div align="right"><?php echo JText::_('COM_TICKETMASTER_ORDERTOTAL'); ?></div>        
              </div>
              <div class="span3">
                    <div align="center"><?php echo showprice($this->config->priceformat , $ordertotal, $this->config->valuta); ?></div>
              </div>
            </div>  
         
          </div>
          <div class="span4">
          
            <?php ## Load the bootstrapped payment plugins.
            JPluginHelper::importPlugin( 'rdmedia' );
            $dispatcher = JDispatcher::getInstance();
            $results = $dispatcher->trigger( 'display' );
            ?>      
          
          </div>
        </div> 
    
    <?php } ?> 
    
<?php } ?> 

<div style="padding-top:2px; clear:both"></div>


<div id="load_tos" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <h3 id="myModalLabel"><?php echo $this->tos->mailsubject; ?></h3>
  </div>
  <div class="modal-body">
    <p style="font-size:105%; padding:0px;">
		<?php echo $this->tos->mailbody; ?>            
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<?php function checkSeat($value, $seat) {
	
   for ($i = 0, $n = count($seat); $i < $n; $i++ ){

		if ($value == $seat[$i]->orderid) {
			if($seat[$i]->row_name != ''){
				$seat = $seat[$i]->row_name.$seat[$i]->seatid;
			}else{
				$seat = $seat[$i]->seatid;
			}
		}

	}	
		
	return $seat; 
} 

?>




