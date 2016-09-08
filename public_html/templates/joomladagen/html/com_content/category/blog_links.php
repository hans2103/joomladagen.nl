<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Create shortcut
$urls = json_decode($this->item->urls);

// Create shortcuts to some parameters.
$params = $this->item->params;
if ($urls && (!empty($urls->urla) || !empty($urls->urlb) || !empty($urls->urlc))) :


		$urlarray = array(
			array($urls->urla, $urls->urlatext, $urls->targeta, 'a'),
			array($urls->urlb, $urls->urlbtext, $urls->targetb, 'b'),
			array($urls->urlc, $urls->urlctext, $urls->targetc, 'c')
			);
?>




<div class="speakersocial">
		<?php if($urlarray[1][0]):?>
			<a class="btn btn-small btn-block" href="<?php echo $urlarray[1][0]?>"><span class="icon joomladay-twitter"> <?php echo(str_replace('https://twitter.com/', '', $urlarray[1][0]))?></span></a>
		<?php endif;?>

		<?php if($urlarray[2][0]):?>
			<a class="btn btn-small btn-block" href="<?php echo $urlarray[2][0]?>"><span class="icon joomladay-facebook"> <?php echo(str_replace('http://www.facebook.com/', '', $urlarray[2][0]))?></span></a>
		<?php endif;?>

		<?php if($urlarray[0][0]):?>
			<a class="btn btn-small btn-block" href="<?php echo $urlarray[0][0]?>">
				<span class="icon joomladay-earth">
				<?php if(!empty($urlarray[0][1])):?>
				<?php echo(str_replace('http://', '', $urlarray[0][1]))?>
				<?php else:?>
				<?php echo(str_replace('http://', '', $urlarray[0][0]))?>
				<?php endif;?>
				</span>
			</a>
		<?php endif;?>
	</div>
<?php endif; ?>