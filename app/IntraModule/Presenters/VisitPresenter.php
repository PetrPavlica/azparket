<?php

namespace App\IntraModule\Presenters;

use App\Model\ACLForm;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Utils\SQLHelper;
use App\Model\Facade\Visit;

class VisitPresenter extends BasePresenter
{
    /** @var Visit @inject */
    public $visFac;

    /** @var SQLHelper */
    private $SQLHelper;

    /**
     * ACL name='Správa stavů výjezdů'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
        $this->SQLHelper = new SQLHelper();
    }

    public function renderDefault($slug = null)
    {
        $qb = $this[ 'table' ]->getDataSource();
        if ($slug) {
            $qb->filterOne(['state' => $slug]);
            $this->template->visitState = $this->em->getVisitStateRepository()->find($slug);
        }
        $this[ 'table' ]->setDataSource($qb);
    }

    public function renderEdit($id, $visitProcessId = null, $day = null, $start = null, $end = null, $ymd = null)
    {
        /* dnešní služba */
        $hourTime = date("H:i:s");
        if($hourTime >= '07:00:01' && $hourTime <= '23:59:59'){
            $today = date('Y-m-d', strtotime('now'));
        }else {
            $today = date('Y-m-d', strtotime('yesterday'));
        }
        $this->template->todayService = $this->em->getServiceRepository()->findOneBy(['dateService' => new \DateTime($today)]);

        /* dnešní absence */
        $todayAbsence = [];
        //vytažení absence - celý den
        $tAbsence = $this->em->createQueryBuilder()
            ->select("u")
            ->from(\App\Model\Database\Entity\Absence::class, "a")
            ->join(\App\Model\Database\Entity\User::class, "u", "WITH", "a.user = u")
            ->where("a.state = 4")
            ->andWhere("a.wholeDay = 1")
            ->andWhere("(a.dateStart <= '" . $today . "' AND a.dateEnd >= '" . $today . "') OR (a.dateStart BETWEEN '" . $today . " 00:00:00' AND '" . $today . " 23:59:59')")
            ->getQuery()
            ->getResult();
        foreach ($tAbsence as $a){
            if (!isset($a->workersUsr[0])) {
                continue;
            }
            $todayAbsence[] = $a->workersUsr[0]->id;
        }
        //vytažení absence - podle času
        $todayDate = new \DateTime();
        $tAbsence = $this->em->createQueryBuilder()
            ->select("a")
            ->from(\App\Model\Database\Entity\Absence::class, "a")
            ->join(\App\Model\Database\Entity\User::class, "u", "WITH", "a.user = u")
            ->where("a.state = 4")
            ->andWhere("a.wholeDay = 0")
            ->andWhere("(a.dateStart <= '" . $today . "' AND a.dateEnd >= '" . $today . "') OR (a.dateStart BETWEEN '" . $today . " 00:00:00' AND '" . $today . " 23:59:59')")
            ->getQuery()
            ->getResult();
        foreach ($tAbsence as $a){
            if (!$a->timeRange) {
                continue;
            }
            if (!$a->user || !$a->user->workersUsr || !isset($a->user->workersUsr[0])) {
                continue;
            }
            $dateStartAbsence = $dateEndAbsence = $tmpAbsenceTime = null;
            $tmpAbsenceTime = str_replace(' ', '', $a->timeRange);
            $tmpAbsenceTime = explode('-', $tmpAbsenceTime);
            if (isset($tmpAbsenceTime[0]) && $tmpAbsenceTime[0] && isset($tmpAbsenceTime[1]) && isset($tmpAbsenceTime[1])) {
                $dtStart[0] = $dtStart[1] = $dtEnd[0] = $dtEnd[1] = 0;
                $dtStart = explode(':', $tmpAbsenceTime[0]);
                $dateStartAbsence = clone $todayDate;
                $dateStartAbsence->setTime($dtStart[0], $dtStart[1]);

                $dtEnd = explode(':', $tmpAbsenceTime[1]);
                $dateEndAbsence = clone $todayDate;
                $dateEndAbsence->setTime($dtEnd[0], $dtEnd[1]);

                if ($dateStartAbsence < $todayDate && $todayDate < $dateEndAbsence) {
                    $todayAbsence[] = $a->user->workersUsr[0]->id;
                }
            }
        }
        $this->template->todayAbsence = $todayAbsence;

