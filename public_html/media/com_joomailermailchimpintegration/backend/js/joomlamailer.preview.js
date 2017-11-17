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

!function($) {
    $(document).ready(function() {

        joomlamailerJS.preview = {
            get: function() {
                var form = $('#adminForm');

	            var data = {
                    'campaignName': encodeURI($('#campaign_name').val()),
                    'subject': encodeURI($('#subject').val()),
                    'from_name': encodeURI($('#from_name').val()),
                    'from_email': encodeURI($('#from_email').val()),
                    'reply_email': encodeURI($('#reply_email').val()),
                    'confirmation_email': encodeURI($('#confirmation_email').val())
                };

                data['text_only'] = ($('#text_only').is(':checked') ? 1 : 0);

	            if (data['text_only']) {
	                data['text_only_content'] = encodeURI($('#text_only_content').val());
	            } else {
	                data['template'] = encodeURI($('#template').val());
	                data['intro'] = encodeURI(joomlamailerJS.create.getIntroContent());

	                var postDataEval = new Object();
                    $.each(postData, function(index, value) {
                        postDataEval[index] = encodeURI(eval(value));
                    });
	                data['postData'] = postDataEval;

	                data['sidebarElements'] = new Object();
                    $.each(sidebarElements, function(index, value) {
                        data['sidebarElements'][value] = value;
                    });

	                data['socialIcons'] = new Object();
                    $.each(socialIcons, function(index, value) {
                        data['socialIcons'][value] = $('#' + value).val();
                    });

	                data['includeComponents'] = includeComponents;
                    data['includeComponentsFields'] = includeComponentsFields;
	                data['includeComponentsOptions'] = new Object();
	                data['componentsPostData'] = new Object();

                    $.each(includeComponents, function(index1, component) {
                        if (includeComponentsOptions[component] !== undefined) {
                            $.each(includeComponentsOptions[component], function(index2, cOption) {
                                if (form.find('#' + cOption)) {
                                    if (form.find('#' + cOption).attr('type') == 'checkbox') {
                                        if (form.find('#' + cOption).is(':checked') == true) {
                                            data['includeComponentsOptions'][cOption] = 1;
                                        } else {
                                            data['includeComponentsOptions'][cOption] = 0;
                                        }
                                    } else {
                                        data['includeComponentsOptions'][cOption] = new Object();
                                        if (form.find('#' + cOption).length == 1) {
                                            data['includeComponentsOptions'][cOption] = form.find('#' + cOption).val();
                                        } else if (form.find('#' + cOption).length > 1) {
                                            $.each(form.find('#' + cOption + ' :selected'), function(index3) {
                                                data['includeComponentsOptions'][cOption][index3] = $(this).val();
                                            });
                                        }
                                    }
                                }
                            });
                        }

                        data['componentsPostData'][component] = new Object();
                        if (form.find('[name=' + component + '\\[\\]]').length > 0) {
                            $.each(form.find('[name=' + component + '\\[\\]]'), function() {
                                if ($(this).is(':checked') == true) {
                                    var thisId = $(this).val();
                                    var objectIndex = Object.keys(data['componentsPostData'][component]).length;
                                    data['componentsPostData'][component][objectIndex] = new Object();
                                    data['componentsPostData'][component][objectIndex]['itemId'] = thisId;
                                    if (includeComponentsFields[component] !== undefined && Object.keys(includeComponentsFields[component]).length > 0) {
                                        $.each(includeComponentsFields[component], function(index2, field) {
                                            if (form.find('[name=' + field + thisId + ']').length == 1) {
                                                if (form.find('[name=' + field + thisId + ']').is(':checked') == true) {
                                                    data['componentsPostData'][component][objectIndex][field + thisId] =
                                                        form.find('[name=' + field + thisId + ']').val();
                                                } else {
                                                    data['componentsPostData'][component][objectIndex][field + thisId] = 0;
                                                }
                                            } else if (form.find('[name=' + field + thisId + ']').length > 1) {
                                                $.each(form.find('[name=' + field + thisId + ']'), function() {
                                                    if ($(this).is(':checked') == true) {
                                                        data['componentsPostData'][component][objectIndex][field + thisId] =
                                                            $(this).val();
                                                    }
                                                });
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    });

	                if ($('#populararticles').is(':checked') == true) {
		                data['popular'] = 1;
		                data['populararticlesAmount'] = $('#populararticlesAmount').val();

		                // exclude
                        data['popEx'] = [];
                        $.each($('#popExclude option:selected'), function() {
                            data['popEx'].push($(this).val());
                        });

		                // include
                        data['popIn'] = [];
                        $.each($('#popInclude option:selected'), function() {
                            data['popIn'].push($(this).val());
                        });

	                } else {
		                data['popular'] = 0;
		                data['populararticlesAmount'] = 0;
		                data['popEx'] = [];
		                data['popIn'] = [];
	                }

                    data['k2_installed'] = ($('#k2_installed').val() == 1) ? 1 : 0;

	                if (data['k2_installed'] == 1) {
		                if ($('#populark2').is(':checked') == true) {
			                data['populark2'] = 1;

			                // exclude
                            data['popk2Ex'] = [];
                            $.each($('#popk2Exclude option:selected'), function() {
                                data['popk2Ex'].push($(this).val());
                            });

			                // include
                            data['popk2In'] = [];
                            $.each($('#popk2Include option:selected'), function() {
                                data['popk2In'].push($(this).val());
                            });
		                } else {
			                data['populark2'] = 0;
			                data['popk2Ex'] = [];
			                data['popk2In'] = [];
		                }

                        data['populark2_only'] = ($('#populark2_only').is(':checked') == true) ? 1 : 0;
	                }
	            }

	            if ($('#gaEnabled').is(':checked') == true) {
		            data['gaEnabled'] = 1;
		            data['gaSource'] = $('#gaSource').val();
		            data['gaMedium'] = $('#gaMedium').val();
		            data['gaName']   = $('#gaName').val();
		            data['gaExcluded'] = encodeURI($('#gaExcluded').val());

	            } else {
		            data['gaEnabled'] = 0;
	            }

                $.ajax({
                    url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=create&task=preview&format=raw',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(postback) {
                        if (postback.msg) {
                            $('#preview').html(postback.msg + postback.html);
                        } else {
                            $('#preview').html(postback.html);
                        }
                        if (postback.js) {
                              eval(postback.js);
                        }
                        $('#ajax-spin').addClass('hidden');
                        $('#preview').css({'opacity': ''});
                    }
                });
            }
        }
    });
}(jQuery);
