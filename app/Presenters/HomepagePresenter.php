<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Utils\Json;
use Nette\Utils\ArrayHash;
use Nette\Database\Context;
use Tracy\Debugger;

use Nette\Security\Passwords;

final class HomepagePresenter extends SecuredPresenter
{
	/** @var Nette\Database\Context */
	protected $database;

	/** @var Passwords */
	private $passwords;

	public function __construct(Context $database, Passwords $passwords)
	{
		$this->database = $database;
		$this->passwords = $passwords;
	}

	//public function actionDefault($hash)
	public function renderDefault($hash)
	{
		//$this->redirect('Zakazky:prehled');
		//$this->template->debug = print_r($this->dotyApi->Testing(), true);
		//$this->template->debug2 = $this->passwords->hash('HeheSloslo.357');
	}

	public function actionLogout()
	{
		$this->user->logout();
		$this->flashMessage('Byli jste úspěšně odhlášeni');
		$this->redirect('Sign:in');
	}
}