        if(!$this->isAjax()) {
            if ($this['form']['customer']->value) {
                $this['form']->setAutocmp('custome', $this->em->getCustomerRepository()->find($this['form']['customer']->value)->name);
            }
            if ($this['form']['traffic']->value) {
                $this['form']->setAutocmp('traffic', $this->em->getTrafficRepository()->find($this['form']['traffic']->value)->name);
            }
            if ($this['form']['customerOrdered']->value) {
                $this['form']->setAutocmp('customerOrdered', $this->em->getCustomerOrderedRepository()->find($this['form']['customerOrdered']->value)->name);
            }
            if ($this['form']['visitProcess']->value) {
                $this['form']->setAutocmp('visitProcess', $this->em->getVisitProcessRepository()->find($this['form']['visitProcess']->value)->name);
            }
        }

        if (!$id) {
            $dateCreated = new \DateTime();
            $this[ 'form' ]->setDefaults(['dateStart' => $dateCreated->format('j. n. Y')]);
            $timeCreated = $dateCreated->format('H:');
            if ($dateCreated->format('i') >= '30') {
                $timeCreated .= '30';
            } else {
                $timeCreated .= '00';
            }
            $this[ 'form' ]->setDefaults(['onceTimes' => $timeCreated]);
        }

        if ($id) {
            $entity = $this->em->getVisitRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('Visit:');
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);

            if ($entity->customer)
                $this['form']->setAutocmp('customer', $entity->customer->name);

            if ($entity->customerOrdered)
                $this['form']->setAutocmp('customerOrdered', $entity->customerOrdered->name);

            if ($entity->traffic)
                $this['form']->setAutocmp('traffic', $entity->traffic->name);

            if ($entity->visitProcess)
                $this['form']->setAutocmp('visitProcess', $entity->visitProcess->name);

            if ($entity->refrigerant)
                $this['form']->setAutocmp('refrigerant', $entity->refrigerant->name);

