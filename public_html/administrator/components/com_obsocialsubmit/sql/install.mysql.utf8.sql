CREATE TABLE IF NOT EXISTS `#__obsocialsubmit_instances` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `addon` varchar(100) NOT NULL DEFAULT '',
  `addon_type` varchar(6) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `cids` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` int(10) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `debug` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__obsocialsubmit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL,
  `iid` int(11) NOT NULL,
  `cid` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) DEFAULT NULL,
  `publish_up` datetime DEFAULT NULL,
  `processed` tinyint(4) DEFAULT '0',
  `process_time` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`,`aid`,`iid`,`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;