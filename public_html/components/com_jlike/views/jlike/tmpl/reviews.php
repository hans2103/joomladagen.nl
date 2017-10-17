<?php
	defined ('_JEXEC' ) or die ( 'Restricted access' );
	JHtml::stylesheet(JUri::root(). 'components/com_jlike/assets/css/jRating.jquery.css' );
	require_once(JPATH_SITE . DS . 'components' . DS . 'com_jlike' . DS . 'views' . DS . 'jlike' . DS . 'tmpl' . DS . 'jRating.php');
	JHtml::script( JUri::root().'components/com_jlike/assets/scripts/jrating.js' );
?>

<script type="text/javascript">
		var global_reply_annotation_id=0;
		var jLikeSmilehtml;
		var textAreaId;
		var selector_id;
		var simely_textarea_id;
		var response_id;
		var user_id =<?php echo $loged_user; ?>;
		var allow_threaded_comment= <?php echo $params->get('allow_threaded_comment') ? $params->get('allow_threaded_comment'):0 ;?>;
		var max_thread_level_limit=<?php echo $params->get('threaded_level') ? $params->get('threaded_level') : 0; ?>;
		if(max_thread_level_limit==0)
			max_thread_level_limit=999;
		var renderedMoreId = new Array();
		var pageLoadAnnotaionIds='<?php
										if(!empty($annotaionIds))
										{
											echo json_encode($annotaionIds);
										}
										else
										{
											echo '';
										} ?>';

		var Originat_comment_count=<?php if(!empty($this->reviews_count[0]))
										{
											echo $this->reviews_count[0];
										}
										else
											echo 0;
										?>;
		var result_comment_count=<?php if(!empty($this->reviews_count[1]))
										echo $this->reviews_count[1];
									else
										echo 0;
									?>;
		var comment_config_limit=<?php echo $comment_limit ? $comment_limit:5 ;?>;


		techjoomla.jQuery(document).ready(function()
		{

			//techjoomla.jQuery("label.timeago").timeago();
			//load the users (name & id who like this(commment id) comment)
			techjoomla.jQuery(".user_liked").click(function(){
				var CommentId=techjoomla.jQuery(this).attr('id');
				CommentId=CommentId.replace('commentid','');
				var like=1; //identify that should show liked users name
				getUsersByCommentId(CommentId,like)
			});

			techjoomla.jQuery(".user_disliked").click(function(){
				var CommentId=techjoomla.jQuery(this).attr('id');
				CommentId=CommentId.replace('commentDislike','');
				var dislike=0; //identify that should show disliked users name
				getUsersByCommentId(CommentId,dislike);
			});

			//set the active of oldest latest
			ordering=techjoomla.jQuery('#sorting').val();
			techjoomla.jQuery("#lioldest").removeClass("active");
			techjoomla.jQuery("#lilatest").addClass("active");

			//showHideviewCommentsMsg view more
			showHideviewCommentsMsg();
			/** insert character(s) at selected cursor location
			 * http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery/946556#946556
			 **/
			techjoomla.jQuery.fn.insertAtCaret = function (myValue) {
				return this.each(function () { /*IE support*/
					if (document.selection) {
						this.focus();
						sel = document.selection.createRange();
						sel.text = myValue;
						this.focus();
					} /*MOZILLA/NETSCAPE support*/
					else if ((this.selectionStart) || (this.selectionStart == '0')) {
						var startPos = this.selectionStart;
						var endPos = this.selectionEnd;
						var scrollTop = this.scrollTop;
						this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
						this.focus();
						this.selectionStart = startPos + myValue.length;
						this.selectionEnd = startPos + myValue.length;
						this.scrollTop = scrollTop;
					} else {
						this.value += myValue;
						this.focus();
					}
				});
			};
			techjoomla.jQuery(".jlike_modal a[data-dismiss='modal']").click(function(){
				techjoomla.jQuery('.jlike_modal').css('display','none');
			});
		});

		function html_substr( str, count ) {

			var div = document.createElement('div');
			div.innerHTML = str;

			walk( div, track );

			function track( el ) {
				if( count > 0 ) {
					var len = el.data.length;
					count -= len;
					if( count <= 0 ) {
						el.data = el.substringData( 0, el.data.length + count );
					}
				} else {
					el.data = '';
				}
			}

			function walk( el, fn ) {
				var node = el.firstChild;
				do {
					if( node.nodeType === 3 ) {
						fn(node);
					} else if( (node.nodeType === 1) && ((node.childNodes) && (node.childNodes[0])) ) {
						walk( node, fn );
					}
				} while( node = node.nextSibling );
			}
			return div.innerHTML;
		}



		function getUsersByCommentId(annotationid,likedOrdisliked)
		{
			techjoomla.jQuery.ajax({
				url:'<?php echo JUri::root();?>index.php?option=com_jlike&task=getUserByCommentId&tmpl=component&format=row',
				type:'POST',
				dataType:'json',
				data:{
					annotationid:annotationid,
					likedOrdisliked:likedOrdisliked,
				},
				success:function(data)
				{
					var html='';
					for(var index=0;index<data.length;index++)
					{
						html+='<img class="img-circle jlike-img-border" src="'+data[index]['avtar']+'" width="10%" height="10%"/> &nbsp;';
						html+='<a href="'+data[index]['user_profile_url']+'">'+data[index]['name']+'</a><hr/>';
					}

					if(likedOrdisliked)
						techjoomla.jQuery('.modal_header').html('<?php echo JText::_('COM_JLIKE_WHO_LIKE_THIS'); ?>');
					else
						techjoomla.jQuery('.modal_header').html('<?php echo JText::_('COM_JLIKE_WHO_DISLIKE_THIS'); ?>');

					techjoomla.jQuery('#modalconent').html(html);
					jQuery("#user_info_modal" ).trigger( "click" );
					//techjoomla.jQuery('.jlike_modal').css('display','block');
					//techjoomla.jQuery('#like_dislike_users').modal('show');
					//techjoomla.jQuery('.jlike_modal').removeClass('fade');

				}
			});
		}


		function increaseLikeCount(reff)
		{
			if(!user_id)
				return alert('<?php echo JText::_('COM_JLIKE_PLS_LOGIN_SITE');?>');
			var annotationid=parseInt((reff.id).replace('like_annotationid',''));
			var currentLable= techjoomla.jQuery("#like_unlike"+annotationid).text(); //if Like change to Unlike & vice-versa
			var likeCount=parseInt(techjoomla.jQuery("#like_count"+annotationid).text());
			var comment= techjoomla.jQuery("#CommentText"+annotationid).html();

			if(isNaN(likeCount))
			{
				likeCount=0;
			}
			var dislikeCount=parseInt(techjoomla.jQuery("#dislike_count"+annotationid).text());
			if(isNaN(dislikeCount))
			{
				dislikeCount=0;
			}
			techjoomla.jQuery.ajax({
				url:'<?php echo JUri::root();?>index.php?option=com_jlike&task=increaseLikeCount&tmpl=component&format=row',
				type:'POST',
				dataType:'json',
				data:{
					annotationid:annotationid,
					comment:comment,
				},
				success:function(data)
				{
					response=parseInt(data)
					if(response)
					{
						if(currentLable.trim()=='<?php echo JText::_('COM_JLIKE_LIKE_BTN'); ?>')
						{
							techjoomla.jQuery('#jlike_like_count_area'+annotationid).show();
							techjoomla.jQuery("#like_count"+annotationid).text(likeCount+1);
							techjoomla.jQuery("#like_unlike"+annotationid).html('<?php echo JText::_('COM_JLIKE_UNLIKE_BTN'); ?>');
						}
						else
						{
							if((likeCount-1)<=0)
							{
								//techjoomla.jQuery('#jlike_like_count_area'+annotationid).hide();
							}
							techjoomla.jQuery("#like_count"+annotationid).text(likeCount-1);
							techjoomla.jQuery("#like_unlike"+annotationid).html('<?php echo JText::_('COM_JLIKE_LIKE_BTN'); ?>');
						}
						if(response==2)
						{
							if((dislikeCount-1)<=0)
							{
								//techjoomla.jQuery('#jlike_dislike_count_area'+annotationid).hide();
							}
							techjoomla.jQuery("#dislike_count"+annotationid).text(dislikeCount-1);
							techjoomla.jQuery("#dislike_undislike"+annotationid).html('<?php echo JText::_('COM_JLIKE_DISLIKE_BTN'); ?>');
						}
					}
					else
					{
						alert('Error in '+currentLable.trim());
					}
				}
			});
		}

		/**method to increaseDislikeCount  **/
		function increaseDislikeCount(reff)
		{
			if(!user_id)
				return alert('<?php echo JText::_('COM_JLIKE_PLS_LOGIN_SITE');?>');

			var annotationid=parseInt((reff.id).replace('dislike_annotationid',''));
			var currentLable= techjoomla.jQuery("#dislike_undislike"+annotationid).text(); //if DisLike change to Udislike & vice-versa
			var likeCount=parseInt(techjoomla.jQuery("#like_count"+annotationid).text());
			var comment= techjoomla.jQuery("#CommentText"+annotationid).html();
			if(isNaN(likeCount))
			{
				likeCount=0;
			}
			var dislikeCount=parseInt(techjoomla.jQuery("#dislike_count"+annotationid).text());
			if(isNaN(dislikeCount))
			{
				dislikeCount=0;
			}
			techjoomla.jQuery.ajax({
				url:'<?php echo JUri::root();?>index.php?option=com_jlike&task=increaseDislikeCount&tmpl=component&format=row',
				type:'POST',
				dataType:'json',
				data:{
					annotationid:annotationid,
					comment:comment,
				},
				success:function(data)
				{
					response=parseInt(data)
					if(response)
					{
						if(currentLable.trim()=='<?php echo JText::_('COM_JLIKE_DISLIKE_BTN'); ?>')
						{
							techjoomla.jQuery('#jlike_dislike_count_area'+annotationid).show();
							techjoomla.jQuery("#dislike_count"+annotationid).text(dislikeCount+1);
							techjoomla.jQuery("#dislike_undislike"+annotationid).html('<?php echo JText::_('COM_JLIKE_UNDISLIKE_BTN'); ?>');
						}
						else
						{
							if((dislikeCount-1)<=0)
							{
								//techjoomla.jQuery('#jlike_dislike_count_area'+annotationid).hide();
							}
							techjoomla.jQuery("#dislike_count"+annotationid).text(dislikeCount-1);
							techjoomla.jQuery("#dislike_undislike"+annotationid).html('<?php echo JText::_('COM_JLIKE_DISLIKE_BTN'); ?>');
						}
						if(response==2)
						{
							if((likeCount-1)<=0)
							{
								//techjoomla.jQuery('#jlike_like_count_area'+annotationid).hide();
							}
							techjoomla.jQuery("#like_count"+annotationid).text(likeCount-1);
							techjoomla.jQuery("#like_unlike"+annotationid).html('<?php echo JText::_('COM_JLIKE_LIKE_BTN'); ?>');
						}
					}
					else
					{
						alert('Error in '+currentLable.trim());
					}
				}
			});
		}
		/** method **/
		function printingRecursiveChildren(data,style,margin_left,width,padding_left,commentbtn_margin,i,threadlevel)
		{
			//get the childrens data for selected annotation id

			//load the more comments for view more
			techjoomla.jQuery.ajax({
				url:'<?php echo JUri::root();?>index.php?option=com_jlike&task=LoadComment&tmpl=component&format=row',
				type:'POST',
				dataType:'json',
				data:
				{
					annotaionIdsArr:0,
					viewmoreId:0,
					element_id:<?php echo $this->urldata->cont_id ;?>,
					element:'<?php echo $this->urldata->element; ?>',
					sorting:2,
					callIdetity:1,
					getchildren:1,
					childrensId:data,
				},
				success:function(data){
					for(index = 0; index < data.length; index++)
					{
						var html ='<hr class="jlike_hr_margin" />';
							html+='<div id="deleteComment'+data[index]['annotation_id']+'" style="'+style+'"  class="media row-fluid  jlike_commentingArea  jlike_renderedComment">';

							html+=	'<a class="pull-left" href="'+data[index]['user_profile_url']+'"><img class="img-circle jlike-img-border" src="'+data[index]['avtar']+'" alt="Smiley face" width="36px" height="auto"></a>';

							html+='<div class="media-body jlike_media_body" >';
							html+=		'<span>';
							html+=			'<a href="'+data[index]['user_profile_url']+'">'+data[index]['name']+'</a>';

							if(user_id==data[index]['user_id']){
								html+=		'<div class="jlike_position_relative pull-right">';
								html+=			'<a  class="pull-left" data-toggle="dropdown" href="#">';
								html+=				'<i class="icon-pencil"></i> ';
								html+=			'</a>';
								html+=			'<ul class="dropdown-menu jlike_edit_dropdown jlike_list_style_type">';
								html+=				'<li id="showEditDeleteButton'+data[index]['annotation_id']+'" tabindex="-1">';
								html+=					'<a id="" class="showEditDeleteButton" onclick="EditComment(this)"><?php echo JText::_('COM_JLIKE_EDIT'); ?></a>';
								html+=				'</li>';
								html+=				'<li id="DeleteButton'+data[index]['annotation_id']+'" tabindex="-1">';
								html+=					'<a id="" class="showEditDeleteButton" onclick="DeleteComment(this)"><?php echo JText::_('COM_JLIKE_DELETE'); ?></a>';
								html+=				'</li>';
								html+=			'</ul>';
								html+=		'</div>';
							 }
							 html+=		'</span>';

							html+=		'<div class="">';
							html+=			'<div id="showlimited'+data[index]['annotation_id']+'" class=" jlike_comment_padding">';

											var smileyannotation=(data[index]['smileyannotation']);
											var temp_str	=	smileyannotation.replace(/(<([^>]+)>)/ig,"");

											if(temp_str.length	>=	165){
											html	+=	html_substr( smileyannotation, 165 )+'...';
											}else{
												html	+=	smileyannotation;
											}
							html+=				'<a  class="jlike_pointer"  onclick="showFullComment('+data[index]['annotation_id']+')">';
											if(temp_str.length>=165){
												html+='<?php echo JText::_('COM_JLIKE_SEE_MORE'); ?>';
											}
							html+=					'</a>';
							html+=			'</div>';
							html+=			'<div id="showlFullComment'+data[index]['annotation_id']+'" class=" jlike_display_none jlike_comment_padding">';
							html+=					data[index]['smileyannotation']+' &nbsp';
							html+=				'<a class="jlike_pointer"  onclick="showLimitedComment('+data[index]['annotation_id']+')"><br/> <?php echo JText::_('COM_JLIKE_SEE_LESS'); ?></a>';
							html+=			'</div>';
								if(user_id==data[index]['user_id'])
								{
									html+='<div id="EditComment'+data[index]['annotation_id']+'" class="jlike_display_none" >';

									html+=	'<div class="jlike_textarea taggable"  <?php echo $maxlength; ?> id="CommentText'+data[index]['annotation_id']+'" contenteditable="true" required="required"  onkeyup="characterLimit(id, <?php echo $maxlength; ?>)">'+data[index]['annotation']+'</div>';

									html+=	'<div class="jlike_smiley_container">';
									html+=		'<div id="'+data[index]['annotation_id']+'" class="jlike_smiley jlike_display_inline_blk jlike_btn_container" >';
									html+=			'<button id="jlike_smiley" class="jlike_smiley" alt="" type="button" onClick="javascript:jLikeshowSmiley(this,'+data[index]['annotation_id']+');"></button>';
									html+=		'</div>';
									html+=	'</div>';

									html+=	'<div id="displaytagsfor_CommentText'+data[index]['annotation_id']+'" class="displayme_CommentText"></div>';

									html+=	'</div>';

								}
								else
								{
									html+=	'<div class="jlike_display_none jlike_textarea taggable"  <?php echo $maxlength; ?> id="CommentText'+data[index]['annotation_id']+'" contenteditable="true" required="required" onkeyup="characterLimit(id, <?php echo $maxlength; ?>)">'+data[index]['annotation']+'</div>';
								}

							html+="<div class='jlike_comment_padding_top'>";
							html+= '<span class="small">';
							//--@show replies on click of show reply
								json_array_children=JSON.stringify(data[index]['children']);
								json_array_children=json_array_children.replace(/\s+/g, '--');

							if((allow_threaded_comment) && (threadlevel<max_thread_level_limit))
							{
								html+=	'<a id="parentid'+data[index]['annotation_id']+'" class="jlike_pointer" onclick="jlike_reply(this,'+(margin_left+8)+','+(width-8)+','+(commentbtn_margin-5)+','+(threadlevel+1)+')" ><?php echo JText::_('COM_JLIKE_REPLY_BTN') ?></a>';
							}

							if(allow_threaded_comment &&(threadlevel<max_thread_level_limit))
							{
								html+="<span id='parentid_show_reply"+data[index]['annotation_id']+"' class='jlike_pointer' onclick=show_reply(this,"+json_array_children+","+(margin_left+8)+","+(width-8)+","+(padding_left+1)+","+threadlevel+") ><div class='jlike_count_box'>"+(data[index]['children'].length)+"</div></span>";
								html+='<span id="nbspId'+data[index]['annotation_id']+'" > &nbsp; </span>';
							//--@ threaded reply button
							}


							//like unlike button code start
							<?php if($params->get('like_dislike_comments'))
							{ ?>

							var likeCount=0;

							if(data[index]['likeCount'])
							{
								likeCount=data[index]['likeCount'];
							}

							html+='<span>';

								html+=				'<a id="like_annotationid'+data[index]['annotation_id']+'" class="jlike_margin_left jlike_like_btn jlike_pointer jlike_margin_left" onclick="increaseLikeCount(this)" >';
								html+=					'<span id="like_unlike'+data[index]['annotation_id']+'">';
															if(data[index]['userLikeDislike']==1){
								html+=							'<?php	echo JText::_('COM_JLIKE_UNLIKE_BTN'); ?>';
															}
															else{
								html+=							'<?php	echo JText::_('COM_JLIKE_LIKE_BTN');?>';
															}
								html+=					'</span>';
								html+=				'</a>';

								html+=	'<span id="jlike_like_count_area'+data[index]['annotation_id']+'">';
									//-@ threaded LIKE COUNT
								html+=	'<span id="commentid'+data[index]['annotation_id']+'" class="user_liked jlike_pointer" onclick="getUsersByCommentId('+data[index]['annotation_id']+','+1+')">';
								html+=		'<div id="like_count'+data[index]['annotation_id']+'" class="jlike_count_box">';
								html+=			likeCount;
								html+=		'</div>';
								html+=	'</span>';
								html+=	'</span>';

							html+='</span>';
							<?php if($params->get('show_comment_dislike_button')){ ?>

								var dislikeCount = 0;

								if(data[index]['dislikeCount'])
								{
									dislikeCount=data[index]['dislikeCount'];
								}


								html+='<span class="jlike_margin_from_left">';


									html+=	'<a id="dislike_annotationid'+data[index]['annotation_id']+'" class="jlike_margin_left jlike_dislike_btn jlike_pointer" onclick="increaseDislikeCount(this)" >';
									html+=		'<span id="dislike_undislike'+data[index]['annotation_id']+'">';
												if(data[index]['userLikeDislike']==2){
									html+=		'<?php echo JText::_('COM_JLIKE_UNDISLIKE_BTN');?>';
												}
												else{
									html+=		'<?php echo JText::_('COM_JLIKE_DISLIKE_BTN');?>';
												}
									html+=		'</span>';
									html+=	'</a>';

									//@S Dislike Count
									html+=	'<span id="jlike_dislike_count_area'+data[index]['annotation_id']+'" >';

									html+=		'<div class="jlike_pointer jlike_count_box" id="dislike_count'+data[index]['annotation_id']+'" onclick="getUsersByCommentId('+data[index]['annotation_id']+','+0+')">'+dislikeCount+'</div>';
									html+=	'</span>';
									//@E Dislike Count


								html+='</span>';
								<?php } ?>
							<?php
							} ?>
							if(user_id==data[index]['user_id'])
							{
								html+=	'<span class="pull-right jlike_review_button">';
								html+=		'<span id="jlike_cancel_comment_btn'+data[index]['annotation_id']+'" class=" jlike_cancel_comment_btn jlike_display_none">  &nbsp;';

								html+=			'<button type="button" class="btn btn-small jlike_cancelbtn_reply" onclick="Cancel('+data[index]['annotation_id']+')"> Cancel</button>';

								html+=			'<button type="button" class="btn btn-success btn-small jlike_commentbtn" onclick="SaveEditedComment('+data[index]['annotation_id']+','+data[index]['annotation_id']+')"> Review</button>';

								html+=		'</span>';
								html+=	'</span>';
							}
							//like unlike button code end
							//Show comment time
							html+='<span id="jlike_comment_time'+data[index]['annotation_id']+'" class="pull-right jlike_comment_time">';
							html+=	data[index]['date'];
							html+=	data[index]['time'];
							html+='</span>';

							html+='</span>';
							html+=		'</div>';
							//--end threaded reply button
							html+=		'</div>';
							html+=	'</div>';
							html+='</div>';
						techjoomla.jQuery(html).insertAfter('#deleteComment'+data[index]['parent_id']);

					}
				}
			});
		}

		function jlike_reply(parent_ref,margin_left,width,commentbtn_margin,threadlevel)
		{
			if(margin_left==0)
			{
				margin_left=7;
			}
			if(width==0)
			{
				width=92;
			}

			if(commentbtn_margin==undefined)
			{
				commentbtn_margin=92;
			}

			owner_reply = 1;

			addCommentArea('<?php echo $likecontainerid;?>',parent_ref.id,margin_left,width,commentbtn_margin,threadlevel, owner_reply);
		}
		/**
		Method to add comment area
		*/
		function addCommentArea(likecontainerid,parent_id,margin_left,width,commentbtn_margin,threadlevel, owner_reply)
		{
			//close the other box if open to add new comment or reply

			techjoomla.jQuery(".clone_othere_reply_box").remove();
			//hide divaddcomment

			if(!parseInt(user_id))
			{
				alert('<?php echo JText::_('COM_JLIKE_LOGIN_TO_REVIEWS'); ?>');
				return false;
			}

			<?php if($this->urldata->jlike_allow_rating == 1)
			{ ?>
				<?php if(!$this->allowRating) { ?>
				alert('<?php echo JText::_('COM_JLIKE_LOGIN_TO_RATING_REVIEWS'); ?>');
				return false;
			<?php } ?><?php } ?>

			//identify it is reply or new comment
			if(parseInt(parent_id)!=0)
			{
				textAreaId=0; //reply to comment
			}
			else
			{
				textAreaId=0; //new comment hence id 0 var  addComment= techjoomla.jQuery("#"+likecontainerid+  " #deleteComment"+textAreaId).length;
				var  addComment= techjoomla.jQuery("#"+likecontainerid+  " #deleteComment"+textAreaId).length;
				if(addComment>0) //show only one comment area on click of Add a comment
				{
					return;
				}
			}

			//get user info
			profile_url="<?php echo $userInfo->user_profile_url; ?>";
			avtar='<?php echo $userInfo->avtar; ?>';
			user_name='<?php echo $userInfo->name; ?>';

			var html ='<div id="deleteComment'+textAreaId+'" style=" margin-left:'+margin_left+'%;" class="media jlike_add_comment jlike_commentingArea jlike_commentBox clone_othere_reply_box jlike_no_radius" >';
				html+='<hr class="jlike_hr_margin">';
				html+="<a class='pull-left' href='"+profile_url+"'><img class='img-circle jlike-img-border' src='"+avtar+"' alt='Smiley face' width='36px' height='auto' ></a>";

				html+='<div class="media-body jlike_media_body" >';
				html+='		<div class="media-heading  jlike_user_name_btn_block">';
				html+="			<a class='pull-left'  href='"+profile_url+"'>"+user_name+"</a>";
				html+='			<span id="<?php echo 'jlike_show_rating' . (isset($reviews->annotation_id) ? $reviews->annotation_id : '' ); ?>" class="jlike_show_rating pull-left Jlike_user_rating" >';


				if (owner_reply != 1)
				{
					html+='				<div class="basic_new" data-rating="<?php echo isset($reviews->rating_upto) ? $reviews->rating_upto : '0';?>" data-average="0" data-id="1"></div>';
					html+='			</span>';
				}

				//edit delete button
				html+=		'<div class="jlike_position_relative pull-right jlike_display_none" id="editingOptions'+textAreaId+'">';
				html+=			'<a  data-toggle="dropdown" href="#" class="pull-left">';
				html+=				'<i class="icon-pencil"></i> ';
				html+=			'</a>';
				html+=			'<ul class="dropdown-menu jlike_edit_dropdown jlike_list_style_type">';
				html+=				'<li id="showEditDeleteButton'+textAreaId+'" tabindex="-1">';
				html+=					'<a id="" class="showEditDeleteButton" onclick="EditComment(this)"><?php echo JText::_('COM_JLIKE_EDIT'); ?></a>';
				html+=				'</li>';
				html+=				'<li id="DeleteButton'+textAreaId+'" tabindex="-1">';
				html+=					'<a id="" class="showEditDeleteButton" onclick="DeleteComment(this)"><?php echo JText::_('COM_JLIKE_DELETE'); ?></a>';
				html+=				'</li>';
				html+=			'</ul>';
				html+=		'</div>';
				html+='		</div>';

				html+=	'<div id="EditComment'+textAreaId+'"  class="jlike_display_block">';

				// SMILEY BUTTON
				html+=		'<div class="jlike_textarea taggable tag_editor editor_0 jl_padding" tag_editor_for="0"  <?php echo $maxlength; ?> id="CommentText'+textAreaId+'" contenteditable="true" required="required" onkeyup="characterLimit(id, <?php echo $maxlength; ?>)" ></div>';

				html+='<div class="jlike_smiley_container">';
				html+=		'<div id='+textAreaId+' class="jlike_smiley jlike_display_inline_blk jlike_btn_container" >';
				html+=			'<button id="jlike_smiley" class="jlike_smiley" alt="" type="button" onClick="javascript:jLikeshowSmiley(this,'+textAreaId+');"></button>';
				html+=		'</div>';
				html+='</div>';
				// SMILEY BUTTON

				//COMMENT & CANCEL BUTTON
				html+= '<div class="row-fluid jlike_comment_padding_top small pull-right">';

				if (owner_reply == 1)
				{
					html+=	"<button type='button' class='btn btn-success btn-small pull-right reviewButton' onclick='SaveNewComment(this, 0, \"<?php echo $likecontainerid; ?>\")'><?php echo JText::_('COM_JLIKE_COMMENT') ?></button>";
				}
				else
				{
					html+=	"<button type='button' disabled='disabled' class='btn btn-success btn-small pull-right reviewButton' onclick='SaveNewComment(this, 2, \"<?php echo $likecontainerid; ?>\")'><?php echo JText::_('COM_JLIKE_REVIEWS') ?></button>";
				}

				html+=	"<button  type='button' class='btn btn-small pull-right jlike_cancel_btn' onclick='CancelNewComment(this)'><?php echo JText::_('COM_JLIKE_CACEL') ?></button> ";
				html+= '</div>';
				//COMMENT & CANCEL BUTTON

				html+=	'</div>';
				html+=			'<input type="hidden" id="comment_id'+textAreaId+'" name="comment_id" value=""/>';
				html+=			'<div class="clearfix"></div>';
				html+=			'<div id="showSavedComment'+textAreaId+'" class="jlike_display_none"></div>';
								//--@ threaded reply button

				html+= '<div class="row-fluid jlike_comment_padding_top small">';
				html+=		'<div class="pull-right">';

				/* Show comment time */
				html+='<span id="jlike_comment_time'+textAreaId+'" class="pull-right jlike_display_none jlike_comment_time">';
				html+='</span>';
				html+=		'</div>';
				html+='</div>';
								//--end threaded reply button
				html+=		'</div>';
				html+='</div>';

			//if parent is present then add after parent
			if(parent_id!=0)
			{
				global_reply_annotation_id=parent_id=parent_id.replace('parentid','');
				techjoomla.jQuery(html).insertAfter('#deleteComment'+parent_id);
			}
			else
			{
				global_reply_annotation_id=0;
				techjoomla.jQuery('#'+likecontainerid  +' div.jlike_comments').prepend(html);
			}

			// Create ratings
			 addRatingStars();
		}


		function ViewMore(data,callFromAscDesc)
		{
			techjoomla.jQuery("#caret").addClass("caret");
			//if call from callFromAscDesc then delete all previous html
			if(callFromAscDesc)
			{
				techjoomla.jQuery("#loadingCommentsProgressBar").hide();

				techjoomla.jQuery('.jlike_commentingArea').remove();
				renderedMoreId=[];
				pageLoadAnnotaionIds='';

				//commentscount
				result_comment_count=<?php
									if(!empty($this->reviews_count[1]))
										echo $this->reviews_count[1];
									else
										echo 0;
									?>;
				techjoomla.jQuery(".comments_count").html('<?php echo JText::_('COM_JLIKE_VIEW_MORE') ?> '+' <?php echo JText::_('COM_JLIKE_VIEW_MORE1') ?>');
			}
			else
			{
				techjoomla.jQuery("#progessBar").hide();
				var comment_limit=<?php echo $comment_limit ? $comment_limit : 5; ?>;
				if(comment_limit<result_comment_count)
					result_comment_count=result_comment_count-comment_limit;
				else
					result_comment_count=0;
				//commentscount
				if(result_comment_count>0)
				{
					techjoomla.jQuery(".comments_count").html('<?php echo JText::_('COM_JLIKE_VIEW_MORE') ?> '+' <?php echo JText::_('COM_JLIKE_VIEW_MORE1') ?>');
				}
			}
			showHideviewCommentsMsg();
			var arrayIndex= renderedMoreId.length;
			for(index = 0; index < data.length; index++)
			{
				renderedMoreId[arrayIndex++]=data[index]['annotation_id'];
			var html='<div id="deleteComment'+data[index]['annotation_id']+'" class="media row-fluid jlike_commentingArea  jlike_renderedComment jlike_comment_border_shadow jlike_commment_padding">';

				html+=	'<a class="pull-left" href="'+data[index]['user_profile_url']+'"><img class="img-circle jlike-img-border" src="'+data[index]['avtar']+'" alt="Smiley face" width="36px" height="auto"></a>';

				html+='<div class="media-body jlike_media_body " >';
				html+=		'<div>';
				html+=			'<a class="pull-left" href="'+data[index]['user_profile_url']+'">'+data[index]['name']+'</a>';
				if(user_id==data[index]['user_id'])
				{
					<?php if ($params->get('jlike_allow_rating_edit') == 1) { ?>
						html+=	'<span id="jlike_show_rating'+data[index]['annotation_id']+'" class="jlike_show_rating pull-left Jlike_user_rating" >';
						html+=	'<div class="basic" data-rating="'+data[index]['rating_upto']+'" data-average="'+data[index]['user_rating']+'" data-id="1"></div>';
						html+=	'</span>';
						html+=		'<div class="jlike_position_relative pull-right">';
						html+=			'<a  class="pull-left" data-toggle="dropdown" href="#">';
						html+=				'<i class="icon-pencil"></i> ';
						html+=			'</a>';
						html+=			'<ul class="dropdown-menu jlike_edit_dropdown jlike_list_style_type">';
						html+=				'<li id="showEditDeleteButton'+data[index]['annotation_id']+'" tabindex="-1">';
						html+=					'<a id="" class="showEditDeleteButton" onclick="EditComment(this)"><?php echo JText::_('COM_JLIKE_EDIT'); ?></a>';
						html+=				'</li>';
						html+=				'<li id="DeleteButton'+data[index]['annotation_id']+'" tabindex="-1">';
						html+=					'<a id="" class="showEditDeleteButton" onclick="DeleteComment(this)"><?php echo JText::_('COM_JLIKE_DELETE'); ?></a>';
						html+=				'</li>';
						html+=			'</ul>';
						html+=		'</div>';
					<?php } else { ?>
						html+=	'<span id="jlike_show_rating'+data[index]['annotation_id']+'" class="jlike_show_rating pull-left Jlike_user_rating" >';
						html+=	'<div class="basic_readonly" data-rating="'+data[index]['rating_upto']+'" data-average="'+data[index]['user_rating']+'" data-id="1"></div>';
						html+=	'</span>';
					<?php } ?>
				 } else {
					html+=	'<span id="jlike_show_rating'+data[index]['annotation_id']+'" class="jlike_show_rating pull-left Jlike_user_rating" >';
					html+=	'<div class="basic_readonly" data-rating="'+data[index]['rating_upto']+'" data-average="'+data[index]['user_rating']+'" data-id="1"></div>';
					html+=	'</span>';
				 }
				html+=		'</div>';
				html+=		'<div class="viewMoreReviews">';
				html+=			'<div id="showlimited'+data[index]['annotation_id']+'" class=" jlike_comment_padding">';


								var smileyannotation=(data[index]['smileyannotation']);
								//var smileyannotation=nl2br(data[index]['smileyannotation']);
								var temp_str	=	smileyannotation.replace(/(<([^>]+)>)/ig,"");


								if(temp_str.length	>=	165){
								html	+=	html_substr( smileyannotation, 165 )+'...';
								}else{
									html	+=	smileyannotation;
								}

				html+=				'<a  class="jlike_pointer"  onclick="showFullComment('+data[index]['annotation_id']+')">';
								if(temp_str.length>=165){
									html+='<?php echo JText::_('COM_JLIKE_SEE_MORE'); ?>';
								}


				html+=					'</a>';
				html+=			'</div>';
				html+=			'<div id="showlFullComment'+data[index]['annotation_id']+'" class=" jlike_display_none jlike_comment_padding">';
				html+=					data[index]['smileyannotation']+' &nbsp';
				html+=				'<a class="jlike_pointer"  onclick="showLimitedComment('+data[index]['annotation_id']+')"><br/> <?php echo JText::_('COM_JLIKE_SEE_LESS'); ?></a>';
				html+=			'</div>';
					 if(user_id==data[index]['user_id'])
					 {
						html+='<div id="EditComment'+data[index]['annotation_id']+'" class="jlike_display_none" >';

						html+=	'<div class="jlike_textarea taggable"  <?php echo $maxlength; ?> id="CommentText'+data[index]['annotation_id']+'" contenteditable="true" required="required" onkeyup="characterLimit(id, <?php echo $maxlength; ?>)">'+data[index]['annotation']+'</div>';
						html+=	'<div class="jlike_smiley_container">';
						html+=		'<div id="'+data[index]['annotation_id']+'" class="jlike_smiley jlike_display_inline_blk jlike_btn_container" >';
						html+=			'<button id="jlike_smiley" class="jlike_smiley" alt="" type="button" onClick="javascript:jLikeshowSmiley(this,'+data[index]['annotation_id']+');"></button>';
						html+=		'</div>';
						html+=	'</div>';

						html+=	'<div id="displaytagsfor_CommentText'+data[index]['annotation_id']+'" class="displayme_CommentText"></div>';

						html+='</div>';
					 }
					 else
					{
						html+=	'<div id="CommentText'+data[index]['annotation_id']+'" class="jlike_display_none jlike_textarea taggable" <?php echo $maxlength; ?> contenteditable="true" required="required" onkeyup="characterLimit(id, <?php echo $maxlength; ?>)"> '+data[index]['annotation']+'</div>';
						html+=	'<div id="displaytagsfor_CommentText'+data[index]['annotation_id']+'" class="displayme_CommentText"></div>';

					}

				html+=	'<div class="row-fluid jlike_comment_padding_top ">';
				html+=		'<span class="small">';
				json_array_children=JSON.stringify(data[index]['children']);
				json_array_children=json_array_children.replace(/\s+/g, '--');

				//~ if(allow_threaded_comment)
				//~ {
				//~ html+=	'<a id="parentid'+data[index]['annotation_id']+'" class="jlike_pointer" onclick="jlike_reply(this,8,92,1,1)" ><?php echo JText::_('COM_JLIKE_REPLY_BTN') ?></a>';
				//~ }
//~
				//~ if(allow_threaded_comment )
				//~ {
					//~ html+='<span id="parentid_show_reply'+data[index]['annotation_id']+'" class="jlike_pointer"';
					//~ html+="onclick=show_reply(this,"+json_array_children+",8,92,1,0) ><div class='jlike_count_box'>"+data[index]['replycount']+"</div></span>";
					//~ html+='<span id="nbspId'+data[index]['annotation_id']+'" > &nbsp; </span>';
				//~ }

				//~ //like unlike button code start
				//~ <?php if($params->get('like_dislike_comments'))
				//~ { ?>
//~
				//~ if(data[index]['likeCount'])
					//~ var likeCount=data[index]['likeCount'];
				//~ else
					//~ var likeCount=0;
//~
				//~ html+='<span>';
				//~ html+=	'<span id="jlike_like_count_area'+data[index]['annotation_id']+'">';
//~
				//~ html+=				'<a id="like_annotationid'+data[index]['annotation_id']+'" class="jlike_like_btn jlike_pointer " onclick="increaseLikeCount(this)" >';
				//~ html+=					'<span id="like_unlike'+data[index]['annotation_id']+'">';
											//~ if(data[index]['userLikeDislike']==1){
				//~ html+=							'<?php	echo JText::_('COM_JLIKE_UNLIKE_BTN'); ?>';
											//~ }
											//~ else{
				//~ html+=							'<?php	echo JText::_('COM_JLIKE_LIKE_BTN');?>';
											//~ }
				//~ html+=					'</span>';
				//~ html+=				'</a>';
//~
				//~ html+='<span id="commentid'+data[index]['annotation_id']+'" onclick="getUsersByCommentId('+data[index]['annotation_id']+','+1+')" class="user_liked jlike_pointer">';
				//~ html+=		'<div class="jlike_count_box" id="like_count'+data[index]['annotation_id']+'" >'+likeCount+'</div>';
				//~ html+=	'</span>';
				//~ html+='</span>';
//~
				//~ html+='</span>';
					//~ <?php if($params->get('show_comment_dislike_button')){ ?>
				//~ if(data[index]['dislikeCount'])
					//~ var dislikeCount=data[index]['dislikeCount'];
				//~ else
					//~ var dislikeCount=0;
//~
//~
				//~ html+='<span class="jlike_margin_from_left">';
//~
				//~ html+=					'<a id="dislike_annotationid'+data[index]['annotation_id']+'" class="jlike_dislike_btn jlike_pointer" onclick="increaseDislikeCount(this)" >';
				//~ html+=						'<span id="dislike_undislike'+data[index]['annotation_id']+'">';
											//~ if(data[index]['userLikeDislike']==2){
				//~ html+=							'<?php echo JText::_('COM_JLIKE_UNDISLIKE_BTN');?>';
											//~ }
											//~ else{
				//~ html+=							'<?php echo JText::_('COM_JLIKE_DISLIKE_BTN');?>';
											//~ }
				//~ html+=						'</span>';
				//~ html+=					'</a>';
//~
				//~ //@S Dislike Count
				//~ html+=	'<span id="dislike_count'+data[index]['annotation_id']+'" onclick="getUsersByCommentId('+data[index]['annotation_id']+','+0+')" class="jlike_pointer jlike_count_box" >'+dislikeCount;
				//~ html+=	'</span>';
				//~ //@E Dislike Count
									//~ <?php } ?>
						//~ <?php
				//~ } ?>
				if(user_id==data[index]['user_id'])
				{
					html+=	'<span class="pull-right jlike_review_button">';
					html+=		'<span id="jlike_cancel_comment_btn'+data[index]['annotation_id']+'" class=" jlike_cancel_comment_btn jlike_display_none">';

					html+=			'<button type="button" class="btn btn-small jlike_cancelbtn" onclick="Cancel('+data[index]['annotation_id']+')"> Cancel</button> &nbsp;';

					html+=			'<button type="button" class="btn btn-success btn-small jlike_commentbtn" onclick="SaveEditedComment('+data[index]['annotation_id']+','+data[index]['annotation_id']+')"> Review</button>';

					html+=	'</span>';
				}
				//like unlike button code end
				<!--Show comment time -->
				html+=	'<span  id="jlike_comment_time'+data[index]['annotation_id']+'" class="pull-right jlike_comment_time" >';
				html+=		data[index]['date'];
				html+=		data[index]['time'];
				html+=	'</span>';

				html+=		'</div>';
				html+=	'</div>';
				html+='</div>';
				html+='</div>';
				html+='</div>';
			techjoomla.jQuery('div.jlike_comments').append(html);
			//alert(11)
			addRatingStars();
			}
		}


		/**
		method to show all comments
		*/
		function showAllComments(ordering,callFromAscDesc)
		{
			techjoomla.jQuery("#caret").removeClass("caret");
			if(callFromAscDesc)
			{
				techjoomla.jQuery("#loadingCommentsProgressBar").show();
			}
			else
			{
				techjoomla.jQuery("#progessBar").show();
			}
			//get the default sorting
			if(!ordering)
			{
				ordering=techjoomla.jQuery('#sorting').val();
			}
			//load the more comments for view more
			techjoomla.jQuery.ajax({
				url:'<?php echo JUri::root();?>index.php?option=com_jlike&task=LoadReviews&tmpl=component&format=row',
				type:'POST',
				dataType:'json',
				data:
				{
					annotaionIdsArr:pageLoadAnnotaionIds,
					viewmoreId:renderedMoreId,
					element_id:<?php echo $this->urldata->cont_id ;?>,
					element:'<?php echo $this->urldata->element; ?>',
					sorting:ordering,
					callIdetity:callFromAscDesc,
					getchildren:0,
					childrensId:0,
				},
				success:function(data){
					ViewMore(data,callFromAscDesc);
				}
			});
		}


		/** DONE
		 * This
		 * - shows jlike_smiley box when clicked on smiely icon in chatbox window
		 *
		 * @param htmlElement selector
		 **/
		function jLikeshowSmiley(selector,textAreaId)
		{
			selector_id=techjoomla.jQuery(selector).parent().attr("id");

			site_link='<?php echo JUri::base();?>';
			if (techjoomla.jQuery(selector).parent().find(".jlike_smileybox").css("display") == 'block')
			{
				techjoomla.jQuery(selector).parent().find(".jlike_smileybox").css("display", "none");
				return false;
			}
			if (jLikeSmilehtml != null)
			{
				techjoomla.jQuery(selector).parent().html(jLikeSmilehtml);
				return;
			}
			techjoomla.jQuery.ajax(
				{
					url: site_link + "components/com_jlike/assets/smileys.txt",
					success: function (data)
					{
						JLikeSmilebackhtml = data;
						var smileyarr = data.split("\n");

						jLikeSmilehtml = '<button onclick="javascript:jLikeshowSmiley(this);" alt="" class="jlike_smiley" id="jlike_smiley" type="button"></button><div class=jlike_smileybox><table><tr>';
						var getsmiledata = new Array();
						for (var i = 0; i < smileyarr.length - 1; i++)
						{
							var getdata = smileyarr[i].split("=");
							getsmiledata.push(getdata[1]);
						}
						getsmiledata = jbunique(getsmiledata);
						for (var i = 0; i < getsmiledata.length; i++)
						{
							if ((i % 2 == 0) && (i != 0))
							{
								jLikeSmilehtml += '</tr><tr>';
							}
							jLikeSmilehtml += '<td><img src="' + site_link + 'components/com_jlike/assets/images/smileys/' + getsmiledata[i] + '"  onClick="javascript:jLikeSmileyClicked(this);" class="jlike_smiley"/></td>';
						}
						jLikeSmilehtml += '</tr></table></div>';
						techjoomla.jQuery(selector).parent().html(jLikeSmilehtml);
					}
				});
				return false;
		}
		/**
		Method to save the newly added comment
		*/
		function SaveNewComment(selector, reviewsOrComment, likecontainerid)
		{
			// CommentText
			var elementId=elementId=techjoomla.jQuery(selector).parent().parent().attr("id");
			textAreaId=elementId.replace('EditComment','');

			url='<?php echo $this->urldata->url; ?>';
			element='<?php echo $this->urldata->element; ?>';
			title='<?php echo $this->urldata->title; ?>';

			var comment_id=techjoomla.jQuery("#comment_id"+textAreaId).val();// changed by Vaishali

			var comment= (techjoomla.jQuery("#CommentText"+textAreaId).html()).trim();// changed by Vaishali

			comment = comment.replace(/<\/div>/g,"\r\n");
			comment = comment.replace(/<div>/g,"");
			comment = comment.replace(/<br>/g,'\n');;
			comment = comment.stripTags();
			//remove white sapaces to check comment is entered or not
			var comment_no_white_space	=	comment.replace(/\s+/g,'');
			if(comment_no_white_space.length	<=	0)
			{
				alert('<?php echo JText::_('COM_JLIKE_REVIEW_BLANK'); ?>');
				return false;
			}

				//Normal comment
				var response;
				if(comment_id)
				{
					SaveEditedComment(comment_id,textAreaId);
				}
				else
				{
					techjoomla.jQuery('#EditComment'+textAreaId).hide();
					techjoomla.jQuery('#jlike_comment_time'+textAreaId).show();

					var extraParams = {'plg_name': jLikeVal[likecontainerid]['plg_name'],'plg_type': jLikeVal[likecontainerid]['plg_type']};

					techjoomla.jQuery.ajax({
						url:'<?php echo JUri::root();?>index.php?option=com_jlike&task=SaveNewComment&tmpl=component&format=row',
						type:'POST',
						dataType:'json',
						data:{
							comment:comment,
							element_id:<?php echo $this->urldata->cont_id ;?>,
							note_type:reviewsOrComment,
							element:element,
							url:url,
							title:title,
							plg_name:'<?php echo $this->urldata->plg_name;?>',
							parent_id:global_reply_annotation_id,
							extraParams:extraParams
						},
						success:function(data){
							var res=parseInt(data);
							if(res)
							{

								//increment comments count after adding new comments
								// if(global_reply_annotation_id==0)
								Originat_comment_count=Originat_comment_count+1;
								techjoomla.jQuery(".jlike_review_display").attr('style','display:none;');
								techjoomla.jQuery(".count_reviews").html(Originat_comment_count);


								//store the newly added comment id in array for view more
								var arrayIndex= renderedMoreId.length;
								renderedMoreId[arrayIndex++]=res;
								response_id=res;
								simely_textarea_id=res;
								techjoomla.jQuery('#comment_id'+textAreaId).attr("id","comment_id"+res);

								techjoomla.jQuery('#comment_id'+res).val(res);


								techjoomla.jQuery('#showEditDeleteButton'+textAreaId).attr("id","showEditDeleteButton"+res);
								techjoomla.jQuery('#showEditDeleteButton'+res).show();

								techjoomla.jQuery('#DeleteButton'+textAreaId).attr("id","DeleteButton"+res);
								techjoomla.jQuery('#DeleteButton'+res).show();

								techjoomla.jQuery('#showSavedComment'+textAreaId).attr("id","showSavedComment"+res);

								techjoomla.jQuery('#showSavedComment'+res).html(comment);
								techjoomla.jQuery('#showSavedComment'+res).show();

								techjoomla.jQuery('#EditComment'+textAreaId).attr("id","EditComment"+res);
								techjoomla.jQuery('#jlike_comment_time'+textAreaId).attr("id","jlike_comment_time"+res);
								techjoomla.jQuery('#jlike_cancel_comment_btn'+textAreaId).attr("id","jlike_cancel_comment_btn"+res);

								techjoomla.jQuery('#displaytagsfor_CommentText'+textAreaId).attr("id","displaytagsfor_CommentText"+res);

								techjoomla.jQuery('#deleteComment'+textAreaId).attr("id","deleteComment"+res);

								techjoomla.jQuery('#CommentText'+textAreaId).attr("id","CommentText"+res);

								techjoomla.jQuery('#'+textAreaId).attr("id",res);

								techjoomla.jQuery('#deleteComment'+res).css('background-color','white');

								techjoomla.jQuery('#deleteComment'+res).removeClass('jlike_add_comment');
								techjoomla.jQuery('#deleteComment'+res).addClass('jlike_saved_comment');

								techjoomla.jQuery('#parentid'+textAreaId).attr("id","parentid"+res);

								techjoomla.jQuery('#editingOptions'+textAreaId).attr("id","editingOptions"+res);
								techjoomla.jQuery('#editingOptions'+res).show();

								techjoomla.jQuery('#deleteComment'+res).removeClass('clone_othere_reply_box');
								//show reply btn after saving the comment
								techjoomla.jQuery('#parentid'+res).show();
								replaceSmielyAsImage(0,0);

								//parsetag(comment);

								//show like dislike button
								techjoomla.jQuery('#like_count'+textAreaId).attr("id","like_count"+res);
								techjoomla.jQuery('#like_annotationid'+textAreaId).attr("id","like_annotationid"+res);
								techjoomla.jQuery('#like_unlike'+textAreaId).attr("id","like_unlike"+res);
								techjoomla.jQuery('#dislike_count'+textAreaId).attr("id","dislike_count"+res);
								techjoomla.jQuery('#dislike_annotationid'+textAreaId).attr("id","dislike_annotationid"+res);
								techjoomla.jQuery('#dislike_undislike'+textAreaId).attr("id","dislike_undislike"+res);
								techjoomla.jQuery('#like_dislike_button'+textAreaId).attr("id","like_dislike_button"+res);
								techjoomla.jQuery('#like_dislike_button'+res).show();
								techjoomla.jQuery('.jlike_user_name_btn_block').show();

								//like dislike icon id
								techjoomla.jQuery('#jlike_like_count_area'+textAreaId).attr("id","jlike_like_count_area"+res);
								techjoomla.jQuery('#jlike_dislike_count_area'+textAreaId).attr("id","jlike_dislike_count_area"+res);

								//show comment date time
								techjoomla.jQuery('#jlike_comment_time'+textAreaId).attr("id","jlike_comment_time"+res);

								techjoomla.jQuery('#jlike_comment_time'+res).show();
								var d=new Date();
								var day=d.getDate();
								var year=d.getFullYear();
								var h=d.getHours();
								var m=d.getMinutes();
								var monthNames = [ "Jan", "Feb", "Mar", "Apr", "May", "June",
									"Jul", "Aug", "Sept", "Oct", "Nov", "Dec" ];
								var month = monthNames[d.getMonth()];
								techjoomla.jQuery('#jlike_comment_time'+res).text(day+' '+month+' '+year+' <?php echo JText::_('COM_JLIKE_COMMENT_DATE_TIME_SEPERATOR'); ?> '+h+':'+m);
							}
						}
					});
				}
		}
		/**
		Method to Edit the comment
		*/
		function SaveEditedComment(annotation_id,textAreaId)
		{
			var comment = (techjoomla.jQuery("#CommentText"+textAreaId).html()).trim();

			// For new line replace end div with \r\n
			comment = comment.replace(/<\/div>/g,"\r\n");

			//comment = comment.replace(/<div>/g,"");
			//comment = comment.replace(/<br>/g,'\r\n');;

			//Strip tag because html not allowed in commnet
			comment = strip_tags(comment);

			if(!comment)
			{
				alert('<?php echo JText::_('COM_JLIKE_REVIEW_BLANK'); ?>');
				return false;
			}


			techjoomla.jQuery('#EditComment'+textAreaId).hide();
			techjoomla.jQuery('#jlike_comment_time'+textAreaId).show();
			techjoomla.jQuery('#jlike_cancel_comment_btn'+textAreaId).hide();

			simely_textarea_id=textAreaId;
			var res=0;
			techjoomla.jQuery.ajax({
				url:'<?php echo JUri::root();?>index.php?option=com_jlike&task=SaveComment&tmpl=component&format=row',
				type:'POST',
				dataType:'json',
				data:
				{
					annotation_id:annotation_id,
					comment:comment,
					note_type:2,
				},
				success:function(data){
					res=parseInt(data);
					if(res==1)
					{
						techjoomla.jQuery('#showlFullComment'+textAreaId).hide();
						techjoomla.jQuery('#showlimited'+textAreaId).show();

						//For new comment after edited
						techjoomla.jQuery('#showEditDeleteButton'+textAreaId).show();
						techjoomla.jQuery('#showSavedComment'+textAreaId).show();
						techjoomla.jQuery('#editingOptions'+textAreaId).show();
						replaceSmielyAsImage(0,0);

						//show comment date time
						techjoomla.jQuery('#jlike_comment_time'+textAreaId).attr("id","jlike_comment_time"+res);

						techjoomla.jQuery('#jlike_comment_time'+res).show();
						var d=new Date();
						var day=d.getDate();
						var year=d.getFullYear();
						var h=d.getHours();
						var m=d.getMinutes();
						var monthNames = [ "Jan", "Feb", "Mar", "Apr", "May", "June",
							"Jul", "Aug", "Sept", "Oct", "Nov", "Dec" ];
						var month = monthNames[d.getMonth()];
						techjoomla.jQuery('#jlike_comment_time'+res).text(day+' '+month+' '+year+' <?php echo JText::_('COM_JLIKE_COMMENT_DATE_TIME_SEPERATOR'); ?> '+h+':'+m);
					}
				}
			});
		}

	/***************************************************
	STRIP HTML TAGS
	****************************************************/
	function strip_tags(html)
	{
		//PROCESS STRING
		if(arguments.length < 3) {
			html=html.replace(/<\/?(?!\!)[^>]*>/gi, '');
		} else {
			var allowed = arguments[1];
			var specified = eval("["+arguments[2]+"]");
			if(allowed){
				var regex='</?(?!(' + specified.join('|') + '))\b[^>]*>';
				html=html.replace(new RegExp(regex, 'gi'), '');
			} else{
				var regex='</?(' + specified.join('|') + ')\b[^>]*>';
				html=html.replace(new RegExp(regex, 'gi'), '');
			}
		}

		//CHANGE NAME TO CLEAN JUST BECAUSE
		var clean_string = html;

		//RETURN THE CLEAN STRING
		return clean_string;
	}

		function replaceSmielyAsImage(method,data)
		{
			if(method) //if this function call from ViewMore() method
			{
				var comment= data;
			}
			else //call from other methods
			{
				var comment=techjoomla.jQuery("#CommentText"+simely_textarea_id).html();
				//var comment= document.getElementById("CommentText"+simely_textarea_id).value;
			}
			var site_url='<?php echo JUri::root(); ?>';

			var replacement = {
					":)": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/smile.jpg" />',
					":-)": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/smile.jpg" />',
					":(": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/sad.jpg" />',
					":-(": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/sad.jpg" />',
					";)": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/wink.jpg" />',
					";-)": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/wink.jpg" />',
					";(": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/cry.jpg" />',
					"B-)": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/cool.jpg" />',
					"B)": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/cool.jpg" />',
					":D": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/grin.jpg" />',
					":-D": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/grin.jpg" />',
					":o": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/shocked.jpg" />',
					":0": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/shocked.jpg" />',
					":-o": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/shocked.jpg" />',
					":-0": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/shocked.jpg" />',
					":-3": '<img src="'+site_url+'components/com_jlike/assets/images/smileys/love.png" />',
			};

			string = escape(comment);
			for (var val in replacement)
			string = string.replace(new RegExp(escape(val), "g"), replacement[val]);
			string = unescape(string);

			if(method)
			{
				return string;
			}
			else
			{
			techjoomla.jQuery('#showlimited'+simely_textarea_id).html(string);
			techjoomla.jQuery('#showlFullComment'+simely_textarea_id).html(string);
			techjoomla.jQuery('#showSavedComment'+simely_textarea_id).html(string);
			}
		}



		/*replace tags as user link*/
		function parsetag(comment)
		{
			var site_url='<?php echo JURI::root(); ?>';
			var comment=techjoomla.jQuery("#CommentText"+simely_textarea_id).html();
			var regex = /{profiletag([^}]*)}/;
			var results = comment.match(regex);

			var matched = null;
			while (matched = regex.exec(comment))
			{
				var data = matched[1].split('|');
				var profileurl = site_url+'index.php?option=com_community&view=profile&userid='+data[0];
				comment = comment.replace(results, '<a href="'+profileurl+'">'+data[1]+'</a>');
			}
			techjoomla.jQuery('#showlimited'+simely_textarea_id).html(comment);
			techjoomla.jQuery('#showlFullComment'+simely_textarea_id).html(comment);
			techjoomla.jQuery('#showSavedComment'+simely_textarea_id).html(comment);
		}
		/**
		Method to delete the comment
		*/
		function DeleteComment(selector)
		{
			var cnfrm=confirm('<?php echo JText::_('COM_JLIKE_DELETE_COMMENT_MSG'); ?>');
			if(!cnfrm)
				return false;
			var elementId=techjoomla.jQuery(selector).parent().attr("id");
			elementId=elementId.replace('DeleteButton','');

			techjoomla.jQuery.ajax({
				url:'<?php echo JUri::root();?>index.php?option=com_jlike&task=DeleteReviews&annotation_id='+elementId+'&tmpl=component&format=row',
				type:'GET',
				dataType:'json',
				success:function(data){
					if(data)
					{
						for(var i=0;i<data.length;i++)
						{
							techjoomla.jQuery('#deleteComment'+data[i]).remove();
						}
						Originat_comment_count=Originat_comment_count-1;
						techjoomla.jQuery(".jlike_review_display").attr('style','display:block;');
						techjoomla.jQuery(".count_reviews").html(Originat_comment_count);

					}
				}
			});
		}

		// Function to restrict character length
		function characterLimit(fieldId, allowedLength)
		{
			var content = jLike.jQuery("#"+fieldId).html().stripTags();

			if(allowedLength<content.length)
			{
				//get only allowed characters & remove exceeded character
				jLike.jQuery("#"+fieldId).html(jLike.jQuery("#"+fieldId).html().substr(0,allowedLength));
				alert("<?php echo JText::_("COM_JLIKE_CHAR_LENGTH"); ?>");
			}

		}

</script>
<?php
	JHtml::stylesheet(JUri::root(). 'components/com_jlike/assets/css/jRating.jquery.css' );
	require_once(JPATH_SITE . DS . 'components' . DS . 'com_jlike' . DS . 'views' . DS . 'jlike' . DS . 'tmpl' . DS . 'jRating.php');
	JHtml::script( JUri::root().'components/com_jlike/assets/scripts/jrating.js' );
?>
