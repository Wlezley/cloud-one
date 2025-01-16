<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\SignInFormFactory;
use Nette;

class SignPresenter extends UnsecuredPresenter
{
    /** @var SignInFormFactory @inject */
    public $signInForm;

    public function renderDefault(): void
    {
        $this->redrawControl();
    }

    protected function createComponentSignInForm(): Nette\Application\UI\Form
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
