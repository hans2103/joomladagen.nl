RENAME TABLE `#__csvi_available_fields` TO `#__csvi_availablefields`;
ALTER TABLE `#__csvi_availablefields`
	CHANGE COLUMN `id` `csvi_availablefield_id` INT(11) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `#__csvi_availablefields`
	ADD COLUMN `action` VARCHAR(6) NOT NULL AFTER `component`;

RENAME TABLE `#__csvi_template_tables` TO `#__csvi_availabletables`;
ALTER TABLE `#__csvi_availabletables`
	DROP INDEX `type_name`;
ALTER TABLE `#__csvi_availabletables`
	CHANGE COLUMN `id` `csvi_availabletable_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `#__csvi_availabletables`
	ALTER `template_type_name` DROP DEFAULT;
	ALTER TABLE `#__csvi_availabletables`
	CHANGE COLUMN `template_type_name` `task_name` VARCHAR(55) NOT NULL AFTER `checked_out_time`;
ALTER TABLE `#__csvi_availabletables`
	ADD COLUMN `action` VARCHAR(6) NOT NULL AFTER `component`;
ALTER TABLE `#__csvi_availabletables`
	ADD COLUMN `enabled` INT(1) NOT NULL DEFAULT '1' AFTER `indexed`;
ALTER TABLE `#__csvi_availabletables`
	ADD UNIQUE INDEX `type_name` (`task_name`, `template_table`, `component`, `action`);

RENAME TABLE `#__csvi_log_details` TO `#__csvi_logdetails`;
ALTER TABLE `#__csvi_logdetails`
	CHANGE COLUMN `id` `csvi_logdetail_id` INT(11) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `#__csvi_logdetails`
	CHANGE COLUMN `log_id` `csvi_log_id` INT(11) NOT NULL AFTER `csvi_logdetail_id`;
ALTER TABLE `#__csvi_logdetails`
	ADD COLUMN `area` VARCHAR(255) NOT NULL AFTER `status`;

ALTER TABLE `#__csvi_logs`
	CHANGE COLUMN `id` `csvi_log_id` INT(11) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `#__csvi_logs`
	ADD COLUMN `start` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `userid`;
ALTER TABLE `#__csvi_logs`
	ADD COLUMN `end` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `start`;
ALTER TABLE `#__csvi_logs`
	ADD COLUMN `addon` VARCHAR(50) NOT NULL AFTER `end`;
ALTER TABLE `#__csvi_logs`
	DROP COLUMN `logstamp`;
ALTER TABLE `#__csvi_logs`
	DROP COLUMN `run_id`;

ALTER TABLE `#__csvi_maps`
	CHANGE COLUMN `id` `csvi_map_id` INT(10) NOT NULL AUTO_INCREMENT FIRST;
ALTER TABLE `#__csvi_maps`
	CHANGE COLUMN `name` `title` VARCHAR(100) NULL DEFAULT NULL AFTER `csvi_map_id`;
ALTER TABLE `#__csvi_maps`
	CHANGE COLUMN `checked_out` `locked_by` INT(10) NULL DEFAULT NULL AFTER `operation`;
ALTER TABLE `#__csvi_maps`
	CHANGE COLUMN `checked_out_time` `locked_on` DATETIME NULL DEFAULT NULL AFTER `locked_by`;

CREATE TABLE IF NOT EXISTS `#__csvi_processed` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ukey` VARCHAR(255) NULL DEFAULT NULL,
	`action` VARCHAR(50) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) CHARSET=utf8 COMMENT='Holds temporary identifiers';

CREATE TABLE IF NOT EXISTS `#__csvi_processes` (
	`csvi_process_id` INT(11) NOT NULL AUTO_INCREMENT,
	`csvi_template_id` INT(11) NOT NULL,
	`csvi_log_id` INT(11) NOT NULL,
	`userId` INT(11) NOT NULL,
	`processfile` VARCHAR(255) NOT NULL,
	`processfolder` VARCHAR(255) NOT NULL,
	`position` INT(11) NOT NULL,
	PRIMARY KEY (`csvi_process_id`)
) CHARSET=utf8 COMMENT='Contains the running import/export processes';

CREATE TABLE IF NOT EXISTS `#__csvi_rules` (
	`csvi_rule_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL,
	`action` VARCHAR(100) NOT NULL,
	`ordering` INT(11) NOT NULL DEFAULT '0',
	`plugin` VARCHAR(255) NOT NULL,
	`plugin_params` TEXT NOT NULL,
	`section` VARCHAR(25) NOT NULL,
	`locked_by` INT(11) UNSIGNED NULL DEFAULT '0',
	`created_by` INT(11) UNSIGNED NULL DEFAULT '0',
	`modified_by` INT(11) UNSIGNED NULL DEFAULT '0',
	`locked_on` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
	`created_on` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
	`modified_on` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`csvi_rule_id`)
) CHARSET=utf8 COMMENT='Rules for CSVI';

DROP TABLE `#__csvi_replacements`;

ALTER TABLE `#__csvi_settings`
	CHANGE COLUMN `id` `csvi_setting_id` INT(11) NOT NULL AUTO_INCREMENT FIRST;

CREATE TABLE IF NOT EXISTS `#__csvi_tasks` (
	`csvi_task_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`locked_by` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`locked_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`task_name` VARCHAR(55) NOT NULL,
	`action` VARCHAR(55) NOT NULL,
	`component` VARCHAR(55) NOT NULL COMMENT 'Name of the component',
	`url` VARCHAR(100) NULL DEFAULT NULL COMMENT 'The URL of the page the import is for',
	`options` VARCHAR(255) NOT NULL DEFAULT 'fields' COMMENT 'The template pages to show for the template type',
	`enabled` TINYINT(1) NOT NULL DEFAULT '1',
	`ordering` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`csvi_task_id`),
	UNIQUE INDEX `type_name` (`task_name`, `action`, `component`)
) CHARSET=utf8 COMMENT='Template types for CSVI';

