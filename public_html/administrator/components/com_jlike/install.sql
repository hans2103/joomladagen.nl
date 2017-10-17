CREATE TABLE IF NOT EXISTS `#__jlike` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `published` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__jlike_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `annotation_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `like` int(11) NOT NULL,
  `dislike` int(11) NOT NULL,
  `date` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__jlike_recommend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `recommend_to` int(11) NOT NULL,
  `recommend_by` int(11) NOT NULL,
  `params` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `#__jlike_annotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordering` INT(11)  NOT NULL ,
  `state` TINYINT(1)  NOT NULL ,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `annotation` text NOT NULL,
  `privacy` int(11) NOT NULL,
  `annotation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_id` int(11) NOT NULL,
  `note` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0 - comment, 1 - note, 2- Review, 3 -Owner Reply for review',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__jlike_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namekey` text,
  `value` text,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__jlike_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `element` text NOT NULL,
  `type` text NOT NULL,
  `title` text NOT NULL,
  `img` text NOT NULL,
  `like_cnt` int(11) NOT NULL,
  `dislike_cnt` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__jlike_likes_lists_xref` (
  `content_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__jlike_like_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(40) NOT NULL,
  `privacy` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

REPLACE INTO `#__jlike` VALUES (1,'1.png','0'),(2,'2.png','0'),(3,'3.png','0'),(4,'4.png','1'),(5,'5.png','0'),(6,'6.png','0');

CREATE TABLE IF NOT EXISTS `#__jlike_content_inviteX_xref` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `content_id` int(15) NOT NULL,
  `importEmailId` int(15) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__jlike_todos` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key, auto increment',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `sender_msg` text NOT NULL COMMENT 'Message given by sender while recommending/assignment',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `content_id` int(11) NOT NULL COMMENT 'FK to jlike_content',
  `assigned_by` int(11) NOT NULL COMMENT 'user id',
  `assigned_to` int(11) NOT NULL COMMENT 'user id',
  `created_date` datetime NOT NULL COMMENT 'Created date',
  `start_date` datetime NOT NULL COMMENT 'todo start date',
  `due_date` datetime NOT NULL COMMENT 'todo end date',
  `status` varchar(100) NOT NULL COMMENT 'I- Incomplete , C- Completed, S- Started',
  `title` varchar(255) NOT NULL COMMENT 'Content tile',
  `type` varchar(100) NOT NULL COMMENT 'Type of the todo (self, reco, assign)',
  `system_generated` tinyint(4) NOT NULL DEFAULT '1',
  `parent_id` int(11) NOT NULL COMMENT 'Shika lesson prerequisites id',
  `list_id` int(11) NOT NULL COMMENT 'jlike list id',
  `modified_date` datetime NOT NULL COMMENT 'modified date',
  `modified_by` int(11) NOT NULL,
  `can_override` tinyint(4) NOT NULL,
  `overriden` tinyint(4) NOT NULL,
  `params` text NOT NULL,
  `todo_list_id` int(11) NOT NULL,
  `ideal_time` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`)
) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__jlike_likeStatusXref` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `content_id` int(10) NOT NULL,
  `status_id` int(11) NOT NULL,
  `user_id` int(15) NOT NULL,
  `cdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS  `#__jlike_rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `rating_upto` int(11) NOT NULL,
  `user_rating` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jlike_reminders`
--
CREATE TABLE IF NOT EXISTS `#__jlike_reminders` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL ,
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`created_by` INT(11)  NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`title` VARCHAR(255)  NOT NULL ,
`days_before` INT(11)  NOT NULL ,
`email_template` TEXT NOT NULL ,
`subject` VARCHAR(255)  NOT NULL ,
`content_type` VARCHAR(255) NOT NULL ,
`cc` VARCHAR(255)  NOT NULL ,
PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- --------------------------------------------------------

--
-- Table structure for table `#__jlike_reminders`
--
DROP TABLE IF EXISTS `#__jlike_reminder_contentids`;
CREATE TABLE `#__jlike_reminder_contentids` (
  `reminder_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL
) DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Table structure for table `#__jlike_reminder_sent`
--
DROP TABLE IF EXISTS `#__jlike_reminder_sent`;
CREATE TABLE `#__jlike_reminder_sent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `todo_id` int(11) NOT NULL,
  `reminder_id` int(11) NOT NULL,
  `sent_on` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
