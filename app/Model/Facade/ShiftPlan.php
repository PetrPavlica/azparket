<?php

namespace App\Model\Facade;

use App\Model\Database\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Nette\Database\Context;

class ShiftPlan
{
    /** @var EntityManager */
    private EntityManager $em;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function findPairsForSelect(String $dateString = NULL, String $namePlan = NULL, int $linePlan = 1, int $dontCheckWorkers = 0): ?array
    {

        $startMonthDay = new \DateTime();
        $startMonthDay->setTimestamp(strtotime('first day of this month ' . $dateString));
        $endMonthDay = new \DateTime();
        $endMonthDay->setTimestamp(strtotime('last day of this month ' . $dateString));
        $startMonthDay->setTime(0,0,0);
        $endMonthDay->setTime(23,59,59);

        $qb = $this->em->getConnection()->prepare("
                SELECT wp.worker_id, (COUNT(*)*12) as count 
                FROM worker_in_plan wp 
                LEFT JOIN shift_plan p ON p.id = wp.plan_id 
                WHERE p.date_plan BETWEEN '".$startMonthDay->format('Y-m-d H:i:s')."' AND '".$endMonthDay->format('Y-m-d H:i:s')."' 
                GROUP BY wp.worker_id
            ");

        $qb->execute();
        $result = $qb->fetchAllKeyValue();

        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startMonthDay));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endMonthDay));
        $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', '1'));
        $plans = $this->em->getShiftPlanRepository()->matching($criteriaStart);
        $fpd = array('A' => 0, 'B' => 0, 'C' => 0, 'D' => 0);
        foreach ($plans as $plan) {
            if(in_array($plan->shift, ['A', 'B', 'C', 'D'])) {
                $fpd[$plan->shift] += $plan->active ? 12 : 0;
            }
        }

        $notThisWorkers = array();
        $thisShiftWorkers = array();
        $thisShiftLineWorkers = array();
        if($dateString) {
            $plans = $this->em->getShiftPlanRepository()->findBy(['dateString' => $dateString]);
            foreach ($plans as $plan) {
                if($plan->workers && count($plan->workers)) {
                    if($namePlan == $plan->name) {
                        if($linePlan == $plan->productionLine) {
                            foreach ($plan->workers as $conn) {
                                $notThisWorkers[$conn->worker->id] = $conn->worker->id;
                                $thisShiftWorkers[$conn->worker->id] = $conn->worker->id;
                                $thisShiftLineWorkers[$conn->worker->id] = $conn->worker->id;
                            }
                        } else {
                            foreach ($plan->workers as $conn) {
                                $notThisWorkers[$conn->worker->id] = $conn->worker->id;
                                $thisShiftWorkers[$conn->worker->id] = $conn->worker->id;
                            }
                        }
                    } else {
                        foreach ($plan->workers as $conn) {
                            $notThisWorkers[$conn->worker->id] = $conn->worker->id;
                        }
                    }
                }
            }

            if($namePlan == 1) {
                $newDate = new \DateTime($dateString . ' 00:00:00');
                $newDate->modify('-1 day');
                $nDateString = $newDate->format('Y-m-d');

                $plans = $this->em->getShiftPlanRepository()->findBy(['dateString' => $nDateString, 'name' => 2]);
                foreach ($plans as $plan) {
                    if($plan->workers && count($plan->workers)) {
                        foreach ($plan->workers as $conn) {
                            $notThisWorkers[$conn->worker->id] = $conn->worker->id;
                        }
                    }
                }
            } else {
                $newDate = new \DateTime($dateString . ' 00:00:00');
                $newDate->modify('+1 day');
                $nDateString = $newDate->format('Y-m-d');

                $plans = $this->em->getShiftPlanRepository()->findBy(['dateString' => $nDateString, 'name' => 1]);
                foreach ($plans as $plan) {
                    if($plan->workers && count($plan->workers)) {
                        foreach ($plan->workers as $conn) {
                            $notThisWorkers[$conn->worker->id] = $conn->worker->id;
                        }
                    }
                }
            }

            $planDate = new \DateTime($dateString . ' 00:00:00');
            $criteriaVacation = new Criteria();
            $criteriaVacation->where(Criteria::expr()->lte('dateStart', $planDate));
            $criteriaVacation->andWhere(Criteria::expr()->gte('dateEnd', $planDate));
            $checkVacations = $this->em->getVacationRepository()->matching($criteriaVacation);
            foreach ($checkVacations as $checkVacation) {
                if($checkVacation->worker) {
                    $notThisWorkers[$checkVacation->worker->id] = $checkVacation->worker->id;
                }
            }
        }

        $entities = $this->em->getWorkerRepository()->findBy(['active' => 1, 'workerPosition' => [1,2,3,4,5,6]], ['surname' => 'ASC', 'name' => 'ASC']);
        $res = array();
        foreach ($entities as $entity) {
            if($dontCheckWorkers || !in_array($entity->id, $notThisWorkers)) {
                if($entity->notWorker && in_array($entity->notWorker->id, $thisShiftWorkers)) {
                    continue;
                }
                //if(!$entity->productionLine || $entity->productionLine->id == $linePlan) {
                    $res[$entity->id] = $entity->surname . ' ' . $entity->name . ($entity->workerPosition ? ' ('.$entity->workerPosition->name .')' : '') .
                        ($entity->shift ? ', Směna '.$entity->shift : '') . ($entity->productionLine ? ' ('.$entity->productionLine->name.')' : '') . ' [' .  (isset($result[$entity->id]) ? $result[$entity->id] : 0) . '/' .
                        ($entity->timeFund ? $entity->timeFund : ($entity->shift ? $fpd[$entity->shift] : '-')) . ']' . ($entity->agency ? ' - agentura' : '') .
                        (($entity->yesWorker && in_array($entity->yesWorker->id, $thisShiftLineWorkers)) ? '<b></b>' : '');
                //}
            }
        }

        //asort($res);
        return $res;
    }

}
