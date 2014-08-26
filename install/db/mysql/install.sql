
CREATE TABLE IF NOT EXISTS `trinet_test_log` (
	`id`         INT(10)       UNSIGNED NOT NULL AUTO_INCREMENT,
	`value`      DECIMAL(18,2)          NOT NULL DEFAULT 0,
	`started_at` TIMESTAMP              NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`user_id`    INT(10)       UNSIGNED NOT NULL DEFAULT 0,

	PRIMARY KEY(`id`),
	KEY(`user_id`)
);

CREATE TABLE IF NOT EXISTS `trinet_test_log_section` (
	`log_id`            INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`iblock_section_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,

	PRIMARY KEY(`log_id`,`iblock_section_id`)
);

CREATE TABLE IF NOT EXISTS `trinet_test_log_element` (
	`log_id`     INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`product_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,

	PRIMARY KEY(`log_id`,`product_id`)
);
