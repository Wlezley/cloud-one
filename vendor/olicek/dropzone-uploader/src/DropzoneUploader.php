<?php
/**
 * Copyright (c) 2015 Petr Olišar (http://olisar.eu)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */

namespace Oli\Form;


/**
 * Description of DropzoneUploader
 *
 * @author Petr Olišar <petr.olisar@gmail.com>
 */
class DropzoneUploader extends \Nette\Application\UI\Control
{
	private string $wwwDir;

	private string $path;

	private array $settings;

	private array $photo;

	private bool $isImage = true;

	private array $allowType = [];

	private bool $rewriteExistingFiles = false;

	public $onSuccess = [];

	public function setPath(string $path): DropzoneUploader
	{
		$this->path = $path;
		return $this;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function setPhoto(array $photo): DropzoneUploader
	{
		$this->photo = $photo;
		return $this;
	}

	public function setSettings(array $settings): DropzoneUploader
	{
		$this->settings = $settings;
		return $this;
	}

	public function isImage(bool $isImage = true): DropzoneUploader
	{
		$this->isImage = $isImage;
		return $this;
	}

	public function setWwwDir(string $wwwDir): DropzoneUploader
	{
		$this->wwwDir = $wwwDir;
		return $this;
	}

	public function setAllowType(array $allowType): DropzoneUploader
	{
		$this->allowType = $allowType;
		return $this;
	}

	public function setRewriteExistingFiles(bool $rewriteExistingFiles): DropzoneUploader
	{
		$this->rewriteExistingFiles = $rewriteExistingFiles;
		return $this;
	}

	public function createComponentUploadForm(): \Nette\Application\UI\Form
	{
		$form = new \Nette\Application\UI\Form();

		$form->getElementPrototype()->addAttributes(["class" => "dropzone"]);

		$form->addUpload("file", NULL)
			->setHtmlId("fileUpload");

		$form->onSuccess[] = [$this, 'process'];

		return $form;
	}

	public function render(): void
	{
		$settings = $this->settings;
		$settings['onSuccess'] = $this->link($settings['onSuccess']);
		$settings['onUploadStart'] = $this->link('checkDirectory!');
		$this->template->uploadSettings = \Nette\Utils\Json::encode($settings);
		$this->template->setFile(__DIR__ . '/template.latte');
		$this->template->render();
	}

	public function process(\Nette\Application\UI\Form $form, $values): void
	{
		$file = $values->file;

		if(!$file instanceof \Nette\Http\FileUpload) {
			throw new \Nette\FileNotFoundException('Nahraný soubor není typu Nette\Http\FileUpload. Pravděpodobně se nenahrál v pořádku.');
		}

		if(!$file->isOk()) {
			throw new \Nette\FileNotFoundException('Soubor byl poškozen: ' . $file->error);
		}

		// if($this->isImage && $file->isImage() !== $this->isImage) {
		//     throw new \Nette\InvalidArgumentException('Soubor musí být obrázek');
		// }

		// if(!empty($this->allowType) && !in_array($file->getContentType(), $this->allowType, TRUE)) {
		//     throw new \Nette\InvalidArgumentException('Soubor není povoleného typu');
		// }

		// Root directory check & create if not extists
		$this->handleCheckDirectory();

		// Filename on storage
		$storageID = $this->getRandomCode(16);

		// Storage directory (first letter of storage filename)
		$dirLetter = str_split($storageID, 1)[0];

		// Original filename
		$fileName = \Nette\Utils\Strings::webalize($file->getUntrustedName(), " .-_+", false);

		// Full storage path
		$targetPath = '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $dirLetter;

		// Suffix
		$SplitedName = \Nette\Utils\Strings::split($file->getSanitizedName(), '~\.\s*~');
		$suffix = strtolower(array_pop($SplitedName));

		$file->move($targetPath . DIRECTORY_SEPARATOR . $storageID);

		$size = $file->getSize();
		$hash = hash_file("md5",	$targetPath . DIRECTORY_SEPARATOR . $storageID); // 32 Chars
		// $hash = hash_file("sha1",	$targetPath . DIRECTORY_SEPARATOR . $storageID); // 40 Chars
		// $hash = hash_file("sha256",	$targetPath . DIRECTORY_SEPARATOR . $storageID); // 64 Chars

		$this->onSuccess($this, $storageID, $fileName, $suffix, $size, $hash);
	}

	public function getRandomCode(int $size): string
	{
		$charlist = '0-9a-z';
		return \Nette\Utils\Random::generate($size, $charlist);
	}

	public function handleCheckDirectory(): void
	{
		$oldmask = umask(0);

		if(!is_dir($this->wwwDir . DIRECTORY_SEPARATOR . $this->path)) {
			mkdir($this->wwwDir . DIRECTORY_SEPARATOR . $this->path, 0755, true);
		}

		umask($oldmask);
	}

	public function handleRefresh(): void
	{
		$this->redrawControl('photos');
	}
}
