<?php
/**
 * -----------------------------------------------------------------------------
 *
 * Functions for TwitterZoid PHP Script
 * Copyright (c) 2008 Philip Newborough <mail@philipnewborough.co.uk>
 *
 * http://crunchbang.org/archives/2008/02/20/twitterzoid-php-script/
 *
 * LICENSE: This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * http://www.gnu.org/licenses/
 *
 * -----------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted Access');

function timesince($i,$date){
    if ($i >= time()){
        $timeago = JText::_('JM_0_SECONDS_AGO');
        return $timeago;
    }
    $seconds = time()-$i;
    $units = array('JM_SECOND' => 1,'JM_MINUTE' => 60,'JM_HOUR' => 3600,'JM_DAY' => 86400,'JM_MONTH' => 2629743,'JM_YEAR' => 31556926);
    foreach($units as $key => $val){
	
        if ($seconds >= $val){
            $results = floor($seconds/$val);
            
            if ($key == 'JM_DAY' | $key == 'JM_MONTH' | $key == 'JM_YEAR'){
                $timeago = $date;
            }else{
                $timeago = ($results >= 2) ? JText::_('JM_ABOUT').' ' . $results . ' ' .  JText::_($key) .   JText::_('JM_S_AGO') : JText::_('JM_ABOUT').' '. $results . ' ' . JText::_($key) .' '.  JText::_('JM_AGO');
            }
        }
    }
    return $timeago;
}
function twitterit(&$text, $twitter_username, $target='_blank', $nofollow=true){
    $urls  =  _autolink_find_URLS($text);
    if (!empty($urls)){
	array_walk($urls, '_autolink_create_html_tags', array('target'=>$target, 'nofollow'=>$nofollow));
	$text  =  strtr($text, $urls);
    }
    $text = preg_replace("/(\s@|^@)([a-zA-Z0-9]{1,15})/","$1<a href=\"http://twitter.com/$2\" target=\"_blank\" rel=\"nofollow\">$2</a>",$text);
    $text = preg_replace("/(\s#|^#)([a-zA-Z0-9]{1,15})/","$1<a href=\"http://twitter.com/#!/search?q=$2\" target=\"_blank\" rel=\"nofollow\">$2</a>",$text);
    $text = str_replace($twitter_username.": ", "",$text);

    return $text;
}
function _autolink_find_URLS($text){
    $scheme = '(http:\/\/|https:\/\/)';
    $www = 'www\.';
    $ip = '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
    $subdomain = '[-a-z0-9_]+\.';
    $name = '[a-z][-a-z0-9]+\.';
    $tld = '[a-z]+(\.[a-z]{2,2})?';
    $the_rest = '\/?[a-z0-9._\/~#&=;%+?-]+[a-z0-9\/#=?]{1,1}';            
    $pattern = "$scheme?(?(1)($ip|($subdomain)?$name$tld)|($www$name$tld))$the_rest";    
    $pattern = '/'.$pattern.'/is';
    $c = preg_match_all($pattern, $text, $m);
    unset($text, $scheme, $www, $ip, $subdomain, $name, $tld, $the_rest, $pattern);
    if ($c){
        return(array_flip($m[0]));
    }
    return(array());
}
function _autolink_create_html_tags(&$value, $key, $other=null){
    $target = $nofollow = null;
    if (is_array($other)){
        $target = ($other['target'] ? " target=\"$other[target]\"":null);
        $nofollow = ($other['nofollow'] ? ' rel="nofollow"':null);     
    }
    $value = "<a href=\"$key\"$target$nofollow>$key</a>";
}
?>
