<?php

declare(strict_types=1);

namespace App\Model\User;

use Nette;

use Nette\Database\Context;
use Nette\Security\IAuthenticator;
use Nette\Security\Passwords;
use Nette\Security\Identity;
//use Nette\Security\AuthenticationException;


class Authenticator implements Nette\Security\IAuthenticator
{
	/** @var Nette\Database\Context */
	protected $database;

	/** @var Passwords */
	private $passwords;

	public function __construct(Context $database, Passwords $passwords)
	{
		$this->database = $database;
		$this->passwords = $passwords;
	}

	/**
	 * @param array $credentials
	 * @return Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials) : Nette\Security\IIdentity
	{
		list($username, $password) = $credentials;

		$row = $this->database->table('user_accounts')->where('username', $username)->fetch();

		if (!($row && $this->passwords->verify($password, $row->password))) {
			throw new Nette\Security\AuthenticationException('Nesprávné přihlašovací údaje');
		}

		$user = $row->toArray();
		unset($user['password']);

		return new Nette\Security\Identity($user['id'], $user['role'], $user);
	}

	/**
	 * @param $username
	 * @param $password
	 * @param string $role
	 */
	public function addUser($username, $password, $role = 'user')
	{
		$this->database->table('user_accounts')->insert([
			'username' => $username,
			'password' => $this->passwords->hash($password),
			'role'	   => $role
		]);
	}
}
