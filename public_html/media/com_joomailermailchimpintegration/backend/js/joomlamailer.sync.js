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

        joomlamailerJS.sync = {
            highlightUsers: function (listId) {
                if (listId == '') {
                    return;
                }
                $.ajax({
                    url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=sync&format=raw&task=getListSubscribers',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'listId': listId
                    },
                    beforeSend: function() {
                        $('#addUsersLoader').css('visibility', 'visible');
                    },
                    success: function (response) {
                        $('#addUsersLoader').css('visibility', '');

                        if ($('table.adminlist tbody tr').length > 0) {
                            $('table.adminlist tbody tr').each(function() {
                                $(this).css('color', '');
                            });
                            $('table.adminlist tbody tr a').each(function() {
                                $(this).css('color', '');
                            });

                            joomlamailerJS.sync.highlightRows(response);
                        }

                        joomlamailerJS.sync.getTotal(listId);
                    }
                });
            },
            highlightRows: function(uids) {
                for (var i = 0; i < uids.length; i++) {
                    $('#row_' + uids[i].userid).css('color', '#009F07');
                    $('#link_' + uids[i].userid).css('color', '#009F07');
                }
            },
            getTotal: function (listId) {
                $('#addUsersLoader').css('visibility', '');

                $.ajax({
                    url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=sync&format=raw&task=getTotal',
                    type: 'POST',
                    data: {
                        'listId': listId
                    },
                    beforeSend: function() {
                        $('#addUsersLoader').css('visibility', '');
                    },
                    success: function (response) {
                        $('#total').val(response);
                        $('#addUsersLoader').css('visibility', 'hidden');
                    }
                });
            },
            selectPopup: function (system) {
                if (system == 'sugar') {
                    var addSelectionFunction = "AjaxAddSugar(1,\'selection\',0)";
                    var addAllFunction = "AjaxAddSugar(1,\'all\',0)";
                } else if (system == 'highrise') {
                    var addSelectionFunction = "AjaxAddHighrise(1,\'selection\',0)";
                    var addAllFunction = "AjaxAddHighrise(1,\'all\',0)";
                } else if (system == 'mailchimp') {
                    var addSelectionFunction = "joomlamailerJS.sync.addToMailchimp('selection')";
                    var addAllFunction = "joomlamailerJS.sync.addToMailchimp('all')";
                }
                var progressBar = '<div id="bg"></div>'
                +'<div style="background:#FFFFFF none repeat scroll 0 0;border:10px solid #000000;height:100px;position:relative;text-align:center;top:37%;width:300px;margin: auto;">'

                +'<div style="margin: 13px 10px 0 10px;">'
                +'<a class="btn btn-success btn-block" href="javascript:' + addSelectionFunction + '"><span style="font-size:1.3em;">' + joomlamailerJS.strings.addSelectedUsers + '</span></a>'
                +'<br />'
                +'<a class="btn btn-warning btn-block" href="javascript:' + addAllFunction + '"><span style="font-size:1.3em;">' + joomlamailerJS.strings.addAllUsers + '</span></a>'
                +'</div>'

                +'<a id="sbox-btn-close" style="text-indent:-5000px;right:-20px;top:-18px;outline:none;" href="javascript:joomlamailerJS.sync.closePopup();">abort</a>'
                +'</div>';

                $('#ajax_response').html(progressBar);
                $('#ajax_response').css('display', 'block');
            },
            closePopup: function () {
                $('#ajax_response').css('display', 'none');
                $('#ajax_response').html('');
            },
            AJAXinit: function (total) {
                var progressBar = '<div id="bg"></div>' +
                    '<div id="progressBarContainer">' +
                        '<div id="progressBarTitle">' + joomlamailerJS.strings.addingUsers + ' (0/'+total+' ' + joomlamailerJS.strings.done + ')</div>' +
                        '<div id="progressBarBg">' +
                            '<div id="progressBarCompleted" style="width: 0%;"></div>' +
                            '<div id="progressBarNumber">0 %</div>' +
                        '</div>' +
                        '<a id="sbox-btn-close" href="javascript:joomlamailerJS.sync.abortAJAX();">abort</a>' +
                    '</div>';

                $('#ajax_response').html(progressBar);
                $('#ajax_response').css('display', 'block');
            },
            AJAXsuccess: function (message) {
                var messageBlock = '<div id="system-message-container">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    '<div class="alert alert-success">' +
                    '<h4 class="alert-heading">Message</h4>' +
                    '<p>' + message + '</p>' +
                    '</div></div>';
                $('#message').html(messageBlock);
                $('#message').slideDown();
            },
            noListSelected: function() {
                $('#listId').css('border', '1px solid #ff0000');
                alert(joomlamailerJS.strings.pleaseSelectAList);
            },
            noUsersSelected: function() {
                alert(joomlamailerJS.strings.noUsersSelected);
            },
            addToMailchimp: function (range) {
                if (range == 'selection'){
                    if ($('#boxchecked').val() == 0) {
                        alert(joomlamailerJS.strings.noUsersSelected);
                    } else {
                        Joomla.submitbutton('sync')
                    }
                } else {
                    if (confirm(joomlamailerJS.strings.addAllUsersConfirm)) {
                        if (document.adminForm.total.value == 0){
                            alert(joomlamailerJS.strings.usersAlreadyAdded);
                        } else {
                            joomlamailerJS.sync.AJAXAddAll();
                        }
                    }
                }
            },
            AJAXAddAll: function (step, processed, finished, errors, errorMsg) {
                if ($('#listId').val() == ''){
                    joomlamailerJS.sync.noListSelected();
                    return;
                }
                if (!step )      { step       = 1; }
                if (!processed ) { processed  = 0; }
                if (!finished  ) { finished   = 0; }
                if (!errors    ) { errors     = 0; }
                if (!errorMsg  ) { errorMsg   = []; }

                var data = {
                    listId: $('#listId').val(),
                    total: $('#total').val(),
                    step: step,
                    processed: processed,
                    errors: errors,
                    errorMsg: errorMsg
                };

                if (step == 1) {
                    joomlamailerJS.sync.AJAXinit(data['total']);
                } else if (finished) {
                    if ($('#ajax_response').length > 0) {
                        setTimeout("jQuery('#ajax_response').css('display', 'none').html('');", 1000);
                    }
                }

                if ((processed + errors) < data['total'] && !finished) {
                    $.ajax({
                        url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=sync&format=raw&task=ajaxSyncAll',
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function(response) {
                            if (response.abortAJAX == 0) {
                                jQuery('#ajax_response').html(response.msg);
                                if (response.finished == 1) {
                                    joomlamailerJS.sync.AJAXsuccess(response.finalMessage);
                                    if (jQuery('#ajax_response').length > 0) {
                                        setTimeout("jQuery('#ajax_response').css('display', 'none').html('');", 1000);
                                    }
                                } else {
                                    joomlamailerJS.sync.AJAXAddAll(++step, response.processed, response.finished, response.errors, response.errorMsg);
                                }
                            }
                        }
                    });
                } else {
                    return;
                }
            },
            abortAJAX: function (noRefresh) {
                $.ajax({
                    url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=sync&format=raw&task=abortAJAX',
                    success: function() {
                        jQuery('#ajax_response').css('display', 'none').html('');

                        if (noRefresh != true) {
                            window.location.reload();
                        }
                    }
                });
            },
            AjaxAddHotness: function (offset, done, finished, errors, errorMsg, addedUsers, failed) {
                if ($('#listId').val() == '') {
                    joomlamailerJS.sync.noListSelected();
                    return;
                }

                if (!offset)     { offset     = 0; }
                if (!done)       { done       = 0; }
                if (!finished)   { finished   = 0; }
                if (!errors)     { errors     = 0; }
                if (!errorMsg)   { errorMsg   = false; }
                if (!addedUsers) { addedUsers = ''; }
                if (!failed)     { failed     = ''; }

                var data = new Object();
                data["listId"] = $('#listId').val();
                data["total"]  = $('#total').val();
                data["step"]   = 500;
                data["done"]   = done;
                data["errors"] = errors;
                data["errorMsg"] = errorMsg;
                data["addedUsers"] = addedUsers;
                data["failed"]     = failed;

                if (done == 0) {
                    joomlamailerJS.sync.AJAXinit(data["total"]);
                } else if (finished) {
                    setTimeout("jQuery('#ajax_response').css('display', 'none').html('');", 1000);
                }

                //        if( data["total"] > 100 && offset == -1 ) { offset = 0; }
                data["offset"] = offset;

                if ((done + errors) < data['total'] && !finished) {
                    $.ajax({
                        url: joomlamailerJS.misc.adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=send&format=raw&task=ajax_sync_hotness',
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function(response) {
                            if (response.abortAJAX == 0) {
                                $('#ajax_response').html(response.msg);
                                if (response.finished == 1) {
                                    if (response.failed != 1) {
                                        joomlamailerJS.send.addInterests(data["listId"]);
                                    }
                                    joomlamailerJS.sync.AJAXsuccess(response.finalMessage);
                                    setTimeout("jQuery('#ajax_response').css('display', 'none').html('');", 1000);
                                } else {
                                    joomlamailerJS.sync.AjaxAddHotness(offset + 1, response.done, response.finished, response.errors, response.errorMsg, response.addedUsers, response.failed);
                                }
                            }
                        }
                    });
                } else {
                    return false;
                }
            }
        }

        if ($('#controller').val() == 'sync') {
            if ($('#listId').val() != '') {
                joomlamailerJS.sync.highlightUsers($('#listId').val());
            }

            $('#listId').on('change', function() {
                $(this).css('border', '');
                joomlamailerJS.sync.highlightUsers($(this).val());
            });

            Joomla.submitbutton = function(pressbutton) {
                if (pressbutton == 'sync_sugar') {
                    joomlamailerJS.sync.selectPopup('sugar');
                } else if (pressbutton == 'sync_highrise') {
                    joomlamailerJS.sync.selectPopup('highrise');
                } else if (pressbutton != 'sugar' && pressbutton != 'highrise' && $('#listId').val() == '') {
                    joomlamailerJS.sync.noListSelected();
                } else if (pressbutton == 'mailchimp') {
                    joomlamailerJS.sync.selectPopup('mailchimp');
                } else if (pressbutton == 'sync_all') {
                    if (confirm(joomlamailerJS.strings.addAllUsersConfirm)) {
                        if ($('#total').val() == 0) {
                            alert(joomlamailerJS.strings.usersAlreadyAdded);
                        } else {
                            joomlamailerJS.sync.AJAXAddAll();
                        }
                    }
                } else {
                    Joomla.submitform(pressbutton);
                }
            }
        }
    });
}(jQuery);
