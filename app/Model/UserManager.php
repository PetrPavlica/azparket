<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use Nette;
use Nette\Security\Passwords;


/**
 * Users management.
 */
final class UserManager implements Nette\Security\Authenticator
{
	use Nette\SmartObject;

    private EntityManager $em;
    private EntityData $ed;
	private Passwords $passwords;


	public function __construct(EntityManager $em, EntityData $ed, Passwords $passwords)
	{
	    $this->em = $em;
	    $this->ed = $ed;
		$this->passwords = $passwords;
	}


	/**
	 * Performs an authentication.
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(string $username, string $password): Nette\Security\SimpleIdentity
	{
		$user = $this->em->getUserRepository()->findOneBy(['username' => $username, 'isBlocked' => false]);

		if (!$user) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		} elseif (!$this->passwords->verify($password, $user->getPasswordHash()) && crypt($password,'rrtzuaascc') !== $user->getPasswordHash()) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

		} elseif ($this->passwords->needsRehash($user->getPasswordHash())) {
            $user->changePasswordHash($this->passwords->hash($password));
		}

		$user->changeLoggedAt();
		$this->em->flush($user);

		$arr = $this->ed->get($user);
		unset($arr['password']);

		if ($user->group) {
		    $arr['groupName'] = $user->group->name;
        }

		return new Nette\Security\SimpleIdentity($user->getId(), $user->getPermissions(), $arr);
	}
}
