<?php $loginuserflag= $this->reviews_count_loginuser[0] ? $this->reviews_count_loginuser[0] : 0;   ?>
<form action="<?php echo $this->urldata->url; ?>" method="post" id="jlike_reviews" name="jlike_reviews" enctype="multipart/form-data">
	<div class="row-fluid jlike_comments_container jlike_text_decoration">
		<?php
		if($this->urldata->show_reviews== 1)
		{ ?>
		<div class="jlike-comment-header">
			<div class="jlike_commentBox ">
				<div class="pull-left">
					<div class="jlike_comment_sort">
						<a data-toggle="dropdown" href="#" class="jlike_comment_msg">
							<i class="jlike_icon_comment_pos icon-comments"></i>
							<span id="total_comments" class="count_reviews"><?php echo $flag= $this->reviews_count[0] ? $this->reviews_count[0] : 0;   ?></span>
							<span ><?php  echo $this->urldata->show_reviews ?  JText::_('COM_JLIKE_REVIEWS'): '<a href="'.$this->urldata->url.'" title="Click here to add Reviews"> '.JText::_('COM_JLIKE_REVIEWS').'</a>' ?></span>
							<?php if($flag): ?>
							<span class="caret jlike_caret_margin"></span>
							<?php endif; ?>
						</a>

						<?php if($flag): ?>
						<ul class="dropdown-menu jlike_list_style_type">
							<li id="lioldest" tabindex="-1">
								<a id="alatest"class="showEditDeleteButton" onclick="setAscending('<?php echo $likecontainerid;?>')"><?php echo JText::_('COM_JLIKE_SET_OLDEST'); ?></a>
							</li>
							<li id="lilatest" tabindex="-1">
								<a id="aoldest"class="showEditDeleteButton" onclick="setDecending('<?php echo $likecontainerid;?>')"><?php echo JText::_('COM_JLIKE_SET_LATEST'); ?></a>
							</li>
						</ul>
						<?php endif; ?>
					</div>
				</div>

				<?php if($loginuserflag): ?>
				<div id="divaddcomment" class="pointer pull-right jlike_review_display" style="display:none;" >
				<?php else :?>
				<div id="divaddcomment" class="pointer pull-right jlike_review_display" >
				<?php endif; ?>
					<div class="jlike_loadbar"> <span id="loadingCommentsProgressBar"></span></div>
					<?php //if($this->urldata->show_reviews==1):
					if ($this->urldata->jlike_allow_rating == 1):?>
					<a class="jlike_comment_msg jlike_comment_padding_right " onclick="addCommentArea('<?php echo $likecontainerid;?>','0','0','97.5',0,0,0)"> <?php echo JText::_('COM_JLIKE_ADD_REVIEWS'); ?> </a>
					<?php endif; ?>
				</div>
				<div style="clear:both"></div>
			</div>
		</div>
		<?php
		} ?>

			<?php
		if ($this->urldata->show_reviews == 1)
		{
			?>
			<!-- ***************************** Rating ********************** -->

			<div class="jlike_comments "><?php
			$i                    = 1;
			$user_comment_present = 0;
			$annotaionIds         = array();

			foreach ($this->reviews as $reviews)
			{
				$annotaionIds[] = $reviews->annotation_id;

				if ($reviews->annotation)
				{
					?><div id="deleteComment<?php echo $reviews->annotation_id; ?>" class="media jlike_commentingArea jlike_renderedComment jlike_no_radius jlike_text_decoration" >

						<hr class="jlike_hr_margin" />

						<a class="pull-left" href="<?php echo $reviews->user_profile_url; ?>">
							<img class="jlike_tp_margin img-circle jlike-img-border" src="<?php echo $reviews->avtar; ?>" alt="Smiley face" width="36px" height="auto">
						</a>

						<div class="media-body jlike_media_body " >
							<span>
								<a class="pull-left" href="<?php echo $reviews->user_profile_url; ?>">
									<?php echo ucwords($reviews->name); ?>
								</a>
								<?php
									if ($loged_user == $reviews->user_id)
									{
										if ($params->get('jlike_allow_rating_edit') == 1)
										{
											?>
											<!-- ***************************** Rating ********************** -->
											<span id="<?php echo 'jlike_show_rating' . $reviews->annotation_id; ?>" class="jlike_show_rating pull-left Jlike_user_rating" >
											<div class="basic" data-rating="<?php echo $reviews->rating_upto;?>" data-average="<?php echo $reviews->user_rating;?>" data-id="1"></div>
											</span>
											<?php
										} else { ?>
											<!-- ***************************** Rating ********************** -->
											<span id="<?php echo 'jlike_show_rating' . $reviews->annotation_id; ?>" class="jlike_show_rating pull-left Jlike_user_rating" >
											<div class="basic_readonly"  data-rating="<?php echo $reviews->rating_upto;?>"  data-average="<?php echo $reviews->user_rating;?>" data-id="1"></div>
											</span>
										<?php
										}
									}
									else
									{
									?>
									<!-- ***************************** Rating ********************** -->

										<span id="<?php echo 'jlike_show_rating' . $reviews->annotation_id; ?>" class="jlike_show_rating pull-left Jlike_user_rating" >
										<div class="basic_readonly"  data-rating="<?php echo $reviews->rating_upto;?>"  data-average="<?php echo $reviews->user_rating;?>" data-id="1"></div>
										</span>
									<?php } ?>

								<?php

								if ($loged_user == $reviews->user_id)
								{
									if ($params->get('jlike_allow_rating_edit') == 1)
									{
									?><div class="jlike_position_relative pull-right">
										<a data-toggle="dropdown" class="pull-left" href="#">
											<i class="icon-pencil" ></i>
										</a>
										<ul class="dropdown-menu jlike_edit_dropdown jlike_list_style_type" >
											<li id="showEditDeleteButton<?php echo $reviews->annotation_id; ?>" tabindex="-1">
												<a class="showEditDeleteButton" onclick="EditComment(this)">
													<?php echo JText::_('COM_JLIKE_EDIT'); ?>
												</a>
											</li>
											<li id="DeleteButton<?php echo $reviews->annotation_id; ?>" tabindex="-1">
												<a class="showEditDeleteButton" onclick="DeleteComment(this)">
													<?php echo JText::_('COM_JLIKE_DELETE'); ?>
												</a>
											</li>
										</ul>
									</div><?php
									}
								}
							?></span>

							<div class="jlike_comment_padding_top">
								<div id="showlimited<?php echo $reviews->annotation_id; ?>" class="showlimited_review">
								<?php
								if (strlen(strip_tags($reviews->smileyannotation)) >= 165)
								{
									echo nl2br(trim($this->jlikehelperObj->getsubstrwithHTML($reviews->smileyannotation, 165, '...', true)));
								}
								else
								{
									echo nl2br(trim($reviews->smileyannotation));
								}
								?>

								<a class="jlike_pointer"  onclick="showFullComment(<?php echo $reviews->annotation_id; ?>)">
									<?php
									if (strlen(strip_tags($reviews->smileyannotation)) >= 165)
									{
										echo JText::_('COM_JLIKE_SEE_MORE');
									}
									?>
								</a>
							</div>

							<div id="showlFullComment<?php echo $reviews->annotation_id; ?>" class="jlike_display_none " >
								<?php echo nl2br(trim($reviews->smileyannotation));?>&nbsp;
								<a class="jlike_pointer" onclick="showLimitedComment(<?php echo $reviews->annotation_id; ?>)">
								<?php
									echo JText::_('COM_JLIKE_SEE_LESS');
								?>
								</a>
							</div>

							<!--comment added user & logged in user are the same then show edit comment-->
							<?php
							if ($loged_user == $reviews->user_id)
							{
								?><div id="EditComment<?php echo $reviews->annotation_id; ?>" class="jlike_display_none" >
									<div class="jlike_textarea taggable" id="CommentText<?php echo $reviews->annotation_id; ?>" contenteditable="true" <?php echo $maxlength; ?> required="required" onkeyup="characterLimit(id, <?php echo $maxlength; ?>)">
										<?php
											echo nl2br(trim($reviews->annotation));
											$user_comment_present = 1;
										?>
									</div>

									<div class="jlike_smiley_container">
										<div id="<?php echo $reviews->annotation_id; ?>" class="jlike_smiley jlike_display_inline_blk jlike_btn_container" >
											<button id="jlike_smiley" class="jlike_smiley " type="button" onClick="javascript:jLikeshowSmiley(this,<?php echo $reviews->annotation_id; ?>);">
											</button>
										</div>
									</div>
								</div><?php
							}
							else
							{ ?>
								<div class="jlike_textarea jlike_display_none taggable"  <?php echo $maxlength; ?> id="CommentText<?php echo $reviews->annotation_id; ?>" contenteditable="true" <?php echo $maxlength; ?>  required="required"><?php
									echo nl2br(trim($reviews->smileyannotation));
									$user_comment_present = 1;
								?></div>
								<div id="displaytagsfor_CommentText<?php echo $reviews->annotation_id; ?>" class="displayme_CommentText"></div><?php
							}
							?>
							<div class="row-fluid jlike_comment_padding_top" >
								<span class="small">
									<?php
									if ($loged_user == $reviews->user_id)
									{
										?>
										<span class="pull-right jlike_review_button">
											<span id="jlike_cancel_comment_btn<?php echo $reviews->annotation_id; ?>" class=" jlike_cancel_comment_btn jlike_display_none" >
												<button type="button" class='btn btn-small jlike_cancelbtn' onclick="Cancel(<?php echo $reviews->annotation_id; ?>)">
													<?php echo JText::_('COM_JLIKE_CACEL'); ?>
												</button>
												<button type="button" class='btn btn-success btn-small jlike_commentbtn' onclick="SaveEditedComment(<?php echo $reviews->annotation_id; ?>,<?php echo $reviews->annotation_id; ?>)">
													<?php echo JText::_('COM_JLIKE_REVIEWS'); ?>
												</button>
											</span>
										</span>
										<?php
									}

									if($params->get('allow_threaded_comment'))
									{ ?>

										<a id="parentid<?php echo $reviews->annotation_id; ?>"
											class="jlike_pointer"
											onclick="jlike_reply(this,'7','<?php echo '80%';?>',37,1)" >
												<?php echo JText::_('COM_JLIKE_REPLY_BTN'); ?>
										</a>
									<?php
									} ?>

									<span id="parentid_show_reply<?php echo $reviews->annotation_id; ?>"
										class="jlike_pointer"
										onclick='show_reply(this,<?php echo (json_encode($reviews->children));?>,8,92,1,0)'>
										<div class="jlike_count_box">
											<?php echo $reviews->replycount; ?>
										</div>
									</span>

									<!--Show rating time -->
									<span id="<?php echo 'jlike_comment_time' . $reviews->annotation_id; ?>" class="jlike_comment_time pull-right" >
										<?php
										echo $reviews->date;
										echo $reviews->time;
										?>
									</span>
								</span>
								<!-- this one-->
							</div>
						</div>
					</div>
					</div> <!-- end of row-fluid--class -->
					<?php
					$i++;
				}
			} //print_r($annotaionIds); //end of foreach for reviews
			?>
			</div> <!-- End of Main comment div-->
			<div style="clear:both"></div>
			<div class="row-fluid">
				<span id="progessBar"></span>
				<!-- @S View More button-->
				<?php
				// Show only when comments on content available more that loaded at page load
				if ($comment_limit < $this->reviews_count[0])
				{ ?>
					<div class="jlike_viewReviewsMsg">
						<div id="viewReviewsMsg" class=" span12 center btn pointer" onclick="showAllReviews(0,0)">
							<span  class="reviews_count"><?php
								echo JText::_('COM_JLIKE_VIEW_MORE') . '  ' . JText::_('COM_JLIKE_VIEW_MORE1');
							?></span>
							<span id="caret" class="caret jlike_caret_margin_top"></span>
						</div>
					</div>
				<?php
				}
				?>
				<div class="clearfix"></div>
				<!-- @E View More button -->

				<!-- show user name in popup who like the comment !-->

				<a id="user_info_modal" href="#like_dislike_users" role="button" class="btn jlike_display_none" data-toggle="modal"></a>

				<!-- Modal -->
				<div id="like_dislike_users" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h3 id="myModalLabel" class="modal_header"></h3>
					</div>
					<div class="modal-body">
						<div id="modalconent" class="modal-body"></div>
					</div>
					<div class="modal-footer">
						<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_JLIKE_CLOSE'); ?></button>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			<input type="hidden" id="sorting" name="sorting" value="1" /><?php
		}
	?>
	</div>
</form>
<?php
if ($this->urldata->show_reviews == 1)
{
	// Require the rating scripting methods file
	require_once(JPATH_SITE . DS . 'components' . DS . 'com_jlike' . DS . 'views' . DS . 'jlike' . DS . 'tmpl' . DS . 'reviews.php');
}
?>
