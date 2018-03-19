/* Joomla user import */
INSERT IGNORE INTO `#__csvi_availablefields` (`csvi_name`, `component_name`, `component_table`, `component`, `action`) VALUES
('skip', 'skip', 'user', 'com_users', 'import'),
('combine', 'combine', 'user', 'com_users', 'import'),
('password_crypt', 'password_crypt', 'user', 'com_users', 'import'),
('group_id', 'group_id', 'user', 'com_users', 'import'),
('usergroup_name', 'usergroup_name', 'user', 'com_users', 'import'),
('fullname', 'fullname', 'user', 'com_users', 'import'),

/* Joomla user export */
('custom', 'custom', 'user', 'com_users', 'export'),
('usergroup_name', 'usergroup_name', 'user', 'com_users', 'export'),
('fullname', 'fullname', 'user', 'com_users', 'export'),

/* Joomla user group import */
('skip', 'skip', 'usergroup', 'com_users', 'import'),
('combine', 'combine', 'usergroup', 'com_users', 'import'),
('parent_name', 'parent_name', 'usergroup', 'com_users', 'import'),
('usergroup_delete', 'usergroup_delete', 'usergroup', 'com_users', 'import'),

/* Joomla user group export */
('custom', 'custom', 'usergroup', 'com_users', 'export'),
('parent_name', 'parent_name', 'usergroup', 'com_users', 'export'),

/* Joomla access level import */
('skip', 'skip', 'accesslevel', 'com_users', 'import'),
('combine', 'combine', 'accesslevel', 'com_users', 'import'),
('usergroup_name', 'usergroup_name', 'accesslevel', 'com_users', 'import'),

/* Joomla access level export */
('custom', 'custom', 'accesslevel', 'com_users', 'export'),
('usergroup_name', 'usergroup_name', 'accesslevel', 'com_users', 'export');