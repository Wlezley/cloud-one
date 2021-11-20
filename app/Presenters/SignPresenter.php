<?php

namespace App\Presenters;

use Nette;
use App\Forms;

class SignPresenter extends UnsecuredPresenter
{
	/** @var Forms\SignInFormFactory @inject */
	public $signInForm;

	public function renderDefault()
	{
		$this->redrawControl();
	}

	protected function createComponentSignInForm()
	{
		$form = $this->signInForm->create();

		$form->onSuccess[] = function () {
			$this->redirect('Homepage:');
		};

		$form->onError[] = function () {
			$this->flashMessage('Nesprávné přihlašovací údaje', 'danger');
		};

		return $form;
	}
}
