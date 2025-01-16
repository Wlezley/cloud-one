<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\HistoryLog;
use App\Model\StorageFiles;
use Nette\Utils\Finder;

class CronPresenter extends BasePresenter
{
    /** @var HistoryLog @inject */
    public $historyLog;

    public function __construct(private string $hash = '')
    {
        \Tracy\Debugger::$showBar = false;
    }

    public function actionDefault(string $hash): void
    {
        if ($this->hash != $hash) {
            echo 'ERR';
            $this->terminate();
        }

        echo 'OK';
        $this->terminate();
    }

    public function actionCleanup(string $hash): void
    {
        if ($this->hash != $hash) {
            echo 'ERR';
            $this->terminate();
        }

        $basePath = __DIR__ . '/../../data';
        $letters = array_merge(range('0', '9'), range('a', 'z'));

        foreach ($letters as $letter) {
            $path =  $basePath . DIRECTORY_SEPARATOR . $letter;

            foreach (Finder::findFiles('*')->in($path)->exclude('.*') as $fileInfo) {
                $fileName = $fileInfo->getFilename();
                $fileRow = $this->db->table(StorageFiles::TABLE_NAME)->select('*')->where(['storageID' => $fileName])->fetch();

                if ($fileRow === null) {
                    $filePath = realpath($fileInfo->getPathname());
                    unlink($filePath);

                    // TODO ...
                    // $this->historyLog->log_Info('CRON/Cleanup', $filePath);
                }
            }
        }

        echo 'OK';
        $this->terminate();
    }
}
