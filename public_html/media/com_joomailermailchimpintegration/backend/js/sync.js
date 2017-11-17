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

function AjaxAddSugar( run, range, offset, done, newContacts, updatedContacts, finished, errors, errorMsg){
    if( range == 'selection' && document.getElementById('boxchecked').value == 0 ) {
        noUsersSelected();
        $j('#ajax_response').css('display', 'none');
        $j('#ajax_response').html('');
        return;
    } else {

        if( !run )      { var run        =  1; }
        if( !offset )   { var offset     = -1; }
        if( !done )     { var done       =  0; }
        if( !newContacts )      { var newContacts        = 0; }
        if( !updatedContacts )  { var updatedContacts    = 0; }
        if( !finished  ){ var finished   = 0; }
        if( !errors    ){ var errors     = 0; }
        if( !errorMsg  ){ var errorMsg   = false; }

        var data = new Object();
        data["range"]  = range;

        data["step"]   = 25;
        data["done"]   = done;
        data["new"]    = newContacts;
        data["updated"]= updatedContacts;
        data["errors"] = errors;
        data["errorMsg"] = errorMsg;

        if( range == 'selection' ){
            var form = document.getElementById('adminForm');
            data["cid"] = new Array();
            var x = 0;
            var z = 0;
            for(i=0; i<form.elements.length; i++)
            {
                if(form.elements[i].type=='checkbox' && form.elements[i].checked==true && form.elements[i].name!='toggle'){
                    if( x <= (run*data["step"]) ){
                        data["cid"][z] = form.elements[i].value;
                        z++;
                    }

                    x++;
                }
            }
            data["total"]  = x;
        } else {
            data["total"]  = document.getElementById('total').value;
        }

        if(done==0) { joomlamailerJS.sync.AJAXinit( data["total"] ); }
        else if ( finished ) { setTimeout("$j('#ajax_response').css('display', 'none'); $j('#ajax_response').html('');",1000); }

        if( data["total"] > 100 && offset == -1 ) { offset = 0; }
        data["offset"]  = offset;

        if( (done+errors) < data["total"] && !finished ) {

            var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=ajax_sync_sugar';
            doAjaxTask(url, data, function(postback){
                if(postback.abortAJAX==0){
                    jQuery('#ajax_response').html(postback.msg);
                    if( !postback.finished){
                        AjaxAddSugar( run, range, offset+25, postback.done, postback.newContacts, postback.updated, postback.finished, postback.errors, postback.errorMsg);
                    } else { joomlamailerJS.sync.AJAXsuccess(postback.finalMessage);
                        if( postback.fatalError ){ $('system-message-inner').addClass('error'); } else { $('system-message-inner').removeClass('error'); }
                        setTimeout("$j('#ajax_response').css('display', 'none'); $j('#ajax_response').html('');",1000);
                    }
                }
            });
        } else {
            return;
        }
    }
}


function AjaxAddHighrise( run, range, offset, done, newContacts, updatedContacts, finished, errors, errorMsg){

    if( range == 'selection' && document.getElementById('boxchecked').value == 0 ) {
        noUsersSelected();
        $j('#ajax_response').css('display', 'none');
        $j('#ajax_response').html('');
        return;
    } else {

        if( !run )		{ var run        =  1; }
        if( !offset )		{ var offset     = -1; }
        if( !done )		{ var done       =  0; }
        if( !newContacts )      { var newContacts     = 0; }
        if( !updatedContacts )  { var updatedContacts = 0; }
        if( !finished  )	{ var finished   = 0; }
        if( !errors    )	{ var errors     = 0; }
        if( !errorMsg  )	{ var errorMsg   = false; }

        var data = new Object();
        data["range"]  = range;

        data["step"]   = 1;
        data["done"]   = done;
        data["new"]    = newContacts;
        data["updated"]= updatedContacts;
        data["errors"] = errors;
        data["errorMsg"] = errorMsg;

        if( range == 'selection' ){
            var form = document.getElementById('adminForm');
            data["cid"] = new Array();
            var x = 0;
            var z = 0;
            for(i=0; i<form.elements.length; i++)
            {
                if(form.elements[i].type=='checkbox' && form.elements[i].checked==true && form.elements[i].name!='toggle'){
                    if( x == (run-1) ){
                        data["cid"][z] = form.elements[i].value;
                        z++;
                    }
                    x++;
                }
            }
            data["total"]  = x;
        } else {
            data["total"]  = document.getElementById('total').value;
        }

        if(done==0) { joomlamailerJS.sync.AJAXinit( data["total"] ); }
        else if ( finished ) { setTimeout("$j('#ajax_response').css('display', 'none'); $j('#ajax_response').html('');",1000); }

        if( data["total"] > 100 && offset == -1 ) { offset = 0; }
        data["offset"]  = offset;

        if( (done+errors) < data["total"] && !finished ) {

            var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=sync_highrise';
            doAjaxTask(url, data, function(postback){
                if(postback.abortAJAX==0){
                    jQuery('#ajax_response').html(postback.msg);
                    if( !postback.finished){
                        run++;
                        AjaxAddHighrise( run, range, offset+data["step"], postback.done, postback.newContacts, postback.updated, postback.finished, postback.errors, postback.errorMsg);
                    } else { joomlamailerJS.sync.AJAXsuccess(postback.finalMessage);
                        if( postback.fatalError ){ $('system-message-inner').addClass('error'); } else { $('system-message-inner').removeClass('error'); }
                        setTimeout("$j('#ajax_response').css('display', 'none'); $j('#ajax_response').html('');",1000);
                    }
                }
            });
        } else {
            return;
        }
    }
}


function AjaxAddLeads( y, done, finished, addedUsers, errors, errorMsg, failed ){

    if( !y )		{ y          = 0; }
    if( !done )		{ done       = 0; }
    if( !finished  ){ finished   = 0; }
    if( !addedUsers){ addedUsers = ''; }
    if( !errors    ){ errors     = 0; }
    if( !errorMsg  ){ errorMsg   = false; }
    if( !failed)	{ failed = ''; }

    var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=ajax_sync_leads';
    var form = document.getElementById('adminForm');
    var step = 50;
    //	console.log(data);

    var data = new Object();
    data["listid"] = document.getElementById('listid').value;
    data["done"]   = done;
    data["addedUsers"] = addedUsers;
    data["errors"] = errors;
    data["errorMsg"] = errorMsg;

    data["cid"] = new Array();
    x = 0;
    y += step;
    z = 0;
    for(i=0; i<form.elements.length; i++)
    {
        if(form.elements[i].type=='checkbox' && form.elements[i].checked==true && form.elements[i].name!='toggle'){
            if( x>=(y-step) && x<y ){

                data["cid"][z] = form.elements[i].value;
                z++;
            }
            x++;
        }
    }
    data["total"]  = x;

    if (document.adminForm.listid.value == ""){
        noListSelected();
    } else if( x==0 ) {
        noUsersSelected();
    } else {
        if( y == step ) {	joomlamailerJS.sync.AJAXinit( document.getElementById('boxchecked').value ); }
        doAjaxTask(url, data, function(postback){ jQuery('#ajax_response').html(postback.msg);
            if( !postback.finished){
                AjaxAddLeads( y, postback.done, postback.finished, postback.addedUsers, postback.errors, postback.errorMsg, postback.failed);
            } else {
                joomlamailerJS.sync.AJAXsuccess(postback.finalMessage);
                setTimeout("$j('#ajax_response').css('display', 'none'); $j('#ajax_response').html('');",1000);
                joomlamailerJS.sync.highlightRows(postback.addedUsers);
            }
        });
    }
}

