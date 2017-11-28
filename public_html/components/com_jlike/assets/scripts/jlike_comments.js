
		function jLikePasteHtmlAtCaret(html) {
			var jlikeSel, jLikeRange;
			if (window.getSelection) {
				// IE9 and non-IE
				jlikeSel = window.getSelection();

				if (jlikeSel.getRangeAt && jlikeSel.rangeCount) {
					jLikeRange = jlikeSel.getRangeAt(0);
					jLikeRange.deleteContents();

					// jLikeRange.createContextualFragment() would be useful here but is
					// non-standard and not supported in all browsers (IE9, for one)
					var jlikeEl = document.createElement("div");
					jlikeEl.innerHTML = html;

					var frag = document.createDocumentFragment(), node, lastNode;
					//console.log(frag);
					while ( (node = jlikeEl.firstChild) ) {
						lastNode = frag.appendChild(node);
					}
					jLikeRange.insertNode(frag);

					// Preserve the selection
					if (lastNode) {
						jLikeRange = jLikeRange.cloneRange();
						jLikeRange.setStartAfter(lastNode);
						jLikeRange.collapse(true);
						jlikeSel.removeAllRanges();
						jlikeSel.addRange(jLikeRange);
					}
				}
			} else if (document.selection && document.selection.type != "Control") {
				// IE < 9
				document.selection.createRange().pasteHTML(html);
			}
		}






		function showHideviewCommentsMsg()
		{
			var result=parseInt(Originat_comment_count)-parseInt(result_comment_count);
			if(result_comment_count)
			{
				techjoomla.jQuery("#viewCommentsMsg").show();
			}else
			{
				techjoomla.jQuery("#viewCommentsMsg").hide();
			}
		}
		/**
		setDecending sort comment in decending order of date (Latest)
		*/
		function setDecending(likecontainerid)
		{
			techjoomla.jQuery("#"+likecontainerid + " #lioldest").removeClass("active");
			techjoomla.jQuery("#"+likecontainerid + " #lilatest").addClass("active");
			techjoomla.jQuery("#"+likecontainerid +  ' #sorting').val(1);
			showAllComments(1,1);
		}
		/**Ascending sort comment in asending order of date (Olderst)
		*/
		function setAscending(likecontainerid)
		{
			techjoomla.jQuery("#"+likecontainerid + " #lilatest").removeClass("active");
			techjoomla.jQuery("#"+likecontainerid + " #lioldest").addClass("active");
			techjoomla.jQuery("#"+likecontainerid + ' #sorting').val(2);
			showAllComments(2,1);
		}

		function nl2br (str, is_xhtml) {
		  var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display
		  return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
		}


		/**
		method CancelNewComment cancel comment New added
		*/
		function CancelNewComment(selector)
		{
			var elementId=elementId=techjoomla.jQuery(selector).parent().parent().attr("id");
			textAreaId=elementId.replace('EditComment','');
			//Comment Not saved remove comment area
			if(textAreaId==0)
			{
				techjoomla.jQuery('#deleteComment'+textAreaId).remove();
			}
			else
			{
				techjoomla.jQuery('#EditComment'+textAreaId).hide();
				techjoomla.jQuery('#jlike_comment_time'+textAreaId).show();
				techjoomla.jQuery('#jlike_cancel_comment_btn'+textAreaId).hide();
				techjoomla.jQuery('#showEditDeleteButton'+textAreaId).show();
				techjoomla.jQuery('#showSavedComment'+textAreaId).show();
				replaceSmielyAsImage();
			}
		}
		/**
		*/
		function Cancel(textAreaId)
		{
			techjoomla.jQuery('#EditComment'+textAreaId).hide();
			techjoomla.jQuery('#jlike_comment_time'+textAreaId).show();
			techjoomla.jQuery('#jlike_cancel_comment_btn'+textAreaId).hide();
			techjoomla.jQuery('#showlFullComment'+textAreaId).hide();
			techjoomla.jQuery('#showlimited'+textAreaId).show();
			return false;
		}

		/** DONE
		 * This
		 * - returns unique array for given array
		 *
		 * @param array arrayName
		 * @return array newArray
		 *
		 * */

		function jbunique(arrayName)
		{
			var newArray = new Array();
			label: for (var i = 0; i < arrayName.length; i++)
			{
				for (var j = 0; j < newArray.length; j++)
				{
					if (newArray[j] == arrayName[i]) continue label;
				}
				newArray[newArray.length] = arrayName[i];
			}
			return newArray;
		}
		/** DONE
		 * This
		 * - hides smileybox when clicked on a smiley
		 * - pushes smiley code in textinput area
		 *
		 * @param htmlElement selector
		 *
		 * */

		/* This - hides smileybox when clicked on a smiley - pushes smiley code in textinput area
		 * @param htmlElement selector
		 **/
		function jLikeSmileyClicked(selector)
		{
			techjoomla.jQuery(selector).parent().parent().parent().parent().parent().hide();
			var jlikeSrcarr = techjoomla.jQuery(selector).attr("src").split("/");
			if (JLikeSmilebackhtml != null)
			{
				var jLikeSmileyarr = JLikeSmilebackhtml.split("\n");
				for (var i = 0; i < jLikeSmileyarr.length; i++)
				{
					var getdata = jLikeSmileyarr[i].split("=");
					if (getdata[1] == jlikeSrcarr[jlikeSrcarr.length - 1])
					{
						document.getElementById('CommentText'+selector_id).focus();
						jLikePasteHtmlAtCaret(getdata[0]);
						break;
					}
				}
				return;
			}
		}
		/**
		show comment
		*/
		function showFullComment(textAreaId)
		{
			techjoomla.jQuery('#showlimited'+textAreaId).hide();
			techjoomla.jQuery('#showlFullComment'+textAreaId).show();
			return false;
		}
		function showLimitedComment(textAreaId)
		{
			techjoomla.jQuery('#showlFullComment'+textAreaId).hide();
			techjoomla.jQuery('#showlimited'+textAreaId).show();
			return false;
		}

		function EditComment(selector)
		{
			var elementId=techjoomla.jQuery(selector).parent().attr("id");
			elementId=elementId.replace('showEditDeleteButton','');

			techjoomla.jQuery('#showlimited'+elementId).hide();
			techjoomla.jQuery('#showlFullComment'+elementId).hide();
			techjoomla.jQuery('#EditComment'+elementId).show();
			techjoomla.jQuery('#jlike_comment_time'+elementId).hide();
			techjoomla.jQuery('#jlike_cancel_comment_btn'+elementId).show();
			techjoomla.jQuery('#showSavedComment'+elementId).hide();
		}

		/**
		onlick of show reply display replys
		**/

		function show_reply(element_ref,children,margin_left,width,padding_left,threadlevel)
		{
			element_ref.onclick='';
			if(!children.length>0)
			{
				return;
			}
			threadlevel=threadlevel+1;
			nbspId=(element_ref.id).replace('parentid_show_reply','');

			var commentbtn_margin=29;

			//for(index = 0; index < children.length; index++)
			{
				var style='margin-left:'+margin_left+'%; width:'+width+'%;';
				var padding_left=1;
				var i=0;
			}

			printingRecursiveChildren(children,style,margin_left,width,padding_left,commentbtn_margin,i,threadlevel);
		}
		/**
		Method for jlike reply button open textarea for reply
		**/
		function jlikeReplyCallFromAddcomment(parent_ref,margin_left,width,commentbtn_margin,threadlevel)
		{
			margin_left=margin_left+8;
			width=width-8;
			commentbtn_margin=0;
			jlike_reply(parent_ref,margin_left,width,commentbtn_margin,threadlevel);
		}
