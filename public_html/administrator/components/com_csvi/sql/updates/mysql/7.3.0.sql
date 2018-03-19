ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `field_date_format` VARCHAR(25) NOT NULL COMMENT 'Date format for the export field' AFTER `default_value`;
