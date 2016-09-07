DROP TABLE IF EXISTS `session`;

CREATE TABLE `session` (
    `session_id` VARBINARY(128) NOT NULL PRIMARY KEY,
    `session_value` BLOB NOT NULL,
    `session_time` INTEGER UNSIGNED NOT NULL,
    `session_lifetime` MEDIUMINT NOT NULL
) COLLATE utf8_bin, ENGINE = InnoDB;

DROP TABLE IF EXISTS user_url;
DROP TABLE IF EXISTS url;
DROP TABLE IF EXISTS user;

CREATE TABLE user (
    user_id    INT UNSIGNED AUTO_INCREMENT NOT NULL,
    google_uid VARCHAR(64) DEFAULT NULL,
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

CREATE TABLE url (
    url_id      VARCHAR(64)   NOT NULL,
    user_id     INT UNSIGNED  DEFAULT NULL,
    url         VARCHAR(1024) NOT NULL,
    title       VARCHAR(255)  NOT NULL,
    description LONGTEXT      DEFAULT NULL,
    keywords    LONGTEXT      DEFAULT NULL,
    image_url   VARCHAR(1024) DEFAULT NULL,
    status      INT UNSIGNED  NOT NULL,
    timestamp   INT UNSIGNED  NOT NULL,
    INDEX IDX_F47645AEA76ED395 (user_id),
    INDEX timestamp_idx (timestamp),
    PRIMARY KEY (url_id)
)
    DEFAULT CHARACTER SET utf8
    COLLATE utf8_unicode_ci
    ENGINE = InnoDB;

ALTER TABLE url
    ADD CONSTRAINT FK_F47645AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id);

CREATE TABLE user_url (
    user_id   INT UNSIGNED NOT NULL,
    url_id    VARCHAR(64)  NOT NULL,
    timestamp INT UNSIGNED NOT NULL,
    INDEX IDX_1F602425A76ED395 (user_id),
    INDEX IDX_1F60242581CFDAE7 (url_id),
    UNIQUE INDEX user_url (url_id, user_id),
    PRIMARY KEY (user_id, url_id)
)
    DEFAULT CHARACTER SET utf8
    COLLATE utf8_unicode_ci
    ENGINE = InnoDB;

ALTER TABLE user_url
    ADD CONSTRAINT FK_1F602425A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id);

ALTER TABLE user_url
    ADD CONSTRAINT FK_1F60242581CFDAE7 FOREIGN KEY (url_id) REFERENCES url (url_id);
