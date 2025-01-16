<?php

declare(strict_types=1);

namespace App\Model\User;

use Nette\Database\Explorer;
use Nette\Http\Session;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

class Authenticator implements \Nette\Security\Authenticator
{
    public function __construct(protected Explorer $db, private Session $session, private Passwords $passwords)
    {
    }

    public function authenticate(string $username, #[\SensitiveParameter] string $password): SimpleIdentity
    {
        $user = $this->db->table('user_accounts')
            ->where(['username' => $username])
            ->limit(1)
            ->fetch();

        if (!($user && $this->passwords->verify($password, $user['password']))) {
            throw new AuthenticationException('Invalid credentials.', self::InvalidCredential);
        } elseif ($this->passwords->needsRehash($user['password'])) {
            $user->update(['password' => $this->passwords->hash($password)]);
        }

        $this->session->regenerateId();
        $sessionId = $this->session->getId();

        $data = [
            'id' => $user['id'],
            'username' => $user['username'],
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'telefon' => $user['telefon'],
            'role' => $user['role'],
            'session_id' => $sessionId
        ];

        return new SimpleIdentity($user['id'], $user['role'], $data);
    }

    public function addUser(string $username, string $password, string $role = 'user'): void
    {
        $this->db->table('user_accounts')->insert([
            'username' => $username,
            'password' => $this->passwords->hash($password),
            'role' => $role
        ]);
    }
}
