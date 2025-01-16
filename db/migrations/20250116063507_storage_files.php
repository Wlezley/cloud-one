<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StorageFiles extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<SQL
            CREATE TABLE `storage_files` (
                `file_id` INT(11) NOT NULL AUTO_INCREMENT,
                `tree_id` INT(11) NOT NULL DEFAULT '0',
                `owner_id` INT(11) NULL DEFAULT NULL,
                `fileName` VARCHAR(512) NOT NULL COLLATE 'utf8mb4_general_ci',
                `fileMime` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_general_ci',
                `fileSize` BIGINT(16) NULL DEFAULT NULL,
                `date_upload` TIMESTAMP NULL DEFAULT NULL,
                `date_download` TIMESTAMP NULL DEFAULT NULL,
                `date_delete` TIMESTAMP NULL DEFAULT NULL,
                `date_modify` TIMESTAMP NULL DEFAULT NULL,
                `fileChecksum` CHAR(32) NOT NULL COLLATE 'utf8mb4_general_ci',
                `storageID` CHAR(16) NOT NULL COLLATE 'utf8mb4_general_ci',
                `downloadID` CHAR(16) NOT NULL COLLATE 'utf8mb4_general_ci',
                PRIMARY KEY (`file_id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB
            AUTO_INCREMENT=1;
        SQL);
    }

    public function down(): void
    {
    }
}
