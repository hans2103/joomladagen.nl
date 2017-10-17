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

		var content_data=this.call('like',likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb);
		if(content_data.id){
			jLike.jQuery("#"+likecontainerid+" #annotationform #content_id").val(content_data.id);

			if(display_pwltcb=='1')
			{
				if(parseInt(content_data.like_cnt) <= parseInt(jLikeVal[likecontainerid]['no_of_pwltc_users']))
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
							ua +="<img class='pwltcb_img img-circle' src='"+udetails.img_url+"' alt=''></a></li>";
							jLike.jQuery("#"+likecontainerid+" .pwltcb .pwltcb_ul").append(ua);
					}
				}
				else
				{
					var more= content_data.like_cnt - jLikeVal[likecontainerid]['no_of_pwltc_users'];
					jLike.jQuery("#"+likecontainerid+" .pwltcb_cnt").html(more);
				}
			}
		}
	},
	dislike : function(e,likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb)
	{
		jLikeVal[likecontainerid]['likeaction']='like';
		jLikeVal[likecontainerid]['dislikeaction']='undislike';
		var content_data=this.call('dislike',likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb);
		if(content_data.id){
			if(display_pwltcb=='1'){
				if(parseInt(content_data.like_cnt) <= parseInt(jLikeVal[likecontainerid]['no_of_pwltc_users']))
				{
					var udetails=getUserDetails(likecontainerid);
					if(udetails)
						jLike.jQuery("#"+likecontainerid+" a[title='"+udetails.uname+"']").closest('li').remove();
				}
				else
				{
					var more= content_data.like_cnt - jLikeVal[likecontainerid]['no_of_pwltc_users'];
					jLike.jQuery("#"+likecontainerid+" .pwltcb_cnt").html(more);
				}
			}
		}

	},
	unlike : function(e,likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb) {

		jLikeVal[likecontainerid]['likeaction']='like';

		if(show_dislike==1)
			jLikeVal[likecontainerid]['dislikeaction']='dislike';
		else
			jLikeVal[likecontainerid]['dislikeaction']='';


		/* Remove from plan or completed */
		jLikeVal[likecontainerid]['unlikeStatus'] = 0;

		if (jLikeVal[likecontainerid]['statusMgt'] == 1)
		{
			var satusflag= confirm(Joomla.JText._('COM_JLIKE_REMOVE_FROM_PLAN_ALERT'));

			if(satusflag)
			{
				/* 1 for completed status */
				jLikeVal[likecontainerid]['unlikeStatus'] = 1;
			}
		}

		var content_data=this.call('unlike',likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb);
		if(content_data.id){
			if(display_pwltcb=='1'){
				if(parseInt(content_data.like_cnt) <= parseInt(jLikeVal[likecontainerid]['no_of_pwltc_users']))
				{
					var udetails=getUserDetails(likecontainerid);
					if(udetails)
					 jLike.jQuery("#"+likecontainerid+" a[title='"+udetails.uname+"']").closest('li').remove();
				}
				else
				{
					var more= content_data.like_cnt - jLikeVal[likecontainerid]['no_of_pwltc_users'];
					jLike.jQuery("#"+likecontainerid+" .pwltcb_cnt").html(more);
				}
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
		var like_status = '';

		if (jLike.jQuery("#likeStatus_"+likecontainerid).val())
		{
			like_status = jLike.jQuery("#likeStatus_"+likecontainerid).val();

		}

		var statusParam = '0';

		if (jLikeVal[likecontainerid]['unlikeStatus'])
		{
			statusParam = 1;
		}

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
				plg_name : jLikeVal[likecontainerid]['plg_name'],
				plg_type : jLikeVal[likecontainerid]['plg_type'],
				method:likeTask,
				like_statusId:like_status,
				statusParam : statusParam,
				extraParams : extraParams
			},
			error: function(){
				alert('Error loading document');
			},
			success : function(data) {
				jLikeVal[likecontainerid]['likecount']=data.like_cnt;
				jLikeVal[likecontainerid]['dislikecount']=data.dislike_cnt;
				setlayout(likecontainerid,show_annotation_snippet,show_dislike,display_pwltcb);
				temp= data;
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
		jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().css('display','block');
		jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().attr('class','me'+likeaction);


		jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().attr('onclick','jLike.'+likeaction+'(this,'+'"'+likecontainerid+'",'+'"'+show_annotation_snippet+'","'+show_dislike+'","'+display_pwltcb+'")');
		if(likeaction=='like')
		{
			/* Change titile*/
			jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().attr({'title':liketext});

			jLike.jQuery("#"+likecontainerid+"  #annotation-snippet").css('visibility','hidden');
			if(likecount!='0')
				jLike.jQuery("#"+likecontainerid+" .like-snippet a.me"+likeaction+" #likecount").text(likecount);
			else
			jLike.jQuery("#"+likecontainerid+" .like-snippet a.me"+likeaction+" #likecount").text(liketext);

		}
		else
		{
			jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().attr({'title':unliketext});
			if(likecount)
				jLike.jQuery("#"+likecontainerid+" .like-snippet a.me"+likeaction+" #likecount").text(likecount);
			else
			jLike.jQuery("#"+likecontainerid+" .like-snippet a.me"+likeaction+" #likecount").text(unliketext);

			if(show_annotation_snippet=='1')
				jLike.jQuery("#"+likecontainerid+"  #annotation-snippet").css('visibility','visible');
			else
			{
				jLike.jQuery("#"+likecontainerid+"  #annotation-snippet").css('visibility','hidden');
			}
		}
	}
	else
	{
		jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().css('display','none');
	}

	if(dislikeaction){

		jLike.jQuery("#"+likecontainerid+" .like-snippet a:last").attr('class','me'+dislikeaction);

		jLike.jQuery("#"+likecontainerid+" .like-snippet a:last").attr('onclick','jLike.'+dislikeaction+'(this,'+'"'+likecontainerid+'",'+'"'+show_annotation_snippet+'","'+show_dislike+'","'+display_pwltcb+'")');


		if(dislikeaction=='dislike'){
			jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().css('display','block');
			jLike.jQuery("#"+likecontainerid+" .like-snippet a:last").attr('title',disliketext);
			jLike.jQuery("#"+likecontainerid+" .like-snippet a.medislike").css('display','block');

			if(dislikecount!='0')
			{
				jLike.jQuery("#"+likecontainerid+" .like-snippet a.me"+dislikeaction +" #dislikecount").text(dislikecount);
			}
			else
			{
				jLike.jQuery("#"+likecontainerid+" .like-snippet a.me"+dislikeaction +" #dislikecount").text(disliketext);
			}
		}
		else
		{
			jLike.jQuery("#"+likecontainerid+" .like-snippet a:last").attr('title',undisliketext);

			jLike.jQuery("#"+likecontainerid+" .like-snippet a").first().css('display','block');

			if(dislikecount!='0')
			{
				jLike.jQuery("#"+likecontainerid+" .like-snippet a.me"+dislikeaction +" #dislikecount").text(dislikecount);
			}
			else
			{
				jLike.jQuery("#"+likecontainerid+" .like-snippet a.me"+dislikeaction +" #dislikecount").text(undisliketext);
			}
		}
	}
	else
	{
		// Like button getting hidden if we hide dislike button through configuration that's via commented following code
		//jLike.jQuery("#"+likecontainerid+" .like-snippet a").last().css('display','none');
	}

}

function getUserDetails(likecontainerid)
{
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
else
jLike.jQuery("#"+likecontainerid+" .labels-space").removeClass("openlable");
return false;
}
function oncheck(likecontainerid){
		var n = jLike.jQuery( "#"+likecontainerid+" .user-labels input[type=checkbox]:checked" ).length;

		if(n > 0)
		{
			jLike.jQuery( "#"+likecontainerid+" #jlike-add-label").css('display','none');
			jLike.jQuery( "#"+likecontainerid+" #jlike-apply-label").css('display','block');
		}
		else
		{
			jLike.jQuery( "#"+likecontainerid+" #jlike-apply-label").css('display','none');
			jLike.jQuery( "#"+likecontainerid+" #jlike-add-label").css('display','block');
		}
	return;
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
			success : function(res)
			{
				var lableHtml= "<li><div class='row-fluid' id='lableRow_"+res+"'><span class='span10'><label class='checkbox'><input type='checkbox' class='label-check' onclick=oncheck('"+likecontainerid+"') name='label-check[]' value='"+res+"'>"+jLike.jQuery("#"+likecontainerid+" #"+labelinput).val()+"</label></span><span class='span2' id='lable_"+res+"' onClick='jlike_deleteList(" + res + ")'><i class='icon-trash icon-white'></i></span></div></li>";


				/*	jLike.jQuery('.user-labels li.divider').before("<li><label class='checkbox'><input type='checkbox' class='label-check' onclick=oncheck('"+likecontainerid+"') name='label-check[]' value='"+res+"'>"+jLike.jQuery("#"+likecontainerid+" #"+labelinput).val()+"</label></li>"); */
				jLike.jQuery('.user-labels li.divider').before(lableHtml);
				jLike.jQuery('#'+labelinput).val('');
			}
		});
}
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
function close_comment_snippet(likecontainerid)
{
	jLike.jQuery("#"+likecontainerid+" .labels-space").removeClass("openlable");
	jLike.jQuery("#"+likecontainerid+" .labels-space").removeClass("open");
	jLike.jQuery("#"+likecontainerid+"  #annotation-snippet").css('visibility','hidden');

}


