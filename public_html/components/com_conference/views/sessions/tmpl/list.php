<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

$this->loadHelper('format');
$this->loadHelper('message');
$this->loadHelper('session');

// Get the Itemid
$itemId = FOFInput::getInt('Itemid',0,$this->input);
if($itemId != 0) {
	$actionURL = 'index.php?Itemid='.$itemId;
} else {
	$actionURL = 'index.php';
}
?>

<div class="conference levels">
	<div class="row-fluid">
		<h1><?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS')?></h1>
	</div>

	<div class="row-fluid">
		<div class="span12">
			<?php if(!empty($this->items)):?>
			<table class="table table-bordered table-striped">
				<thead>
					<tr>
						<th><?php echo JText::_('COM_CONFERENCE_FIELD_SESSION')?></th>
						<th width="30%"><?php echo JText::_('COM_CONFERENCE_FIELD_SPEAKER')?></th>
						<th width="25%"><?php echo JText::_('COM_CONFERENCE_FIELD_SLOT')?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($this->items as $session):?>
					<tr>
						<td>
							<?php echo($session->title)?>
						</td>
						<td>
							<?php $speakers = ConferenceHelperFormat::speakers($session->conference_speaker_id); ?>
							<?php if(!empty($speakers)):?>
							<?php
								$sessionspeakers = array();
								foreach($speakers as $speaker) :
									$sessionspeakers[] = trim($speaker->title);
								endforeach;
								?>
								<div class="speaker">
									<?php echo implode(', ', $sessionspeakers); ?>
								</div>
							<?php endif;?>
						</td>
						<td>
							<?php if($session->slides):?>
							<a href="#slides<?php echo($session->conference_session_id)?>" role="button" class="btn" data-toggle="modal">Slides</a>

							<!-- Start Modal -->
							<div id="slides<?php echo($session->conference_session_id)?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							  <div class="modal-header">
							    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							    <h3 id="myModalLabel"><?php echo($session->title)?></h3>
							  </div>
							  <div class="modal-body">
							    <?php echo($session->slides)?>
							  </div>
							  <div class="modal-footer">
							    <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</button>
							  </div>
							</div>
							<!-- End Modal -->
							<?php endif;?>
						</td>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
			<?php endif;?>
		</div>
	</div>

	<div class="row-fluid">
		<form id="conference-pagination" name="conference-pagination" action="<?php echo $actionURL ?>" method="post">
			<input type="hidden" name="option" value="com_conference" />
			<input type="hidden" name="view" value="speakers" />
			<?php if ($this->pageparams->get('show_pagination',1)) : ?>
				<?php if($this->pagination->get('pages.total') > 1): ?>
				<div class="pagination">
					<?php if ($this->pageparams->get('show_pagination_results',1)) : ?>
					<p class="counter">
						<?php echo $this->pagination->getPagesCounter(); ?>
					</p>
					<?php endif; ?>
					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		</form>
	</div>
</div>