<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
global $isJ25;
$connections = $this->get('Connections');
?>
<div id="obss_msg_result">
</div>
<div class="well well-small alert alert-success well-post">
	<form id="obss_msg_form" action="index.php?option=com_obsocialsubmit&task=cpanel.postmsg" method="post" >
		<div class="row-fluid" id="obss_msg_msgbox">
			<textarea class="input-block-level" id="obss_msg_fied" name="msg" rows="2" placeholder="<?php echo JText::_('COM_OBSOCIALSUBMIT_MSG_TEXTAREA_PLACEHOLDER'); ?>"></textarea>
		</div>
		<div class="clearfix">&nbsp;</div>
		<div class="row-fluid">
			<div class="span9">
				<ul class="unstyled">
				<?php
				$classes = array(
						'twitter'=>'fa-twitter-square',
						'facebook'=>'fa-facebook-square',
						'linkedin'=>'fa-linkedin-square'
					);
				$cids_default = array( '4', '5', '6' );
				foreach ( $connections as $connection ) {
					if( $connection->published != 1 ) continue;
					$in_array	= in_array($connection->id, $cids_default);
					$in_array = true;
					$checked	= $in_array?' checked="checked" ':'';
					$class_icon = $in_array?'icon-publish':'icon-unpublish';
				?>
					<li>
						<label>
							<span class="btn btn-micro jgrid hasTooltip obss_con" title="">
								<i class="<?php echo $class_icon;?>"></i>
								<input name="cids[]" type="checkbox" style="display: none" value="<?php echo $connection->id ;?>"<?php echo $checked; ?>/>
							</span>
							<a href="index.php?option=com_obsocialsubmit&task=connection.edit&id=<?php echo $connection->id; ?>"><i class="fa-fw fa <?php echo $classes[$connection->addon] ;?>"></i> <?php echo $connection->title; ?></a>
						</label>
					</li>
				<?php
				}
				?>
				</ul>
			</div>
			<div class="span3 center">
				
				<a id="obss_submit_btn" class="btn btn-primary btn-large input-block-level hasTooltip" data-original-title="<?php echo JText::_('COM_OBSOCIALSUBMIT_MSG_POST_BTN_TIPS'); ?>" data-loading-text="<?php echo JText::_('COM_OBSOCIALSUBMIT_MSG_POST_BTN_LBL_LOADING'); ?>" href="#"><i class="fa fa-thumbs-up"></i> <?php echo JText::_('COM_OBSOCIALSUBMIT_MSG_POST_BTN_LBL');?></a>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">

	function initCheckbox(){
		jQuery('.obss_con  input[type="checkbox"]').each(function(index, el){
			var ei = jQuery(el).parent().children("i").get(0);
			if( jQuery(el).is(':checked') ) {
				ei.addClass('icon-publish');
				ei.removeClass('icon-unpublish');
			} else {
				ei.removeClass('icon-publish');
				ei.addClass('icon-unpublish');
			}
		});
	}

	jQuery( document ).ready(function() {
	   initCheckbox();
	   jQuery('.obss_con input[type="checkbox"]').change(function(e){
		   var ei = jQuery(this).parent().children("i").get(0);
//		   console.log(jQuery(this).is(':checked'));
		   if( jQuery(this).is(':checked') ) {
			   ei.addClass('icon-publish');
			   ei.removeClass('icon-unpublish');
		   } else {
			   ei.removeClass('icon-publish');
			   ei.addClass('icon-unpublish');
		   }
	   });

	   jQuery('#obss_submit_btn').click( function(e){
//		   e.preventDefault();
		   var data = jQuery('#obss_msg_form').serialize();
		   var nchecked = jQuery('.obss_con input[type="checkbox"]:checked').length;
		   var msg = jQuery('#obss_msg_fied').val();
		   if(!msg){
			   document.getElementById('obss_msg_result').innerHTML ='<div class="alert alert-danger"><?php echo JText::_('COM_OBSOCIALSUBMIT_ALERT_NO_MESSAGE_ENTERED'); ?></div>';
			   return;
			   console.log(msg);
		   }
		   
		   if(!nchecked){
//			   console.log( 'Number selected connection:' + nchecked );
			   document.getElementById('obss_msg_result').innerHTML ='<div class="alert alert-danger"><?php echo JText::_('COM_OBSOCIALSUBMIT_ALERT_NO_SELECTED_CONNECTION'); ?></div>';
			   return;
		   }
		   

		   jQuery(this).button('loading');
		   jQuery.post(
				'index.php?option=com_obsocialsubmit&task=cpanel.postmsg',
				data, 
				function(data, textStatus, jqXHR){
					document.getElementById('obss_msg_form').reset();
					document.getElementById('obss_msg_result').innerHTML = data;
					console.log(data);
//					console.log(textStatus);
					initCheckbox();
					jQuery('#obss_submit_btn').button('reset')
				});
	   });
	});

</script>
