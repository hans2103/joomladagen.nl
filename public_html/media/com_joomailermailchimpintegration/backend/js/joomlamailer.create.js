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
    $(document).ready(function() {

        joomlamailerJS.create = {
            init: function() {

                // initialise sortable tables
                $('table.sortable').each(function() {
                    var id = $(this).attr('id');
                    var tableDnD = new TableDnD();
                    tableDnD.init($(this)[0]);
                    tableDnD.onDrop = function(table, droppedRow) {
                        var order = '';
                        $('#adminForm input[name=' + id + '\\[\\]]').each(function() {
                            order += $(this).val() + ";";
                        });
                        $('#' + id +  'Order').val(order);
                    }
                });

            },
            insertMergeTag: function(value, editorId) {
                if ($('#editorType').val() === 'tinymce') {
                    tinyMCE.get(editorId).execCommand('mceInsertContent', false, value);
                } else {
                    jInsertEditorText(value, editorId);
                }
            },
            preview: function() {
                if ($('#campaign_name').val() == '') {
                    alert(joomlamailerJS.strings.errorCampaignName);
                } else if (!joomlamailerJS.functions.validateNoSpecials($('#campaign_name').val())) {
                    alert(joomlamailerJS.strings.errorCampaignNameSpecialChars);
                } else if ($('#subject').val() == '') {
                    alert(joomlamailerJS.strings.errorBlankSubject);
                } else if (/\*\||\|\*/.test($('#subject').val())) {
                    alert(joomlamailerJS.strings.errorSubjectNoMergeTags);
                } else if ($('#template').val() == '') {
                    alert(joomlamailerJS.strings.errorSelectTemplate);
                } else {
                    $('#ajax-spin').removeClass('hidden');
                    $('#preview').css('opacity', '0.3');
                    window.location = '#preview';

                    joomlamailerJS.create.onSubmit();
                    joomlamailerJS.preview.get();
                }
            }
        };

        $('#campaign_name').blur(function() {
            if ($('#gaName') && $('#gaName').val() == '') {
                $('#gaName').val(joomlamailerJS.functions.replaceSpecials($('#campaign_name').val()));
            }
        });

        $('#from_name').keyup(function() {
            var value = $(this).val();
            value = value.replace(/\@/g, '(at)').replace(/"/g, '');
            $(this).val(value);
        });

        $('#text_only').on('change', function(){
            if ($('#text_only').is(':checked')) {
                $('#html_container').css('display', 'none');
                $('#text_only_container').css('display', '');
                $('#create_campaignTabs').find('a[href="#create_sidebar"]').parent().css({display: 'none'});
            } else {
                $('#html_container').css('display', '');
                $('#text_only_container').css('display', 'none');
                $('#create_campaignTabs').find('a[href="#create_sidebar"]').parent().css({display: ''});
            }
        });

        $('.insertMergeTag').change(function() {
            joomlamailerJS.create.insertMergeTag($(this).val(), $(this).data('editor'));
            $(this).selectedIndex = 0;
        });

        $('#populararticles, #populark2').change(function() {
            var container = $(this).data('container');
            if ($(this).is(':checked') === true) {
                $('#' + container).slideDown();
            } else {
                $('#' + container).slideUp();
            }
        });

        $('#previewButton').click(function() {
            joomlamailerJS.create.preview();
        });

        $('#saveButton').click(function() {
            Joomla.submitbutton('save')
        });

        Joomla.submitbutton = function(pressbutton) {
            if ($('#campaign_name').val() == '') {
                alert(joomlamailerJS.strings.errorCampaignName);
            } else if (!joomlamailerJS.functions.validateNoSpecials($('#campaign_name').val())) {
                alert(joomlamailerJS.strings.errorCampaignNameSpecialChars);
            } else if ($('#subject').val() == '') {
                alert(joomlamailerJS.strings.errorBlankSubject);
            } else if (/\*\||\|\*/.test($('#subject').val())) {
                alert(joomlamailerJS.strings.errorSubjectNoMergeTags);
            } else if ($('#from_name').val() == '') {
                alert(joomlamailerJS.strings.errorBlankFromName);
            } else if ($('#from_email').val() == '') {
                alert(joomlamailerJS.strings.errorBlankFromEmail);
            } else if ($('#reply_email').val() == '') {
                alert(joomlamailerJS.strings.errorBlankReplyEmail);
            } else if ($('#confirmation_email').val() == '') {
                alert(joomlamailerJS.strings.errorBlankConfirmationEmail);
            } else if ($('#template').val() == '') {
                alert(joomlamailerJS.strings.errorSelectTemplate);
            } else {
                joomlamailerJS.functions.preloader();
                joomlamailerJS.create.onSubmit();
                Joomla.submitform(pressbutton);
            }

            return;
        };

        joomlamailerJS.create.init();
    });
}(jQuery);
