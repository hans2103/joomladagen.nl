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

$app =& JFactory::getApplication();
$pathway =& $app->getPathway();
$pathway->addItem(JText::_( 'COM_TICKETMASTER_EVENT_OVERVIEW' ), 'index.php?option=com_ticketmaster');
$pathway->addItem(JText::_( 'COM_TICKETMASTER_EVENT' ), 'index.php?option=com_ticketmaster&view=eventlist&id='.$this->items->eventid);
$pathway->addItem($this->items->eventname.' - '.$this->items->ticketname);

## Obtain user information.
$user = & JFactory::getUser();
$userid = $user->id;
## Get document type and add it.
$document = JFactory::getDocument();
$document->addStyleSheet( 'components/com_ticketmaster/assets/css/component.css' );

## Link to the lightbox:
$link = 'index.php?option=com_ticketmasterext&tmpl=component&cid='.$this->items->ticketid;

$cssfile = 'components/com_ticketmaster/assets/css-overrides/event-bootstrap.css';

## Check if there is a css override.
if (file_exists($cssfile)) {
    $document->addStyleSheet( $cssfile );
} else {
    $document->addStyleSheet( 'components/com_ticketmaster/assets/css/event-bootstrap.css' );
}

## Adding the lightbox functionality

if ($this->config->load_jquery == 1) {
	$document->addScript('//code.jquery.com/jquery-latest.js');
}elseif ($this->config->load_jquery == 2) {
	$document->addScript( JURI::root(true).'/administrator/components/com_ticketmaster/assets/jquery/jquery.js');
}

if($isJ30) {
	## We are in J3, load the bootstrap!
	JHtml::_('bootstrap.framework');
	jimport('joomla.html.html.bootstrap');
}else{
	$document->addStyleSheet( JURI::root(true).'/administrator/components/com_ticketmaster/assets/bootstrap/css/bootstrap.css' );
	$document->addScript( JURI::root(true).'/administrator/components/com_ticketmaster/assets/bootstrap/js/bootstrap.js');
}


$document->addScript(JURI::root(true).'/components/com_ticketmaster/assets/j3-lightbox/js/jquery.colorbox.js');
$document->addScript(JURI::root(true).'/components/com_ticketmaster/assets/j3-lightbox/js/colorbox.js');
$document->addStyleSheet( JURI::root(true).'/components/com_ticketmaster/assets/j3-lightbox/css/colorbox.css' );

include_once( 'components/com_ticketmaster/assets/functions.php' );

## Getting the global DB session
$session =& JFactory::getSession();
## Gettig the orderid if there is one.
$ordercode = $session->get('ordercode');

## Including required paths to calculator.
$path_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'helpers'.DS.'get.amount.php';
include_once( $path_include );

## Total for this order:
$total = _getAmount($session->get('ordercode'));
$fees = _getFees($session->get('ordercode'));
$ordertotal = $total-$fees;

## Getting the parameters for this view:
$params = JComponentHelper::getParams('com_ticketmaster');
$gmaps_width = $params->get('gmaps_width', '200');
$gmaps_heigth = $params->get('gmaps_heigth', '200');
$show_venuebuttons = $params->get('show_venuebuttons', 1);
$button_color = $params->get('button_color', 'btn');
$show_venue_events = $params->get('show_venue_events', '1');
$show_venuedetails = $params->get('show_venuedetails', '1');
$button_color = $params->get('button_color', 'btn');

$button = 'btn '.$button_color;

## Redirection link in JRoute:
$gotocart = JRoute::_('index.php?option=com_ticketmaster&view=cart');

if ($this->extended->seat_chart != '') {

	$image = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmasterext'.DS.'assets'.DS.'seatcharts'.DS.$this->extended->seat_chart;
	## Get the image size
	list($width, $height, $type, $attr) = getimagesize($image);

}

?>

