CREATE TABLE IF NOT EXISTS `#__tjvendors_passbook` (
`id` INT(11) UNSIGNED NOT NULL  AUTO_INCREMENT,
`vendor_id` INT(11)  NOT NULL ,
`currency` VARCHAR(255)  NOT NULL ,
`total` DOUBLE(15,2)  NOT NULL ,
`credit` DOUBLE(15,2)  NOT NULL ,
`debit` DOUBLE(15,2)  NOT NULL ,
`reference_order_id` VARCHAR(255)  NOT NULL ,
`transaction_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`client` VARCHAR(255)  NOT NULL ,
`transaction_id` VARCHAR(255)  NOT NULL ,
`status` TINYINT(1)  NOT NULL ,
`params` VARCHAR(255)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__tjvendors_fee` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`vendor_id` INT(11)  NOT NULL ,
`currency` VARCHAR(255)  NOT NULL ,
`client` VARCHAR(255)  NOT NULL ,
`percent_commission` DOUBLE(15,2)  NOT NULL ,
`flat_commission` DOUBLE(15,2)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vendor_client_xref` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`vendor_id` INT(11)  NOT NULL ,
`client` VARCHAR(255)  NOT NULL ,
`approved` tinyint(1)  NOT NULL DEFAULT '1',
`state` tinyint(1)  NOT NULL DEFAULT '1',
`payment_gateway` varchar(255)  NOT NULL DEFAULT '1',
`params` varchar(255)  NOT NULL DEFAULT '1',
PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `#__tjvendors_vendors` (
  `vendor_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `vendor_title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `vendor_description` text NOT NULL,
  `vendor_logo` varchar(255) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `ordering` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` varchar(255) NOT NULL,
  PRIMARY KEY (`vendor_id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `content_history_options`)
SELECT * FROM ( SELECT 'Vendor','com_tjvendors.vendor','{"special":{"dbtable":"#__tjvendors_vendors","key":"id","type":"Vendor","prefix":"TjTable"}}', '{"formFile":"administrator\/components\/com_tjvendors\/models\/forms\/vendor.xml", "hideFields":["checked_out","checked_out_time","params","language"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_tjvendors.vendor')
) LIMIT 1;
