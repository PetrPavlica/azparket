<?php

namespace App\Model\Facade;

use App\Model\Database\Entity\Task as TaskEntity;
use App\Model\Database\Entity\TaskLog;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class Task
{
    /** @var EntityManager */
    private EntityManager $em;

    /** @var SQLHelper */
    private $SQLHelper;

    public function __construct(EntityManager $em, SQLHelper $sql)
    {
        $this->em = $em;
        $this->SQLHelper = $sql;
    }

    public function swapStateFast($id, $idto, $userId) {
        $task = $this->em->getTaskRepository()->find($id);
        if ($task) {
            $newState = $this->em->getTaskStateRepository()->find($idto);
            if (!$newState) {
                return false;
            }
            $oldState = $task->taskState;

            if ($oldState->id != $newState->id) {
                $task->setInStateDate(new \DateTime());
                $this->logTaskText($task, $userId,
                    'Změna stavu úkolu.', $oldState->name, $newState->name);
            }

            $task->setTaskState($newState);
            $task->setLastEditedDate(new \DateTime());
            $this->em->flush($task);
            return true;
        }
        return false;
    }

    public function logTaskText($task, $user, $text, $old = '', $new = '') {
        if (is_numeric($task)) {
            $task = $this->em->getTaskRepository()->find($task);
        }
        if (is_array($user) && isset($user[ 'id' ])) {
            $user = $this->em->getUserRepository()->find($user[ 'id' ]);
        } else if (is_numeric($user)) {
            $user = $this->em->getUserRepository()->find($user);
        }

        $log = new TaskLog();
        $log->setUser($user);
        $log->setTask($task);
        $log->setText($text);
        $log->setOldText($old);
        $log->setNewText($new);
        $this->em->persist($log);
        $this->em->flush();
    }

}