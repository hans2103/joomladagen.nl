<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'venue');
$tjvendorsModelVendors = JModelLegacy::getInstance('Venue', 'JticketingModel');
$venueDetails = $tjvendorsModelVendors->getItem($data['venue']);
$location = $venueDetails->name . '' . $venueDetails->address;

echo "BEGIN:VCALENDAR

VERSION:2.0

PRODID:-//hacksw/handcal//NONSGML v1.0//EN

CALSCALE:GREGORIAN

METHOD:REQUEST

TRANSP:OPAQUE

BEGIN:VEVENT

UID:" . $a = md5(uniqid(mt_rand(), true)) . "

DTSTAMP: " . gmdate('Ymd') . 'T' . gmdate('His') . "

DTSTART:" . JFactory::getDate($data['startdate'])->format('Ymd\THis', true) . "

DTEND:" . JFactory::getDate($data['enddate'])->format('Ymd\THis', true) . "

SUMMARY:" . $data['title'] . "

DESCRIPTION:" . $data['long_description'] . "

LOCATION:" . $location . "

END:VEVENT

END:VCALENDAR";

?>
