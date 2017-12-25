CREATE TABLE IF NOT EXISTS `#__jdidealgateway_emails` (
  `id`               INT(10)          NOT NULL AUTO_INCREMENT
  COMMENT 'Auto increment ID',
  `subject`          VARCHAR(150)     NOT NULL
  COMMENT 'The subject',
  `body`             TEXT             NOT NULL
  COMMENT 'The body text',
  `trigger`          VARCHAR(50)      NOT NULL
  COMMENT 'When the e-mail is send',
  `created`          DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `modified`         DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language`         CHAR(7)          NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)
  CHARSET = utf8
  COMMENT = 'E-mail templates';

CREATE TABLE IF NOT EXISTS `#__jdidealgateway_logs` (
  `id`               INT(10)        NOT NULL AUTO_INCREMENT
  COMMENT 'Auto increment ID',
  `profile_id`       INT(10)        NOT NULL
  COMMENT 'The payment provider',
  `trans`            VARCHAR(50)    NOT NULL
  COMMENT 'Transaction number',
  `order_id`         VARCHAR(10)    NOT NULL
  COMMENT 'Order ID',
  `order_number`     VARCHAR(50)    NOT NULL
  COMMENT 'Order Number',
  `quantity`         INT(4)         NOT NULL DEFAULT '0'
  COMMENT 'The quantity purchased',
  `amount`           DECIMAL(12, 5) NOT NULL
  COMMENT 'Amount to be paid',
  `origin`           VARCHAR(50)    NOT NULL
  COMMENT 'Origin of call',
  `card`             VARCHAR(50)    NOT NULL
  COMMENT 'The payment card used',
  `processed`        TINYINT(1)     NOT NULL DEFAULT '0'
  COMMENT 'Set a transaction if it has been checked',
  `history`          TEXT           NOT NULL
  COMMENT 'History of payment request',
  `date_added`       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP
  COMMENT 'Date and time of payment',
  `result`           VARCHAR(25)    NULL     DEFAULT NULL
  COMMENT 'Result of the payment request',
  `notify_url`       VARCHAR(255)   NOT NULL
  COMMENT 'Notification URL',
  `return_url`       VARCHAR(255)   NOT NULL
  COMMENT 'Return URL',
  `cancel_url`       VARCHAR(255)   NOT NULL
  COMMENT 'Cancellation URL',
  `paymentReference` VARCHAR(255)   NULL     DEFAULT NULL
  COMMENT 'Overboeking reference',
  `paymentId`        VARCHAR(255)   NULL     DEFAULT NULL
  COMMENT 'Payment ID for checking transaction',
  PRIMARY KEY (`id`)
)
  CHARSET = utf8
  COMMENT = 'Stores all iDEAL transactions';

CREATE TABLE IF NOT EXISTS `#__jdidealgateway_messages` (
  `id`               INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT
  COMMENT 'Auto increment ID',
  `subject`          VARCHAR(150)        NOT NULL
  COMMENT 'The subject',
  `orderstatus`      VARCHAR(25)         NOT NULL
  COMMENT 'The status to which the message applies',
  `profile_id`       INT(10)             NOT NULL
  COMMENT 'The ID of the profile',
  `message_type`     TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'Set which text to show',
  `message_text_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT 'The message ID',
  `message_text`     TEXT                NOT NULL
  COMMENT 'The body text',
  `language`         CHAR(7)             NOT NULL DEFAULT '',
  `created`          DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `modified`         DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `checked_out`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
)
  CHARSET = utf8
  COMMENT = 'Messages to show to customers';

CREATE TABLE IF NOT EXISTS `#__jdidealgateway_pays` (
  `id`         INT(10)        NOT NULL AUTO_INCREMENT,
  `user_email` VARCHAR(75)    NOT NULL
  COMMENT 'Email of the user sending money',
  `amount`     DECIMAL(12, 5) NOT NULL
  COMMENT 'The amount being send',
  `status`     CHAR(1)        NOT NULL DEFAULT 'P'
  COMMENT 'Status of the payment',
  `remark`     VARCHAR(255)   NULL     DEFAULT NULL
  COMMENT 'Remark',
  `cdate`      DATETIME       NOT NULL
  COMMENT 'Date the payment was added',
  PRIMARY KEY (`id`)
)
  CHARSET = utf8
  COMMENT = 'Payments received in JD iDEAL Gateway';

CREATE TABLE IF NOT EXISTS `#__jdidealgateway_profiles` (
  `id`               INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`             VARCHAR(100)        NOT NULL,
  `psp`              VARCHAR(25)         NOT NULL,
  `alias`            VARCHAR(100)        NOT NULL,
  `paymentInfo`      TEXT                NOT NULL,
  `ordering`         TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `created`          DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `modified`         DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)
  CHARSET = utf8
  COMMENT = 'Payments received in JD iDEAL Gateway';
