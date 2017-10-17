<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );
/**
 * @package		jLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

if(!defined('DS')){
       define('DS',DIRECTORY_SEPARATOR);
}

//JHtml::_('behavior.framework',true);
JHtml::_('behavior.modal', 'a.modal');

$params = JComponentHelper::getParams('com_jlike');

$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (JFile::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_jlike');
}

$document=JFactory::getDocument();
$document->addScript(JUri::root().'components/com_jlike/assets/scripts/jlike_distributed.js');
$document->addScript(JUri::root().'components/com_jlike/assets/scripts/jlike_comments.js');
$document->addStylesheet(JUri::root().'components/com_jlike/assets/css/jlike_distributed.css');
//$document->addStylesheet(JUri::root().'components/com_jlike/assets/css/like.css');


//Load bootstrap on joomla > 3.0 ; This option will be usefull if site is joomla 3.0 but not a bootstrap template
if (JVERSION > '3.0')
{
	$load_bootstrap=$params->get('load_bootstrap');
	//check config
	if ($load_bootstrap)
	{
		// Load bootstrap CSS.
		JHtml::_('bootstrap.loadcss');

	}
}

//get looged user details
$oluser = JFactory::getUser();

$userInfo = new StdClass();
$userInfo->id = $loged_user = $oluser->id;
$userInfo->email=$oluser->email;
$userInfo->name=$oluser->name;


$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

if (!class_exists('ComjlikeMainHelper'))
{
	// Require_once $path;
	JLoader::register('ComjlikeMainHelper', $helperPath);
	JLoader::load('ComjlikeMainHelper');
}

$ComjlikeMainHelper = new ComjlikeMainHelper;

$sLibObj  = $ComjlikeMainHelper->getSocialLibraryObject('', array("plg_type" => $this->urldata->plg_name, "plg_name" => $this->urldata->plg_type));

$link = '';
$link = $profileUrl = $sLibObj->getProfileUrl($oluser);

if ($profileUrl)
{
	if (!parse_url($profileUrl, PHP_URL_HOST))
	{
		$link = JUri::root() . substr(JRoute::_($profileUrl), strlen(JUri::base(true)) + 1);
	}
}

$userInfo->user_profile_url = $link;
$userInfo->avtar   = $sLibObj->getAvatar($oluser, 50);

//array of annotation ids for view more
$annotaion_ids=array();

$comment_limit = $params->get('no_of_commets_to_show');
$limit_on_comment_lenght = $params->get('limit_on_comment_lenght');

$comment_length=0;
$maxlength='';
if($limit_on_comment_lenght)
{
	$comment_length=$params->get('comment_length');
	if($comment_length)
		$maxlength='maxlength='.$comment_length;
}
//end comments

$params=$this->params;
$data=$this->data;
$item=$this->buttonset;

$likecontainerid="like-".str_replace('.','-',$this->urldata->element)."-".$this->urldata->cont_id;
$likecontenttitle	=	html_entity_decode(urldecode($this->urldata->title));


$show_annotation_snippet=$display_dislike=$display_pwltcb=0;

$dislike_onload_style="style='display:none'";
if($params->get('allow_user_lables') || $params->get('allow_annotation')){
$show_annotation_snippet=1;
}

if($params->get('allow_dislike')){
		$display_dislike=1;
		$dislike_onload_style="style='display:inline-block'";
}

if(1 == $oluser->guest)
{
	if($params->get('show_users') && $params->get('which_users_to_show')	==	'0')
				$display_pwltcb=1;
}
else
{
	if($params->get('show_users')){
		$display_pwltcb=1;
	}
}

$like_text=$data['likecount'];
$dislike_text=$data['dislikecount'];
if($data['likeaction']=='like')
{
	/*$like_text=$data['likecount'];
	if($data['likecount'])
		$like_text=$data['likecount'];
	else
		$like_text=$data['liketext'];
	*/
	$like_tooltip=$data['liketext'];
}
else
{
	//$like_text=$data['likecount'];
	$like_tooltip=$data['unliketext'];
}
if($data['dislikeaction']=='dislike')
{
	/*if($data['dislikecount'])
		$dislike_text=$data['dislikecount'];
	else
		$dislike_text=$data['disliketext'];*/

	$dislike_tooltip=$data['disliketext'];
}
else{
	//$dislike_text=$data['dislikecount'];
	$dislike_tooltip=$data['undisliketext'];
}
?>
<div class="techjoomla-bootstrap">