<script type="text/javascript" src="components/com_ticketmaster/assets/j3-lightbox/js/jquery.colorbox.js"></script>
<script type="text/javascript" src="components/com_ticketmaster/assets/j3-lightbox/js/colorbox.js"></script>

<script type="text/javascript">

	var JQ = jQuery.noConflict();

	JQ(document).ready(function() {
		  initialize()
	});

  function updateCart(){

	var JQ = jQuery.noConflict();
  	JQ('#cart-information-loader').show();

	var order = 'ordercode=' + <?php echo $ordercode; ?> ;

	JQ.ajax({
		//this is the php file that processes the data and send mail
		url: "index.php?option=com_ticketmaster&controller=order&task=updatecart&format=raw",
		//POST method is used
		type: "POST",
		//pass the data
		data: order,
		//Do not cache the page
		cache: false,
		//success
		success: function (html) {
			//if process.php returned 1/true (send mail success)
			 JQ("#cart-information").html(html);
			 JQ('#cart-information-loader').hide();
			 JQ("#seatselection").show();
		}
	});

  }

  function buytickets(items){

		// Getting the data we need for submission.
		var JQ = jQuery.noConflict();
		var inputdata = JQ("#qty_"+items).val();

		//organize the data properly
		var data = 'amount=' + inputdata + '&ticketid=' + items +  '&ordercode='
		+ <?php echo $ordercode; ?> + '&togo='  + <?php echo $this->items->totaltickets; ?> + '&eventid=' + <?php echo $this->items->eventid; ?> +'';

		JQ.ajax({
			//this is the php file that processes the data and send mail
			url: "index.php?option=com_ticketmaster&controller=order&task=buyticket&format=raw",
			//POST method is used
			type: "POST",
			//pass the data
			data: data,
			// data type = json
			dataType: 'json',
			//Do not cache the page
			cache: false,
			//success
			beforeSend: function() {
				JQ( "#tm-loader" ).show();
			},
			success: function (html) {
				// We're done, show data
				JQ( "#tm-loader" ).hide();

				if(html.status == 666) {

					JQ( '#message' ).html(html.msg);
					updateCart();

				}else{
					JQ( "#message" ).html(html.msg);
					updateCart();

				}

			},
		   error:function (xhr, ajaxOptions, thrownError){
			 alert(xhr.status);
		  }
		});


  }

  function waitinglist(items){

		// Getting the data we need for submission.
		var JQ = jQuery.noConflict();
		var inputdata = JQ("#qty_"+items).val();

		//organize the data properly
		var data = 'amount=' + inputdata + '&ticketid=' + items +  '&ordercode='
		+ <?php echo $ordercode; ?> + '&togo='  + <?php echo $this->items->totaltickets; ?> + '&eventid=' + <?php echo $this->items->eventid; ?> +'';

		JQ.ajax({
			//this is the php file that processes the data and send mail
			url: "index.php?option=com_ticketmaster&controller=order&task=waitinglist&format=raw",
			//POST method is used
			type: "POST",
			//pass the data
			data: data,
			// data type = json
			dataType: 'json',
			//Do not cache the page
			cache: false,
			//success
			beforeSend: function() {
				JQ( "#tm-loader" ).show();
			},
			success: function (html) {
				// We're done, show data
				JQ( "#tm-loader" ).hide();

				if(html.status == 666) {

					JQ( '#message' ).html(html.msg);
					updateCart();

				}else{
					JQ( "#message" ).html(html.msg);
					updateCart();

				}

			},
		   error:function (xhr, ajaxOptions, thrownError){
			 alert(xhr.status);
		  }
		});


  }


</script>

<h1>Tickets Joomla!Dagen</h1>

