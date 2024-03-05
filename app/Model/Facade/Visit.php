<?php

namespace App\Model\Facade;

use App\Model\Database\Entity\Visit as VisitEntity;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class Visit
{
    /** @var EntityManager */
    private EntityManager $em;

    /** @var EntityData */
    public $ed;

    /** @var SQLHelper */
    private $SQLHelper;

    public function __construct(EntityManager $em, EntityData $ed, SQLHelper $sql)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->SQLHelper = $sql;
    }

    public function createCopiesVisit($ids) {
        foreach ($ids as $id) {
            $oldVisit = $this->em->getVisitRepository()->find($id);
            $oldVisitArr = $this->ed->get($oldVisit);
            $newVisit = new \App\Model\Database\Entity\Visit();
            $newVisit = $this->ed->set($newVisit, $oldVisitArr);
            $newVisit->setId(null);
            $newVisit->setWorker(null);
            $newVisit->setMaterial(null);
            $newVisit->setCustomer(null);
            $newVisit->setCustomerOrdered(null);
            $newVisit->setTraffic(null);
            $newVisit->setVisitProcess(null);
            $newVisit->setStatus(null);
            $newVisit->setState(null);

            if($oldVisit->customer) {
                $newVisit->setCustomer($this->em->getCustomerRepository()->find($oldVisit->customer->id));
            }
            if($oldVisit->customerOrdered) {
                $newVisit->setCustomerOrdered($this->em->getCustomerOrderedRepository()->find($oldVisit->customerOrdered->id));
            }
            if($oldVisit->traffic) {
                $newVisit->setTraffic($this->em->getTrafficRepository()->find($oldVisit->traffic->id));
            }
            if($oldVisit->visitProcess) {
                $newVisit->setVisitProcess($this->em->getVisitProcessRepository()->find($oldVisit->visitProcess->id));
            }
            if($oldVisit->status) {
                $newVisit->setStatus($this->em->getVisitStatusRepository()->find($oldVisit->status->id));
            }
            if($oldVisit->state) {
                $newVisit->setState($this->em->getVisitStateRepository()->find($oldVisit->state->id));
            }
            $this->em->persist($newVisit);
            $this->em->flush($newVisit);

            if ($oldVisit->worker) {
                foreach ($oldVisit->worker as $w) {
                    if ($w) {
                        $workerOnVisit = new \App\Model\Database\Entity\WorkerOnVisit();
                        $workerOnVisit->setVisit($newVisit);
                        $workerOnVisit->setWorker($w->worker);
                        $this->em->persist($workerOnVisit);
                        $this->em->flush($workerOnVisit);
                    }
                }
            }
            $this->em->flush();
        }
    }

    public function deleteVisitDocum($id) {
        $item = $this->em->getVisitDocumentRepository()->find($id);
        if ($item) {
            $visit = $item->visit;
            if (file_exists($item->document)) {
                unlink($item->document);
            }
            $this->em->remove($item);
            $this->em->flush();
            return $visit->id;
        }

        return false;
    }

    public function saveNewDocum($name, $user, $path, $visitId, $type)
    {
        $doc = new \App\Model\Database\Entity\VisitDocument();
        $doc->setName($name);
        $doc->setDescription($type);
        $doc->setDocument($path);
        $visit = $this->em->getVisitRepository()->find($visitId);
        $doc->setVisit($visit);
        $doc->setUser($this->em->getUserRepository()->find($user));
        $this->em->persist($doc);
        $this->em->flush($doc);

        return $doc->id;
    }

    public function addMaterialOnVisit(
        $mOnVisId,
        $visit,
        $number,
        $description,
        $stock,
        $unit,
        $count,
        $materialId = null
    ) {
        if (is_numeric($visit)) {
            $visit = $this->em->getVisitRepository()->find($visit);
        }

        $material = null;
        if ($materialId) {
            $material = $this->em->getMaterialRepository()->find($materialId);
        }

        if ($mOnVisId && $mOnVisId != 0) {
            $mat = $this->em->getMaterialOnVisitRepository()->find($mOnVisId);
            $mat->setNumber($number);
            $mat->setDescription($description);
            $mat->setStock($stock);
            $mat->setUnit($unit);
            $mat->setCount($count);
            $mat->setVisit($visit);
            $mat->setMaterial($material);
            $this->em->flush($mat);
        } else {
            $mat = new \App\Model\Database\Entity\MaterialOnVisit();
            $mat->setNumber($number);
            $mat->setDescription($description);
            $mat->setStock($stock);
            $mat->setUnit($unit);
            $mat->setCount($count);
            $mat->setVisit($visit);
            $mat->setMaterial($material);
            $this->em->persist($mat);
            $this->em->flush($mat);
        }

        return true;
    }



}