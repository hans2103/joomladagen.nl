/* Product import */
INSERT IGNORE INTO `#__csvi_availablefields` (`csvi_name`, `component_name`, `component_table`, `component`, `action`) VALUES
('skip', 'skip', 'product', 'com_j2store', 'import'),
('combine', 'combine', 'product', 'com_j2store', 'import'),
('product_delete', 'product_delete', 'product', 'com_j2store', 'import'),
('category_path', 'category_path', 'product', 'com_j2store', 'import'),
('manufacturer_name', 'manufacturer_name', 'product', 'com_j2store', 'import'),
('vendor_user_email', 'vendor_user_email', 'product', 'com_j2store', 'import'),
('title', 'title', 'product', 'com_j2store', 'import'),
('alias', 'alias', 'product', 'com_j2store', 'import'),
('catid', 'catid', 'product', 'com_j2store', 'import'),
('quantity', 'quantity', 'product', 'com_j2store', 'import'),
('product_css_class', 'product_css_class', 'product', 'com_j2store', 'import'),
('taxprofile_name', 'taxprofile_name', 'product', 'com_j2store', 'import'),
('download_limit', 'download_limit', 'product', 'com_j2store', 'import'),
('download_expiry', 'download_expiry', 'product', 'com_j2store', 'import'),

/* Product export */
('custom', 'custom', 'product', 'com_j2store', 'export'),
('category_path', 'category_path', 'product', 'com_j2store', 'export'),
('manufacturer_name', 'manufacturer_name', 'product', 'com_j2store', 'export'),
('vendor_user_email', 'vendor_user_email', 'product', 'com_j2store', 'export'),
('title', 'title', 'product', 'com_j2store', 'export'),
('alias', 'alias', 'product', 'com_j2store', 'export'),
('catid', 'catid', 'product', 'com_j2store', 'export'),
('product_css_class', 'product_css_class', 'product', 'com_j2store', 'export'),
('quantity', 'quantity', 'product', 'com_j2store', 'export'),
('taxprofile_name', 'taxprofile_name', 'product', 'com_j2store', 'export'),
('download_limit', 'download_limit', 'product', 'com_j2store', 'export'),
('download_expiry', 'download_expiry', 'product', 'com_j2store', 'export'),

/* Price import */
('skip', 'skip', 'price', 'com_j2store', 'import'),
('combine', 'combine', 'price', 'com_j2store', 'import'),
('price_delete', 'price_delete', 'price', 'com_j2store', 'import'),
('sku', 'sku', 'price', 'com_j2store', 'import'),
('price_new', 'price_new', 'price', 'com_j2store', 'import'),
('customer_group_name', 'customer_group_name', 'price', 'com_j2store', 'import'),
('customer_group_name_new', 'customer_group_name_new', 'price', 'com_j2store', 'import'),

/* Price export */
('custom', 'custom', 'price', 'com_j2store', 'export'),
('sku', 'sku', 'price', 'com_j2store', 'export'),
('customer_group_name', 'customer_group_name', 'price', 'com_j2store', 'export'),

/* Product files import */
('skip', 'skip', 'productfile', 'com_j2store', 'import'),
('combine', 'combine', 'productfile', 'com_j2store', 'import'),
('sku', 'sku', 'productfile', 'com_j2store', 'import'),

/* Product files export */
('custom', 'custom', 'productfile', 'com_j2store', 'export'),
('sku', 'sku', 'productfile', 'com_j2store', 'export'),

/* Order import */
('skip', 'skip', 'order', 'com_j2store', 'import'),
('combine', 'combine', 'order', 'com_j2store', 'import'),
('order_state', 'order_state', 'order', 'com_j2store', 'import'),
('order_state_id', 'order_state_id', 'order', 'com_j2store', 'import'),
('comment', 'comment', 'order', 'com_j2store', 'import'),
('notify_customer', 'notify_customer', 'order', 'com_j2store', 'import'),

/* Order export */
('custom', 'custom', 'order', 'com_j2store', 'export'),
('order_state', 'order_state', 'order', 'com_j2store', 'export'),

/* Order items import */
('skip', 'skip', 'orderitem', 'com_j2store', 'import'),
('combine', 'combine', 'orderitem', 'com_j2store', 'import'),
('orderitem_taxprofile_name', 'orderitem_taxprofile_name', 'orderitem', 'com_j2store', 'import'),
('sku', 'sku', 'orderitem', 'com_j2store', 'import'),
('thumb_image', 'thumb_image', 'orderitem', 'com_j2store', 'import'),
('shipping', 'shipping', 'orderitem', 'com_j2store', 'import'),
('vendor_user_email', 'vendor_user_email', 'orderitem', 'com_j2store', 'import'),

/* Order items export */
('custom', 'custom', 'orderitem', 'com_j2store', 'export'),
('sku', 'sku', 'orderitem', 'com_j2store', 'export'),
('thumb_image', 'thumb_image', 'orderitem', 'com_j2store', 'export'),
('shipping', 'shipping', 'orderitem', 'com_j2store', 'export'),
('vendor_user_email', 'vendor_user_email', 'orderitem', 'com_j2store', 'export'),
('orderitem_taxprofile_name', 'orderitem_taxprofile_name', 'orderitem', 'com_j2store', 'export'),
('name', 'name', 'orderitem', 'com_j2store', 'export'),

/* Product images import */
('skip', 'skip', 'productimage', 'com_j2store', 'import'),
('combine', 'combine', 'productimage', 'com_j2store', 'import'),
('sku', 'sku', 'productimage', 'com_j2store', 'import'),

/* Product images export */
('custom', 'custom', 'productimage', 'com_j2store', 'export'),
('sku', 'sku', 'productimage', 'com_j2store', 'export'),

/* Product filters import */
('skip', 'skip', 'productfilter', 'com_j2store', 'import'),
('combine', 'combine', 'productfilter', 'com_j2store', 'import'),
('sku', 'sku', 'productfilter', 'com_j2store', 'import'),
('filter_name', 'filter_name', 'productfilter', 'com_j2store', 'import'),
('filter_group_name', 'filter_group_name', 'productfilter', 'com_j2store', 'import'),
('product_id', 'product_id', 'productfilter', 'com_j2store', 'import'),
('filter_id', 'filter_id', 'productfilter', 'com_j2store', 'import'),

/* Product filters export */
('custom', 'custom', 'productfilter', 'com_j2store', 'export'),
('sku', 'sku', 'productfilter', 'com_j2store', 'export'),
('filter_group_name', 'filter_group_name', 'productfilter', 'com_j2store', 'export'),
('filter_name', 'filter_name', 'productfilter', 'com_j2store', 'export'),
('product_id', 'product_id', 'productfilter', 'com_j2store', 'import'),
('filter_id', 'filter_id', 'productfilter', 'com_j2store', 'import'),
('sku', 'sku', 'productimage', 'com_j2store', 'export'),

/* Geo zone rules import */
('skip', 'skip', 'j2store_geozonerules', 'com_j2store', 'import'),
('combine', 'combine', 'j2store_geozonerules', 'com_j2store', 'import'),
('geozone_name_new', 'geozone_name_new', 'j2store_geozonerules', 'com_j2store', 'import'),
('geozone_name_delete', 'geozone_name_delete', 'j2store_geozonerules', 'com_j2store', 'import'),
('zone_name_new', 'zone_name_new', 'j2store_geozonerules', 'com_j2store', 'import'),
('zone_code_new', 'zone_code_new', 'j2store_geozonerules', 'com_j2store', 'import'),

/* Geo zone rules export */
('custom', 'custom', 'j2store_geozonerules', 'com_j2store', 'export');