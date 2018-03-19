ALTER TABLE `#__csvi_rules`
	DROP COLUMN `section`;
DROP TABLE IF EXISTS `#__csvi_templates_rules`;
DELETE FROM `#__csvi_settings` WHERE `csvi_setting_id` IN ('2');