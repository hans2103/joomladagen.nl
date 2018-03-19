DELETE FROM `#__csvi_availabletables` WHERE `component` = 'com_content';
INSERT IGNORE INTO `#__csvi_availabletables` (`task_name`, `template_table`, `component`, `action`, `enabled`) VALUES
('content', 'content', 'com_content', 'export', '1'),
('content', 'content', 'com_content', 'import', '1');

DELETE FROM `#__csvi_tasks` WHERE `component` = 'com_content';
INSERT IGNORE INTO `#__csvi_tasks` (`task_name`, `action`, `component`, `url`, `options`) VALUES
('content', 'export', 'com_content', '', 'source,file,layout,content,fields,limit.advancedUser'),
('content', 'import', 'com_content', '', 'source,file,content,fields,limit.advancedUser');