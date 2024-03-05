<?php

namespace App\Model;

use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use Nette;
use Nette\Security\Passwords;

final class CustomerAuthenticator implements Nette\Security\Authenticator
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
        $customer = $this->em->getCustomerRepository()->findOneBy(['email' => $username]);

        if (!$customer) {
            throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

        } elseif (!$this->passwords->verify($password, $customer->getPasswordHash())) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

        } elseif (!$customer->active) {
            throw new Nette\Security\AuthenticationException('The account is not approved.', self::NOT_APPROVED);
        } elseif ($this->passwords->needsRehash($customer->getPasswordHash())) {
            $customer->changePasswordHash($this->passwords->hash($password));
        }

        //$customer->changeLoggedAt();
        $this->em->flush($customer);

        $arr = $this->ed->get($customer);
        unset($arr['password']);

        return new Nette\Security\SimpleIdentity($customer->getId(), 'customer', $arr);
    }
}