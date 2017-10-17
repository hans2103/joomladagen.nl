CREATE TABLE IF NOT EXISTS `#__tjfields_fields` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`core` int(11) NOT NULL DEFAULT '0',
	`label` varchar(255) NOT NULL,
	`name` varchar(255) NOT NULL,
	`type` varchar(255) NOT NULL,
	`state` tinyint(1) NOT NULL,
	`required` varchar(255) NOT NULL,
	`readonly` int(11) NOT NULL DEFAULT '0',
	`placeholder` varchar(255) NOT NULL,
	`created_by` int(11) NOT NULL,
	`default_value` varchar(255) NOT NULL,
	`min` varchar(255) NOT NULL,
	`max` varchar(255) NOT NULL,
	`rows` int(11) NOT NULL,
	`cols` int(11) NOT NULL,
	`description` varchar(255) NOT NULL,
	`js_function` text NOT NULL,
	`validation_class` text NOT NULL,
	`format` varchar(255) NOT NULL,
	`ordering` int(11) NOT NULL,
	`filterable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - For not filterable field. 1 for filterable field',
	`client` varchar(255) NOT NULL,
	`group_id` int(11) NOT NULL,
	PRIMARY KEY (`id`)
)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__tjfields_fields_value` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`field_id` int(11) NOT NULL COMMENT 'Field table ID',
	`content_id` int(11) NOT NULL COMMENT 'client specific id',
	`value` text NOT NULL,
	`option_id` int(11) DEFAULT NULL,
	`user_id` int(11) NOT NULL,
	`email_id` varchar(255) NOT NULL,
	`client` varchar(255) NOT NULL COMMENT 'client(eg com_jticketing.event)',
	PRIMARY KEY (`id`)
)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__tjfields_groups` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`ordering` int(11) NOT NULL,
	`state` tinyint(1) NOT NULL,
	`created_by` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	`client` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__tjfields_options` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`field_id` int(11) NOT NULL,
	`options` varchar(255) NOT NULL,
	`default_option` varchar(255) NOT NULL,
	`value` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__tjfields_client_type` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`client` varchar(255) NOT NULL,
	`client_type` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__tjfields_category_mapping` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `field_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL COMMENT 'CATEGORY ID FROM JOOMLA CATEGORY TABLE FOR CLIENTS EG CLIENT=COM_QUICK2CART.PRODUCT',
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
