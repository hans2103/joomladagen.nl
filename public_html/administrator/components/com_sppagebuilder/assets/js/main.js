/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

jQuery(function($) {

	$(document).ready(function(){

		$('#toolbar-apply, #toolbar-save, #toolbar-save-copy').on('click', function(event) {
			$('#sp-page-builder').append($('<div class="ajax-loader"></div>'));
		});

		$('#jform_id, #jform_sptext').closest('.control-group').hide();

		$(this).find('select').each(function(){
			$(this).chosen('destroy');
		});

		$(this).init_tinymce();

		jqueryUiLayout();

		//Filter Addons
		$(document).on('click', '#modal-addons .addon-filter ul li a', function(){
			var $self = $(this);
			var $this = $(this).parent();
			$self.closest('ul').children().removeClass('active');
			$self.parent().addClass('active');

			if($this.data('category')=='all') {
				$('#modal-addons').find('.pagebuilder-addons').children().removeAttr('style');
				return true;
			}

			$('#modal-addons').find('.pagebuilder-addons').children().each(function(){
				if($(this).hasClass( 'addon-cat-' + $this.data('category') )) {
					$(this).removeAttr('style');
				} else {
					$(this).css('display', 'none');
				}
			});

		});

		//Search addon
		$(document).on('keyup', '#modal-addons #search-addon', function(){
			var value = $(this).val();
			var exp = new RegExp('.*?' + value + '.*?', 'i');

			$('#modal-addons .addon-filter ul li').removeClass('active').first().addClass('active');

			$('#modal-addons').find('.pagebuilder-addons').children().each(function() {
				var isMatch = exp.test($('.element-title', this).text());
				$(this).toggle(isMatch);
			});
		});

		//Duplicate row
		$(document).on('click', '.duplicate-row', function(event){
			event.preventDefault();

			var $this = $(this),
				$parent = $this.closest('.pagebuilder-section'),
				$clone = $parent[0].outerHTML;

			var $cloned = $($clone).insertAfter($parent).fadeIn(500);

			jqueryUiLayout();
		});

		$(document).on('click', '.add-addon', function(event){
			event.preventDefault();

			$('#modal-addons .addon-filter ul li').removeClass('active').first().addClass('active');

			var $columns = $('.page-builder-area .column');
			$columns.removeClass('active-column').find('.generated').each(function(){
				$(this).removeClass('active-generated');
			});

			$(this).parent().prev().addClass('active-column');

			var $html = $('.pagebuilder-addons-wrapper').find('.pagebuilder-addons').clone(true);
			$('#modal-addons').find('.sp-modal-body').empty();
			$('#modal-addons').find('.sp-modal-body').html( $html );
			$('#modal-addons').spmodal();
		});

		//EDIT GENERATED ADDON
		$(document).on('click', '.addon-edit', function(event) {
			event.preventDefault();
			var $toClone = $(this).closest('.generated').addClass('active-generated');
			
			$('#modal-addon').find('.addon-bg-loader').show();
			$('#modal-addon').spmodal('show');

			var addonData 		= $toClone.data(),
				addon_name 		= 'sp_' + addonData.name,
				addon_settings 	= addonData.settings;

			var request = $.ajax({
				url: "index.php?option=com_sppagebuilder&view=ajax&format=raw&action=addon-load&action_role=edit",
				method: "POST",
				data: { addon_name : addon_name, addon_settings: addon_settings },
				dataType: "html"
			});

			request.done(function( msg ) {
				if (msg) {
					$('#modal-addon').manageAddons(msg);
					$('#modal-addon').find('#save-change').removeClass('addnew').addClass('edit');
				}
			});
		});

		// OPEN ADDON SETTINGS
		$('.addon-open').on('click',function(event){
			event.preventDefault();

			$('#modal-addons').spmodal('hide');

			$('#modal-addon').find('.addon-bg-loader').show();
			$('#modal-addon').spmodal('show');

			var addon_name = $(this).data('tag');

			var request = $.ajax({
				url: "index.php?option=com_sppagebuilder&view=ajax&format=raw&action=addon-load",
				method: "POST",
				data: { addon_name : addon_name },
				dataType: "html"
			});

			request.done(function( msg ) {
				if (msg) {
					$('#modal-addon').manageAddons(msg);
					$('#modal-addon').find('#save-change').removeClass('edit').addClass('addnew');
				}
			});

			request.fail(function( jqXHR, textStatus ) {
				alert( "Request failed: " + textStatus );
			});
		});

		// MANAGE ADDONS SETTINGS
		$.fn.manageAddons = function(msg){

			var $this = $(this);
			
			$this
				.find('.sp-modal-body')
					.empty()
					.append(msg)
				.end()
				.find('.sppb-color')
					.addClass('minicolors')
				.end()
				.find('.minicolors')
					.minicolors({
					control: 'hue',
					position: 'bottom',
					theme: 'bootstrap'
				});

			$this.randomIds();
			$this.find('.sp-modal-title').text('');
			$this.find('.sp-modal-title').text( $this.find('h3').text() );

			$('#modal-addon .accordion .addon-title').each(function(){
				$(this).closest('.accordion-group').find('.accordion-toggle').text( $(this).val() );
			});

			$().sortableRepeatble();
			$this.find('select').chosen({allow_single_deselect:true});
			$this.find('.sppb-editor').each(function(){
				var $id = $(this).attr('id');
				tinymce.execCommand('mceAddEditor', false, $id);
			});

			$this.find('.addon-bg-loader').hide();
		}


		// ADD REPEATABLE
		$(document).on('click', '.clone-repeatable', function(){
			$(this).next().find('>.accordion-group:first-child').cloneRepeatable();
		});

		// CLONE REPEATABLE
		$(document).on('click', '#modal-addon .action-duplicate', function(){
			$(this).closest('.accordion-group').cloneRepeatable();
		});

		//REMOVE REPEATABLE
		$(document).on('click', '#modal-addon .action-remove', function(event){

			event.preventDefault();
			if($(this).closest('.accordion').find('.accordion-group').length != 1) //Do not delete last item
			{
				if ( confirm("Click Ok button to delete, Cancel to leave.") == true ) {
					$(this).closest('.accordion-group').fadeOut(500, function(){
						$(this).remove();
					});
				}
			}

		});

		//SAVE ADDON SETTINGS
		$(document).on('click', '#modal-addon #save-change', function(){
			var title;

			if ( $(this).hasClass('addnew') ){
				var addons 		= $('#modal-addon').getAddOnsSettings(),
					$generated 	= $('#modal-addon').find('.generated'),
					$clone 		= $generated.clone().data('settings',addons);

				$clone.find('.addon-input-title').text(addons.atts.admin_label);
				$clone.appendTo( $('.page-builder-area .column.active-column'));
			} else  {
				var addons = $('#modal-addon').getAddOnsSettings();
				$('.active-generated').data('settings',addons)
										.find('.addon-input-title').text(addons.atts.admin_label)
										.removeClass('active-generated');
			}

			$('#modal-addons').spmodal('hide');
			$('#modal-addon').find('.sp-modal-body').empty();

		});

		// REMOVE ACTIVE CLASS/DESELECT ACTIVE SELECTOR
		$(document).on('click', '#save-change, .sp-modal-footer .sppb-btn-danger', function(){
			$('.active-generated').removeClass('active-generated');
		});

		$('.sp-modal-header').on('click','button.close',function(){
			$('.active-generated').removeClass('active-generated');
		});

		// Duplicated column
		$(document).on('click', '.action .addon-duplicate', function(event){
			event.preventDefault();
			var $that = $(this),
				$parent = $that.closest('.generated'),
				$original = $that.closest('.generated').removeClass('active-generated'),
				$clone = $original.clone(true, true);
			$clone.hide().insertAfter( $parent ).fadeIn(500);
		});

		// REMOVE ROW
		$(document).on('click', '.delete-row', function(event){
			event.preventDefault();

			if ( confirm("Click Ok button to delete Row, Cancel to leave.") == true )
			{
				$(this).closest('.pagebuilder-section').fadeOut(100, function(){
					$(this).remove();
				});
			}
		});

		// REMOVE ADDON
		$(document).on('click', '.action .remove-addon', function(event){
			event.preventDefault();

			if ( confirm("Click Ok button to delete, Cancel to leave.") == true ) {
				$(this).closest('.generated').remove();
			}

		});

		// TITLE CHANGE onKeyUp
		$(document).on('keyup', '#modal-addon .accordion .addon-title', function(){
			$(this).closest('.accordion-group').find('.accordion-toggle').text( $(this).val() );
		});

		// GENERATE ROW LAYOUT/ GENERATE COLUMNS
		$(document).on('click', '.row-layout', function(event){
			event.preventDefault();

			var $that 			= $(this),
				colType 		= $that.data('type'),
				$column_start 	= '<div class="column ui-sortable">',
				$column_end 	= '</div>',
				column;

			if ($that.hasClass('active') && colType != 'custom' ) {
				return;
			};

			if (colType == 'custom')
			{
				column = prompt('Enter your custom column layout like 4,2,2,2,2 as total 12 grid','4,2,2,2,2');
				if (!column) {
					return;
				}
			}

			var $parent 		= $that.closest('.row-layout-container'),
				$gparent 		= $that.closest('.pagebuilder-section'),
				oldLayoutData 	= $parent.find('.active').data('layout'),
				oldLayout;


			// check inner section
			if ($gparent.hasClass('section-inner'))
			{
				var $column_setting_html = '<div class="col-settings"><a class="add-addon" href="javascript:void(0)"><i class="fa fa-plus-circle"></i> Addon</a> <a class="column-options" href="javascript:void(0)"><i class="fa fa-cog"></i> Options</a></div></div>';
				
				$column_start 	= '<div class="column inner-col ui-sortable">';
			} else {
				var $column_setting_html = '<div class="col-settings">' + add_row_button + '<a class="add-addon" href="javascript:void(0)"><i class="fa fa-plus-circle"></i> Addon</a> <a class="column-options" href="javascript:void(0)"><i class="fa fa-cog"></i> Options</a></div></div>';
			}

			if (oldLayoutData !=12) {
				oldLayout = oldLayoutData.split(',');
			}else{
				oldLayout = ['12'];
			}

			var layoutData 		= $that.data('layout'),
				newLayout;

			if(layoutData != 12 ){
				newLayout = layoutData.split(',');
			}else{
				newLayout = ['12'];
			}

			if ( colType == 'custom' ) {
				var error 	= true;

				if ( column != null ) {
					var colArray = column.split(',');

					var colSum = colArray.reduce(function(a, b) {
						return Number(a) + Number(b);
					});

					if ( colSum == 12 ) {
						newLayout = colArray;
						$(this).data('layout', column)
						error = false;
					}
				}

				if (error) {
					alert('Error generated. Please correct your column arragnement and try again.');
					return false;
				}
			}

			var col = [],
				colAttr = [];

			$gparent.find('>.row>.column-parent').each(function(i,val){
				col[i] = $(this).find('.column').html();
				var colData = $(this).data();

				if (typeof colData == 'object') {
					colAttr[i] = $(this).data();
				}else{
					colAttr[i] = '';
				}
			});

			$parent.find('.active').removeClass('active');
			$that.addClass('active');

			var new_item = '';

			if ( oldLayout.length > newLayout.length )
			{
				var j = 1;
				for( var i=0; i < newLayout.length; i++ )
				{
					var dataAttr = '';
					if (typeof colAttr[i] == 'object') {
						$.each(colAttr[i],function(index,value){
							dataAttr += ' data-'+index+'="'+value+'"';
						});
					}

					new_item +='<div class="column-parent col-sm-'+newLayout[i]+'" '+dataAttr+'>';

					if ( j != newLayout.length ) {
						if (col[i]) {
							new_item += $column_start;
							new_item += col[i];
							new_item += $column_end;
						} else {
							new_item += $column_start + $column_end;
						}
					} else {
						new_item += $column_start;

						for(; j < col.length+1; j++) {
							if( col[j-1] ) {
								new_item += col[j-1];
							}
						}
						new_item += $column_end;
					}

					new_item += $column_setting_html;
					j++;
				}
			} else {
				for( var i=0; i<newLayout.length; i++ ) {
					var dataAttr = '';

					if ( typeof colAttr[i] == 'object' ) {
						$.each(colAttr[i],function( index, value ) {
							dataAttr += ' data-'+index+'="'+value+'"';
						});
					}

					new_item += '<div class="column-parent col-sm-'+newLayout[i]+'" '+dataAttr+'>';

					if ( col[i] ){
						new_item += $column_start;
						new_item += col[i];
						new_item += $column_end;
					}else{
						new_item += $column_start + $column_end;
					}
					new_item += $column_setting_html;
				}
			}

			$old_column = $gparent.find('.column-parent');
			$gparent.find('.row.ui-sortable').append( new_item );
			$old_column.remove();
			$gparent.find('.column').columnSortable();
			jqueryUiLayout();

			return false;
		});


		// COLUMNS SETTINGS
		$(document).on('click', '.column-options', function(event){
			event.preventDefault();

			var $this = $(this);
			$('.column-parent').removeClass('active-column-parent');
			var $parent = $(this).closest('.column-parent');
			$parent.addClass('active-column-parent');

			$('#modal-column').find('.sp-modal-body').empty();
			var $clone = $('.column-settings').clone(true);

			$clone.find('.sppb-color').each(function(){
				$(this).addClass('minicolors');
			});

			//Chosen
			$clone = $('#modal-column').find('.sp-modal-body').append( $clone );

			//retrive column settings
			$clone.find('.addon-input').each(function(){
				var $that = $(this),
					$attr_value = $parent.data( $(this).data('attrname'));

				// make a check for chekbox only
				if ($that.attr('type') == 'checkbox') {
					if ($attr_value == '1') {
						$that.attr('checked','checked');
					}else{
						$that.removeAttr('checked');
					}
				}else{
					$(this).val( $attr_value );
				}
			});

			$clone.find('.minicolors').each(function() {
				$(this).minicolors({
					control: 'hue',
					position: 'bottom',
					theme: 'bootstrap'
				});
			});

			$clone.find('select').chosen({
				allow_single_deselect: true
			});
			$('#modal-column').spmodal();

		});

		// OPEN COPY ROW MODAL
		$(document).on('click','.copy-row',function(event) {
			event.preventDefault();

			var $this = $(this),
				$parent = $this.closest('.pagebuilder-section'),
				$copyText = $parent[0].outerHTML;

			var request = $.ajax({
				url: "index.php?option=com_sppagebuilder&view=ajax&format=raw&action=copy",
				type: "POST",
				data: { copyText : encodeURIComponent( $copyText ) },
				dataType: "text",
				complete:function(response){
					if (response.readyState === 4) {

						var $modal = $('#modal-copy-paste');

						$modal.on('show.bs.spmodal', function(event) {
							$(this).find('.sp-modal-title').text('Copy the code below');
							$(this).find('#paste-row-save').hide();

							var $modalBody = $(this).find('.sp-modal-body'),
								$modalTextarea = '<textarea class="copy-paste-text" readonly="readonly" onclick="this.focus();this.select()" >'+response.responseText+'</textarea>';
							$modalBody.empty();
							$modalBody.append( $modalTextarea );
						}); 

						$modal.on('shown.bs.spmodal', function(event) {
							$(this).find('.copy-paste-text').select().focus();
						});

						$modal.spmodal();
					} else {
						alert('Something bad happend, try again later!');
					}						
				}
			});
		});

		// PASTE ROW CHANGE
		$( '#paste-row-save' ).on( 'click', function( event ) {
			event.preventDefault();

			var pasteText = $( '#modal-copy-paste' ).find( '.copy-paste-text' ).val();
			
			var request = $.ajax({
				url: "index.php?option=com_sppagebuilder&view=ajax&format=raw&action=paste",
				type: "POST",
				data: { pasteText : pasteText },
				dataType: "text",
				complete:function( response ){
					if ( response.readyState === 4 )
					{
						var $currentRow 	= $( '.page-builder-area' ).find( '.pagebuilder-section.active-row' ),
							section_text 	= decodeURIComponent( response.responseText );

						var section_check 		= $currentRow.hasClass( 'section-inner' ),
							section_text_check 	= $(section_text).hasClass( 'section-inner' );

						if ( ( section_check && section_text_check ) || ( !section_check && !section_text_check ) ) {
							$( section_text ).insertAfter( $currentRow );
							$currentRow.fadeOut( 100, function() {
								$( this ).remove();
							});

							jqueryUiLayout();
						} else {
							alert( 'You can\'t exchange row/section between inner and outer' );
						}
					} else {
						alert( 'Something bad happend, try again later!' );
					}						
				}
			});
		});

		// OPEN PASTE ROW MODAL
		$(document).on('click', '.paste-row', function(event) {
			event.preventDefault();

			var $parent = $(this).closest('.pagebuilder-section');

			$('.pagebuilder-section').removeClass('active-row');
			$parent.addClass('active-row');

			var $modal = $('#modal-copy-paste');

			$modal.on('show.bs.spmodal', function(event) {
				$(this).find('.sp-modal-title').text('Paste previous copied code below');
				$(this).find('#paste-row-save').show();

				var $modalBody = $(this).find('.sp-modal-body'),
					$modalTextarea = '<textarea class="copy-paste-text"></textarea>';
				$modalBody.empty();
				$modalBody.append( $modalTextarea );
			});

			$modal.spmodal();
		});

		// SAVE COLUMN SETTINGS
		$(document).on('click', '#save-column', function(){

			$('#modal-column').find('.addon-input').each(function(){

				var $this = $(this),
					$parent = $('.active-column-parent'),
					$attrname = $this.data('attrname');

				$parent.removeData( $attrname );

				if ($this.attr('type') == 'checkbox') {
					if ($this.attr("checked")) {
						$parent.attr('data-' + $attrname, '1');
					}else{
						$parent.attr('data-' + $attrname, '0');
					}
				}else{
					$parent.attr('data-' + $attrname, $(this).val());
				}

			});
		});

		// DISABLE ROW
		$(document).on('click', '.disable-row', function(event) {
			event.preventDefault();

			var $currentRow = $(this).closest('.pagebuilder-section');

			if ( $currentRow.hasClass( 'row-disable' ) ) {
				$currentRow.removeClass( 'row-disable' );
				$currentRow.find('.section-inner').removeClass( 'row-disable' );
			} else {
				$currentRow.addClass( 'row-disable' );
				$currentRow.find('.section-inner').addClass( 'row-disable' );
			}
		});

		// ROW SETTINGS MODAL/FORM OPEN
		$(document).on('click', '.row-options', function(event){
			event.preventDefault();

			var $this = $(this),
				$parent = $(this).closest('.pagebuilder-section');

			$('.pagebuilder-section').removeClass('active-row');
			$parent.addClass('active-row');

			$('#modal-row').find('.sp-modal-body').empty();
			var $clone = $('.row-settings').clone(true);

			$clone.find('.sppb-color').each(function(){
				$(this).addClass('minicolors');
			});

			$clone = $('#modal-row').find('.sp-modal-body').append( $clone );

			//retrive column settings
			$clone.find('.addon-input').each(function(){
				var $that = $(this),
					$attr_value = $parent.data( $(this).data('attrname'));

				// make a check for chekbox only
				if ($that.attr('type') == 'checkbox') {
					if ($attr_value == '1') {
						$that.attr('checked','checked');
					}else{
						$that.removeAttr('checked');
					}
				}else if($that.hasClass('input-media')){
					if($attr_value){
						$that.prev('.sppb-media-preview').removeClass('no-image')
							.attr('src',pagebuilder_base+$attr_value);
					}
					$that.val( $attr_value );
				}else{
					$that.val( $attr_value );
				}
			});

			$clone.find('.minicolors').each(function() {
				$(this).minicolors({
					control: 'hue',
					position: 'bottom',
					theme: 'bootstrap'
				});
			});

			$('#modal-row').randomIds();

			$clone.find('select').chosen({
				allow_single_deselect: true
			});
			$('#modal-row').spmodal();
		});

		// REMOVE MEDIA
		$(document).on('click', 'a.remove-media', function(event){
			event.preventDefault();
			$(this).closest('.media').find('.input-media').val('');
			$(this).closest('.media').find('.media-preview img').removeAttr('src');
		});

		// SAVE ROW SETTINGS
		$(document).on('click', '#save-row', function(){
			$('#modal-row').find('.addon-input').each(function(){

				var $this = $(this),
					$parent = $('.active-row'),
					$attrname = $this.data('attrname');

				$parent.removeData( $attrname );

				if ($this.attr('type') == 'checkbox') {
					if ($this.attr("checked")) {
						$parent.attr('data-' + $attrname, '1');
					}else{
						$parent.attr('data-' + $attrname, '0');
					}
				}else{
					$parent.attr('data-' + $attrname, $(this).val());
				}

			});
		});

		$.fn.allData = function() {
			var intID = $.data(this.get(0));
			return($.cache[intID]);
		};

		/*---------------------------------------
		 *    Add section and inner section 
		 *---------------------------------------*/

		var template = $.trim( $( '#sppb-section' ).html() );

		var raw_section = template.replace(/{{section-class}}/,'')
									.replace(/{{section-data-attr}}/,'')
									.replace(/{{column-class}}/,'')
									.replace(/{{add-row-col}}/,add_row_button);

		var inner_section = template.replace(/{{section-class}}/,' section-inner')
										.replace(/{{section-data-attr}}/,'data-element_type="section"')
										.replace(/{{column-class}}/,' inner-col')
										.replace(/{{add-row-col}}/,'');

		// ADD ROW/SECTION BELOW
		$( '#add-row' ).on( 'click', function() {
			$( raw_section ).appendTo( '.page-builder-area' );
			jqueryUiLayout();
		}); 

		// ADD INNER-ROW IN COLUMN
		$( document ).on( 'click', '.add-row-col', function( event ) {
			event.preventDefault();

			$( this ).parent().prev().append( inner_section );
			jqueryUiLayout();
		});

		// ADD ROW/SECTION JUST AFTER CURRENT ROW/SECTION
		$( document ).on( 'click', '.add-rowplus', function() {

			var $parent 	= $( this ).closest( '.pagebuilder-section' ),
				check_inner = $parent.hasClass( 'section-inner' );

			if ( check_inner ) {
				$( inner_section ).insertAfter( $parent );
			} else {
				$( raw_section ).insertAfter( $parent );
			}

			jqueryUiLayout();
		});


	}); // END OF DOM READY FUNCTION

	$('#add-template').on('click', function(event) {
		event.preventDefault();
		if ($(this).siblings('#pagebuilder-templates')) {
			$('#pagebuilder-templates').slideToggle('fast');
		}
	});

	// LOAD EXISTING TEMPLATE
	$('.form-horizontal').on('click', '.add-template', function(event) {
		event.preventDefault();

		var $data = $(this).data('template');

		var request = $.ajax({
			url: "index.php?option=com_sppagebuilder&view=ajax&format=raw&action=load-template",
			type: "POST",
			data: { template : $data },
			dataType: "html",
			beforeSend:function(){
				$('#add-template i').removeClass('fa-plus').addClass('fa-spinner fa-spin');
				$('#sp-page-builder').append('<div class="ajax-loader"></div>').fadeIn('fast');
			},
			complete:function(){
				$('#add-template i').removeClass('fa-spinner fa-spin').addClass('fa-plus');
				$('#pagebuilder-templates').slideToggle('fast');
			}
		});

		request.done(function( msg ) {
			$('#sp-page-builder').empty();
			$('#sp-page-builder').append(msg).fadeIn('normal');
			jqueryUiLayout();
		});

		request.fail(function( jqXHR, textStatus ) {
			alert( "Failed to load template, Try again" );
		});
	});

	// IMPORT LAYOUT/PAGE/TEMPLATE FILED
	$('#upload-file').on('change', function() {
		var file = $(this).prop('files')[0];
		if (file === undefined) {
			$('.upload-file-name').val('');
			$('#import-layout').attr('disabled', 'disabled');
			return;
		}
		$('#import-layout').removeAttr('disabled');
		$('.upload-file-name').val(file.name);
	});

	$('#import-layout').on('click', function(event) {
		event.preventDefault();

		var formdata = new FormData(),
			file =  $("#upload-file").prop('files')[0];

		if ($(this).attr('disabled') == 'disabled') {
			return;
		}

		if (file === undefined) {
			return;
		}

		if (formdata) {
			formdata.append('importLayout', file);
		}

		var request = $.ajax({
			url: "index.php?option=com_sppagebuilder&view=ajax&format=raw&action=local-upload",
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			beforeSend:function(){
				$('#add-template i').removeClass('fa-plus').addClass('fa-spinner fa-spin');
				$('#sp-page-builder').append('<div class="ajax-loader"></div>').fadeIn('fast');
			},
			complete:function(){
				$('#add-template i').removeClass('fa-spinner fa-spin').addClass('fa-plus');
				$('#pagebuilder-templates').slideToggle('fast');
			}
		});

		request.done(function( msg ) {
			$('#sp-page-builder').empty();
			$('#sp-page-builder').append(msg).fadeIn('normal');
			$('.upload-file-name, #upload-file').val('');
			$('#import-layout').attr('disabled', 'disabled');
			jqueryUiLayout();
		});

		request.fail(function( jqXHR, textStatus ) {
			alert( "Failed to import template, Try again" );
		});
	});


	// GET PAGE SETTINGS
	function getPageSettings(){

		var item = [];
		$('.page-builder-area >.pagebuilder-section').each(function( index ) {

			var $row 		= $( this ),
				rowIndex 	= index,
				rowObj 		= getRowDataAttributes( $row , 0 ),
				disable     = checkRowDisableStat( $row ),
				layout 		= getRowLayout( $row );

			item[rowIndex] = {
				'type'  	: 'sp_row',
				'layout'	: layout,
				'disable'	: disable,
				'settings' 	: rowObj,
				'attr'		: []
			};

			$row.find( '>.row>.column-parent' ).each(function( index ){

			var $column 	= $( this ),
				colIndex 	= index,
				className 	= $column.attr( 'class' ),
				colObj 		= $column.data();
				delete colObj.sortableItem;

				item[rowIndex].attr[ colIndex ] = {
					'type' 				: 'sp_col',
					'class_name' 		: className,
					'settings' 			: colObj,
					'attr' 				: []
				};

				$column.find( '>.column>div[data-element_type]' ).each(function( index ){


					var $element 	= $( this ),
						elm_type 	= $element.data( 'element_type' ),
						codeIndex 	= index;

					// Inner section row, column and addons
					if ( elm_type === 'section' )
					{
						var rowObj_inner = $element.data();
							delete rowObj_inner.sortableItem;
							delete rowObj_inner.element_type;

						var rowIndex_inner 		= index,
							disable_inner     	= checkRowDisableStat( $element ),
							layout_inner 		= getRowLayout( $element );

						var inner_row_obj = {
							'type'  	: 'sp_row',
							'layout'	: layout_inner,
							'disable'	: disable_inner,
							'settings' 	: rowObj_inner,
							'attr'		: []
						};


						//Inner section columns

						$element.find( '.column-parent' ).each(function( index ){

							var $column_inner 		= $( this ),
								colIndex_inner 		= index,
								className_inner 	= $column_inner.attr( 'class' ),
								colObj_inner 		= $column_inner.data();
									delete colObj_inner.sortableItem;

							inner_row_obj.attr[ colIndex_inner ] = {
								'type' 				: 'sp_col',
								'class_name' 		: className_inner,
								'settings' 			: colObj_inner,
								'attr' 				: []
							};


							//Inner section addons

							$column_inner.find( '.generated' ).each(function( index ){

								var $element_inner 		= $( this ),
									elm_inner_type 		= $element_inner.data( 'element_type' ),
									codeIndex_inner 	= index;

								if ( elm_inner_type !== 'section' )
								{
									var element_inner_addon = $element_inner.getAddOns();
									inner_row_obj.attr[ colIndex_inner ].attr[codeIndex_inner] = element_inner_addon;
								}

							});

						});

						item[rowIndex].attr[colIndex].attr[codeIndex] = inner_row_obj;
					}
					else if ( elm_type === 'add_ons' )
					{
						var element_addon_array = $element.getAddOns();

						item[rowIndex].attr[colIndex].attr[codeIndex] = element_addon_array;
					}
				});

			});

		});

	return item;

	}

	// JOOMLA FORM SUBMIT HANDLER
	document.adminForm.onsubmit = function( event ){
		event.stopImmediatePropagation();
		
		$('#jform_sptext').val( JSON.stringify(getPageSettings()) );
	}

	// GET ROW ATTRIBUTES LIST
	function getRowDataAttributes( element_row , depth )
	{
		var $row = element_row,
			rowObj = $row.data();
			delete rowObj.sortableItem;

		return rowObj;
	}

	// check disable/enable the row 

	function checkRowDisableStat( element_row )
	{
		var disable = false,
			$row 	= element_row;

		if ( $row.hasClass( 'row-disable' ) ) {
			disable = true;
		}

		return disable;
	}


	// get row layout style

	function getRowLayout( element_row )
	{
		var $row 			= element_row,
			activeLayout 	= $row.find( '.row-layout.active' ),
			layoutArray 	= activeLayout.data( 'layout' ),
			layout 			= 12;

			if( layoutArray != 12){
				layout = layoutArray.split(',').join('');
			}

		return layout;
	}

	// GET ADDON SETTINGS
	$.fn.getAddOnsSettings = function() {
		var $addon 		= $(this),
			$generated	= $addon.find('.generated'),
			codeName	= $generated.data('name'),
			codeTitle	= $generated.data('addon_title'),
			codeType	= $generated.data('addon_type'),
			codeAtts 	= new Object();

		$addon.find('.item-inner > .form-group .addon-input').each( function( ) {
			codeAtts[ $(this).data('attrname') ] = $(this).getFieldValue();
		});

		var addon_obj = {
			'type' 		: codeType,
			'name' 		: codeName.toLowerCase(),
			'title'		: codeTitle,
			'atts'		: codeAtts,
			'scontent'	: []
		};

		if( $addon.find('.repeatable-items').length )
		{
			var tinymceID = tinymce.editors;
			$addon.find('.item-inner .repeatable-items .accordion-group').each(function(index)
			{
				var reIndex 	= index,
					$this 		= $(this),
					reCodeAtts 	= new Object(),
					repName 	= $this.data('inner_base');

				$this.find('.addon-input').each(function(){
					reCodeAtts[ $(this).data('attrname') ] = $(this).getFieldValue();
				});

				addon_obj.scontent[index] = {
					'type' 	: 'repeatable',
					'name'	: repName,
					'atts'	: reCodeAtts
				}
			});
		}

		return addon_obj;
	}

	// GET ADDONS FIELDS VALUES
	$.fn.getFieldValue = function(){
		var $this = $(this);

		if ($this.attr('type') == 'checkbox') {
			if ( $this.attr("checked") ) {
				return '1';
			} else {
				return '0';
			}
		} else if( $this.hasClass('sppb-editor') ) {
			var textarea_id = $this.attr('id');
			return tinyMCE.get(textarea_id).getContent({format : 'raw'});
		} else {
			return $this.val();
		}
	}

	// GET ADDON DATA: onSave/onApply
	$.fn.getAddOns = function() {
		return $(this).data('settings');
	}

	function jqueryUiLayout()
	{
		$( ".page-builder-area" ).sortable({
			placeholder: "ui-state-highlight",
			forcePlaceholderSize: true,
			axis: 'y',
			opacity: 0.8,
			tolerance: 'pointer',

		}).disableSelection();

		$('.pagebuilder-section').find('>.row').rowSortable();
		$('.column').columnSortable();
	}

	// tinyMCE EDITOR SOURCE CODE EDIT
	$(document).on('focusin', function(e) {
		if ($(e.target).closest(".mce-window").length) {
			e.stopImmediatePropagation();
		}
	});

	// TOOLTIPS
	$('.sp-tooltip').tooltip();
});