DELETE FROM `#__csvi_availabletables` WHERE `component` = 'com_users';
INSERT IGNORE INTO `#__csvi_availabletables` (`task_name`, `template_table`, `component`, `action`, `enabled`) VALUES
('user', 'user', 'com_users', 'export', '0'),
('user', 'users', 'com_users', 'export', '1'),
('user', 'user', 'com_users', 'import', '0'),
('user', 'users', 'com_users', 'import', '1'),
('usergroup', 'usergroup', 'com_users', 'export', '0'),
('usergroup', 'usergroups', 'com_users', 'export', '1'),
('usergroup', 'usergroup', 'com_users', 'import', '0'),
('usergroup', 'usergroups', 'com_users', 'import', '1'),
('accesslevel', 'accesslevel', 'com_users', 'export', '0'),
('accesslevel', 'viewlevels', 'com_users', 'export', '1'),
('accesslevel', 'accesslevel', 'com_users', 'import', '0'),
('accesslevel', 'viewlevels', 'com_users', 'import', '1');

DELETE FROM `#__csvi_tasks` WHERE `component` = 'com_users';
INSERT IGNORE INTO `#__csvi_tasks` (`task_name`, `action`, `component`, `url`, `options`) VALUES
('user', 'export', 'com_users', 'index.php?option=com_users&view=users', 'source,file,layout,users,fields,limit.advancedUser'),
('user', 'import', 'com_users', 'index.php?option=com_users&view=users', 'source,file,fields,limit.advancedUser'),
('usergroup', 'export', 'com_users', 'index.php?option=com_users&view=groups', 'source,file,layout,fields,limit.advancedUser'),
('usergroup', 'import', 'com_users', 'index.php?option=com_users&view=groups', 'source,file,fields,limit.advancedUser'),
('accesslevel', 'export', 'com_users', 'index.php?option=com_users&view=levels', 'source,file,layout,fields,limit.advancedUser'),
('accesslevel', 'import', 'com_users', 'index.php?option=com_users&view=levels', 'source,file,fields,limit.advancedUser');