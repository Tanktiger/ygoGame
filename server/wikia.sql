CREATE TABLE `cards_wikia` (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `name_de` VARCHAR(255) NULL DEFAULT NULL,
    `name_en` VARCHAR(255) NULL DEFAULT NULL,
    `name_en_alternate` VARCHAR(255) NULL DEFAULT NULL,
    `url` TEXT NULL,
    `pic_url` TEXT NULL,
    `type` VARCHAR(255) NULL DEFAULT NULL,
    `propertys` VARCHAR(255) NULL DEFAULT NULL,
    `attribute` VARCHAR(255) NULL DEFAULT NULL,
    `atk` SMALLINT(6) NULL DEFAULT NULL,
    `def` SMALLINT(6) NULL DEFAULT NULL,
    `level` TINYINT(4) NULL DEFAULT NULL,
    `effect` TEXT NULL,
    `code` INT(11) NULL DEFAULT NULL,
    `fusion_material` TEXT NULL,
    `material` TEXT NULL,
    PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