<div class="likes" id="<?php echo $likecontainerid;?>">

		<?php if($this->urldata->show_like_buttons == '1')
		{
			// This is for guest user
			if (1 == $oluser->guest)
			{
			?>

					<span id="" class="like-snippet">
						<a href="javascript:void(0);" title="<?php echo $like_tooltip ?>" class="like-snippet-btn  <?php echo 'me'.$data['likeaction'];?> " style="display:<?php echo ($data['likeaction'])? 'inline-block': 'none' ?>" onclick='alert("<?php echo JText::_('COM_JLIKE_MUSTLOGIN');?>")';>
							<span class="fa fa-thumbs-up icon-white"></span>
							<span class="like-snippet-text hidden-phone"><?php echo $data['liketext'];?></span>
							<span class="like-snippet-count" id="likecount"><?php echo $like_text ?></span>
						</a>
						<a href="javascript:void(0);" title="<?php echo $dislike_tooltip ?>"  class="like-snippet-btn <?php echo 'me'.$data['dislikeaction'];?>" <?php echo $dislike_onload_style;?> onclick='alert("<?php echo JText::_('COM_JLIKE_MUSTLOGIN');?>")';>
							<span class="fa fa-thumbs-down icon-white"></span>
							<span class="like-snippet-text hidden-phone"><?php echo $data['disliketext'];?></span>
							<span class="like-snippet-count" id="dislikecount"><?php echo $dislike_text ?></span>
						</a>
					</span><!-- like-snippet -->
				<div style="clear:both"></div>
				<?php
			}
			else
			{?>

				<span id="" class="like-snippet">
					<a href="javascript:void(0);" title="<?php echo $like_tooltip ?>" class="like-snippet-btn <?php echo 'me'.$data['likeaction'];?> " style="display:<?php echo ($data['likeaction'])? 'inline-block': 'none' ?>" onclick="jLike.<?php echo ($data['likeaction'])?>(this,'<?php echo $likecontainerid ;?>','<?php echo $show_annotation_snippet;?>','<?php echo $display_dislike;?>','<?php echo $display_pwltcb;?>');">
						<i class="fa fa-thumbs-up"></i>
						<span class="like-snippet-text hidden-phone"><?php echo $data['liketext'];?></span>
						<span class="like-snippet-count" id="likecount"><?php echo $like_text ?></span>
					</a>
					<a href="javascript:void(0);" title="<?php echo $dislike_tooltip ?>"  class="like-snippet-btn <?php echo 'me'.$data['dislikeaction'];?>" <?php echo $dislike_onload_style;?> onclick="jLike.<?php echo ($data['dislikeaction'])?>(this,'<?php echo $likecontainerid ;?>','<?php echo $show_annotation_snippet;?>','<?php echo $display_dislike;?>','<?php echo $display_pwltcb;?>');">
						<i class="fa fa-thumbs-down"></i>
						<span class="like-snippet-text hidden-phone"><?php echo $data['disliketext'];?></span>
						<span class="like-snippet-count" id="dislikecount"><?php echo $dislike_text ?></span>
					</a>
				</span><!-- like-snippet -->
		<?php } ?>
<?php } ?>
			<!--<div class="pwltcb tjlms-dotted-rightborder" id="pwltcb" style="display:none">
					<ul class="pwltcb_ul">
						<?php
								$pwltcb_cnt=0;
								foreach($this->data['pwltcb'] as $ind=>$obj)
								{
									if($pwltcb_cnt==5)
										break;
									?>
										<li class="pwltcb_li">
											<a title="<?php echo $obj->name ?>" target="_blank"  <?php echo ($obj->link_url)?'href="'.$obj->link_url.'"':''; ?>>
												<img class="pwltcb_img img-circle" src="<?php echo $obj->img_url ?>" alt="" data-jsid="img">
											</a>
										</li>
							<?php
										$pwltcb_cnt++;
								}
						?>
					</ul>
					<?php
							$more_pwltcb=count($this->data['pwltcb'])-$pwltcb_cnt ;
							if($more_pwltcb	>0 )
								echo "<span class='pwltcb_more'> ".JText::sprintf( 'COM_JLIKE_MORE_LIKE_MSG',  $more_pwltcb )."	</span>";
					 ?>
			</div>-->
			<?php if($this->urldata->show_list == '1') { ?>
				<div class="jlike-form-actions user-labels">
					<?php if (1 == $oluser->guest) { ?>

						<div class="alert alert-error"><?php echo JText::_('COM_JLIKE_MUSTLOGIN_FOR_LIST');?></div>

					<?php } else { ?>
								<form name="jlike_list_form" id="jlike_list_form">
									<ul class="unstyled_list">
										<?php
												if(!empty($this->userlables))
												{
													foreach($this->userlables as $ind=>$obj)
													{
														if (!$this->content_id)
														{
															$obj->checked = '';
														}

														?>
														<li>
															<label class="checkbox">
																<input type="checkbox" onClick="manageListforContent(this,'<?php echo $likecontainerid;?>')" class='label-check' value="<?php echo $obj->id;?>" name="label-check[]" <?php echo $obj->checked;?>>
																<?php echo $obj->title; ?>
															</label>
														</li>
													<?php
													}
												}
										?>

										<li id="jlike-label-devider"><hr/></li>

										<li id="jlike-add-label">
												<div class="jlike-header" class="pointer">
														<?php echo JText::_("COM_JLIKE_ADD_LIST_LABEL");?>
												</div>
												<div class="input-append">
													<input class="span10 jlike-tag-append-text" id="appendedInputButton" type="text" placeholder="<?php echo JText::_('NEW_TAG_ADD_PLACEHOLDER')?>">
													<button class="btn jlike-tag-append-button" type="button" title="<?php echo JText::_('CLICK_TO_CREATE_NEW')?>" onclick="addlables('appendedInputButton','<?php echo JText::_('NO_BLANK_LABLES')?>','<?php echo $likecontainerid;?>');"><i class="fa fa-plus-circle icon-blue icon-plus-sign"></i></button>

												</div>
										</li>

									</ul>
									<input type="hidden" id="content_id" name="content_id" value="">
							</form>
				<?php } ?>
				</div><!-- labels-space -->
		<?php } ?>

		<?php if($this->urldata->show_note == '1'){ ?>
				<div id="annotation-snippet" class="annotation-snippet">
					<?php if (1 == $oluser->guest) { ?>

						<div class="alert alert-error"><?php echo JText::_('COM_JLIKE_MUSTLOGIN_FOR_NOTES');?></div>

					<?php } else { ?>
						<form class="form-horizontal" id="annotationform" name="annotationform">
								<div class="catch-error alert" style="display:none;"></div>

							<div class="control-group">
									<textarea placeholder="<?php echo JText::_('ANNOTATE_PLACE_HOLDER')?>" name="annotation" id="annotation" title="<?php echo JText::_('ANNOTATE_PLACE_HOLDER_TITLE')?>" class="annotationplace"><?php echo $this->userNote; ?></textarea>
							</div><!-- control-group-->
							<div class="row-fluid jlike-form-actions">
									<div class="pull-right">
										<div id="jlike-loading-image" class="jlike-loading-image" style="display:none;">&nbsp;</div>
										<button type="button" class="btn btn-tjlms-green btn-primary" onclick="savedata('<?php echo $likecontainerid;?>','<?php echo JText::_('COM_JLIKE_SAVE_SUCCESS_MSG');?>')"><?php echo JText::_('COM_JLIKE_SAVE');?></button>
										<!--<button type="button" class="btn btn-tjlms-orange" onclick="close_comment_snippet('<?php echo $likecontainerid;?>')"><?php echo JText::_('COM_JLIKE_CANCEL');?></button>-->
									</div>
							</div>
							<input type="hidden" id="content_id" name="content_id" value="">
						</form>
					<?php } ?>
				</div><!-- annotation-snippet -->


		<?php } ?>

