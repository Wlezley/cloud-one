<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\StorageTree;
use Carbon\Carbon;
use Nette\Utils\Random;
use Oli\Form\DropzoneUploaderFactory;
use Oli\Form\DropzoneUploader;
use ZipArchive;

final class FilesPresenter extends SecuredPresenter
{
    /** @var DropzoneUploaderFactory */
    private $dropzone;

    /** @var StorageTree @inject */
    public $storageTree;

    public function __construct(DropzoneUploaderFactory $dropzone)
    {
        $this->dropzone = $dropzone;
    }

    public function startup(): void
    {
        parent::startup();
    }

    // TODO: Move to the model
    public function getRandomCode(int $size, ?string $table = null, ?string $field = null): string
    {
        $charlist = '0-9a-z';

        if ($table == null || $field == null) {
            return Random::generate($size, $charlist);
        }

        $randomCode = null;
        $counter = 0;
        $limit = 100;

        for ($counter; $counter < $limit; $counter++) {
            $randomCode = Random::generate($size, $charlist);
            $result = $this->db->query('SELECT * FROM `'.$table.'` WHERE ? = ? LIMIT 1', $field, $randomCode);

            if($result->getRowCount() == 0) {
                break;
            }
        }

        return ($counter == $limit) ? str_repeat('f', $size) : $randomCode;
    }

    // Uploader control
    public function createComponentUploader(): DropzoneUploader
    {
        // CREATE A DROPZONE FACTORY OBJECT
        $dropzone = $this->dropzone->create();

        $dropzone->onSuccess[] = function (DropzoneUploader $dropzoneUploader, $storageID, $fileName, $suffix, $size, $hash)
        {
            $owner_id = (isset($this->getUser()->id) ? $this->getUser()->id : 0); // CURRENTLY LOGGED USER ID

            $base = $this->getHttpRequest()->getUrl()->basePath . 'files';
            $path = $this->getHttpRequest()->getUrl()->path;
            if (substr($path, 0, strlen($base)) == $base) {
                $path = substr($path, strlen($base));
            } else { /* ERROR 404 ?? */ }
            $tree_id = $this->storageTree->getTreeIdByPath($path, $owner_id);

            $timestampNow = Carbon::now()->format('Y-m-d H:i:s'); // CURRENT TIMESTAMP
            $downloadID = $this->getRandomCode(16, 'storage_files', 'downloadID'); // DOWNLOAD ID (KEY)

            if ($suffix === 'jpeg') {
                $suffix = 'jpg';
            }

            $this->db->table('storage_files')->insert([
                'tree_id' => $tree_id,
                'owner_id' => $owner_id,
                'fileName' => $fileName,
                'fileMime' => $suffix,
                'fileSize' => $size,
                'date_upload' => $timestampNow,
                'date_download' => null,
                'date_delete' => null,
                'date_modify' => null,
                'fileChecksum' => $hash,
                'storageID' => $storageID,
                'downloadID' => $downloadID,
                // 'attributes' => '',
                // 'status' => '',
            ]);
        };

        $this->getFlashSession()->remove();

        return $dropzone;
    }

    // Render default file list
    public function renderDefault(): void
    {
        $this->template->treeList = null;
        $this->template->fileList = null;

        $this->template->countFolders = 0;
        $this->template->countFiles = 0;

        $this->template->path = '/';

        $resultFiles = $this->db->query('SELECT * FROM storage_files WHERE tree_id = 0 ORDER BY fileName ASC');
        if (($this->template->countFiles = $resultFiles->getRowCount()) >= 1) {
            $this->template->fileList = $resultFiles->fetchAll();
        }

        if (!isset($this->template->fileList) || $this->template->fileList == null) {
            $this->flashMessage('Složka je prázdná.', 'info');
        } else {
            $this->template->ownerList = $this->storageTree->getOwnerList();
        }
    }

