<?php
/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

// no direct access
defined('_JEXEC') or die('Restricted Access'); ?>
<div id="socialTab">
	<p class="twitter-stuff">
		<?php if ($this->twitterName) : ?>
			<img src="<?php echo JURI::root(); ?>/media/com_joomailermailchimpintegration/backend/views/subscriber/twitter.png" alt="twitter logo"/>
		    <?php echo $this->twitterName; ?>

			<span class="twitter-links">
				<a href="http://twitter.com/#!/<?php echo $this->twitterName ?>" target="_blank">
				<?php echo JText::_('twitter profile'); ?>
			</a>
				|
			<a href="http://twitter.com/#search?q=@<?php echo $this->twitterName ?>" target="_blank">
				<?php echo JText::_('@Mention'); ?>
			</a>
		</span>
		<?php else : ?>
			This user has not provided any Twitter information.
		<?php endif; ?>
	</p>
	<p class="twitter-stuff">
		<?php if ($this->facebookName) : ?>
			<img src="<?php echo JURI::root(); ?>/media/com_joomailermailchimpintegration/backend/views/subscriber/facebook.png" alt="facebook" />
			<a href="http://www.facebook.com/profile.php?id=<?php echo $this->facebookName ?>" target="_blank">
				Facebook profile
			</a>
		<?php else : ?>
			This user has not provided any Facebook information.
		<?php endif; ?>
	</p>
</div>