<div class="row-fluid">


  	<div class="">

	<?php
    if ($this->items->ticketdescription != ''){
        echo $this->items->ticketdescription;
    }
    ?>

    <div id="message"><!-- Dont remove this container, it is used for ordering messages --></div>

    <?php if($this->items->counter_choice == 0){ ?>

		<?php if ($this->config->show_quantity_eventlist == 1) { ?>
	        <?php if ($this->items->totaltickets < 25 && $this->items->totaltickets != 0) { ?>
	            <div id = "ticketmaster-order-warning">
	                <strong>
	                <?php echo JText::_('COM_TICKETMASTER_ONLY').' '.$this->items->totaltickets.' '. JText::_('COM_TICKETMASTER_LEFT_MSG').' '.
	                $this->items->eventname; ?>
	                </strong>
	            </div>
	        <?php } ?>
	    <?php } ?>

    <?php } ?>

	<?php if ($this->items->show_seatplans != 1) { ?>
		<h3><?php echo JText::_('COM_TICKETMASTER_ORDER_YOUR_TICKETS_NOW'); ?></h3>
	<?php }else{ ?>
		<h3><?php echo JText::_('COM_TICKETMASTER_TICKETPRICING'); ?></h3>
	<?php } ?>

    <?php if (count($this->childs) != 0) { ?>
	<div>


		<?php

        $k = 0;
        for ($i = 0, $n = count($this->childs); $i < $n; $i++ ){


        ## Give give $row the this->item[$i]
        $row = $this->childs[$i];

		## For the ticket totals -- If parent:
		if($row->counter_choice == 0){
			$total_tickets = $this->items->totaltickets;
		}else{
			## using the child counter:
			$total_tickets = $row->totaltickets;
		}
        ?>

		<?php if($row->free_text_1 == 'newgroup'):?>
	    </table>
		</div>

	    <div class="well well-small">
		<?php if($row->free_text_2):?>
		<h3><?php echo($row->free_text_2);?></h3>
		<?php endif;?>
		<?php if($row->free_text_2 == 'Familieprogramma'):?>
		<?php
	    if (!empty($row->ticketdescription)){
	        echo $row->ticketdescription;
	    }
	    ?>
		<?php endif;?>
		<table class="table table-striped" style="margin-bottom: 0;">
		<?php endif;?>

            <tr>
                <td><div>

                    <?php if ($this->items->show_seatplans != 1) { ?>

	                    <div style="float:left;">
	                        <input id="qty_<?php echo $row->ticketid;?>" type="text" class="input-mini" autocomplete="OFF" value="1"
	                            style="text-align:center; width:20px;" size="1" width="15px" />
	                     </div>

	                    <div style="padding:5px 5px 5px 50px;"><?php echo $row->ticketname; ?> -
	                    	<?php echo showprice($this->config->priceformat ,$row->ticketprice, $this->config->valuta); ?>

	                    	<?php
						    if (!empty($row->ticketdescription)){
							    if($row->free_text_2 != 'Familieprogramma'){
						        	echo $row->ticketdescription;
						        }
						    }
						    ?>
	                    	</div>

					<?php }else{ ?>

					 	<div style="float:left; padding:10px;"><?php echo $row->ticketname; ?></div>

					<?php } ?>

                </div>
                </td>
                <td width="100px;"><?php if ($total_tickets <= 0){ ?>
						   <?php if ($this->config->show_waitinglist != 1) { ?>
                           <div style="text-align:right; padding-top: 5px; padding-right: 2px;">
                                <?php echo JText::_('COM_TICKETMASTER_SOLD_OUT'); ?>
                           </div>
                       <?php }else{ ?>
                           <a class="<?php echo $button; ?>" onclick="waitinglist(<?php echo $row->ticketid;?>,2)" style="float:right;">
                              <span><?php echo JText::_('COM_TICKETMASTER_WAITING_LIST'); ?></span>
                           </a>
                       <?php } ?>
                    <?php } else { ?>

							<?php if ($this->items->show_seatplans != 1) { ?>

				                <a class="<?php echo $button; ?> btn-success" onclick="buytickets(<?php echo $row->ticketid;?>,2)" style="float:right;">
	                              <span><?php echo JText::_('COM_TICKETMASTER_ORDER'); ?></span>
	                           </a>

	                        <?php }else{  ?>

	                        	<div align="right" style="margin-right:10px;">
	                        		<?php echo showprice($this->config->priceformat ,$row->ticketprice, $this->config->valuta); ?>
	                        	</div>

	                        <?php } ?>
                       </a>

                    <?php } ?>
                </td>
            </tr>

			<?php if ( $row->min_ordering != 0 || $row->max_ordering != 0 ) { ?>

            <?php
				$minimum = str_replace('%%TICKETNAME%%', $row->ticketname, JText::_('COM_TICKETMASTER_MINIMUM_FOR_ORDER'));
				$maximum = str_replace('%%TICKETNAME%%', $row->ticketname, JText::_('COM_TICKETMASTER_MAXIMUM_FOR_ORDER'));
			?>
                <tr>
                   <td colspan="2">
                        <div style="font-size:85%; text-align:center;">

                            <?php if($row->max_ordering != 0 && $row->min_ordering != 0){ ?>
                            	<?php echo $minimum; ?> <?php echo $row->min_ordering; ?> || <?php echo $maximum; ?> <?php echo $row->max_ordering; ?>
                            <?php }else if($row->max_ordering != 0 && $row->min_ordering == 0){ ?>
                            	<?php echo $maximum; ?> <?php echo $row->max_ordering; ?>
                            <?php }else if($row->max_ordering == 0 && $row->min_ordering != 0){ ?>
	                            <?php echo $minimum; ?> <?php echo $row->min_ordering; ?>
	                        <?php } ?>

                        </div>
                   </td>
                </tr>
            <?php } ?>

		 <?php
        	$k=1 - $k;
        }
        ?>

        </table>
        </div>

    <?php }else{ ?>

            <table class="table table-striped">
                <tr>
                    <td><div style="padding-top: 3px;">
                    <?php if ($this->items->show_seatplans != 1) { ?>

	                    <div style="float:left;">
	                        <input id="qty_<?php echo $this->items->ticketid;?>" type="text" class="input-mini" autocomplete="OFF" value="1"
	                            style="text-align:center; width:20px;" size="1" width="15px" />
	                     </div>

	                    <div style="float:left; height:10px; line-height:0px; padding:10px;"><?php echo $this->items->ticketname; ?> -
	                    			<?php echo showprice($this->config->priceformat ,$this->items->ticketprice, $this->config->valuta); ?></div>

					<?php }else{ ?>

					 	<div style="float:left; height:10px; line-height:0px; padding:10px;">
					 		<?php echo $this->items->ticketname; ?>
					 		<?php if ($this->items->show_start_time == 1) {
					 				echo ' - '. date ($this->config->time_format, strtotime($this->items->ticketdate)).' '.
					 					JText::_('COM_TICKETMASTER_EVENTPAGE_HOUR_NOTATION_FOR_EU'); }
					 		?>
					 	</div>

					<?php } ?>
                    </div>
                    </td>
                    <td><?php if ($this->items->totaltickets <= 0){ ?>

						   <?php if ($this->config->show_waitinglist != 1) { ?>
                               <div style="text-align:right; padding-top: 5px; padding-right: 2px;">
                                    <?php echo JText::_('COM_TICKETMASTER_SOLD_OUT'); ?>
                               </div>
						   <?php }else{ ?>
                               <a class="<?php echo $button; ?>" onclick="waitinglist(<?php echo $this->items->ticketid;?>,2)" style="float:right;">
                                  <span><?php echo JText::_('COM_TICKETMASTER_WAITING_LIST'); ?></span>
                               </a>
                           <?php } ?>

                        <?php } else { ?>

							<?php if ($this->items->show_seatplans != 1) { ?>

				                <a class="<?php echo $button; ?>" onclick="buytickets(<?php echo $this->items->ticketid;?>,2)" style="float:right;">
	                              <span><?php echo JText::_('COM_TICKETMASTER_ORDER'); ?></span>
	                           </a>

	                        <?php }else{  ?>

	                        	<div align="right" style="margin-right:10px;">
	                        		<?php echo showprice($this->config->priceformat ,$this->items->ticketprice, $this->config->valuta); ?>
	                        	</div>

	                        <?php } ?>

                        <?php } ?>

                    </td>
                </tr>

            </table>

            <?php if ( $this->items->min_ordering != 0 || $this->items->max_ordering != 0 ) { ?>

            <?php
				$minimum = str_replace('%%TICKETNAME%%', $ticketname, JText::_('COM_TICKETMASTER_MINIMUM_FOR_ORDER'));
				$maximum = str_replace('%%TICKETNAME%%', $ticketname, JText::_('COM_TICKETMASTER_MAXIMUM_FOR_ORDER'));
			?><table>
                <tr>
                   <td>
                        <div style="font-size:85%; text-align:center; padding-bottom:5px;">

                            <?php if($this->items->max_ordering != 0 && $this->items->min_ordering != 0){ ?>
                            	<?php echo $minimum; ?> <?php echo $this->items->min_ordering; ?> || <?php echo $maximum; ?> <?php echo $this->items->max_ordering; ?>
                            <?php }else if($this->items->max_ordering != 0 && $this->items->min_ordering == 0){ ?>
                            	<?php echo $maximum; ?> <?php echo $this->items->max_ordering; ?>
                            <?php }else if($this->items->max_ordering == 0 && $this->items->min_ordering != 0){ ?>
	                            <?php echo $minimum; ?> <?php echo $this->items->min_ordering; ?>
	                        <?php } ?>

                        </div>
                   </td>
                </tr>
              </table>
            <?php } ?>

    <?php } ?>

    </div>

