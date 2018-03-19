ALTER TABLE `#__csvi_templatefields`
	ADD COLUMN `source_field` VARCHAR(255) NOT NULL COMMENT 'Field name of the source table' AFTER `xml_node`;
