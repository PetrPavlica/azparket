<?php

namespace App\Model\Facade;

use App\Model\Database\Entity\ManagedChange;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class ChangeManager
{
    /** @var EntityManager */
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function approveChangeManager($change, $user) {
        if (is_numeric($change)) {
            $change = $this->em->getManagedChangeRepository()->find($change);
        }
        if (is_numeric($user)) {
            $user = $this->em->getUserRepository()->find($user);
        }

        $change->setApproveUser($user);
        $change->setApproveDate(new DateTime());

        $this->em->flush($change);
        return true;
    }
}