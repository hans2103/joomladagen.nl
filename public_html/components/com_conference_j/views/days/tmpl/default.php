<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

$this->loadHelper('params');
$this->loadHelper('format');
$this->loadHelper('message');
$this->loadHelper('schedule');
?>

<div class="conference schedule">
	<div class="row-fluid">
		<h1><?php echo JText::_('COM_CONFERENCE_DAYS_TITLE')?></h1>
	</div>

	<div class="row-fluid">
		<ul class="nav nav-tabs">
		<?php if(!empty($this->items)) foreach($this->items as $i=>$item):?>
			<li class="<?php echo $item->slug ?><?php if($i==1):?> active<?php endif;?>">
				<a href="#<?php echo $item->slug ?>" data-toggle="tab"><?php echo $item->title ?></a>
			</li>
		<?php endforeach;?>
		</ul>

		<div class="tab-content">
			<?php if(!empty($this->items)) foreach($this->items as $i=>$item):?>
			<div class="tab-pane <?php if($i==1):?>active<?php endif;?>" id="<?php echo $item->slug ?>">
				<?php
					$slots = ConferenceHelperSchedule::slots($item->conference_day_id);
					$rooms = ConferenceHelperSchedule::rooms();
				?>
				<table class="table table-bordered table-striped">
					<thead class="hidden-phone">
						<tr>
							<th width="10%"></th>
							<?php if(!empty($rooms)) foreach($rooms as $room):?>
							<th width="<?php echo(90/count($rooms));?>%"><?php echo $room->title ?></th>
							<?php endforeach;?>
						</tr>
					</thead>

					<tbody>
						<?php if(!empty($slots)) foreach($slots as $slot):?>
						<?php if($slot->general):?>
						<tr class="info">
							<td class="hidden-phone"><?php echo JHtml::_('date', $slot->start_time ,'H:i'); ?></td>
							<td colspan="<?php echo(count($rooms));?>">
								<span class="visible-phone">
									<?php echo JHtml::_('date', $slot->start_time ,'H:i'); ?>:
								</span>
								<?php if(isset($this->sessions[$slot->conference_slot_id][ConferenceHelperSchedule::generalroom()])) :?>
								<?php $session = $this->sessions[$slot->conference_slot_id][ConferenceHelperSchedule::generalroom()];?>
								<?php if($session->listview): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id='.$session->conference_session_id)?>"><?php echo $session->title ?></a>
								<?php else:?>
									<?php echo $session->title ?>
								<?php endif;?>

								<?php endif;?>
							</td>
						</tr>
						<?php else:?>
						<tr>
							<td><?php echo JHtml::_('date', $slot->start_time ,'H:i'); ?></td>
							<?php if(!empty($rooms)) foreach($rooms as $room):?>
							<?php if(isset($this->sessions[$slot->conference_slot_id][$room->conference_room_id])) :?>
								<td>
								<?php $session = $this->sessions[$slot->conference_slot_id][$room->conference_room_id];?>
									<span class="visible-phone roomname">
										<?php echo $session->room ?>
									</span>
									<?php if($session->level):?>
									<a href="<?php echo JRoute::_('index.php?option=com_conference&view=levels')?>"><span class="label <?php echo $session->level_label ?>">
										<?php echo $session->level ?>
									</span></a><br/>
									<?php endif;?>
								<div class="session">
									<?php if($session->listview): ?>
										<?php if($session->slides): ?>
											<span class="icon-grid-view" rel="tooltip"  data-original-title="<?php echo JText::_('COM_CONFERENCE_SLIDES_AVAILABLE')?>"></span>
										<?php endif;?>
										<a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id='.$session->conference_session_id)?>"><?php echo $session->title ?></a>
									<?php else:?>
										<?php echo $session->title ?>
									<?php endif;?>
									<?php if(ConferenceHelperParams::getParam('language',0)): ?>
										<?php if($session->language == 'en'): ?>
											<img class="lang" src="media/mod_languages/images/<?php echo($session->language)?>.gif"/>
										<?php endif; ?>
									<?php endif; ?>
								</div>
								<?php if($session->conference_speaker_id):?>
									<?php $speakers = ConferenceHelperFormat::speakers($session->conference_speaker_id); ?>
									<?php if(!empty($speakers)):?>
									<?php
										$sessionspeakers = array();
										foreach($speakers as $speaker) :
											if($speaker->enabled) {
												$sessionspeakers[] = '<span class="icon-user"></span> <a href="index.php?option=com_conference&view=speaker&id='.$speaker->conference_speaker_id.'">'.trim($speaker->title).'</a>';
											} else {
												$sessionspeakers[] = '<span class="icon-user"></span> '.trim($speaker->title);
											}
										endforeach;
									?>
									<div class="speaker">
										<small><?php echo implode('<br/> ', $sessionspeakers); ?></small>
									</div>
									<?php endif;?>
								<?php endif;?>
							</td>
							<?php else:?>
							<td class="hidden-phone"></td>
							<?php endif;?>
							<?php endforeach;?>
						</tr>
						<?php endif;?>
						<?php endforeach;?>
					</tbody>
				</table>
			</div>
			<?php endforeach;?>
		</div>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery("[rel=tooltip]").tooltip();
	});

	var url = document.location.toString();
	if (url.match('#')) {
	    jQuery('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
	}

	jQuery('.nav-tabs a').on('shown', function (e) {
	    if(history.pushState) {
	        history.pushState(null, null, e.target.hash);
	    } else {
	        window.location.hash = e.target.hash; //Polyfill for old browsers
	    }
	})
</script>