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

<div class="conference">
	<?php if(!empty($this->items)) foreach($this->items as $item):?>
	<?php $speaker = ConferenceHelperSession::speaker($item->conference_speaker_id); ?>
	<div class="well well-small spreker">
		<div class="row-fluid">
			<div class="span4">
				<a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id='.$item->conference_session_id)?>" class="thumbnail">
					<?php if($item->speakerimage):?>
						<img src="<?php echo $item->speakerimage?>">
					<?php else:?>
						<img src="http://placehold.it/200x200">
					<?php endif;?>
				</a>
			</div>
			<div class="span8">
				<h3>
					<a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id='.$item->conference_session_id)?>"><?php echo $this->escape($item->title)?></a>
					<?php if($item->level):?>
					&nbsp;<span class="label <?php echo $item->level_label ?>">
						<?php echo $item->level ?>
					</span>
					<?php endif;?>
				</h3>
				<h4><span class="icon-user"></span> <?php echo $item->speaker ?></h4>
				<?php if(!empty($item->slides)) :
  				  $text = '<span class="icon-grid-view" rel="tooltip" data-original-title="' . JText::_('COM_CONFERENCE_SLIDES_AVAILABLE') . '">' . JText::_('COM_CONFERENCE_SLIDES_AVAILABLE') . '</span>';
  				  $url = JRoute::_('index.php?option=com_conference&view=session&id='.$item->conference_session_id);
  				  echo JHtml::_('link', $url, $text);
  				  endif; ?>
				<?php echo(substr($item->description,0, strpos($item->description, "</p>")+4));?>
				<a class="btn btn-small pull-right" href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id='.$item->conference_session_id)?>"><?php echo JText::_('COM_CONFERENCE_READ_MORE') ?> <?php //echo $this->escape($item->title)?></a>
			</div>
		</div>
	</div>
	<?php endforeach;?>

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
