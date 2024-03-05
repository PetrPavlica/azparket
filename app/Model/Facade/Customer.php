<?php

namespace App\Model\Facade;

use App\Model\Database\Entity\CustomerInType;
use App\Model\Database\Entity\CustomerNotification;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use Nette\Security\Passwords;
use Nette\Utils\Random;
use Tracy\Debugger;

class Customer
{
    /** @var EntityManager */
    private EntityManager $em;

    /** @var Passwords */
    private Passwords $passwords;

    /** @var EntityData */
    private EntityData $entityData;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Passwords $passwords, EntityData $entityData) {
        $this->em = $em;
        $this->passwords = $passwords;
        $this->entityData = $entityData;
    }

    public function createNewCredencial($user) {
        $password = $this->randomCharacters(6);
        $user->setPassword($this->hash($password));
        $this->em->flush($user);
        return $password;
    }

    private function randomCharacters($delka_hesla) {
        $skupina_znaku = 'abcdefghjkopqrstuvwx123456789ABCDEFGHJKLMNOPQRSTUVWX';
        $vystup = '';
        $pocet_znaku = strlen($skupina_znaku) - 1;
        for ($i = 0; $i < $delka_hesla; $i++) {
            $vystup .= $skupina_znaku[ mt_rand(0, $pocet_znaku) ];
        }
        return $vystup;
    }

    /**
     * Hash password
     * @param string $password
     * @return string
     */
    public function hash($password) {
        return $this->passwords->hash($password);
    }

    public function createFromRegister($values)
    {
        try {
            $ent = $this->em->getCustomerRepository()->findOneBy(['email' => $values['email']]);
            if (!$ent) {
                $ent = new \App\Model\Database\Entity\Customer();
                $ent->setEmail($values['email']);
                $this->em->persist($ent);
                //$ent->setIdNo($values['idNo']);
                $ent->setPassword($this->hash($values['password']));
                //$ent->setCompany($values['company']);
                //$ent->setWorkshop($values['workshop']);
                //$ent->setIdNo($values['idNo']);
                //$ent->setVatNo($values['vatNo']);
                $ent->setName($values['name']);
                $ent->setSurname($values['surname']);
                $ent->setFullname($values['fullname']);
                $ent->setStreet($values['street']);
                $ent->setCity($values['city']);
                $ent->setZip($values['zip']);
                $ent->setPhone($values['phone']);
                $ent->setActive(1);
                $this->em->flush($ent);
            }

            return $ent;
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return null;
    }

    public function recoveryPassword($values)
    {
        try {
            $qb = $this->em->getCustomerRepository()->createQueryBuilder('c')
                ->where('c.email = :email')
                ->setParameters([
                    'email' => $values['email'],
                ])
                ->setMaxResults(1);
            $customer = $qb->getQuery()->getOneOrNullResult();
            $customer->setRecoveryHash(Random::generate(40));
            $this->em->flush($customer);

            return $customer;
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return null;
    }

    public function checkPasswordHash($email, $hash)
    {
        $qb = $this->em->getCustomerRepository()->createQueryBuilder('c')
            ->where('c.email = :email')
            ->setParameters([
                'email' => $email,
            ])
            ->setMaxResults(1);
        $customer = $qb->getQuery()->getOneOrNullResult();

        return $customer && $customer->recoveryHash === $hash;
    }

    public function newPassword($values)
    {
        try {
            $qb = $this->em->getCustomerRepository()->createQueryBuilder('c')
                ->where('c.email = :email')
                ->setParameters([
                    'email' => $values['email'],
                ])
                ->setMaxResults(1);
            $customer = $qb->getQuery()->getOneOrNullResult();
            $customer->setRecoveryHash(null);
            $customer->setPassword($this->hash($values['password']));
            $this->em->flush($customer);
            return $customer;
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return null;
    }

    public function profileSave($values, $userId)
    {
        $notifications = $values['notifications'];
        unset($values['notifications']);
        try {
            $customer = $this->em->getCustomerRepository()->find($userId);
            if (empty($values['password'])) {
                unset($values['password']);
            }
            $customer = $this->entityData->set($customer, $values);
            $this->em->flush($customer);

            $states = $this->em->getProcessStateRepository()->findBy(['active' => true, 'notification' => true], ['order' => 'ASC']);
            if ($states) {
                $stateIds = [];
                $this->em->beginTransaction();
                foreach ($states as $s) {
                    $state = $this->em->getCustomerNotificationRepository()->findOneBy(['customer' => $customer, 'processState' => $s]);
                    if (!$state) {
                        $state = new CustomerNotification();
                        $state->setCustomer($customer);
                        $state->setProcessState($s);
                        $this->em->persist($state);
                    }
                    $state->setActive(in_array($s->getId(), $notifications));
                    $this->em->flush($state);
                    $stateIds[] = $state->getId();
                }
                if (count($stateIds)) {
                    $this->em->createQuery('
                        DELETE FROM ' . CustomerNotification::class . ' c
                        WHERE c.customer = :c and c.id NOT IN (:ids)
                    ')->execute([
                        'c' => $customer->id,
                        'ids' => $stateIds
                    ]);
                } else {
                    $this->em->createQuery('
                        DELETE FROM ' . CustomerNotification::class . '
                        WHERE customer = :c
                    ')->execute([
                        'c' => $customer->id
                    ]);
                }
                $this->em->commit();
            }

            return $customer;
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return null;
    }
}
