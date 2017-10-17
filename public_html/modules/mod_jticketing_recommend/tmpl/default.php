<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');?>

<?php if ($moduleData->loggedInUserID): ?>

	<div class="courseRecommendMenu couserBlock">
		<div class="panel-heading row">
			<div class="panel-heading-left-content pull-left">
				<i class="fa fa-user fa-2x"></i>
				<span class="course_block_title"><?php echo JText::_('MOD_TJLMS_RECOMMEND_PANEL_HEADING')?></span>
			</div>
		</div>

		<div class="panel-content">
			<?php	if (!empty($moduleData->getuserRecommendedUsers))
				{ ?>
					<div class="row-fluid usermenu">

					<!--recommend mod_data course to a friend-->
					<?php
					foreach($moduleData->getuserRecommendedUsers as $index => $recommeduser)
					{
					?>
						<div class="span4 center">

							<?php if (empty($recommeduser->avatar)) : ?>
								<?php	$recommeduser->avatar = JUri::root(true).'/media/com_tjlms/images/default/user.png';	?>
							<?php endif;	?>

							<?php if (!empty($recommeduser->profileurl)) : ?>
								<a class="" target="_blank" href="<?php echo $recommeduser->profileurl?>" >
							<?php endif;	?>
									<img class="img-circle solid-border" title="<?php echo $userName = ( $show_user_or_username == 'name' ? $recommeduser->name : $recommeduser->username ); ?>" src="<?php echo $recommeduser->avatar;?>" />
							<?php if (!empty($recommeduser->profileurl)) : ?>
								</a>
							<?php endif;	?>

						</div>
				<?php } ?>
					</div>
				<?php
				}
				else
				{
				?>
					<div class="panel-message center">

					<?php	echo JText::_('MOD_TJLMS_NO_RECOMMEND'); ?>

					</div>
				<?php
				}
				?>

			<div class="panel-action recommendCourse">

				<?php
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('content');
				$result =$dispatcher->trigger('showRecommendBtn',array('com_jticketing.event',$moduleData->event_id,$moduleData->eventInfo->title, '/components/com_jticketing/helpers/event.php', $moduleData->social_integration, 'JteventHelper'));

				if(!empty($result)) { ?>
						<div class="tjcourse-recommend-btn">
								<?php	echo $result[0]; ?>
						</div>
						<div class="clearfix"></div>

			<?php }  ?>
			</div>
		</div>
	</div>
<?php endif; ?>
