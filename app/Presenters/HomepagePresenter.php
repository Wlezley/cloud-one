<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Utils\Json;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

use Nette\Security\Passwords;

final class HomepagePresenter extends SecuredPresenter
{
	public function __construct()
	{
	}

	//public function actionDefault($hash)
	public function renderDefault($hash)
	{
		/*
		$passwords = new Passwords;
		$noveHeslo = $passwords->hash("TajneHeslo.123");
		*/
	}

	public function actionLogout()
	{
		$this->user->logout();
		$this->flashMessage('Byli jste úspěšně odhlášeni');
		$this->redirect('Sign:in');
	}
}
