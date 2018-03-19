DELETE FROM `#__csvi_availabletables` WHERE `component` = 'com_fields';
INSERT IGNORE INTO `#__csvi_availabletables` (`task_name`, `template_table`, `component`, `action`, `enabled`) VALUES
('fields', 'fields', 'com_fields', 'export', '1'),
('fields', 'fields', 'com_fields', 'import', '1'),
('fields', 'fields_categories', 'com_fields', 'export', '1'),
('fields', 'fields_categories', 'com_fields', 'import', '1'),
('fieldgroups', 'fields_groups', 'com_fields', 'export', '1'),
('fieldgroups', 'fields_groups', 'com_fields', 'import', '1');

DELETE FROM `#__csvi_tasks` WHERE `component` = 'com_fields';
INSERT IGNORE INTO `#__csvi_tasks` (`task_name`, `action`, `component`, `url`, `options`) VALUES
('fields', 'export', 'com_fields', 'index.php?option=com_fields', 'source,file,layout,fields,limit.advancedUser'),
('fields', 'import', 'com_fields', 'index.php?option=com_fields', 'source,file,fields,limit.advancedUser'),
('fieldgroups', 'export', 'com_fields', 'index.php?option=com_fields&view=groups', 'source,file,layout,fields,limit.advancedUser'),
('fieldgroups', 'import', 'com_fields', 'index.php?option=com_fields&view=groups', 'source,file,fields,limit.advancedUser');
