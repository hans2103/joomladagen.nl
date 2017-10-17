<?php
/**
* @version		1.0.0 jomgive $
* @package		jomgive
* @copyright	Copyright © 2012 - All rights reserved.
* @license		GNU/GPL
* @author		TechJoomla
* @author mail	extensions@techjoomla.com
* @website		http://techjoomla.com
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal', 'a.modal');

$document = JFactory::getDocument();
$params = $this->params;
$mylikes_itemid = $this->comjlikeHelper->getitemid('index.php?option=com_jlike&view=likes&layout=my');

// TO use lanugage cont in javascript
JText::script('COM_JLIKE_LIKE_UPDATING', true);
JText::script('COM_JLIKE_DELETE_LIKE_MK_SEL', true);
JText::script('COM_JLIKE_DELETE_LIKE_CONFIRMATION', true);
?>
<script type="text/javascript">
	/*Update note*/
	root_url = "<?php echo JUri::root(); ?>";

	var jLikeVal = [];
	var jLike =
	{
		jQuery : window.jQuery,
		extend : function(obj)
		{
			this.jQuery.extend(this, obj);
		}
	};

	/*jQuery.noConflict(true);*/


	/**
	Update Note function call.
	*/
	function Jlike_updateNote(noteEleId, anno_id)
	{
		var oldNote = jLike.jQuery('#'+noteEleId).val();
		var postParam = {
			form : {
				anno_id : anno_id,
				note : oldNote
			}
		};

		jLike.jQuery.ajax({
			url: root_url+ "index.php?option=com_jlike&task=likes.updateNote&tmpl=component",
			type: 'POST',
			data:postParam,
			cache: false,
			dataType: 'json',
			beforeSend: function()
			{
				jLike.jQuery('#noteMsg_'+anno_id).show();
			},
			complete: function()
			{
				jLike.jQuery('#noteMsg_'+anno_id).hide();
			},
			success: function(msg)
			{
			},
			error: function(response)
			{
				//jLike.jQuery('.jlikeError').show('slow');
			}
		});
	}

	/**
	This function delete likes from my likes vew
	*/
	function jl_deleteMyLikes()
	{
		if (document.adminForm.boxchecked.value==0)
		{
			alert('<?php echo JText::_("COM_JLIKE_DELETE_LIKE_MK_SEL", true); ?>');
		}
		else
		{
			var flag= confirm(Joomla.JText._('COM_JLIKE_DELETE_LIKE_CONFIRMATION'));
			if (flag==true)
			{
				Joomla.submitbutton('likes.delete');
			}
		}
	}


	/**
	Send mail function call.
	*/
	function jl_mailMyLikes()
	{
		if (document.adminForm.boxchecked.value==0)
		{
			alert('<?php echo JText::_("COM_JLIKE_DELETE_LIKE_MK_SEL", true); ?>');
		}
		else
		{
			document.adminForm.task.value='mailMyLikes';
			document.adminForm.submit();
		}
	}

