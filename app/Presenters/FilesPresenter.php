<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Utils\Json;
use Nette\Utils\Random;
use Nette\Utils\ArrayHash;
use Nette\Database\Context;
use Tracy\Debugger;

use Oli\Form\DropzoneUploaderExtension;
use Oli\Form\DropzoneUploaderFactory;
use Oli\Form\DropzoneUploader;
use Carbon\Carbon;


final class FilesPresenter extends SecuredPresenter
{
	/** @var Nette\Database\Context */
	protected $database;

	/** @var Oli\Form\DropzoneUploaderFactory */
	private $dropzone;

	public function __construct(Context $database, DropzoneUploaderFactory $dropzone)
	{
		$this->database = $database;
		$this->dropzone = $dropzone;
	}

	public function startup()
	{
		parent::startup();
	}

	/** Vygeneruje nahodny alfa-numericky kod
	 * @todo Presunout do MODELU!
	 * @param	integer			$size
	 * @param	string|NULL		$table
	 * @param	string|NULL		$field
	 *
	 * @return	int|NULL
	 */
	public function getRandomCode($size, $table = NULL, $field = NULL)
	{
		$charlist = '0-9a-z';

		if ($table == NULL || $field == NULL) {
			return Random::generate($size, $charlist);
		}

		$randomCode = NULL;
		$counter = 0;
		$limit = 100;

		for ($counter; $counter < $limit; $counter++) {
			$randomCode = Random::generate($size, $charlist);
			$result = $this->database->query('SELECT * FROM `'.$table.'` WHERE ? = ? LIMIT 1', $field, $randomCode);
			if(!isset($result) || $result->getRowCount() == 0) break;
		}

		return ($counter == $limit) ? str_repeat('f', $size) : $randomCode;
	}

	public function createComponentUploader()
	{
		// CREATE A DROPZONE FACTORY OBJECT
		$dropzone = $this->dropzone->create();

		$dropzone->onSuccess[] = function (DropzoneUploader $dropzoneUploader, $storageID, $fileName, $suffix, $size, $hash)
		{
			$ownerID = (isset($this->getUser()->id) ? $this->getUser()->id : 0);	// CURRENTLY LOGGED USER ID
			$timestampNow = Carbon::now()->format('Y-m-d H:i:s');					// CURRENT TIMESTAMP
			$downloadID = $this->getRandomCode(16, 'storage_files', 'downloadID');	// DOWNLOAD ID (KEY)

			if ($suffix === "jpeg") {
				$suffix = "jpg";
			}

			$this->database->table('storage_files')->insert([
				//'fileID'		=> 0,				// (int 11)		AUTO_INCREMENT
				'ownerID'		=> $ownerID,		// (int 11)		ID majitele souboru - podle tabulky user_accounts (0 = SYSTEM)
				'fileName'		=> $fileName,		// (varchar512)	Jmeno souboru (orig)
				'fileMime'		=> $suffix,			// (varchar 64)	Pripona souboru (orig)
				'fileSize'		=> $size,			// (bigint 16)	Velikost souboru v bytech (max. 9.99 PetaBytes)
				'date_upload'	=> $timestampNow,	// (timestamp)	Datum nahrani
				'date_download'	=> NULL,			// (timestamp)	Datum stazeni
				'date_delete'	=> NULL,			// (timestamp)	Datum smazani
				'date_modify'	=> NULL,			// (timestamp)	Datum zmeny
				'fileChecksum'	=> $hash,			// (char 32)	Kontrolni soucet MD5 (MD5 = 32 znaku, SHA256 = 64 znaku)
				'storageID'		=> $storageID,		// (char 16)	ID souboru na disku
				'downloadID'	=> $downloadID,		// (char 16)	ID pro download
				//'attributes'	=> "",				// TODO: Json ARRAY or HEX-FLAG?
				//'status'		=> "",				// TODO: Json ARRAY or HEX-FLAG?
			]);
		};

		return $dropzone;
	}

	public function renderDefault()
	{
		$this->template->fileList = NULL;
		$this->template->filesCount = 0;

		$resultFiles = $this->database->query('SELECT * FROM storage_files ORDER BY fileName ASC' );
		if (($this->template->filesCount = $resultFiles->getRowCount()) >= 1) {
			$this->template->fileList = $resultFiles->fetchAll();
		}

		if (!isset($this->template->fileList) || $this->template->fileList == NULL) {
			$this->flashMessage('Složka je prázdná.', 'info');
			return;
		}

		$this->template->ownerList = array();

		$resultOwners = $this->database->query('SELECT id,username,fullname,role FROM user_accounts ORDER BY id ASC' );
		if ($resultOwners->getRowCount() >= 1) {
			foreach ($resultOwners->fetchAll() as $owner) {
				$this->template->ownerList[$owner->id] = [
					'username' => $owner->username,
					'fullname' => $owner->fullname,
					'role'     => $owner->role,
				];
			}
		}
	}

