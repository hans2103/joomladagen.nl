/**
* CSVI JavaScript
*
* @copyright Copyright (C) 2006 - @year@ RolandD Cyber Produksi. All rights reserved.
* @version $Id: csvi.js 2858 2015-03-23 15:48:59Z Roland $
 */

var Csvi = {
	// Retrieve the template types for the given component
	loadTasks: function(fname)
	{
		fname = fname || 'jform';
		var action = jQuery("#" + fname + "_action").val();
		var component = jQuery("#" + fname + "_component").val();
		var customTable = jQuery('#jform_custom_table_chzn');

		if (component !== 'com_csvi')
		{
			customTable.addClass('hidden');
		}
		else
		{
			customTable.removeClass('hidden');
		}

		jQuery.ajax({
			async: false,
			url: 'index.php',
			dataType: 'json',
			data: 'option=com_csvi&task=task.loadtasks&format=json&action='+action+'&component='+component,
			success: function(result)
			{
				jQuery('#' + fname + '_operation > option').remove();
				jQuery.each(result.data, function(value, name) {
					jQuery('#' + fname + '_operation').append(jQuery('<option></option>').val(value).html(name));
				});

				var operation = jQuery('#' + fname + '_operation');
				operation.trigger("liszt:updated");  // Old chosen version
				operation.trigger("chosen:updated"); // New chosen version
			},
			error: function(result, status, statusText) {
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+result.responseText);
			}
		});
	},

	// Retrieve the template override for the given component
	loadOverrides: function()
	{
		var action = jQuery("#jform_action").val();
		var component = jQuery("#jform_component").val();
		if (component !== 'com_csvi') jQuery('#jform_custom_table_chzn').addClass('hidden');
		else jQuery('#jform_custom_table_chzn').removeClass('hidden');
		jQuery.ajax({
			async: false,
			url: 'index.php',
			dataType: 'json',
			data: 'option=com_csvi&task=template.loadoverrides&format=json&action='+action+'&component='+component,
			success: function(data)
			{
				var override = jQuery('#jform_override');
				override.find('option').remove();
				jQuery.each(data.data, function(value, name) {
					override.append(jQuery('<option></option>').val(value).html(name));
				});

				override.trigger('liszt:updated');  // Old chosen version
				override.trigger('chosen:updated'); // New chosen version
			},
			error: function(data, status, statusText) {
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.messages);
			}
		});
	},

	getData: function(task) {
		var template_type = jQuery('#jformimport_type').val();
		var table_name = jQuery('#jformcustom_table_import').val();
		jQuery.ajax({
				async: false,
				url: 'index.php',
				dataType: 'json',
				data: 'option=com_csvi&task=exports.'+task+'&format=json&template_type='+template_type+'&table_name='+table_name,
				success: function(data) {
					switch (task) {
						case 'loadtables':
							loadTables(data);
							break;
						case 'loadfields':
							loadFields(data);
							break;
					}
				},
				error:function (xhr, ajaxOptions, thrownError){
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
	            }
		});
	},

	createFolder: function(folder, element) {
		var spinner = jQuery('#'+element).html("<img src='components/com_csvi/assets/images/csvi_ajax-loading.gif' />");
		jQuery.ajax({
			async: false,
			url: 'index.php',
			dataType: 'json',
			data: 'option=com_csvi&task=about.createfolder&format=json&folder='+folder,
			success: function(data)
			{
				switch (data.result)
				{
					case 'false':
						jQuery('#' + element).remove();
						Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), data.resultText);
						break;
					case 'true':
						location.reload();
						break;
				}
			},
			error: function(data, status, statusText)
			{
				jQuery('#'+element).html(Joomla.JText._('COM_CSVI_ERROR_CREATING_FOLDER'));
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		});
	},

	showExportSource: function()
	{
		// Hide all selected options
		jQuery('#localfield, #ftpfield, #emailfield, #databasefield').hide();

		// Load the selected options
		jQuery('select#jform_exportto :selected').each(function(index, selected)
		{

			switch (selected.value)
			{
				// Export options
				case 'todownload':
					// Everything is already hidden
					break;
				case 'tofile':
					jQuery('#localfield').show();
					break;
				case 'toftp':
					jQuery('#ftpfield').show();
					break;
				case 'toemail':
					jQuery('#emailfield').show();
					break;
				case 'todatabase':
					jQuery('#databasefield').show();
					break;
			}
		});
	},

	showImportSource: function(source)
	{
		var ftpfield = jQuery('#ftpfield');
		var urlfield = jQuery('#urlfield');
		var testurlbutton = jQuery('#testurlbutton');
		var databasefield = jQuery('#databasefield');
		var testpathbutton = jQuery('.testpathbutton');
		var importupload = jQuery('.importupload');

		switch (source)
		{
			// Import options
			case 'fromserver':
				ftpfield.hide();
				urlfield.hide();
				testurlbutton.hide();
				databasefield.hide();
				testpathbutton.show();
				jQuery('.importupload, .importurl, .databasefield, .textfield').parent().parent().hide();
				jQuery('.importserver').parent().parent().show();
				break;
			case 'fromurl':
				urlfield.show();
				ftpfield.hide();
				testpathbutton.hide();
				databasefield.hide();
				jQuery('.importupload, .importserver, .ftpfield, .textfield, .databasefield').parent().parent().hide();
				jQuery('.importurl').parent().parent().show();
				break;
			case 'fromftp':
				ftpfield.show();
				urlfield.hide();
				testurlbutton.hide();
				testpathbutton.hide();
				databasefield.hide();
				jQuery('.importupload, .importserver, .importurl, .textfield, .databasefield').parent().parent().hide();
				jQuery('.ftpfield').parent().parent().show();
				break;
			case 'fromupload':
				ftpfield.hide();
				urlfield.hide();
				testurlbutton.hide();
				testpathbutton.hide();
				databasefield.hide();
				jQuery('.importserver, .ftpfield, .importurl, .textfield, .databasefield').parent().parent().hide();
				importupload.parent().parent().show();
				break;
			case 'fromtextfield':
				ftpfield.hide();
				urlfield.hide();
				testurlbutton.hide();
				testpathbutton.hide();
				databasefield.hide();
				jQuery('.importserver, .ftpfield, .importurl, .databasefield').parent().parent().hide();
				break;
			case 'fromdatabase':
				ftpfield.hide();
				urlfield.hide();
				testurlbutton.hide();
				testpathbutton.hide();
				jQuery('.importserver, .ftpfield, .importurl, .textfield').parent().parent().hide();
				importupload.hide();
				databasefield.show();
				databasefield.parent().parent().show();
				break;
		}
	},

	showFields: function(show, target)
	{
		var items = '';
		show = parseInt(show);

		if (show === 1)
		{
			// Create array of options
			items = target.split(' ');

			for (i=0; i < items.length; i++)
			{
				// Check for a class
				if (items[i].charAt(0) === '.')
				{
					jQuery(items[i]).parent().parent().show();
				}
				else if (items[i].charAt(0) === '#')
				{
					jQuery(items[i]).show();
				}
			}
		}
		else
		{
			// Create array of options
			items = target.split(' ');

			for (var i=0; i < items.length; i++)
			{
				// Check for a class
				if (items[i].charAt(0) === '.')
				{
					jQuery(items[i]).parent().parent().hide();
				}
				else if (items[i].charAt(0) === '#')
				{
					jQuery(items[i]).hide();
				}
			}
		}

		return;
	},

	searchUser: function() {
		_timeout = null;
		jQuery("#selectuserid tbody").remove();
		jQuery("#selectuserid").append('<tbody><tr><td colspan="2"><div id="ajaxuserloading"><img src="../administrator/components/com_csvi/assets/images/csvi_ajax-loading.gif" /></div></td></tr></tbody>');
		var searchfilter = jQuery("input[name='searchuserbox']").val();
		var component = jQuery("#jform_component").val();
		jQuery.ajax({
			async: false,
			url: 'index.php',
			dataType: 'json',
			cache: false,
			data: 'option=com_csvi&task=exports.getdata&function=getorderuser&format=json&filter='+searchfilter+'&component='+component,
			success: function(data) {
				jQuery("#ajaxuserloading").remove();
				jQuery("#selectuserid tbody").remove();
				var options = [];
				var r = 0;
				options[++r] = '<tbody>';

				if (data.length > 0)
				{
					for (var i = 0; i < data.length; i++)
					{
						options[++r] = '<tr><td class="user_id">';
						options[++r] = data[i].user_id;
						options[++r] = '</td><td class="user_name">';
						options[++r] = data[i].user_name;
						options[++r] = '</td></tr>';
					}
				}

				options[++r] = '</tbody>';
				jQuery("#selectuserid").append(options.join(''));
				jQuery('table#selectuserid tbody tr').click(function()
				{
					var user_id = jQuery(this).find('td.user_id').html();

					// Check if the user ID is already in the select box
					var existingvals = [];
					jQuery('select#jform_orderuser option').each(function()
					{
					    var optionval = jQuery(this).val();
					    if (optionval !== "") existingvals.push(optionval);
					});

					if (jQuery.inArray(user_id, existingvals) >= 0) {
						return;
					}
					else
					{
						var options = '<option value="'+user_id+'" selected="selected">'+jQuery(this).find('td.user_name').html()+'</option>';
						jQuery("select#jform_orderuser").append(options);
						jQuery("select#jform_orderuser option:eq(0)").attr("selected", false);
					}

					jQuery("select#jform_orderuser").trigger("liszt:updated");  // Old chosen version
					jQuery("select#jform_orderuser").trigger("chosen:updated"); // New chosen version
				});
			},
			error: function(data, status, statusText) {
				jQuery("#ajaxproductloading").remove();
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		})
	},

	searchProduct: function() {
		_timeout = null;
		jQuery("#selectproductsku tbody").remove();
		jQuery("#selectproductsku").append('<tbody><tr><td colspan="2"><div id="ajaxproductloading"><img src="../administrator/components/com_csvi/assets/images/csvi_ajax-loading.gif" /></div></td></tr></tbody>');
		var searchfilter = jQuery("input[name='searchproductbox']").val();
		var component = jQuery("#jform_component").val();
		jQuery.ajax({
			async: false,
			url: 'index.php',
			dataType: 'json',
			cache: false,
			data: 'option=com_csvi&task=exports.getdata&function=getorderproduct&format=json&filter='+searchfilter+'&component='+component,
			success: function(data) {
				jQuery("#ajaxproductloading").remove();
				jQuery("#selectproductsku tbody").remove();
				var options = [];
				var r = 0;
				options[++r] = '<tbody>';
				if (data.length > 0)
				{
					for (var i = 0; i < data.length; i++)
					{
						options[++r] = '<tr><td class="product_id dialog-hide">';
						options[++r] = data[i].product_id;
						options[++r] = '</td><td class="product_sku">';
						options[++r] = data[i].product_sku;
						options[++r] = '</td><td class="product_name">';
						options[++r] = data[i].product_name;
						options[++r] = '</td></tr>';
					}
				}
				options[++r] = '</tbody>';
				jQuery("#selectproductsku").append(options.join(''));
				jQuery('table#selectproductsku tbody tr').click(function()
				{
					var product_sku = jQuery(this).find('td.product_sku').html();

					// Check if the product ID is already in the select box
					var existingvals = [];
					jQuery('select#jform_orderproduct option').each(function()
					{
					    var optionval = jQuery(this).val();
					    if (optionval !== "") existingvals.push(optionval);
					});

					if (jQuery.inArray(product_sku, existingvals) >= 0)
					{
						return;
					}
					else
					{
						var options = '<option value="'+product_sku+'" selected="selected">'+jQuery(this).find('td.product_name').html()+'</option>';
						jQuery("select#jform_orderproduct").append(options);
						jQuery("select#jform_orderproduct option:eq(0)").attr("selected", false);
					}

					jQuery("select#jform_orderproduct").trigger("liszt:updated");  // Old chosen version
					jQuery("select#jform_orderproduct").trigger("chosen:updated"); // New chosen version
				});
			},
			error: function(data, status, statusText) {
				jQuery("#ajaxproductloading").remove();
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		})
	},

	searchItemProduct: function() {
		_timeout = null;
		jQuery("#selectitemproductsku tbody").remove();
		jQuery("#selectitemproductsku").append('<tbody><tr><td colspan="2"><div id="ajaxproductloading"><img src="/administrator/components/com_csvi/assets/images/csvi_ajax-loading.gif" /></div></td></tr></tbody>');
		var searchfilter = jQuery("input[name='searchitemproductbox']").val();
		jQuery.ajax({
			async: false,
			url: 'index.php',
			datatype: 'json',
			data: 'option=com_csvi&task=process.getitemproduct&format=json&filter='+searchfilter,
			success: function(data) {
				jQuery("#ajaxproductloading").remove();
				jQuery("#selectitemproductsku tbody").remove();
				var options = [];
				var r = 0;
				options[++r] = '<tbody>';
				if (data.length > 0) {
					for (var i = 0; i < data.length; i++) {
						options[++r] = '<tr><td class="product_sku">';
						options[++r] = data[i].product_sku;
						options[++r] = '</td><td class="product_name">';
						options[++r] = data[i].product_name;
						options[++r] = '</td></tr>';
					}
				}
				options[++r] = '</tbody>';
				jQuery("#selectitemproductsku").append(options.join(''));
				jQuery("table#selectitemproductsku tr:even").addClass("row0");
				jQuery("table#selectitemproductsku tr:odd").addClass("row1");
				jQuery('table#selectitemproductsku tbody tr').click(function() {
					var product_sku = jQuery(this).find('td.product_sku').html();
					// Check if the product ID is already in the select box
					var existingvals = [];
					jQuery('select#jform_orderitem_orderitemproduct option').each(function() {
					    var optionval = jQuery(this).val();
					    if (optionval !== "") existingvals.push(optionval);
					});
					if (jQuery.inArray(product_sku, existingvals) >= 0) {
						return;
					}
					else {
						var options = '<option value="'+product_sku+'" selected="selected">'+jQuery(this).find('td.product_name').html()+'</option>';
						jQuery("select#jform_orderitem_orderitemproduct").append(options);
						jQuery("select#jform_orderitem_orderitemproduct option:eq(0)").attr("selected", false);
					}

					jQuery("select#jform_orderitem_orderitemproduct").trigger("liszt:updated");  // Old chosen version
					jQuery("select#jform_orderitem_orderitemproduct").trigger("chosen:updated"); // New chosen version
				});
			},
			error: function(data, status, statusText) {
				jQuery("#ajaxproductloading").remove();
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		})
	},

	loadExportSites: function(site, selected)
	{
		if (jQuery('#jform_export_site').attr('type') !== 'hidden')
		{
			jQuery('#layout_nav').closest('li').hide();

			// Show XML layout tab only if its XML export
			if (site == 'xml')
			{
				jQuery('#layout_nav').closest('li').show();
			}

			switch (site) {
				case 'xml':
				case 'html':
					jQuery.ajax({
						async: false,
						url: 'index.php',
						dataType: 'json',
						data: 'option=com_csvi&task=exports.loadsites&format=json&exportsite=' + site,
						success: function (data) {
							if (data) {
								jQuery('#jform_export_site > option').remove();
								jQuery.each(data, function (value, name) {
									jQuery('#jform_export_site').append(jQuery('<option></option>').val(value).html(name));
								});

								// Set the selected value
								jQuery('#jform_export_site').val(selected).change();

								jQuery("#jform_export_site").trigger("liszt:updated");  // Old chosen version
								jQuery("#jform_export_site").trigger("chosen:updated"); // New chosen version
							}
						},
						error: function (data, status, statusText) {
							Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText + '<br /><br />' + data.responseText);
						}
					});
					jQuery('#jform_export_site').parent().parent().show();
					jQuery('#jform_field_delimiter, #jform_text_enclosure').parent().parent().hide();
					break;
				default:
					jQuery('#jform_export_site').parent().parent().hide();
					jQuery('#jform_field_delimiter, #jform_text_enclosure').parent().parent().show();
					break;
			}
		}
	},

	loadCategoryTree: function (lang, component) {
		jQuery.ajax({
			async: false,
			url: 'index.php',
			dataType: 'json',
			data: 'option=com_csvi&task=exports.getdata&function=loadcategorytree&format=json&filter='+lang+'&component='+component,
			success: function(data)
			{
				if (data)
				{
					var productCategories = jQuery('#jform_product_categories');
					productCategories.find('option').remove();
					jQuery.each(data, function(key, item) {
						productCategories.append(jQuery('<option></option>').val(item.value).html(item.text));
					});
					productCategories.find('option:first').attr("selected", "true");

					productCategories.trigger("liszt:updated");  // Old chosen version
					productCategories.trigger("chosen:updated"); // New chosen version
				}
			},
			error: function(data, status, statusText)
			{
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		});
	},

	loadManufacturers: function (lang, component) {
		jQuery.ajax({
			async: false,
			url: 'index.php',
			dataType: 'json',
			data: 'option=com_csvi&task=exports.getdata&function=loadmanufacturers&format=json&filter='+lang+'&component='+component,
			success: function(data)
			{
				if (data)
				{
					var manufacturers = jQuery('#jform_manufacturers');
					manufacturers.find('option').remove();
					jQuery.each(data, function(key, item)
					{
						manufacturers.append(jQuery('<option></option>').val(item.value).html(item.text));
					});
					manufacturers.find('option:first').attr('selected', 'true');

					manufacturers.trigger('liszt:updated');  // Old chosen version
					manufacturers.trigger('chosen:updated'); // New chosen version
				}
			},
			error: function(data, status, statusText)
			{
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		});
	},

	testFtp: function(action) {
		var ftphost = jQuery('#jform_ftphost').val(),
		ftpport = jQuery('#jform_ftpport').val(),
		ftpusername = jQuery('#jform_ftpusername').val(),
		ftppass = jQuery('#jform_ftppass').val(),
		ftproot = jQuery('#jform_ftproot').val(),
		ftpfile = jQuery('#jform_ftpfile').val(),
		sftp	= jQuery('#jform_sftp1').is(':checked') ? 1 : 0;

		jQuery
			.ajax({
				async : false,
				url : 'index.php',
				type : 'post',
				dataType : 'json',
				data : 'option=com_csvi&task=template.testftp&format=json&ftphost='+ftphost+'&ftpport='+ftpport+'&ftpusername='+ftpusername+'&ftppass='+ftppass+'&ftproot='+ftproot+'&ftpfile='+ftpfile+'&action='+action+'&sftp='+sftp,
				success : function(response) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_INFORMATION'), response.data);
				},
				error : function(data, status, statusText) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText)
				}
			});
	},

	testConnection: function(action) {
		var dbusername = jQuery('#jform_database_username').val();
		var dbpassword = jQuery('#jform_database_password').val();
		var dburl = jQuery('#jform_database_host').val();
		var dbportno = jQuery('#jform_database_portno').val();
		var dbname = jQuery('#jform_database_name').val();
		var dbtable = jQuery('#jform_database_table').val();
		jQuery
			.ajax({
				async : false,
				url : 'index.php',
				type : 'post',
				dataType : 'json',
				data : 'option=com_csvi&task=template.testdbconnection&format=json&dbusername='+dbusername+'&dbpassword='+dbpassword+'&dburl='+dburl+'&dbportno='+dbportno+'&dbname='+dbname+'&dbtable='+dbtable+'&'+'&action='+action,
				success : function(response) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_INFORMATION'), response.data);
				},
				error : function(data, status, statusText) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText)
				}
			});
	},

	testURL: function(action) {
		var testurl = jQuery('#jform_urlfile').val();
		var testuser = jQuery('#jform_urlusername').val();
		var testuserfield = jQuery('#jform_urlusernamefield').val();
		var testpass = jQuery('#jform_urlpass').val();
		var testpassfield = jQuery('#jform_urlpassfield').val();
		var testmethod = jQuery('#jform_urlmethod').val();
		var testcredentialtype = jQuery('#jform_urlcredential').val();

		jQuery
			.ajax({
				async : false,
				url : 'index.php',
				type : 'post',
				dataType : 'json',
				data : 'option=com_csvi&task=template.testurl&format=json&testurl='+testurl+'&testuser='+testuser+'&testuserfield='+testuserfield+'&testpass='+testpass+'&testpassfield='+testpassfield+'&testmethod='+testmethod+'&testcredentialtype='+testcredentialtype,
				success : function(response) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_INFORMATION'), response.data);
				},
				error : function(data, status, statusText) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText)
				}
			});
	},

	testPath: function(action) {
		var testpath = jQuery('#jform_local_csv_file').val();
		jQuery
			.ajax({
				async : false,
				url : 'index.php',
				type : 'post',
				dataType : 'json',
				data : 'option=com_csvi&task=template.testpath&format=json&testpath='+testpath,
				success : function(response) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_INFORMATION'), response.data);
				},
				error : function(data, status, statusText) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText)
				}
			});
	},

	loadPluginForm: function(plugin) {
		jQuery
		.ajax({
			async : false,
			url : 'index.php',
			dataType : 'html',
			data : 'option=com_csvi&task=rule.loadpluginform&tmpl=component&plugin='+plugin,
			success : function(data) {
				jQuery('#pluginfields').html('<div id="'+plugin+'">'+data+'</div>');
				jQuery('.help-block').hide();
				jQuery("select").chosen();
			},
			error : function(data, status, statusText) {
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		});
	},

	showModalDialog: function(title, message) {
		var modal = '<div class="modal hide fade" id="errorModal">'
					+ '<div class="modal-header">'
					+ ' <button type="button" class="close" data-dismiss="modal">&#215;</button>'
					+ ' <h3>'
					+ title
					+ '</h3>'
					+ '</div>'
					+ ' <div class="modal-body modal-batch">'
					+ message
					+ '</div>'
					+ '<div class="modal-footer">'
					+ ' <button data-dismiss="modal" type="button" class="btn cancel-btn">'
					+ Joomla.JText._('COM_CSVI_CLOSE_DIALOG')
					+ ' </button>'
					+ '</div>'
					+ '</div>';
		jQuery(modal).modal('show');
	},

	loadCustomAvailableFields: function () {
		var tablename = jQuery('#jform_table_name').val(),
			fieldName = jQuery('#jform_field_name'),
			selectedField = fieldName.val();

		// Get the arguments for Custom Table export
		if (arguments.length > 0)
		{
			var tablenamerow = arguments[0].id;
			var fieldnamesplit = arguments[0].id.split('-');
			var counter = fieldnamesplit[0];
			tablename = jQuery('#' + tablenamerow).val();
			fieldName = jQuery('#' + counter + '-jform_' + arguments[1] + '_field_name');
		}

		jQuery.ajax({
			async: false,
			url: 'index.php',
			dataType: 'json',
			data: 'option=com_csvi&task=templatefield.customtablecolumns&tablename='+tablename+'&format=json',
			success: function(data)
			{
				// Empty the list
				fieldName.empty();

				// Add the new options
				jQuery.each(data.data.columns, function(val, text)
				{
					fieldName.append(jQuery('<option></option>').attr('value', val).text(text));
				});

				// Set the original field name as selected
				fieldName.val(selectedField);

				// Update the chosen list
				fieldName.trigger('liszt:updated');
				fieldName.trigger('chosen:updated'); // New chosen version
			},
			error: function(data, status, statusText) {
				fieldName.empty();
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		});
	},
	loadCustomTableColumns: function (element) {
		// Get the field list
		var field = jQuery('#' + element.id).closest('td').next().find('.customfield');

		jQuery.ajax({
			async: true,
			url: 'index.php',
			dataType: 'json',
			data: 'option=com_csvi&task=template.gettablecolumns&tablename=' + element.value + '&format=json',
			success: function(data)
			{
				// Empty the list
				field.empty();

				// Add the new options
				jQuery.each(data.data.columns, function(val, text)
				{
					field.append(jQuery('<option></option>').attr('value', val).text(text));
				});

				// Update the chosen list
				field.trigger('liszt:updated');
				field.trigger('chosen:updated'); // New chosen version
			},
			error: function(data, status, statusText) {
				field.empty();
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		});
	}
};

var CsviMaint = {
	loadOptions: function(operation) {
		var component = jQuery('#component').val();
		var operation = jQuery('#operation').val();
		var optionField = jQuery('#optionfield');

		jQuery.ajax({
			async: false,
			url: 'index.php',
			dataType: 'json',
			data: 'option=com_csvi&task=maintenance.read&subtask=options&component='+component+'&operation='+operation+'&format=json',
			success: function(data) {
					optionField.empty().append(data.data.options);

					// Update the chosen list
					jQuery("#optionfield select").chosen({"disable_search_threshold":10});
					optionField.trigger("liszt:updated");
					optionField.trigger("chosen:updated"); // New chosen version
			},
			error: function(data, status, statusText) {
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		});
	},

	loadOperation: function() {
		jQuery('#optionfield').empty();
		var component = jQuery('#component').val();
		var operation = jQuery('#operation');

		jQuery.ajax({
			async: false,
			url: 'index.php',
			dataType: 'json',
			data: 'option=com_csvi&task=maintenance.read&subtask=operations&component='+component+'&format=json',
			success: function(data)
			{
				// Empty the list
				operation.empty();

				// Add the new options
				jQuery.each(data.data.options, function(val, text)
				{
					operation.append(jQuery('<option></option>').attr('value', val).text(text));
				});

				// Add any specific confirmation message
				if (data.confirm)
				{
					jQuery('#confirm').val(data.confirm);
				}

				// Update the chosen list
				operation.trigger("liszt:updated");
				operation.trigger("chosen:updated"); // New chosen version
			},
			error: function(data, status, statusText) {
				operation.empty();
				Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
			}
		});
	}
};

var CsviTemplates = {

	getData: function(task) {
		var component = jQuery('#jform_options_component').val();
		var template_type = jQuery('#jform_options_operation').val();
		var table_name = jQuery('#jform_custom_table').val();

		jQuery.ajax({
				async: false,
				url: 'index.php',
				dataType: 'json',
				data: 'option=com_csvi&task=process.'+task+'&format=json&template_type='+template_type+'&table_name='+table_name+'&component='+component,
				success: function(data) {
					switch (task) {
						case 'loadtables':
							if (data) {
								var optionsValues = '<select id="jformcustom_table" name="jform[custom_table]">';
								for (var i = 0; i < data.length; i++) {
										optionsValues += '<option value="' + data[i] + '">' + data[i] + '</option>';
								};
								optionsValues += '</select>';
								jQuery('#jformcustom_table').replaceWith(optionsValues);
							}
							break;
						case 'loadfields':
							if (data) {
								if (data.length > 0) {
									var optionsValues = '';
									var trValues = '';
									for (var i = 0; i < data.length; i++) {
											optionsValues += '<option value="' + data[i] + '">' + data[i] + '</option>';
											trValues += '<tr><td><input type="checkbox" name="quickfields" value="' + data[i] + '" /></td><td class="addfield">' + data[i] + '</td></tr>';
									};
									jQuery('#_field_name').replaceWith('<select id="_field_name" name="field[_field_name]">'+optionsValues+'</select>');
								}
							};
							break;
					}
				},
				error : function(data, status, statusText) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
				}
		});
	},

	deleteFields: function() {
		var template_id = jQuery('#select_template').val();
		var cids = [];
		jQuery("[name='cid[]']").each(function() {
			if (jQuery(this).is(':checked')) {
				cids.push(this.value);
			}
		});
		jQuery
			.ajax({
				async : false,
				url : 'index.php',
				type : 'post',
				dataType : 'json',
				data : 'option=com_csvi&task=templatefield.deletetemplatefield&format=json&cids='
						+ cids.join(','),
				success : function(data) {
					window.location = "index.php?option=com_csvi&view=process&template_id="+template_id;
				},
				error : function(data, status, statusText) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
				}
			});
	},

	getHref: function(ahref) {
		var checked = false;
		jQuery("input[name='cid[]']").each(function(index, option) {
		    if (jQuery(option).prop('checked')) {
		   		ahref += jQuery(option).parent().parent().find('a').attr('href');
		   		checked = true;
		    }
		});
		if (checked) {
			var options = {size: {x: 500, y: 450}};
			SqueezeBox.initialize(options);
			SqueezeBox.setContent('iframe',ahref);
		}
		else {
			Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), Joomla.JText._('COM_CSVI_CHOOSE_TEMPLATE_FIELD'));
		}
	},

	saveOrder: function() {
		var template_id = jQuery('#select_template').val();
		var values = [];
		var names = [];
		jQuery("input[name*='ordering']").each(function() {
			values.push(jQuery(this).val());
			names.push(jQuery(this).attr('name'));
		});
		jQuery
			.ajax({
				async : false,
				url : 'index.php',
				type : 'post',
				dataType : 'json',
				data : 'option=com_csvi&task=templatefield.saveorder&format=json&values='+values.join(',')+'&names='+names.join(','),
				success : function(data) {
					window.location = "index.php?option=com_csvi&view=process&template_id="+template_id;
				},
				error : function(data, status, statusText) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
				}
			});
	},

	renumberFields: function() {
		var template_id = jQuery('#select_template').val();
		jQuery
			.ajax({
				async : false,
				url : 'index.php',
				type : 'post',
				dataType : 'json',
				data : 'option=com_csvi&task=templatefield.renumberFields&format=json&template_id='+template_id,
				success : function(data) {
					window.location = "index.php?option=com_csvi&view=process&template_id="+template_id;
				},
				error : function(data, status, statusText) {
					Csvi.showModalDialog(Joomla.JText._('COM_CSVI_ERROR'), statusText+'<br /><br />'+data.responseText);
				}
			});
	}
};

// Set the live events
jQuery(document).ready
	(function() {
		var _timeout = null;
		jQuery("#searchuser, #searchproduct, #searchitemproduct").on('keyup', function(e)
		{
			if(_timeout != null) {
				clearTimeout(_timeout); _timeout = null;
			}
			var callfunc = jQuery(this)[0].id;
			switch (callfunc) {
				case 'searchuser':
					_timeout = setTimeout('Csvi.searchUser()', 1000);
					break;
				case 'searchproduct':
					_timeout = setTimeout('Csvi.searchProduct()', 1000);
					break;
				case 'searchitemproduct':
					_timeout = setTimeout('Csvi.searchItemProduct()', 1000);
					break;
			}
		});

		// Avoid enter key to load showmodaldialog function
		jQuery("#editTemplate input").bind("keypress", function (e) {
			if (e.keyCode == 13) {
				return false;
			}
		});

		// If importing is from external database, show template fields mapping accordingly
		if (jQuery('#jform_fromdatabase').val() == 0)
		{
			jQuery('.control-group label[for="jform_source_field"]').parent().remove();
		}
		else
		{
			jQuery('.control-group label[for="jform_xml_node"]').parent().remove();
		}
	});
