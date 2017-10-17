	techjoomla.jQuery( document ).ready(function() {
	techjoomla.jQuery('#appendedInputButton').bind("keypress", function(e) {
	  if (e.keyCode == 13)
		{
			return false;
		}
	});
	});

var jLikeVal = [];
var jLike = {
	jQuery : window.jQuery,
	extend : function(obj) {
		this.jQuery.extend(this, obj);
	}
}
jLike.extend({
	like : function(e,likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb) {
		jLikeVal[likecontainerid]['likeaction']='unlike';

		if(show_dislike==1)
			jLikeVal[likecontainerid]['dislikeaction']='dislike';
		else
			jLikeVal[likecontainerid]['dislikeaction']='';

		var content_id=this.call('like',likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb);
		if(content_id){
			jLike.jQuery("#"+likecontainerid+" #annotationform #content_id").val(content_id);
			jLike.jQuery("#"+likecontainerid+" #jlike_list_form #content_id").val(content_id);
			if(display_pwltcb=='1')
			{
				var udetails=getUserDetails(likecontainerid);
				if(udetails)
				{
					var ua="<li class='pwltcb_li'><a title='"+udetails.uname+"' target='_blank' ";
						if(udetails.link_url)
						{
								ua += " herf='"+udetails.link_url+"'";
						}
						ua+=">";
						ua +="<img class='pwltcb_img' src='"+udetails.img_url+"' alt=''></a></li>";
						jLike.jQuery("#"+likecontainerid+" .pwltcb .pwltcb_ul").append(ua);
				}
			}
		}
	},
	dislike : function(e,likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb) {
		jLikeVal[likecontainerid]['likeaction']='like';
		jLikeVal[likecontainerid]['dislikeaction']='undislike';
		var content_id=this.call('dislike',likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb);
		if(content_id){
			if(display_pwltcb=='1'){
			var udetails=getUserDetails(likecontainerid);
			if(udetails)
			 jLike.jQuery("#"+likecontainerid+" a[title='"+udetails.uname+"']").closest('li').remove();
			}
		}

	},
	unlike : function(e,likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb) {

		jLikeVal[likecontainerid]['likeaction']='like';

		if(show_dislike==1)
			jLikeVal[likecontainerid]['dislikeaction']='dislike';
		else
			jLikeVal[likecontainerid]['dislikeaction']='';

		var content_id=this.call('unlike',likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb);
		if(content_id){
			if(display_pwltcb=='1'){
			var udetails=getUserDetails(likecontainerid);
			if(udetails)
			 jLike.jQuery("#"+likecontainerid+" a[title='"+udetails.uname+"']").closest('li').remove();
			}
		}
	},
	undislike : function(e,likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb) {
		jLikeVal[likecontainerid]['likeaction']='like';
		jLikeVal[likecontainerid]['dislikeaction']='dislike';
		var content_id=this.call('undislike',likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb);

	},
	call : function(likeTask,likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb) {
		var temp='';

		var extraParams = {'plg_name': jLikeVal[likecontainerid]['plg_name'],'plg_type': jLikeVal[likecontainerid]['plg_type']};

		jLike.jQuery.ajax({
			url: root_url+ 'index.php?option=com_jlike&task=store',
			async : false,
			type: 'POST',
			dataType: 'json',
			data : {
				element_id : jLikeVal[likecontainerid]['cont_id'],
				element : jLikeVal[likecontainerid]['element'],
				title : jLikeVal[likecontainerid]['title'],
				url : jLikeVal[likecontainerid]['url'],
				method:likeTask,
				extraParams : extraParams
			},
			error: function(){
				alert('Error loading document');
			},
			success : function(data) {
				jLikeVal[likecontainerid]['likecount']=data.like_cnt;
				jLikeVal[likecontainerid]['dislikecount']=data.dislike_cnt;
				setlayout(likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb);
				temp= data.id;
			}
		});
		return temp;
	}
});

