/**
 * @package         Regular Labs Extension Manager
 * @version         7.1.4
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var RLEM_RLEM       = 0;
var RLEM_IDS_FAILED = [];
var RLEM_MESSAGES   = {'error': [], 'warning': []};
var RLEM_TASK       = 'install';
var RLEM_INSTALL    = 0;

(function($) {
	$(document).ready(function() {
		RegularLabsManagerProcess.resizeModal();
	});
	$(window.parent).resize(function() {
		RegularLabsManagerProcess.resizeModal();
	});

	if (typeof RegularLabsManagerProcess !== 'undefined') {
		return;
	}

	RegularLabsManagerProcess = {

		process: function(task, retry) {
			this.hide('title');
			this.show('processing', $('.titles'));

			if (retry) {
				this.processNextStep(0);
				return;
			}

			RLEM_TASK    = task;
			RLEM_INSTALL = (task != 'uninstall');

			var sb = window.parent.SqueezeBox;
			sb.overlay['removeEvent']('click', sb.bound.close);

			if (RLEM_IDS[0] == 'extensionmanager') {
				RLEM_RLEM = 1;
			}

			sb.setOptions({
				onClose: function() {
					window.parent.location.href = window.parent.location;
				}
			});

			this.processNextStep(0);
		},

		processNextStep: function(step) {
			var id = RLEM_IDS[step];

			if (id) {
				this.install(step);
				this.resizeModal();

				return;
			}

			var sb = window.parent.SqueezeBox;
			this.hide('title');
			if (RLEM_IDS_FAILED.length) {
				this.showMessages('error', 'failed');
				this.showMessages('warning', 'failed');
				this.show('failed', $('.titles'));
				RLEM_IDS        = RLEM_IDS_FAILED;
				RLEM_IDS_FAILED = [];
			} else {
				this.hide('processlist');
				this.showMessages('warning', 'done');
				this.show('done', $('.titles'));
				if (!RLEM_RLEM) {
					window.parent.RegularLabsManager.refreshData(1);
					sb.removeEvents();
				}
			}
			sb.overlay['addEvent']('click', sb.bound.close);

			this.resizeModal();
		},

		install: function(step, retry_once) {
			var id = RLEM_IDS[step];

			this.hide('status', $('tr#row_' + id));
			this.show('processing_' + id);

			var url = this.getInstallUrl(id);

			RegularLabsScripts.loadajax(url,
				'RegularLabsManagerProcess.processResult( data, ' + step + ', ' + retry_once + ' )',
				'RegularLabsManagerProcess.processResult( data, ' + step + ', ' + retry_once + ' )',
				RLEM_TOKEN + '=1'
			);
		},

		getInstallUrl: function(id) {
			var url = 'index.php?option=com_regularlabsmanager&view=process&tmpl=component&id=' + id;

			if (!RLEM_INSTALL) {
				return url + '&action=uninstall';
			}

			var ext_url = $('#url_' + id).val() + '&action=' + RLEM_TASK + '&host=' + window.location.hostname;

			return url + '&action=install&url=' + encodeURIComponent(ext_url);
		},

		processResult: function(data, step, retry_once) {
			data = !data || typeof data !== 'string' ? '' : data.trim();

			if (!data && !retry_once) {
				this.processNextStep(step, 1);

				return;
			}

			var id = RLEM_IDS[step];

			this.hide('status', $('tr#row_' + id));

			if (!data || ( data !== '1' && data.indexOf('<div class="alert alert-success"') < 0 )) {
				RLEM_IDS_FAILED.push(id);
				this.enqueueMessages('error', id, data);
				this.show('failed_' + id);
				this.processNextStep(++step);

				return;
			}

			this.enqueueMessages('warning', id, data);
			this.show('success_' + id);
			this.processNextStep(++step);
		},

		show: function(classes, parent) {
			if (!parent) {
				parent = $('div#rlem');
			} else {
				parent.addClass(classes.replace(',', ''));
			}

			classes = '.' + classes.replace(', ', ', .');
			parent.find(classes).removeClass('hide');
		},

		hide: function(classes, parent) {
			if (!parent) {
				parent = $('div#rlem');
			} else {
				parent.removeClass(classes.replace(',', ''));
			}

			classes = '.' + classes.replace(', ', ', .');
			parent.find(classes).addClass('hide');
		},

		showMessages: function(type, parent_class) {
			if (!RLEM_MESSAGES[type].length) {
				return;
			}

			$('.' + parent_class + ' .' + type + 's > div').html('<p class="alert-message">' + RLEM_MESSAGES[type].join('</p><p class="alert-message">') + '</p>');
			$('.' + parent_class + ' .' + type + 's').show();

			RLEM_MESSAGES[type] = [];
		},

		enqueueMessages: function(type, id, data) {
			var title = '<strong>' + $('#ext_name_' + id).html() + '</strong><br>';

			if (data.indexOf('</') < 0) {
				if (type == 'error') {
					RLEM_MESSAGES[type].push(title + data);
				}

				return;
			}

			var regex = new RegExp('<div class="alert '
				+ ( type == 'warning' ? '(?:alert-warning)?' : 'alert-' + type)
				+ '">[\\s\\S]*?<p class="alert-message">([\\s\\S]*?)<\\/p>', 'm');
			var match = data.match(regex);

			if (!match) {
				return;
			}

			var message = match[1];

			if (message.indexOf('JFolder: :delete') > -1) {
				return;
			}

			RLEM_MESSAGES[type].push(title + message);
		},

		resizeModal: function() {
			var orig_height = $('.sbox-content-iframe > iframe', window.parent.document).height();
			var max_height  = $(window.parent).height() - 100;
			var new_height  = $('#rlem').height() + 38;

			if (new_height < orig_height && new_height > orig_height - 20) {
				new_height = orig_height;
			}
			if (new_height > max_height) {
				new_height = max_height;
			}

			if (new_height == orig_height) {
				return;
			}

			window.parent.SqueezeBox.resize({x: 480, y: new_height});

			new_width = $('.sbox-content-iframe', window.parent.document).width();
			$('.sbox-content-iframe > iframe', window.parent.document).width(new_width).height(new_height);
		}
	}
})(jQuery);
