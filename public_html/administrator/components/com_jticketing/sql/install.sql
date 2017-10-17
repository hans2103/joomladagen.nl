-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 07, 2014 at 11:23 AM
-- Server version: 5.5.29
-- PHP Version: 5.3.10-1ubuntu3.6




/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `test_merge_20dec`
--

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_atteendeelist`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_atteendeelist` (
  `ticketid` int(11) NOT NULL,
  `eventid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(900) NOT NULL,
  `user_email` varchar(900) NOT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_attendees`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_attendees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL COMMENT 'user_id of jticketing_order table',
  `owner_email` varchar(100) DEFAULT NULL COMMENT 'buyer email for guest checkout',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_attendee_fields`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_attendee_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventid` int(11) NOT NULL COMMENT 'id of integration xref table',
  `placeholder` text NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'This is type of field like radio,selectbox,text,hidden',
  `label` varchar(255) NOT NULL,
  `required` int(11) NOT NULL,
  `validation_class` varchar(500) NOT NULL,
  `js_function` varchar(255) NOT NULL COMMENT 'This is javascript function to call',
  `state` int(11) NOT NULL COMMENT '1-published 0-not published',
  `core` tinyint(1) NOT NULL COMMENT 'There are some core fields like first name,last name,email,phone no',
  `min` int(10) NOT NULL,
  `max` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `tips` varchar(255) NOT NULL,
  `searchable` int(3) NOT NULL,
  `registration` tinyint(1) NOT NULL,
  `options` text NOT NULL,
  `default_selected_option` text NOT NULL,
  `field_code` varchar(255) NOT NULL,
  `show_on_view` int(11) NOT NULL COMMENT 'This is name of option, view and layout name to be given',
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_attendee_field_values`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_attendee_field_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attendee_id` int(11) NOT NULL COMMENT 'primary key of Jticketing_attendees table',
  `field_id` int(11) NOT NULL,
  `field_value` text NOT NULL,
  `field_source` varchar(250) NOT NULL COMMENT 'We are using two types of field manager.  One source is jticketing_attendee_fields and  tjfields_fields  so values of this fields should be com_jticketing or com_tjfields.com_jticketig.ticket  ',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_balance_order_items`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_balance_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(15) NOT NULL,
  `type_id` int(15) NOT NULL,
  `ticketcount` int(11) NOT NULL,
  `ticket_price` int(11) NOT NULL,
  `amount_paid` float(10,2) NOT NULL,
  `attribute_amount` float(10,2) NOT NULL,
  `coupon_discount` float(10,2) NOT NULL,
  `payment_status` varchar(255) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(700) NOT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_checkindetails`
--
CREATE TABLE IF NOT EXISTS `#__jticketing_checkindetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticketid` int(11) DEFAULT NULL,
  `eventid` int(11) DEFAULT NULL,
  `attendee_id` int(11) DEFAULT NULL,
  `attendee_name` text DEFAULT NULL,
  `attendee_email` text DEFAULT NULL,
  `checkintime` datetime DEFAULT NULL,
  `checkouttime` datetime DEFAULT NULL,
  `spend_time` time DEFAULT NULL,
  `checkin` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_coupon`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) NOT NULL,
  `ordering` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(100) NOT NULL,
  `code` varchar(100) NOT NULL,
  `value` int(11) NOT NULL,
  `val_type` tinyint(4) NOT NULL,
  `max_use` int(11) NOT NULL,
  `max_per_user` int(11) NOT NULL,
  `description` text NOT NULL,
  `coupon_params` text NOT NULL,
  `from_date` datetime DEFAULT NULL,
  `exp_date` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_events`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_by` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `catid` int(11) NOT NULL,
  `venue` int(11) NOT NULL DEFAULT '0',
  `short_description` text NOT NULL,
  `long_description` text NOT NULL,
  `startdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `booking_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `booking_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `location` text NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `permission` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '0 - Open (Anyone can mark attendence), 1 - Private (Only invited can mark attendence)',
  `image` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` tinyint(3) NOT NULL,
  `allow_view_attendee` tinyint(3) NOT NULL,
   `access` int(11) NOT NULL,
  `featured` tinyint(1) NOT NULL,
  `online_events` tinyint(4) NOT NULL,
  `ordering` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `jt_params` text NOT NULL,
   PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_event_images`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_event_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `event_id` int(11) NOT NULL COMMENT 'fk - primary key of table#__jticketing_integration',
  `path` varchar(400) NOT NULL COMMENT 'image path',
  `video_provider` varchar(50) NOT NULL,
  `video_url` text NOT NULL,
  `video_img` tinyint(1) NOT NULL,
  `gallery_image` tinyint(1) NOT NULL,
  `order` int(5) NOT NULL COMMENT 'ordering',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------



-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_integration_xref`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_integration_xref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `eventid` int(11) NOT NULL,
  `source` varchar(100) NOT NULL,
  `paypal_email` varchar(100) NOT NULL,
  `checkin` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL,
  `cron_status` int(11) NOT NULL,
  `cron_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_order`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(23) NOT NULL,
  `parent_order_id` int(11) NOT NULL,
  `event_details_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `cdate` datetime DEFAULT NULL,
  `mdate` datetime DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payee_id` varchar(100) DEFAULT NULL,
  `order_amount` float(10,2) NOT NULL,
  `original_amount` float(10,2) DEFAULT NULL COMMENT 'original amount with no fee applied',
  `amount` float(10,2) NOT NULL COMMENT 'amount after applying fee',
  `coupon_code` varchar(100) NOT NULL,
  `fee` float(10,2) DEFAULT NULL COMMENT 'site admin commision(processing fee)',
  `status` varchar(100) DEFAULT NULL,
  `processor` varchar(100) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `ticketscount` int(11) NOT NULL,
  `extra` text,
  `order_tax` float(10,2) DEFAULT NULL,
  `order_tax_details` text NOT NULL,
  `coupon_discount` float(10,2) DEFAULT NULL,
  `coupon_discount_details` text,
  `ticket_email_sent` tinyint(2) NOT NULL DEFAULT '0',
  `customer_note` text NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_order_items`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(15) NOT NULL,
  `type_id` int(15) NOT NULL,
  `attendee_id` int(11) NOT NULL COMMENT 'id of #__jticketing_attendees table',
  `ticketcount` int(11) NOT NULL,
  `ticket_price` int(11) NOT NULL,
  `amount_paid` float(10,2) NOT NULL,
  `fee_amt` int(11) NOT NULL,
  `fee_params` text NOT NULL,
  `attribute_amount` float(10,2) NOT NULL,
  `coupon_discount` float(10,2) NOT NULL,
  `payment_status` varchar(255) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(700) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_ticket_payouts`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_ticket_payouts` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `payee_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `transction_id` varchar(15) NOT NULL,
  `payee_id` varchar(55) NOT NULL,
  `amount` float(10,2) NOT NULL,
  `status` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` text NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_types`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `desc` varchar(500) NOT NULL,
  `price` float(10,2) NOT NULL,
  `deposit_fee` float(10,2) NOT NULL,
  `available` int(10) NOT NULL,
  `count` int(10) NOT NULL,
  `unlimited_seats` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=unlimited 0=limited',
  `eventid` int(10) NOT NULL,
  `max_limit_ticket` INT(11) NOT NULL,
`access` tinyint(4) DEFAULT NULL,
`state` tinyint(4) DEFAULT NULL,
PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_users`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `address_type` varchar(11) NOT NULL,
  `firstname` varchar(250) NOT NULL,
  `lastname` varchar(250) NOT NULL,
  `vat_number` varchar(250) NOT NULL,
  `tax_exempt` tinyint(4) NOT NULL,
  `country_code` varchar(250) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(250) NOT NULL,
  `state_code` varchar(250) NOT NULL,
  `zipcode` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `approved` tinyint(1) NOT NULL,
`country_mobile_code` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) DEFAULT CHARSET=utf8 COMMENT='Jticketing User Information' AUTO_INCREMENT=1 ;


