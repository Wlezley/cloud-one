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
	 * 
	 * @param	int				$size
	 * @param	string|null		$table
	 * @param	string|null		$field
	 *
	 * @return	string
	 */
	public function getRandomCode($size, $table = null, $field = null)
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

			if (!isset($result) || $result->getRowCount() == 0) {
				break;
			}
		}

		return ($counter == $limit) ? str_repeat('f', $size) : $randomCode;
	}
}
