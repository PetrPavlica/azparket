<?php

namespace App\Model\Facade;

use App\Model\Database\EntityManager;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use Nette\Database\Explorer;
use Ublaboo\ImageStorage\ImageStorage;

class BaseIntra
{
    /** @var EntityManager */
    private EntityManager $em;

    /** @var SQLHelper */
    private $SQLHelper;

    /** @var Explorer */
    protected $db;

    /** @var ImageStorage */
    public $imageStorage;

    public function __construct(EntityManager $em, SQLHelper $sql, Explorer $db)
    {
        $this->em = $em;
        $this->SQLHelper = $sql;
        $this->db = $db;
    }

    public function getAbsenceStateCount($user)
    {
        if (is_numeric($user)) {
            $user = $this->em->getUserRepository()->find($user);
        }
        $andWhere = '';
        if ($user && !in_array($user->group->id, [1])) {
            $andWhere = ' WHERE user_id = '.$user->id;
        }
        $qb = $this->em->getConnection()->prepare('
            SELECT state_id, COUNT(*) as count
            FROM absence
            '.$andWhere.'
            GROUP BY state_id
        ');

        $qb->execute();
        $result = $qb->fetchAllKeyValue();

        return $result;
    }

    public function getVisitProcessStateCount($user)
    {
        if (is_numeric($user)) {
            $user = $this->em->getUserRepository()->find($user);
        }
        if ($user && in_array($user->group->id, [2])) {
            //pro techniky pouze svoje
            if (isset($user->workersUsr[0]) && $user->workersUsr[0]) {
                $qb = $this->em->getConnection()->prepare('
                    SELECT vp.state_id, COUNT(*) as count
                    FROM visit_process vp
                    JOIN worker_on_visit_process wovp ON wovp.visit_process_id = vp.id
                    WHERE wovp.worker_id = '.$user->workersUsr[0]->id.'
                    GROUP BY vp.state_id
                ');
            } else {
                $qb = $this->em->getConnection()->prepare('
                    SELECT vp.state_id, 0 as count
                    FROM visit_process vp
                    GROUP BY vp.state_id
                ');
            }
        } else {
            $qb = $this->em->getConnection()->prepare('
                SELECT state_id, COUNT(*) as count
                FROM visit_process
                GROUP BY state_id
            ');
        }

        $qb->execute();
        $result = $qb->fetchAllKeyValue();

        return $result;
    }

    public function getVisitStateCount($user)
    {
        if (is_numeric($user)) {
            $user = $this->em->getUserRepository()->find($user);
        }
        if ($user && in_array($user->group->id, [2])) {
            //pro techniky pouze svoje
            if (isset($user->workersUsr[0]) && $user->workersUsr[0]) {
                $qb = $this->em->getConnection()->prepare('
                    SELECT v.state_id, COUNT(*) as count
                    FROM visit v
                    JOIN worker_on_visit wov ON wov.visit_id = v.id
                    WHERE wov.worker_id = '.$user->workersUsr[0]->id.'
                    GROUP BY v.state_id
                ');
            } else {
                $qb = $this->em->getConnection()->prepare('
                    SELECT v.state_id, 0 as count
                    FROM visit v
                    GROUP BY v.state_id
                ');
            }
        } else {
            $qb = $this->em->getConnection()->prepare('
                SELECT state_id, COUNT(*) as count
                FROM visit
                GROUP BY state_id
            ');
        }

        $qb->execute();
        $result = $qb->fetchAllKeyValue();

        return $result;
    }
}