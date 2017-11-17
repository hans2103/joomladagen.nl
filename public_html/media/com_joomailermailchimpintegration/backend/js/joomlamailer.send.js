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

        joomlamailerJS.send = {
            init: function() {
                joomlamailerJS.send.segmentsTested = false;
                joomlamailerJS.send.creditCount = 0;
                joomlamailerJS.send.currentCredits = 0;

                $('#pickDeliveryTime').clockpick({
                    starthour : 0,
                    endhour : 23,
                    showminutes : true,
                    minutedivisions: 4,     // :00, :15, :30, :45
                    military: true,
                    //event: 'mouseover',
                    layout: 'horizontal',
                    valuefield: 'deliveryTime'
                });
            },
            loadCampaign: function(cid) {
                joomlamailerJS.functions.preloader();
                window.location = 'index.php?option=com_joomailermailchimpintegration&view=send&campaign=' + cid
            },
            addCondition: function() {
                if ($('#listId').val() == '') {
                    joomlamailerJS.sync.noListSelected();
                    return;
                }
                var x;
                var next = parseInt($('#conditionCount').val()) + 1;
                if (next > 10) {
                    return;
                }
                $.ajax({
                    url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=send&format=raw&task=addCondition',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        listId: $('#listId').val(),
                        conditionCount: $('#conditionCount').val()
                    },
                    beforeSend: function() {
                        for (x = 2; x < 11; x++) {
                            if ($('#segment' + x).html() == '') {
                                $('#segment' + x).css({
                                    'background': "url('" + joomlamailerJS.misc.baseUrl + "media/com_joomailermailchimpintegration/backend/images/loader_16.gif') no-repeat 10px 10px",
                                    'display': 'block',
                                    'height': '32px'
                                });
                                break;
                            }
                        }
                        //$('#segment' + next).html(joomlamailerJS.helpers.ajaxLoader).css('display', 'block');
                    },
                    success: function(response) {
                        $('#conditionCount').val(next);
                        if (next == 10) {
                            $('#addCondition').css('display', 'none');
                        }
                        $('#segment' + x).css({'background': '', 'height': ''}).html(response.html);
                        if (response.js) {
                            eval(response.js);
                            eval($('.calendar').attr('src', '../media/com_joomailermailchimpintegration/backend/images/calendar.png'));
                        }
                    }
                });
            },
            removeCondition: function(nr) {
                $('#segment' + nr).html('').css('display', 'none');

                var conditionsCount = parseInt($('#conditionCount').val()) - 1;
                $('#conditionCount').val(conditionsCount);
                if (conditionsCount < 10 ) {
                    $('#addCondition').css('display', '');
                }
            },
            addInterests: function(listId) {
                var staticOptions = 10;
                if (listId != '') {
                    $.ajax({
                        url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=send&format=raw&task=addInterests',
                        type: 'post',
                        dataType: 'json',
                        data: {listId: listId},
                        success: function(response) {
                            if (response.length > 0) {
                                for (x = 1; x <= 10; x++) {
                                    var element = $('#segmenttype' + x);
                                    if (element.html() != '') {
                                        var options = element.find('option');
                                        if (options.length > staticOptions) {
                                            for (i = options.length; i > staticOptions; i--) {
                                                options[i-1].remove();
                                            }
                                        }
                                        for (var i = 0; i < response.length; i++) {
                                            element.append($j('<option />').val(response[i].id).html(response[i].name));
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    for (x = 1; x <= 10; x++) {
                        var element = $('#segmenttype' + x);
                        if (element.html() != '') {
                            var options = element.find('option');
                            if (options.length > staticOptions) {
                                for (i = options.length; i > staticOptions; i--) {
                                    options[i-1].remove();
                                }
                            }
                        }
                    }
                }
            },
            getSegmentFields: function(selector, num) {
                $.ajax({
                    url:  joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=send&format=raw&task=getSegmentFields',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        listId: $('#listId').val(),
                        type: $('#segmenttype' + num).val(),
                        condition: $('#segmentTypeCondition_' + num).val(),
                        num: num,
                        conditionDetail: ($('#segmentTypeConditionDetail_' + num).length == 1
                            ? $('#segmentTypeConditionDetail_' + num).val() : '')
                    },
                    beforeSend: function() {
                        $('#segmentTypeConditionDiv_' + num).html($('<img>').css('padding', '7px 0 0 10px')
                            .attr('src', joomlamailerJS.misc.baseUrl + 'media/com_joomailermailchimpintegration/backend/images/loader_16.gif'));
                    },
                    success: function(response) {
                        $(selector).html(response.html);
                        if (response.js) {
                            eval(response.js);
                            eval($j('.calendar').attr('src', '../media/com_joomailermailchimpintegration/backend/images/calendar.png'));
                        }
                    }
                });
            },
            testSegments: function() {
                if ($('#listId').val() == '') {
                    joomlamailerJS.sync.noListSelected();
                    return;
                }

                $('#ajax-spin').removeClass('hidden');
                joomlamailerJS.send.segmentsTested = true;

                var data = {
                    listId: $('#listId').val(),
                    match: $('#match').val(),
                    conditionDetailValue: '',
                    type: '',
                    condition: '',
                    conditionDetail: '',
                    conditionDetailValue: ''
                };

                $.each($('.segmentType'), function() {
                    var index = $(this).data('index'),
                        ConditionTypeField = $('#segmenttype' + index).val().split(';'),
                        ConditionType = ConditionTypeField[0],
                        Field = ConditionTypeField[1];

                    data['type'] += ConditionType + ';' + Field + '|*|';
                    data['condition'] += $('#segmentTypeCondition_' + index).val() + '|*|';

                    if (ConditionType == 'Date') {
                        data['conditionDetail'] += $('#segmentTypeConditionDetail_' + index).val() + '|*|';
                        data['conditionDetailValue'] += $('#segmentTypeConditionDetailValue_' + index).val() + '|*|';
                    } else {
                        data['conditionDetail'] += '#|*|';
                        if ($('#segmentTypeConditionDetailValue_' + index).attr('multiple') !== undefined) {
                            var value = $('#segmentTypeConditionDetailValue_' + index).val()
                                ? $('#segmentTypeConditionDetailValue_' + index).val().join(',') : ''; // avoid "null" if no value selected in multiselect
                            data['conditionDetailValue'] += value + '|*|';
                        } else {
                            data['conditionDetailValue'] += $('#segmentTypeConditionDetailValue_' + index).val() + '|*|';
                        }
                    }
                });

                $j.ajax({
                    url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=send&format=raw&task=testSegments',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function(response) {
                        $('#ajax-spin').addClass('hidden');
                        $('#testResponse').html(response.msg).show();

                        if (response.error) {
                            $('#testResponse').addClass('alert alert-danger');
                        } else {
                            $('#testResponse').removeClass('alert alert-danger');
                            if ($('#test').is(':checked') === false) {
                                $('#creditCount').text(response.memberCount);
                                joomlamailerJS.send.creditCount = response.memberCount;
                                joomlamailerJS.send.currentCredits = response.memberCount;
                            }
                        }
                    },
                    error: function() {
                        $('#ajax-spin').addClass('hidden');
                    }
                });
            },
            validateEmail: function(email) {
                var pattern = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if (email != '' && !pattern.test(email)) {
                    alert(joomlamailerJS.strings.errorInvalidEmails);
                    return false;
                }

                return true;
            },
            setCredits: function(listId) {
                joomlamailerJS.send.currentCredits = listMembers[listId];
                $('#total').val(listMembers[listId]);
                if ($('#test').is(':checked') === false) {
                    $('#creditCount').text(listMembers[listId]);
                    joomlamailerJS.send.creditCount = listMembers[listId];
                } else {
                    joomlamailerJS.send.credits();
                }
            },
            credits: function() {
                var counter = 0;
                $('.testEmailField').each(function() {
                    if ($(this).val() != '') {
                        counter++;
                    }
                });
                $('#creditCount').text(counter);
            },
            rating: function(elem, store) {
                var num = elem.parent().data('num');
                var value = elem.val();

                if (store) {
                    $('#segmentTypeConditionDetailValue_' + num).val(value);
                }
                for (var i = 1; i < 6; i++) {
                    if (i <= value) {
                        $('#segmentTypeConditionDiv_' + num + ' .rating_' + i).addClass('active');
                    } else {
                        $('#segmentTypeConditionDiv_' + num + ' .rating_' + i).removeClass('active');
                    }
                }
            },
            restoreRating: function(num) {
                var rating = $('#segmentTypeConditionDetailValue_' + num).val();
                for (var i = 1; i < 6; i++) {
                    if (i <= rating) {
                        $('#segmentTypeConditionDiv_' + num + ' .rating_' + i).addClass('active');
                    } else {
                        $('#segmentTypeConditionDiv_' + num + ' .rating_' + i).removeClass('active');
                    }
                }
            }
        }

        $(document).on('mouseleave', '.memberRating', function() {
            joomlamailerJS.send.restoreRating($(this).data('num'))
        });

        $(document).on('hover', '.memberRating li', function() {
            joomlamailerJS.send.rating($(this), false);
        });
        $(document).on('click', '.memberRating li', function() {
            joomlamailerJS.send.rating($(this), true);
        });

        $('#listId').change(function() {
            joomlamailerJS.send.addInterests($(this).val());
            joomlamailerJS.send.setCredits($(this).val());
        });

        $('#test').change(function() {
            joomlamailerJS.send.setCredits($('#listId').val());
            if ($('#test').is(':checked') === true) {
                $('#testmails').slideDown();
                $('#sendTestButton').removeClass('hidden');
                $('.sendNowButton').addClass('hidden');
                $('#isTest').show();

            } else {
                $('#testmails').slideUp();
                $('#sendTestButton').addClass('hidden');
                $('.sendNowButton').removeClass('hidden');
                $('#isTest').hide();
            }
        });

        $('.testEmailField').change(function() {
            if (joomlamailerJS.send.validateEmail($(this).val()) === false) {
                $(this).val('').focus();
            }
        })
        .blur(function() {
            joomlamailerJS.send.credits();
        });

        $('#sendTestButton').click(function(e) {
            e.preventDefault();
            Joomla.submitbutton('send');
        });

        /*$('#timewarp').click(function() {
            if ($(this).is(':checked') === true) {
                if (joomlamailerJS.misc.customerPlan == 'free') {
                    alert(joomlamailerJS.strings.errorTimewarpOnlyForPayed);
                    $('#timewarp').attr('checked', false);
                } else {
                    $('#schedule').attr('checked', true);
                }
            }
        });*/

        $('#deliveryDate, #deliveryTime').change(function() {
            if ($(this).val() != '') {
                $('#schedule').attr('checked', true);
            }
        });

        $('#useSegments').change(function() {
            if ($(this).is(':checked') === true && $('#listId').val() == '') {
                joomlamailerJS.sync.noListSelected();
                $(this).attr('checked', false);
            }
        });
        $('#segmenttype1').change(function() {
            joomlamailerJS.send.getSegmentFields('#segmentTypeConditionDiv_1', 1);
        });
        $('#segmentTypeConditionDetail_1').change(function() {
            joomlamailerJS.send.getSegmentFields('#segmentTypeConditionDiv_1', 1);
        });

        $('#addCondition').click(function() {
            joomlamailerJS.send.addCondition();
        });

        $('#testSegments').click(function(e) {
            e.preventDefault();
            joomlamailerJS.send.testSegments();
        });

        joomlamailerJS.send.init();

        Joomla.submitbutton = function(pressbutton) {
            if (pressbutton == 'syncHotness') {
                if ($('#listId').val() == '') {
                    joomlamailerJS.sync.noListSelected();
                    return;
                } else if (confirm(joomlamailerJS.strings.confirmSyncHotnessNow)) {
                    joomlamailerJS.sync.AjaxAddHotness(0);
                    return;
                }
            } else if (pressbutton == 'remove') {
                if (confirm(joomlamailerJS.strings.confirmDraftDelete)) {
                    Joomla.submitform(pressbutton);
                    return true;
                } else {
                    return false;
                }
            } else {
                if ($('#listId').val() == '') {
                    joomlamailerJS.sync.noListSelected();
                    return;
                } else if ($('#test').is(':checked') == true) {
                    var testEmails = [];
                    var invalidEmails = false;
                    $('.testEmailField').each(function() {
                        if ($(this).val()) {
                            if (joomlamailerJS.send.validateEmail($(this).val()) === false) {
                                invalidEmails = true;
                                return false;
                            }

                            testEmails.push($(this).val());
                        }
                    });
                    testEmails = testEmails.join();
                    if (invalidEmails == true) {
                        return;
                    } else if (testEmails == '') {
                        alert(joomlamailerJS.strings.errorEnterTestRecipients);
                        return;
                    } else {
                        joomlamailerJS.functions.preloader();
                        Joomla.submitform(pressbutton);
                    }

                    return;
                }

                if ($('#schedule').is(':checked') == true) {
                    var patternDate = /\d{4}-\d{2}-\d{2}/;
                    var patternTime = /\d{2}:\d{2}/;

                    if (!$('#deliveryDate').val().test(patternDate) || !$('#deliveryTime').val().test(patternTime)) {
                        alert(joomlamailerJS.strings.errorInvalidDate);
                        return;
                    }

                    var today = new Date();
                    var tomorrow = new Date();
                    tomorrow.setDate(today.getDate() + 1);
                    var deliveryDate = $('#deliveryDate').val();
                    deliveryDate = deliveryDate.replace(/-/g, '/');
                    var selectedDate = new Date(deliveryDate + ' ' + $('#deliveryTime').val() + ':00');

                    if (today > selectedDate) {
                        alert(joomlamailerJS.strings.errorInvalidDeliveryDateInThePast);
                        return;
                    } /*else if ($('#timewarp').is(':checked') == true) {
                        if (joomlamailerJS.misc.customerPlan == 'free') {
                            alert(joomlamailerJS.strings.errorTimewarpOnlyForPayed);
                            return;
                        } else if (tomorrow > selectedDate) {
                            alert(joomlamailerJS.strings.errorTimewarpNotScheduled24h);
                            return;
                        }
                    }*/
                } else if ($('#timewarp').is(':checked') == true) {
                    alert(joomlamailerJS.strings.errorTimewarpNotScheduled24h);
                    return;
                }

                if ($('#useSegments').is(':checked') == true && joomlamailerJS.send.segmentsTested == false) {
                    alert(joomlamailerJS.strings.errorPleaseTestSegments);
                    return;
                }

                if ($('#campaignType').is(':checked') == true) {
                    if ($('#useSegments').is(':checked') == true ||
                        $('#schedule').is(':checked') == true ||
                        $('#timewarp').is(':checked') == true ||
                        $('#useTwitter').is(':checked') == true) {

                        alert(joomlamailerJS.strings.errorAutoresponderSetup);
                        return;
                    } else if (isNaN(parseInt($('#new-auto-offset-time').val())) || parseInt($('#new-auto-offset-time').val()) <= 0) {
                       alert(joomlamailerJS.strings.errorAutoresponderDays);
                       return
                    } else {
                        joomlamailerJS.functions.preloader();
                        Joomla.submitform(pressbutton);
                        return;
                    }
                } else {
                    if (joomlamailerJS.send.creditCount == 0) {
                        alert(joomlamailerJS.strings.errorNoRecipients);
                        return;
                    } else if (confirm(joomlamailerJS.strings.confirmSend_1 + ' ' + joomlamailerJS.send.creditCount + ' ' + joomlamailerJS.strings.confirmSend_2)){
                        joomlamailerJS.functions.preloader();
                        Joomla.submitform(pressbutton);
                        return;
                    }
                }
            }

            return;
        }
    });
}(jQuery);

