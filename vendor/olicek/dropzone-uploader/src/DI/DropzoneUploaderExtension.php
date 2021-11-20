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
class DropzoneUploaderExtension extends \Nette\DI\CompilerExtension
{
	public $defaults = [
		'wwwDir' => 'data',
		'path' => '',
		'settings' => [
			'maxFiles' => 50,
			'fileSizeLimit' => 5000000000, // 5000 MB
			//'fileSizeLimit' => 1000000000, // 1000 MB
			//'fileSizeLimit' => 100, // 10 MB
			'ajax' => TRUE,
			'onSuccess' => 'refresh!'
		],
		'photo' => [
			'width' => NULL,
			'height' => NULL,
			'flags' => NULL, //\Nette\Utils\Image::FIT,
			'quality' => NULL,
			'type' => NULL
		],
		'isImage' => FALSE,
		'allowType' => NULL,
		'rewriteExistingFiles' => FALSE
	];

	public function getDefaults()
	{
		return /*$this->getConfig(*/$this->defaults;//);
	}

	public function loadConfiguration()
	{
		//$config = $this->getConfig($this->defaults);
		$config = $this->getDefaults();
		$builder = $this->getContainerBuilder();

		$builder->addFactoryDefinition($this->prefix('dropzone'))
			->setImplement('Oli\Form\DropzoneUploaderFactory')
			->getResultDefinition()
				->setFactory('Oli\Form\DropzoneUploader')
				->addSetup('setWwwDir', [$config['wwwDir']])
				->addSetup('setPath', [$config['path']])
				->addSetup('setSettings', [$config['settings']])
				->addSetup('setPhoto', [$config['photo']])
				->addSetup('isImage', [$config['isImage']])
				->addSetup('setAllowType', [$config['allowType']])
				->addSetup('setRewriteExistingFiles', [$config['rewriteExistingFiles']]);
	}
}