            $this->template->material = $this->em->getMaterialOnVisitRepository()->findBy(['visit' => $id]);
            $this->template->needBuyMat = $this->em->getMaterialNeedBuyRepository()->findBy(['visit' => $id]);
            $this->template->documents = $this->em->getVisitDocumentRepository()->findBy(['visit' => $id]);
        } elseif ($visitProcessId) {
            $this->template->vProcessId = $visitProcessId;
            $vProcess = $this->em->getVisitProcessRepository()->find($visitProcessId);
            $cVis = $this->em->getVisitRepository()->findBy(['visitProcess' => $vProcess]);
            $cVisit = (count($cVis) + 1) * 10;
            if($cVisit < 100){
                $cVisit = '0'.$cVisit;
            }
            $vDef = ['visitProcess' => $visitProcessId, 'orderId' => $vProcess->orderId, 'orderId2' => $vProcess->orderId.'/'.$cVisit, 'name' => $vProcess->name];
            $this[ 'form' ]->setAutocmp('visitProcess', $vProcess->name);
            if($vProcess->customer) {
                $vDef['customer'] = $vProcess->customer->id;
                $this[ 'form' ]->setAutocmp('customer', $vProcess->customer->name);
            }
            if($vProcess->customerOrdered) {
                $vDef['customerOrdered'] = $vProcess->customerOrdered->id;
                $this[ 'form' ]->setAutocmp('customerOrdered', $vProcess->customerOrdered->name);
            }
            if($vProcess->traffic) {
                $vDef['traffic'] = $vProcess->traffic->id;
                $this[ 'form' ]->setAutocmp('traffic', $vProcess->traffic->name);
                $vDef['worker'] = [];
                $workerSubstitute = [];
                foreach ($vProcess->traffic->workerSubstitute as $item) {
                    if(!$item->worker){
                        continue;
                    }
                    if (!$item->worker->active) {
                        continue;
                    }
                    $workerSubstitute[] = $item->worker->id;
                }
                foreach ($vProcess->traffic->worker as $item) {
                    if(!$item->worker){
                        continue;
                    }
                    if (!$item->worker->active) {
                        continue;
                    }
                    if(in_array($item->worker->id, $todayAbsence)){
                        $vDef['worker'] = $workerSubstitute;
                        break;
                    }
                    $vDef['worker'][] = $item->worker->id;
                }
            }
            $this[ 'form' ]->setDefaults($vDef);
        } elseif ($day) {
            $dateStart = new \DateTime($ymd);
            $se = $start.'-'.$end;
            $defArr = ['dateStart' => $dateStart->format('j. n. Y'), 'onceTimes' => $start];
            $this[ 'form' ]->setDefaults($defArr);
        }

        $this->template->openTab = ($this->getParameter('openTab') !== null) ? $this->getParameter('openTab') : null;

        if(isset($this->sess->turnoverExportVisitsDoc)){
            $this->template->canDownloadZip = true;
        }
    }

    /**
     * ACL name='Odeslání dokumentů výjezdů emailem'
     */
    public function renderSendDocs($id, $docIds = [])
    {
        if (!$id) {
            $this->flashMessage('Výjezd se nepodařilo najít!', 'warning');
            $this->redirect('Visit:default');
        }
        $visit = $this->em->getVisitRepository()->find($id);
        if (!$visit) {
            $this->flashMessage('Výjezd se nepodařilo najít!', 'warning');
            $this->redirect('Visit:default');
        }
        if (!is_array($docIds) || !count($docIds)) {
            $this->flashMessage('Dokumenty se nepodařilo najít!', 'warning');
            $this->redirect('Visit:edit', ['id' => $id, 'openTab' => '#docs']);
        }
        $dId = [];
        foreach ($docIds as $docId) {
            $doc = $this->em->getVisitDocumentRepository()->find($docId);
            $dId[$docId] = $docId;
        }
        $this[ 'sendDocsForm' ]->getComponent('id')->value = $id;
        $this[ 'sendDocsForm' ]->getComponent('docIds')->items = $dId;
        $this[ 'sendDocsForm' ]->getComponent('docIds')->value = $docIds;
        $this->template->visit = $visit;
        $this->template->docIds = $docIds;
    }

    /**
     * ACL name='Odeslání dokumentů výjezdů emailem k fakturaci'
     */
    public function renderSendInvoicing($id, $docIds = [])
    {
        if (!$id) {
            $this->flashMessage('Výjezd se nepodařilo najít!', 'warning');
            $this->redirect('Visit:default');
        }
        $visit = $this->em->getVisitRepository()->find($id);
        if (!$visit) {
            $this->flashMessage('Výjezd se nepodařilo najít!', 'warning');
            $this->redirect('Visit:default');
        }
        if (!is_array($docIds) || !count($docIds)) {
            $this->flashMessage('Dokumenty se nepodařilo najít!', 'warning');
            $this->redirect('Visit:edit', ['id' => $id, 'openTab' => '#docs']);
        }
        $dId = [];
        foreach ($docIds as $docId) {
            $doc = $this->em->getVisitDocumentRepository()->find($docId);
            $dId[$docId] = $docId;
        }
        $this[ 'sendInvoicingForm' ]->getComponent('id')->value = $id;
        $this[ 'sendInvoicingForm' ]->getComponent('docIds')->items = $dId;
        $this[ 'sendInvoicingForm' ]->getComponent('docIds')->value = $docIds;
        $this->template->visit = $visit;
        $this->template->docIds = $docIds;
    }

    /**
     * ACL name='Tabulka s přehledem výjezdů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Visit::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Visit:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');


        $grid->getColumn('materialNeedBuy')->setRenderer(function($item) {
            $arr = [];
            if ($item->materialNeedBuy) {
                foreach ($item->materialNeedBuy as $mnb) {
                    $arr[] = $mnb->name;
                }
            }
            return implode(', ', $arr);
        });
        $grid->getColumn('materialNeedBuy')->setSortableCallback(function($qb, $value) {
            $qb->leftJoin(\App\Model\Database\Entity\MaterialNeedBuy::class, 'mnb2', 'WITH', 'a.id = mnb2.visit');
            foreach ($value as $k => $v) {
                $qb->orderBy('mnb2.name', $v);
            }
        });
        $grid->getFilter('materialNeedBuy')->setCondition(function($qb, $value) {
            $search = $this->SQLHelper->termToLike($value, 'mnb', ['name']);
            $qb->leftJoin(\App\Model\Database\Entity\MaterialNeedBuy::class, 'mnb', 'WITH', 'a.id = mnb.visit');
            $qb->andWhere($search);
        });

        $grid->getColumn('durationHours')
            ->setRenderer(function($item) {
                return sprintf('%d:%02d', $item->durationHours, $item->durationMinutes);
            });

        $multiAction->addAction('edit', 'Upravit', 'Visit:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $grid->addGroupAction('Udělat kopii')->onSelect[] = [$this, 'makeCopy'];

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit výjezdu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Visit::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit výjezd', 'success'], ['Nepodařilo se uložit výjezd!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'successVisitForm'];
        return $form;
    }

    public function successVisitForm($form, $values)
    {
        $values2 = $this->request->getPost();
        if(!$values->dateStart) {
            $this->flashMessage('Prosím vyplňte pole Datum!', 'warning');
            return;
        }
        if(!$values->onceTimes) {
            $this->flashMessage('Prosím vyplňte pole Čas výjezdu!', 'warning');
            return;
        }
        if(!$values->customer) {
            $this->flashMessage('Prosím vyplňte pole Zákazník!', 'warning');
            return;
        }
        if(!$values->traffic) {
            $this->flashMessage('Prosím vyplňte pole Provozovna!', 'warning');
            return;
        }
        if(!$values->customerOrdered) {
            $this->flashMessage('Prosím vyplňte pole Objednatel!', 'warning');
            return;
        }
        if(!$values->state) {
            $this->flashMessage('Prosím vyplňte pole Stav workflow!', 'warning');
            return;
        }

        $new = 0;
        $visitBefore = null;
        $visitBeforeState = null;
        if ($values->id) {
            $visitBefore = $this->ed->get($this->em->getVisitRepository()->find($values->id));

            if ($visitBefore && $visitBefore['state']) {
                $visitBeforeState = $this->em->getVisitStateRepository()->find($visitBefore['state']);
            }
        } else {
            $new = 1;
        }

        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }

        //log změn stavu workflow (visitState)
        if ($new && $entity->state) {
            $logVisitState = new \App\Model\Database\Entity\VisitLog();
            $logVisitState->setVisit($entity);
            $logVisitState->setUser($this->em->getUserRepository()->find($this->user->getId()));
            $logVisitState->setFoundedDate(new \DateTime());
            $logVisitState->setText('Založení výjezdu.');
            $logVisitState->setNewText($entity->state->name);
            $this->em->persist($logVisitState);
            $this->em->flush($logVisitState);
        }
        if ($visitBeforeState && $entity->state && $visitBeforeState->id != $entity->state->id) {
            $logVisitState = new \App\Model\Database\Entity\VisitLog();
            $logVisitState->setVisit($entity);
            $logVisitState->setUser($this->em->getUserRepository()->find($this->user->getId()));
            $logVisitState->setFoundedDate(new \DateTime());
            $logVisitState->setText('Změna stavu uživatelem.');
            $logVisitState->setOldText($visitBeforeState->name);
            $logVisitState->setNewText($entity->state->name);
            $this->em->persist($logVisitState);
            $this->em->flush($logVisitState);
        }

        if (isset($values2[ 'ajSend' ])) {
            return;
        }

        if (isset($values2['addNeedBuy']) && $values2['textsearchMaterialNeedBuy']) {
            $needBuy = new \App\Model\Database\Entity\MaterialNeedBuy();
            $needBuy->setVisit($entity);
            $needBuy->setIsBuy(false);
            $needBuy->setName($values2['textsearchMaterialNeedBuy']);
            $needBuy->setMaterial($this->em->getMaterialRepository()->find($values2['searchMaterialNeedBuy']));
            $this->em->persist($needBuy);
            $this->em->flush($needBuy);
            $this->flashMessage('Nutno objednat se podařilo přidat', 'success');

            $this->em->clear(\App\Model\Database\Entity\MaterialNeedBuy::class);
            $this->em->clear(\App\Model\Database\Entity\Visit::class);
            if ($this->isAjax()) {
                $this->redrawControl('materialNeedBuy');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2[ 'downloadDocs' ]) || isset($values2[ 'sendEmailDocs' ]) || isset($values2[ 'sendInvoicingDocs' ])) {
            $ids = [];
            foreach ($values2 as $k => $f) {
                if ($f == true) {
                    $st = substr($k, 0, 4);
                    if ($st == 'doc/') {
                        $r = explode('/', $k);
                        //r[0]-dokument, r[1]-id dokumentu
                        $ids[] = $r[1];
                    }
                }
            }
            if (isset($values2[ 'downloadDocs' ])) {
                $this->downloadDocuments($ids, $entity->orderId2);
            }
            if (isset($values2[ 'sendEmailDocs' ])) {
                $this->redirect('Visit:sendDocs', ['id' => $entity->id, 'docIds' => $ids]);
            }
            if (isset($values2[ 'sendInvoicingDocs' ])) {
                $this->redirect('Visit:sendInvoicing', ['id' => $entity->id, 'docIds' => $ids]);
            }
            return;
        }

        if (!$entity->durationHours && !$entity->durationMinutes) {
            $entity->setDurationHours(-1);
            $this->em->flush($entity);
        }

        if ($entity->visitProcess && $entity->visitProcess->visits) {
            $visitsOnProcess = $entity->visitProcess->visits;
            if (is_array($visitsOnProcess)) {
                $lastVisitOnPrcess = end($visitsOnProcess);
                if ($lastVisitOnPrcess && $lastVisitOnPrcess->id == $entity->id) {
                    if ($values->status) {
                        $vss = $this->em->getVisitProcessStateRepository()->findOneBy(['name' => $values->status->name]);
                        if ($vss) {
                            $visitProcess = $entity->visitProcess;
                            $visitProcess->setState($vss);

                            if ($values->status->id == 1) {
                                $visitProcess->dateFinished = $entity->dateStart;
                            }

                            $this->em->flush($visitProcess);
                        }
                    }
                }
            }
        }

        if ($new) {
            if (!$entity->orderId) {
                $entity->setOrderId(explode('/', $entity->orderId2)[0]);
                $this->em->flush($entity);
            }

            if(!$entity->visitProcess) {
                $visitProcess = new \App\Model\Database\Entity\VisitProcess();
                $visitProcess->setOrderId($entity->orderId);
                $visitProcess->setName($entity->name);
                $visitProcess->setDescription($entity->description);
                $visitProcess->setCustomer($entity->customer);
                $visitProcess->setCustomerOrdered($entity->customerOrdered);
                $visitProcess->setTraffic($entity->traffic);
                $visitProcess->setState($this->em->getVisitProcessStateRepository()->findOneBy(['active' => 1], ['stateOrder' => 'ASC']));
                $this->em->persist($visitProcess);
                $this->em->flush($visitProcess);
                $entity->setVisitProcess($visitProcess);
                $this->em->flush($entity);
            }

            $workerOnVisit = $this->em->getWorkerOnVisitRepository()->findBy(['visit' => $entity]);
            if(!$workerOnVisit) {
                $hour = $min = 0;
                sscanf($entity->onceTimes, '%d:%d', $hour, $min);
                $date = clone $entity->dateStart;
                $serviceTime = clone $date->setTime($hour, $min);
                $dateService = clone $entity->dateStart;
                if ($serviceTime->getTimestamp() < $date->setTime(7, 0)->getTimestamp()) {
                    $dateService->modify('-1 day');
                }
                $service = $this->em->getServiceRepository()->findOneBy(['dateService' => $dateService]);
                if($service) {
                    $year = $entity->dateStart->format('Y');
                    $holidays = array($year.'-01-01' => 'Nový rok', $year.'-05-01' => 'Svátek práce', $year.'-05-08' => 'Den vítězství',
                        $year.'-07-05' => 'Den slovanských věrozvěstů Cyrila a Metoděje', $year.'-07-06' => 'Den upálení mistra Jana Husa',
                        $year.'-09-28' => 'Den české státnosti', $year.'-10-28' => 'Den vzniku samostatného československého státu',
                        $year.'-11-17' => 'Den boje za svobodu a demokracii', $year.'-12-24' => 'Štědrý den', $year.'-12-25' => '1. svátek vánoční',
                        $year.'-12-26' => '2. svátek vánoční');
                    $holidays[date('Y-m-d', strtotime(StrFTime("%Y-%m-%d", easter_date($year)) . ' -2 day'))] = 'Velký pátek';
                    $holidays[date('Y-m-d', strtotime(StrFTime("%Y-%m-%d", easter_date($year)) . ' +1 day'))] = 'Velikonoční pondělí';

                    if(array_key_exists($entity->dateStart->format('Y-m-d'), $holidays) || in_array($entity->dateStart->format('N'), ['6', '7']) || intval(str_replace(':', '', $entity->onceTimes)) >= 1600 || intval(str_replace(':', '', $entity->onceTimes)) <= 0700 || intval(str_replace(':', '', $entity->onceTimes)) <= 700 ) {
                        $wov = new \App\Model\Database\Entity\WorkerOnVisit();
                        $wov->setVisit($entity);
                        $wov->setWorker($service->worker);
                        $this->em->persist($wov);
                        $this->em->flush($wov);
                    }
                }
            }
        }

        $dataSignature = $values->signature;
        if (preg_match('/^data:image\/(\w+);base64,/', $dataSignature, $typeSignature)) {
            $dataSignature = substr($dataSignature, strpos($dataSignature, ',') + 1);
            $typeSignature = strtolower($typeSignature[1]); // jpg, png, gif
            if (in_array($typeSignature, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                $data = str_replace( ' ', '+', $dataSignature );
                $dataSignature = base64_decode($dataSignature);
            }
        }
        $customerSignImage = null;
        if ($dataSignature) {
            $path = '_data/visit_doc/' . $entity->id . '/';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $customerSignImage = $path . date("Y_m_d_H_i_s") . '_signature_' . $values->orderId . '.' . $typeSignature;
            file_put_contents($customerSignImage, $dataSignature);
            $entity->setCustomerSignImage($customerSignImage);
            $this->em->flush($entity);
        }

        if (isset($values2['sendBack'])) { // Uložit a zpět
            $this->redirect('Visit:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('Visit:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('Visit:edit');
        } else {
            $this->redirect('Visit:edit', ['id' => $entity->id]);
        }
    }

    /**
     * ACL name='Formulář pro edit materiálu'
     */
    public function createComponentMaterialModalForm() {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\MaterialOnVisit::class, $this->user, $this, __FUNCTION__);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'materialModalFormSuccess'];
        return $form;
    }

    public function materialModalFormSuccess($form, $values) {
        $values2 = $this->request->getPost();

        // ukládám formulář  pomocí automatického save
        //$material = $this->formGenerator->processForm($form, $values, true);
        $res = $this->visFac->addMaterialOnVisit($values2['id'], $values2[ 'visit_id' ], $values2[ 'number' ], /*$values2[ 'description' ]*/$values2['textmaterial'],
            $values2[ 'stock' ], $values2['unit'], $values2['count'], $values2['material']);
        if (!$res) {
            $this->flashMessage('Materiál se nepodařilo uložit!', 'warning');
        } else {
            $this->flashMessage('Materiál se podařilo uložit.', 'success');
        }

        if (isset($values2['sendMatNew'])) {
            $this['materialModalForm']->setValues([], true);
            $this->redrawControl('materialModalParent');
            $this->redrawControl('materialModal');
        }
        if($this->isAjax()) {
            $this->redrawControl('material-visit');
        } else {
            //$this->redirect('Visit:edit', ['id' => $material->visit->id]);
            $this->redirect('this');
        }
    }

    /**
     * ACL name='Formulář pro odeslání dokumentů mailem'
     */
    public function createComponentSendDocsForm() {
        $form = new ACLForm();
        $form->addHidden('id');
        $form->addMultiSelect('docIds')->setHtmlAttribute('style', 'display: none;');
        $form->addText('email', 'Email');
        $form->addTextArea('note', 'Poznámka', 2, 5)->setHtmlAttribute('class', 'form-control');
        $form->setMessages(['Podařilo se odeslat dokumenty', 'success'], ['Nepodařilo se odeslat dokumenty!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'sendDocsFormSuccess'];
        return $form;
    }

    public function sendDocsFormSuccess($form, $values) {
        $values2 = $this->request->getPost();
        $visit = $this->em->getVisitRepository()->find($values2['id']);
        $res = $this->mailSender->sendVisitDocs($values2, $this->user->getId(), 'výjezdu '.$visit->orderId2, $visit->traffic->id);
        if ($res) {
            $this->flashMessage('Emaily se podařilo odeslat.', 'success');
        } else {
            $this->flashMessage('Emaily se nepodařilo odeslat.', 'warning');
        }
        $this->redirect('Visit:edit', ['id' => $values2['id'], 'openTab' => '#docs']);
    }

    /**
     * ACL name='Formulář pro odeslání dokumentů mailem k fakturaci'
     */
    public function createComponentSendInvoicingForm() {
        $form = new ACLForm();
        $form->addHidden('id');
        $form->addMultiSelect('docIds')->setHtmlAttribute('style', 'display: none;');
        $form->addTextArea('note', 'Poznámka', 2, 5)->setHtmlAttribute('class', 'form-control');
        $form->setMessages(['Podařilo se odeslat dokumenty', 'success'], ['Nepodařilo se odeslat dokumenty!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'sendInvoicingFormSuccess'];
        return $form;
    }

    public function sendInvoicingFormSuccess($form, $values) {
        $values2 = $this->request->getPost();
        $visit = $this->em->getVisitRepository()->find($values2['id']);
        $res = $this->mailSender->sendVisitDocs($values2, $this->user->getId(),'výjezdu '.$visit->orderId2, $visit->traffic->id,true);
        if ($res) {
            $this->flashMessage('Emaily se podařilo odeslat.', 'success');
        } else {
            $this->flashMessage('Emaily se nepodařilo odeslat.', 'warning');
        }
        $this->redirect('Visit:edit', ['id' => $values2['id'], 'openTab' => '#docs']);
    }

    public function makeCopy($ids)
    {
        $this->visFac->createCopiesVisit($ids);
        $this->flashMessage('Kopírování bylo dokončeno. Kopie v tabulce.', 'success');
        $this->redirect('this');
    }

    private function downloadDocuments($ids, $baseFileName)
    {
        $archiveName = str_replace('/', '_', $baseFileName);
        $fileName = date('Ymdhis').'-'.$archiveName.".zip";
        $archive_file_name = "_data/temp-files/".$fileName;
        $zip = new \ZipArchive();
        if ($zip->open($archive_file_name, \ZipArchive::CREATE) === TRUE) {
            foreach ($ids as $docId) {
                $doc = $this->em->getVisitDocumentRepository()->find($docId);
                //$docName = str_replace('/', '_', substr($doc->document, strrpos($doc->document, '/') + 1));
                $docName = $doc->name;
                $zip->addFile($doc->document, $docName);
            }
            $zip->close();

            $this->sess->turnoverExportVisitsDoc['file'] = $archive_file_name;
            $this->sess->turnoverExportVisitsDoc['name'] = $archiveName;
        }
        $this->redirect('this');
    }

    public function handleDeleteVisitDocument($documentId)
    {
        $res = $this->visFac->deleteVisitDocum($documentId);
        if ($this->isAjax()) {
            $this->redrawControl('product-documents');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDownloadZip()
    {
        if (isset($this->sess->turnoverExportVisitsDoc)) {
            $this->getHttpResponse()->setContentType('application/zip');
            $this->getHttpResponse()->setHeader('Content-Disposition', 'filename='.$this->sess->turnoverExportVisitsDoc['name'].'.zip');
            $this->getHttpResponse()->setHeader('Content-Length', filesize($this->sess->turnoverExportVisitsDoc['file']));
            $this->getHttpResponse()->setHeader('Content-Transfer-Encoding', 'binary');
            $this->getHttpResponse()->setHeader('Cache-Control', 'must-revalidate');
            $this->getHttpResponse()->setHeader('Pragma', 'public');
            readfile($this->sess->turnoverExportVisitsDoc['file']);
            unlink($this->sess->turnoverExportVisitsDoc['file']);
            unset($this->sess->turnoverExportVisitsDoc);
            $this->terminate();
        }
    }

    public function handleCheckMaterialPart()
    {
        $values = $this->request->getPost();
        if($values['material']) {
            $entity = $this->em->getMaterialOnVisitRepository()->find($values['material']);
            $arr = $this->ed->get($entity);
            $this['materialModalForm']->setAutocmp('material', $entity->description);
            $this['materialModalForm']->setDefaults($arr);
            $this->template->modalMaterial = $entity;
        }

        $this->redrawControl('materialModalParent');
        $this->redrawControl('materialModal');
    }

    public function handleRemoveMaterialPart()
    {
        $values = $this->request->getPost();
        if (isset($values['material'])) {
            $entity = $this->em->getMaterialOnVisitRepository()->find($values['material']);
            if ($entity) {
                $this->em->remove($entity);
                $this->em->flush();
                $this->flashMessage('Materiál se podařilo odstranit.', 'success');
            } else {
                $this->flashMessage('Materiál se nepodařilo odstranit!', 'warning');
            }
        } else {
            $this->flashMessage('Materiál se nepodařilo odstranit!', 'warning');
        }

        $this->redrawControl('material-visit');
    }

    public function handleRemoveMaterialNeedBuyPart($matNeedBuy)
    {
        if ($matNeedBuy) {
            $entity = $this->em->getMaterialNeedBuyRepository()->find($matNeedBuy);
            if ($entity) {
                $this->em->remove($entity);
                $this->em->flush();
                $this->flashMessage('Nutno objednat se podařilo odstranit.', 'success');
            } else {
                $this->flashMessage('Nutno objednat se nepodařilo odstranit!', 'warning');
            }
        } else {
            $this->flashMessage('Nutno objednat se nepodařilo odstranit!', 'warning');
        }
        $this->redrawControl('materialNeedBuy');
    }

    public function handleUpdateFiles()
    {
        if ($this->sess->files) {
            foreach ($this->sess->files as $f) {
                $this->flashMessage('Soubor ' . $f . ' byl úspěšně nahrán.', 'success');
            }
            unset($this->sess->files);
        }
        if ($this->isAjax()) {
            $this->redrawControl('product-documents');
        }
    }

    public function handleEditfile($idVisit)
    {
        $files = $this->request->getFiles();
        if ($files) {
            foreach($files as $f) {
                $fileName = $f->getName();
                $type = substr($fileName, strrpos($fileName, '.') + 1);
                $name = $fileName;
                $folder = $tmp = '_data/visit_doc/' . $idVisit . '/';
                if (!file_exists($folder)) {
                    mkdir($folder, 0775);
                }
                $tmp = str_replace(".", "", microtime(true));
                $path = $folder . $tmp . '.' . $type;
                $f->move($path);
                $this->visFac->saveNewDocum($name, $this->user->getId(), $path, $idVisit, $type);

                $this->sess->files[] = $f->getName();
            }
        }
    }

    public function handleCheckTrafficWorker($traffic)
    {
        $trafficWorker['worker'] = [];
        $trafficWorker['workerSubstitute'] = [];

        if (is_numeric($traffic)) {
            $trafficEnt = $this->em->getTrafficRepository()->find($traffic);
            if ($trafficEnt) {
                $worker = [];
                foreach ($trafficEnt->worker as $c){
                    if (!$c->worker->active) {
                        continue;
                    }
                    $worker[] = $c->worker->id;
                }
                $trafficWorker['worker'] = $worker;
                $workerSubstitute = [];
                foreach ($trafficEnt->workerSubstitute as $c){
                    if (!$c->worker->active) {
                        continue;
                    }
                    $workerSubstitute[] = $c->worker->id;
                }
                $trafficWorker['workerSubstitute'] = $workerSubstitute;
            }
        }

        $this->payload->data = json_encode($trafficWorker);
        $this->sendPayload();
    }

    public function handleCheckServiceWorker()
    {
        $data = $this->request->getPost()['data'];

        $hour = 0; $min = 0;
        sscanf($data['onceTimes'], '%d:%d', $hour, $min);
        $date = \DateTimeImmutable::createFromFormat('d. m. Y', $data['dateStart']);


        $service = null;
        if ($date) {
            $year = $date->format('Y');
            $holidays = array($year.'-01-01' => 'Nový rok', $year.'-05-01' => 'Svátek práce', $year.'-05-08' => 'Den vítězství',
                $year.'-07-05' => 'Den slovanských věrozvěstů Cyrila a Metoděje', $year.'-07-06' => 'Den upálení mistra Jana Husa',
                $year.'-09-28' => 'Den české státnosti', $year.'-10-28' => 'Den vzniku samostatného československého státu',
                $year.'-11-17' => 'Den boje za svobodu a demokracii', $year.'-12-24' => 'Štědrý den', $year.'-12-25' => '1. svátek vánoční',
                $year.'-12-26' => '2. svátek vánoční');
            $holidays[date('Y-m-d', strtotime(StrFTime("%Y-%m-%d", easter_date($year)) . ' -2 day'))] = 'Velký pátek';
            $holidays[date('Y-m-d', strtotime(StrFTime("%Y-%m-%d", easter_date($year)) . ' +1 day'))] = 'Velikonoční pondělí';

            $serviceTime = clone $date->setTime($hour, $min);
            if ($serviceTime->getTimestamp() > $date->setTime(16, 0)->getTimestamp()) {
                $service = $this->em->getServiceRepository()->searchByDate($serviceTime);

            } elseif ($serviceTime->getTimestamp() < $date->setTime(7, 0)->getTimestamp()) {
                $service = $this->em->getServiceRepository()->searchByDate($serviceTime->modify('-1day'));

            } elseif (in_array($serviceTime->format('N'), [6,7])) {
                $service = $this->em->getServiceRepository()->searchByDate($serviceTime);

            } elseif (array_key_exists($serviceTime->format('Y-m-d'), $holidays)) {
                $service = $this->em->getServiceRepository()->searchByDate($serviceTime);
            }
        }

        $msg = '';
        if ($service) {
            $msg = "Službu má technik: " . $service->worker->name;
        }

        $this->template->serviceWorker = $msg;
        $this->redrawControl('serviceWorkerMsg');
    }

    /**
     * Get customers for autocomplete
     * @param string $term
     */
    public function handleGetCustomers($term)
    {
        $result = $this->em->getCustomerRepository()->getDataAutocompleteCustomers($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    /**
     * @param $term
     */
    public function handleGetCustomerOrdereds($term)
    {
        $result = $this->em->getCustomerOrderedRepository()->getDataAutocompleteCustomerOrdered($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleGetTraffics($term)
    {
        $result = $this->em->getTrafficRepository()->getDataAutocompleteTraffics($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleGetVisitProcess($term)
    {
        $result = $this->em->getVisitProcessRepository()->getDataAutocompleteVisitProcess($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleGetMaterials($term)
    {
        $result = $this->em->getMaterialRepository()->getDataAutocompleteMaterials($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleGetRefrigerantMaterials($term)
    {
        $result = $this->em->getMaterialRepository()->getDataAutocompleteMaterials($term, true);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

}