</div>

<div id="ticketmaster_right">
	<div class="well well-small">

    <h4 style="text-align:center;"><?php echo JText::_('COM_TICKETMASTER_CART'); ?></h4>



        <div id="cart-information">

			<?php  if ($this->ticket->total == 0) {

					 echo '<p style="text-align:center">'.JText::_('COM_TICKETMASTER_EMPTY_CART').'</span>';
					 $visible= 'hidden';

					}else{

						$visible= 'visible';?>

                        <strong>

							<?php if ($this->ticket->total > 1) {
								$tickets = JText::_('COM_TICKETMASTER_TICKETS');
							}else{
								$tickets = JText::_('COM_TICKETMASTER_TICKET');
							} ?>

						   <p style="text-align:center">
							   <?php  echo $this->ticket->total .' '.$tickets; ?> <?php echo JText::_('COM_TICKETMASTER_IN_CART'); ?><br/>
                               <?php  echo JText::_('COM_TICKETMASTER_ORDERTOTAL_CART'); ?>
                               <?php  echo showprice($this->config->priceformat, $ordertotal, $this->config->valuta); ?><br/>
                           </p>
                        </strong>

            <?php } ?>

         </div>


        <div id = "cart-information" style="font-size:100%; width:100%; float:right;">

            <div id="cart-information-loader-2" style="width:100%;">
                <div id = "cart-information-loader" style="display: none; margin:0px;" align="center">
                    <img src="components/com_ticketmaster/assets/images/ajaxloader.gif" height="15px" />
                </div>
            </div>

			<?php if ($this->items->show_seatplans == 1) { ?>

                <a class="iframe <?php echo $button; ?> btn-block btn-success" id="seatselection" href="<?php echo $link; ?>">
                    <span><?php echo JText::_('COM_TICKETMASTER_CHOOSE_SEAT'); ?></span>
                </a>

			<?php } ?>

            <a class="<?php echo $button; ?> btn-block btn-success" onClick="location.href='<?php echo $gotocart; ?>'">
                <span><?php echo JText::_('COM_TICKETMASTER_SHOW_CART'); ?></span>
            </a>
        </div>

		<div id="cart-information-loader-1" style="font-size:115%; width:15%; float:right; margin-top:2px; display: none;">
        	<img src="components/com_ticketmaster/assets/images/ajax-loader.gif" height="20px" />
        </div>


	</div>

