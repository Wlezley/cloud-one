<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserAccounts extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<SQL
            CREATE TABLE `user_accounts` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `username` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `fullname` VARCHAR(128) NOT NULL COLLATE 'utf8mb4_general_ci',
                `email` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_general_ci',
                `telefon` VARCHAR(16) NOT NULL COLLATE 'utf8mb4_general_ci',
                `password` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `role` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB
            AUTO_INCREMENT=1;
        SQL);

        $password = '$2y$12$ToJTXd.r6LgLeIqxfbIWhO7rRQ4mOk5MzuxY4gZZRei.ihFQOmcqW'; // 'admin'
        $this->execute(<<<SQL
            INSERT INTO `user_accounts` (`id`, `username`, `fullname`, `email`, `telefon`, `password`, `role`) VALUES (1, 'admin', 'admin', '', '', '$password', 'superadmin');
        SQL);
    }

    public function down(): void
    {
    }
}
