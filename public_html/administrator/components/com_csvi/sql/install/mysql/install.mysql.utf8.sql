CREATE TABLE IF NOT EXISTS `#__csvi_availablefields` (
	`csvi_availablefield_id` INT(11) NOT NULL AUTO_INCREMENT,
	`csvi_name` VARCHAR(255) NOT NULL,
	`component_name` VARCHAR(55) NOT NULL,
	`component_table` VARCHAR(55) NOT NULL,
	`component` VARCHAR(55) NOT NULL,
	`action` VARCHAR(6) NOT NULL,
	`isprimary` TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`csvi_availablefield_id`),
	UNIQUE INDEX `component_name_table` (`component_name`, `component_table`, `component`, `action`)
) CHARSET=utf8 COMMENT='Available fields for CSVI';

CREATE TABLE IF NOT EXISTS `#__csvi_availabletables` (
	`csvi_availabletable_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`checked_out` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`task_name` VARCHAR(55) NOT NULL,
	`template_table` VARCHAR(55) NOT NULL,
	`component` VARCHAR(55) NOT NULL,
	`action` VARCHAR(6) NOT NULL,
	`indexed` TINYINT(1) NOT NULL DEFAULT '0',
	`enabled` TINYINT(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`csvi_availabletable_id`),
	UNIQUE INDEX `type_name` (`task_name`, `template_table`, `component`, `action`)
) CHARSET=utf8 COMMENT='Template tables used per template type for CSVI';

CREATE TABLE IF NOT EXISTS `#__csvi_currency` (
  `currency_id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `currency_code` varchar(3) DEFAULT NULL,
  `currency_rate` varchar(55) DEFAULT NULL,
  PRIMARY KEY (`currency_id`),
  UNIQUE KEY `currency_code` (`currency_code`)
) CHARSET=utf8 COMMENT='Curriencies and exchange rates for CSVI';

CREATE TABLE IF NOT EXISTS `#__csvi_icecat_index` (
  `path` varchar(100) DEFAULT NULL,
  `product_id` int(2) DEFAULT NULL,
  `updated` int(14) DEFAULT NULL,
  `quality` varchar(6) DEFAULT NULL,
  `supplier_id` int(1) DEFAULT NULL,
  `prod_id` varchar(16) DEFAULT NULL,
  `catid` int(3) DEFAULT NULL,
  `m_prod_id` varchar(50) DEFAULT NULL,
  `ean_upc` varchar(10) DEFAULT NULL,
  `on_market` int(1) DEFAULT NULL,
  `country_market` varchar(10) DEFAULT NULL,
  `model_name` varchar(26) DEFAULT NULL,
  `product_view` int(5) DEFAULT NULL,
  `high_pic` varchar(51) DEFAULT NULL,
  `high_pic_size` int(5) DEFAULT NULL,
  `high_pic_width` int(3) DEFAULT NULL,
  `high_pic_height` int(3) DEFAULT NULL,
  `m_supplier_id` int(3) DEFAULT NULL,
  `m_supplier_name` varchar(51) DEFAULT NULL,
  `ean_upc_is_approved` TINYINT(1) DEFAULT NULL,
  KEY `product_mpn` (`prod_id`),
  KEY `manufacturer_name` (`supplier_id`)
) CHARSET=utf8 COMMENT='ICEcat index data for CSVI';

CREATE TABLE IF NOT EXISTS `#__csvi_icecat_suppliers` (
  `supplier_id` int(11) unsigned NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  UNIQUE KEY `Unique supplier` (`supplier_id`,`supplier_name`),
  KEY `Supplier name` (`supplier_name`)
) CHARSET=utf8 COMMENT='ICEcat supplier data for CSVI';

CREATE TABLE IF NOT EXISTS `#__csvi_logdetails` (
	`csvi_logdetail_id` INT(11) NOT NULL AUTO_INCREMENT,
	`csvi_log_id` INT(11) NOT NULL,
	`line` INT(11) NOT NULL,
	`description` TEXT NOT NULL,
	`result` VARCHAR(45) NOT NULL,
	`status` VARCHAR(45) NOT NULL,
  `area` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`csvi_logdetail_id`)
) CHARSET=utf8 COMMENT='Log details for CSVI';

CREATE TABLE IF NOT EXISTS `#__csvi_logs` (
	`csvi_log_id` INT(11) NOT NULL AUTO_INCREMENT,
	`userid` INT(11) NOT NULL,
  `start` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`addon` VARCHAR(50) NOT NULL,
	`action` VARCHAR(255) NOT NULL,
	`action_type` VARCHAR(255) NOT NULL DEFAULT '',
	`template_name` VARCHAR(255) NULL DEFAULT NULL,
	`records` INT(11) NOT NULL,
	`file_name` VARCHAR(255) NULL DEFAULT NULL,
	`run_cancelled` TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`csvi_log_id`)
) CHARSET=utf8 COMMENT='Log results for CSVI';

CREATE TABLE IF NOT EXISTS `#__csvi_mapheaders` (
	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`map_id` INT(10) NOT NULL,
	`csvheader` VARCHAR(100) NOT NULL,
	`templateheader` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`id`)
) CHARSET=utf8 COMMENT='Holds map field mapping';

