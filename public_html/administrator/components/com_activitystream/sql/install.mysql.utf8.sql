CREATE TABLE IF NOT EXISTS `#__tj_activities` (
	`id`       INT(11)     NOT NULL AUTO_INCREMENT,
	`actor` text(900),
	`actor_id` text(900),
	`object` text(900),
	`object_id` text(900),
	`target` text(900),
	`target_id` text(900),
	`type` text(900),
	`template` text(900),
	`formatted_text` text(900),
	`access` tinyint(2) DEFAULT 1,
	`state` tinyint(2) DEFAULT 1,
	`location` text(900),
	`latitude` float(10,6),
	`longitude` float(10,6),
	`created_date` datetime DEFAULT NULL,
	`updated_date` datetime DEFAULT NULL,
	PRIMARY KEY (`id`)
)
	DEFAULT CHARSET =utf8 AUTO_INCREMENT =1;

