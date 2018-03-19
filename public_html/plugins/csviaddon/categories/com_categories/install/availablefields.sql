/* Joomla category import */
INSERT IGNORE INTO `#__csvi_availablefields` (`csvi_name`, `component_name`, `component_table`, `component`, `action`) VALUES
('skip', 'skip', 'category', 'com_categories', 'import'),
('combine', 'combine', 'category', 'com_categories', 'import'),
('category_path', 'category_path', 'category', 'com_categories', 'import'),
('category_layout', 'category_layout', 'category', 'com_categories', 'import'),
('image', 'image', 'category', 'com_categories', 'import'),
('meta_author', 'meta_author', 'category', 'com_categories', 'import'),
('meta_robots', 'meta_robots', 'category', 'com_categories', 'import'),

/* Joomla category export */
('custom', 'custom', 'category', 'com_categories', 'export'),
('category_path', 'category_path', 'category', 'com_categories', 'export'),
('category_layout', 'category_layout', 'category', 'com_categories', 'export'),
('image', 'image', 'category', 'com_categories', 'export'),
('meta_author', 'meta_author', 'category', 'com_categories', 'export'),
('meta_robots', 'meta_robots', 'category', 'com_categories', 'export');
