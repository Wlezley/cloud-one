<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

class StorageFiles extends Storage
{
    public const TABLE_NAME = 'storage_files';

    /** @var Nette\Database\Explorer @inject */
    public $db;

    public int $tree_id;

    /** @var list<array> */
    public array $file_list;

    public bool $is_loaded;

    public function __construct(Nette\Database\Explorer $db)
    {
        $this->db = $db;
        $this->is_loaded = false;
    }

    // TODO...
    public function load(int $id = 0): void
    {
        $this->is_loaded = true;
    }
}

/*
CREATE TABLE `storage_files` (
    `fileID` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID souboru',
    `ownerID` INT(11) NULL DEFAULT NULL COMMENT 'ID majitele souboru',
    `fileName` VARCHAR(512) NOT NULL COMMENT 'Jmeno souboru' COLLATE 'utf8mb4_general_ci',
    `fileMime` VARCHAR(64) NOT NULL COMMENT 'Pripona souboru' COLLATE 'utf8mb4_general_ci',
    `fileSize` BIGINT(16) NULL DEFAULT NULL COMMENT 'Velikost souboru',
    `date_upload` TIMESTAMP NULL DEFAULT NULL COMMENT 'Datum nahrani',
    `date_download` TIMESTAMP NULL DEFAULT NULL COMMENT 'Datum stazeni',
    `date_delete` TIMESTAMP NULL DEFAULT NULL COMMENT 'Datum smazani',
    `date_modify` TIMESTAMP NULL DEFAULT NULL COMMENT 'Datum zmeny',
    `fileChecksum` CHAR(32) NOT NULL COMMENT 'Kontrolni soucet MD5' COLLATE 'utf8mb4_general_ci',
    `storageID` CHAR(16) NOT NULL COMMENT 'ID souboru na disku' COLLATE 'utf8mb4_general_ci',
    `downloadID` CHAR(16) NOT NULL COMMENT 'ID pro download' COLLATE 'utf8mb4_general_ci',
    PRIMARY KEY (`fileID`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
*/