CREATE TABLE IF NOT EXISTS `#__csvi_maps` (
  `csvi_map_id` INT(10) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NULL DEFAULT NULL,
  `mapfile` VARCHAR(100) NULL DEFAULT NULL,
  `action` VARCHAR(100) NULL DEFAULT NULL,
  `component` VARCHAR(100) NULL DEFAULT NULL,
  `operation` VARCHAR(100) NULL DEFAULT NULL,
  `auto_detect_delimiters` TINYINT(1) UNSIGNED NULL DEFAULT '1',
  `field_delimiter` VARCHAR(1) NULL DEFAULT ',',
  `text_enclosure` VARCHAR(1) NULL DEFAULT '"',
  `im_mac` TINYINT(1) NULL DEFAULT '0',
  `locked_by` INT(10) NULL DEFAULT NULL,
  `locked_on` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`csvi_map_id`)
) CHARSET=utf8 COMMENT='Holds map configurations';

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

CREATE TABLE IF NOT EXISTS `#__csvi_related_categories` (
	`product_sku` varchar(64) NOT NULL,
	`related_cat` text NOT NULL
) CHARSET=utf8 COMMENT='Related categories import for CSVI';

CREATE TABLE IF NOT EXISTS `#__csvi_related_products` (
  `product_sku` varchar(64) NOT NULL,
  `related_sku` text NOT NULL
) CHARSET=utf8 COMMENT='Related products import for CSVI';

CREATE TABLE IF NOT EXISTS `#__csvi_rules` (
  `csvi_rule_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `ordering` INT(11) NOT NULL DEFAULT '0',
  `plugin` VARCHAR(255) NOT NULL,
  `plugin_params` TEXT NOT NULL,
  `locked_by` INT(11) UNSIGNED NULL DEFAULT '0',
  `created_by` INT(11) UNSIGNED NULL DEFAULT '0',
  `modified_by` INT(11) UNSIGNED NULL DEFAULT '0',
  `locked_on` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  `created_on` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  `modified_on` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`csvi_rule_id`)
) CHARSET=utf8 COMMENT='Rules for CSVI';

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

CREATE TABLE IF NOT EXISTS `#__csvi_templatefields` (
  `csvi_templatefield_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for the template field',
  `csvi_template_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'The template ID',
  `field_name` VARCHAR(255) NOT NULL COMMENT 'Name for the field',
  `table_name` VARCHAR(255) NOT NULL COMMENT 'Table name for custom export',
  `xml_node` VARCHAR(255) NOT NULL COMMENT 'The XML node path',
  `source_field` VARCHAR(255) NOT NULL COMMENT 'Field name of the source table',
  `column_header` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Header for the column',
  `default_value` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Default value for the field',
  `field_date_format` VARCHAR(25) NOT NULL DEFAULT '' COMMENT 'Date format for the export field',
  `enabled` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Process the field',
  `sort` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sort the field',
  `cdata` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Use the CDATA tag',
  `ordering` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'The order of the field',
  `created_by` INT(11) NOT NULL DEFAULT '0',
  `locked_by` INT(11) NOT NULL DEFAULT '0',
  `modified_by` INT(11) NOT NULL DEFAULT '0',
  `created_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`csvi_templatefield_id`)
) CHARSET=utf8 COMMENT='Holds the fields for a CSVI template';

CREATE TABLE IF NOT EXISTS `#__csvi_templatefields_rules` (
  `csvi_templatefields_rule_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for the cross reference',
  `csvi_templatefield_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID of the field',
  `csvi_rule_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID of the replacement rule',
  PRIMARY KEY (`csvi_templatefields_rule_id`),
  UNIQUE INDEX `Unique` (`csvi_templatefield_id`, `csvi_rule_id`),
  INDEX `Rules` (`csvi_rule_id`)
) CHARSET=utf8 COMMENT='Holds the replacement cross reference for a CSVI template field';

CREATE TABLE IF NOT EXISTS `#__csvi_templates` (
	`csvi_template_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for the saved setting',
	`template_name` VARCHAR(255) NOT NULL COMMENT 'Name for the saved setting',
	`settings` TEXT NOT NULL COMMENT 'The actual settings',
  `advanced` TINYINT(1) NOT NULL DEFAULT '0',
	`action` VARCHAR(6) NOT NULL DEFAULT 'import' COMMENT 'The type of template',
  `frontend` TINYINT(3) NOT NULL DEFAULT '0' COMMENT 'Enabled front-end/cron usage',
  `secret` VARCHAR(25) NULL DEFAULT NULL COMMENT 'The secret key to use for automated import/export',
  `log` TINYINT(1) NOT NULL DEFAULT '0',
  `lastrun` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`enabled` INT(11) NOT NULL DEFAULT '0',
	`ordering` INT(11) NOT NULL DEFAULT '0',
	`locked_by` INT(11) NULL DEFAULT '0',
	`locked_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`csvi_template_id`)
) CHARSET=utf8 COMMENT='Stores the template settings for CSVI';

CREATE TABLE IF NOT EXISTS `#__csvi_sefurls` (
	`sefurl_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`plainurl` VARCHAR(255) NOT NULL,
	`sefurl` VARCHAR(512) NOT NULL,
	PRIMARY KEY (`sefurl_id`),
	INDEX `plainurl` (`plainurl`)
) CHARSET=utf8 COMMENT='Stores cached SEF URLs for export';
