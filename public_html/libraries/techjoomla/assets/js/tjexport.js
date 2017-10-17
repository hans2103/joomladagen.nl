/* Call this function when click on CSV Export button and call ajax for every limit set on view
 * limitStart is start position of record
 * fileName is CSV file name
 * divId is Id to append progress bar
 */
var tjexport = {
	ajaxObj : null,
	divId : null,
	exportCsv: function(limitStart, fileName, divId){
		var that = this;

		if (limitStart == 0)
		{
			if (divId == '' || typeof(divId) == 'undefined')
			{
				divId = 'adminForm';
			}

			this.divId = divId;

			jQuery(".export").attr("disabled", true);
			tjexport.showProgressBar();
			tjexport.displayNotice('info',csv_export_inprogress);
		}

		data = JSON.stringify({"limit_start":limitStart, "file_name":fileName});
		tjexport.updateLoader(Math.round((limitStart * 100)/ 100000));

		this.ajaxObj = jQuery.ajax({
		url: csv_export_url,
		type: 'POST',
		data: data,
		dataType : 'JSON',
		success: function(response){
				if (response['limit_start'] == response['total'])
				{
					jQuery(".export").removeAttr("disabled");
					console.log(response['limit_start']);
					console.log(response['total']);
					location.href = response['download_file'];
					tjexport.displayNotice('success',csv_export_success);
					tjexport.hideProgressBar();
					console.log("Download Successfully.");
				}
				else
				{
					tjexport.exportCsv(response['limit_start'], response['file_name']);
				}

				tjexport.updateLoader(Math.round((response['limit_start'] * 100)/ response['total']));
			},
		error: function(xhr, status, error) {
				jQuery(".export").removeAttr("disabled");
				tjexport.displayNotice('error', csv_export_error);
				console.log("Something went wrong.");
			}
		});
	},
	displayNotice:function( alert, message){
		jQuery('#'+this.divId).children('.alert').remove();
		jQuery('#'+this.divId).prepend("<div class='center alert alert-"+alert+"'>"+message+"</div>");
	},
	showProgressBar:function(){
		jQuery('#'+this.divId).prepend("<div class='progress progress-striped active'><div class='bar'></div><button onclick='return tjexport.abort();' class='btn btn-danger btn-small pull-right'>"
			+ Joomla.JText._('LIB_TECHJOOMLA_CSV_EXPORT_ABORT') + "</button></div>");
	},
	updateLoader:function(percent){
		jQuery(".progress .bar").css("width", percent+'%');
		jQuery(".progress .bar").text(percent+'%');
	},
	hideProgressBar:function(){
		jQuery('#'+ this.divId).children('.progress').remove();
	},
	abort : function(){
		if(!confirm(Joomla.JText._('LIB_TECHJOOMLA_CSV_EXPORT_CONFIRM_ABORT'))){
			return false;
		}

		this.ajaxObj.abort();
		tjexport.displayNotice('error', Joomla.JText._('LIB_TECHJOOMLA_CSV_EXPORT_UESR_ABORTED'));
		tjexport.hideProgressBar();
		return false;
	}
}
