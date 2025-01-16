<?php

declare(strict_types=1);

namespace App\Presenters;

class SecuredPresenter extends BasePresenter
{
	public function startup(): void
	{
		parent::startup();

		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}
}