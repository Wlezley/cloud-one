<?php

declare(strict_types=1);

namespace App\Presenters;

class UnsecuredPresenter extends BasePresenter
{
	public function startup(): void
	{
		parent::startup();

		if ($this->user->isLoggedIn()) {
			$this->redirect('Homepage:');
		}
	}
}