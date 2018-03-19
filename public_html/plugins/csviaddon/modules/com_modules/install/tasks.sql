DELETE FROM `#__csvi_availabletables` WHERE `component` = 'com_modules';
INSERT IGNORE INTO `#__csvi_availabletables` (`task_name`, `template_table`, `component`, `action`, `enabled`) VALUES
('modules', 'modules', 'com_modules', 'export', '1'),
('modules', 'modules', 'com_modules', 'import', '1');

DELETE FROM `#__csvi_tasks` WHERE `component` = 'com_modules';
INSERT IGNORE INTO `#__csvi_tasks` (`task_name`, `action`, `component`, `url`, `options`) VALUES
('modules', 'export', 'com_modules', '', 'source,file,layout,module,fields,limit.advancedUser'),
('modules', 'import', 'com_modules', '', 'source,file,module,fields,limit.advancedUser');