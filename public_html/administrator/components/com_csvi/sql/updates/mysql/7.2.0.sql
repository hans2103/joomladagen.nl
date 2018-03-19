ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `table_name` VARCHAR(255) NOT NULL COMMENT 'Table name for custom export' AFTER `field_name`;
