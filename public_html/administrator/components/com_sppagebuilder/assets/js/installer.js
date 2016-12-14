/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

jQuery(function($) {

	// Install integration package
	$(document).on('click', '.sp-pagebuilder-btn-install', function(event) {
		event.preventDefault();
		var $this = $(this),
			integration = $this.closest('li').data('integration');

		$.ajax({
			type : 'POST',
			url: 'index.php?option=com_sppagebuilder&task=integrations.install&integration=' + integration,
			beforeSend: function() {
				$this.html('<i class="fa fa-spinner fa-spin"></i> Installing...');
			},
			success: function (response) {
				$this.find('i').removeAttr('class');
				var data = $.parseJSON(response);
				if(data.success) {
					$this.html('<i class="fa fa-check"></i> Installed');
					setTimeout(function(){
						$this.closest('li').removeAttr('class').addClass('installed');
					}, 500);
				} else {
					alert(data.message);
				}
			}
		});
	});

	// Activate
	$(document).on('click', '.sp-pagebuilder-btn-enable', function(event) {
		event.preventDefault();
		var $this = $(this),
			integration = $this.closest('li').data('integration');

		$.ajax({
			type : 'POST',
			url: 'index.php?option=com_sppagebuilder&task=integrations.enable&integration=' + integration,
			beforeSend: function() {
				$this.find('i').addClass('fa fa-spinner fa-spin');
			},
			success: function (response) {
				$this.find('i').removeAttr('class');
				var data = $.parseJSON(response);
				if(data.success) {
					$this.closest('li').removeAttr('class').addClass('enabled');
				} else {
					alert(data.message);
				}
			}
		});
	});

	// Deactivate
	$(document).on('click', '.sp-pagebuilder-btn-disable', function(event) {
		event.preventDefault();
		var $this = $(this),
			integration = $this.closest('li').data('integration');

		$.ajax({
			type : 'POST',
			url: 'index.php?option=com_sppagebuilder&task=integrations.disable&integration=' + integration,
			beforeSend: function() {
				$this.find('i').addClass('fa fa-spinner fa-spin');
			},
			success: function (response) {
				$this.find('i').removeAttr('class');
				var data = $.parseJSON(response);
				if(data.success) {
					$this.closest('li').removeAttr('class').addClass('installed');
				} else {
					alert(data.message);
				}
			}
		});
	});

	// Uninstall
	$(document).on('click', '.sp-pagebuilder-btn-uninstall', function(event) {
		event.preventDefault();
		var $this = $(this),
			integration = $this.closest('li').data('integration');

		$.ajax({
			type : 'POST',
			url: 'index.php?option=com_sppagebuilder&task=integrations.uninstall&integration=' + integration,
			beforeSend: function() {
				$this.html('<i class="fa fa-spinner fa-spin"></i> Uninstalling...');
			},
			success: function (response) {
				$this.find('i').remove();
				var data = $.parseJSON(response);
				if(data.success) {
					$this.html('<i class="fa fa-check"></i> Uninstalled');
					setTimeout(function(){
						$this.html('Uninstall');
						$this.closest('li').removeAttr('class').addClass('available');
					}, 500);
				} else {
					alert(data.message);
				}
			}
		});
	});

});