</script>
<div class="techjoomla-bootstrap">
	<form action="" method="post" name="adminForm" id="adminForm">

		<div class="row-fluid">
			<div class="span6">
			<?php
			if ($params->get('allow_annotation'))
			{
			?>
				<label class="checkbox">
				<input class="btn tip hasTooltip" type="checkbox" onclick="if(this.checked)
					{document.getElementById('show_with_coments_only').value='1'}
					else
					{document.getElementById('show_with_coments_only').value='0'};
					this.form.submit();" title="<?php echo JText::_('COM_JLIKE_SHOW_WITH_COMMENTS_ONLY'); ?>" <?php if(JRequest::getVar('show_with_coments_only')=='1') echo 'checked';else echo '';?> /><?php echo JText::_('COM_JLIKE_SHOW_WITH_COMMENTS_ONLY');?>
				 </label>
			<?php
			}
			?>
			</div>
			<div class="span6">
				<div class="btn-toolbar pull-right" id="">
					<div class="btn-wrapper" id="jlike-delete">
						<?php
						// Check if invitex is installed
						$enableMailLikedBtn = $this->params->get('enableMailLikedBtn', 0);

						if ($enableMailLikedBtn === "1")
						{
							if (JFile::exists(JPATH_ROOT . '/components/com_invitex/invitex.php'))
							{
								if (JComponentHelper::isEnabled('com_invitex', true))
								{
									?>
									<button type="button" class="btn hasTooltip"
										title="<?php echo JText::_('JLIKE_SEND_MY_LIKE_DESC'); ?>"
										onclick="jl_mailMyLikes()">
											<i class="icon-mail"></i>
											<?php echo JText::_('JLIKE_SEND_MY_LIKE'); ?>
									</button>
									<?php
								}
							}
						}
						?>

						<button type="button" onclick="jl_deleteMyLikes()" class="btn btn-small btn-danger"> <span class="icon-trash icon-white"></span> <?php echo JText::_("COM_JLIKE_DELETE_BTN"); ?></button>


					</div>
				</div>
			</div>
		</div>
		<div id="filter-bar" class="btn-toolbar">
			<div class="pull-left hidden-phone">
				<div class="input-append ">
					<input type="text"
						placeholder="<?php echo JText::_('COM_JLIKE_AL_FILTER_SEARCH_DESC'); ?>"
						name="filter_search"
						id="filter_search"
						value="<?php if(!empty($this->search)) echo $this->search ?>"
						class="input-medium"
						onchange="document.adminForm.submit();" />

					<button type="button" onclick="this.form.submit();" class="btn tip " data-original-title="Search">
						<i class="icon-search" ></i>
					</button>

					<button onclick="document.getElementById('filter_search').value='';this.form.submit();" type="button" class="btn tip " data-original-title="Clear">
						<i class="icon-remove"></i>
					</button>
				</div>
			</div>

			<div class="pull-right">
				<span class="hidden-phone">
				<?php

					echo $this->filter_likecontent_classification;
				?>
				</span>
				<?php

				if (JVERSION >= 3.0)
				{ ?>
					<span class="hidden-phone">
						<?php echo $this->pagination->getLimitBox(); ?>
					</span>
					<?php
				}

				?>
			</div>
		</div>
		<div style="clear:both"></div>
		<div id="no-more-tables">
		<table class="table table-striped table-bordered table-hover ">
			<thead>
			<tr>
				<th class="nowrap jlike_width_1 center">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th >
					<?php echo JHTML::_( 'grid.sort', 'COM_JLIKE_TITLE','likecontent.title', $this->sortDirection, $this->sortColumn); ?>
				</th>
					<?php
					if ($params->get('allow_annotation'))
					{?>
						<th  ><?php echo JHTML::_( 'grid.sort', 'COM_JLIKE_ANNOTATIONS','likeannotations.annotation', $this->sortDirection, $this->sortColumn); ?></th>
						<?php
					} ?>

				<th ><?php echo JHTML::_( 'grid.sort', 'COM_JLIKE_CLASSIFICATION','likecontent.element', $this->sortDirection, $this->sortColumn); ?></th>

				<th ><?php echo JHTML::_( 'grid.sort', 'COM_JLIKE_LIKE_COUNT','likecontent.like_cnt', $this->sortDirection, $this->sortColumn); ?> </th>

				<?php
				if($params->get('allow_dislike')){
				?>
				<th ><?php echo JHTML::_( 'grid.sort', 'COM_JLIKE_DISLIKE_COUNT','likecontent.dislike_cnt', $this->sortDirection, $this->sortColumn); ?> </th>
				<?php
				}
				?>
				<th ><?php echo JText::_("COM_JLIKE_CREATED_DATE"); ?></th>
<!--
				<th ><?php //echo JText::_("COM_JLIKE_MODIFIED_DATE"); ?></th>
