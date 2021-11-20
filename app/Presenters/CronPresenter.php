<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Database\Context;
use Tracy\Debugger;


class CronPresenter extends BasePresenter
{
	/** @var Nette\Database\Context */
	protected $database;

	/** @var Model\SmsBrana\SmsBrana */
	protected $smsBrana;

	/** @var Model\HistoryLog\HistoryLog */
	protected $historyLog;

	public function __construct(Context $database)
	{
		// Disable Tracy Debug Bar
		Debugger::$showBar = false;

		$this->database = $database;
		$this->smsBrana = new Model\SmsBrana\SmsBrana($this->database);
		$this->historyLog = new Model\HistoryLog\HistoryLog($this->database);
	}

	public function actionDefault($hash)
	{
		if ($hash == '1aerg6384areg651dfb8atr468hzz4ar6t84t541')
		{
			$this->dotyApi->DB_UpdateCategories();
			$this->historyLog->log_UpdateCron("CRON/DB_UpdateCategories");
			//sleep(5);
			$this->dotyApi->DB_UpdateServices();
			$this->historyLog->log_UpdateCron("CRON/DB_UpdateServices");
			//sleep(5);

			// TEST SMS
			//$this->smsBrana->sendSMS('608284446', 'TEST 123.', 0);
		}
	}
}
