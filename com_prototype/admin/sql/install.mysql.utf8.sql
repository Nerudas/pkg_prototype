CREATE TABLE IF NOT EXISTS `#__prototype_categories` (
	`id`             INT(11)      NOT NULL AUTO_INCREMENT,
	`title`          VARCHAR(255) NOT NULL DEFAULT '',
	`parent_id`      INT(11)      NOT NULL DEFAULT '0',
	`lft`            INT(11)      NOT NULL DEFAULT '0',
	`rgt`            INT(11)      NOT NULL DEFAULT '0',
	`level`          INT(10)      NOT NULL DEFAULT '0',
	`path`           VARCHAR(400) NOT NULL DEFAULT '',
	`alias`          VARCHAR(400) NOT NULL DEFAULT '',
	`attribs`        TEXT         NOT NULL DEFAULT '',
	`fields`         MEDIUMTEXT   NOT NULL DEFAULT '',
	`placemark_id`   INT(11)      NOT NULL DEFAULT '0',
	`balloon_layout` TEXT         NOT NULL DEFAULT '',
	`icon`           TEXT         NOT NULL DEFAULT '',
	`state`          TINYINT(3)   NOT NULL DEFAULT '0',
	`metakey`        MEDIUMTEXT   NOT NULL DEFAULT '',
	`metadesc`       MEDIUMTEXT   NOT NULL DEFAULT '',
	`access`         INT(10)      NOT NULL DEFAULT '0',
	`metadata`       MEDIUMTEXT   NOT NULL DEFAULT '',
	`tags_search`    MEDIUMTEXT   NOT NULL DEFAULT '',
	`tags_map`       MEDIUMTEXT   NOT NULL DEFAULT '',
	`items_tags`     MEDIUMTEXT   NOT NULL DEFAULT '',
	UNIQUE KEY `id` (`id`)
)
	ENGINE = MyISAM
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 0;

CREATE TABLE IF NOT EXISTS `#__prototype_placemarks` (
	`id`     INT(11)      NOT NULL AUTO_INCREMENT,
	`title`  VARCHAR(255) NOT NULL DEFAULT '',
	`images` LONGTEXT     NOT NULL DEFAULT '',
	`layout` TEXT         NOT NULL DEFAULT '',
	`state`  TINYINT(3)   NOT NULL DEFAULT '0',
	`access` INT(10)      NOT NULL DEFAULT '0',
	UNIQUE KEY `id` (`id`)
)
	ENGINE = MyISAM
	DEFAULT CHARSET = utf8
	AUTO_INCREMENT = 0;