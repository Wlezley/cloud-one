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

	/** @var Model\HistoryLog\HistoryLog */
	protected $historyLog;

	/** @var string */
	private string $hash;

	public function __construct(/*string $hash,*/ Context $database)
	{
		// Disable Tracy Debug Bar
		Debugger::$showBar = false;

		$this->database = $database;
		//$this->hash = $hash;
		$this->historyLog = new Model\HistoryLog\HistoryLog($this->database);
		$this->terminate();
	}

	public function actionDefault($hash)
	{
		if ($this->hash == $hash)
		{
			echo "OK!";
		}
		$this->terminate();
	}
}
