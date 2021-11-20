<?php

declare(strict_types=1);

namespace App\Model\SmsBrana;

use Nette;
use App\Model;
use Nette\Utils\Json;
use Nette\Utils\ArrayHash;
use Nette\Database\Context;
use Tracy\Debugger;
use Carbon\Carbon;


class SmsBrana
{
	/** @var Nette\Database\Context */
	protected $database;

	/** @var int */
	protected $userID;

	public function __construct(Context $database)
	{
		$this->database = $database;
	}

	public function sendSMS($number, $message, $zakazka = 0, $user = 0)
	{
		$login = 'filipkozeluh123_h1';
		$password = '019aaa312';
		//$number = urlencode($number);
		$message = urlencode($message);

		// SMS API URL BUILD
		$apiurl = "https://api.smsbrana.cz/smsconnect/http.php?login=".$login."&password=".$password."&action=send_sms&number=".$number."&message=".$message;

		// For this to work, file_get_contents requires that allow_url_fopen is enabled.
		// This can be done at runtime by including: ini_set("allow_url_fopen", 1);
		$response = file_get_contents($apiurl);

		// SMS DB LOG
		$this->logSMS($number, $message, $response, $zakazka, $user);

		return $response;
	}

	public function logSMS($number, $message, $response, $zakazka = 0, $user = 0) // (user 0 = SYSTEM)
	{
		return $this->database->table('log_sms')->insert([
			//'id'			=> //AUTOINCREMENT
			'zakazka'		=> $zakazka,
			'user'			=> $user,
			//'date'		=> Carbon::now()->format('Y-m-d H:i:s'),
			'telefon'		=> $number,
			'message'		=> $message,
			'response'		=> $response
		]);
	}

	public function getSMSLogByOrderID($zakazka = 0)
	{
		$dataIn = $this->database->table('log_sms')->where('zakazka', $zakazka)->order('date DESC')->fetchAll();
		$dataOut = array();

		if(isset($dataIn) && $dataIn)
		{
			foreach ($dataIn as $m_id => $item)
			{
				$userName = "<SYSTEM>"; // Default ID: 0 (known as SYSTEM user)
				if($item->user != 0)
				{
					$uname = $this->database->query('SELECT username FROM user_accounts WHERE id = ?', $item->user);
					$userName = (($uname->getRowCount() != 1) ? "ID: " . $item->user : $uname->fetch()->username);
				}

				$dataOut[$m_id] = [
					'id'							=> $item->id,
					'zakazka'						=> $item->zakazka,
					'user'							=> $userName,
					'date'							=> ($item->date == NULL) ? "N/A" : Carbon::createFromTimestamp($item->date->getTimestamp(), 'Europe/Prague')->format('d.m.Y H:i'),
					'telefon'						=> $item->telefon,
					'message'						=> urldecode($item->message),
					'response'						=> $item->response
				];
			}
		}
		return $dataOut;
	}
}
