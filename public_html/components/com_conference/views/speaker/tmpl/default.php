<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

$this->loadHelper('params');
$this->loadHelper('modules');
$this->loadHelper('format');

?>
<div class="conference">
	<div class="row-fluid">
		<h1><?php echo $this->escape($this->item->title)?></h1>
	</div>
	<div class="well well-small spreker">
		<div class="row-fluid">
			<div class="span4">
				<span class="thumbnail">
					<?php if($this->item->image):?>
						<img src="<?php echo $this->item->image?>">
					<?php else:?>
						<img src="http://placehold.it/200x200">
					<?php endif;?>
				</span>
				<div class="speakersocial">
					<?php if(($this->item->twitter) && (ConferenceHelperParams::getParam('twitter'))):?>
                        <a class="speakersocial__link btn btn-small btn-block" target="_blank" href="http://twitter.com/<?php echo $this->item->twitter?>"><span class="speakersocial__icon icon speakersocial__icon--twitter conference-twitter"></span> <span class="speakersocial__text"><?php echo $this->item->twitter?></span></a>
					<?php endif;?>
					<?php if(($this->item->facebook) && (ConferenceHelperParams::getParam('facebook'))):?>
						<a class="speakersocial__link btn btn-small btn-block" target="_blank" href="http://facebook.com/<?php echo $this->item->facebook?>"><span class="speakersocial__icon speakersocial__icon--facebook icon conference-facebook"></span> <span class="speakersocial__text"><?php echo $this->item->facebook?></span></a>
					<?php endif;?>
					<?php if(($this->item->googleplus) && (ConferenceHelperParams::getParam('googleplus'))):?>
						<a class="speakersocial__link btn btn-small btn-block" target="_blank" href="http://plus.google.com/<?php echo $this->item->googleplus?>"><span class="speakersocial__icon speakersocial__icon--googleplus icon conference-google-plus"></span> <span class="speakersocial__text"><?php echo $this->item->title?></span></a>
					<?php endif;?>
					<?php if(($this->item->linkedin) && (ConferenceHelperParams::getParam('linkedin'))):?>
						<a class="speakersocial__link btn btn-small btn-block" target="_blank" href="http://www.linkedin.com/in/<?php echo $this->item->linkedin?>"><span class="speakersocial__icon speakersocial__icon--linkedin icon conference-linkedin"></span> <span class="speakersocial__text"><?php echo $this->item->linkedin?></span></a>
					<?php endif;?>
					<?php if(($this->item->website) && (ConferenceHelperParams::getParam('twitter'))):?>
						<a class="speakersocial__link btn btn-small btn-block" target="_blank" href="http://<?php echo $this->item->website?>"><span class="speakersocial__icon speakersocial__icon--earth icon conference-earth"></span> <span class="speakersocial__text"><?php echo $this->item->website?></span></a>
					<?php endif;?>
				</div>
			</div>
			<div class="span8">
				<?php echo ($this->item->bio)?>
				<?php if($this->sessions):?>
				<h4><?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS')?></h4>
				<table class="table table-striped">
				<tbody>
					<?php foreach($this->sessions as $session):?>
					<tr>
						<td>
							<?php if($session->listview): ?>
		                  	<a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id='.$session->conference_session_id)?>">
		                  		<?php echo($session->title)?>
		                  	</a>
		                  <?php else:?>
		                  	<?php echo($session->title)?>
		                  <?php endif;?>
						</td>
					</tr>
					<?php endforeach;?>
				</tbody>
				</table>
				<?php endif;?>
			</div>
		</div>
	</div>
</div>
