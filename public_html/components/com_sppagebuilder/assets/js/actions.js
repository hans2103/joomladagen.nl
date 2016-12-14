/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

jQuery(function($) {

	$('#btn-save-page').on('click', function(event) {
		event.preventDefault();

		var $this = $(this);

		var pageData = $.parseJSON($('#jform_sptext').val());

		pageData.filter(function(row){
			return row.columns.filter(function(column){
				return column.addons.filter(function(addon){
					if (addon.type === 'sp_row') {
						return addon.columns.filter(function(column){
							return column.addons.filter(function(addon){
								if(typeof addon.htmlContent != undefined){
									delete addon.htmlContent;
								}
								if(typeof addon.assets != undefined){
									delete addon.assets;
								}
								return addon;
							})
						})

					}else{
						if(typeof addon.htmlContent != undefined){
							delete addon.htmlContent;
						}
						if(typeof addon.assets != undefined){
							delete addon.assets;
						}
						return addon;
					}

				});
			})
		});

		$('#jform_sptext').val(JSON.stringify(pageData))

		var form = $('#adminForm');

		$.ajax({
			type : 'POST',
			url: 'index.php?option=com_sppagebuilder&task=page.apply',
			data: form.serialize(),
			beforeSend: function() {
				$this.find('.fa-save').removeClass('fa-save').addClass('fa-spinner fa-spin');
			},
			success: function (response) {

				try {
					var data = $.parseJSON(response);

					$this.find('.fa').removeClass('fa-spinner fa-spin').addClass('fa-save');

					if($('.sp-pagebuilder-notifications').length === 0) {
						$('<div class="sp-pagebuilder-notifications"></div>').appendTo('body')
					}

					var msg_class = 'success';

					if(!data.status) {
						var msg_class = 'error';
					}

					if(data.title) {
						$('#jform_title').val(data.title);
					}

					if(data.id) {
						$('#jform_id').val(data.id)
					}

					$('<div class="notify-'+ msg_class +'">' + data.message + '</div>').css({
						opacity: 0,
						'margin-top': -15,
						'margin-bottom': 0
					}).animate({
						opacity: 1,
						'margin-top': 0,
						'margin-bottom': 15
					},200).prependTo('.sp-pagebuilder-notifications');

					$('.sp-pagebuilder-notifications').find('>div').each(function() {
						var $this = $(this);

						setTimeout(function(){
							$this.animate({
								opacity: 0,
								'margin-top': -15,
								'margin-bottom': 0
							}, 200, function() {
								$this.remove();
							});
						}, 3000);
					});

					if(!data.status) {
						return;
					}

					window.history.replaceState("", "", data.redirect);

					if(data.preview_url) {
						if($('#btn-page-preview').length === 0) {
							$('#btn-page-options').parent().before('<div class="sp-pagebuilder-btn-group"><a id="btn-page-preview" target="_blank" href="'+ data.preview_url +'" class="sp-pagebuilder-btn sp-pagebuilder-btn-primary"><i class="fa fa-eye"></i> Preview</a></div>');
						}
					}

					if(event.target.id == 'btn-save-new') {
						window.location.href= "index.php?option=com_sppagebuilder&view=page&layout=edit";
					}

					if(event.target.id == 'btn-save-close') {
						window.location.href= "index.php?option=com_sppagebuilder&view=pages";
					}

				} catch (e) {
					window.location.href= "index.php?option=com_sppagebuilder&view=pages";
				}
			}
		})
	});
});