    // Render file list from directory (with sub-directories)
    public function renderDirectory(string $path = ''): void
    {
        $this->template->treeList = null;
        $this->template->fileList = null;

        $this->template->countFolders = 0;
        $this->template->countFiles = 0;
        $this->template->path = $path;

        $pathArray = explode('/', trim($path, '/'));

        // bdump($pathArray, "URL / PATH ARRAY"); // DEBUG

        $owner_id = 1;
        $parent_id = 0;
        $tree_id = 0;

        $treeMap = [];
        $upDir = '';
        $lastPath = '';

        foreach ($pathArray as $key => $name_url) {
            $folder = $this->db->query('SELECT * FROM storage_tree WHERE owner_id = ? AND parent_id = ? AND name_url = ? LIMIT 1', $owner_id, $parent_id, $name_url);
            $folderInfo = $folder->fetch();
            $upDir = rtrim($lastPath, '/');

            if (!$folderInfo) {
                if (!empty($lastPath)) {
                    $this->redirect('this', ['path' => rtrim($lastPath, '/')]);
                }
                break;
            }

            $treeMap[$key] = [
                'parent_id' => $parent_id,
                'tree_id' => $folderInfo['tree_id'],
            ];

            $lastPath .= $folderInfo['name_url'] . '/';
            $parent_id = $folderInfo['tree_id'];

            if ($parent_id != 0) {
                $tree_id = $parent_id;
            } else {
                break;
            }
        }

        // bdump($treeMap, "URL / TREE MAP"); // DEBUG

        // Folders
        $this->template->treeList = [];
        $resultFolders = $this->db->query('SELECT * FROM storage_tree WHERE owner_id = ? AND parent_id = ? ORDER BY name_url ASC', $owner_id, $parent_id);
        if (($this->template->countFolders = $resultFolders->getRowCount()) >= 1) {
            $this->template->treeList = $resultFolders->fetchAll();
            foreach ($this->template->treeList as $key => $item) {
                $this->template->treeList[$key]['name_url'] = $lastPath . $this->template->treeList[$key]['name_url'];
            }
        }

        // Files
        // $resultFiles = $this->db->query('SELECT * FROM storage_files ORDER BY fileName ASC');
        // if (($this->template->countFiles = $resultFiles->getRowCount()) >= 1) {
        //     $this->template->fileList = $resultFiles->fetchAll();
        // }

        $this->storageTree->setOwnerID($owner_id);
        $this->storageTree->setTreeID($parent_id);
        $this->template->fileList = $this->storageTree->getFileList();

        // Empty folder
        if (empty($this->template->treeList) && empty($this->template->fileList)) {
            $this->flashMessage('Složka je prázdná.', 'info');
            // return;
        }

        if (!$this->template->flashes) {
            $this->flashMessage('&nbsp;', 'none');
        }

        $this->template->ownerList = $this->storageTree->getOwnerList();

        // $this->template->upDir = rtrim($lastPath, '/');
        $this->template->upDir = $upDir;

        // ID and path of actual folder
        $this->template->tree_id = $tree_id;
        $this->template->tree_path = '/' . $lastPath;
    }

    // Download file by storageID and downloadID (hash)
    public function actionDownload(string $storageID, string $downloadID): void
    {
        // OWNER ID (GET CURRENTLY LOGGED USER ID)
        $owner_id = (isset($this->getUser()->id) ? $this->getUser()->id : 0);

        // $resultSel = $this->db->query('SELECT * FROM storage_files WHERE storageID = ? AND downloadID = ? AND owner_id = ? LIMIT 1', $storageID, $downloadID, $owner_id);
        $resultSel = $this->db->query('SELECT * FROM storage_files WHERE storageID = ? AND downloadID = ? LIMIT 1', $storageID, $downloadID);
        if ($resultSel->getRowCount() != 1) {
            $this->flashMessage('CHYBA: Soubor nebyl nalezen, nebo pro jeho stažení nemáte dostatečná oprávnění.', 'danger');
            $this->redirect('Files:default');
        }

        $data = $resultSel->fetch();
        $basePath = '..' . DIRECTORY_SEPARATOR . 'data';
        $dirLetter = str_split($data->storageID, 1)[0];
        $baseName = $data->fileName;
        $hashName = $data->storageID;
        $storFile = $basePath . DIRECTORY_SEPARATOR . $dirLetter . DIRECTORY_SEPARATOR . $hashName;

        if (!file_exists($storFile)) {
            $this->flashMessage("CHYBA: Soubor '$baseName' nebyl nalezen.", 'danger');
            $this->redirect('Files:default');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($baseName));
        // header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        // header('Cache-Control: public');
        header('Pragma: public');
        header('Content-Length: ' . filesize($storFile));
        ob_clean();
        flush();
        readfile($storFile);

        $this->redirect('Files:default');
        // $this->terminate(); // Maybe better?
    }