function setlayout(likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb){

	/*getting jLikeVal*/

	var likeaction=jLikeVal[likecontainerid]['likeaction'];
	var dislikeaction=jLikeVal[likecontainerid]['dislikeaction'];
	var cont_id=jLikeVal[likecontainerid]['cont_id'];
	var element=jLikeVal[likecontainerid]['element'];
	var title=jLikeVal[likecontainerid]['title'];
	var url=jLikeVal[likecontainerid]['url'];
	var liketext=jLikeVal[likecontainerid]['liketext'];
	var disliketext=jLikeVal[likecontainerid]['disliketext'];
	var unliketext=jLikeVal[likecontainerid]['unliketext'];
	var undisliketext=jLikeVal[likecontainerid]['undisliketext'];
	 var likecount=jLikeVal[likecontainerid]['likecount'];
	var dislikecount=jLikeVal[likecontainerid]['dislikecount'];
	var likeclass=jLikeVal[likecontainerid]['like_icon_class'];
	var dislikeclass=jLikeVal[likecontainerid]['dislike_icon_class'];

	/*getting jLikeVal end*/

/*if likeaction=like  unlike is pressed we need to show like button
  else  like is pressed show unlike button and annotation snippet as per config
	if no likeaction do not show like or unlike button
*/
	if(likeaction){
		jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().css('display','inline-block');
		jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().attr('class','like-snippet-btn me'+likeaction);


		jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().attr('onclick','jLike.'+likeaction+'(this,'+'"'+likecontainerid+'",'+'"'+show_annotation_snippet+'","'+show_dislike+'","'+display_pwltcb+'")');
		if(likeaction=='like')
		{
				jLike.jQuery("#"+likecontainerid+" .like-snippet  #likecount").text(likecount);
				jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().attr('title',liketext);
		}
		else
		{

				jLike.jQuery("#"+likecontainerid+" .like-snippet  #likecount").text(likecount);
				jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().attr('title',unliketext);
		}
	}
	else
	{
		jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().css('display','none');
	}
	if(dislikeaction){
		jLike.jQuery("#"+likecontainerid+" .like-snippet a").last().attr('class','like-snippet-btn me'+dislikeaction);
		jLike.jQuery("#"+likecontainerid+" .like-snippet a").last().attr('onclick','jLike.'+dislikeaction+'(this,'+'"'+likecontainerid+'",'+'"'+show_annotation_snippet+'","'+show_dislike+'","'+display_pwltcb+'")');


		if(dislikeaction=='dislike'){
			jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().css('display','inline-block');
			jLike.jQuery("#"+likecontainerid+" .like-snippet a.medislike").css('display','inline-block');


			jLike.jQuery("#"+likecontainerid+" .like-snippet  #dislikecount").text(dislikecount);
			jLike.jQuery("#"+likecontainerid+" .like-snippet a").last().attr('title',disliketext);
		}
		else
		{
				jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().css('display','inline-block');


			jLike.jQuery("#"+likecontainerid+" .like-snippet #dislikecount").text(dislikecount);
			jLike.jQuery("#"+likecontainerid+" .like-snippet a").last().attr('title',undisliketext);

		}
	}
	else
	{
		jLike.jQuery("#"+likecontainerid+" .like-snippet a").last().css('display','none');
	}

}
function getUserDetails(likecontainerid) {
		var extraParams = {'plg_name': jLikeVal[likecontainerid]['plg_name'],'plg_type': jLikeVal[likecontainerid]['plg_type']};
		var udetails='';
		jLike.jQuery.ajax({
			url: root_url+ 'index.php?option=com_jlike&task=getUserdetails',
			async : false,
			type: 'POST',
			dataType: 'json',
			data : {
				extraParams : extraParams
			},
			error: function(){
				alert('Error loading document');
			},
			success : function(data) {
				udetails=data;
			}
		});
		return udetails;
	}
function openlabels(action,likecontainerid)
{
	if(action=='add')
	{
		if(jLike.jQuery("#"+likecontainerid+" .labels-space").hasClass("openlable"))
			jLike.jQuery("#"+likecontainerid+" .labels-space").removeClass("openlable");
		else
			jLike.jQuery("#"+likecontainerid+" .labels-space").addClass("openlable");
	}
	else{
		saveList(likecontainerid);
		jLike.jQuery("#"+likecontainerid+" .labels-space").removeClass("openlable");
	}
return false;
}

