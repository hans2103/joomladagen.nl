DELETE FROM `#__csvi_availabletables` WHERE `component` = 'com_categories';
INSERT IGNORE INTO `#__csvi_availabletables` (`task_name`, `template_table`, `component`, `action`, `enabled`) VALUES
('category', 'category', 'com_categories', 'export', '0'),
('category', 'categories', 'com_categories', 'export', '1'),
('category', 'category', 'com_categories', 'import', '0'),
('category', 'categories', 'com_categories', 'import', '1');

DELETE FROM `#__csvi_tasks` WHERE `component` = 'com_categories';
INSERT IGNORE INTO `#__csvi_tasks` (`task_name`, `action`, `component`, `url`, `options`) VALUES
('category', 'export', 'com_categories', '', 'source,file,layout,fields,limit.advancedUser'),
('category', 'import', 'com_categories', '', 'source,file,category,fields,limit.advancedUser');