    // Download multiple files as ZIP by storageID list (JSON string array contains storage IDs)
    public function actionDownloadBulk(string $storageID_List): void
    {
        // OWNER ID (GET CURRENTLY LOGGED USER ID)
        $owner_id = (isset($this->getUser()->id) ? $this->getUser()->id : 0);

        $jsonData = [];

        if (empty($storageID_List) || empty($jsonData = json_decode($storageID_List, true))) {
            $this->flashMessage('CHYBA: Soubor nebyl nalezen, nebo nemáte dostatečná oprávnění.', 'danger');
            $this->redirect('Files:default');
        }

        $basePath = '..' . DIRECTORY_SEPARATOR . 'data';
        $zipName = 'cloud_one_bulk_' . Carbon::now()->format('Y-m-d_H-i-s') . '.zip';
        $zipFile = $basePath . DIRECTORY_SEPARATOR . 'zip' . DIRECTORY_SEPARATOR . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            foreach ($jsonData as $storageID) {
                // $resultSel = $this->db->query('SELECT * FROM storage_files WHERE storageID = ? AND downloadID = ? AND owner_id = ? LIMIT 1', $storageID, $downloadID, $owner_id);
                $resultSel = $this->db->query('SELECT * FROM storage_files WHERE storageID = ? LIMIT 1', $storageID);

                if ($resultSel->getRowCount() != 1) {
                    $this->flashMessage("CHYBA: Soubor (SID: $storageID) nebyl nalezen, nebo pro jeho stažení nemáte dostatečná oprávnění.", 'danger');
                    $this->redirect('Files:default');
                }

                $data = $resultSel->fetch();
                $dirLetter = substr($data->storageID, 0, 1);
                $baseName = $data->fileName;
                $hashName = $data->storageID;
                $storFile = $basePath . DIRECTORY_SEPARATOR . $dirLetter . DIRECTORY_SEPARATOR . $hashName;

                $zip->addFile($storFile, $baseName);
            }
            $zip->close();

            // TODO: Opravit stahovani velkych souboru (500 MB +)
            header('Content-Description: File Transfer');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename=' . $zipName);
            // header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            // header('Cache-Control: public');
            header('Pragma: public');
            header('Content-Length: ' . filesize($zipFile));
            ob_clean();
            flush();
            readfile($zipFile);

            unlink($zipFile);
        }