	public function actionDownload($storageID, $downloadID)
	{
		// OWNER ID (GET CURRENTLY LOGGED USER ID)
		$ownerID = (isset($this->getUser()->id) ? $this->getUser()->id : 0);

	//	$resultSel = $this->database->query('SELECT * FROM storage_files WHERE storageID = ? AND downloadID = ? AND ownerID = ? LIMIT 1', $storageID, $downloadID, $ownerID);
		$resultSel = $this->database->query('SELECT * FROM storage_files WHERE storageID = ? AND downloadID = ? LIMIT 1', $storageID, $downloadID);
		if ($resultSel->getRowCount() != 1) {
			$this->flashMessage('CHYBA: Soubor nebyl nalezen, nebo pro jeho stažení nemáte dostatečná oprávnění.', 'danger');
			$this->redirect('Files:default');
			return;
		}

		$data = $resultSel->fetch();
		$basePath = '..' . DIRECTORY_SEPARATOR . 'data';
		$dirLetter = str_split($data->storageID, 1)[0];
		$baseName = $data->fileName;
		$hashName = $data->storageID;
		$storFile = $basePath . DIRECTORY_SEPARATOR . $dirLetter . DIRECTORY_SEPARATOR . $hashName;

		if (!file_exists($storFile)) {
			$this->flashMessage('CHYBA: Soubor "' . $baseName . '" nebyl nalezen.', 'danger');
			$this->redirect('Files:default');
			return;
		}

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . basename($baseName));
		//header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		//header('Cache-Control: public');
		header('Pragma: public');
		header('Content-Length: ' . filesize($storFile));
		ob_clean();
		flush();
		readfile($storFile);

