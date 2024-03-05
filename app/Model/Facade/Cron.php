<?php


namespace App\Model\Facade;

use App\Model\Database\Entity\SkillInWorkerTender;
use App\Model\Database\Entity\WorkerInWorkerTender;
use App\Model\Database\Entity\WorkerTender;
use App\Model\Database\EntityManager;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Nette\Database\Connection;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use App\Model\Facade\BaseFront as FrontFac;
use App\Model\Facade\Offer as OfferFac;

class Cron
{
    /** @var EntityManager */
    private EntityManager $em;

    /** @var Connection */
    private $dbo;
    
    /** @var FrontFac */
    public $frontFac;
    
    /** @var OfferFac */
    public $offerFac;

    /**
     * Cron constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, FrontFac $frontFac, OfferFac $offerFac)
    {
        $this->em = $em;
        $this->frontFac = $frontFac;
    }

    public function generateWorkerTenderRegularlyNextYear($year = null) {
        if (!$year) {
            $year = date('Y');
        }
        $actualDate = new \DateTime();

        $rsm = new ResultSetMappingBuilder(
            $this->em, ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT
        );
        $rsm->addRootEntityFromClassMetadata(WorkerTender::class, 'wt');
        $query = $this->em->createNativeQuery("
                SELECT " . $rsm->generateSelectClause() . "
                FROM worker_tender wt
                WHERE wt.tender_type = 'Pravidelné'
                AND wt.tender_date BETWEEN '".$year."-01-01 00:00:00' AND '".$year."-12-31 23:59:59'
                ", $rsm);
        $workerTenders = $query->getResult();

        foreach ($workerTenders as $workerTender) {
            $wt = clone $workerTender;
            $tenderDate = $wt->tenderDate;
            $tenderDate->modify('+1 year');
            if ($tenderDate->format('N') == 6) {
                //sobota
                $tenderDate->modify('+2 day');
            } elseif ($tenderDate->format('N') == 7) {
                //neděle
                $tenderDate->modify('+1 day');
            }
            $wt->id = null;
            $wt->createdAt = $actualDate;
            $wt->updatedAt = $actualDate;
            $wt->tenderDate = $tenderDate;
            $this->em->persist($wt);
            $this->em->flush();

            //přiřadit dovednostni
            if ($workerTender->skills) {
                foreach ($workerTender->skills as $siwt) {
                    $skillInWorkerTender = new SkillInWorkerTender();
                    $skillInWorkerTender->setSkill($siwt->skill);
                    $skillInWorkerTender->setTender($wt);
                    $this->em->persist($skillInWorkerTender);
                    $this->em->flush();
                }
            }

            //přiřadit zaměstnance
            if ($wt->skills) {
                foreach ($wt->skills as $siwt) {
                    if ($siwt->skill->workers) {
                        foreach ($siwt->skill->workers as $siworker) {
                            $wot = $this->em->getWorkerInWorkerTenderRepository()->findBy(['worker' => $siworker->worker, 'tender' => $wt]);
                            if (!$wot) {
                                $workerInTender = new WorkerInWorkerTender();
                                $workerInTender->setWorker($siworker->worker);
                                $workerInTender->setTender($wt);
                                $this->em->persist($workerInTender);
                                $this->em->flush();
                            }
                        }
                    }
                }
            }
        }
    }

    public function checkSendAutoOffer() {
        $success = 1;
        $offers = $this->em->getOfferRepository()->createQueryBuilder('o')
            ->where('o.sendDate IS NULL AND o.autoSend = 1 AND o.plannedSendDate >= :now AND o.customer IS NOT NULL')
            ->setParameters(['now' => new \DateTime()])
            ->getQuery()->getResult();
        
        $locale = 'cs'; // in case of multilang. offer, add lang to entity props & check before send

        $webSettings = $this->frontFac->getWebSettings($locale);
        
        foreach ($offers as $o) {
            if (!$this->offerFac->prepareAndSendOffer($o, $o->customer->email, '', $webSettings['default_offer_email_subject'], $webSettings['default_offer_email'], $locale)) {
                $success = 0;
            }
        }
        return $success;
    }
}
