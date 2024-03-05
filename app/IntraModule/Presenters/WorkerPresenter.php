<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\ShiftBonus;
use App\Model\Database\Entity\User;
use App\Model\Database\Entity\Worker;
use App\Model\Database\Entity\WorkerNote;
use App\Model\Database\Entity\SkillInWorker;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;

class WorkerPresenter extends BasePresenter
{
    /** @var Passwords @inject */
    public Passwords $passwords;

    /**
     * WorkerPosition's ID
     * @persistent
     */
    public $backWPosID;

    /**
     * WorkerTender's ID
     * @persistent
     */
    public $backWTendID;

    /** @var \DateTime @persistent */
    public $futureShiftsDateFrom;

    /** @var \DateTime @persistent */
    public $futureShiftsDateTo;

    /**
     * ACL name='Správa zaměstnanců'
     * ACL rejection='Nemáte přístup ke správě zaměstnanců.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
        $this->futureShiftsDateFrom = null;
        $this->futureShiftsDateTo = null;
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání nového zaměstnance'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getWorkerRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Zaměstnanec nebyl nalezen.', 'error');
                $this->redirect('Worker:');
            } // :o:  close();
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;

            if($entity->user) {
                $entityUser = $this->em->getUserRepository()->find($entity->user->id);
                $arrUser = $this->ed->get($entityUser);
                $this['userForm']->setDefaults($arrUser);
                $this->template->entityUser = $entityUser;
            }

            $shiftBonuses = $this->em->getShiftBonusRepository()->findBy(['worker' => $entity]);
            $this->template->shiftBonuses = $shiftBonuses;

            $qb = $this->em->getWorkerInPlanRepository()->createQueryBuilder('wipl');
            $qb->select('wipl')
                ->join('wipl.plan', 'p')
                ->join('wipl.worker', 'w')
                ->where('w.id = :id')
                ->orderBy('p.datePlan', 'ASC');
            $params['id'] = $id;
            if ($this->futureShiftsDateFrom) {
                $qb->andWhere('p.datePlan >= :dateFrom');
                $params['dateFrom'] = $this->futureShiftsDateFrom->format('Y-m-d H:i:s');
            } else {
                $qb->andWhere('p.datePlan >= NOW()');
            }
            if ($this->futureShiftsDateTo) {
                $qb->andWhere('p.datePlan <= :dateTo');
                $params['dateTo'] = $this->futureShiftsDateTo->format('Y-m-d H:i:s');
            }
            $qb->setParameters($params);
            $futureShifts = $qb->getQuery()->getResult();

            $this->template->futureShifts = $futureShifts;

            $qb = $this->em->getWorkerInWorkerTenderRepository()->createQueryBuilder('wiwt');
            $qb->select('wiwt')
                ->join('wiwt.tender', 't')
                ->join('wiwt.worker', 'w')
                ->where('w.id = :id')
                ->setParameters(['id' => $this->params['id']]);
            $tenders = $qb->getQuery()->getResult(); 
            $this->template->tenders = $tenders;

            $this->template->openTab = ($this->getParameter('openTab') !== null) ? $this->getParameter('openTab') : null;
        } else {
            $this->template->openTab = null;
        }

        if ($this->getParameter('backWPosID') !== null) {
            $this->template->backWPosID = $this->getParameter('backWPosID');
        }
        if ($this->getParameter('backWTendID') !== null) {
            $this->template->backWTendID = $this->getParameter('backWTendID');
        }

        $bonusGroupTrans = array();
        $shiftBonusGroups = $this->em->getShiftBonusGroupRepository()->findBy([]);
        foreach ($shiftBonusGroups as $bonusGroup) {
            $bonusGroupTrans[$bonusGroup->id] = $bonusGroup->name;
        }
        $this->template->bonusGroupTrans = $bonusGroupTrans;

        $this->template->shiftTrans = ['A'=>'A', 'B'=>'B', 'C'=>'C', 'D'=>'D'];
        $this->template->dayTrans = [1=>'Pondělí', 2=>'Úterý', 3=>'Středa', 4=>'Čtvrtek', 5=>'Pátek', 6=>'Sobota', 7=>'Neděle'];
        $this->template->nameTrans = [1=>'Ranní', 2=>'Noční'];
        $this->template->lineTrans = [1=>'KTL', 2=>'ZN'];

        if(isset($this->sess->turnoverExportWorkerFutureShift)){
            header('Content-Disposition: attachment; filename='.$this->sess->turnoverExportWorkerFutureShift['name'] );
            header('Content-Type: application/pdf');
            header('Content-Length: ' . filesize($this->sess->turnoverExportWorkerFutureShift['file']));
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            readfile($this->sess->turnoverExportWorkerFutureShift['file']);

            unlink($this->sess->turnoverExportWorkerFutureShift['file']);
            unset($this->sess->turnoverExportWorkerFutureShift);
        }
    }

    /**
     * ACL name='Tabulka s přehledem zaměstnanců'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Worker::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Worker:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Worker:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        /*$grid->getColumn('shiftBonusGridOnly')
            ->setTemplate(__DIR__ . '/templates/Worker/column_bol.latte', ['column' => 'shiftBonus']);

        $grid->getFilter('shiftBonusGridOnly')
            ->setCondition(function($qb, $value) {
                if($value == true) {
                    $qb->leftJoin('App\Model\Database\Entity\ShiftBonus', 'bon', 'WITH', 'a.id = bon.worker ');
                    $qb->andWhere('bon.worker IS NOT NULL');
                } else {
                    $sub = $this->em->createQueryBuilder();
                    $sub->select('bon');
                    $sub->from('App\Model\Database\Entity\ShiftBonus', 'bon');
                    $sub->andWhere('bon.worker = a.id');
                    $qb->andWhere($qb->expr()->not($sub->expr()->exists($sub->getDQL())));
                }
            });*/

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Tabulka s přehledem poznámek k zaměstnancům'
     */
    public function createComponentTableNotes()
    {
        $grid = $this->gridGen->generateGridByAnnotation(WorkerNote::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'WorkerNote:edit', 'id', (($this->getParameter('id') !== null) ? ['backWID' => $this->getParameter('id')] : 0));

        $col = $grid->getColumn('worker')->setDefaultHide();;

        if ($this->getParameter('id') !== null) {
            $qb = $this->em->getWorkerNoteRepository()->createQueryBuilder('n');
            $qb->select('n');
            $qb->join('n.worker', 'w');
            $qb->where('w.id = :id');
            $qb->setParameters(['id' => $this->getParameter('id')]);
            $grid->setDataSource($qb);
        } else {
            $grid->setDataSource([]);
            return $grid;
        }

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'WorkerNote:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit zaměstnance'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Worker::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit zaměstnance', 'success'], ['Nepodařilo se uložit zaměstnance!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'processFormSuccess'];

