CREATE TABLE IF NOT EXISTS `#__xmap_sitemap` (
  `id`             INT(11)          UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id`       INT(11)          UNSIGNED NOT NULL DEFAULT '0',
  `title`          VARCHAR(250)                       DEFAULT NULL,
  `alias`          VARCHAR(250)                       DEFAULT NULL,
  `introtext`      TEXT                               DEFAULT NULL,
  `params`         TEXT                               DEFAULT NULL,
  `selections`     TEXT                               DEFAULT NULL,
  `excluded_items` TEXT                               DEFAULT NULL,
  `published`      TINYINT(1)                         DEFAULT NULL,
  `access`         INT                                DEFAULT NULL,
  `created`        DATETIME                  NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`     INT(11)          UNSIGNED NOT NULL DEFAULT '0',
  `modified`       DATETIME                  NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`    INT(11)          UNSIGNED NOT NULL DEFAULT '0',
  `count_xml`      INT(11)          UNSIGNED NOT NULL DEFAULT '0',
  `count_html`     INT(11)          UNSIGNED NOT NULL DEFAULT '0',
  `views_xml`      INT(11)          UNSIGNED NOT NULL DEFAULT '0',
  `views_html`     INT(11)          UNSIGNED NOT NULL DEFAULT '0',
  `lastvisit_xml`  INT(11)          UNSIGNED          DEFAULT NULL,
  `lastvisit_html` INT(11)          UNSIGNED          DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_state` (`published`)
);

CREATE TABLE IF NOT EXISTS `#__xmap_items` (
  `uid`        VARCHAR(250)                    NOT NULL DEFAULT '',
  `itemid`     INT(11)                UNSIGNED NOT NULL,
  `view`       VARCHAR(10)                     NOT NULL DEFAULT '',
  `sitemap_id` INT(11)                UNSIGNED NOT NULL,
  `properties` VARCHAR(250)                    NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`, `itemid`, `view`, `sitemap_id`),
  KEY `uid` (`uid`, `itemid`),
  KEY `view` (`view`)
);