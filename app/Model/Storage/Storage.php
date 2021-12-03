<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use App\Model;
use Nette\Utils\Json;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;
use Nette\Database\Explorer;
use Tracy\Debugger;

use Carbon\Carbon;

class Storage
{
	/** @var Nette\Database\Explorer @inject */
	public $db;

	/** @var int */
	protected $userID;

	public function __construct()
	{
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
			$result = $this->db->query('SELECT * FROM `'.$table.'` WHERE ? = ? LIMIT 1', $field, $randomCode);
			if(!isset($result) || $result->getRowCount() == 0) break;
		}

		return ($counter == $limit) ? str_repeat('f', $size) : $randomCode;
	}
}
