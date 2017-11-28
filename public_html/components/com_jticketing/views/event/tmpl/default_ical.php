<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Unauthorized Access');
?>
BEGIN:VCALENDAR

VERSION:2.0

PRODID:-//hacksw/handcal//NONSGML v1.0//EN

CALSCALE:GREGORIAN

METHOD:REQUEST

TRANSP:OPAQUE

BEGIN:VEVENT

UID:<?php echo md5(uniqid(mt_rand(), true));?>

DTSTAMP:<?php echo gmdate('Ymd').'T'. gmdate('His');?>Z

DTSTART:<?php echo JHtml::date($event->startdate, 'Ymd\THis', true);?>

DTEND:<?php  echo  JHtml::date($event->enddate, 'Ymd\THis', true);?>

SUMMARY:<?php echo $event->title;?>

DESCRIPTION:<?php if(isset($event->short_description)) echo $event->short_description; else if($event->description);?>

LOCATION:<?php echo $event->location;?>

END:VEVENT

END:VCALENDAR
