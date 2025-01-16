<?php

namespace App\Forms;

use Nette;
use Nette\Security\User;
use Nette\Application\UI\Form;

class SignInFormFactory
{
    public function __construct(protected User $user)
    {
    }

    public function create(): Form
    {
        $form = new Form();

        $form->addText('username', 'Přihlašovací jméno')
            ->setHtmlAttribute('placeholder', 'Přihlašovací jméno')
            ->setRequired();

        $form->addPassword('password', 'Heslo')
            ->setHtmlAttribute('placeholder', 'Heslo')
            ->setRequired();

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'process'];

        return $form;
    }

    public function process(Form $form, $values): void
    {
        try {
            $this->user->login($values->username, $values->password);
            // $this->user->setExpiration('+6 hours', true);
            // $this->user->setExpiration('+6 hours');
            $this->user->setExpiration(null);
        } catch(Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}

