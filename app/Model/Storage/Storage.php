<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\Random;

class Storage
{
    /** @var Nette\Database\Explorer @inject */
    public $db;

    /** @var int */
    protected $userID;

    public function __construct()
    {
    }

    public function getRandomCode(int $size, ?string $table = null, ?string $field = null): string
    {
        $charlist = '0-9a-z';

        if ($table == null || $field == null) {
            return Random::generate($size, $charlist);
        }

        $randomCode = null;
        $counter = 0;
        $limit = 100;

        for ($counter; $counter < $limit; $counter++) {
            $randomCode = Random::generate($size, $charlist);
            $result = $this->db->query('SELECT * FROM `'.$table.'` WHERE ? = ? LIMIT 1', $field, $randomCode);

            if (!isset($result) || $result->getRowCount() == 0) {
                break;
            }
        }

        return ($counter == $limit) ? str_repeat('f', $size) : $randomCode;
    }

    public function getOwnerList(): array
    {
        $result = $this->db->query('SELECT id,username,fullname,role FROM user_accounts ORDER BY id ASC');

        $ownerList = [];
        if ($result->getRowCount() >= 1) {
            foreach ($result->fetchAll() as $owner) {
                $ownerList[$owner->id] = [
                    'username' => $owner->username,
                    'fullname' => $owner->fullname,
                    'role'     => $owner->role,
                ];
            }
        }

        return $ownerList;
    }
}