        $this->redirect('Files:default');
        // $this->terminate(); // Maybe better?
    }

    // Delete file by storageID
    public function actionDelete(string $storageID): void
    {
        // OWNER ID (GET CURRENTLY LOGGED USER ID)
        $owner_id = (isset($this->getUser()->id) ? $this->getUser()->id : 0);

        // $resultSel = $this->db->query('SELECT * FROM storage_files WHERE owner_id = ? AND storageID = ? LIMIT 1', $owner_id, $storageID);
        $resultSel = $this->db->query('SELECT * FROM storage_files WHERE storageID = ? LIMIT 1', $storageID);
        if ($resultSel->getRowCount() != 1) {
            $this->flashMessage('CHYBA: Soubor nebyl nalezen, nebo pro jeho odstranění nemáte dostatečná oprávnění.', 'danger');
            $this->redirect('Files:default');
        }

        $data = $resultSel->fetch();
        // $basePath = 'data';
        $basePath = '..' . DIRECTORY_SEPARATOR . 'data';
        $dirLetter = str_split($data->storageID, 1)[0];
        $baseName = $data->fileName;
        $hashName = $data->storageID;
        $storFile = $basePath . DIRECTORY_SEPARATOR . $dirLetter . DIRECTORY_SEPARATOR . $hashName;

        // if (!file_exists($storFile)) {
        //     $this->flashMessage("CHYBA: Soubor '$baseName' nebyl nalezen.", 'danger');
        //     $this->redirect('Files:default');
        // }

        if (file_exists($storFile) && !unlink($storFile)) {
            $this->flashMessage("CHYBA: Soubor '$baseName' nelze smazat.", 'danger');
            $this->redirect('Files:default');
        }

        $resultDel = $this->db->query('DELETE FROM storage_files WHERE file_id = ? LIMIT 1', $data->file_id);
        if ($resultDel->getRowCount() != 1) {
            $this->flashMessage("CHYBA: Soubor '$baseName' se nepodařilo odstranit z databáze.", 'danger');
            $this->redirect('Files:default');
        }

        $this->flashMessage("Soubor '$baseName' byl odstraněn.", 'success'); // TODO: Move files to the recycle bin
        $this->redirect('Files:default');
    }

    // Delete multiple files by storageID list (JSON string array contains storage IDs)
    public function actionDeleteBulk(string $storageID_List): void
    {
        $jsonData = [];

        if (!empty($storageID_List)) {
            $jsonData = json_decode($storageID_List, true);
        }

        if (empty($jsonData)) {
            $this->flashMessage('CHYBA: Prázdná JSON data.', 'danger');
            $this->redirect('Files:default');
        }

        $fileCounter = 0;
        foreach ($jsonData as $storageID) {
            // OWNER ID (GET CURRENTLY LOGGED USER ID)
            $owner_id = (isset($this->getUser()->id) ? $this->getUser()->id : 0);

            // $resultSel = $this->db->query('SELECT * FROM storage_files WHERE owner_id = ? AND storageID = ? LIMIT 1', $owner_id, $storageID);
            $resultSel = $this->db->query('SELECT * FROM storage_files WHERE storageID = ? LIMIT 1', $storageID);
            if ($resultSel->getRowCount() != 1) {
                $this->flashMessage('CHYBA: Soubor nebyl nalezen, nebo pro jeho odstranění nemáte dostatečná oprávnění.', 'danger');
                // $this->redirect('Files:default');
                break;
            }

            $data = $resultSel->fetch();
            // $basePath = 'data';
            $basePath = '..' . DIRECTORY_SEPARATOR . 'data';
            $dirLetter = str_split($data->storageID, 1)[0];
            $baseName = $data->fileName;
            $hashName = $data->storageID;
            $storFile = $basePath . DIRECTORY_SEPARATOR . $dirLetter . DIRECTORY_SEPARATOR . $hashName;

            // if (!file_exists($storFile)) {
            //     $this->flashMessage("CHYBA: Soubor '$baseName' nebyl nalezen.", 'danger');
            //     // $this->redirect('Files:default');
            //     break;
            // }

            if (file_exists($storFile) && !unlink($storFile)) {
                $this->flashMessage("CHYBA: Soubor '$baseName' nelze smazat.", 'danger');
                // $this->redirect('Files:default');
                break;
            }

            $resultDel = $this->db->query('DELETE FROM storage_files WHERE file_id = ? LIMIT 1', $data->file_id);
            if ($resultDel->getRowCount() != 1) {
                $this->flashMessage("CHYBA: Soubor '$baseName' se nepodařilo odstranit z databáze.", 'danger');
                // $this->redirect('Files:default');
                break;
            }

            // $this->flashMessage("Soubor '$baseName' byl odstraněn.", 'info'); // TODO: Move files to the recycle bin

            $fileCounter++;
        }

        switch ($fileCounter) {
            case 0:
                $outputMessage = ['Nebyl odstraněn žádný soubor', 'warning'];
                break;
            case 1:
                $outputMessage = ['Byl odstraněn 1 soubor.', 'success'];
                break;
            case 2:
            case 3:
            case 4:
                $outputMessage = ["Byly odstraněny $fileCounter soubory.", 'success'];
                break;
            default:
                $outputMessage = ["Bylo odstraněno $fileCounter souborů.", 'success'];
                break;
        }

        $this->flashMessage($outputMessage[0], $outputMessage[1]);
        $this->redirect('Files:default');
    }

    /* ######################################## TREE ACTIONS ######################################## */

    // Create new folder
    public function actionAddFolder(int $tree_id, string $name): void
    {
        $tree_path = '';
        $this->storageTree->load($tree_id);
        // $name = base64_decode($name);

        if ($this->storageTree->isLoaded()) {
            $tree_path = trim($this->storageTree->getPathByTreeId($tree_id), '/');
            $this->storageTree->create($name, $tree_id, $this->storageTree->getOwnerID());
            $this->flashMessage("Složka '$name' byla vytvořena.", 'success');
        } else if ($tree_id == 0) {
            $owner_id = (isset($this->getUser()->id) ? $this->getUser()->id : 0);
            $this->storageTree->create($name, $tree_id, $owner_id);
            $this->flashMessage("Složka '$name' byla vytvořena (PARENT ID == 0).", 'success');
        } else {
            $this->flashMessage('Při vytváření složky došlo k chybě!', 'danger');
        }

        $this->redirect('Files:directory', $tree_path);
    }

    // Delete folder
    public function actionDeleteFolder(int $tree_id): void
    {
        $parent_path = '';
        $this->storageTree->load($tree_id);

        if ($this->storageTree->isLoaded()) {
            $parent_id = $this->storageTree->getParentID();

            if ($parent_id != 0) {
                $parent_path = trim($this->storageTree->getPathByTreeId($parent_id), '/');
            }

            $name = $this->storageTree->getName();
            $this->storageTree->delete();
            $this->flashMessage("Složka '$name' byla odstraněna.", 'success');
        } else {
            $this->flashMessage('Složka nebyla nalezena!', 'danger');
        }

        $this->redirect('Files:directory', $parent_path);
    }
}
