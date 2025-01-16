<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StorageTree extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<SQL
            CREATE TABLE `storage_tree` (
                `tree_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `parent_id` INT(11) NOT NULL DEFAULT '0',
                `owner_id` INT(11) NOT NULL DEFAULT '0',
                `name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `name_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `date_create` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
                `date_download` TIMESTAMP NULL DEFAULT NULL,
                `date_delete` TIMESTAMP NULL DEFAULT NULL,
                `date_modify` TIMESTAMP NULL DEFAULT NULL ON UPDATE current_timestamp(),
                PRIMARY KEY (`tree_id`) USING BTREE,
                UNIQUE INDEX `parentID_ownerID_nameUrl` (`parent_id`, `owner_id`, `name_url`) USING BTREE
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
