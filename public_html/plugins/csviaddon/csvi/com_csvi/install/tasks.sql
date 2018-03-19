DELETE FROM `#__csvi_tasks` WHERE `component` = 'com_csvi';
INSERT IGNORE INTO `#__csvi_tasks` (`task_name`, `action`, `component`, `url`, `options`) VALUES
('custom', 'export', 'com_csvi', '', 'source,file,custom,layout,customexport_fields,limit.advancedUser'),
('custom', 'import', 'com_csvi', '', 'source,file,custom,fields,limit.advancedUser');