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
	/** @var StorageTree @inject */
	public $storageTree;

	public function __construct()
	{
		// $this->storageTree = $storageTree;
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
		//$this->storageTree->load(0);
		//$this->template->debug = $this->storageTree->getPath();

		$this->storageTree->setOwnerID(1);
		// bdump($this->storageTree->getTreeList(), "GET TREE LIST");
		// bdump($this->storageTree->getFileList(), "GET FILE LIST");
		// <<---- DEBUG ONLY

		$testings = [0, 4, 6, 14, 15];

		foreach ($testings as $id) {
			$this->storageTree->load($id);
			$this->template->testing[$id]['treeList'] = $this->storageTree->getTreeList();
			$this->template->testing[$id]['fileList'] = $this->storageTree->getFileList();
		}



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
