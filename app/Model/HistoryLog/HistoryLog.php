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

    public function writeLogIssueRAW(
        int $user = 0,
        string $level = 'n/a',
        string $action = 'unk',
        string $type = 'unk',
        ?string $subject = null,
        string $description = '',
        ?string $data = null): void
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

    /** @return array<string,string|int|null> */
    public function readLogIssueRAW(int $id): array
    {
        return $this->db->table(self::TABLE_NAME)->get($id)->toArray();
    }

    public function getLogList(int $page = 1, ?string $type = null, ?string $action = null, ?string $level = null): void
    {
        // TODO: Select from DB
        return;
    }

    // SYSTEM ---->>
    public function log_Info(string $description, string $data): void
    {
        $this->writeLogIssueRAW(0, 'info', 'unk', 'system', null, $description, $data);
    }
    public function log_Warning(string $description, string $data): void
    {
        $this->writeLogIssueRAW(0, 'warning', 'unk', 'system', null, $description, $data);
    }
    public function log_Error(string $description, string $data): void
    {
        $this->writeLogIssueRAW(0, 'error', 'unk', 'system', null, $description, $data);
    }
    public function log_Debug(string $description, string $data): void
    {
        $this->writeLogIssueRAW(0, 'debug', 'unk', 'system', null, $description, $data);
    }
    // <<---- SYSTEM

    // CRON ---->>
    public function log_UpdateCron(string $fceName, ?string $subject = null, ?string $data = null): void
    {
        $this->writeLogIssueRAW(0, 'info', 'update', 'cron', $subject, $fceName, $data);
    }
    // <<---- CRON
}
