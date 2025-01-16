<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

class HistoryLog
{
    public const TABLE_NAME = 'log_history';

    /** @var Explorer @inject */
    public $db;

    /** @var int */
    protected $userID;

    public function __construct()
    {
    }

    public function writeLogIssueRAW($user = 0, $level = 'n/a', $action = 'unk', $type = 'unk', $subject = NULL, $description = '', $data = NULL): void
    {
        $this->db->table(self::TABLE_NAME)->insert([
            'user'          => $user,           // 0 = SYSTEM
            'level'         => $level,          // Options: info, warning, error, debug, n/a
            'action'        => $action,         // Options: new, add, edit, update, delete, remove, send, print, upload, unk
            'type'          => $type,           // Options: cron, system, contract, product_item, file, webcam, signature, warehouse, status, unk
            'subject'       => $subject,        // ID of the item to which the listing applies (For example - productid, contractid, etc...), default is NULL
            'description'   => $description,    // VARCHAR (255)
            'data'          => $data,           // TODO: Array to JSON
        ]);
    }

    public function readLogIssueRAW(int $id): array
    {
        return $this->db->table(self::TABLE_NAME)->get($id)->toArray();
    }

    public function getLogList($page = 1, $type = NULL, $action = NULL, $level = NULL): void
    {
        // TODO: Select from DB
        return;
    }

    // SYSTEM ---->>
    public function log_Info($description, $data): void
    {
        $this->writeLogIssueRAW(0, 'info', 'unk', 'system', NULL, $description, $data);
    }
    public function log_Warning($description, $data): void
    {
        $this->writeLogIssueRAW(0, 'warning', 'unk', 'system', NULL, $description, $data);
    }
    public function log_Error($description, $data): void
    {
        $this->writeLogIssueRAW(0, 'error', 'unk', 'system', NULL, $description, $data);
    }
    public function log_Debug($description, $data): void
    {
        $this->writeLogIssueRAW(0, 'debug', 'unk', 'system', NULL, $description, $data);
    }
    // <<---- SYSTEM

    // CRON ---->>
    public function log_UpdateCron($fceName, $subject = NULL, $data = NULL): void
    {
        $this->writeLogIssueRAW(0, 'info', 'update', 'cron', $subject, $fceName, $data);
    }
    // <<---- CRON
}
