<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\HistoryLog;

class CronPresenter extends BasePresenter
{
    /** @var HistoryLog @inject */
    public $historyLog;

    public function __construct(private string $hash = '')
    {
        \Tracy\Debugger::$showBar = false;
    }

    public function actionDefault($hash): void
    {
        if ($this->hash == $hash) {
            echo 'OK!';
        } else {
            echo 'ERROR: Wrong hash.';
        }

        $this->terminate();
    }
}
