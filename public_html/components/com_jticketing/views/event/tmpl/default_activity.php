<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

$user = JFactory::getUser();
$limit = 10;
?>
<div class="row">
	<div class="col-xs-12 col-sm-12">
		<form name="post-activity" method="post">
			<?php 
			if ($user->id == $this->item->created_by)
			{
			?>
				<div class="feed-item-cover todays-activity">
					<div class="date col-xs-3 col-sm-1 col-lg-1">
						<?php echo JText::_("COM_JTICKETING_ACTIVITY_TODAY");?>
						</br>
						<?php echo JHtml::Date('now', 'd, M');?>
					</div>
					<div class="feed-item col-xs-9 col-sm-11 col-lg-11">
						<div class="feed-item-inner">
							<div class="form-group">
								<input
									class="form-control input-lg"
									id="activity-post-text"
									name="activity-post-text"
									placeholder="<?php echo JText::_("COM_JTICKETING_ACTIVITY_TODAY_TEXT");?>"
									maxlength="300">
								</input>
								<div id="activity-post-text-length" class="pull-right clearfix"></div>
							</div>
							<div class="form-group">
								<button
									type="submit"
									id="postactivity"
									class="btn btn-primary pull-right clearfix">
									<?php echo JText::_("COM_JTICKETING_TEXT_ACTIVITY_POST");?>
								</button>
							</div>
						</div>
					</div>
				</div>
			<?php
			}
			?>
			<div id="tj-activitystream" tj-activitystream-widget tj-activitystream-theme="eventfeed" tj-activitystream-bs="bs3" 
			tj-activitystream-type="'event.addvideo', 'event.addimage','jticketing.addevent', 'jticketing.textpost', 'event.extended', 'eventBooking.extended', 'jticketing.order'" tj-activitystream-target-id="<?php echo $this->item->id;?>" tj-activitystream-limit="<?php echo $limit;?>" tj-activitystream-language="<?php echo $this->item->language;?>">
			</div>
			<input type="hidden" name="option" value="com_jticketing"></input>
			<input type="hidden" name="task" value="event.addPostedActivity"></input>
		</form>
	</div>
</div>
<script>
	jtSite.event.loadActivity();
</script>