</div>

<?php if ($this->config->show_venuebox == 1) { ?>

    <div class="ticktmaster_box_heading">

    	<?php echo JText::_('COM_TICKETMASTER_VENUE_DETAILS'); ?>

    </div>
    <div class="ticktmaster_box_content">

        <div class="row-fluid" style="width:95%;">
          <div class="span9">

            <?php if ($this->config->show_google_maps == 1) { ?>
                <div id="map-canvas" style="width:100%; height:<?php echo $gmaps_heigth; ?>px;"></div>
            <?php } ?>

          </div>
          <div class="span3">

            <address>
              <font style="font-size:115%; font-weight:bold; line-height:25px;"><?php echo $this->items->venue; ?></font><br>
              <?php echo $this->items->street; ?><br>
              <?php echo $this->items->zipcode; ?><br>
			  <?php echo $this->items->city; ?><br>
              <a href="<?php echo $this->items->website; ?>" target="_blank"><?php echo $this->items->website; ?></a><br>
              <abbr title="Phone">P:</abbr> <?php echo $this->items->phone_number; ?>
            </address>

            <address>
              <strong><?php echo $this->items->contact_person; ?></strong><br>
              <a href="mailto:<?php echo $this->items->email_address; ?>"><?php echo $this->items->email_address; ?></a>
            </address>

            <?php if ($show_venuebuttons == 1) { ?>

            <?php  $alias = JFilterOutput::stringURLSafe($this->items->venue); ?>
            <?php  $link = JRoute::_('index.php?option=com_ticketmaster&view=venue&id='.$this->items->vid.':'.$alias);  ?>

                <div style="clear:both; margin-top: 10px; width:100%;">
                    <?php if($show_venuedetails == 1) {?>
                   		<a href="<?php echo $link; ?>" class="btn btn-small <?php echo $button_color; ?>">
							<?php echo JText::_('COM_TICKETMASTER_VENUE_DETAILS'); ?></a>
                    <?php } ?>
                </div>
            <?php } ?>

          </div>
        </div>

     </div>

<?php }?>

