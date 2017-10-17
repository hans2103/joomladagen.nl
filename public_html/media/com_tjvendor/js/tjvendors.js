/*
 * @version    SVN: <svn_id>
 * @package    Tjvendors
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

var tjVAdmin =
{
	vendor: {
		/*Initialize event js*/
			initVendorJs: function() {
				jQuery(document).ready(function() {
					tjVAdmin.vendor.generateGatewayFields();
					jQuery(document).on("change","#jform_user_id", function () {
						tjVAdmin.vendor.checkVendor();
					});

					jQuery(document).on("change","#jform_payment_gateway", function () {
						tjVAdmin.vendor.generateGatewayFields();
					});
				});

				Joomla.submitbutton = function (task)
				{
					if (task == 'vendor.apply' || task == 'vendor.save' || task == 'vendor.save2new')
					{
						var validData = document.formvalidator.isValid(document.getElementById('vendor-form'));
						var username = document.getElementById("jform_user_id").value;

						if (username == '')
						{
							var jmsgs = [Joomla.JText._('COM_TJVENDOR_USER_ERROR')];
							Joomla.renderMessages({'warning': jmsgs });
						}
						else if (validData == true)
						{
							Joomla.submitform(task, document.getElementById('vendor-form'));
						}
					}
					else if (task == 'vendor.cancel')
					{
						Joomla.submitform(task, document.getElementById('vendor-form'));
					}
					else
					{
						Joomla.submitform(task, document.getElementById('vendor-form'));
					}
				}
			},
			changePayoutStatus: function(payout_id, ele){
				var paidUnpaid1          = document.getElementById('paidUnpaid').value;
				var userObject           = {};
				userObject["payout_id"]  = payout_id;
				userObject["paidUnpaid"] = jQuery(ele).val();

				JSON.stringify(userObject) ;
				jQuery.ajax({
					type: "POST",
					dataType: "json",
					data: userObject,
					url: "index.php?option=com_tjvendors&task=payout.changePayoutStatus",
					success:function(data) {

						if (data)
						{
							document.location='index.php?option=com_tjvendors&view=reports&client='+client;
						}
					},
				});
			},
			checkVendor: function() {
				var user=document.getElementById('jform_user_id_id').value;
				var userObject = {};
				userObject["user"] = user;
				userObject["vendor_id"] = vendor_id;
				JSON.stringify(userObject) ;
				jQuery.ajax({
					type: "POST",
					dataType: "json",
					data: userObject,
					url: "index.php?option=com_tjvendors&task=vendor.checkDuplicateUser",
					success:function(data) {

						if (data)
						{
							if (layout === "update")
							{
								var jmsgs = [Joomla.JText._('COM_TJVENDOR_DUPLICARE_VENDOR_ERROR')];
								Joomla.renderMessages({'warning': jmsgs });

								return vendorCheck = "exists";
							}
							else
							{
								document.location='index.php?option=com_tjvendors&view=vendor&layout=edit&client='+client+'&vendor_id='+data.vendor_id;
							}
						}
					},
				});
			},
			generateGatewayFields: function(){
				var payment_gateway=document.getElementById('jform_payment_gateway').value;
				var userObject = {};
				userObject["payment_gateway"] = payment_gateway;
				JSON.stringify(userObject) ;
				jQuery.ajax({
					type: "POST",
					dataType: "json",
					data: userObject,
					url: "index.php?option=com_tjvendors&task=vendor.generateGatewayFields",
					success:function(data) {
						jQuery('#payment_details').empty();

						if (data)
						{
							jQuery('#payment_details').html(data);
						}
						else if (!data && payment_gateway != "" && layout != "update")
						{
							var error_html = Joomla.JText._('COM_TJVENDOR_PAYMENTGATEWAY_NO_FIELD_MESSAGE');
							jQuery("#payment_details").html("<div id='fieldmessage' class='alert alert-warning'>" + error_html + "</div>");
						}
					},
				});
			}
	},
	reports: {
		/*Initialize event js*/
		initReportsJs: function() {
			jQuery(document).ready(function() {
				jQuery("#dates").blur(function() {
						document.adminForm.submit();
				});
				jQuery("#date").blur(function() {
						document.adminForm.submit();
				});
			});
		}
	}
}
var tjVSite =
{
	vendor: {
		/*Initialize event js*/
			initVendorJs: function() {
				jQuery(document).ready(function() {
					tjVSite.vendor.generateGatewayFields();
					jQuery(document).on("change","#jform_payment_gateway", function () {
						tjVSite.vendor.generateGatewayFields();
					});
				});

				Joomla.submitbutton = function (task)
				{
					if (task == 'vendor.save')
					{
						var validData = document.formvalidator.isValid(document.getElementById('vendor-form'));
						if(validData == true)
						{
							Joomla.submitform(task, document.getElementById('vendor-form'));
						}
					}
					else if (task == 'vendor.cancel')
					{
						Joomla.submitform(task, document.getElementById('vendor-form'));
					}
					else
					{
						Joomla.submitform(task, document.getElementById('vendor-form'));
					}
				}
			},

			generateGatewayFields: function(){
				var payment_gateway=document.getElementById('jform_payment_gateway').value;
				var userObject = {};
				userObject["payment_gateway"] = payment_gateway;
				JSON.stringify(userObject) ;
				jQuery.ajax({
					type: "POST",
					dataType: "json",
					data: userObject,
					url: "?option=com_tjvendors&task=vendor.generateGatewayFields",
					success:function(data) {
						jQuery('#payment_details').empty();

						if (data)
						{
							jQuery('#payment_details').html(data);
						}
						else if (!data && payment_gateway != "" && layout != "profile")
						{
							var error_html = Joomla.JText._('COM_TJVENDOR_PAYMENTGATEWAY_NO_FIELD_MESSAGE');
							jQuery("#payment_details").html("<div class='alert alert-warning'>" + error_html + "</div>");
						}
					},
				});
			}
	},
	vendors: {
		/*Initialize event js*/
		initVendorsJs: function() {
			jQuery(document).ready(function() {
				jQuery("#dates").blur(function() {
						document.adminForm.submit();
				});
				jQuery("#date").blur(function() {
						document.adminForm.submit();
				});
			});
		}
	}
}

