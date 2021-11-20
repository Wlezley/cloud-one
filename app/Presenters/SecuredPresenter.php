<?php

namespace App\Presenters;

use Nette;
use App\Forms;

class SecuredPresenter extends BasePresenter
{
	/* * @var Forms\ISearchFormFactory @inject * /
	public $searchForm;*/

	public function startup()
	{
		parent::startup();

		if (!$this->user->isLoggedIn() /*|| !isset($this->getContext()->parameters['prava'])*/ )
			$this->redirect('Sign:in');

		//$this->template->permissions = TRUE;
		/*isset($this->user->getRoles()[0])
		? $this->getContext()->parameters['prava'][$this->user->getRoles()[0]]
		: $this->getContext()->parameters['prava']['admin'];*/
	}

	/*protected function createComponentSearchForm()
	{
		$form = $this->searchForm->create();

		return $form;
	}*/
}