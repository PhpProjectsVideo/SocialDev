DROP TABLE IF EXISTS `session`;

CREATE TABLE `session` (
    `session_id` VARBINARY(128) NOT NULL PRIMARY KEY,
    `session_value` BLOB NOT NULL,
    `session_time` INTEGER UNSIGNED NOT NULL,
    `session_lifetime` MEDIUMINT NOT NULL
) COLLATE utf8_bin, ENGINE = InnoDB;