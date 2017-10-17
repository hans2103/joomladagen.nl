/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

jQuery(function($) {

	//languages
	$(document).on('click', '.sp-pagebuilder-lang-btns .sp-pagebuilder-btn-install, .sp-pagebuilder-lang-btns .sp-pagebuilder-btn-update', function(event) {
		event.preventDefault();
		var $this = $(this),
			language = $this.closest('tr').data('language');

			$.ajax({
				type : 'POST',
				url: 'index.php?option=com_sppagebuilder&task=languages.install&language=' + language,
				beforeSend: function() {
					$this.html('<i class="fa fa-spinner fa-spin"></i> Installing...');
				},
				success: function (response) {
					var data = $.parseJSON(response);
					if(data.success) {
						$this.find('i').removeAttr('class');
						$this.html('<i class="fa fa-check"></i> Installed');
						$this.closest('tr').find('.installed-version').html('<span class="label label-success">'+ data.version +'</span>');
						setTimeout(function(){
							$this.closest('tr').removeAttr('class').addClass('enabled update-updated');
						}, 500);
					} else {
						$this.find('i').removeAttr('class');
						$this.html('<i class="fa fa-exclamation-triangle"></i> error');
						alert(data.message);
					}

				}
			});
	});

});