--
-- Table structure for table `#__jticketing_reminder_types`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_reminder_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `description` text NOT NULL,
  `days` int(11) NOT NULL,
  `hours` int(11) NOT NULL,
  `minute` int(11) NOT NULL,
  `subject` varchar(600) NOT NULL,
  `sms` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `css` text NOT NULL,
  `email_template` text NOT NULL,
  `sms_template` text NOT NULL,
  `event_id` int(11) NOT NULL,
  `replytoemail` varchar(255) NOT NULL,
  `reminder_params` text  NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `#__jticketing_queue`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT 'id of #__jticketing_order table',
  `subject` text NOT NULL,
  `content` text NOT NULL,
  `reminder_type_id` int(11) NOT NULL,
  `reminder_type` varchar(500) NOT NULL,
  `date_to_sent` datetime NOT NULL,
  `email` text NOT NULL,
  `mobile_no` bigint(20) NOT NULL,
  `sent` int(11) NOT NULL DEFAULT '0' COMMENT '0=not sent 1=sent 2=expired 3=delayed so it can be sent when cron runs later',
  `sent_date` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `#__Stripe_xref`
--
CREATE TABLE IF NOT EXISTS `#__Stripe_xref` (
 `id` int(11) NOT NULL auto_increment,
 `user_id` int(11) NOT NULL,
 `client_id` int(11) NOT NULL,
 `client` varchar(20) NOT NULL,
 `params` text NOT NULL,
 PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__tjlms_user_xref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `join_date` date NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `rhdq7_jticketing_venues`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_venues` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_by` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `venue_category` int(11) NOT NULL,
  `online` int(3) NOT NULL,
  `online_provider` varchar(255) COLLATE utf8_bin NOT NULL,
  `country` int(11) NOT NULL,
  `state_id` int(1) NOT NULL,
  `city` varchar(255) COLLATE utf8_bin NOT NULL,
  `zipcode` varchar(255) COLLATE utf8_bin NOT NULL,
  `address` varchar(255) COLLATE utf8_bin NOT NULL,
  `longitude` float NOT NULL,
  `latitude` float NOT NULL,
  `privacy` int(11) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

--
-- Table structure for table `#__techjoomlaAPI_users`
--
CREATE TABLE IF NOT EXISTS `#__techjoomlaAPI_users` (
  `id` int(11) NOT NULL auto_increment,
  `api` varchar(200) NOT NULL,
  `token` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `client` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

--
-- Table structure for table `#__jticketing_media_files`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_media_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) COLLATE utf8_bin NOT NULL,
  `type` varchar(250) COLLATE utf8_bin NOT NULL,
  `path` varchar(250) COLLATE utf8_bin NOT NULL,
  `state` tinyint(1) NOT NULL,
  `source` varchar(250) COLLATE utf8_bin NOT NULL,
  `original_filename` varchar(250) COLLATE utf8_bin NOT NULL,
  `size` int(11) NOT NULL,
  `storage` varchar(250) COLLATE utf8_bin NOT NULL,
  `created_by` int(11) NOT NULL,
  `access` tinyint(1) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` varchar(500) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

--
-- Table structure for table `#__jticketing_media_files`
--

CREATE TABLE IF NOT EXISTS `#__media_files_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `client` varchar(250) COLLATE utf8_bin NOT NULL,
  `is_gallery` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