-->


			</tr>
			</thead>
			<tbody>
		<?php
		$i=1;
		if($this->data)
		{
			foreach ($this->data as $likedata)
			{
				if(isset($likedata->like_cnt) && $likedata->like_cnt <=0 )
				{
					continue;
				}
				?>
				<tr id="mylikeRow_<?php echo $likedata->id; ?>">
					<td class="nowrap center jlike_width_1" >
						<?php echo JHtml::_('grid.id', $i, $likedata->id); ?>
					</td>
					<td data-title="<?php echo JText::_("COM_JLIKE_TITLE"); ?>">
						<div>
							<strong><a href="<?php echo $likedata->url;?>"><?php echo $likedata->title;?></a></strong>
						</div>
						<div class="com_jlike_clear_both"></div>
					</td>
					<?php
					if ($params->get('allow_annotation'))
					{
					?>
						<td id="mylikeRow_<?php echo $likedata->id ?>_note"  data-title="<?php echo JText::_("COM_JLIKE_ANNOTATIONS"); ?>">
							<?php
							if($likedata->annotation)
							{ ?>
							<textarea name="text_comment" class="jlike_mylikeNoteStyle"
								id="mylikeNote_<?php echo $likedata->anno_id ?>" rows='4' cols = '20' onBlur="Jlike_updateNote(this.id,<?php echo $likedata->anno_id; ?>)"><?php echo trim($likedata->annotation) ?></textarea>
								<span id='noteMsg_<?php echo $likedata->anno_id ?>' style="display:none;">
								<?php echo JText::_("COM_JLIKE_LIKE_UPDATING"); ?>
									<img class="" src="<?php echo JUri::root() ?>components/com_jlike/assets/images/ajax-loading.gif" height="15" width="15">
								</span>
							<?php
							}
							else
							{
								echo '-';
							}
							?>


						</td>
					<?php
					}
					?>
					<td data-title="<?php echo JText::_("COM_JLIKE_CLASSIFICATION"); ?>">
						<?php
						$brodfile        = JPATH_SITE . "/components/com_jlike/classification.ini";
						$classifications = parse_ini_file($brodfile);
						$element         = $likedata->element;

						foreach ($classifications as $v => $clssfcs)
						{
							if ($v == $likedata->element)
							{
								$element = $clssfcs;
								break;
							}
						}
						if (!$element)
						{
							$element = $likedata->element;
						}
						echo $element;
						?>
					</td>

					<td data-title="<?php echo JText::_("COM_JLIKE_LIKE_COUNT"); ?>"><?php echo $likedata->like_cnt;?></td>
					<?php
					if($params->get('allow_dislike')){
					?>
					<td data-title="<?php echo JText::_("COM_JLIKE_DISLIKE_COUNT"); ?>"><?php echo $likedata->dislike_cnt;?></td>
					<?php
					}
					?>

					<td data-title="<?php echo JText::_("COM_JLIKE_CREATED_DATE"); ?>"><?php echo $likedata->created ?></td>
<!--
					<td data-title="<?php echo JText::_("COM_JLIKE_MODIFIED_DATE"); ?>"><?php //echo $likedata->modified ?></td>
-->
				</tr>
				<?php
				$i++;
			}
		}
		else
		{ ?>
			<tr><td colspan='8' style="text-align:center"><?php echo JText::_('COM_JLIKE_NO_DATA');?></td></tr>
		<?php
		}
		?>
		</tbody>
		</table>
		</div>
		<?php
		if(JVERSION<3.0)
		{
		   $class_pagination='pager';
		}
		else
		{
		   $class_pagination='pagination';
		}
		?>

		<div class="com_jlike_align_center <?php echo $class_pagination; ?>">
			<?php
			if (JVERSION<3.0)
			{
				echo $this->pagination->getListFooter();
			}
			else
			{
				echo $this->pagination->getPagesLinks();
			}
			?>
		</div>
		<input type="hidden" name="option" value="com_jlike" />
		<input type="hidden" name="show_with_coments_only" id="show_with_coments_only" value="" />
		<input type="hidden" name="view" value="likes" />
		<input type="hidden" name="layout" value="default" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="controller" value="likes" />
		<input type="hidden" name="filter_order" id="filter_order" value="<?php if(!empty($this->sortColumn)) echo $this->sortColumn; ?>" />
		<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php if(!empty($this->sortDirection)) echo $this->sortDirection; ?>" />
	</form>
</div>

