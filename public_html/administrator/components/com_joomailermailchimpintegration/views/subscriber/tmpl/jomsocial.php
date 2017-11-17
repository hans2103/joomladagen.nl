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
<div id="jomSocialGroups">
	<p>
		User belongs to the following groups:
	</p>
	<ul class="clearfix">
		<?php
		if (is_array($this->jomSocialGroups)) {
			foreach($this->jomSocialGroups as $key => $group) {
				$class = 'groupLeft';
				$link = JRoute::_(JURI::root().'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id);
				$name = $group->name;
				if (\Joomla\String\StringHelper::strlen($group->name) > 30) {
					$name = \Joomla\String\StringHelper::substr($group->name, 0, 30) . '...';
				}
				echo '<li class="'. $class .'"><a href="'.$link.'" target="_blank">' . $name . '</a></li>';
			}
		}
		?>
	</ul>
</div>
<div id="jomSocialDiscussions">
	<p>
	Discussions that the user has started: <b><?php echo ($this->totalDiscussionsOfUser); ?></b>
	</p>
	<p>
		<?php echo JText::_('Recent discussions'); ?>:
	</p>
	<ul class="clearfix">
		<?php
        if (is_array($this->jomSocialDiscussions)) {
            foreach($this->jomSocialDiscussions as $key => $discussion) {
                $link = JRoute::_(JURI::root().'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id);
                $name = $discussion->title;
					if (\Joomla\String\StringHelper::strlen($discussion->title) > 30) {
						$name = \Joomla\String\StringHelper::substr($discussion->title , 0, 30) . '...';
                }
                $link = JRoute::_(JURI::root().'index.php?option=com_community&view=groups&task=viewdiscussion&groupid='.$discussion->groupid.'&topicid='.$discussion->id);
                echo '<li class="groupLeft"><a href="'.$link.'" target="_blank">' . $name . '</a></li>';
            }
        } ?>
	</ul>
</div>