function addlables(labelinput,noblankmsg,likecontainerid){
		if(jLike.jQuery("#"+likecontainerid+" #"+labelinput).val()== '')
		{
			alert(noblankmsg);
			return false;
		}
		jLike.jQuery.ajax({
			url: root_url+ 'index.php?option=com_jlike&task=addlables',
			async : false,
			type: 'POST',
			dataType: 'json',
			data : {
				lable : jLike.jQuery("#"+likecontainerid+" #"+labelinput).val(),
			},
			error: function(){
				alert('Error loading document');
			},
			success : function(res) {
				jLike.jQuery('.user-labels li#jlike-label-devider').before("<div class='jlikecheckbox'><li><label class='checkbox'><input type='checkbox' class='label-check' onclick=manageListforContent(this,'"+likecontainerid+"') name='label-check[]' value='"+res+"'>"+jLike.jQuery("#"+likecontainerid+" #"+labelinput).val()+"</label></li></div>");
				jLike.jQuery('#'+labelinput).val('');
			}
		});
}
/*
function savedata(likecontainerid,success_msg){
			var lables =0;
			var annotation =' ';

			if(jLike.jQuery("#"+likecontainerid+" .label-check"))
			{
				var lables=jLike.jQuery("#"+likecontainerid+" .label-check:checked").length;
			}
			if(jLike.jQuery("#"+likecontainerid+" #annotation"))
			{
				var annotation=jLike.jQuery("#"+likecontainerid+" #annotation").val();
			}

			if(lables==0 && !annotation)
			{
				alert("Both User lables and Annotation can not be blank!!");
				return false;
			}

			var str = jLike.jQuery("#"+likecontainerid+" form#annotationform").serialize();
			jLike.jQuery.ajax({
				url: root_url+ 'index.php?option=com_jlike&task=savedata',
				async : false,
				type: 'POST',
				dataType: 'json',
				data : {
					formdata : str,
				},
				error: function(){
					alert('Error loading document');
				},
				beforeSend: function(){
        	jLike.jQuery('#jlike-loading-image').show();
    		},
				complete: function(){
        	jLike.jQuery('#jlike-loading-image').hide();
    		},
				success : function(res) {
						jLike.jQuery("#"+likecontainerid+" .labels-space").removeClass("openlable");
						jLike.jQuery("#"+likecontainerid+" #annotation-snippet").css('visibility','hidden');
				}
		});
}*/
function manageListforContent(thislable,likecontainerid)
{
	action='add';
	console.log(thislable.checked);
	if(!thislable.checked)
	{
		action='remove';
	}

	jLike.jQuery.ajax
	({
		url: root_url+ 'index.php?option=com_jlike&task=manageListforContent',
		async : false,
		type: 'POST',
		dataType: 'json',
		data : {
			list_id : thislable.value,
			element_id : jLikeVal[likecontainerid]['cont_id'],
			element : jLikeVal[likecontainerid]['element'],
			title : jLikeVal[likecontainerid]['title'],
			url : jLikeVal[likecontainerid]['url'],
			action : action,
			content_id: jLike.jQuery("#"+likecontainerid+" #jlike_list_form #content_id").value
		},
		error: function(){
			alert('Error loading document');
		},
		beforeSend: function(){
			jLike.jQuery('#jlike-loading-image').show();
		},
		complete: function(){
			jLike.jQuery('#jlike-loading-image').hide();
		},
		success : function(res) {
				//jLike.jQuery(".noteBtn").trigger('click');
		}
	});
}
function removeContentFromList(likecontainerid)
{

	var str = jLike.jQuery("#"+likecontainerid+" form#jlike_list_form").serialize();
			jLike.jQuery.ajax({
				url: root_url+ 'index.php?option=com_jlike&task=savedata',
				async : false,
				type: 'POST',
				dataType: 'json',
				data : {
					formdata : str,
					element_id : jLikeVal[likecontainerid]['cont_id'],
					element : jLikeVal[likecontainerid]['element'],
					title : jLikeVal[likecontainerid]['title'],
					url : jLikeVal[likecontainerid]['url'],
				},
				error: function(){
					alert('Error loading document');
				},
				beforeSend: function(){
        	jLike.jQuery('#jlike-loading-image').show();
    		},
				complete: function(){
        	jLike.jQuery('#jlike-loading-image').hide();
    		},
				success : function(res) {
						//jLike.jQuery(".noteBtn").trigger('click');
				}
		});
		techjoomla.jQuery( ".toolbar_buttons#lists" ).trigger('click');
}
function saveList(likecontainerid)
{

	if(jLike.jQuery("#"+likecontainerid+" .label-check"))
	{
		var lables = jLike.jQuery("#"+likecontainerid+" .label-check:checked").length;
	}
	if (lables == 0)
	{
		alert('not checked');
		return false;
	}
	var str = jLike.jQuery("#"+likecontainerid+" form#jlike_list_form").serialize();
			jLike.jQuery.ajax({
				url: root_url+ 'index.php?option=com_jlike&task=savedata',
				async : false,
				type: 'POST',
				dataType: 'json',
				data : {
					formdata : str,
					element_id : jLikeVal[likecontainerid]['cont_id'],
					element : jLikeVal[likecontainerid]['element'],
					title : jLikeVal[likecontainerid]['title'],
					url : jLikeVal[likecontainerid]['url'],
				},
				error: function(){
					alert('Error loading document');
				},
				beforeSend: function(){
        	jLike.jQuery('#jlike-loading-image').show();
    		},
				complete: function(){
        	jLike.jQuery('#jlike-loading-image').hide();
    		},
				success : function(res) {
						//jLike.jQuery(".noteBtn").trigger('click');
				}
		});
		techjoomla.jQuery( ".toolbar_buttons#lists" ).trigger('click');
}

