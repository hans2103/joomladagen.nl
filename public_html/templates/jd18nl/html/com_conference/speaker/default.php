<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://stichtingsympathy.nl
 */

defined('_JEXEC') or die;

$params = JComponentHelper::getParams('com_conference');
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
					<?php if(($this->item->twitter) && ($params->get('twitter'))):?>
						<a class="btn btn-small btn-block" target="_blank" href="http://twitter.com/<?php echo $this->item->twitter?>">
							<span class="icon conference-twitter"></span> <?php echo $this->item->twitter?>
						</a>
					<?php endif;?>
					<?php if(($this->item->facebook) && ($params->get('facebook'))):?>
						<a class="btn btn-small btn-block" target="_blank" href="http://facebook.com/<?php echo $this->item->facebook?>">
							<span class="icon conference-facebook"></span> <?php echo $this->item->facebook?>
						</a>
					<?php endif;?>
					<?php if(($this->item->googleplus) && ($params->get('googleplus'))):?>
						<a class="btn btn-small btn-block" target="_blank" href="http://plus.google.com/<?php echo $this->item->googleplus?>">
							<span class="icon conference-google-plus"></span> <?php echo $this->item->title?>
						</a>
					<?php endif;?>
					<?php if(($this->item->linkedin) && ($params->get('linkedin'))):?>
						<a class="btn btn-small btn-block" target="_blank" href="http://www.linkedin.com/in/<?php echo $this->item->linkedin?>">
							<span class="icon conference-linkedin"></span> <?php echo $this->item->linkedin?>
						</a>
					<?php endif;?>
					<?php if(($this->item->website) && ($params->get('twitter'))):?>
						<a class="btn btn-small btn-block" target="_blank" href="http://<?php echo $this->item->website?>">
							<span class="icon conference-earth"></span> <?php echo $this->item->website?>
						</a>
					<?php endif;?>
				</div>
			</div>
			<div class="span8">
				<?php echo ($this->item->bio)?>
				<?php if ($this->item->sessions):?>
				<h4><?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS')?></h4>
				<table class="table table-striped">
				<tbody>
					<?php foreach ($this->item->sessions as $session):?>
					<tr>
						<td>
							<?php if ($session->listview): ?>
		                  	<a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id=' . $session->conference_session_id)?>">
		                  		<?php echo($session->title)?>
		                  	</a>
		                  <?php else :?>
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