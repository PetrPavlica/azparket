<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\OperationLog;
use App\Model\Database\Entity\OperationLogItem;
use App\Model\Database\Entity\OperationLogProblem;
use App\Model\Database\Entity\OperationLogSuggestion;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\ShiftPlan;
use App\Model\Database\Entity\WorkerInPlan;
use Nette\Application\UI\Form;
use Doctrine\Common\Collections\Criteria;

class OperationLogPresenter extends BasePresenter
{
    /** @var \App\Model\Facade\ShiftPlan @inject */
    public $shiftFac;

    /** @var integer @persistent */
    public $week;

    /** @var integer @persistent */
    public $year;

    /** @var string @persistent */
    public $type;

    /**
     * ACL name='Správa provozní deník'
     * ACL rejection='Nemáte přístup k provoznímu deníku.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);

        $this->sess->logType = $this->getParameter('type');
    }

    /**
     * ACL name='Zobrazení stránky s provozními deníky'
     */
    public function renderDefault($type)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        $this->template->hideTitleHeading = true;
        if($type) {
            $this->type = $type;
        } elseif (!$this->type) {
            $this->type = '1';
        }
        $this->template->aaType = $this->type;
        $this->template->timeNow = new \DateTime();

        if (!$this->week) {
            $this->week = date('W');
            if ($this->week > 10 && date('n') == 1) {
                $this->year = date('Y', strtotime('-1 year'));
            } else {
                $this->year = date('Y');
            }
        }

        $weekStart = new \DateTime();
        $weekStart->setISODate($this->year, $this->week);
        $weekStart = new \DateTime($weekStart->format('Y').'-'.$weekStart->format('m').'-'.$weekStart->format('d').' 00:00:00');
        $startDate = clone $weekStart;
        $endDate = clone $weekStart;

        $this->template->dateInput = $weekStart->format('Y-m-d');
        $this->template->year = $this->year;
        $this->template->week = $this->week;

        $weekStart = $weekStart->modify('-1 week');

        $this->template->previousWeek = $weekStart->format('W');
        $this->template->previousYear = $weekStart->format('Y');

        $weekStart = $weekStart->modify('+2 week');

        $this->template->nextWeek = $weekStart->format('W');
        $this->template->nextYear = $weekStart->format('Y');

        $startDate->setTime(0,0,0);
        $endDate = $endDate->modify('+6 days');
        $endDate->setTime(23,59,59);

        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
        $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', $this->type));
        $plans = $this->em->getShiftPlanRepository()->matching($criteriaStart);
        $plansA = array();
        foreach ($plans as $plan) {
            $keyString = $plan->dateString . '_' . $plan->name;
            $plansA[$keyString] = array();
            $plansA[$keyString]['id'] = $plan->id;
            $plansA[$keyString]['shift'] = $plan->shift;
        }

        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
        $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', $this->type));
        $logs = $this->em->getOperationLogRepository()->matching($criteriaStart);
        $spotsA = array();
        foreach ($logs as $log) {
            $keyString = $log->dateString . '_' . $log->name;
            $spotsA[$keyString] = array();
            $spotsA[$keyString]['id'] = $log->id;
            $spotsA[$keyString]['log'] = $log;
            $filled = 0;
            if($log->check1n1 && $log->check1n2 && $log->check2n1 && $log->check2n2 && $log->check3n1 && $log->check3n2 && $log->check4n1 && $log->check4n2 && $log->check5n1 && $log->check5n2 &&
                $log->check6n1 && $log->check6n2 && $log->check7n1 && $log->check7n2 && $log->check8n1 && $log->check9n1 && $log->check10n1 && $log->check10n2) {
                $logItems = $this->em->getOperationLogItemRepository()->findBy(['operationLog' => $log], ['interNumber' => 'ASC','id' => 'ASC']);
                $usedInterNumber = array();
                $allItemsChecked = 1;
                foreach ($logItems as $logItem) {
                    if(($logItem->interNumber == 1 || $logItem->interNumber % 10 == 0) && !isset($usedInterNumber[$logItem->interNumber])) {
                        if(!$logItem->result2) {
                            $allItemsChecked = 0;
                            break;
                        }
                        $usedInterNumber[$logItem->interNumber] = 1;
                    }
                }
                if($allItemsChecked) {
                    if($log->productionLine == 2) {
                        if($log->check10n3 && $log->check10n4) {
                            $filled = 1;
                        }
                    } else {
                        $filled = 1;
                    }
                }
            }

            if($filled) {
                $spotsA[$keyString]['name'] = 'Vyplněno';
                $spotsA[$keyString]['desc'] = 'Vyplněno';
                $spotsA[$keyString]['style'] = 'background-color: #d8fed8;'; // Green light
            } else {
                $spotsA[$keyString]['name'] = 'Nekompletní';
                $spotsA[$keyString]['desc'] = 'Nekompletní';
                $spotsA[$keyString]['style'] = 'background-color: #eff123;'; // Yellow
            }
        }

