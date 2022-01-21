<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model;
use App\Model\Storage;
use App\Model\StorageTree;
use Nette\Utils\Json;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;


final class SettingsPresenter extends SecuredPresenter
{
	/** @var StorageTree */
	private $storageTree;

	public function __construct(StorageTree $storageTree)
	{
		$this->storageTree = $storageTree;
	}

	public function startup()
	{
		parent::startup();
	}

	public function renderDefault()
	{
		// DEBUG ?
		$this->flashMessage('Nastavení cloudu je v rekonstrukci.', 'warning');

		// DEBUG ONLY ---->>
		$this->storageTree->load(4);
		$this->template->debug = $this->storageTree->getPath();
		// <<---- DEBUG ONLY



		$this->template->seznamUzivatelu = NULL;
		$this->template->pocetPolozek = 0;

		$result = $this->db->query('SELECT * FROM user_accounts');
		if($result->getRowCount() >= 1)
		{
			$this->template->seznamUzivatelu = $result->fetchAll();
		}

		if(!isset($this->template->seznamUzivatelu) || $this->template->seznamUzivatelu == NULL)
		{
			$this->flashMessage('Seznam uživatelů je prázdný.', 'info');
			return;
		}
		$this->template->pocetPolozek = count($this->template->seznamUzivatelu);
	}
}
