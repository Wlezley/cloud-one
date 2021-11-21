<?php
/**
 * Copyright (c) 2015 Petr Olišar (http://olisar.eu)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */

namespace Oli\Form;

use Nette\Utils\Random;


/**
 * Description of DropzoneUploader
 *
 * @author Petr Olišar <petr.olisar@gmail.com>
 */
class DropzoneUploader extends \Nette\Application\UI\Control
{
	private $wwwDir;

	private $path;

	private $settings;

	private $photo;

	private $isImage = TRUE;

	private $allowType = NULL;

	private $rewriteExistingFiles = FALSE;

	public $onSuccess = [];

	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function setPhoto($photo)
	{
		$this->photo = $photo;
	}

	public function setSettings(array $settings)
	{
		$this->settings = $settings;
		return $this;
	}

	public function isImage($isImage = TRUE)
	{
		$this->isImage = $isImage;
		return $this;
	}

	public function setWwwDir($wwwDir)
	{
		$this->wwwDir = $wwwDir;
		return $this;
	}

	public function setAllowType($allowType)
	{
		$this->allowType = $allowType;
		return $this;
	}

	public function setRewriteExistingFiles($rewriteExistingFiles)
	{
		$this->rewriteExistingFiles = $rewriteExistingFiles;
		return $this;
	}

	public function createComponentUploadForm()
	{
		$form = new \Nette\Application\UI\Form();

		$form->getElementPrototype()->addAttributes(["class" => "dropzone"]);

		$form->addUpload("file", NULL)
			->setHtmlId("fileUpload");

		$form->onSuccess[] = [$this, 'process'];

		return $form;
	}

	public function render()
	{
		$settings = $this->settings;
		$settings['onSuccess'] = $this->link($settings['onSuccess']);
		$settings['onUploadStart'] = $this->link('checkDirectory!');
		$this->template->uploadSettings = \Nette\Utils\Json::encode($settings);
		$this->template->setFile(__DIR__ . '/template.latte');
		$this->template->render();
	}

	// ############################################################################################

	/** Vygeneruje nahodny alfa-numericky kod
	 * @param	integer			$size
	 *
	 * @return	int|NULL
	 */
	public function getRandomCode($size)
	{
		$charlist = '0-9a-z';
		//use Nette\Utils\Random;
		return Random::generate($size, $charlist);
	}

	// ############################################################################################

	public function process(\Nette\Application\UI\Form $form, $values)
	{
		$file = $values->file;

		if(!$file instanceof \Nette\Http\FileUpload) {
			throw new \Nette\FileNotFoundException('Nahraný soubor není typu Nette\Http\FileUpload. Pravděpodobně se nenahrál v pořádku.');
		}

		if(!$file->isOk()) {
			throw new \Nette\FileNotFoundException('Soubor byl poškozen: ' . $file->error);
		}

		/*if($this->isImage && $file->isImage() !== $this->isImage) {
			throw new \Nette\InvalidArgumentException('Soubor musí být obrázek');
		}*/

		/*if(is_array($this->allowType) && in_array($file->getContentType(), $this->allowType, TRUE)) {
			throw new \Nette\InvalidArgumentException('Soubor není povoleného typu');
		}*/

		// NENI POTREBA ??
		$this->handleCheckDirectory();

		// Filename on storage
		$storageID = $this->getRandomCode(16);

		// Storage directory (first letter of storage filename)
		$dirLetter = str_split($storageID, 1)[0];

		// Original filename
		$fileName = $file->getName();

		// Full storage path
		$targetPath = '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $dirLetter;

		// Suffix
		$SplitedName = \Nette\Utils\Strings::split($file->getSanitizedName(), '~\.\s*~');
		$suffix = strtolower(array_pop($SplitedName));

		//$this->moveUploadedFile($file, $targetPath, $storageID);
		$file->move($targetPath . DIRECTORY_SEPARATOR . $storageID);

		$size = $file->getSize();
		$hash = hash_file("md5",	$targetPath . DIRECTORY_SEPARATOR . $storageID);	// 32 Chars
//		$hash = hash_file("sha1",	$targetPath . DIRECTORY_SEPARATOR . $storageID);	// 40 Chars
//		$hash = hash_file("sha256",	$targetPath . DIRECTORY_SEPARATOR . $storageID);	// 64 Chars

		$this->onSuccess($this, $storageID, $fileName, $suffix, $size, $hash);
	}

	// ############################################################################################

	private function moveUploadedFile($file, $targetPath, $storageID)
	{
		$file->move($targetPath . DIRECTORY_SEPARATOR . $storageID);
	}

	public function handleCheckDirectory()
	{
		$oldmask = umask(0);

		if(!is_dir($this->wwwDir . DIRECTORY_SEPARATOR . $this->path)) {
			mkdir($this->wwwDir . DIRECTORY_SEPARATOR . $this->path, 0755, true);
		}

		umask($oldmask);
	}

	public function handleRefresh(){
		$this->redrawControl('photos');
	}
}