<!-- ***************************** Comments ********************** -->
<div class="row-fluid jlike-comments-notes">

	<?php if($this->urldata->show_comments == '1')
	{ ?>
		<div class="jlike-comments">
			<?php if (1 == $oluser->guest) { ?>

						<div class="alert alert-error"><?php echo JText::_('COM_JLIKE_MUSTLOGIN_FOR_COMMENTS');?></div>

					<?php } else { ?>

			<?php
				$style='margin-left:8%; width:92%;';
				$margin_left=8;
				$width=92;

				$comjlikeHelper = new comjlikeHelper();
				$commentFile = $comjlikeHelper->getjLikeViewpath('jlike','likebuttons_comments');

				ob_start();
					include($commentFile);
					$html = ob_get_contents();
				ob_end_clean();

				echo $html;
			?>
			<?php } ?>
		</div>
	<?php } ?>
</div>

<?php if (1 != $oluser->guest){ ?>
<script type="text/javascript">
		jLikeVal['<?php echo $likecontainerid; ?>']=[];
		jLikeVal['<?php echo $likecontainerid; ?>']['likeaction'] = "<?php echo $data['likeaction'];?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['dislikeaction'] = "<?php echo $data['dislikeaction'];?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['cont_id'] = "<?php echo $this->urldata->cont_id ;?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['element'] = "<?php echo $this->urldata->element; ?>";
		var title = "<?php echo addslashes($likecontenttitle);?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['title']	=	techjoomla.jQuery('<div/>').html(title).text();

		jLikeVal['<?php echo $likecontainerid; ?>']['url'] = "<?php echo $this->urldata->url; ?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['likecount'] = "<?php echo $data['likecount'];?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['dislikecount'] = "<?php echo $data['dislikecount'];?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['liketext'] = "<?php echo JText::_('LIKE') ;?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['disliketext'] = "<?php echo JText::_('DISLIKE') ;?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['unliketext'] = "<?php echo JText::_('UNLIKE') ;?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['undisliketext'] = "<?php echo JText::_('UNDISLIKE') ;?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['like_icon_class'] = "<?php echo $params->get('like_icon_class') ;?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['dislike_icon_class'] = "<?php echo $params->get('dislike_icon_class') ;?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['plg_name'] = "<?php echo !empty($this->urldata->plg_name) ? $this->urldata->plg_name : '';?>";
		jLikeVal['<?php echo $likecontainerid; ?>']['plg_type'] = "<?php echo !empty($this->urldata->plg_type) ? $this->urldata->plg_type : '';?>";
</script>
<?php } ?>
 </div><!-- likes -->
<div style="clear:both"></div>
</div><!-- techjoomala-bootstrap -->
