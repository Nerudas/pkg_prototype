CREATE TABLE IF NOT EXISTS `#__prototype_items`
(
	`id`              INT(11)          NOT NULL AUTO_INCREMENT,
	`title`           VARCHAR(255)     NOT NULL DEFAULT '',
	`text`            MEDIUMTEXT       NOT NULL DEFAULT '',
	`location`        TEXT             NOT NULL DEFAULT '',
	`price`           BIGINT           NOT NULL DEFAULT '',
	`preset_price`    TEXT             NOT NULL DEFAULT '',
	`preset_delivery` TEXT             NOT NULL DEFAULT '',
	`preset_object`   TEXT             NOT NULL DEFAULT '',
	`external_link`   TEXT             NOT NULL DEFAULT '',
	`images`          MEDIUMTEXT       NOT NULL DEFAULT '',
	`state`           TINYINT(3)       NOT NULL DEFAULT '0',
	`active`          TINYINT(3)       NOT NULL DEFAULT '0',
	`catid`           INT(11)          NOT NULL DEFAULT '0',
	`date`            DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created`         DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by`      INT(11)          NOT NULL DEFAULT '0',
	`modified`        DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by`     INT(11)          NOT NULL DEFAULT '0',
	`publish_up`      DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
	`publish_down`    DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
	`payment`         TEXT             NOT NULL DEFAULT '',
	`payment_number`  TEXT             NOT NULL DEFAULT '',
	`payment_down`    DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
	`map`             TEXT             NOT NULL DEFAULT '',
	`latitude`        DOUBLE(20, 6),
	`longitude`       DOUBLE(20, 6),
	`attribs`         TEXT             NOT NULL DEFAULT '',
	`access`          INT(10)          NOT NULL DEFAULT '0',
	`hits`            INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`region`          CHAR(7)          NOT NULL DEFAULT '*',
	`tags_search`     MEDIUMTEXT       NOT NULL DEFAULT '',
	`tags_map`        MEDIUMTEXT       NOT NULL DEFAULT '',
	UNIQUE KEY `id` (`id`)
)
	ENGINE = MyISAM
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 0;

CREATE TABLE IF NOT EXISTS `#__prototype_categories`
(
	`id`            INT(11)      NOT NULL AUTO_INCREMENT,
	`title`         VARCHAR(255) NOT NULL DEFAULT '',
	`parent_id`     INT(11)      NOT NULL DEFAULT '0',
	`lft`           INT(11)      NOT NULL DEFAULT '0',
	`rgt`           INT(11)      NOT NULL DEFAULT '0',
	`level`         INT(10)      NOT NULL DEFAULT '0',
	`path`          VARCHAR(400) NOT NULL DEFAULT '',
	`alias`         VARCHAR(400) NOT NULL DEFAULT '',
	`attribs`       TEXT         NOT NULL DEFAULT '',
	`presets`       LONGTEXT     NOT NULL DEFAULT '',
	`state`         TINYINT(3)   NOT NULL DEFAULT '0',
	`front_created` TINYINT(3)   NOT NULL DEFAULT '0',
	`metakey`       MEDIUMTEXT   NOT NULL DEFAULT '',
	`metadesc`      MEDIUMTEXT   NOT NULL DEFAULT '',
	`access`        INT(10)      NOT NULL DEFAULT '0',
	`metadata`      MEDIUMTEXT   NOT NULL DEFAULT '',
	`tags_search`   MEDIUMTEXT   NOT NULL DEFAULT '',
	`tags_map`      MEDIUMTEXT   NOT NULL DEFAULT '',
	`items_tags`    MEDIUMTEXT   NOT NULL DEFAULT '',
	UNIQUE KEY `id` (`id`)
)
	ENGINE = MyISAM
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 0;