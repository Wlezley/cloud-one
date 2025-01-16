<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserRoles extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<SQL
            CREATE TABLE `user_roles` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `roles` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `allowLogin` TINYINT(1) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB
            AUTO_INCREMENT=1;
        SQL);

        $this->execute(<<<SQL
            INSERT INTO `user_roles` (`id`, `name`, `roles`, `allowLogin`) VALUES (1, 'superadmin', '', 1);
            INSERT INTO `user_roles` (`id`, `name`, `roles`, `allowLogin`) VALUES (2, 'admin', '', 1);
            INSERT INTO `user_roles` (`id`, `name`, `roles`, `allowLogin`) VALUES (3, 'user', '', 1);
            INSERT INTO `user_roles` (`id`, `name`, `roles`, `allowLogin`) VALUES (4, 'guest', '', 0);
        SQL);
    }

    public function down(): void
    {
    }
}
