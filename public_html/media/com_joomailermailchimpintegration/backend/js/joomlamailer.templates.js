/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

!function($){
    $(document).ready(function(){

        joomlamailerJS.templates = {
            placeholders: [],
            initEditor: function() {
                $('.optionsHeader').toggle(
                    function () {
                        $('#' + $(this).data('scope')).slideUp();
                        $(this).find('.optionsHeader_r').addClass('optionsHeader_rc');
                    },
                    function () {
                        $('#' + $(this).data('scope')).slideDown();
                        $(this).find('.optionsHeader_r').removeClass('optionsHeader_rc');
                    }
                );

                $('#template').keyup(function() {
                    $(this).val($j(this).val().toLowerCase().replace(' ', '_').replace(/[^\w\s]/gi, ''));
                });

                joomlamailerJS.templates.createUploader('uploadLogo');

                var currentColor = '#000000';
                $.each($('.colorPreviewBox'), function(){
                    currentColor = $('#' + $('.colorPreview', this).data('scope')).val();
                    $('.colorPreview', this).css('background-color', currentColor);
                });

                $('.colorPreview').click(function() {
                    openPicker($(this).data('scope'));
                });

                $('.colorValue').click(function() {
                    openPicker($(this).attr('id'));
                }).keyup(function() {
                    removeLayer('picker');
                    if ($(this).val().substr(0, 1) != '#') {
                        $(this).val('#' + $(this).val());
                    }
                    $('.colorPreview[data-scope=' + $(this).attr('id') + ']').css('background-color', $(this).val());
                }).change(function() {
                   if ($(this).val() == ''){
                       $(this).val('#000000');
                   }
                }).blur(function(){
                    removeLayer('picker');
                    $('.colorPreview[data-scope=' + $(this).attr('id') + ']').css('background-color', $(this).val());
                });

                $('#placeholders').sortable({
                    revert: true,
                    axis: "y"
                });

                $('#phPosition').change(function(){
                    if ($(this).val() != ''){
                        $('#phOptions').slideDown();
                    } else {
                        $('#phOptions').slideUp();
                    }
                });

                $('#toggleSelect').toggle(
                    function(){
                        $.each($('.phCb'), function(){ this.checked = true; });
                        $('#toggleSelect').addClass('selNone');
                    },
                    function(){
                        $.each($('.phCb'), function(){ this.checked = false; });
                        $('#toggleSelect').removeClass('selNone');
                    }
                );

                $('#insertLogoUrl').click(function() {
                    if ($('.headerBar a', window.frames['previewIframe'].document).length > 0){
                        $('.headerBar a', window.frames['previewIframe'].document).attr('href', $('#logoUrl').val());
                    } else {
                        $('.headerBar img', window.frames['previewIframe'].document).wrap(
                            '<a href="' + $('#logoUrl').val() + '" style="border:none;" />');
                    }
                    $('.headerBar img', window.frames['previewIframe'].document).css('border', 'none');
                    $('.headerBar a', window.frames['previewIframe'].document).attr('title', $('#logoAlt').val());
                    $('.headerBar img', window.frames['previewIframe'].document).attr('alt', $('#logoAlt').val());
                    $('.headerBar img', window.frames['previewIframe'].document).attr('title', $('#logoAlt').val());
                });

                $('#clearPosition').click(function() {
                     joomlamailerJS.templates.clearPosition();
                });
                $('#insertPlaceholders').click(function() {
                     joomlamailerJS.templates.insertPlaceholders();
                });
                $('#applyCss').click(function() {
                     joomlamailerJS.templates.applyCss();
                });

                Joomla.submitbutton = function(pressbutton) {
                    if (pressbutton == 'cancel') {
                        Joomla.submitform(pressbutton);
                    } else {
                        if (jQuery('#template').val() == '') {
                            alert(joomlamailerJS.strings.errorTemplateName);
                        } else {
                            $.ajax({
                                url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=templates&format=raw&task=checkIfTemplateExists',
                                type: 'POST',
                                data: {
                                    'template': $('#template').val()
                                },
                                beforeSend: function() {
                                    joomlamailerJS.functions.preloaderTE();
                                },
                                success: function (templateExists) {
                                    joomlamailerJS.functions.preloaderTERemove();
                                    if (templateExists != '1' || confirm(joomlamailerJS.strings.confirmOverwriteTemplate)) {
                                        var firstColumn = $('.bodyTable td:first', window.frames['previewIframe'].document).attr('class');
                                        var sideColumnExists = $('.sideColumn', window.frames['previewIframe'].document).length;
                                        $('#templateContent').val($('#previewIframe').contents().find('html').html().replace("'","\'"));
                                        if (firstColumn == 'sideColumn' && sideColumnExists){
                                            $('#columns').val('l');
                                        } else if (firstColumn == 'defaultText' && sideColumnExists){
                                            $('#columns').val('r');
                                        }
                                        joomlamailerJS.functions.preloader();
                                        Joomla.submitform(pressbutton);
                                    }
                                }
                            });
                        }
                    }
                }
            },
            addColors: function() {
                var page = $('#page_background').val();
                $('body', window.frames['previewIframe'].document).attr('bgcolor', page);
                $('body', window.frames['previewIframe'].document).css('background', page);
                $('.backgroundTable', window.frames['previewIframe'].document).css('background', page);

                var header = $('#header_background').val();
                $('.headerTop', window.frames['previewIframe'].document).attr('bgcolor', header);
                $('.headerTop', window.frames['previewIframe'].document).css('background', header);
                $('.headerBar', window.frames['previewIframe'].document).attr('bgcolor', header);
                $('.headerBar', window.frames['previewIframe'].document).css('background', header);
                $('.headerBarText', window.frames['previewIframe'].document).css('background', header);

                var content = $('#content_background').val();
                $('.defaultText', window.frames['previewIframe'].document).attr('bgcolor', content);
                $('.defaultText', window.frames['previewIframe'].document).css('background', content);

                var sidebar = $('#sidebar_background').val();
                $('.sideColumn', window.frames['previewIframe'].document).attr('bgcolor', sidebar);
                $('.sideColumn', window.frames['previewIframe'].document).css('background', sidebar);

                var footerRow = $('#footer_background').val();
                $('.footerRow', window.frames['previewIframe'].document).attr('bgcolor', footerRow);
                $('.footerRow', window.frames['previewIframe'].document).css('background', footerRow);

                var footerText = $('#footer_text').val();
                $('.footerRow', window.frames['previewIframe'].document).css('color', footerText);
                $('.footerText', window.frames['previewIframe'].document).css('color', footerText);

                var style = '<style>';
                var bodyText = $('#body_text').val();
                style = style + '* { color: ' + bodyText + '; }';
                var headings = $('#headings').val();
                style = style + ' h1,h2,h3,h4,h5,h6,.sideColumnTitle,.mainColumnTitle,.title,.subTitle { color: ' + headings + '; }';
                var links = $('#links').val();
                style = style + ' a { color: ' + links + ';}';
                $('a', window.frames['previewIframe'].document).css('color', links);
                style = style + '</style>';
                $('body', window.frames['previewIframe'].document).append(style);

                $('.sideColumnTitle', window.frames['previewIframe'].document).css('color', '');
                $('.mainColumnTitle', window.frames['previewIframe'].document).css('color', '');
                $('.title', window.frames['previewIframe'].document).css('color', '');
                $('.subTitle', window.frames['previewIframe'].document).css('color', '');
            },
            reloadPalettes: function() {
                $.ajax({
                    url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=templates&task=reloadPalettes&format=raw',
                    type: 'POST',
                    data: {
                        'hex': $('#hex').val().replace("#",""),
                        'keywords': $('#keywords').val().replace(" ","+")
                    },
                    dataType: 'json',
                    success: function (response) {
                        $("#palettes").html(response.html);
                        if (response.js){
                            eval(response.js);
                        }
                    }
                });
            },
            applyPalette: function(x, shuffle){
                var scopes = [
                    [
                        'page_background'
                    ],
                    [
                        'content_background',
                        'sidebar_background',
                    ],
                    [
                        'header_background',
                        'footer_background',
                    ],
                    [
                        'body_text',
                        'headings',
                    ],
                    [
                        'footer_text',
                        'links'
                    ]
                ];

                for (index in scopes) {
                    if (!scopes.hasOwnProperty(index)) {
                        continue;
                    }
                    for (scope in scopes[index]) {
                        if (!scopes[index].hasOwnProperty(scope)) {
                            continue;
                        }

                        var colorIndex = (shuffle) ? Math.floor(Math.random()*4) : index;

                        $('#' + scopes[index][scope]).val(colorsets[x][colorIndex]);
                        $('.colorPreview[data-scope=' + scopes[index][scope] + ']').css('background', colorsets[x][colorIndex]);
                    }
                }

                joomlamailerJS.templates.addColors();
                if (!shuffle) {
                    $('#apply' + x).attr('href', 'javascript:joomlamailerJS.templates.applyPalette(' + x + ', 1)');
                }
            },
            createUploader: function (buttonId, scope) {
                if (scope === undefined) {
                    scope = joomlamailerJS.templates;
                }
                var uploader = new qq.FileUploader({
                    element: document.getElementById(buttonId),
                    action: 'index.php?option=com_joomailermailchimpintegration&controller=templates&format=raw&task=uploadLogo&name=' + $('#template').val(),
                    allowedExtensions: ['jpg','jpeg','png','gif','bmp'],
                    multiple: false,
                    /*messages: {
                        typeError: '{file}: ' + joomlamailerJS.strings.errorInvalidFileType + '! ' + joomlamailerJS.strings.allowedFileTypes + ': {extensions}'
                    },*/
                    onSubmit: function(id, fileName){ $(buttonId + ' .qq-upload-list').fadeIn(); },
                    onComplete: function(id, fileName, responseJSON){ scope.uploadComplete(fileName, buttonId); },
                    debug: false
                });
            },
            uploadComplete: function (fileName, buttonId){
                if (buttonId == 'uploadLogo') {
                    if (window.frames['previewIframe'] !== undefined) {
                        $('.headerBar img', window.frames['previewIframe'].document)
                            .attr('src', joomlamailerJS.misc.baseUrl + 'tmp/' + joomlamailerJS.misc.templateFolder + '/'  + fileName);

                        iframeHeight = $('.backgroundTable', window.frames['previewIframe'].document).height();
                        if (iframeHeight){
                            $('#previewIframe').attr('height', iframeHeight + 50);
                        }
                    }

                    $('#logoFilename').html(fileName);
                    $('.qq-upload-list').fadeOut();
                    $('#logoSizeInfo').fadeOut();
                } else {
                    buttonId = buttonId.replace('Upload', '');
                    $('#' + buttonId + 'Icon' , window.frames['previewIframe'].document)
                        .attr('src', joomlamailerJS.misc.baseUrl + 'tmp/' + joomlamailerJS.misc.templateFolder + '/' + fileName);
                    $('#' + buttonId + 'Upload .qq-upload-list').fadeOut();
                }
            },
            logoWidthInfo: function() {
                var logoWidth = $('.headerBar', $('#previewIframe').contents()).width();
                if (logoWidth) {
                    var logoSizeInfo = joomlamailerJS.strings.templateWidthOf + ' <span style="font-weight:bold;">'
                        + logoWidth + 'px</span>';
                    $('#logoSizeInfo').html(logoSizeInfo);
                }
            },
            resetIframe: function() {
                var iframeHeight = $('#previewIframe').contents().height();
                if (iframeHeight) {
                    $('#preview').css({
                        'height': (iframeHeight + 50),
                        'overflow-y': 'hidden'
                    });
                    $('#previewIframe').attr('height', (iframeHeight + 50));
                }

                $('.sideColumnTitle,.mainColumnTitle', window.frames['previewIframe'].document).editable(
                    function(value, settings){ return(value); },
                {
                    //submit : 'OK',
                    //cancel : 'Cancel',
                    data: function(value, settings) {
                        var retval = value.replace(/&lt;/, '<').replace(/&gt;/,'>');
                        retval = retval.replace(/&lt;/, '<').replace(/&gt;/,'>');
                        retval = retval.replace(/&lt;/, '<').replace(/&gt;/,'>');
                        retval = retval.replace(/&lt;/, '<').replace(/&gt;/,'>');
                        retval = retval.replace(/&lt;/, '<').replace(/&gt;/,'>');

                        return retval;
                    },
                    onblur: 'submit',
                    width: '99%'
                }).addClass('editable').attr('title', joomlamailerJS.strings.clickToEdit);

                $('a', window.frames['previewIframe'].document).click(function(){
                    var link = $(this).attr('href').replace(joomlamailerJS.misc.templateUrl, '').replace('*%7', '*|').replace('%7*', '|*');
                    alert(link);
                    return false;
                });

                $('#joomlamailer_ajax_loader', window.frames['previewIframe'].document).remove();
            },
            clearPosition: function (){
                var pos = $('#phPosition').val();
                if (pos == '') {
                    return;
                }

                if (confirm(joomlamailerJS.strings.confirmClearPosition + " " + $('#phPosition option:selected').text() + joomlamailerJS.strings.position)) {
                    var template = encodeURIComponent($('html', window.frames['previewIframe'].document).html());
                    $.ajax({
                        url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=templates&format=raw&task=updatePosition',
                        type: 'POST',
                        data: {
                            'template': template,
                            'path': joomlamailerJS.misc.tmpPath,
                            'position': pos,
                            'insertHtml': ''
                        },
                        dataType: 'json',
                        beforeSend: function() {
                            joomlamailerJS.functions.preloaderTE();
                        },
                        success: function (response) {
                            $('#previewIframe').attr('src', $('#previewIframe').attr('src'));
                        }
                    });
                }
            },
            insertPlaceholders: function (){
                var pos = $('#phPosition').val();
                if (pos == '') {
                    return;
                }

                var insertHtml = $(pos, window.frames['previewIframe'].document).html();
                $.each($('.phCb'), function() {
                    if (this.checked === true) {
                        insertHtml += joomlamailerJS.templates.placeholders[this.value];
                    }
                });

                var template = encodeURIComponent($('html', window.frames['previewIframe'].document).html());

                $.ajax({
                    url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=templates&format=raw&task=updatePosition',
                    type: 'POST',
                    data: {
                        'template': template,
                        'path': joomlamailerJS.misc.tmpPath,
                        'position': pos,
                        'insertHtml': encodeURIComponent(insertHtml)
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        joomlamailerJS.functions.preloaderTE();
                    },
                    success: function (response) {
                        $('#previewIframe').attr('src', $('#previewIframe').attr('src'));
                    }
                });
            },
            applyCss: function () {
                var pos = $('#cssElement').val();
                if (pos == '') {
                    return;
                }

                if ($('#customFont').val() != ''){
                    var font = $('#customFont').val();
                } else {
                    var font = $('#font').val();
                }

                $(pos, window.frames['previewIframe'].document).css('font-family', font);
                $(pos, window.frames['previewIframe'].document).css('font-size', $('#fontSize').val());

                if ($('#bold').is(':checked')){
                    $(pos, window.frames['previewIframe'].document).css('font-weight', 'bold');
                } else {
                    $(pos, window.frames['previewIframe'].document).css('font-weight', 'normal');
                }
                if ($('#italics').is(':checked')){
                    $(pos, window.frames['previewIframe'].document).css('font-style', 'italic');
                } else {
                    $(pos, window.frames['previewIframe'].document).css('font-style', 'normal');
                }
                if ($('#underline').is(':checked')){
                    $(pos, window.frames['previewIframe'].document).css('text-decoration', 'underline');
                } else {
                    $(pos, window.frames['previewIframe'].document).css('text-decoration', 'none');
                }

                $(pos, window.frames['previewIframe'].document).css('color', $('#color').val());
            }
        }

        $('#previewIframe').load(function(){
            joomlamailerJS.templates.logoWidthInfo();
        });
    });
}(jQuery);