<script type="text/javascript"
  src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->config->google_maps_key;?>&sensor=false">
</script>

<script type="text/javascript">

var position = new google.maps.LatLng(<?php echo $this->items->googlemaps_latitude; ?>,<?php echo $this->items->googlemaps_longitude; ?>);
var marker;
var map;

  function initialize() {
	var mapOptions = {
	  center: new google.maps.LatLng(<?php echo $this->items->googlemaps_latitude; ?>,<?php echo $this->items->googlemaps_longitude; ?>),
	  zoom: 16,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map(document.getElementById("map-canvas"),
		mapOptions);

        var marker = new google.maps.Marker({
            position: position,
            map: map,
            title:"<?php echo $this->items->venue; ?>"
        });

        var contentString = '<strong><?php echo $this->items->venue; ?></strong>';

        var infowindow = new google.maps.InfoWindow({
            content: contentString
        });

        google.maps.event.addListener(marker, 'click', function() {
          infowindow.open(map,marker);
        });

  }
  google.maps.event.addDomListener(window, 'load', initialize);
</script>

<div class="well well-small ">
	<p style="text-align: center;">* Deze kortingsprijzen zijn geldig tot en met 11 maart 2015, en op=op.<br/>Restitutie van tickets is mogelijk tot zeven dagen voor het evenement.<br/>Stichting Sympathy is vrijgesteld van BTW.</p>
</div>
