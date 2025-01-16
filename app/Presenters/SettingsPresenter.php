<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\StorageTree;

final class SettingsPresenter extends SecuredPresenter
{
    /** @var StorageTree @inject */
    public $storageTree;

    public function startup(): void
    {
        parent::startup();
    }

    public function renderDefault(): void
    {
        $this->flashMessage('Nastavení cloudu je v rekonstrukci.', 'warning');

        // DEBUG ONLY ---->>
        // $this->storageTree->load(0);
        // $this->template->debug = $this->storageTree->getPath();

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

        bdump($this->storageTree->getOwnerList(), "GET OWNER LIST");
        // $this->template->ownerList = $this->storage->getOwnerList();
    }
}