RENAME TABLE `#__csvi_template_fields` TO `#__csvi_templatefields`;

ALTER TABLE `#__csvi_templatefields`
	CHANGE COLUMN `id` `csvi_templatefield_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for the template field' FIRST;
ALTER TABLE `#__csvi_templatefields`
	CHANGE COLUMN `template_id` `csvi_template_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'The template ID' AFTER `csvi_templatefield_id`;
ALTER TABLE `#__csvi_templatefields`
	CHANGE COLUMN `file_field_name` `xml_node` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Name for the field from the file' AFTER `csvi_template_id`;
ALTER TABLE `#__csvi_templatefields`
	DROP COLUMN `template_field_name`;
ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `enabled` TINYINT(1) NOT NULL AFTER `default_value`;
ALTER TABLE `#__csvi_templatefields`
	CHANGE COLUMN `sort` `sort` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sort the field' AFTER `combine_char`;
ALTER TABLE `#__csvi_templatefields`
	CHANGE COLUMN `cdata` `cdata` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Use the CDATA tag' AFTER `sort`;
ALTER TABLE `#__csvi_templatefields`
	DROP COLUMN `combine_char`;
ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `plugin` MEDIUMTEXT NOT NULL AFTER `cdata`;
ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `created_by` INT(11) NOT NULL AFTER `plugin`;
ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `locked_by` INT(11) NOT NULL AFTER `created_by`;
ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `modified_by` INT(11) NOT NULL AFTER `locked_by`;
ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `created_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `modified_by`;
ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `locked_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_on`;
ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `modified_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `locked_on`;

CREATE TABLE IF NOT EXISTS `#__csvi_templatefields_rules` (
	`csvi_templatefields_rule_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for the cross reference',
	`csvi_templatefield_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID of the field',
	`csvi_rule_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID of the replacement rule',
	PRIMARY KEY (`csvi_templatefields_rule_id`),
	UNIQUE INDEX `Unique` (`csvi_templatefield_id`, `csvi_rule_id`),
	INDEX `Rules` (`csvi_rule_id`)
) CHARSET=utf8 COMMENT='Holds the replacement cross reference for a CSVI template field';

DROP TABLE `#__csvi_template_fields_combine`;

DROP TABLE `#__csvi_template_fields_replacement`;

RENAME TABLE `#__csvi_template_settings` TO `#__csvi_templates`;
ALTER TABLE `#__csvi_templates`
	CHANGE COLUMN `id` `csvi_template_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for the saved setting' FIRST;
ALTER TABLE `#__csvi_templates`
	CHANGE COLUMN `name` `template_name` VARCHAR(255) NOT NULL COMMENT 'Name for the saved setting' AFTER `csvi_template_id`;
ALTER TABLE `#__csvi_templates`
	ADD COLUMN `advanced` TINYINT(1) NOT NULL DEFAULT '0' AFTER `settings`;
ALTER TABLE `#__csvi_templates`
	CHANGE COLUMN `process` `action` VARCHAR(6) NOT NULL DEFAULT 'import' COMMENT 'The ENGINE of template' AFTER `advanced`;
ALTER TABLE `#__csvi_templates`
	ADD COLUMN `frontend` TINYINT(3) NOT NULL DEFAULT '0' AFTER `action`;
ALTER TABLE `#__csvi_templates`
	ADD COLUMN `secret` VARCHAR(25) NOT NULL AFTER `frontend`;
ALTER TABLE `#__csvi_templates`
	ADD COLUMN `log` TINYINT(1) NOT NULL DEFAULT '0' AFTER `secret`;
ALTER TABLE `#__csvi_templates`
	ADD COLUMN `lastrun` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `log`;
ALTER TABLE `#__csvi_templates`
	ADD COLUMN `enabled` INT(11) NOT NULL DEFAULT '0' AFTER `lastrun`;
ALTER TABLE `#__csvi_templates`
	ADD COLUMN `ordering` INT(11) NOT NULL DEFAULT '0' AFTER `enabled`;
ALTER TABLE `#__csvi_templates`
	ADD COLUMN `locked_by` INT(11) NOT NULL DEFAULT '0' AFTER `ordering`;
ALTER TABLE `#__csvi_templates`
	ADD COLUMN `locked_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `locked_by`;
