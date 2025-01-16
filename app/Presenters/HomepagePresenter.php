<?php

declare(strict_types=1);

namespace App\Presenters;

final class HomepagePresenter extends SecuredPresenter
{
    public function renderDefault(): void
    {
    }

    public function actionLogout(): void
    {
        $this->user->logout();
        $this->flashMessage('Byli jste úspěšně odhlášeni');
        $this->redirect('Sign:in');
    }
}
