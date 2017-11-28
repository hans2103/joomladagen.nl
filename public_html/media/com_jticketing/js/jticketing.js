/*
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
var jtSite =
{
	events:
	{
		toggleDiv: function(spanId)
		{
			jQuery("#"+spanId).toggle();
		}
	},

	venueForm:
	{
		/* Google Map autosuggest  for location */
		initializeGMapSuggest: function ()
		{
			input = document.getElementById('jform_address');
			var autocomplete = new google.maps.places.Autocomplete(input);
		},

		initVenueFormJs: function()
		{
			google.maps.event.addDomListener(window, 'load', jtSite.venueForm.initializeGMapSuggest);
			jQuery(document).ready(function()
			{
				jQuery('input[name="jform[online]').click(function()
				{
					jtSite.venueForm.showOnlineOffline(jQuery('input[name="jform[online]"]:checked').val())
				});

				jtSite.venueForm.showOnlineOffline(jQuery('input[name="jform[online]"]:checked').val());

				jQuery('#jformonline_provider').change(function()
				{
					jtSite.venueForm.getPluginParams();
				});

				if (editId || jQuery('input[name="jform[online]"]:checked').val() == 1)
				{
					jtSite.venueForm.getPluginParams();
				}

				if (getValue)
				{
					jQuery('#jformonline_provider').trigger('change');
				}
			});
		},

		showOnlineOffline: function (ifonline)
		{
			if (ifonline == 1)
			{
				jQuery("#jformonline_provider").closest(".form-group").show();
				jQuery("#provider_html").show();
				jQuery("#jformoffline_provider").hide();
			}
			else
			{
				jQuery("#jformonline_provider").closest(".form-group").hide();
				jQuery("#provider_html").hide();
				jQuery("#jformoffline_provider").show();
			}
		},

		venueFormSubmitButton: function(task)
		{
			if (task == 'venueform.save')
			{
				var venue_name = jQuery('input[name="jform[jform_name]"]:checked').val();
				var api_username = jQuery('input[name="jform[api_username]"]:checked').val();
				var api_password = jQuery('input[name="jform[api_password]"]:checked').val();
				var host_url = jQuery('input[name="jform[host_url]"]:checked').val();
				var source_sco_id = jQuery('input[name="jform[source_sco_id]"]:checked').val();
				var onlines = jQuery('input[name="jform[online]"]:checked').val();
				var onlineProvider  = jQuery('#jformonline_provider').val();
				if(editId && onlines == "0")
				{
					jQuery('#api_username').val('');
					jQuery('#api_password').val('');
					jQuery('#host_url').val('');
					jQuery('#source_sco_id').val('');
				}
				if (jQuery('input[name="jform[online]"]:checked').val() == 1)
				{
					if (!onlineProvider || onlineProvider == '0')
					{
 						alert(Joomla.JText._('COM_JTICKETING_VENUE_FORM_ONLINE_PROVIDER'));
						jQuery('#jformonline_provider').focus();
						return false;
					}

					if (!document.formvalidator.isValid('#provider_html'))
					{
						return false;
					}

					jsonObj = [];
					jQuery('#provider_html input').each(function() {
					var id = jQuery(this).attr("id");
					var output = jQuery(this).val();
					item = {}
					item ["id"] = id;
					item ["output"] = output;

					var source = jsonObj.push(item);
					jsonString = JSON.stringify(item);
					jQuery("#venue_params").val(jsonString);
					});

					var error  = jtSite.venueForm.validateOnlineLicense();

					if (error)
					{
						var error_html = '';
						error_html += "<br />" + Joomla.JText._('COM_JTICKETING_INVALID_FIELD') +  error;
						jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						return false;
					}
				}
				else
				{
					if (!jQuery("#jform_address").val())
					{
						var error_html = '';
						error_html += "<br />" + Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_FORM_LBL_VENUE_ADDRESS');
						jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						jQuery('#jform_address').focus();
						return false;
					}
				}
				if (!jQuery("#form-venue #jform_name").val())
				{
					var error_html = '';
					error_html += "<br />" + Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_FORM_LBL_VENUE_TITLE');
					jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
					return false;
				}
				Joomla.submitform(task, document.getElementById('form-venue'));
			}

			if (task == 'venueform.cancel')
			{
				Joomla.submitform(task, document.getElementById('form-venue'));
			}
		},

		getPluginParams: function()
		{
			jQuery('#jformonline_provider').change(function()
			{
				var element = jQuery(this).val();
				jQuery.ajax({
				type:'POST',
				url:root_url + 'index.php?option=com_jticketing&task=venueform.getelementparams',
				data: {element:element,venue_id:jQuery("#venue_id").val()},
				datatype:"HTML",
				async: 'false',
				success:function(response){
					jQuery('#provider_html').html(response);
					var online = jQuery('input[name="jform[online]"]:checked').val();
					jQuery('#provider_html').css('display', 'none');
					if(online == 1)
					{
						jQuery('#provider_html').css('display', 'block');
					}
					},
					error: function() {
						jQuery('#provider_html').hide();
						return true;
						},
					});
			});

			// Google Map autosuggest  for location
			function initialize()
			{
				input = document.getElementById('jform_address');
				var autocomplete = new google.maps.places.Autocomplete(input);
			}

			google.maps.event.addDomListener(window, 'load', initialize);
		},

		// Function : For finding longitude latitude of selected address
		getLongitudeLatitude: function()
		{
			var geocoder = new google.maps.Geocoder();
			var address = jQuery('#jform_address').val();
			geocoder.geocode({ 'address': address}, function(results, status)
			{
				if (status == google.maps.GeocoderStatus.OK)
				{
					var latitude = results[0].geometry.location.lat();
					var longitude = results[0].geometry.location.lng();
					jQuery('#jform_latitude').val(latitude);
					jQuery('#jform_longitude').val(longitude);
				}
			});
		},

		validateOnlineLicense : function()
		{
			var returnVal = '';

			jQuery.ajax({
				url: root_url + 'index.php?option=com_jticketing&task=venueform.validateOnlineLicense',
				type: 'GET',
				data: {data : jQuery("#form-venue").serialize()},
				dataType: 'json',
				async:false,
				success: function(data)
				{
					if (data.hasOwnProperty("error_message"))
					{
						returnVal = data.error_message;
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			});

			return returnVal;
		},

		// Function : For Get Current Location
		getCurrentLocation: function()
		{
			if (navigator.geolocation)
			{
				navigator.geolocation.getCurrentPosition(showLocation);
			}
			else
			{
				var address = Joomla.JText._('COM_JTICKETING_ADDRESS_NOT_FOUND');
				var lonlatval = Joomla.JText._('COM_JTICKETING_LONG_LAT_VAL');
				jQuery('#jform_address').val(address);
				jQuery("#jform_longitude").val(lonlatval);
				jQuery("#jform_latitude").val(lonlatval);
			}

			// Function : For Showing user current location
			function showLocation(position)
			{
				var latitude = position.coords.latitude;
				var longitude = position.coords.longitude;
				jQuery.ajax({
					type:'POST',
					url:root_url + 'index.php?option=com_jticketing&task=venueform.getLocation',
					data:'latitude='+latitude+'&longitude='+longitude,
					dataType: 'json',
					success:function(data)
					{
						console.log(data);
						var address = data["location"];
						var longitude = data["longitude"];
						var latitude = data["latitude"];

						if(data)
						{
							jQuery("#jform_address").val(address);
							jQuery("#jform_longitude").val(longitude);
							jQuery("#jform_latitude").val(latitude);
						}
					}
				});
			}
		}
	},

	event:
	{
		onlineMeetingUrl : function(thisVal)
		{
			var eventId = jQuery('#event_id').val();
			jQuery.ajax({
				url: jticketing_baseurl + 'index.php?option=com_jticketing&view=event&task=event.onlineMeetingUrl&eventId='+eventId,
				type: 'GET',
				dataType: 'json',
				beforeSend: function () {
					jQuery(thisVal).button('loading');
				},
				complete: function () {
					jQuery(thisVal).button('reset');
				},
				success: function(data)
				{
					if(data == 1)
					{
						top.location.href = jticketing_baseurl + 'index.php?option=com_users&view=login';
						return;
					}
					win = window.open(data,'_blank', 'location=yes,fullscreen=yes,scrollbars=yes,status=yes');
					if (win) {
					//Browser has allowed it to be opened
					win.focus();
					} else {
					//Browser has blocked it
					alert(Joomla.JText._('COM_JTICKETING_EVENTS_ENTER_MEETING_SITE_POPUPS'));
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			});
		},

		meetingRecordingUrl : function(thisVal)
		{
			var eventId = jQuery('#event_id').val();
			jQuery.ajax({
				url: jticketing_baseurl + 'index.php?option=com_jticketing&view=event&task=event.meetingRecordingUrl&eventId='+eventId,
				type: 'GET',
				dataType: 'json',
				beforeSend: function () {
					jQuery(thisVal).button('loading');
				},
				complete: function () {
					jQuery(thisVal).button('reset');
				},
				success: function(data)
				{
					console.log(data);
					var j = 1;

					if(data == 1)
					{
						top.location.href = jticketing_baseurl + 'index.php?option=com_users&view=login';
					}
					else if(!jQuery.trim(data))
					{
						jQuery("#content").text(recording_error);
						jQuery('#recordingUrl').modal('show');
					}
					else if(Array.isArray(data))
					{
						var modal_div = document.getElementById("content");

						for (var i=0; i < data.length; i++)
						{
							// later you create new element and use appendChild to add it
							var new_element = document.createElement("div");
							var aTag = document.createElement('a');
							var mydiv = modal_div.appendChild(new_element);
							aTag.setAttribute('href',data[i]);
							aTag.setAttribute('target',"_blank");
							aTag.innerHTML = recording_name + (i+j);
							mydiv.appendChild(aTag);
						}
						jQuery("#content").val(jQuery(this).new_element);
						jQuery('#recordingUrl').modal('show');
					}
					else
					{
						var mydiv = document.getElementById("content");
						// later you create new element and use appendChild to add it
						var aTag = document.createElement('a');
						aTag.setAttribute('href',data);
						aTag.setAttribute('target',"_blank");
						aTag.innerHTML = recording_name;
						mydiv.appendChild(aTag);
						jQuery("#content").val(jQuery(this).mydiv);
						jQuery('#recordingUrl').modal('show');
					}

					jQuery('#recordingUrl').on('hidden.bs.modal', function () {
						jQuery('#content').html("");
					});
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			});
		},

		initEventDetailJs: function(){
			jQuery(document).ready(function()
			{
				jQuery('[data-toggle="tooltip"]').tooltip();
				jtCounter.jtCountDown('jt-countdown', startDate, endDate, currentDate);
				jtSite.event.onChangeGetOrderData();
			});
		},

		getGoogleLocation: function(){
			jQuery("#googleloc").click(function()
			{
				jtSite.event.getCurrentLocation();
			});
		},

		viewMoreAttendee: function(){

			var eventId = document.getElementById('event_id').value;

			if(gbl_jticket_pro_pic == 0)
			{
				gbl_jticket_pro_pic = document.getElementById('attendee_pro_pic_index').value;
			}

			techjoomla.jQuery.ajax({
				url:jticket_baseurl+'index.php?option=com_jticketing&task=event.viewMoreAttendee&tmpl=component',
				type:'POST',
				dataType:'json',
				data:
				{
					eventId:eventId,
					jticketing_index:gbl_jticket_pro_pic
				},
				success:function(data)
				{
					gbl_jticket_pro_pic = data['jticketing_index'];
					techjoomla.jQuery("#jticketing_attendee_pic ").append(data['records']);

					if(!data['records'] || attedee_count <= gbl_jticket_pro_pic)
					{
						techjoomla.jQuery("#btn_showMorePic").hide();
					}
				},
				error:function(data)
				{
					console.log('error');
				}
			});
		},

		getCurrentLocation:function()
		{
			if (navigator.geolocation)
			{
				navigator.geolocation.getCurrentPosition(showLocation);
			}
			else
			{
				var address = Joomla.JText._('COM_JTICKETING_ADDRESS_NOT_FOUND');
				var lonlatval = Joomla.JText._('COM_JTICKETING_LONG_LAT_VAL');
				jQuery('#jform_address').val(address);
				jQuery("#jform_longitude").val(lonlatval);
				jQuery("#jform_latitude").val(lonlatval);
			}
			// Function : For Showing user current location
			function showLocation(position)
			{
				var destinationlat=destinationlat;
				var destinationlong=destinationlong;
				var latitude = position.coords.latitude;
				var longitude = position.coords.longitude;

				url  = "https://www.google.com/maps/dir/"+latitude+ "+" + longitude +"/" + destinationlat +"+"+ destinationlong;
				window.location.replace(url);
			}

		},

		loadActivity: function(){
			jQuery(window).load(function() {
				if (jQuery('#tj-activitystream .feed-item-cover').length == '0')
				{
					jQuery('.todays-activity .feed-item').css('border-left', '0px');
				}

				jQuery('#postactivity').attr('disabled',true);
				jQuery('#activity-post-text').on('input', function(){
					if (jQuery('#activity-post-text').val() == '')
					{
						jQuery('#postactivity').attr('disabled',true);
					}
					else
					{
						jQuery('#postactivity').attr('disabled',false);
					}
					var textMax = jQuery('#activity-post-text').attr('maxlength');
					var textLength = jQuery('#activity-post-text').val().length;
					var text_remaining = textMax - textLength;
					jQuery('#activity-post-text-length').html(text_remaining + ' ' + Joomla.JText._('COM_JTICKETING_POST_TEXT_ACTIVITY_REMAINING_TEXT_LIMIT'));
				});
			});
		},
		onChangefun: function()
		{
			jQuery("#gallary_filter").change(function()
			{
				var filterVal = document.getElementById("gallary_filter").value;

				if(filterVal=="1")
				{
					jQuery("#videos").show();
					jQuery("#images").hide();
					jQuery(".videosText").text(Joomla.JText._('COM_JTICKETING_GALLERY_VIDEO_TEXT'));
					jQuery(".imagesText").hide();
				}
				else if(filterVal=="2")
				{
					jQuery("#videos").hide();
					jQuery("#images").show();
					jQuery(".videosText").text(Joomla.JText._('COM_JTICKETING_GALLERY_IMAGE_TEXT'));
					jQuery(".imagesText").hide();
				}
				else if(filterVal=="0")
				{
					jQuery("#videos").show();
					jQuery("#images").show();
					jQuery(".videosText").text(Joomla.JText._('COM_JTICKETING_EVENT_VIDEOS'));
					jQuery(".imagesText").show();
				}
			}).change();
		},
		eventImgPopup: function(){
			jQuery('.popup-gallery').magnificPopup({
				delegate: 'a',
				type: 'image',
				tLoading: 'Loading image #%curr%...',
				mainClass: 'mfp-img-mobile',
				gallery: {
					enabled: true,
					navigateByImgClick: true,
					preload: [0,1]
				},
				image: {
					tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
					titleSrc: function(item) {
						return item.el.attr('title') + '<small></small>';
					}
				}
			});
		},

		onChangeGetOrderData: function(){

			jQuery("#event-graph-period").change(function(){
				var graphFilterVal = jQuery("#event-graph-period").val();
				var eventId = jQuery('#event_id').val();
				var ajaxcall = techjoomla.jQuery.ajax({
					type:'GET',
					url: jticketing_baseurl + 'index.php?option=com_jticketing&view=event&task=event.getEventOrderGrapgData&eventId='+eventId+'&filtervalue='+graphFilterVal,
					dataType:'json',
					error:function(data)
					{
						console.log('error')
					}
				});

				ajaxcall.done(function (data) {
					var graphid = document.getElementById("myevent_graph").getContext('2d');
					var total_eventOrderschart = new Chart(graphid, {
						type: 'line',
						data: {
								labels: data.orderDate,
								datasets: [
								{
									label: data.totalOrdersAmount ? data.totalOrdersAmount : 0,
									data: data.orderAmount ? data.orderAmount : 0,
									backgroundColor: "rgba(203, 235, 230, 0.5)",
									borderColor:"rgba(55, 179, 142, 1)",
									lineTension:'0',
									borderWidth:'2',
									pointRadius:1,
									pointBackgroundColor: "rgba(55, 179, 142, 1)",
									pointBorderColor: "rgba(55, 179, 142, 1)",
									pointHoverBackgroundColor: "rgba(55, 179, 142, 1)",
									pointHoverBorderColor: "rgba(55, 179, 142, 1)"
								},
								{
									label: data.avgOrdersAmount ? data.avgOrdersAmount : 0,
									data: data.orderAvg ? data.orderAvg : 0,
									backgroundColor: "rgba(216, 225, 180, 1)",
									borderColor:"rgba(251, 214, 20, 0.90)",
									lineTension:'0',
									borderWidth:'2',
									pointRadius:1,
									pointBackgroundColor: "rgba(251, 214, 20, 0.90)",
									pointBorderColor: "rgba(251, 214, 20, 0.90)",
									pointHoverBackgroundColor: "rgba(251, 214, 20, 0.90)",
									pointHoverBorderColor: "rgba(251, 214, 20, 0.90)"
								}]
							}
					});
				});
			}).change();
		},

		searchAttendee: function (){
			var input, filter, table, tr, td, i;
			input = document.getElementById("attendeeInput");
			filter = input.value.toUpperCase();
			table = document.getElementById("eventAttender");
			tr = table.getElementsByTagName("tr");

			for (i = 0; i < tr.length; i++)
			{
				td = tr[i].getElementsByTagName("td")[0];

				if (td)
				{
					if (td.innerHTML.toUpperCase().indexOf(filter) > -1)
					{
						tr[i].style.display = "";
					}
					else
					{
						tr[i].style.display = "none";
					}
				}
			}
		},
	},
	order:
	{
		/*Initialize event js*/
		initOrderJs: function() {
			jQuery(document).ready(function() {
				jtSite.order.selectOnlineTicketType();
			});
		},
		displayCoupon: function()
		{
			jQuery("#dis_amt").show();
			if (eventType == 0)
			{
				var totalAmtInputbox = parseFloat(jQuery("#total_amt_inputbox").val());
			}
			else
			{
				var totalAmtInputbox = parseFloat(totalAmount);
			}
			totalAmtInputbox = totalAmtInputbox.toFixed(2)
			jQuery("#net_amt_pay_inputbox").val(totalAmtInputbox);
			jQuery("#net_amt_pay").html(totalAmtInputbox);
			if(jQuery("#coupon_chk").is(":checked"))
			{
				if (eventType == 0)
				{
					var totalCalcAmt2;
					jQuery("input[class='input-sm type_ticketcounts']").each(function()
					{
						totalCalcAmt2 = parseFloat(totalCalcAmt2) + parseFloat(jQuery(this).val())
					});
					if (totalCalcAmt2 == 0)
					{
						alert(Joomla.JText._('COM_JTICKETING_NUMBER_OF_TICKETS'));
						document.getElementById("coupon_chk").checked = false;
						return;
					}
				}
				document.getElementById("coup_button").removeAttribute("disabled");
				jQuery("#cop_tr").show();
				jQuery("#coupon_code").show();
				jQuery("#coup_button").show();
			}
			else
			{
				if (eventType == 0)
				{
					var totalAmt = parseFloat(jQuery("#net_amt_pay_inputbox").val());
				}
				else
				{
					var totalAmt = parseFloat(totalAmount);
				}
				totalAmt = totalAmt.toFixed(2);
				var allowTaxation = jQuery("#allow_taxation").val();
				if (allowTaxation == 1)
				{
					jtSite.order.calculateTax(totalAmt);
				}
				if (eventType == 0)
				{
					jQuery("#net_amt_pay").text(totalAmt);
					jQuery("#net_amt_pay_inputbox").val(totalAmt);
				}
				jQuery("#cop_tr").hide();
				jQuery("#coupon_code").hide();
				jQuery("#coup_button").hide();
				jQuery("#dis_amt").show();
				jQuery("#dis_cop_amt").html();
				jQuery("#dis_cop").hide();
				jQuery("#coupon_code").val("");
			}
		},
		applyCoupon: function()
		{
			document.getElementById("coup_button").setAttribute("disabled", "disabled");
			if (jQuery("#coupon_chk").is(":checked"))
			{
				if (jQuery("#coupon_code").val() == "")
				{
					document.getElementById("coup_button").removeAttribute("disabled");
					alert(Joomla.JText._('ENTER_COP_COD'));
				}
				else
				{
					var couponCode = document.getElementById("coupon_code").value;
					jQuery.ajax(
					{
						url: root_url + "index.php?option=com_jticketing&format=json&task=order.getCoupon",
						type: "GET",
						data:'coupon_code='+couponCode,
						dataType: "json",
						success: function(data)
						{
							amount = 0;
							val = 0;
							if (parseInt(data[0].error) == 1)
							{
								alert(couponCode + Joomla.JText._('COP_EXISTS'));
								if (eventType == 1)
								{
									jQuery('#coupon_chk').prop('checked', false);
									jQuery('#coupon_code, #coup_button').hide();
								}
								document.getElementById("coup_button").removeAttribute("disabled");
								return;
							}
							if (parseFloat(data[0].value) > 0)
							{
								if (data[0].val_type == 1)
								{
									if (eventType == 0)
									{
										val = (data[0].value/100) * document.getElementById("total_amt_inputbox").value;
									}
									else
									{
										val = (data[0].value/100) * totalAmount;
									}
								}
								else
								{
									val = data[0].value;
								}
								if (eventType == 0)
								{
									finalVar = 0;
									jQuery("input[class='totalpriceclass']").each(function(){
									finalVar = parseFloat(jQuery(this).val()) + parseFloat(finalVar);
									});
									amount = parseFloat(finalVar) - parseFloat(val);
								}
								else
								{
									amount = parseFloat(totalAmount) - parseFloat(val);
								}
								if (parseFloat(amount) <= 0)
								{
									amount = 0;
								}
								if (isNaN(amount))
								{
									amount = 0;
								}
								jQuery("#net_amt_pay_inputbox").val(amount)
								jQuery("#net_amt_pay").html(amount);
								var allowTaxation = jQuery("#allow_taxation").val();
								if (allowTaxation == 1)
								{
									jtSite.order.calculateTax(amount);
								}
								jQuery("#dis_cop_amt").html(""+val);
								jQuery("#dis_amt").show();
								jQuery("#dis_cop").show();
							}
						}
					});
				}
			}
		},
		calTotal: function(available, totalPriceId, count, obj, unlimited, perUserLimit, maxUserPerTicket)
		{
			totalTicketCount = 0;
			jQuery("input[class='input-small type_ticketcounts']").each(function()
			{
				totalTicketCount = parseInt(totalTicketCount) + parseInt(jQuery(this).val())
			});


			/* If entered no of ticket is greater than no of tickets allowed*/
			if (parseInt(perUserLimit) < parseInt(totalTicketCount))
			{
				alert(maxUserPerTicket);
				obj["value"] = 0;
				return;
			}

			/* If entered no of ticket is greater than no of tickets allowed */
			if ((parseInt(unlimited)!=1))
			{
				if (parseInt(available)< parseInt(obj['value']))
				{
					alert(Joomla.JText._('ENTER_LESS_COUNT_ERROR'));
					obj["value"] = 0;
					return;
				}
			}
			totalCalcAmt = 0;
			totalPrice = count * parseFloat(obj['value']);

			if (isNaN(totalPrice))
			{
				totalPrice = 0;
			}

			totalPrice = totalPrice.toFixed(2);

			// Attach unformatted value to html for total amount calculation and then override this with proper format after ajax
			jQuery("#ticket_total_price" + totalPriceId).html(totalPrice);
			jQuery("#ticket_total_price_inputbox"+totalPriceId).val(totalPrice);

			jQuery("input[class='totalpriceclass']").each(function()
			{
				totalCalcAmt = parseFloat(totalCalcAmt) + parseFloat(jQuery(this).val())
			});

			var couponEnable = 0;
			if (parseInt(totalCalcAmt) == 0)
			{
				jQuery("#cooupon_troption").hide();
			}
			else
			{
				couponEnable = 1;
				jQuery("#cooupon_troption").show();
			}
			if (jQuery("#coupon_chk").is(":checked") && jQuery("#coupon_code").val() != "")
			{
				jtSite.order.applyCoupon();
			}
			if(isNaN(totalCalcAmt))
			{
				totalCalcAmt = 0;
			}

			// Get formatted ammount
			jQuery.ajax(
			{
				url: root_url + "index.php?option=com_jticketing&format=json&task=order.getTotalAmount&tmpl=component&amt=" + totalCalcAmt + "&totalPrice="+totalPrice,
				type: "POST",

				dataType: "json",
				success: function(result)
				{
					// Total ticket calculation amount
					formattedAmount = result.data.formatted_amount
					roundedAmount = result.data.rounded_amount;

					// Currenct ticket type price
					roundedTotalPrice = result.data.roundedTotalPrice;
					formattedTotalPrice = result.data.formattedTotalPrice;

					jQuery("#ticket_total_price" + totalPriceId).html(formattedTotalPrice);
					jQuery("#ticket_total_price_inputbox"+totalPriceId).val(roundedTotalPrice);

					jQuery("#total_amt").html(formattedAmount);
					jQuery("#total_amt_inputbox").val(roundedAmount);
					jQuery("#net_amt_pay").html(formattedAmount);
					jQuery("#net_amt_pay_inputbox").val(roundedAmount);
					var allowTaxation = jQuery("#allow_taxation").val();

					if (allowTaxation == 1)
					{
						jtSite.order.calculateTax(roundedAmount);
					}
				}
			});

		},
		calculateTax: function(amount)
		{
			jQuery.ajax(
			{
				url: root_url + "index.php?option=com_jticketing&format=json&task=order.applytax&tmpl=component&total_calc_amt=" + amount,
				type: "GET",
				dataType: "json",
				success: function(result)
				{
					jQuery("#order_tax").val(result.data.roundedTaxAmount);
					jQuery("#tax_to_pay").html(result.data.formattedTaxAmount);
					jQuery("#tax_to_pay_inputbox").val(result.data.roundedTaxAmount);
					jQuery("#net_amt_after_tax").html(result.data.formattedNetAmountAfterTax);
					jQuery("#net_amt_after_tax_inputbox").val(result.data.roundedNetAmountAfterTax);
					jQuery("#tax_tr").show();
				}
			});
		},
		checkForAlpha: function(el)
		{
			var i = 0;
			for (i = 0; i < el.value.length; i++)
			{
				if ((el.value.charCodeAt(i) > 64 && el.value.charCodeAt(i) < 92) || (el.value.charCodeAt(i) > 96 && el.value.charCodeAt(i) < 123))
				{
					alert(Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));
					el.value = el.value.substring(0, i);
					break;
				}
			}
			if (el.value < 0)
			{
				alert(Joomla.JText._('COM_JTICKETING_ENTER_AMOUNT_GR_ZERO'));
			}
			if (el.value % 1 !== 0)
			{
				alert(Joomla.JText._('COM_JTICKETING_ENTER_AMOUNT_INT'));
				el.value = 0;
				return false;
			}
		},
		validateSpecialChar: function(ele) {
			var inputVal = jQuery(ele).val();
			var checkSpecialChars = "`~!@#$%^&*()_=+[]{}|\;:'\",<.>/?-";

			for(i = 0; i < checkSpecialChars.length;i++){
				if(inputVal.indexOf(checkSpecialChars[i]) > -1){
					jQuery(ele).val('');
					alert(Joomla.JText._('COM_JTICKETING_CHECK_SPECIAL_CHARS'));
					return false;
					}
				}
		},
		jtShowFilter: function()
		{
			jQuery("#jthorizontallayout").toggle();
		},
		jticketingGenerateState: function(countryId, SelectedValue, totalPrice)
		{
			var country = jQuery('#'+countryId).val();
			if (country == undefined)
			{
				return (false);
			}
			jQuery.ajax({
				url: root_url + '?option=com_jticketing&format=json&task=order.loadState&country=' + country + '&tmpl=component',
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					if (countryId == 'country')
					{
						statebackup = data;
					}
					jtSite.order.generateOption(data, countryId, SelectedValue);
				}
			});
			jQuery("select").trigger("liszt:updated");  /* IMP : to update to chz-done selects*/
		},
		generateOption: function(data, countryId, SelectedValue)
		{
			var country = jQuery('#'+countryId).val();
			var options, index, select, option;
			if (countryId == 'country'){
				select = jQuery('#state');
				defaultOption = 'Select State';
			}
			select.find('option').remove().end();
			selected="selected=\"selected\"";
			var op = '<option '+selected+' value="">' +defaultOption+ '</option>';
			if (countryId=='country'){
				jQuery('#state').append(op);
			}
			if (data !== undefined && data !== null)
			{
				options = data;
				for (index = 0; index < data.length; ++index)
				{
					selected = "";
					if (name == SelectedValue)
						selected = "selected=\"selected\"";
					var opObj = data[index];
					selected = "";
					if (opObj.id == SelectedValue)
					{
						selected = "selected=\"selected\"";
					}
					var op = '<option ' +selected+ ' value=\"'+opObj.id+'\">' +opObj.region+ '</option>';
					{
						jQuery('#state').append(op);
					}
				}
			}
			jQuery("select").trigger("liszt:updated");  /* IMP : to update to chz-done selects*/
		},
		selectAttendee: function(obj){
			var selectedAttendee = obj.value;
			if (selectedAttendee == undefined || selectedAttendee <= 0)
			{
				/* Find Prent div and clear all text and hidden fields */
				var parentDiv = jQuery(obj).parent().parent().parent().parent().find('div');
				var allElements = jQuery(parentDiv).find('input[id*="attendee_field_"],select[id*="attendee_field_"]');
						allElements.each(function(){
						var  elementType = jQuery(this).attr('type');
						switch(elementType){
							case 'checkbox':
								break;
							case 'radio':
								break;
							default:
								jQuery(this).val('');
						}
				});
				return (false);
			}
			var hiddenAttendeeField = jQuery(obj).parent().parent().parent().find('input[id*="attendee_field_attendee_id"]');
			hiddenAttendeeField.val(selectedAttendee);
			jQuery.ajax({
				url:root_url+'?option=com_jticketing&format=json&task=order.selectAttendee&attendee_id='+selectedAttendee+'&tmpl=component&format=raw',
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					/* Prefill All data of selected Attendee */
					jQuery.each(data, function(name, val)
					{
						var el = jQuery(obj).parent().parent().parent().find('input[id*="attendee_field_'+name+'"],select[id*="attendee_field_'+name+'"]');
						var  type = el.attr('type');
						switch(type)
						{
							case 'checkbox':
								el.attr('checked', 'checked');
								break;
							case 'radio':
								el.filter('[value="'+val+'"]').attr('checked', 'checked');
								break;
							default:
								el.val(val);
						}
					});
				}
			});
		},
		verifyBookingID: function()
		{
			var bookId = document.getElementById("online_guest").value;
			var url = "index.php?option=com_jticketing&format=json&task=order.verifyBookingID";
			jQuery.ajax({
				url: url,
				type: 'POST',
				async: false,
				data:{
				'book_id':bookId,
				},
				dataType: 'json',
				success: function(data)
				{
					if (data.success)
					{
						window.location.href = data.host_url;
					}
					else
					{
						alert(Joomla.JText._('JT_TICKET_BOOKING_ID_VALIDATION'));
					}
				}
			});
		},
		gatewayHtml: function(element,orderId)
		{
			var prevButtonHtml = '<button id="btnWizardPrev1" onclick="jQuery(\'#MyWizard\').wizard(\'previous\');"	type="button" class="btn  btn-default  btn-prev pull-left" > <i class="icon-arrow-left" ></i>'+Joomla.JText._('COM_JTICKETING_PREV')+'</button>';
			jQuery.ajax({
				beforeSend: function()
				{
					jQuery('#jticketing-payHtmlDiv').before('<div class=\"com_jticketing_ajax_loading\"><div class=\"com_jticketing_ajax_loading_text\">'+loadingMsg+' ...</div><img class=\"com_socialad_ajax_loading_img\" src="'+root_url+'components/com_jticketing/assets/images/loading_data.gif"></div>');
				},
				complete: function() {
					jQuery('.com_jticketing_ajax_loading').remove();
				},
				url: root_url + '?option=com_jticketing&task=payment.changegateway&gateways='+element+'&order_id='+orderId+'&tmpl=component',
				type: 'POST',
				data:'',
				dataType: 'text',
				success: function(data)
				{
					if (data)
					{
						jQuery('#jticketing-payHtmlDiv').html(data);
						jQuery('#jticketing-payHtmlDiv div.form-actions').prepend(prevButtonHtml);
						jQuery('#jticketing-payHtmlDiv div.form-actions input[type="submit"]').addClass('pull-right');
					}
				}
			});
		},
		// Function for login
		jtLogin: function()
		{
			jQuery.ajax({
				url: root_url + '?option=com_jticketing&format=json&task=order.loginValidate&tmpl=component',
				type: 'post',
				data: jQuery('#user-info-tab #login :input'),
				dataType: 'json',
				beforeSend: function(){
					jQuery('#button-login').attr('disabled', true);
					jQuery('#button-login').after('<span class="wait">&nbsp;Loading..</span>');
				},
				complete: function(){
					jQuery('#button-login').attr('disabled', false);
					jQuery('.wait').remove();
				},
				success: function(json){
					jQuery('.warning, .j2error').remove();
					if (json['error']){
						jQuery('#login').prepend('<div class="warning danger" >' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');
						jQuery('.warning').fadeIn('slow');
					}
					else if (json['redirect'] && !json['redirect_invoice_view']){
						jtSite.order.updateBillingDetails();
						jQuery('#btnWizardNext').show();
					}
					else if (json['redirect_invoice_view'])
					{
						document.location = json['redirect_invoice_view'];
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			});
		},
		/* Get updated billing details. */
		updateBillingDetails: function()
		{
			jQuery('#btnWizardNext').removeAttr('disabled');
			jQuery.ajax({
				url: root_url + '?option=com_jticketing&format=json&task=order.getUpdatedBillingInfo&tmpl=component',
				type: 'post',
				data: jQuery('#user-info-tab #login :input'),
				dataType: 'json',
				beforeSend: function(){
				},
				complete: function(){
				},
				success: function(json){
					if (json['error']){
					}
					else if (json['billing_html'])
					{
						/* Update billing tab step HTML. */
						jQuery('#step_billing_info').html(json['billing_html']);
						/* Update state selct list options. */
						jtSite.order.jticketingGenerateState('country', '', '');
						jQuery('#billing_info_data').show();
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			});
		},

		selectOnlineTicketType: function(){
			jQuery("#coupon_code, #coup_button").hide();
			jQuery("#dis_cop").hide();

			if (typeof(eventTypeData) === "undefined" || eventTypeData === '')
			{
				return false;
			}

			var ticketName;
			var allowTaxation = jQuery("#allow_taxation").val();

			try{
				var eventDetails = JSON.parse(eventTypeData);
			}
			catch(e){
				var eventDetails = JSON.stringify(eventTypeData);
				eventDetails = JSON.parse(eventDetails);
			}

			ticketName = jQuery("#tickets_types").val();
				eventDetails.forEach(function(ticket) {

					if (ticket.title == ticketName)
					{
						totalAmount = ticket.price;
						if (unlimitedSeats == '1')
						{
							ticket.available = Joomla.JText._('UNLIM_SEATS');
						}
						jQuery("#ticket_available").text(ticket.available);
						jQuery("#type_id").val(ticket.id);
						jQuery("#ticket_total_price").text(ticket.price);
						jQuery("#ticket_total_price_inputbox").val(ticket.price);
						jQuery("#net_amt_pay_inputbox").val(ticket.price);
						jQuery("#net_amt_pay").text(ticket.price);

						if (allowTaxation == 1)
						{
							jtSite.order.calculateTax(ticket.price);
						}
					}
				});

				jQuery("#tickets_types").change(function() {
				ticketName = this.value;
				eventDetails.forEach(function(ticket) {

					if (ticket.title == ticketName)
					{
						totalAmount = ticket.price;
						if (unlimitedSeats == '1')
						{
							ticket.available = Joomla.JText._('UNLIM_SEATS');
						}
						jQuery("#ticket_available").text(ticket.available);
						jQuery("#ticket_total_price").text(ticket.price);
						jQuery("#ticket_total_price_inputbox").val(ticket.price);
						jQuery("#net_amt_pay_inputbox").val(ticket.price);
						jQuery("#net_amt_pay").text(ticket.price);
						jQuery("#coupon_chk").prop("checked", false);
						jQuery("#coupon_code, #coup_button").hide();

						if (allowTaxation == 1)
						{
							jtSite.order.calculateTax(totalAmount);
						}
					}
				});
			});
		}
	},
	orders: {
	/*Initialize orders js*/
		initOrdersJs: function() {
			jQuery(document).ready(function()
			{
				jQuery('.jt_selectbox').attr('data-chosen', 'com_jticketing');

				jQuery("input[name='gateways']").change(function()
				{
					var paymentGeteway = jQuery("input[name='gateways']:checked").val();

					jQuery('#html-container').empty().html('Loading...');
					jQuery.ajax({
							url: rootUrl+'index.php?option=com_jticketing&tmpl=component&task=orders.retryPayment&order='+orderID+'&gateway_name='+paymentGeteway,
							type: 'GET',
							dataType: 'json',
							success: function(response)
							{
								jQuery('#html-container').removeClass('ajax-loading').html( response );
							}
						});
				});
			});
			Joomla.submitbutton = function (task)
			{
				if (task == 'cancel')
				{
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		},
		selectStatusOrder: function(appid,processor,ele)
		{
			document.getElementById('order_id').value = appid;
			document.getElementById('payment_status').value = ele.value;
			document.getElementById('processor').value = processor;
			if (task='orders.save')
			{
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
		},
		printDiv: function()
		{
			var printContents = document.getElementById('printDiv').innerHTML;
			var originalContents = document.body.innerHTML;
			document.body.innerHTML = printContents;
			window.print();
			document.body.innerHTML = originalContents;
		},
		showPaymentGetways: function()
		{
			if (document.getElementById('gatewaysContent').style.display=='none')
			{
				document.getElementById('gatewaysContent').style.display='block';
			}
			return false;
		}
	},
	eventform:
	{
		initEventJs: function()
		{
			jQuery(document).ready(function()
			{
				var radioValue = jQuery("input[name='jform[online_events]']:checked").val();

				jtSite.eventform.venueDisplay();
				jtSite.eventform.existingEvents();
				jtSite.eventform.selectExistingEventOnload();
				jtSite.eventform.showLocation();

				jQuery("input[name='jform[online_events]']").change(function()
				{
					jtSite.eventform.venueDisplay();
					jtSite.eventform.existingEvents();
				});

				jQuery("#jform_venue").change(function()
				{
					jtSite.eventform.showLocation();
				});
				jQuery('#jform_startdate').blur(function() {
					jtAdmin.eventform.venueDisplay();
				});
				jQuery('#jform_enddate').blur(function() {
						jtAdmin.eventform.venueDisplay();
				});
				jQuery("#jform_created_by, #jformevent_start_time_hour, #jformevent_start_time_min, #jformevent_start_time_ampm").change(function() {
					jtSite.eventform.venueDisplay();
				});
				jQuery("#jformevent_end_time_hour, #jformevent_end_time_min, #jformevent_end_time_ampm").change(function() {
					jtSite.eventform.venueDisplay();
				});

				jQuery("#jformevent_start_time_hour_chzn").change(function()
				{
					jtSite.eventform.venueDisplay();
				});

				jQuery(".venueCheck").change(function()
				{
					jtSite.eventform.slectExistingEvent();
				});

				jQuery(".existingEvent").change(function()
				{
					jtSite.eventform.existingEventSelection();
				});

				mediaGallery = JSON.parse(mediaGallery);
				jQuery.each(mediaGallery, function(key, media)
				{
					tjMediaFile.previewFile(media, 1);
				});

				jQuery('input[type=radio][name="jform[venuechoice]"]').on('click', function()
				{
					var venuechoicestatus = jQuery('input[type=radio][name="jform[venuechoice]"]:checked').val();

					if(venuechoicestatus == 'existing')
					{
						jQuery("#existingEvent").show();
					}
					else
					{
						jQuery("#existingEvent").hide();
					}
				});

				jQuery(document).on('subform-row-add', function(event, row){

					jQuery('.price').change(function(){

						var returnValue = jtSite.eventform.getRoundedValue(this.value);

						if (returnValue)
						{
							jQuery(this.id).focus();

							var error_html = '';
							error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') +  returnValue;
							jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
							return false;
						}
					});
				});

				jQuery('.price').change(function(){
					var returnValue = jtSite.eventform.getRoundedValue(this.value);

					if (returnValue)
					{
						jQuery(this.id).focus();

						var error_html = '';
						error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') +  returnValue;
						jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						return false;
					}
				});
			});

			Joomla.submitbutton = function (task)
			{
				var eventStartDate = document.getElementById('jform_startdate').value;
				var eventStartHours = document.getElementById('jformevent_start_time_hour').value;
				var eventStartMinutes = document.getElementById('jformevent_start_time_min').value;
				var eventStartAmPm = document.getElementById('jformevent_start_time_ampm').value;
				var eventEndDate = document.getElementById('jform_enddate').value;
				var eventEndHours = document.getElementById('jformevent_end_time_hour').value;
				var eventEndMinutes = document.getElementById('jformevent_end_time_min').value;
				var eventEndAmPm = document.getElementById('jformevent_end_time_ampm').value;
				var eventStartTime = jtSite.eventform.ConvertTimeformat(eventStartDate, eventStartHours, eventStartMinutes, eventStartAmPm);
				var eventEndTime = jtSite.eventform.ConvertTimeformat(eventEndDate, eventEndHours, eventEndMinutes, eventEndAmPm);
				var compareStartDate = new Date(eventStartTime);
				var compareEndDate = new Date(eventEndTime);
				var eventBookingStartDate = document.getElementById('jform_booking_start_date').value;
				var eventBookingStartHours = document.getElementById('jformbooking_start_time_hour').value;
				var eventBookingStartMinutes = document.getElementById('jformbooking_start_time_min').value;
				var eventBookingStartAmPm = document.getElementById('jformbooking_start_time_ampm').value;
				var eventBookingEndDate = document.getElementById('jform_booking_end_date').value;
				var eventBookingEndHours = document.getElementById('jformbooking_end_time_hour').value;
				var eventBookingEndMinutes = document.getElementById('jformbooking_end_time_min').value;
				var eventBookingEndAmPm = document.getElementById('jformbooking_end_time_ampm').value;
				var eventBookingStartTime = jtSite.eventform.ConvertTimeformat(eventBookingStartDate, eventBookingStartHours, eventBookingStartMinutes, eventBookingStartAmPm);
				var eventBookingEndTime = jtSite.eventform.ConvertTimeformat(eventBookingEndDate, eventBookingEndHours, eventBookingEndMinutes, eventBookingEndAmPm);
				var compareBookingStartDate = new Date(eventBookingStartTime);
				var compareBookingEndDate = new Date(eventBookingEndTime);
				var value = new Array() ;

				jQuery(".price").each(function() {
					returnValue = jtSite.eventform.getRoundedValue(jQuery(this).val());
					if (returnValue) {
						value.push(returnValue);
					}
				});

				if(task == "eventform.save")
				{
						if (value.length != 0)
						{
							jQuery(value).each(function(){
								var error_html = '';
								error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') +  value;
								jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
								return false;
							});
						}
						else if (compareEndDate <= compareStartDate) {
							var error_html = '';
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_DATE_ERROR');
							jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						}
						else if (compareBookingEndDate <= compareBookingStartDate) {
							var error_html = '';
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_DATE_ERROR');
							jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						}
						else if (compareEndDate < compareBookingEndDate) {
							var error_html = '';
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_EVENT_END_ERROR');
							jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						}
						else
						{
							var validData = document.formvalidator.isValid(document.getElementById('adminForm'));
							if(validData == true)
							{
								Joomla.submitform(task, document.getElementById('adminForm'));
							}
						}
				}
				else if (task == 'eventform.cancel')
				{
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
				else
				{
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		},
		getRoundedValue: function(value) {
			var errorMsg = '';

				jQuery.ajax({
					type: "POST",
					dataType: "json",
					data: value,
					async:false,
					url: root_url+"index.php?option=com_jticketing&format=json&task=eventform.getRoundedValue&price="+value,
					success:function(data) {

						if (data.data != value)
						{
							roundedPrice = data.data;
							errorMsg = Joomla.JText._('COM_JTICKETING_VALIDATE_ROUNDED_PRICE').concat(roundedPrice);
						}

					},
				});

			return errorMsg;

		},
		showLocation: function(){
			var venue = jQuery('#jform_venue').val();
			var onlineOfflineVenues = jQuery("input[name='jform[online_events]']:checked").val();
			if(enableOnlineVenues == 0)
			{
				if (selectedVenue > 0) {
					jQuery('#event-location').hide();
					jQuery("#jform_location").prop('required', false);
				} else if (venue == 0 || !venue) {
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
				} else {
					jQuery('#event-location').hide();
					jQuery("#jform_location").prop('required', false);
				}
			}
			else
			{
				if (selectedVenue > 0 && !venue) {
					jQuery('#event-location').hide();
					jQuery("#jform_location").prop('required', false);
				}
				else if(venue == 0 && onlineOfflineVenues == 0)
				{
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required',true);
				}
				else if (!venue && onlineOfflineVenues == 0)
				{
					var venuevalue = jQuery('#foorm_venue').val();
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required',false);
				}
				else
				{
					jQuery('#event-location').hide();
					jQuery("#jform_location").prop('required',false);
				}
			}
		},
		venueDisplay: function()
		{
			var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
			var eventStartDate=document.getElementById('jform_startdate').value;
			var eventStartHours=document.getElementById('jformevent_start_time_hour').value;
			var eventStartMinutes=document.getElementById('jformevent_start_time_min').value;
			var eventStartAmPm=document.getElementById('jformevent_start_time_ampm').value;
			var eventEndDate=document.getElementById('jform_enddate').value;
			var eventEndHours=document.getElementById('jformevent_end_time_hour').value;
			var eventEndMinutes=document.getElementById('jformevent_end_time_min').value;
			var eventEndAmPm=document.getElementById('jformevent_end_time_ampm').value;
			var eventStartTime = jtSite.eventform.ConvertTimeformat(eventStartDate, eventStartHours, eventStartMinutes, eventStartAmPm);
			var eventEndTime = jtSite.eventform.ConvertTimeformat(eventEndDate, eventEndHours, eventEndMinutes, eventEndAmPm);
			if(enforceVendor == 0)
			{
				var created_by = jQuery("input[name='jform[created_by]']").val();
			}
			var onlineOfflineVenues = jQuery("input[name='jform[online_events]']:checked").val();
			var selectedVenue = jQuery('#jform_venue').val();
			if( onlineOfflineVenues == '1')
			{
				jQuery('#event-location').hide();
				jQuery('#venuechoice_id').show();
				jQuery('#existingEvent').show();
			}
			else
			{
				jQuery('#venuechoice_id').hide();
				jQuery('#existingEvent').hide();
			}
			var userObject = {};
			userObject["radioValue"] = radioValue;
			userObject["eventStartDate"] = eventStartDate;
			userObject["eventStartTime"] = eventStartTime;
			userObject["eventEndDate"] = eventEndDate;
			userObject["eventEndTime"] = eventEndTime;
			userObject["enforceVendor"] = enforceVendor;
			if(enforceVendor == 1)
			{
				userObject["vendor_id"] = vendor_id;
			}
			else
			{
				userObject["created_by"] = created_by;
			}
			jQuery('#jform_venue, .chzn-results').empty();
			JSON.stringify(userObject);
			jQuery.ajax({
				type: "POST",
				data: userObject,
				dataType:"json",
				url: root_url + "?option=com_jticketing&format=json&task=eventform.getVenueList",
				success:function(data) {
					if(data == '' && eventId == 0 && radioValue == 1)
					{
						jQuery("#jform_venue").prop("disabled", true);
						var op="<option value='0' selected='selected'>" + Joomla.JText._('COM_JTICKETING_NO_ONLINE_VENUE_ERROR') + "</option>" ;
						jQuery('#jform_venue').append(op);
						jQuery("#jform_venue").trigger("liszt:updated");
						var error_html = '';
						error_html += "<br />" + Joomla.JText._('COM_JTICKETING_NO_VENUE_ERROR_MSG');
						jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
					}
					else
					{
						jQuery("#jform_venue").prop("disabled", false);
						if(eventId != 0)
						{
							if(venueId == 0)
							{
								jQuery('#jform_venue, .chzn-results').empty();
								venueName = Joomla.JText._('COM_JTICKETING_CUSTOM_LOCATION');
							}
							var op="<option value='"+venueId+"' selected='selected'>"  +venueName+ " </option>" ;
							jQuery('#jform_venue').append(op);
							jQuery("#jform_venue").trigger("liszt:updated");
							for(index = 0; index < data.length; ++index)
							{
								var op="<option value='"+data[index].value+"' > " + data[index]['text'] + "</option>" ;
								jQuery('#jform_venue').append(op);
								jQuery("#jform_venue").trigger("liszt:updated");
							}
						}
						else
						{
							for(index = 0; index < data.length; ++index)
							{
								var op="<option value='"+data[index].value+"' > " + data[index]['text'] + "</option>" ;
								jQuery('#jform_venue').append(op);
								jQuery("#jform_venue").trigger("liszt:updated");
							}
						}
					}
				},
			});
		},
		ConvertTimeformat:function(date, hours, minutes, amPm)
		{
			hours = parseInt(hours);
			minutes = parseInt(minutes);
			if (amPm == "PM" && hours < 12) hours = hours + 12;
			if (amPm == "AM" && hours == 12) hours = hours - 12;
			if (hours < 10) hours = "0" + hours;
			if (minutes < 10) minutes = "0" + minutes;
			var time = date+" "+hours+":"+minutes+":00";
			var utcDateTime = new Date(time).toISOString();
			utcDateTime = utcDateTime.substring(0, utcDateTime.length - 5);
			var formattedUtcDateTime = utcDateTime.replace("T", " ");
			return formattedUtcDateTime;
		},
		existingEvents: function()
		{
			var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
			var existingEventChoice = jQuery("input[name='jform[venuechoice]']:checked").val();
			if( existingEventChoice == 'new' && radioValue == '1')
			{
				jQuery('#form_existingEvent').hide();
			}
			else
			{
				jQuery('#form_existingEvent').show();
			}
		},
		slectExistingEvent: function()
		{
			var venueId = document.getElementById("jform_venue").value;
			var venuestatus = jQuery('input[type=radio][name="jform[online_events]"]:checked').val();

			if(venueId != '0' && venuestatus == '1')
			{
				techjoomla.jQuery.ajax({
					type: 'POST',
					data:{
					'venueId':venueId
					},
					dataType: 'json',
					url: root_url + 'index.php?option=com_jticketing&format=json&task=eventform.getAllMeetings',

					beforeSend: function () {
						jQuery('#ajax_loader').show();
						jQuery('#ajax_loader').html("<img src=" + root_url + "media/com_jticketing/images/ajax-loader.gif>");
						jQuery('#ajax_loader').css('display','block');
					},
					complete: function () {
						jQuery('#ajax_loader').hide();
					},
					success: function(data)
					{
						techjoomla.jQuery('#jform_existing_event option').remove();
						var option, index;
						var eventList = data['0']['report-bulk-objects']['row'];
						if (eventList !== undefined && eventList !== null)
						{
							var op = "<option value='abcde' selected='selected'>" + Joomla.JText._('COM_JTICKETING_FORM_SELECT_EXISTING_EVENT_OPTION') + "</option>";
							techjoomla.jQuery("#jform_existing_event").append(op);
							for(index = 0; index < eventList.length; ++index)
							{
								var eventvalue = eventList[index]['url'] .replace(/^\s+|\s+$/g, "");

								if(existing_url==eventvalue)
								{
									var op="<option value='"+eventvalue+"' selected='selected'>"  +eventList[index]['name']+ " (" + eventList[index]['date-modified'] + ")"+ "</option>" ;
								}
								else
								{
									var op="<option value='"+eventvalue+"' >"  +eventList[index]['name']+ " (" + eventList[index]['date-modified'] + ")"+ "</option>" ;
								}

								techjoomla.jQuery('#jform_existing_event').append(op);
							}

							jQuery("#jform_existing_event").trigger("liszt:updated");
						}
					},
					error: function(response)
					{
						console.log(' ERROR!!' );
						return;
					}
					});
			}
		},
		selectExistingEventOnload: function() {
		var venuestatus = jQuery('input[type=radio][name="jform[online_events]"]:checked').val();
		if(venueId != '0' && venuestatus == '1')
		{
			jQuery.ajax({
				type: 'POST',
				data:{
				'venueId':venueId
				},
				dataType: 'json',
				url: root_url + 'index.php?option=com_jticketing&format=json&task=eventform.getAllMeetings',
				success: function(data)
				{
					jQuery('#jform_existing_event option').remove();
					var option, index;
					var eventList = data['0']['report-bulk-objects']['row'];
					if (eventList !== undefined && eventList !== null)
					{
						var op = "<option value='abcde' selected='selected'>" + Joomla.JText._('COM_JTICKETING_FORM_SELECT_EXISTING_EVENT_OPTION') + "</option>";
						jQuery("#jform_existing_event").append(op);
						for(index = 0; index < eventList.length; ++index)
						{
							var eventvalue = eventList[index]['url'] .replace(/^\s+|\s+$/g, "");

							if(existing_url==eventvalue)
							{
								var op="<option value='"+eventvalue+"' selected='selected'>"  +eventList[index]['name']+ " (" + eventList[index]['date-modified'] + ")"+ "</option>" ;
							}
							else
							{
								var op="<option value='"+eventvalue+"' >"  +eventList[index]['name']+ " (" + eventList[index]['date-modified'] + ")"+ "</option>" ;
							}
							jQuery('#jform_existing_event').append(op);
						}
						jQuery("#jform_existing_event").trigger("liszt:updated");
					}
				},
				error: function(response)
				{
					//jQuery('').show('slow');
					// show ckout error msg
					console.log(' ERROR!!' );
					return;
				}
				});
			}
		},
		existingEventSelection: function()
		{
			var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
			var existingEventChoice = jQuery("input[name='jform[venuechoice]']:checked").val();

			if( existingEventChoice == 'new' && radioValue == '1')
			{
				jQuery('#form_existingEvent').hide();
			}
			else
			{
				jQuery('#form_existingEvent').show();
			}
		}
	}
}
var jtAdmin = {
	event: {
		/*Initialize event js*/
			initEventJs: function() {
				jQuery(document).ready(function() {
					var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
					jtAdmin.event.venueDisplay();
					jtAdmin.event.existingEvents();
					jtAdmin.event.showLocation();
					jtAdmin.event.selectExistingEventOnload();
					jQuery("input[name='jform[online_events]']").change(function() {
						jtAdmin.event.venueDisplay();
						jtAdmin.event.existingEvents();
					});
					mediaGallery = JSON.parse(mediaGallery);

					jQuery.each( mediaGallery, function( key, media ){
				  		tjMediaFile.previewFile(media, 1);
					});
					jQuery("#jform_venue").change(function() {
						jtAdmin.event.showLocation();
					});
					jQuery('#jform_startdate').blur(function() {
						jtAdmin.event.venueDisplay();
					});
					jQuery('#jform_enddate').blur(function() {
						jtAdmin.event.venueDisplay();
					});
					jQuery("#jform_created_by, #jformevent_start_time_hour, #jformevent_start_time_min, #jformevent_start_time_ampm").change(function() {
						jtAdmin.event.venueDisplay();
					});
					jQuery("#jformevent_end_time_hour, #jformevent_end_time_min, #jformevent_end_time_ampm").change(function() {
						jtAdmin.event.venueDisplay();
					});
					jQuery(".venueCheck").change(function()
					{
						jtAdmin.event.slectExistingEvent();
					});
					jQuery("#jformevent_start_time_hour_chzn").change(function()
					{
						jtAdmin.event.venueDisplay();
					});
					jQuery(".existingEvent").change(function()
					{
						jtAdmin.event.existingEventSelection();
					});
					jQuery("#jform_created_by").change(function()
					{
						jtAdmin.event.checkUserEmail();
					});
					jQuery('input[type=radio][name="jform[venuechoice]"]').on('click', function(){
					var venuechoicestatus = jQuery('input[type=radio][name="jform[venuechoice]"]:checked').val();
						if(venuechoicestatus == 'existing')
						{
							jQuery("#existingEvent").show();
						}
						else
						{
							jQuery("#existingEvent").hide();
						}
					});

					jQuery(document).on('subform-row-add', function(event, row){

						jQuery('.price').change(function(){

							var returnValue = jtAdmin.event.getRoundedValue(this.value);

							if (returnValue)
							{
								jQuery(this.id).focus();

								var error_html = '';
								error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') +  returnValue;
								jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
								return false;
							}
						});
					});

					jQuery('.price').change(function(){
						var returnValue = jtAdmin.event.getRoundedValue(this.value);

						if (returnValue)
						{
							jQuery(this.id).focus();

							var error_html = '';
							error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') +  returnValue;
							jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
							return false;
						}
					});

				});
				Joomla.submitbutton = function (task)
				{
					var eventStartDate=document.getElementById('jform_startdate').value;
					var eventStartHours=document.getElementById('jformevent_start_time_hour').value;
					var eventStartMinutes=document.getElementById('jformevent_start_time_min').value;
					var eventStartAmPm=document.getElementById('jformevent_start_time_ampm').value;
					var eventEndDate=document.getElementById('jform_enddate').value;
					var eventEndHours=document.getElementById('jformevent_end_time_hour').value;
					var eventEndMinutes=document.getElementById('jformevent_end_time_min').value;
					var eventEndAmPm=document.getElementById('jformevent_end_time_ampm').value;
					var eventStartTime = jtAdmin.event.ConvertTimeformat(eventStartDate, eventStartHours, eventStartMinutes, eventStartAmPm);
					var eventEndTime = jtAdmin.event.ConvertTimeformat(eventEndDate, eventEndHours, eventEndMinutes, eventEndAmPm);
					var compareStartDate = new Date(eventStartTime);
					var compareEndDate = new Date(eventEndTime);
					var eventBookingStartDate=document.getElementById('jform_booking_start_date').value;
					var eventBookingStartHours=document.getElementById('jformbooking_start_time_hour').value;
					var eventBookingStartMinutes=document.getElementById('jformbooking_start_time_min').value;
					var eventBookingStartAmPm=document.getElementById('jformbooking_start_time_ampm').value;
					var eventBookingEndDate=document.getElementById('jform_booking_end_date').value;
					var eventBookingEndHours=document.getElementById('jformbooking_end_time_hour').value;
					var eventBookingEndMinutes=document.getElementById('jformbooking_end_time_min').value;
					var eventBookingEndAmPm=document.getElementById('jformbooking_end_time_ampm').value;
					var eventBookingStartTime = jtAdmin.event.ConvertTimeformat(eventBookingStartDate, eventBookingStartHours, eventBookingStartMinutes, eventBookingStartAmPm);
					var eventBookingEndTime = jtAdmin.event.ConvertTimeformat(eventBookingEndDate, eventBookingEndHours, eventBookingEndMinutes, eventBookingEndAmPm);
					var compareBookingStartDate = new Date(eventBookingStartTime);
					var compareBookingEndDate = new Date(eventBookingEndTime);

					var value = new Array() ;

					jQuery(".price").each(function() {
						returnValue = jtAdmin.event.getRoundedValue(jQuery(this).val());
						if (returnValue) {
							value.push(returnValue);
						}
					});

					if(task == "event.save" || task == "event.save2new" || task == "event.apply")
					{
						if (value.length != 0)
						{
							jQuery(value).each(function(){
								var error_html = '';
								error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') +  value;
								jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
								return false;
							});
						}
						else if(compareEndDate <= compareStartDate)
						{
							var error_html = '';
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_DATE_ERROR');
							jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						}
						else if(compareBookingEndDate <= compareBookingStartDate)
						{
							var error_html = '';
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_DATE_ERROR');
							jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						}
						else if(compareEndDate < compareBookingEndDate)
						{
							var error_html = '';
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_EVENT_END_ERROR');
							jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						}
						else
						{
							var validData = document.formvalidator.isValid(document.getElementById('adminForm'));
							if(validData == true)
							{
								jtAdmin.event.showLocation();
								Joomla.submitform(task, document.getElementById('adminForm'));
							}
						}

					}
					else if (task == 'event.cancel')
					{
						Joomla.submitform(task, document.getElementById('adminForm'));
					}
					else
					{
						Joomla.submitform(task, document.getElementById('adminForm'));
					}
				}
			},
			showLocation: function() {
				var venue = jQuery('#jform_venue').val();
				var onlineOfflineVenues = jQuery("input[name='jform[online_events]']:checked").val();
				if(enableOnlineVenues == 0)
				{
					if(selectedVenue > 0)
					{
						jQuery('#event-location').hide();
						jQuery("#jform_location").prop('required',false);
					}
					else if(venue == 0 || !venue)
					{
						jQuery('#event-location').show();
						jQuery("#jform_location").prop('required',true);
					}
					else
					{
						jQuery('#event-location').hide();
						jQuery("#jform_location").prop('required',false);
					}
				}
				else
				{
					if (selectedVenue > 0 && !venue) {
						jQuery('#event-location').hide();
						jQuery("#jform_location").prop('required', false);
					}
					else if(venue == 0 && onlineOfflineVenues == 0)
					{
						jQuery('#event-location').show();
						jQuery("#jform_location").prop('required',true);
					}
					else if (!venue && onlineOfflineVenues == 0)
					{
						var venuevalue = jQuery('#jform_venue').val();
						jQuery('#event-location').show();
						jQuery("#jform_location").prop('required',true);
					}
					else
					{
						jQuery('#event-location').hide();
						jQuery("#jform_location").prop('required',false);
					}
				}
			},
			checkUserEmail: function() {
				var user=document.getElementById('jform_created_by_id').value;
				var userObject = {};
				userObject["user"] = user;
				JSON.stringify(userObject) ;
				jQuery.ajax({
					type: "POST",
					dataType: "json",
					data: userObject,
					url: "index.php?option=com_jticketing&format=json&task=event.checkUserEmail",
					success:function(data) {
						jQuery('#warning_message').empty();
						if(data.check)
						{
							if(array_check == 1 || handle_transactions == 1)
							{
								jQuery("#warning_message").html('<div class="alert alert-warning">'
								+Joomla.JText._('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG1') +
								'<a href= "index.php?option=com_tjvendors&view=vendor&layout=update&client=com_jticketing&vendor_id=' +data.vendor_id + '" target="_blank">'
								+Joomla.JText._('COM_JTICKETING_VENDOR_FORM_LINK') + '</a>'
								+Joomla.JText._('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG2'));
							}

						}
					},
				});
			},

		getRoundedValue: function(value) {

			var errorMsg = '';

				jQuery.ajax({
					type: "POST",
					dataType: "json",
					data: value,
					async:false,
					url: "index.php?option=com_jticketing&format=json&task=event.getRoundedValue&price="+value,
					success:function(data) {

						if (data.data != value)
						{
							roundedPrice = data.data;
							errorMsg = Joomla.JText._('COM_JTICKETING_VALIDATE_ROUNDED_PRICE').concat(roundedPrice);
						}

					},
				});

				return errorMsg;
			},
			venueDisplay: function() {
				var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
				var eventStartDate=document.getElementById('jform_startdate').value;
				var eventStartHours=document.getElementById('jformevent_start_time_hour').value;
				var eventStartMinutes=document.getElementById('jformevent_start_time_min').value;
				var eventStartAmPm=document.getElementById('jformevent_start_time_ampm').value;
				var eventEndDate=document.getElementById('jform_enddate').value;
				var eventEndHours=document.getElementById('jformevent_end_time_hour').value;
				var eventEndMinutes=document.getElementById('jformevent_end_time_min').value;
				var eventEndAmPm=document.getElementById('jformevent_end_time_ampm').value;
				var eventStartTime = jtAdmin.event.ConvertTimeformat(eventStartDate, eventStartHours, eventStartMinutes, eventStartAmPm);
				var eventEndTime = jtAdmin.event.ConvertTimeformat(eventEndDate, eventEndHours, eventEndMinutes, eventEndAmPm);
				var created_by = document.getElementById('jform_created_by_id').value;
				var onlineOfflineVenues = jQuery("input[name='jform[online_events]']:checked").val();
				var selectedVenue = jQuery('#jform_venue').val();
				if( onlineOfflineVenues == '1')
				{
					jQuery('#event-location').hide();
					jQuery('#venuechoice_id').show();
					jQuery('#existingEvent').show();
				}
				else
				{
					jQuery('#venuechoice_id').hide();
					jQuery('#existingEvent').hide();
				}
				var userObject = {};
				userObject["radioValue"] = radioValue;
				userObject["eventStartDate"] = eventStartDate;
				userObject["eventStartTime"] = eventStartTime;
				userObject["eventEndDate"] = eventEndDate;
				userObject["eventEndTime"] = eventEndTime;
				userObject["created_by"] = created_by;
				jQuery('#jform_venue, .chzn-results').empty();
				JSON.stringify(userObject);
				jQuery.ajax({
					type: "POST",
					data: userObject,
					dataType:"json",
					url: "index.php?option=com_jticketing&format=json&task=event.getVenueList",
					success:function(data) {
						if(data == '' && eventId == 0 && radioValue == 1)
						{
							jQuery("#jform_venue").prop("disabled", true);
							var op="<option value='0' selected='selected'>" + Joomla.JText._('COM_JTICKETING_NO_ONLINE_VENUE_ERROR') + "</option>" ;
							jQuery('#jform_venue').append(op);
							jQuery("#jform_venue").trigger("liszt:updated");
							var error_html = '';
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_NO_VENUE_ERROR_MSG');
							jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						}
						else
						{
							jQuery("#jform_venue").prop("disabled", false);
							if(eventId != 0)
							{
								if(venueId == 0)
								{
									jQuery('#jform_venue, .chzn-results').empty();
									venueName = Joomla.JText._('COM_JTICKETING_CUSTOM_LOCATION');
								}
								var op="<option value='"+venueId+"' selected='selected'>"  +venueName+ " </option>" ;
								jQuery('#jform_venue').append(op);
								jQuery("#jform_venue").trigger("liszt:updated");
								for(index = 0; index < data.length; ++index)
								{
									var op="<option value='"+data[index].value+"' > " + data[index]['text'] + "</option>" ;
									jQuery('#jform_venue').append(op);
									jQuery("#jform_venue").trigger("liszt:updated");
								}
							}
							else
							{
								for(index = 0; index < data.length; ++index)
								{
									var op="<option value='"+data[index].value+"' > " + data[index]['text'] + "</option>" ;
									jQuery('#jform_venue').append(op);
									jQuery("#jform_venue").trigger("liszt:updated");
								}
							}
						}
					},
				});
			},
			ConvertTimeformat:function(date, hours, minutes, amPm) {
				hours = parseInt(hours);
				minutes = parseInt(minutes);
				if (amPm == "PM" && hours < 12) hours = hours + 12;
				if (amPm == "AM" && hours == 12) hours = hours - 12;
				if (hours < 10) hours = "0" + hours;
				if (minutes < 10) minutes = "0" + minutes;
				var time = date+" "+hours+":"+minutes+":00";
				var utcDateTime = new Date(time).toISOString();
				utcDateTime = utcDateTime.substring(0, utcDateTime.length - 5);
				var formattedUtcDateTime = utcDateTime.replace("T", " ");
				return formattedUtcDateTime;
			},
			/* To hide and show existing events on load*/
			existingEvents: function() {
				var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
				var existingEventChoice = jQuery("input[name='jform[venuechoice]']:checked").val();
				if( existingEventChoice == 'new' && radioValue == '1')
				{
					jQuery('#form_existingEvent').hide();
				}
				else
				{
					jQuery('#form_existingEvent').show();
				}
			},
		slectExistingEvent: function() {
		var venueId = document.getElementById("jform_venue").value;
		var venuestatus = jQuery('input[type=radio][name="jform[online_events]"]:checked').val();
		if(venueId != '0' && venuestatus == '1')
		{
			jQuery.ajax({
				type: 'POST',
				data:{
				'venueId':venueId
				},
				dataType: 'json',
				url: 'index.php?option=com_jticketing&format=json&task=event.getAllMeetings',
				beforeSend: function () {
					jQuery('#ajax_loader').show();
					jQuery('#ajax_loader').html("<img src=" + root_url + "administrator/components/com_jticketing/assets/images/ajax-loader.gif>");
					jQuery('#ajax_loader').css('display','block');
				},
				complete: function () {
					jQuery('#ajax_loader').hide();
				},
				success: function(data)
				{
					jQuery('#jform_existing_event option').remove();
					var option, index;
					var eventList = data['0']['report-bulk-objects']['row'];
					if (eventList !== undefined && eventList !== null)
					{
						var op = "<option value='abcde' selected='selected'>" + Joomla.JText._('COM_JTICKETING_FORM_SELECT_EXISTING_EVENT_OPTION') + "</option>";
						jQuery("#jform_existing_event").append(op);
						for(index = 0; index < eventList.length; ++index)
						{
							var eventvalue = eventList[index]['url'] .replace(/^\s+|\s+$/g, "");
							if(existing_url==eventvalue)
							{
								var op="<option value='"+eventvalue+"' selected='selected'>"  +eventList[index]['name']+ " (" + eventList[index]['date-modified'] + ")"+ "</option>" ;
							}
							else
							{
								var op="<option value='"+eventvalue+"' >"  +eventList[index]['name']+ " (" + eventList[index]['date-modified'] + ")"+ "</option>" ;
							}
							jQuery('#jform_existing_event').append(op);
						}
						jQuery("#jform_existing_event").trigger("liszt:updated");
					}
				},
				error: function(response)
				{
					//jQuery('').show('slow');
					// show ckout error msg
					console.log(' ERROR!!' );
					return;
				}
				});
			}
		},
		selectExistingEventOnload: function() {
		var venuestatus = jQuery('input[type=radio][name="jform[online_events]"]:checked').val();
		if(venueId != '0' && venuestatus == '1')
		{
			jQuery.ajax({
				type: 'POST',
				data:{
				'venueId':venueId
				},
				dataType: 'json',
				url: 'index.php?option=com_jticketing&format=json&task=event.getAllMeetings',
				success: function(data)
				{
					jQuery('#jform_existing_event option').remove();
					var option, index;
					var eventList = data['0']['report-bulk-objects']['row'];
					if (eventList !== undefined && eventList !== null)
					{
						var op = "<option value='abcde' selected='selected'>" + Joomla.JText._('COM_JTICKETING_FORM_SELECT_EXISTING_EVENT_OPTION') + "</option>";
						jQuery("#jform_existing_event").append(op);
						for(index = 0; index < eventList.length; ++index)
						{
							var eventvalue = eventList[index]['url'] .replace(/^\s+|\s+$/g, "");

							if(existing_url==eventvalue)
							{
								var op="<option value='"+eventvalue+"' selected='selected'>"  +eventList[index]['name']+ " (" + eventList[index]['date-modified'] + ")"+ "</option>" ;
							}
							else
							{
								var op="<option value='"+eventvalue+"' >"  +eventList[index]['name']+ " (" + eventList[index]['date-modified'] + ")"+ "</option>" ;
							}
							jQuery('#jform_existing_event').append(op);
						}
						jQuery("#jform_existing_event").trigger("liszt:updated");
					}
				},
				error: function(response)
				{
					//jQuery('').show('slow');
					// show ckout error msg
					console.log(' ERROR!!' );
					return;
				}
				});
			}
		},
	existingEventSelection :function()
	{
		var venueId = document.getElementById("jform_venue").value;
		var venueurl = jQuery('#jform_existing_event :selected').val();
		jQuery("#event_url").val(venueurl);
		jQuery.ajax({
			type: 'POST',
			async: false,
			data:
			{
				'venueId':venueId,
				'venueurl':venueurl
			},
			dataType: 'json',
			url: 'index.php?option=com_jticketing&format=json&task=event.getScoID',
			success: function(data)
			{
				jQuery("#event_sco_id").val(data);
			},
			error: function(response)
			{
				// show ckout error msg
				console.log(' ERROR!!' );
				return;
			}
		});
	},
			validateDates: function () {
			var event_start_date_old = jQuery('#jform_startdate').val();
			var event_end_date_old  = jQuery('#jform_enddate').val();
			var booking_start_date_old  = jQuery('#jform_booking_start_date').val();
			var booking_end_date_old  = jQuery('#jform_booking_end_date').val();
			return true;
		}
	},
	orders: {
	/*Initialize orders js*/
		initOrdersJs: function() {
			Joomla.submitbutton = function (task)
			{
				if (task =='orders.remove')
				{
					var result = confirm(Joomla.JText._('COM_JTICKETING_ORDER_DELETE_CONF'));

					if (result != true)
					{
						return false;
					}

					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		},
		selectStatusOrder: function(appid,processor,ele)
		{
			document.getElementById('order_id').value = appid;
			document.getElementById('payment_status').value = ele.value;
			document.getElementById('processor').value = processor;

			if (task='orders.save')
			{
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
		}
	},
	venue:
	{
		/* Google Map autosuggest  for location */
		initializeGMapSuggest: function ()
		{
			input = document.getElementById('jform_address');
			var autocomplete = new google.maps.places.Autocomplete(input);
		},

		initVenueJs: function()
		{
			google.maps.event.addDomListener(window, 'load', jtAdmin.venue.initializeGMapSuggest);
			jQuery(document).ready(function()
			{
				jQuery('input[name="jform[online]').click(function()
				{
					jtAdmin.venue.showOnlineOffline(jQuery('input[name="jform[online]"]:checked').val())
				});

				jtAdmin.venue.showOnlineOffline(jQuery('input[name="jform[online]"]:checked').val());

				jQuery('#jformonline_provider').change(function()
				{
					jtAdmin.venue.getPluginParams();
				});

				if (editId && jQuery('input[name="jform[online]"]:checked').val() == 1)
				{
					jtAdmin.venue.getPluginParams();
				}

				if (getValue)
				{
					jQuery('#jformonline_provider').trigger('change');
				}
			});
		},
		showOnlineOffline: function (ifonline)
		{
			if (ifonline == 1)
			{
				jQuery("#jformonline_provider").closest(".control-group").show();
				jQuery("#provider_html").show();
				jQuery("#jformoffline_provider").hide();
			}
			else
			{
				jQuery("#jformonline_provider").closest(".control-group").hide();
				jQuery("#provider_html").hide();
				jQuery("#jformoffline_provider").show();
			}
		},
		venueSubmitButton: function(task)
		{
			if (task == 'venue.apply' || task == 'venue.save' || task == 'venue.save2new')
			{
				var venue_name = jQuery('input[name="jform[jform_name]"]:checked').val();
				var api_username = jQuery('input[name="jform[api_username]"]:checked').val();
				var api_password = jQuery('input[name="jform[api_password]"]:checked').val();
				var host_url = jQuery('input[name="jform[host_url]"]:checked').val();
				var source_sco_id = jQuery('input[name="jform[source_sco_id]"]:checked').val();
				var onlines = jQuery('input[name="jform[online]"]:checked').val();
				var onlineProvider  = jQuery('#jformonline_provider').val();
				if(editId && onlines == "0")
				{
					jQuery('#api_username').val('');
					jQuery('#api_password').val('');
					jQuery('#host_url').val('');
					jQuery('#source_sco_id').val('');
				}
				if (jQuery('input[name="jform[online]"]:checked').val() == 1)
				{
					if (!onlineProvider || onlineProvider == '0')
					{
						error_html = Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_ONLINE_EVENTS_PROVIDER')
						jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						return false;
					}
					if (!document.formvalidator.isValid('#provider_html'))
					{
						return false;
					}
					jsonObj = [];
					jQuery('#provider_html input').each(function() {
					var id = jQuery(this).attr("id");
					var output = jQuery(this).val();
					item = {}
					item ["id"] = id;
					item ["output"] = output;
					var source = jsonObj.push(item);
					jsonString = JSON.stringify(item);
					jQuery("#venue_params").val(jsonString);
					});
				}
				else
				{
					if (!jQuery("#jform_address").val())
					{
						var error_html = '';
						if (!jQuery("#jform_address").val())
						{
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_FORM_LBL_VENUE_ADDRESS');
						}
						jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
						return false;
					}
				}
				if (!jQuery("#jform_name").val())
				{
					var error_html = '';
					error_html += "<br />" + Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_FORM_LBL_VENUE_TITLE');
					jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
					return false;
				}
				jQuery("#system-message-container").html("");
				Joomla.submitform(task, document.getElementById('venue-form'));
			}
			if (task == 'venue.cancel')
			{
				Joomla.submitform(task, document.getElementById('venue-form'));
			}
		},
		getPluginParams: function()
		{
			jQuery('#jformonline_provider').change(function()
			{
				var element = jQuery(this).val();
				jQuery.ajax({
				type:'POST',
				url:'index.php?option=com_jticketing&task=venue.getelementparams',
				data: {element:element,venue_id:jQuery("#venue_id").val()},
				datatype:"HTML",
				async: 'false',
				success:function(response){
					jQuery('#provider_html').html(response);
					var online = jQuery('input[name="jform[online]"]:checked').val();
					jQuery('#provider_html').css('display', 'none');
					if(online == 1)
					{
						jQuery('#provider_html').css('display', 'block');
					}
					},
					error: function() {
						jQuery('#provider_html').hide();
						return true;
						},
					});
			});
			// Google Map autosuggest  for location
			function initialize()
			{
				input = document.getElementById('jform_address');
				var autocomplete = new google.maps.places.Autocomplete(input);
			}
			google.maps.event.addDomListener(window, 'load', initialize);
		},
		// Function : For finding longitude latitude of selected address
		getLongitudeLatitude: function()
		{
			var geocoder = new google.maps.Geocoder();
			var address = jQuery('#jform_address').val();
			geocoder.geocode({ 'address': address}, function(results, status)
			{
				if (status == google.maps.GeocoderStatus.OK)
				{
					var latitude = results[0].geometry.location.lat();
					var longitude = results[0].geometry.location.lng();
					jQuery('#jform_latitude').val(latitude);
					jQuery('#jform_longitude').val(longitude);
				}
			});
		},
		// Function : For Get Current Location
		getCurrentLocation: function()
		{
			if (navigator.geolocation)
			{
				navigator.geolocation.getCurrentPosition(showLocation);
			}
			else
			{
				var address = Joomla.JText._('COM_JTICKETING_ADDRESS_NOT_FOUND');
				var lonlatval = Joomla.JText._('COM_JTICKETING_LONG_LAT_VAL');
				jQuery('#jform_address').val(address);
				jQuery("#jform_longitude").val(lonlatval);
				jQuery("#jform_latitude").val(lonlatval);
			}
			// Function : For Showing user current location
			function showLocation(position)
			{
				var latitude = position.coords.latitude;
				var longitude = position.coords.longitude;
				jQuery.ajax({
					type:'POST',
					url:'index.php?option=com_jticketing&task=venue.getLocation',
					data:'latitude='+latitude+'&longitude='+longitude,
					dataType: 'json',
					success:function(data)
					{
						console.log(data);
						var address = data["location"];
						var longitude = data["longitude"];
						var latitude = data["latitude"];
						if(data)
						{
							jQuery("#jform_address").val(address);
							jQuery("#jform_longitude").val(longitude);
							jQuery("#jform_latitude").val(latitude);
						}
					}
				});
			}
		}
	},

	coupon: {
		initCouponJs: function()
		{
			jQuery(document).ready(function()
			{
				jQuery("#jform_value").change(function()
				{
					jtAdmin.coupon.checkForZeroAndAlpha(this, '46', Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));
				});
				jQuery("#jform_max_use").change(function()
				{
					jtAdmin.coupon.checkForZeroAndAlpha(this, '46', Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));
				});
				jQuery("#jform_max_per_user").change(function()
				{
					jtAdmin.coupon.checkForZeroAndAlpha(this, '46', Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));
				});
			});
		},

		couponSubmitButton: function(task)
		{
			if (task == 'coupon.apply')
			{
				var checkdate = jtAdmin.coupon.checkDates();
				if(checkdate === 0)
				{
					return false;
				}

				var check = jtAdmin.coupon.coupounDuplicateCheck();
				if(check === 0)
				{
					return false;
				}
			}

			if (task == 'coupon.save')
			{
				var checkdate = jtAdmin.coupon.checkDates();
				if(checkdate === 0)
				{
					return false;
				}

				var check = jtAdmin.coupon.coupounDuplicateCheck();
				if(check === 0)
				{
					return false;
				}
			}

			if (task == 'coupon.save2new')
			{
				var checkdate = jtAdmin.coupon.checkDates();
				if(checkdate === 0)
				{
					return false;
				}

				var check = jtAdmin.coupon.coupounDuplicateCheck();
				if(check === 0)
				{
					return false;
				}
			}

			if (task == 'coupon.cancel')
			{
				Joomla.submitform(task, document.getElementById('coupon-form'));
			}
			else
			{
				if (task != 'coupon.cancel' && document.formvalidator.isValid(document.id('coupon-form')))
				{
					Joomla.submitform(task, document.getElementById('coupon-form'));
				}
				else
				{
					alert(Joomla.JText._('JGLOBAL_VALIDATION_FORM_FAILED'));
				}
			}

			document.adminForm.submit();
		},

		checkForZeroAndAlpha: function(ele,allowedChar,msg)
		{
			if(ele.value <= 0)
			{
				alert(Joomla.JText._('COM_JTICKETING_MIN_AMT_SHOULD_GREATER_MSG'));
				ele.value='';
			}
		},

		coupounDuplicateCheck: function()
		{
			var coupon_code=document.getElementById('jform_code').value;
			var duplicatecode = 0;

			if (parseInt(cid) == 0)
			{
				var url = "index.php?option=com_jticketing&tmpl=component&task=coupon.getcode&selectedcode=" + coupon_code;
			}
			else
			{
				var url = "index.php?option=com_jticketing&task=coupon.getselectcode&tmpl=component&couponid=" + cid + "&selectedcode=" + coupon_code;
			}

			jQuery.ajax({
			url:url,
			type: 'GET',
			async:false,
			success: function(response) {
					if (parseInt(response)==1)
					{
						alert(Joomla.JText._('COM_JTICKETING_DUPLICATE_COUPON'));
						duplicatecode = 1;
					}
					else
					{
						return 1;
					}
				}
			});

			if(duplicatecode === 1)
			{
				return 0;
			}
		},

		/** Function to check coupon valid from and valid to date **/
		checkDates: function()
		{
			var selectedFromDate = document.getElementById('jform_from_date').value;
			var selectedToDate = document.getElementById('jform_from_date').value;
			startDate = new Date(selectedFromDate);
			startDate.setHours(0, 0, 0, 0);
			endDate = new Date(selectedToDate);
			endDate.setHours(0, 0, 0, 0);

			var today = new Date();
			today.setHours(0, 0, 0, 0);

			/** Coupon expiry date should not be less than from date **/
			if (document.getElementById('jform_exp_date').value !='')
			{
				if (document.getElementById('jform_from_date').value > document.getElementById('jform_exp_date').value)
				{
					alert(Joomla.JText._('COM_JTICKETING_DATE_ERROR_MSG'));
					jQuery('#jform_from_date').focus();
					return 0;
				}
				else
				{
					return 1;
				}
			}
		}
	}
}

// To show online/offline venue
function showOnlineOffline(ifonline)
{
	if (ifonline == 1)
	{
		jQuery("#jformonline_provider").closest(".control-group").show();
		jQuery("#provider_html").show();
		jQuery("#jformoffline_provider").hide();
	}
	else
	{
		jQuery("#jformonline_provider").closest(".control-group").hide();
		jQuery("#provider_html").hide();
		jQuery("#jformoffline_provider").show();
	}
}

// To show online/offline venue
function validateOnlineOffline(ifonline)
{
	if (ifonline == 1)
	{
		jQuery("#jformonline_provider").closest(".form-group").show();
		jQuery("#provider_html").show();
		jQuery("#jformoffline_provider").hide();
		jQuery('#jform_address').removeAttr("required");
		jQuery('#jform_address').removeClass("required");
	}
	else
	{
		jQuery("#jformonline_provider").closest(".form-group").hide();
		jQuery("#provider_html").hide();
		jQuery("#jformoffline_provider").show();
		jQuery('#api_username').removeAttr("required");
		jQuery('#api_username').removeClass("required");
		jQuery('#host_url').removeAttr("required");
		jQuery('#host_url').removeClass("required");
		jQuery('#api_password').removeAttr("required");
		jQuery('#api_password').removeClass("required");
		jQuery('#source_sco_id').removeAttr("required");
		jQuery('#source_sco_id').removeClass("required");
	}
}

var tjMediaFile = {
	validateFile : function (thisFile, isGallary, isAdmin)
	{
		var uploadType = jQuery(thisFile).attr('type');

		if (uploadType == 'file')
		{
			var uploadedfile = jQuery(thisFile)[0].files[0];

			if (mediaSize < (uploadedfile.size/1000000)){
				alert(Joomla.JText._('COM_TJMEDIA_VALIDATE_MEDIA_SIZE'));

				return false;
			}

			tjMediaFile.uploadFile(uploadedfile, thisFile, uploadType, isGallary, isAdmin);

		}
		else
		{
			fileLink = jQuery('#jform_gallery_link').val();
			fileLink = tjMediaFile.validateYouTubeUrl(fileLink);

			if (!fileLink)
			{
				alert(Joomla.JText._('COM_TJMEDIA_VALIDATE_YOUTUBE_URL'));
				return false;
			}

			tjMediaFile.uploadFile(fileLink, thisFile, 'link', isGallary, isAdmin);
		}
	},

	uploadFile : function (uploadedfile, thisFile, uploadType, isGallary, isAdmin)
	{
		var mediaformData = new FormData();

		if (uploadType == 'file')
		{
			mediaformData.append('file', uploadedfile);
			mediaformData.append('upload_file', uploadType);
			mediaformData.append('isGallary', isGallary);
		}
		else if (uploadType == 'link')
		{
			mediaformData.append('upload_type', uploadType);
			mediaformData.append('name', uploadedfile);
			mediaformData.append('type', 'youtube');
		}

		if (isAdmin != 0)
		{
			url = "index.php?option=com_jticketing&format=json&task=event.uploadMedia";
		}
		else
		{
			url = jticketing_baseurl + "index.php?option=com_jticketing&format=json&task=eventform.uploadMedia";
		}

		this.ajaxObj = jQuery.ajax({
			type: "POST",
			url: url,
			dataType:'JSON',
			contentType: false,
			processData: false,
			data: mediaformData,
			xhr: function()
			{
				var myXhr = jQuery.ajaxSettings.xhr();

				if(myXhr.upload)
				{
					myXhr.upload.addEventListener('progress', function (e){

						if(e.lengthComputable)
						{
							var percentage = Math.floor((e.loaded / e.total) * 100);
							tjMediaFile.progressBar.updateStatus(thisFile.id, percentage);
						}
					}, false);
				}

				return myXhr;
			},

			beforeSend: function(x)
			{
				tjMediaFile.progressBar.init(thisFile.id, '');
			},

			success: function (data)
			{
				if (isGallary == 1)
				{
					if (data.data.type == 'video.youtube')
					{
						jQuery('#jform_gallery_link').val('');
					}
					else
					{
						jQuery(thisFile).val('');
					}
				}

				if (data.success)
				{
					tjMediaFile.previewFile(data.data, isGallary);
					tjMediaFile.progressBar.statusMsg(thisFile.id, 'success', data.message);
				}
				else
				{
					jQuery(thisFile).val('');
					jQuery('#'+thisFile.id).siblings('.progress').remove();
					tjMediaFile.progressBar.statusMsg(thisFile.id, 'error', data.message);
				}
			},

			error: function(xhr,status,error)
			{
				tjMediaFile.progressBar.statusMsg(thisFile.id, 'error', error);
			}
		});
	},
	previewFile : function (data, isGallary)
	{
		if (data.id)
		{
			if (isGallary == 1)
			{
				tjMediaFile.tjMediaGallery.appendMediaToGallary(data);
			}
			else
			{
				jQuery('#uploaded_media').attr('src', data[eventMainImage]);
				jQuery('#uploaded_media').closest('.thumbnails').removeClass('hide_jtdiv');
				jQuery('#jform_event_old_image').val(jQuery('#jform_event_image').val());
				jQuery('#jform_event_image').val(data.id);
			}
		}

		return false;
	},

	validateYouTubeUrl : function (url)
	{
		if (url != undefined || url != '')
		{
			var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
			var match = url.match(regExp);

			if (match && match[2].length == 11)
			{
				return 'https://www.youtube.com/embed/' + match[2] + '?enablejsapi=1';
		        }
			else
			{
				return false;
			}
	    	}
	},

	progressBar :
	{
		init : function (divId, msg)
		{
			jQuery('#'+divId).siblings('.alert').remove();
			jQuery('#'+divId).siblings('.progress').remove();
			this.progress = jQuery("<div class='progress progress-striped active'><div class='bar'></div><button onclick='return tjexport.abort();' class='btn btn-danger btn-small pull-right'>Abort</button></div>");
			this.statusBar = this.progress.find('.bar');
			this.abort = jQuery("<div class='abort'><span>Abort</span></div>").appendTo(this.statusbar);
			jQuery('#'+divId).closest('.controls').append(this.progress);
		},

		updateStatus : function (divId, percentage)
		{
			this.statusBar.css("width", percentage+'%');
			this.statusBar.text(percentage+'%');
		},

		abort : function ()
		{
			if(!confirm(Joomla.JText._('LIB_TECHJOOMLA_CSV_EXPORT_CONFIRM_ABORT')))
			{
				return false;
			}

			this.ajaxObj.abort();
		},

		statusMsg : function (divId, alert, msg)
		{
			setTimeout(function()
			{
  				jQuery('#'+divId).siblings('.progress').remove();
			}, 2000);

			var closeBtn = "<a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>";
			var msgDiv = jQuery("<div class='alert alert-"+alert+"'><strong>"+ msg +"</strong>"+ closeBtn +"</div>");
			jQuery('#'+divId).closest('.controls').append(msgDiv);
		}
	},

	tjMediaGallery :
	{
		appendMediaToGallary : function (mediaData)
		{
			var $newMedia = jQuery('.media_gallary_parent li.clone_media:first-child').clone();
			var type = mediaData.type.split('.');

			if (type[0] === 'video')
			{
				if (type[1] === 'youtube')
				{
					mediaTag = "<iframe width='160' height='113' src="+mediaData.media+"> </iframe>";
				}
				else
				{
					mediaTag = "<video class='media_video_width' preload='metadata' controls ><source src="+mediaData.media+"></video>";
				}

			}else if (type[0] === 'image'){
				mediaTag = "<img src="+mediaData[eventGalleryImage]+" class='media_image_width'>";
			}
			$newMedia.removeClass('hide_jtdiv');
			$newMedia.find('.thumbnail').append(mediaTag);
			$newMedia.find(".media_field_value").val(mediaData.id);
			$newMedia.find(".media_field_value").attr('id', 'media_id_'+mediaData.id);
			$newMedia.appendTo('.media_gallary_parent');
		},

		deleteMedia : function (currentDiv, isAdmin)
		{
			var $currentDiv = jQuery(currentDiv);

			if (isAdmin == 1)
			{
				url = "index.php?option=com_jticketing&format=json&task=event.deleteMedia";
			}
			else
			{
				url = jticketing_baseurl + "index.php?option=com_jticketing&format=json&task=eventform.deleteMedia";
			}

			if(!confirm(Joomla.JText._('JGLOBAL_CONFIRM_DELETE')))
			{
				return false;
			}

			var mediaId = $currentDiv.next().val();

			jQuery.ajax({
				type: "POST",
				url: url,
				dataType:'JSON',
				data:
				{
					id:mediaId
				},
				success: function (data){
					$currentDiv.closest('.clone_media').remove();
				},
				error: function(xhr,status,error)
				{
					tjMediaFile.progressBar.statusMsg(thisFile.id, 'error', error);
				}
			});
		}
	}
};


var jtCounter = {
	jtCountDown : function (divId, startDate, endDate, currentDate, isReverse){
		var counterDate = startDate;
		if (isReverse) {
			counterDate = endDate;
		}
		jQuery('#'+divId).countdown(counterDate)
			.on('update.countdown', function(event)
			{
				var format = msg = '';
				if (event.offset.totalDays > 0) {
					msg = Joomla.JText._('JT_EVENT_COUNTER_STARTS_IN_DAYS');
					if (isReverse) {
						msg = Joomla.JText._('JT_EVENT_COUNTER_ENDS_IN_DAYS');
					}
				  	format = msg.replace("%s", "%-D");
				}
				else if (event.offset.totalDays == 0) {
				    msg = Joomla.JText._('JT_EVENT_COUNTER_STARTS_IN_TIME');
				  	if (isReverse) {
				  		msg = Joomla.JText._('JT_EVENT_COUNTER_ENDS_IN_TIME');
				  	}
				  	format = msg.replace("%s", "%H:%M:%S");
				}
				jQuery(this).html(event.strftime(format));
			})
			.on('finish.countdown', function(event)
			{
				if (endDate > currentDate)
				{
					jtCounter.jtCountDown(divId, startDate, endDate, currentDate, '1');
				}

				if (endDate < currentDate)
				{
					jQuery(this).html(Joomla.JText._('JT_EVENT_COUNTER_EXPIRE'));
				}
			});
	}
};

var validation = {

	positiveNumber : function()
	{
		jQuery(window).load(function(){
		document.formvalidator.setHandler('positive-number', function(value, element) {
			value = punycode.toASCII(value);
			var regex = /^[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$/;
			return regex.test(value);
			});
		});
	}

}

