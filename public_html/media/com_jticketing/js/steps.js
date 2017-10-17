	techjoomla.jQuery(document).ready(function(){
		/* Skip country and state dropdowns from chosen js and css*/
		jQuery('#country').attr('data-chosen', 'com_jticketing');
		jQuery('#state').attr('data-chosen', 'com_jticketing');

		if (eventPrice <= 0 && userID != 0)
		{
			jQuery("#id_step_payment_info").hide();
			jQuery("#id_step_billing_info").hide();
			jQuery("#step_billing_info").hide();
		}

		techjoomla.jQuery("select").trigger("liszt:updated");  /* IMP : to update to chz-done selects*/

		techjoomla.jQuery('#MyWizard').on('change', function(e, data) {
			values = techjoomla.jQuery('#ticketform').serialize();
			var refThis = techjoomla.jQuery("#jticketing-steps li[class='active']");
			var stepId = refThis[0].id;
			var collectAttendeeInformation = techjoomla.jQuery('#collect_attendee_information').val();

			if (stepId === "id_step_payment_info")
			{
				techjoomla.jQuery('#btnWizardNext').hide();
			}
			else
			{
				techjoomla.jQuery('#btnWizardNext').show();
			}

			/*for Step No.1*/
			if (stepId === "id_step_select_ticket" && data.direction === 'next')
			{
				var totalCalcAmt = totalCalcAmt1 = 0;
				techjoomla.jQuery('input[class*=\"type_ticketcounts\"]').each(function(){
					totalCalcAmt1 = parseFloat(totalCalcAmt1) + parseFloat(techjoomla.jQuery(this).val())
					});

					if (parseInt(totalCalcAmt1) <= 0 && eventType == 0 || (isNaN(totalCalcAmt1)) && eventType == 0 || (totalCalcAmt1 == '') && eventType == 0)
					{
						alert(Joomla.JText._('COM_JTICKETING_ENTER_NO_OF_TICKETS'));
						return e.preventDefault();
					}
					else
					{
						loadingImage();
						techjoomla.jQuery.ajax({
								url: root_url + '?option=com_jticketing&format=json&task=order.save&step=selectTicket&tmpl=component',
									type: 'POST',
									data:values,
									dataType:'json',
									async:'false',
									beforeSend: function() {
									},
									complete: function() {
									},
									success: function(data)
									{
										/* Now Set Inner Html of Step No2 to Fill Attendee Fields */
										if (data.attendee_html)
										{
											techjoomla.jQuery('#step_select_attendee').html(data.attendee_html)
										}

										if (data.redirect_invoice_view && !data.attendee_html)
										{
											document.location = data.redirect_invoice_view;
										}

										var eventType = techjoomla.jQuery('#event_type').val();

										if (eventType == 0)
										{
											var btnName = Joomla.JText._('COM_JTICKETING_SAVE_AND_CLOSE');
											techjoomla.jQuery('#btnWizardNext').text(btnName);
										}
									},
						   });
					}
			}

			/*for Step No.2*/
			if(stepId === "id_step_select_attendee" && (data.direction === 'next') ) {

					var attendeeForm = document.attendee_field_form;
					if (!document.formvalidator.isValid(attendeeForm))
					{
						if(data.direction === 'next')
						{
							alert(Joomla.JText._('COM_JTICKETING_FILL_ALL_REQUIRED_FIELDS'));
							return e.preventDefault();
						}
					}
					else
					{
						techjoomla.jQuery(".alert-error").hide();
						loadingImage();

						values = techjoomla.jQuery('#attendee_field_form').serialize();
						techjoomla.jQuery.ajax({

								url: root_url + '?option=com_jticketing&format=json&task=order.save&step=selectAttendee&tmpl=component',
									type:'POST',
									data:values,
									dataType:'json',
									beforeSend: function() {
									},
									complete: function() {
									},
									success: function(data)
									{
										/* Now Set Inner Html of Step No2 to Fill Attendee Fields*/
										if (data.attendee_html)
											techjoomla.jQuery('#step_select_attendee').html(data.attendee_html)

										if (data.redirect_invoice_view && !data.attendee_html)
										{
											document.location = data.redirect_invoice_view;
										}
									},
						   });
					}
			}

			/*for Step No.3*/
			if(stepId === "id_step_billing_info" && data.direction === 'next')
			{
				var billingForm = document.billing_info_form;

				if (!document.formvalidator.isValid(billingForm))
				{
					alert(Joomla.JText._('COM_JTICKETING_FILL_ALL_REQUIRED_FIELDS'));
					techjoomla.jQuery('#btnWizardNext').show();

					return e.preventDefault();
				}
				else if (parseInt(terms_enabled) === 1)
				{
					if (document.getElementById('accpt_terms').checked === true)
					{
						techjoomla.jQuery('#btnWizardNext').show();
					}
					else
					{
						techjoomla.jQuery('#btnWizardNext').show();
						alert(Joomla.JText._('COM_JTICKETING_ACCEPT_TERMS_AND_CONDITIONS'));
						return e.preventDefault();
					}
				}

				techjoomla.jQuery(".alert-error").hide();
				values = techjoomla.jQuery('#billing_info_form').serialize();

				techjoomla.jQuery.ajax({
					url: root_url + '?option=com_jticketing&format=json&task=order.save&step=billinginfo&tmpl=component',
						type:'POST',
						data:values,
						dataType:'json',
						async:false,
						beforeSend: function() {
								loadingImage();
						},
						complete: function() {
						hideImage();
						},
						success: function(data)
						{
							/* Now Set Inner Html of Step No2 to Fill Attendee Fields*/
							if (data.payment_html)
							{
								techjoomla.jQuery('#step_payment_info').html(data.payment_html);
							}

							if (data.redirect_invoice_view)
							{
								document.location = data.redirect_invoice_view;
							}
					   },
				   });
			}

			setTimeout(function(){ hideImage() },10);
			techjoomla.jQuery('html,body').animate({scrollTop: techjoomla.jQuery("#jticketing-steps").offset()},'slow');
		});

		techjoomla.jQuery('#MyWizard').on('changed', function(e, data) {

			var thisActive = techjoomla.jQuery("#jticketing-steps li[class='active']");
			stepThisActive = thisActive[0].id;


			if (stepThisActive == techjoomla.jQuery("#jticketing-steps li").first().attr('id'))
			{
				techjoomla.jQuery(".jticketing-form #btnWizardPrev").hide();
			}
			else
			{
				techjoomla.jQuery(".jticketing-form #btnWizardPrev").show();
			}

			if (stepThisActive == techjoomla.jQuery("#jticketing-steps li").last().attr('id')){
				techjoomla.jQuery(".jticketing-form .prev_next_wizard_actions").hide();
				var prev_button_html = '<button id="btnWizardPrev" onclick="techjoomla.jQuery(\'#MyWizard\').wizard(\'previous\');"	type="button" class="btn btn-prev pull-left" > <i class="icon-arrow-left" ></i>Prev</button>';

				if (stepThisActive == "id_step_payment_info" ){
					techjoomla.jQuery('#jticketing-payHtmlDiv div.form-actions').prepend( prev_button_html );
					techjoomla.jQuery('#jticketing-payHtmlDiv div.form-actions input[type="submit"]').addClass('pull-right');
				}
			}
			else
			{
				techjoomla.jQuery(".jticketing-form .prev_next_wizard_actions").show();
				techjoomla.jQuery('#btnWizardNext').show();
			}

			/* If billing info step */
			if (stepThisActive == 'id_step_billing_info')
			{
				if (techjoomla.jQuery('#user-info-tab').is(':visible'))
				{
					techjoomla.jQuery('#btnWizardNext').hide();
				}
				else
				{
					techjoomla.jQuery('#btnWizardNext').show();
				}
			}
		});

		techjoomla.jQuery('#MyWizard').on('finished', function(e, data) {
			var refThis = techjoomla.jQuery("#jticketing-steps li[class='active']");
			var stepId = refThis[0].id;
			var collectAttendeeInformation = techjoomla.jQuery('#collect_attendee_information').val();
		});

		techjoomla.jQuery('#btnWizardPrev').on('click', function() {
			var refThis = techjoomla.jQuery("#jticketing-steps li[class='active']");
			var stepId = refThis[0].id;
			var collectAttendeeInformation = techjoomla.jQuery('#collect_attendee_information').val();
			techjoomla.jQuery('#btnWizardNext').show();
			techjoomla.jQuery('#MyWizard').wizard('previous');
		});

		techjoomla.jQuery('#btnWizardNext').on('click', function() {
			var refThis = techjoomla.jQuery("#jticketing-steps li[class='active']");
			var stepId = refThis[0].id;
			console.log(stepId);
			var captchEnabled = techjoomla.jQuery('#captch_enabled').val()

			if (parseInt(captchEnabled) == 1 && stepId.toString() == 'id_step_select_ticket')
			{
				var captchaResponse = techjoomla.jQuery("textarea#g-recaptcha-response").val();

				if (captchaResponse)
				{
					techjoomla.jQuery('#MyWizard').wizard('next');
				}
				else
				{
					alert(Joomla.JText._('COM_JTICKETING_VALIDATE_CAPTCHA'));
				}
			}
			else
			{
				techjoomla.jQuery('#MyWizard').wizard('next');
			}
		});

		techjoomla.jQuery('#btnWizardStep').on('click', function() {
			var item = techjoomla.jQuery('#MyWizard').wizard('selectedItem');
		});

		techjoomla.jQuery('#MyWizard').on('stepclick', function(e, data) {
			var refThis = techjoomla.jQuery("#jticketing-steps li[class='active']");
			var stepId = refThis[0].id;
			var collectAttendeeInformation = techjoomla.jQuery('#collect_attendee_information').val();

			if (stepId === "id_step_payment_info")
			{
				techjoomla.jQuery('#btnWizardNext').show();
			}

			if(stepId === "id_step_select_attendee" && collectAttendeeInformation == 1)
			{
					var attendeeForm = document.attendee_field_form;

					if (!document.formvalidator.isValid(attendeeForm))
					{
						alert(Joomla.JText._('COM_JTICKETING_FILL_ALL_REQUIRED_FIELDS'));

						return e.preventDefault();
					}
					else
					{
						techjoomla.jQuery(".alert-error").hide();
						values=techjoomla.jQuery('#attendee_field_form').serialize();
						techjoomla.jQuery.ajax({
								url: root_url+'?option=com_jticketing&format=json&task=order.save&step=selectAttendee&tmpl=component',
									type:'POST',
									data:values,
									dataType:'json',
									beforeSend: function() {
									},
									complete: function() {
									},
									success: function(data)
									{
										/* Now Set Inner Html of Step No2 to Fill Attendee Fields */
										if(data.attendee_html)
										techjoomla.jQuery('#step_select_attendee').html(data.attendee_html)
									},
							});
					}
			}
		});

		/* optionally navigate back to 2nd step */
		techjoomla.jQuery('#btnStep2').on('click', function(e, data) {
		  techjoomla.jQuery('[data-target=#step2]').trigger("click");
		});
	});

	function loadingImage()
	{
		techjoomla.jQuery('<div id="appsloading"></div>')
		.css("background", "rgba(255, 255, 255, .8) url('" + root_url + "components/com_jticketing/assets/images/loading_data.gif') 50% 15% no-repeat")
		.css("top", techjoomla.jQuery('#TabConetent').position().top - techjoomla.jQuery(window).scrollTop())
		.css("left", techjoomla.jQuery('#TabConetent').position().left - techjoomla.jQuery(window).scrollLeft())
		.css("width", techjoomla.jQuery('#TabConetent').width())
		.css("height", techjoomla.jQuery('#TabConetent').height())
		.css("position", "fixed")
		.css("z-index", "1000")
		.css("opacity", "0.80")
		.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
		.css("filter", "alpha(opacity = 80)")
		.appendTo('#TabConetent');
	}

	function hideImage()
	{
		techjoomla.jQuery('#appsloading').remove();
	}

	function goToByScroll(id)
	{
		 techjoomla.jQuery('html,body').animate({
				 scrollTop: techjoomla.jQuery("#"+id).offset().top},
				 'slow');
	}

	function Jticketing_chkbillmail(logoutMessage,userId)
	{
		email = techjoomla.jQuery('#email1').val();
		techjoomla.jQuery('#btnWizardNext').removeAttr('disabled');

		/* If logged in user */
		if (userId > 0)
		{
			return true;
		}

		var	isguest = techjoomla.jQuery('input[name="account_jt"]:checked').val();

		techjoomla.jQuery.ajax({
			url: root_url + '?option=com_jticketing&format=json&task=order.checkUserEmailId&email=' + email + '&tmpl=component&view=order',
			type:'GET',
			dataType:'json',
			success: function(data)
			{
				if (data[0] == 1)
				{
					alert(logoutMessage);
					techjoomla.jQuery('#user-info-tab').show();
					techjoomla.jQuery('#user_info').show();
					techjoomla.jQuery('#btnWizardNext').hide();
				}
				else
				{
					techjoomla.jQuery('#billing_info_data').show();
					techjoomla.jQuery('#user-info-tab').hide();
					techjoomla.jQuery('#btnWizardNext').removeAttr('disabled');
					techjoomla.jQuery('#btnWizardNext').show();
				}
			}
		});
	}

	function toggleDisplay(Id, logoutMessage, loggedInUserid)
	{
		Jticketing_chkbillmail(logoutMessage, logoutMessage, loggedInUserid);
		techjoomla.jQuery('#' + Id).show();
	}
