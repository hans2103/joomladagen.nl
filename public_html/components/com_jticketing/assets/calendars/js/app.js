(function getCal() {
//console.log(test);
var d = new Date();
var month = d.getMonth()+1;
var day = d.getDate();
var yyyymmdd = d.getFullYear() + '-' + (month<10 ? '0' : '') + month + '-' + (day<10 ? '0' : '') + day;
jQuery.ajax(
{

	url:root_url+'index.php?option=com_jticketing&task=calendar.getEventList',
	type:'GET',
	//contentType: "application/json",
	dataType: "json",
	success:function(data)
	{
		var list = data;
		(function($) {
		"use strict";
			var options = {

						events_source : data,
						view: 'month',
						tmpl_path:  jQuery('#template_path_calendar').val(),
						tmpl_cache: false,
						day: yyyymmdd,
						onAfterEventsLoad: function(events) {
							if(!events) {
								return;
							}
							var list = jQuery('#eventlist');
							list.html('');

							jQuery.each(events, function(key, val) {
								jQuery(document.createElement('li'))
									.html('<a href="' + val.url + '">' + val.title + '</a>')
									.appendTo(list);
							});
						},
						onAfterViewLoad: function(view) {

							jQuery('#month_text').html(this.getTitle());
							jQuery('.btn-group button').removeClass('active');
							jQuery('button[data-calendar-view="' + view + '"]').addClass('active');
						},
						classes: {
							months: {
								general: 'label'
							}
						}
					};

						var calendar = $('#calendar').calendar(options);

					$('.btn-group button[data-calendar-nav]').each(function() {
						var $this = $(this);
						$this.click(function() {
							calendar.navigate($this.data('calendar-nav'));
						});
					});

					$('.btn-group button[data-calendar-view]').each(function() {
						var $this = $(this);
						$this.click(function() {
							calendar.view($this.data('calendar-view'));
						});
					});

					$('#first_day').change(function(){
						var value = $(this).val();
						value = value.length ? parseInt(value) : null;
						calendar.setOptions({first_day: value});
						calendar.view();
					});

					$('#language').change(function(){
						calendar.setLanguage($(this).val());
						calendar.view();
					});
					$('#events-modal .modal-header, #events-modal .modal-footer').click(function(e){
						e.preventDefault();
						//e.stopPropagation();
					});
					$('.events-list').click(function(e){
						jQuery('#events-modal').show();

						e.preventDefault();
						e.stopPropagation();
						 jQuery('#events-modal').addClass('modal hide fade in');
					});
					$('.close').click(function(e){
						 jQuery('#events-modal').removeClass('modal hide fade in');
							jQuery('#events-modal').hide();

					});
				}(jQuery));
	}
});
}(jQuery));

