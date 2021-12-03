<?php

namespace App\Presenters;

use Nette;

class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var Nette\Database\Explorer @inject */
	public $db;

	public function __construct()
	{
	}
}
