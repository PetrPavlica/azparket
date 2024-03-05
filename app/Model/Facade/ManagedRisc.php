<?php

namespace App\Model\Facade;

use App\Model\Database\Entity\ManagedRisc as ManagedRiscEntity;
use App\Model\Database\Entity\ManagedRiscRevaluation;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class ManagedRisc
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

    public function saveItems(ManagedRiscEntity $risc, $data)
    {
        $parameters = [
            'id' => $risc->id
        ];

        if (isset($data['items'])) {
            $itemsNotDel = [];
            foreach ($data['items'] as $k => $item) {
                $itemEnt = null;
                if (isset($item['id']) && $item['id']) {
                    $itemEnt = $this->em->getManagedRiscRevaluationRepository()->find($item['id']);
                }
                if (!$itemEnt) {
                    $itemEnt = new ManagedRiscRevaluation();
                    $this->em->persist($itemEnt);
                    $itemEnt->setManagedRisc($risc);
                }
                $itemEnt->setRevaluationDate(date_create_from_format('j. n. Y', $item['revaluationDate']));
                $itemEnt->setProbability($item['probability']);
                $itemEnt->setRelevance($item['relevance']);
                $itemEnt->setDetectability($item['detectability']);
                $itemEnt->setBenefit($item['benefit']);
                $itemEnt->setFeasibility($item['feasibility']);
                $itemEnt->setRevalRespond($item['revalRespond']);
                $itemEnt->setRealizationState($item['realizationState']);
                $this->em->flush($itemEnt);
                $itemsNotDel[] = $itemEnt->id;
            }

            if (count($itemsNotDel)) {
                $qb = $this->em->createQuery('DELETE ' . ManagedRiscRevaluation::class . ' s WHERE s.managedRisc = :id and s.id not in(:ids)');
                $parameters['ids'] = $itemsNotDel;
                $qb->execute($parameters);
            } else {
                $removeAll = true;    
            }
        
        } else if (isset($risc->revaluations) && count($risc->revaluations)) {
            $removeAll = true;
        }
        
        if (isset($removeAll)) {
            $qb = $this->em->createQuery('DELETE ' . ManagedRiscRevaluation::class . ' s WHERE s.managedRisc = :id');
            $qb->execute($parameters);
        }
    }

    /**
     * Return array of revaluations in managed risc
     */
    public function getItems($risc)
    {
        $items = $this->em->getManagedRiscRevaluationRepository()->findBy(['managedRisc' => $risc->id]);

        $arr = [];
        foreach ($items as $k => $s) {
            $arr[$k] = [
                'id' => $s->id,
                'revaluationDate' => date_format($s->revaluationDate, "j. m. Y"),
                'probability' => $s->probability,
                'relevance' => $s->relevance,
                'detectability' => $s->detectability,
                'benefit' => $s->benefit,
                'feasibility' => $s->feasibility,
                'revalRespond' => $s->revalRespond,
                'realizationState' => $s->realizationState,
            ];
        }

        return $arr;
    }

    public function getDataFromManagedRisc(ManagedRiscEntity $risc)
    {
        $arr = [
            'id' => $risc->id,
            'items' => $this->getItems($risc)
        ];

        return $arr;
    }
}