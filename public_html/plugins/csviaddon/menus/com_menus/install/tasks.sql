DELETE FROM `#__csvi_availabletables` WHERE `component` = 'com_menus';
INSERT IGNORE INTO `#__csvi_availabletables` (`task_name`, `template_table`, `component`, `action`, `enabled`) VALUES
('menu', 'menu', 'com_menus', 'export', '1'),
('menu', 'menu', 'com_menus', 'import', '1');

DELETE FROM `#__csvi_tasks` WHERE `component` = 'com_menus';
INSERT IGNORE INTO `#__csvi_tasks` (`task_name`, `action`, `component`, `url`, `options`) VALUES
('menu', 'export', 'com_menus', 'index.php?option=com_menus', 'source,file,menu,layout,fields,limit.advancedUser'),
('menu', 'import', 'com_menus', 'index.php?option=com_menus', 'source,file,menu,fields,limit.advancedUser');
