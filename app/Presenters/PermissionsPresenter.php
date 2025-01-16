<?php

declare(strict_types=1);

namespace App\Presenters;

final class PermissionsPresenter extends SecuredPresenter
{
    public function startup(): void
    {
        parent::startup();
    }

    public function renderDefault(): void
    {
        $this->flashMessage('Uživatelská oprávnění jsou v rekonstrukci.', 'warning');

        $this->template->seznamUzivatelu = null;
        $this->template->pocetPolozek = 0;

        $result = $this->db->query('SELECT * FROM user_accounts');
        if($result->getRowCount() >= 1) {
            $this->template->seznamUzivatelu = $result->fetchAll();
        }

        if(!isset($this->template->seznamUzivatelu) || $this->template->seznamUzivatelu == null) {
            $this->flashMessage('Seznam uživatelů je prázdný.', 'info');
        } else {
            $this->template->pocetPolozek = count($this->template->seznamUzivatelu);
        }
    }
}