function send_recommendation(likecontainerid,success_msg)
{
		var str = jLike.jQuery("form#recommendcourse_form").serialize();

		jLike.jQuery.ajax({
			url: root_url+ 'index.php?option=com_jlike&task=send_recommendation',
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
				loadingImage(likecontainerid);
			},
			complete: function(){
				hideImage();
			},
			success : function(res) {
				alert('Recommendation send');
				window.parent.SqueezeBox.close();
			}
		});

}

/*This function delete the list*/
function jlike_deleteList(likecontainerid, jlike_base_url)
{
	var postParam = {};

	var flag= confirm(Joomla.JText._('COM_JLIKE_DELETE_LIST_CONFIRMATION'));

	if(flag==true)
	{
		jLike.jQuery.ajax({
			url: root_url+ "index.php?option=com_jlike&task=jlike_deleteList&lableId=" + likecontainerid + "&tmpl=component&format=raw",
			type: 'POST',
			data:postParam,
			cache: false,
			/*crossDomain: true,*/
			dataType: 'json',
			/*beforeSend: setHeader,*/
			beforeSend: function()
			{
			},
			complete: function() {
			},
			success: function(msg)
			{
				alert(msg['statusMsg']);

				if (msg['status'] == 1)
				{
					jLike.jQuery('#lableRow_' + likecontainerid).remove();
				}

			},
			error: function(){
				console.log('Error loading document');
			},
		});
	}
}


function jlUpdateStatus(element, element_id, eleId)
{
		var like_status = '';

		if (jLike.jQuery("#"+eleId).val())
		{
			like_status = jLike.jQuery("#"+eleId).val();

		}

		jLike.jQuery.ajax({
			url: root_url+ "index.php?option=com_jlike&task=changeItemStatus&tmpl=component&format=raw",
			type: 'POST',
			data : {
				element:element,
				element_id:element_id,
				status_id : like_status,
			},
			cache: false,
			dataType: 'json',
			beforeSend: function()
			{
				jLike.jQuery('#jLload_' + eleId).show();
			},
			complete: function() {
				jLike.jQuery('#jLload_' + eleId).hide();
			},
			success: function(msg)
			{

			},
			error: function(){
				console.log('Error while updating status');
			},
		});
}

function jlike_loginRedirect(loginUrl)
{
	var comfirmRedirect = confirm(Joomla.JText._('COM_JLIKE_MUSTLOGIN'));

	if (comfirmRedirect == true)
	{
		window.location= loginUrl;
	}
}
