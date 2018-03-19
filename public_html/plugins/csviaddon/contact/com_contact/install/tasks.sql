DELETE FROM `#__csvi_availabletables` WHERE `component` = 'com_contact';
INSERT IGNORE INTO `#__csvi_availabletables` (`task_name`, `template_table`, `component`, `action`, `enabled`) VALUES
('contact', 'contact_details', 'com_contact', 'export', '1'),
('contact', 'contact_details', 'com_contact', 'import', '1');

DELETE FROM `#__csvi_tasks` WHERE `component` = 'com_contact';
INSERT IGNORE INTO `#__csvi_tasks` (`task_name`, `action`, `component`, `url`, `options`) VALUES
('contact', 'export', 'com_contact', 'index.php?option=com_contacts', 'source,file,layout,fields,limit.advancedUser'),
('contact', 'import', 'com_contact', 'index.php?option=com_contacts', 'source,file,contact,fields,limit.advancedUser');