        $dateLoop = clone $startDate;
        $columnsA = array(-1);
        for($n = 0; $n < 7; $n++) {
            $columnsA[$dateLoop->format('Y-m-d')] = $dateLoop->format('j. n. Y');
            $dateLoop = $dateLoop->modify('+1 days');
        }

        $this->getWorklineProductsExternal();

        $this->template->cusDays = ['', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'];
        $this->template->cusFloors = [1 => 'ranní', 2 => 'noční'];
        $this->template->spotsA = $spotsA;
        $this->template->plansA = $plansA;
        $this->template->columnsA = $columnsA;
        $this->template->floorsA = array(-3,-1,1,-2,2);
        $this->template->placesA = array(1);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním provozní deník'
     */
    public function renderEdit($id, $planId = null)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if(!$this->isAjax()) {
            $this->template->workerSelect = $this->shiftFac->findPairsForSelect();
        }
        $this->template->workerPositionSelect = $this->em->getWorkerPositionRepository()->findBy(['id' => [1,2,3,4,5,6]]);

        $isZn = 0;
        if ($id) {
            $entity = $this->em->getOperationLogRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Deník nebyl nalezen.', 'error');
                $this->redirect('OperationLog:');
            } // :o:  close();
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;

            $lastLog = null;
            if($entity->name == 2) {
                $lastLog = $this->em->getOperationLogRepository()->findOneBy(['name' => 1, 'dateString' => $entity->dateString, 'productionLine' => $entity->productionLine]);
            } else {
                $lastDate = clone $entity->datePlan;
                $lastDate->modify('-1 day');
                $lastDateString = $lastDate->format('Y-m-d');
                $lastLog = $this->em->getOperationLogRepository()->findOneBy(['name' => 2, 'dateString' => $lastDateString, 'productionLine' => $entity->productionLine]);
            }
            $this->template->lastLog = $lastLog;
            $this->template->logItems = $this->em->getOperationLogItemRepository()->findBy(['operationLog' => $entity], ['interNumber' => 'ASC','id' => 'ASC']);
            $this->template->logProblems = $this->em->getOperationLogProblemRepository()->findBy(['operationLog' => $entity]);
            $this->template->logSuggestions = $this->em->getOperationLogSuggestionRepository()->findBy(['operationLog' => $entity]);

            $modalSpot = $this->em->getShiftPlanRepository()->findOneBy(['name' => $entity->name, 'dateString' => $entity->dateString, 'productionLine' => $entity->productionLine]);
            $tomDate = clone $modalSpot->datePlan;
            $tomDate->modify('+1 day');
            $this->template->nextDayDateString = $tomDate->format('Y-m-d');

            $workersArr = array();
            $modalSpotWorkers = $this->em->getWorkerInPlanRepository()->findBy(['plan' => $modalSpot], ['workerPosition' => 'ASC']);
            if($modalSpotWorkers && count($modalSpotWorkers)) {
                foreach ($modalSpotWorkers as $conn) {
                    if($conn->minusLog) {
                        continue;
                    }
                    $wName = $conn->worker->surname . ' ' . $conn->worker->name;
                    if(!isset($workersArr['leader1']) && $conn->workerPosition && $conn->workerPosition->id == 1) {
                        $workersArr['leader1'] = $wName;
                    } elseif(!isset($workersArr['leader2']) && $conn->workerPosition && $conn->workerPosition->id == 2) {
                        $workersArr['leader2'] = $wName;
                    } else {
                        $workersArr[$conn->worker->id] = $wName;
                    }
                }
            }

            if($entity->productionLine == 2) {
                $isZn = 1;
                $givenItems = [1=>'Řetěz 5m',2=>'Baterka 1ks',3=>'Brýle 3ks',4=>'Drátěný kartáč 2ks',5=>'Mobilní telefon 1ks',6=>'Stolek na měření včetně obsahu 1ks',
                    7=>'Bruska 1ks',8=>'Metr 1ks',9=>'Kleště 3ks',10=>'Aku-vrtačka 2ks',11=>'Digitální teploměr 1ks',12=>'Uklizené a čisté pracoviště !!'];
            } else {
                $givenItems = [1=>'Kabel 220V 1ks',2=>'El. Vrtačka 1ks',3=>'Mobilní telefon 1ks',4=>'Stolek na měření včetně obsahu 1ks',
                    5=>'Aku 2ks',6=>'Svítilna 1ks',7=>'Digitální teploměr 1ks',8=>'Uklizené a čisté pracoviště !!'];
            }
            $this->template->givenItems = $givenItems;
            $this->template->workersArr = $workersArr;
            $this->template->modalSpot = $modalSpot;
            $this->template->modalSpotWorkers = $modalSpotWorkers;
            $this->template->openTab = ($this->getParameter('openTab') !== null) ? $this->getParameter('openTab') : null;
        } elseif($planId) {
            $modalSpot = $this->em->getShiftPlanRepository()->find($planId);

            $entity = new OperationLog();
            $entity->setName($modalSpot->name);
            $entity->setNamePublic(($modalSpot->name == 1) ? 'Ranní' : 'Noční');
            $entity->setDateString($modalSpot->dateString);
            $entity->setDatePlan($modalSpot->datePlan);
            $entity->setProductionLine($modalSpot->productionLine);
            $this->em->persist($entity);
            $this->em->flush();

            $lastLog = null;
            if($entity->name == 2) {
                $lastLog = $this->em->getOperationLogRepository()->findOneBy(['name' => 1, 'dateString' => $entity->dateString, 'productionLine' => $entity->productionLine]);
            } else {
                $lastDate = clone $entity->datePlan;
                $lastDate->modify('-1 day');
                $lastDateString = $lastDate->format('Y-m-d');
                $lastLog = $this->em->getOperationLogRepository()->findOneBy(['name' => 2, 'dateString' => $lastDateString, 'productionLine' => $entity->productionLine]);
            }
            if($lastLog) {
                $entity->setTakeItem1($lastLog->giveItem1);
                $entity->setTakeItem1Note($lastLog->giveItem1Note);
                $entity->setTakeItem2($lastLog->giveItem2);
                $entity->setTakeItem2Note($lastLog->giveItem2Note);
                $entity->setTakeItem3($lastLog->giveItem3);
                $entity->setTakeItem3Note($lastLog->giveItem3Note);
                $entity->setTakeItem4($lastLog->giveItem4);
                $entity->setTakeItem4Note($lastLog->giveItem4Note);
                $entity->setTakeItem5($lastLog->giveItem5);
                $entity->setTakeItem5Note($lastLog->giveItem5Note);
                $entity->setTakeItem6($lastLog->giveItem6);
                $entity->setTakeItem6Note($lastLog->giveItem6Note);
                $entity->setTakeItem7($lastLog->giveItem7);
                $entity->setTakeItem7Note($lastLog->giveItem7Note);
                $entity->setTakeItem8($lastLog->giveItem8);
                $entity->setTakeItem8Note($lastLog->giveItem8Note);
                $entity->setTakeItem9($lastLog->giveItem9);
                $entity->setTakeItem9Note($lastLog->giveItem9Note);
                $entity->setTakeItem10($lastLog->giveItem10);
                $entity->setTakeItem10Note($lastLog->giveItem10Note);
                $entity->setTakeItem11($lastLog->giveItem11);
                $entity->setTakeItem11Note($lastLog->giveItem11Note);
                $entity->setTakeItem12($lastLog->giveItem12);
                $entity->setTakeItem12Note($lastLog->giveItem12Note);
                $this->em->flush();
            }

            $interNumberCounter = 0;
            $lastRod = 0;
            if(file_exists("dfiles/workline/".$modalSpot->dateString.".txt")) {
                $worklines = unserialize(file_get_contents("dfiles/workline/".$modalSpot->dateString.".txt"));
                usort($worklines, function($a, $b) {
                    $aTime = new \DateTime($a['date']);
                    $bTime = new \DateTime($b['date']);
                    return $aTime->getTimestamp() - $bTime->getTimestamp();
                });

                foreach ($worklines as $workline) {
                    if($workline['shift'] == $modalSpot->shift) {
                        if($modalSpot->productionLine == 2) {
                            if($workline['kind'] != 'G' && $workline['kind'] != 'N') {
                                continue;
                            }
                        } else {
                            if($workline['kind'] == 'G' || $workline['kind'] == 'N') {
                                continue;
                            }
                        }

                        $testTime = new \DateTime($workline['date']);
                        if($modalSpot->name == 1 || intval($testTime->format('H')) > 13) {
                            $logItem = $this->em->getOperationLogItemRepository()->findOneBy(['externalId' => $workline['id']]);
                            if(!$logItem) {
                                $logItem = new OperationLogItem();
                            }

                            if($lastRod != $workline['rod']) {
                                $interNumberCounter++;
                            }

                            $logItem->setExternalId($workline['id']);
                            $logItem->setInterNumber($interNumberCounter);
                            $logItem->setRod($workline['rod']);
                            $logItem->setCode($workline['productNumber']);
                            $logItem->setTyp($workline['productKind']);
                            $logItem->setCounts($workline['countOk'] + $workline['countBad']);
                            $logItem->setCountsResult2($workline['countBad']);
                            if(isset($workline['defect']) && $workline['defect'] && !$logItem->note) {
                                $logItem->setNote($workline['defect']);
                            }
                            $logItem->setOperationLog($entity);

                            $this->em->persist($logItem);

                            $lastRod = $workline['rod'];
                        }
                    }
                }
            }
            $tomDate = clone $modalSpot->datePlan;
            $tomDate->modify('+1 day');
            $tomDateString = $tomDate->format('Y-m-d');
            if($modalSpot->name == 2 && file_exists("dfiles/workline/".$tomDateString.".txt")) {
                $worklines = unserialize(file_get_contents("dfiles/workline/".$tomDateString.".txt"));
                usort($worklines, function($a, $b) {
                    $aTime = new \DateTime($a['date']);
                    $bTime = new \DateTime($b['date']);
                    return $aTime->getTimestamp() - $bTime->getTimestamp();
                });

                foreach ($worklines as $workline) {
                    if($workline['shift'] == $modalSpot->shift) {
                        if($modalSpot->productionLine == 2) {
                            if($workline['kind'] != 'G' && $workline['kind'] != 'N') {
                                continue;
                            }
                        } else {
                            if($workline['kind'] == 'G' || $workline['kind'] == 'N') {
                                continue;
                            }
                        }

                        $testTime = new \DateTime($workline['date']);
                        if(intval($testTime->format('H')) < 13) {
                            $logItem = $this->em->getOperationLogItemRepository()->findOneBy(['externalId' => $workline['id']]);
                            if(!$logItem) {
                                $logItem = new OperationLogItem();
                            }

                            if($lastRod != $workline['rod']) {
                                $interNumberCounter++;
                            }

                            $logItem->setExternalId($workline['id']);
                            $logItem->setInterNumber($interNumberCounter);
                            $logItem->setRod($workline['rod']);
                            $logItem->setCode($workline['productNumber']);
                            $logItem->setTyp($workline['productKind']);
                            $logItem->setCounts($workline['countOk'] + $workline['countBad']);
                            $logItem->setCountsResult2($workline['countBad']);
                            if(isset($workline['defect']) && $workline['defect'] && !$logItem->note) {
                                $logItem->setNote($workline['defect']);
                            }
                            $logItem->setOperationLog($entity);

                            $this->em->persist($logItem);

                            $lastRod = $workline['rod'];
                        }
                    }
                }
            }
            $this->em->flush();

            $this->redirect('OperationLog:edit', ['id' => $entity->id]);
        } else {
            $this->template->openTab = null;
            $this->redirect('OperationLog:default');
        }

