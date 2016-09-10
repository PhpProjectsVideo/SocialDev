DROP TABLE IF EXISTS `session`;

CREATE TABLE `session` (
    `session_id` VARBINARY(128) NOT NULL PRIMARY KEY,
    `session_value` BLOB NOT NULL,
    `session_time` INTEGER UNSIGNED NOT NULL,
    `session_lifetime` MEDIUMINT NOT NULL
) COLLATE utf8_bin, ENGINE = InnoDB;

DROP TABLE IF EXISTS user;

CREATE TABLE user (
    user_id    INT UNSIGNED AUTO_INCREMENT NOT NULL,
    google_uid VARCHAR(64)                 NOT NULL,
    email      VARCHAR(255)                NOT NULL,
    username   VARCHAR(100)                DEFAULT NULL,
    UNIQUE INDEX UNIQ_8D93D6495E10E8A (google_uid),
    UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
    UNIQUE INDEX UNIQ_8D93D649F85E0677 (username),
    PRIMARY KEY (user_id)
)
    DEFAULT CHARACTER SET utf8
    COLLATE utf8_unicode_ci
    ENGINE = InnoDB;

DROP TABLE IF EXISTS url;

CREATE TABLE url (
    url_id      VARCHAR(64)   NOT NULL,
    url         VARCHAR(1024) NOT NULL,
    title       VARCHAR(255)  NOT NULL,
    description LONGTEXT      DEFAULT NULL,
    keywords    LONGTEXT      DEFAULT NULL,
    image_url   VARCHAR(1024) DEFAULT NULL,
    PRIMARY KEY (url_id)
)
    DEFAULT CHARACTER SET utf8
    COLLATE utf8_unicode_ci
    ENGINE = InnoDB;