function savedata(likecontainerid,success_msg){

		var str = jLike.jQuery("#"+likecontainerid+ " form#annotationform").serialize();
		jLike.jQuery.ajax({
			url: root_url+ 'index.php?option=com_jlike&task=savedata',
			async : false,
			type: 'POST',
			dataType: 'json',
			data : {
				formdata : str,
				element_id : jLikeVal[likecontainerid]['cont_id'],
				element : jLikeVal[likecontainerid]['element'],
				title : jLikeVal[likecontainerid]['title'],
				url : jLikeVal[likecontainerid]['url'],
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				jLike.jQuery("#" + likecontainerid + " form#annotationform .alert").removeClass('alert-success').addClass('alert-error').html(jqXHR.responseText);
				jLike.jQuery("#" + likecontainerid + " form#annotationform .alert").show();
			},
			beforeSend: function(){
					jLike.jQuery('#jlike-loading-image').show();
			},
			complete: function(){
					jLike.jQuery('#jlike-loading-image').hide();
			},
			success : function(res) {
				jLike.jQuery("#" + likecontainerid + " form#annotationform .alert").removeClass('alert-success').addClass('alert-success').html('Successfully saved');
				jLike.jQuery("#" + likecontainerid + " form#annotationform .alert").show();
			}
	});
}

function changeprivacy(privacylabel,likecontainerid)
{
		jLike.jQuery( "#"+likecontainerid+" #jlike_privacy label" ).each(function (e) {
		if ( this.id == privacylabel ) {
				jLike.jQuery(this).attr('class',"btn active btn-success");
		} else {
				jLike.jQuery(this).attr('class',"btn");
		}
});

}
/*function close_comment_snippet(likecontainerid)
{
	jLike.jQuery("#"+likecontainerid+" .labels-space").removeClass("openlable");
	jLike.jQuery("#"+likecontainerid+" .labels-space").removeClass("open");
	jLike.jQuery("#"+likecontainerid+"  #annotation-snippet").css('display','none');

}*/
function close_comment_snippet(likecontainerid)
{
	techjoomla.jQuery( ".toolbar_buttons#notes" ).trigger('click');
}
function openAnotationSnipet(likecontainerid)
{

	jLike.jQuery("#"+likecontainerid+"  #annotation-snippet").css('display','block');

}