		$this->redirect('Files:default');
	}

	public function actionDownloadBulk(string $storageID_List)
	{
		// OWNER ID (GET CURRENTLY LOGGED USER ID)
		$ownerID = (isset($this->getUser()->id) ? $this->getUser()->id : 0);

		$jsonData = [];

		if (empty($storageID_List) || empty($jsonData = json_decode($storageID_List, true))) {
			$this->flashMessage("CHYBA: Soubor nebyl nalezen, nebo nemáte dostatečná oprávnění.", "danger");
			$this->redirect('Files:default');
			return;
		}

		$basePath = ".." . DIRECTORY_SEPARATOR . "data";
		$zipName = "cloud_one_bulk_" . Carbon::now()->format('Y-m-d_H-i-s') . ".zip";
		$zipFile = $basePath . DIRECTORY_SEPARATOR . "zip" . DIRECTORY_SEPARATOR . $zipName;

		$zip = new \ZipArchive();
		if ($zip->open($zipFile, \ZipArchive::CREATE) === true) {
			foreach ($jsonData as $storageID) {
			//	$resultSel = $this->database->query('SELECT * FROM storage_files WHERE storageID = ? AND downloadID = ? AND ownerID = ? LIMIT 1', $storageID, $downloadID, $ownerID);
				$resultSel = $this->database->query('SELECT * FROM storage_files WHERE storageID = ? LIMIT 1', $storageID);

				if ($resultSel->getRowCount() != 1) {
					$this->flashMessage("CHYBA: Soubor (SID: " . $storageID . ") nebyl nalezen, nebo pro jeho stažení nemáte dostatečná oprávnění.", "danger");
					$this->redirect('Files:default');
					return;
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
			//header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			//header('Cache-Control: public');
			header('Pragma: public');
			header('Content-Length: ' . filesize($zipFile));
			ob_clean();
			flush();
			readfile($zipFile);

			unlink($zipFile);
		}

		$this->redirect('Files:default');
		//$this->terminate();
	}

	public function actionDelete($storageID)
	{
		// OWNER ID (GET CURRENTLY LOGGED USER ID)
		$ownerID = (isset($this->getUser()->id) ? $this->getUser()->id : 0);

		//$resultSel = $this->database->query('SELECT * FROM storage_files WHERE ownerID = ? AND storageID = ? LIMIT 1', $ownerID, $storageID);
		$resultSel = $this->database->query('SELECT * FROM storage_files WHERE storageID = ? LIMIT 1', $storageID);
		if ($resultSel->getRowCount() != 1) {
			$this->flashMessage('CHYBA: Soubor nebyl nalezen, nebo pro jeho odstranění nemáte dostatečná oprávnění.', 'danger');
			$this->redirect('Files:default');
			return;
		}

		$data = $resultSel->fetch();
		//$basePath = 'data';
		$basePath = '..' . DIRECTORY_SEPARATOR . 'data';
		$dirLetter = str_split($data->storageID, 1)[0];
		$baseName = $data->fileName;
		$hashName = $data->storageID;
		$storFile = $basePath . DIRECTORY_SEPARATOR . $dirLetter . DIRECTORY_SEPARATOR . $hashName;

		/*if (!file_exists($storFile)) {
			$this->flashMessage('CHYBA: Soubor "' . $baseName . '" nebyl nalezen.', 'danger');
			$this->redirect('Files:default');
			return;
		}*/

		if (file_exists($storFile) && !unlink($storFile)) {
			$this->flashMessage('CHYBA: Soubor "' . $baseName . '" nelze smazat.', 'danger');
			$this->redirect('Files:default');
			return;
		}

		$resultDel = $this->database->query('DELETE FROM storage_files WHERE fileID = ? LIMIT 1', $data->fileID);
		if ($resultDel->getRowCount() != 1) {
			$this->flashMessage('CHYBA: Soubor "' . $baseName . '" se nepodařilo odstranit z databáze.', 'danger');
			$this->redirect('Files:default');
			return;
		}

		$this->flashMessage('Soubor "' . $baseName . '" byl odstraněn.', 'info'); // TODO: NAPROGRAMOVAT KOŠ A PŘESOUVAT DO KOŠE !!!
		$this->redirect('Files:default');
	}

	/** Deletes files based on the JSON storage id field 
	 * @todo 			Optimize!!!
	 * @param	string	JSON contains storage IDs
	 * @return	bool
	 */
	public function actionDeleteBulk(string $storageID_List)
	{
		$jsonData = [];

		if (!empty($storageID_List)) {
			$jsonData = json_decode($storageID_List, true);
		}

		if (empty($jsonData)) {
			$this->redirect('Files:default');
			return;
		}

		foreach ($jsonData as $storageID) {
			//$this->actionDelete($storageID);

			// OWNER ID (GET CURRENTLY LOGGED USER ID)
			$ownerID = (isset($this->getUser()->id) ? $this->getUser()->id : 0);

			//$resultSel = $this->database->query('SELECT * FROM storage_files WHERE ownerID = ? AND storageID = ? LIMIT 1', $ownerID, $storageID);
			$resultSel = $this->database->query('SELECT * FROM storage_files WHERE storageID = ? LIMIT 1', $storageID);
			if ($resultSel->getRowCount() != 1) {
				$this->flashMessage('CHYBA: Soubor nebyl nalezen, nebo pro jeho odstranění nemáte dostatečná oprávnění.', 'danger');
				$this->redirect('Files:default');
				return;
			}

			$data = $resultSel->fetch();
			//$basePath = 'data';
			$basePath = '..' . DIRECTORY_SEPARATOR . 'data';
			$dirLetter = str_split($data->storageID, 1)[0];
			$baseName = $data->fileName;
			$hashName = $data->storageID;
			$storFile = $basePath . DIRECTORY_SEPARATOR . $dirLetter . DIRECTORY_SEPARATOR . $hashName;

			/*if (!file_exists($storFile)) {
				$this->flashMessage('CHYBA: Soubor "' . $baseName . '" nebyl nalezen.', 'danger');
				$this->redirect('Files:default');
				return;
			}*/

			if (file_exists($storFile) && !unlink($storFile)) {
				$this->flashMessage('CHYBA: Soubor "' . $baseName . '" nelze smazat.', 'danger');
				$this->redirect('Files:default');
				return;
			}

			$resultDel = $this->database->query('DELETE FROM storage_files WHERE fileID = ? LIMIT 1', $data->fileID);
			if ($resultDel->getRowCount() != 1) {
				$this->flashMessage('CHYBA: Soubor "' . $baseName . '" se nepodařilo odstranit z databáze.', 'danger');
				$this->redirect('Files:default');
				return;
			}

			$this->flashMessage('Soubor "' . $baseName . '" byl odstraněn.', 'info'); // TODO: NAPROGRAMOVAT KOŠ A PŘESOUVAT DO KOŠE !!!
		}

		$this->redirect('Files:default');
		return;
	}
}
