<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport('joomla.application.component.helper');
jimport('joomla.filesystem.folder');
/**
 * @package		jomLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */
?>
	<script language="JavaScript">
				function migrateoldlikes(success_msg,error_msg)
					{
						jQuery.ajax({
														url: 'index.php?option=com_jlike&task=migrateLikes&tmpl=component',
													
														dataType: 'json', 
														beforeSend: function(){
																jQuery('#jlike-loading-image').show();
															},
															complete: function(){
																jQuery('#jlike-loading-image').hide();
															},
														error: function(){ 
															jQuery('#migrate_msg').css("display", "block");		
															jQuery('#migrate_msg').addClass("alert alert-error");
															jQuery('#migrate_msg').text(error_msg);
															return false;
														}, 
														
														success: function(response) 
														{
																	jQuery('#migrate_msg').css("display", "block");		
																	jQuery('#migrate_msg').addClass("alert alert-success");			
																	jQuery('#migrate_msg').text(success_msg);
																	jQuery('#migrate_button').css("display", "none");
																	return false;
														}
						});
						return false;
					}			

				</script>	
				
				<div class="well well-large center">
						<?php 
						$limit_populate_link=JRoute::_(JURI::base().'index.php?option=com_jlike&tmpl=component&task=migrateLikes');
						?>
							<div class="alert" id="migrate_msg" style='display:none'></div>
							<div>
								<div class='jlike-loading-image' style="display:none;background: url('<?php echo JURI::root().'/'.'components'.'/'.'com_jlike/assets/images/ajax-loading.gif'?>') no-repeat scroll 0 0 transparent"></div>
								<button class="btn btn-success" id="migrate_button" onclick="migrateoldlikes('<?php echo JText::_('Data successfully migrated!!');?>','<?php echo JText::_('There is some error while migrating your data!');?>')"><?php echo JText::_('Migrate old Likes data to Jlike');?></button>
							</div>
				</div>
