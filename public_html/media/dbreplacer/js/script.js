/**
 * @package         DB Replacer
 * @version         6.0.1PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var RLDBReplacer = null;

(function($) {
	"use strict";

	$(document).ready(function() {
		RLDBReplacer.initialize();
		RLDBReplacer.updateFields();
	});

	RLDBReplacer = {
		// private property
		overlay: null, // holds all the overlay object
		params : {}, // holds the form values
		update : {}, // holds data on what fields to update
		pending: 1, // hold value if action is being done
		fnc    : null, // hold onclick function
		fnckey : null, // hold onkey function

		initialize: function() {
			var self = this;

			this.fnc    = ( function(noadd) {
				if (!noadd) {
					self.pending++;
				}
				$(function() {
					self.pending--;
					if (self.pending < 1) {
						self.updateFields();
					}
				});
			} );
			this.fnckey = ( function() {
				self.pending++;
				window.setTimeout(function() {
					self.fnc(1);
				}, 500);
			} );

			this.overlay = $('<div/>', {
				id: 'DBR_overlay'
			}).css({
				backgroundColor: 'black',
				position       : 'fixed',
				left           : 0,
				top            : 0,
				width          : '100%',
				height         : '100%',
				zIndex         : 5000,
				opacity        : .2
			}).hide().on('click', function() {
				self._finishLoad();
			}).appendTo('body');

			this.submit = $('#dbr_submit');
			//this.submit.addEvent( 'click', function(){ self.protectSpaces(); } );
			this.submit.hide();
		},

		getXML: function(field, params) {
			if (!field) {
				field = 'columns';
			}
			var self = this;
			this._startLoad();
			$.ajax({
				type   : 'post',
				url    : 'index.php?rl_qp=1&folder=administrator.components.com_dbreplacer&file=ajax.php&field=' + field + '&params=' + btoa(encodeURIComponent(params)),
				success: function(data) {
					self._insertData(data, field);
				},
				error  : function(data) {
					var text = DBR_INVALID_QUERY;
					var pos  = data.statusText.indexOf('SQL=SELECT');
					if (pos > 1 && data.statusText.indexOf('You have an error') < 0) {
						text = text + '<br />' + data.statusText.substr(0, pos);
					}
					self._insertData(
						'<div class="alert alert-danger">' + text + '</div>',
						field);
				}
			});
		},

		resetFields: function(forced) {
			$('.dbr_element').each(function(i, el) {
				el.value = '';
			});
			this.updateFields(1);
		},

		protectSpaces: function() {
			$('.dbr_element').each(function(i, el) {
				if (el.type == 'textarea') {
					el.value = el.value.replace(/^ /, '||space||').replace(/ $/, '||space||');
				}
			});
		},

		updateFields: function(forced) {
			var self   = this;
			var update = 0;

			var updateall = 0;

			$('.dbr_element').each(function(i, el) {
				if (el.name == 'table' && el.type != 'select-one') {
					updateall = 1;
				}
				switch (el.type) {
					case 'checkbox':
						var val = ( el.checked ) ? el.value : '';
						break;
					case 'radio':
					case 'select-multiple':
						var val = self._multipleSelectValues(el);
						break;
					default:
						var val = el.value;
						break;
				}
				var elname = el.name.replace('[]', '');
				if (self.params[elname] != val) {
					self.update[elname] = 1 + (typeof(self.params[elname]) != "undefined");
					update              = 1;
				} else {
					self.update[elname] = 0;
				}
				self.params[elname] = val;
			});

			if (forced || updateall) {
				self._updateField('columns', forced);
				self._updateField('rows', forced);
				update = 0;
			}
			if (update) {
				if (this.update.table) {
					self._updateField('columns', (this.update.table == 2));
				}
				self._updateField('rows', 1);
			}
			self.pending = 0;
		},

		toggleInactiveColumns: function() {
			$('#dbr_results').toggleClass('hide-inactive');
		},

		_updateField: function(type, clear) {
			if (clear) {
				this.params[type] = '';
			}
			this.getXML(type, JSON.stringify(this.params));
		},

		_updateActions: function() {
			var self = this;
			$('.dbr_element').each(function(i, el) {
				switch (el.type) {
					case 'radio':
					case 'checkbox':
						$(el).bind('click.dbreplacer', self.fnc);
						$(el).bind('keyup.dbreplacer', self.fnc);
						break;
					case 'select':
					case 'select-one':
					case 'select-multiple':
					case 'text':
					case 'textarea':
						$(el).bind('change.dbreplacer', self.fnc);
						$(el).bind('keyup.dbreplacer', self.fnckey);
						break;
					default:
						$(el).bind('change.dbreplacer', self.fnc);
						break;
				}
			});
		},

		_removeActions: function() {
			var self = this;
			$('.dbr_element').each(function(i, el) {
				switch (el.type) {
					case 'radio':
					case 'checkbox':
						$(el).unbind('click.dbreplacer');
						$(el).unbind('keyup.dbreplacer');
						break;
					case 'select':
					case 'select-one':
					case 'text':
						$(el).unbind('change.dbreplacer');
						$(el).unbind('keyup.dbreplacer');
						break;
					default:
						$(el).unbind('change.dbreplacer');
						break;
				}
			});
		},

		_startLoad: function() {
			$(this.overlay).css('cursor', 'wait');
			$(this.overlay).fadeIn();
		},

		_finishLoad: function() {
			this._updateActions();
			$(this.overlay).delay(200).css('cursor', '');
			$(this.overlay).fadeOut();
		},

		_insertData: function(data, field) {
			var el = $('#dbr_' + field);
			if (el) {
				el.html(data);
			}
			if (field == 'rows') {
				if (data.indexOf('<span class="replace_string">') != -1) {
					this.submit.fadeIn();
				} else {
					this.submit.fadeOut();
				}
			}
			this._finishLoad();
		},

		_multipleSelectValues: function(el) {
			var vals = [];
			for (var j = 0; j < el.options.length; j++) {
				if (el.options[j].selected) {
					vals[vals.length] = el.options[j].value;
				}
			}
			return vals.join(',');
		}
	}
})(jQuery);
