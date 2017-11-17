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
        (function(window) {
            var document = window.document;

            var joomlamailerJS = (function() {
                var $ = jQuery;

                return {
                    strings: {},
                    misc: {},
                    functions: {},
                    helpers: {}
                };
            })();

            window.joomlamailerJS = joomlamailerJS;
        })(window);

        joomlamailerJS.functions = {
            init: function() {
                if ($('#toolbar-account-settings').length > 0) {
                    var link = $('#toolbar-account-settings button').attr('onclick').replace(/^location\.href=/, 'window.open(').replace(/;$/, ');');
                    $('#toolbar-account-settings button').attr('onclick', link);
                }
            },
            preloader: function() {
                $(joomlamailerJS.functions.getPreloaderDiv()).appendTo('body');
                $('#joomlamailer_ajax_loader').fadeIn();
            },
            preloaderRemove: function() {
                $('#joomlamailer_ajax_loader').fadeOut(300, function() { $(this).remove(); });
            },
            preloaderTE: function() {
                $('#previewIframe').contents().find('body').append(joomlamailerJS.functions.getPreloaderDiv());
                $('#previewIframe').contents().find('#joomlamailer_ajax_loader').fadeIn();
            },
            preloaderTERemove: function() {
                $('#previewIframe').contents().find('#joomlamailer_ajax_loader').fadeOut(300, function() { $(this).remove(); });
            },
            getPreloaderDiv: function() {
                var div = $('<div/>', {
                    id: 'joomlamailer_ajax_loader',
                    css: {
                        'display': 'none',
                        'width': '100%',
                        'height': '100%',
                        'background': '#000',
                        'opacity': '0.2',
                        'position': 'fixed',
                        'top': '0',
                        'left': '0',
                        'z-index': '999999'
                    }
                });
                $('<div/>', {
                    css: {
                        'background': 'url(' + joomlamailerJS.misc.baseUrl + 'media/com_joomailermailchimpintegration/backend/images/loader_55.gif) no-repeat',
                        'position': 'absolute',
                        'top': '48%',
                        'left': '48%',
                        'width': '54px',
                        'height': '54px'
                    }
                }).appendTo(div);

                return div;
            },
            hideSetupInfo: function() {
                $.ajax({
                    url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=main&format=raw&task=hideSetupInfo',
                    dataType: 'json',
                    success: function(xhr) {
                        if (xhr.success == 1) {
                            $('#setupInfo').slideUp();
                        }
                    }
                });
            },
            showSetupInfo: function() {
                var url = 'index.php?option=com_joomailermailchimpintegration&controller=main&format=raw&task=showSetupInfo';
                $.ajax({
                    url: url,
                    success: function() {
                        $('#showSetupInfo').html('ok');
                    }
                });
            },
            changeTab: function() {
                $('#activeTab').val($(this).attr('href').substring(1));
            },
            changeSlider: function() {
                $('#activeArticleListSlider').val($(this).attr('href').substring(1));
            },
            initFieldsForm: function() {
                if ($('#type').val() == 'dropdown' || $('#type').val() == 'radio') {
                    $('#coreRow1').css('opacity', '1');
                    $('#coreRow2').css({
                        'display': '',
                        'opacity': '1'
                    });
                    $('#CBrow').css('opacity', '0.2');
                    $('#JSrow').css('opacity', '0.2');
                    $('#VMrow').css('opacity', '0.2');
                    if ($('#JSfield').val()) {
                        $('#JSrow').css('opacity', '1');
                    }
                } else {
                    $('#coreRow2').css('display', 'none');
                }

                $('#type').change(function() {
                    if ($(this).val() != 0) {
                        if ($('#CBrow').length > 0){ document.adminForm.CBfield.selectedIndex = 0; }
                        if ($('#JSrow').length > 0){ document.adminForm.JSfield.selectedIndex = 0; }
                        if ($('#VMrow').length > 0){ document.adminForm.VMfield.selectedIndex = 0; }
                        $('#coreRow1').css('opacity', '1');
                        $('#CBrow').css('opacity', '0.2');
                        $('#JSrow').css('opacity', '0.2');
                        $('#VMrow').css('opacity', '0.2');
                        if ($(this).val() == 'dropdown' || $(this).val() == 'radio') {
                            $('#coreRow2').css({
                                'display': '',
                                'opacity': '1'
                            });
                        } else {
                            $('#coreRow2').css('display', 'none');
                        }
                    } else {
                        $('#coreRow1').css('opacity', '');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '');
                        $('#JSrow').css('opacity', '');
                        $('#VMrow').css('opacity', '');
                    }
                });
                $('#CBfield').change(function() {
                    if ($(this).val()) {
                        document.adminForm.type.selectedIndex = 0;
                        document.adminForm.coreOptions.value = '';
                        if ($('#JSrow').length > 0){ document.adminForm.JSfield.selectedIndex = 0; }
                        if ($('#VMrow').length > 0){ document.adminForm.VMfield.selectedIndex = 0; }
                        $('#coreRow1').css('opacity', '0.2');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '');
                        $('#JSrow').css('opacity', '0.2');
                        $('#VMrow').css('opacity', '0.2');
                    } else {
                        $('#coreRow1').css('opacity', '');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '');
                        $('#JSrow').css('opacity', '');
                        $('#VMrow').css('opacity', '');
                    }
                });
                $('#JSfield').change(function() {
                    if ($(this).val()) {
                        document.adminForm.type.selectedIndex = 0;
                        if ($('#CBrow').length > 0){ document.adminForm.CBfield.selectedIndex = 0; }
                        if ($('#VMrow').length > 0){ document.adminForm.VMfield.selectedIndex = 0; }
                        $('#coreRow1').css('opacity', '0.2');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '0.2');
                        $('#JSrow').css('opacity', '');
                        $('#VMrow').css('opacity', '0.2');
                    } else {
                        $('#coreRow1').css('opacity', '');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '');
                        $('#JSrow').css('opacity', '');
                        $('#VMrow').css('opacity', '');
                    }
                });
                $('#VMfield').change(function() {
                    if ($(this).val()) {
                        document.adminForm.type.selectedIndex = 0;
                        if ($('#CBrow').length > 0){ document.adminForm.CBfield.selectedIndex = 0; }
                        if ($('#JSrow').length > 0){ document.adminForm.JSfield.selectedIndex = 0; }
                        $('#coreRow1').css('opacity', '0.2');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '0.2');
                        $('#JSrow').css('opacity', '0.2');
                        $('#VMrow').css('opacity', '');
                    } else {
                        $('#coreRow1').css('opacity', '');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '');
                        $('#JSrow').css('opacity', '');
                        $('#VMrow').css('opacity', '');
                    }
                });
            },
            initGroupsForm: function() {
                $('#coreType').change(function() {
                    if ($(this).val()) {
                        $('#coreRow1').css('opacity', '');
                        $('#coreRow2').css({
                            'display': '',
                            'opacity': '1'
                        });
                        if ($('#CBrow').length > 0){ document.adminForm.CBfield.selectedIndex = 0; }
                        if ($('#JSrow').length > 0){ document.adminForm.JSfield.selectedIndex = 0; }
                        if ($('#VMrow').length > 0){ document.adminForm.VMfield.selectedIndex = 0; }
                        $('#CBrow').css('opacity', '0.2');
                        $('#JSrow').css('opacity', '0.2');
                        $('#VMrow').css('opacity', '0.2');
                    } else {
                        $('#coreRow1').css('opacity', '');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '');
                        $('#JSrow').css('opacity', '');
                        $('#VMrow').css('opacity', '');
                    }
                });
                $('#CBfield').change(function() {
                    if ($(this).val()) {
                        document.adminForm.coreType.selectedIndex = 0;
                        document.adminForm.coreOptions.value = '';
                        if ($('#JSrow').length > 0){ document.adminForm.JSfield.selectedIndex = 0; }
                        if ($('#VMrow').length > 0){ document.adminForm.VMfield.selectedIndex = 0; }
                        $('#coreRow1').css('opacity', '0.2');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '');
                        $('#JSrow').css('opacity', '0.2');
                        $('#VMrow').css('opacity', '0.2');
                    } else {
                        $('#coreRow1').css('opacity', '');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '');
                        $('#JSrow').css('opacity', '');
                        $('#VMrow').css('opacity', '');
                    }
                });
                $('#JSfield').change(function() {
                    if ($(this).val()) {
                        document.adminForm.coreType.selectedIndex = 0;
                        document.adminForm.coreOptions.value = '';
                        if ($('#CBrow').length > 0){ document.adminForm.CBfield.selectedIndex = 0; }
                        if ($('#VMrow').length > 0){ document.adminForm.VMfield.selectedIndex = 0; }
                        $('#coreRow1').css('opacity', '0.2');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '0.2');
                        $('#JSrow').css('opacity', '');
                        $('#VMrow').css('opacity', '0.2');
                    } else {
                        $('#coreRow1').css('opacity', '');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '');
                        $('#JSrow').css('opacity', '');
                        $('#VMrow').css('opacity', '');
                    }
                });
                $('#VMfield').change(function() {
                    if ($(this).val()) {
                        document.adminForm.coreType.selectedIndex = 0;
                        document.adminForm.coreOptions.value = '';
                        if ($('#CBrow').length > 0){ document.adminForm.CBfield.selectedIndex = 0;}
                        if ($('#JSrow').length > 0){ document.adminForm.JSfield.selectedIndex = 0;}
                        $('#coreRow1').css('opacity', '0.2');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '0.2');
                        $('#JSrow').css('opacity', '0.2');
                        $('#VMrow').css('opacity', '');
                    } else {
                        $('#coreRow1').css('opacity', '');
                        $('#coreRow2').css('display', 'none');
                        $('#CBrow').css('opacity', '');
                        $('#JSrow').css('opacity', '');
                        $('#VMrow').css('opacity', '');
                    }
                });
            },
            clearReportsCache: function() {
                $.ajax({
                    url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=campaigns&format=raw&task=clearReportsCache',
                    beforeSend: function() {
                        $('#cacheLoader').css('visibility', 'visible');
                    },
                    success: function() {
                        $('#cacheLoader').css('visibility', 'hidden');
                        joomlamailerJS.functions.preloader();
                        location.reload(true);
                    }
                });
            },
            validateNoSpecials: function(string) {
                return /^[0-9A-Za-z\s]+$/.test(string);
            },
            replaceSpecials: function(string) {
                string = string.replace(/ /g, '_')
                    .replace(/Ä/g, 'Ae')
                    .replace(/ä/g, 'ae')
                    .replace(/Ö/g, 'Oe')
                    .replace(/ö/g, 'oe')
                    .replace(/Ü/g, 'Ue')
                    .replace(/ü/g, 'ue')
                    .replace(/ß/g, 'ss');
                return string;
            },
            deselect: function (){
                $('#' + $(this).data('field') + ' option:selected').prop('selected', false);
            }
        }

        joomlamailerJS.functions.init();
        $(document).on('click', '#hideSetupInfo', joomlamailerJS.functions.hideSetupInfo);
        $(document).on('click', '#create_campaignTabs a', joomlamailerJS.functions.changeTab);
        $(document).on('click', '#article_lists_sliders .accordion-heading a', joomlamailerJS.functions.changeSlider);
        $(document).on('click', '.deselect', joomlamailerJS.functions.deselect);
    });
}(window.jQuery);