        $this->template->isZn = $isZn;
        $this->template->dayTrans = [1=>'Pondělí', 2=>'Úterý', 3=>'Středa', 4=>'Čtvrtek', 5=>'Pátek', 6=>'Sobota', 7=>'Neděle'];
        $this->template->nameTrans = [1=>'Ranní', 2=>'Noční'];
        $this->template->lineTrans = [1=>'KTL', 2=>'Galvanika'];
        $this->template->endRunTrans = [1 => 'Plynulý chod', 2 => 'Mimo provoz do 24h', 3 => 'Mimo provoz nad 24h'];
    }

    /**
     * ACL name='Tabulka s přehledem provozní deník'
     */
    public function createComponentTable()
    {
        $findBy = [];
        if (isset($this->sess->logType)) {
            $findBy = ['productionLine' => $this->sess->logType];
        }

        $grid = $this->gridGen->generateGridByAnnotation(OperationLog::class, get_class(), __FUNCTION__, 'default', $findBy);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'OperationLog:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'OperationLog:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        //$this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit provozní deník'
     */
    public function createComponentForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(OperationLog::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit deník', 'success'], ['Nepodařilo se uložit deník!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            $isNew = 0;
            $entity = null;
            if($values2['id']) {
                $entity = $that->em->getApproveRepository()->find($values2['id']);
            } else {
                $isNew = 1;
            }

            $values['endRunDate'] = date_create_from_format('j. n. Y H:i', $values['endRunDate']);
            if(!$values['endRunDate']) { $values['endRunDate'] = null; }
            $values['releaseStartDate'] = date_create_from_format('j. n. Y H:i', $values['releaseStartDate']);
            if(!$values['releaseStartDate']) { $values['releaseStartDate'] = null; }
            $values['releaseEndDate'] = date_create_from_format('j. n. Y H:i', $values['releaseEndDate']);
            if(!$values['releaseEndDate']) { $values['releaseEndDate'] = null; }
            $values['releaseDate'] = date_create_from_format('j. n. Y H:i', $values['releaseDate']);
            if(!$values['releaseDate']) { $values['releaseDate'] = null; }

            //$entity = $that->formGenerator->processForm($form, $values, true);

            if (!$entity) {
                return;
            }

            if (isset($values2['sendEnd'])) { // Ukončit směnu
                $this->redirect('logout!');
            } elseif (isset($values2['sendBack'])) { // Uložit a zpět
                $this->redirect('OperationLog:default', ['type' => $entity->productionLine]);
            } else if (isset($values2['send'])) { //Uložit
                $this->redirect('OperationLog:edit', ['id' => $entity->id]);
            } else if (isset($values2['sendNew'])) {
                $this->redirect('OperationLog:edit');
            } else {
                $this->redirect('OperationLog:edit', ['id' => $entity->id]);
            }

        };

        return $form;
    }

    /**
     * ACL name='Formulář pro ukončení směny'
     */
    public function createComponentEndingModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(OperationLog::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se ukončit směnu', 'success'], ['Nepodařilo se ukončit směnu!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();
            $entity = $this->em->getOperationLogRepository()->find($values2['id']);
            $entity->setGiveItemsCheck(1);
            $entity->setGiveItemsCheckChanged(new \DateTime());
            $this->em->flush();
            if(isset($values2['logout'])) {
                $this->redirect('logout!');
            }
        };

        return $form;
    }

    public function handleCheckLogEnding() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getOperationLogRepository()->find($values['item']);
            $arr = $this->ed->get($entity);
            $this['endingModalForm']->setDefaults($arr);
            $this->template->modalEnding = $entity;
        }
        $this->redrawControl('endingModal');
    }

    /**
     * ACL name='Formulář pro přebrání směny'
     */
    public function createComponentStartingModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(OperationLog::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se přebrat směnu', 'success'], ['Nepodařilo se ukončit směnu!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();
            $entity = $this->em->getOperationLogRepository()->find($values2['id']);
            $entity->setTakeItemsCheck(1);
            $entity->setTakeItemsCheckChanged(new \DateTime());
            $this->em->flush();
            $that->redirect('OperationLog:edit', ['id' => $entity->id]);
        };

        return $form;
    }

    public function handleCheckLogStarting() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getOperationLogRepository()->find($values['item']);
            $arr = $this->ed->get($entity);
            $this['startingModalForm']->setDefaults($arr);
            $this->template->modalStarting = $entity;
        }
        $this->redrawControl('startingModal');
    }

    /**
     * ACL name='Formulář pro uvolnění linky'
     */
    public function createComponentReleaseModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(OperationLog::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uvolnit linku', 'success'], ['Nepodařilo se uvolnit linku!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $that->redrawControl('logReleaseTable');
            return;
        };

        return $form;
    }

    public function handleCheckLogRelease() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getOperationLogRepository()->find($values['item']);
            $arr = $this->ed->get($entity);
            $this['releaseModalForm']->setDefaults($arr);
            $this->template->modalEnding = $entity;
        }
        $this->redrawControl('releaseModal');
    }


    /**
     * ACL name='Formulář pro přidání/edit záznamů'
     */
    public function createComponentPartModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(OperationLogItem::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit záznam', 'success'], ['Nepodařilo se uložit záznam!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            $oldEntity = NULL;
            if($values2['id']) {
                $oldEntity = $that->em->getOperationLogItemRepository()->find($values2['id']);
            }

            $entity = $that->formGenerator->processForm($form, $values, true);
            if($entity) {
                $entity->setOperationLog($that->em->getOperationLogRepository()->find($values2['operationLog']));
                $that->em->flush();
            }

            if ($that->isAjax()) {
                $that->redrawControl('partTable');
            } else {
                $that->redirect('OperationLog:edit',  ['id' => $values2['operationLog']]);
            }
        };

        return $form;
    }

    public function handleCheckLogItem() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getOperationLogItemRepository()->find($values['item']);
            $arr = $this->ed->get($entity);
            $this['partModalForm']->setDefaults($arr);
            $this->template->modalPart = $entity;
        }
        $this->redrawControl('partModal');
    }

    public function handleCheckNormalChange() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getOperationLogRepository()->find($values['item']);
            if($values['input']) {
                $forSet = 'set' . ucfirst($values['input']);
                if($values['input'] == 'endRunDate' || $values['input'] == 'releaseStartDate' || $values['input'] == 'releaseEndDate' || $values['input'] == 'releaseDate') {
                    $values['val'] = date_create_from_format('j. n. Y H:i', $values['val']);
                    if(!$values['val']) {
                        $values['val'] = null;
                    }
                }
                if($values['input'] == 'endRun') {
                    if(!$values['val']) {
                        $values['val'] = null;
                    }
                }
                $entity->$forSet($values['val']);

                $forChangedCheck = $values['input'] . 'Changed';
                if(property_exists($entity, $forChangedCheck)) {
                    $forChangedSet = $forSet . 'Changed';
                    $entity->$forChangedSet(new \DateTime());
                }
            }
            $this->em->flush();
        }
    }

    public function handleResult1Change() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getOperationLogItemRepository()->find($values['item']);
            if($values['val']) {
                $entity->setResult1($values['val']);
            } else {
                $entity->setResult1(NULL);
            }
            $entity->setResult1Changed(new \DateTime());

            $this->em->flush();
        }
    }

    public function handleResult2Change() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getOperationLogItemRepository()->find($values['item']);
            if($values['val']) {
                $entity->setResult2($values['val']);
            } else {
                $entity->setResult2(NULL);
            }
            $entity->setResult2Changed(new \DateTime());

            $this->em->flush();
        }
    }

    public function handleCountsResult2Change() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getOperationLogItemRepository()->find($values['item']);
            if($values['val']) {
                $entity->setCountsResult2($values['val']);
            } else {
                $entity->setCountsResult2(NULL);
            }
            $entity->setCountsResult2Changed(new \DateTime());

            $this->em->flush();
        }
    }

    public function handleNoteChange() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getOperationLogItemRepository()->find($values['item']);
            if($values['val']) {
                $entity->setNote($values['val']);
            } else {
                $entity->setNote(NULL);
            }
            $entity->setNoteChanged(new \DateTime());

            $this->em->flush();
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit závad'
     */
    public function createComponentProblemModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(OperationLogProblem::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit závadu', 'success'], ['Nepodařilo se uložit závadu!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            $oldEntity = NULL;
            if($values2['id']) {
                $oldEntity = $that->em->getOperationLogProblemRepository()->find($values2['id']);
            }

            $entity = $that->formGenerator->processForm($form, $values, true);
            if($entity) {
                $entity->setOperationLog($that->em->getOperationLogRepository()->find($values2['operationLog']));
                $that->em->flush();
            }

            if ($that->isAjax()) {
                $that->redrawControl('problemTable');
            } else {
                $that->redirect('OperationLog:edit',  ['id' => $values2['operationLog']]);
            }
        };

        return $form;
    }

    public function handleCheckLogProblem() {
        $values = $this->request->getPost();
        if($values['problem']) {
            $entity = $this->em->getOperationLogProblemRepository()->find($values['problem']);
            $arr = $this->ed->get($entity);
            $this['problemModalForm']->setDefaults($arr);
            $this->template->modalProblem = $entity;
        }
        $this->redrawControl('problemModal');
    }

    public function handleRemoveLogProblem() {
        $values = $this->request->getPost();
        $entity = $this->em->getOperationLogProblemRepository()->find($values['problem']);
        if($entity) {
            $this->em->remove($entity);
            $this->em->flush();

            $this->redrawControl('problemTable');
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit připomínek'
     */
    public function createComponentSuggestionModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(OperationLogSuggestion::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit připomínku', 'success'], ['Nepodařilo se uložit připomínku!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            $oldEntity = NULL;
            if($values2['id']) {
                $oldEntity = $that->em->getOperationLogSuggestionRepository()->find($values2['id']);
            }

            $entity = $that->formGenerator->processForm($form, $values, true);
            if($entity) {
                $entity->setOperationLog($that->em->getOperationLogRepository()->find($values2['operationLog']));
                $that->em->flush();
            }

            if ($that->isAjax()) {
                $that->redrawControl('suggestionTable');
            } else {
                $that->redirect('OperationLog:edit',  ['id' => $values2['operationLog']]);
            }
        };

        return $form;
    }

    public function handleCheckLogSuggestion() {
        $values = $this->request->getPost();
        if($values['suggestion']) {
            $entity = $this->em->getOperationLogSuggestionRepository()->find($values['suggestion']);
            $arr = $this->ed->get($entity);
            $this['suggestionModalForm']->setDefaults($arr);
            $this->template->modalSuggestion = $entity;
        }
        $this->redrawControl('suggestionModal');
    }

    public function handleRemoveLogSuggestion() {
        $values = $this->request->getPost();
        $entity = $this->em->getOperationLogSuggestionRepository()->find($values['suggestion']);
        if($entity) {
            $this->em->remove($entity);
            $this->em->flush();

            $this->redrawControl('suggestionTable');
        }
    }

    public function handleLoadLogItems() {
        $this->getWorklineProductsExternal(1);

        $values = $this->request->getPost();
        if($values['log']) {
            $entity = $this->em->getOperationLogRepository()->find($values['log']);
            $modalSpot = $this->em->getShiftPlanRepository()->findOneBy(['name' => $entity->name, 'dateString' => $entity->dateString, 'productionLine' => $entity->productionLine]);

            $interNumberCounter = 0;
            $lastRod = 0;
            if(file_exists("dfiles/workline/".$modalSpot->dateString.".txt")) {
                $worklines = unserialize(file_get_contents("dfiles/workline/".$modalSpot->dateString.".txt"));

                usort($worklines, function($a, $b) {
                    $aTime = new \DateTime($a['date']);
                    $bTime = new \DateTime($b['date']);
                    if($aTime->getTimestamp() == $bTime->getTimestamp()) {
                        return $a['id'] - $b['id'];
                    } else {
                        return $aTime->getTimestamp() - $bTime->getTimestamp();
                    }
                });

                foreach ($worklines as $workline) {
                    if($workline['shift'] == $modalSpot->shift) {
                        if($modalSpot->productionLine == 2) {
                            if($workline['kind'] != 'G' && $workline['kind'] != 'N') {
                                continue;
                            }
                        } else {
                            if($workline['kind'] == 'G' || $workline['kind'] == 'N') {
                                continue;
                            }
                        }

                        $testTime = new \DateTime($workline['date']);
                        if($modalSpot->name == 1 || intval($testTime->format('H')) > 13) {
                            $logItem = $this->em->getOperationLogItemRepository()->findOneBy(['externalId' => $workline['id']]);
                            if(!$logItem) {
                                $logItem = new OperationLogItem();
                            }

                            if($lastRod != $workline['rod']) {
                                $interNumberCounter++;
                            }

                            $logItem->setExternalId($workline['id']);
                            $logItem->setInterNumber($interNumberCounter);
                            $logItem->setRod($workline['rod']);
                            $logItem->setCode($workline['productNumber']);
                            $logItem->setTyp($workline['productKind']);
                            $logItem->setCounts($workline['countOk'] + $workline['countBad']);
                            $logItem->setCountsResult2($workline['countBad']);
                            if(isset($workline['defect']) && $workline['defect'] && !$logItem->note) {
                                $logItem->setNote($workline['defect']);
                            }
                            $logItem->setOperationLog($entity);

                            $this->em->persist($logItem);

                            $lastRod = $workline['rod'];
                        }
                    }
                }
            }
            $tomDate = clone $modalSpot->datePlan;
            $tomDate->modify('+1 day');
            $tomDateString = $tomDate->format('Y-m-d');
            if($modalSpot->name == 2 && file_exists("dfiles/workline/".$tomDateString.".txt")) {
                $worklines = unserialize(file_get_contents("dfiles/workline/".$tomDateString.".txt"));
                usort($worklines, function($a, $b) {
                    $aTime = new \DateTime($a['date']);
                    $bTime = new \DateTime($b['date']);
                    if($aTime->getTimestamp() == $bTime->getTimestamp()) {
                        return $a['id'] - $b['id'];
                    } else {
                        return $aTime->getTimestamp() - $bTime->getTimestamp();
                    }
                });

                foreach ($worklines as $workline) {
                    if($workline['shift'] == $modalSpot->shift) {
                        if($modalSpot->productionLine == 2) {
                            if($workline['kind'] != 'G' && $workline['kind'] != 'N') {
                                continue;
                            }
                        } else {
                            if($workline['kind'] == 'G' || $workline['kind'] == 'N') {
                                continue;
                            }
                        }

                        $testTime = new \DateTime($workline['date']);
                        if(intval($testTime->format('H')) < 13) {
                            $logItem = $this->em->getOperationLogItemRepository()->findOneBy(['externalId' => $workline['id']]);
                            if(!$logItem) {
                                $logItem = new OperationLogItem();
                            }

                            if($lastRod != $workline['rod']) {
                                $interNumberCounter++;
                            }

                            $logItem->setExternalId($workline['id']);
                            $logItem->setInterNumber($interNumberCounter);
                            $logItem->setRod($workline['rod']);
                            $logItem->setCode($workline['productNumber']);
                            $logItem->setTyp($workline['productKind']);
                            $logItem->setCounts($workline['countOk'] + $workline['countBad']);
                            $logItem->setCountsResult2($workline['countBad']);
                            if(isset($workline['defect']) && $workline['defect'] && !$logItem->note) {
                                $logItem->setNote($workline['defect']);
                            }
                            $logItem->setOperationLog($entity);

                            $this->em->persist($logItem);

                            $lastRod = $workline['rod'];
                        }
                    }
                }
            }
            $this->em->flush();
        }

        $this->redrawControl('logItemsTable');
    }

    public function createComponentPlanModalForm()
    {
        $that = $this;

        $form = new Form();
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();
            if(isset($values2['spot']) && isset($values2['worker'])) {
                $worker = $this->em->getWorkerRepository()->find($values2['worker']);
                $workerPosition = NULL;
                if($values2['workerPosition']) {
                    $workerPosition = $this->em->getWorkerPositionRepository()->find($values2['workerPosition']);
                }
                $plan = $this->em->getShiftPlanRepository()->find($values2['spot']);

                if($worker && $plan) {
                    $workInPlan = new WorkerInPlan();
                    $workInPlan->setWorker($worker);
                    $workInPlan->setPlan($plan);
                    $workInPlan->setWorkerPosition($workerPosition ? $workerPosition : $worker->workerPosition);
                    $workInPlan->setManual(1);
                    $workInPlan->setMinusLog(0);
                    $workInPlan->setPlusLog(1);
                    $workInPlan->setHours('12');
                    $this->em->persist($workInPlan);
                    $this->em->flush();
                }
            }

            $that->redirect('this');
        };

        return $form;
    }

    public function handleCheckSpotPlan() {
        $values = $this->request->getPost();

        $modalSpot = $this->em->getShiftPlanRepository()->find($values['spot']);
        $this->template->workerSelect = $this->shiftFac->findPairsForSelect($modalSpot->dateString, $modalSpot->name, $modalSpot->productionLine, 1);
        $this->redrawControl('planModal');
    }

    public function handleMinusWorkerSpot() {
        $values = $this->request->getPost();
        $entity = $this->em->getWorkerInPlanRepository()->find($values['plan']);
        $entity->setMinusLog(1);
        $this->em->flush();
        $this->redirect('this');
    }

    public function handlePlusWorkerSpot() {
        $values = $this->request->getPost();
        $entity = $this->em->getWorkerInPlanRepository()->find($values['plan']);
        $entity->setMinusLog(0);
        $this->em->flush();
        $this->redirect('this');
    }

    public function handleRemoveWorkerSpot() {
        $values = $this->request->getPost();
        $entity = $this->em->getWorkerInPlanRepository()->find($values['plan']);
        if($entity) {
            $this->em->remove($entity);
            $this->em->flush();

            $this->redirect('this');
        }
    }

    public function handleHoursChange() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getWorkerInPlanRepository()->find($values['item']);
            $entity->setHours($values['val']);
            $this->em->flush();
        }
    }

    public function getWorklineProductsExternal($force = 0) {
        if($force || !(file_exists("dfiles/workline/2022-01-03.txt") && date('Y-m-d') == date('Y-m-d', filemtime("dfiles/workline/2022-01-03.txt")))) {
            $worklineDays = array();
            $countResults = 1;
            $failSafePage = 1;
            while($countResults > 0 && $failSafePage < 300) {
                $curl = curl_init();
                $qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1000));
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "http://192.168.1.112:3000/v1/workline-product?" . $qParams,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "Content-Type: multipart/form-data",
                        "cache-control: no-cache"
                    ),
                )); //  ..
                $response = curl_exec($curl);
                $data = json_decode($response, true);

                foreach ($data as $prodArray) {
                    $dateString = substr($prodArray['date'], 0, 10);
                    if(!isset($worklineDays[$dateString])) {
                        $worklineDays[$dateString] = array();
                    }

                    if(count($worklineDays) > 5) {
                        foreach ($worklineDays as $tDateString => $tWorkline) {
                            file_put_contents("dfiles/workline/".$tDateString.".txt", serialize($tWorkline));
                            unset($worklineDays[$tDateString]);
                            break;
                        }
                    }

                    $worklineDays[$dateString][] = $prodArray;
                }

                $countResults = count($data);
                $failSafePage++;
            }

            foreach ($worklineDays as $tDateString => $tWorkline) {
                file_put_contents("dfiles/workline/".$tDateString.".txt", serialize($tWorkline));
            }

        }

        return array();
    }

}