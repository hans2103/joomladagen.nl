CREATE TABLE IF NOT EXISTS `#__csvi_sefurls` (
	`sefurl_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`plainurl` VARCHAR(255) NOT NULL,
	`sefurl` VARCHAR(512) NOT NULL,
	PRIMARY KEY (`sefurl_id`),
	INDEX `plainurl` (`plainurl`)
) CHARSET=utf8 COMMENT='Stores cached SEF URLs for export';