        return $form;
    }

    public function processFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        $oldEntity = null;
        if ($values->id) {
            $oldEntity = $this->em->getWorkerRepository()->find($values->id);
        }
       
        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }

        if($entity->endContractDate && $oldEntity && $entity->endContractDate != $oldEntity->endContractDate) {
            $startDate = new \DateTime($entity->endContractDate->format('Y-m-d') . ' 00:00:00');
            $startDate->modify('+1 day');
            $qb = $this->em->getConnection()->prepare("
                            DELETE worker_in_plan FROM worker_in_plan 
                            INNER JOIN shift_plan ON shift_plan.id = worker_in_plan.plan_id 
                            WHERE "."worker_in_plan.worker_id = " . $entity->id . " AND shift_plan.date_plan >= '".$startDate->format('Y-m-d H:i:s')."'
                            ");
            $qb->execute();
            $this->em->flush();
        }

        if(isset($values2['createUser']) && $values2['createUser']) {
            $user = new User();

            $userName =  preg_replace('/\s+/', '', strtolower($this->remove_accents($entity->name . $entity->surname . $entity->id)));
            $user->setUsername($userName);
            $user->setPassword($this->passwords->hash($userName));
            $user->setName($entity->name . ' ' . $entity->surname);
            $user->setPhone($entity->phone);
            $user->setEmail($entity->email);

            $user->setIsAdmin(false);
            $user->setIsBlocked(false);
            $user->setIsMaster(false);
            $user->setDocumentsAllow(false);
            $user->setQualificationAllow(false);
            $user->setQualificationEdit(false);
            $user->setQualificationViewEffective(false);

            $this->em->persist($user);
            $this->em->flush();

            $qb = $this->em->getConnection()->prepare("
                            UPDATE user SET group_id = 2 
                            WHERE id = " . $user->id . "
                            ");
            $qb->execute();

            $entity->setUser($user);
            $this->em->flush();
        }

        if (isset($values2['sendBack'])) { // Uložit a zpět
            if ($this->getParameter('wposID') !== null) {
                $this->redirect('WorkerPosition:edit', ['id' => $this->getParameter('wposID'), 'openTab' => '#workers']);
            } else {
                $this->redirect('Worker:default');
            }
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('Worker:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('Worker:edit');
        } else {
            $this->redirect('Worker:edit', ['id' => $entity->id]);
        }
    }

    /**
     * ACL name='Formulář pro přidání/editaci uživatelů'
     */
    public function createComponentUserForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(User::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit přístupy', 'success'], ['Nepodařilo se přístupy uložit!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'processFormUser'];
        return $form;
    }

    public function processFormUser($form, $values)
    {
        $values2 = $this->request->getPost();

        if (isset($values['password'])) {
            if ($values['password'] != '') {
                $values['password'] = $this->passwords->hash($values['password']);
            } else {
                unset($values['password']);
            }
        }

        $user = $this->formGenerator->processForm($form, $values, true);

        $worker = $this->em->getWorkerRepository()->findOneBy(['user' => $user]);
        if($worker) {
            $this->redirect('Worker:edit',  ['id' => $worker->id, 'openTab' => '#user']);
        } else {
            $this->redirect('this');
        }
    }

    public function createComponentPlanModalForm()
    {
        $that = $this;

        $form = new Form();
        $form->onSuccess[] = function(Form $form, $values) use($that): void {

            $values2 = $this->request->getPost();
            if(isset($values2['bonus']) && $values2['bonus']) {
                $entity = $this->em->getShiftBonusRepository()->find($values2['bonus']);
            } else {
                $entity = new ShiftBonus();
            }

            if(isset($values2['worker']) && $values2['worker']) {
                $worker = $this->em->getWorkerRepository()->find($values2['worker']);

                $entity->setWorker($worker);
                $entity->setShift($values2['shift']);
                $entity->setName($values2['name']);
                $entity->setProductionLine($values2['line']);
                $entity->setDayOfWeek($values2['day']);
                $entity->setDateEnd(NULL);
                if($values2['dateEnd']) {
                    $testDate = date_create_from_format('d. m. Y', $values2['dateEnd']);
                    if($testDate) {
                        $entity->setDateEnd($testDate);
                    }
                }

                $this->em->persist($entity);
                $this->em->flush();
            }
            
            $that->template->openTab = '#shifts';
            if ($that->isAjax()) {
                $that->redrawControl('bonusTable');
            } else {
                $that->redirect('Worker:edit',  ['id' => $values2['worker'], 'openTab' => '#shifts']);
            }
        };

        return $form;
    }

    public function createComponentPlanModalGroupForm()
    {
        $that = $this;

        $form = new Form();
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();
            if(isset($values2['bonusGroup']) && $values2['bonusGroup'] && isset($values2['worker']) && $values2['worker']) {
                $worker = $this->em->getWorkerRepository()->find($values2['worker']);
                $oldBonuses = $this->em->getShiftBonusRepository()->findBy(['worker' => $worker]);
                foreach ($oldBonuses as $oldBonus) {
                    $this->em->remove($oldBonus);
                }

                $bonusTemplates = $this->em->getShiftBonusTemplateRepository()->findBy(['shiftBonusGroup' => $values2['bonusGroup']]);
                foreach ($bonusTemplates as $bonusTemplate) {
                    $entity = new ShiftBonus();
                    $entity->setWorker($worker);
                    $entity->setShift($bonusTemplate->shift);
                    $entity->setName($bonusTemplate->name);
                    $entity->setProductionLine($bonusTemplate->productionLine);
                    $entity->setDayOfWeek($bonusTemplate->dayOfWeek);
                    $entity->setDateEnd(NULL);
                    $this->em->persist($entity);
                }
                $this->em->flush();
            }

            $that->template->openTab = '#shifts';
            if ($that->isAjax()) {
                $that->redrawControl('bonusTable');
            } else {
                $that->redirect('Worker:edit',  ['id' => $values2['worker'], 'openTab' => '#shifts']);
            }
        };

        return $form;
    }

    public function handleCheckShiftBonus() {
        $values = $this->request->getPost();
        if($values['bonus']) {
            $entity = $this->em->getShiftBonusRepository()->find($values['bonus']);
            $this->template->modalBonus = $entity;
        }
        $this->redrawControl('planModal');
    }

    public function handleCheckShiftBonusGroup() {
        $this->redrawControl('planGroupModal');
    }

    public function handleRemoveShiftBonus() {
        $values = $this->request->getPost();
        $entity = $this->em->getShiftBonusRepository()->find($values['bonus']);
        $this->em->remove($entity);
        $this->em->flush();

        $this->redrawControl('bonusTable');
    }

    public function handleRemoveShiftBonusAll() {
        $values = $this->request->getPost();
        $worker = $this->em->getWorkerRepository()->find($values['worker']);
        $entities = $this->em->getShiftBonusRepository()->findBy(['worker' => $worker]);
        foreach ($entities as $entity) {
            $this->em->remove($entity);


        }
        $this->em->flush();

        $this->redrawControl('bonusTable');
    }

    public function handleSaveTender() {
        $values = $this->request->getPost();
        if($values['id'] && $values['result']) {
            $entity = $this->em->getWorkerInWorkerTenderRepository()->find($values['id']);
            if ($entity) {
                $entity->setResult($values['result']);
                $entity->setResultDesc(isset($values['resultDesc']) ? $values['resultDesc'] : '');
                $this->em->persist($entity);
                $this->em->flush();

                if ($values['result'] !== 'F') {
                    foreach ($entity->tender->skills as $siw) {
                        if (!$this->em->getSkillInWorkerRepository()->findBy(['worker' => $entity->worker, 'skill' => $siw->skill])) {
                            $siwNew = new SkillInWorker();
                            $siwNew->setWorker($entity->worker);
                            $siwNew->setSkill($siw->skill);
                            $this->em->persist($siwNew);
                            $this->em->flush();
                        }
                    }
                    $this->em->persist($entity->worker);
                    $this->em->flush();
                }
                $this->flashMessage('Hodnocení uloženo', 'success');
            } else {
                $this->flashMessage('Školení zaměstnance nebylo nalezeno', 'error');
            }
        }
        $this->redrawControl('tableTenders');
    }

    public function handleRemoveTender() {
        $values = $this->request->getPost();
        $entity = $this->em->getWorkerInWorkerTenderRepository()->find($values['id']);
        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        } else {
            $this->flashMessage('Zaměstnanec se v tomto školení již nenachází', 'error');
        }
        $this->redrawControl('tableTenders');
    }

    public function handleShowFutureShiftsByDate() {
        $data = $this->request->getPost();

        if ($data['dateFrom']) {
            $this->futureShiftsDateFrom = new \DateTime($data['dateFrom']);
        } else {
            $this->futureShiftsDateFrom = null;
        }
        if ($data['dateTo']) {
            $this->futureShiftsDateTo = new \DateTime($data['dateTo']);
        } else {
            $this->futureShiftsDateTo = null;
        }
        if ($data['dateFrom'] && $data['dateTo'] && $this->futureShiftsDateFrom > $this->futureShiftsDateTo) {
            //jestliže je "datum od" vetší jak "datum do" tak je prohodím
            $this->futureShiftsDateFrom = new \DateTime($data['dateTo']);
            $this->futureShiftsDateTo = new \DateTime($data['dateFrom']);
        }
        $this->sess->futureShiftsDateFrom[$data['workerId']] = $this->futureShiftsDateFrom;
        $this->sess->futureShiftsDateTo[$data['workerId']] = $this->futureShiftsDateTo;

        $this->redrawControl('futureTable');
    }

    public function handlePrintWorkerFutureShift() {
        $values = $this->request->getPost();
        $worker = $this->em->getWorkerRepository()->find($values['id']);
        $datetime = new \datetime();
        $dateFrom = isset($this->sess->futureShiftsDateFrom[$values['id']]) ? $this->sess->futureShiftsDateFrom[$values['id']] : null;
        $dateTo = isset($this->sess->futureShiftsDateTo[$values['id']]) ? $this->sess->futureShiftsDateTo[$values['id']] : null;

        /* data start */
        $qb = $this->em->getWorkerInPlanRepository()->createQueryBuilder('wipl');
        $qb->select('wipl')
            ->join('wipl.plan', 'p')
            ->join('wipl.worker', 'w')
            ->where('w.id = :id')
            ->orderBy('p.datePlan', 'ASC');
        $params['id'] = $values['id'];
        if ($dateFrom) {
            $qb->andWhere('p.datePlan >= :dateFrom');
            $params['dateFrom'] = $dateFrom->format('Y-m-d H:i:s');
        } else {
            $qb->andWhere('p.datePlan >= NOW()');
        }
        if ($dateTo) {
            $qb->andWhere('p.datePlan <= :dateTo');
            $params['dateTo'] = $dateTo->format('Y-m-d H:i:s');
        }
        $qb->setParameters($params);
        $futureShifts = $qb->getQuery()->getResult();
        /* data end */

        $outputName = $worker->name.'_'.$worker->surname.'_směny.pdf';
        $file = $this->pdfPrinter->handlePrintFutureWorkerShift($futureShifts, $worker, $dateFrom, $dateTo, $outputName, 'F');

        $this->sess->turnoverExportWorkerFutureShift['name'] = $outputName;
        $this->sess->turnoverExportWorkerFutureShift['file'] = $file;
        $this->redirect('this');
    }

    public function remove_accents($string) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
        );

        $string = strtr($string, $chars);

        return $string;
    }
}