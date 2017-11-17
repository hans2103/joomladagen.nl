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

<link rel="stylesheet" href="<?php echo JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/share.css';?>"/>

<div id="content"><h3><?php echo JText::_('JM_SHARE_ON_SOCIAL_NETWORKS');?></h3>
    <ul id="social-links">
        <li>
        <a class="twitter-button social-button" title="Twitter" href="http://twitter.com/home/?status=<?php echo $this->title.' - '.$this->url; ?>" target="_blank">Twitter</a>
        <li>
            <a class="facebook-button social-button" title="Facebook" href="http://www.facebook.com/share.php?u=<?php echo $this->url; ?>&t=<?php echo $this->title; ?>" target="_blank">Facebook</a>
        </li>
        <li>
            <a class="myspace-button social-button" title="MySpace" href="http://www.myspace.com/Modules/PostTo/Pages/?u=<?php echo $this->url; ?>&t=<?php echo $this->title; ?>" target="_blank">MySpace</a>
        </li>
        <li>
            <a class="digg-button social-button" title="Digg" href="http://digg.com/submit?phase=2&amp;url=<?php echo $this->url; ?>&title=<?php echo $this->title; ?>" target="_blank">Digg</a>
        </li>
        <li>
            <a class="delicious-button social-button" title="Delicious" href="http://del.icio.us/post?url=<?php echo $this->url; ?>title=<?php echo $this->title; ?>" target="_blank">Delicious</a>
        </li>
        <li>
            <a class="reddit-button social-button" title="reddit" href="http://reddit.com/submit?&amp;url=<?php echo $this->url; ?>title=<?php echo $this->title; ?>" target="_blank">reddit</a>
        </li>
    </ul>
</div>

