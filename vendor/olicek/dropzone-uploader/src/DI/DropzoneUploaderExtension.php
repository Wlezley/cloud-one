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
			'ajax' => true,
			'onSuccess' => 'refresh!'
		],
		'photo' => [
			'width' => null,
			'height' => null,
			'flags' => null, // \Nette\Utils\Image::FIT,
			'quality' => null,
			'type' => null
		],
		'isImage' => false,
		'allowType' => [],
		'rewriteExistingFiles' => false
	];

	public function getDefaults()
	{
		return $this->defaults;
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
