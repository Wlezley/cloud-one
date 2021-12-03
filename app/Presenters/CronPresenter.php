<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model;
use Tracy\Debugger;


class CronPresenter extends BasePresenter
{
	/** @var Model\HistoryLog\HistoryLog */
	protected $historyLog;

	/** @var string */
	private string $hash;

	public function __construct(string $hash = "")
	{
		// Disable Tracy Debug Bar
		Debugger::$showBar = false;

		//$this->hash = $hash;
		$this->historyLog = new Model\HistoryLog($this->db);
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
