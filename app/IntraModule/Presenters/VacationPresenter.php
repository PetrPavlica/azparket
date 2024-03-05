<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Vacation;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\VacationFund;
use Doctrine\Common\Collections\Criteria;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

class VacationPresenter extends BasePresenter
{
    /** @var integer @persistent */
    public $yeara;

    /**
     * ACL name='Správa absencí'
     * ACL rejection='Nemáte přístup ke správě absencí.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním absence'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getVacationRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Absence nebyla nalezena.', 'error');
                $this->redirect('Vacation:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Zobrazení stránky s přehledem dovolené'
     */
    public function renderFund() {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        $this->template->hideTitleHeading = true;

        if (!$this->yeara) {
            $this->yeara = date('Y');
        }

        $weekStart = new \DateTime($this->yeara.'-01-01 00:00:00');
        $startDate = new \DateTime($this->yeara.'-01-01 00:00:00');
        $endDate = new \DateTime($this->yeara.'-12-31 23:59:59');

        $this->template->dateInput = $weekStart->format('Y');
        $this->template->yeara = $this->yeara;

        $this->template->previousYear = $this->yeara - 1;
        $this->template->nextYear = $this->yeara + 1;

        $workerArr = array();
        $workers = $this->em->getWorkerRepository()->findBy(['active' => 1, 'agency' => 0], ['surname' => 'ASC', 'name' => 'ASC']);
        foreach ($workers as $worker) {
            $vacationFund = $this->em->getVacationFundRepository()->findOneBy(['year' => $this->yeara, 'worker' => $worker]);
            if(!$vacationFund) {
                $vacationFund = new VacationFund();
                $vacationFund->setYear($this->yeara);
                $vacationFund->setWorker($worker);
                $vacationFund->setHoursBase(0);
                $vacationFund->setHoursPlus(0);
                $vacationFund->setHoursMinus(0);
                $this->em->persist($vacationFund);
                $this->em->flush();
            }
            $workerArr[$worker->id] = array();
            $workerArr[$worker->id]['id'] = $vacationFund->id;
            $workerArr[$worker->id]['name'] = $worker->surname . ' ' . $worker->name;
            $workerArr[$worker->id]['hoursBase'] = $vacationFund->hoursBase;
            $workerArr[$worker->id]['hoursPlus'] = $vacationFund->hoursPlus;
            $workerArr[$worker->id]['hoursMinus'] = $vacationFund->hoursMinus;
            $workerArr[$worker->id]['hoursLeft'] = $worker->hoursVacation;

            //$this->recountVacationLeft($worker);
        }

        $this->template->workerArr = $workerArr;
    }

    /**
     * ACL name='Tabulka s přehledem absencí'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Vacation::class, get_class(), __FUNCTION__);

        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);


            $grid = $this->gridGen->setClicableRows($grid, $this, 'Vacation:edit');

            $multiAction = $grid->getAction('multiAction');
            $multiAction->addAction('edit', 'Upravit', 'Vacation:edit');
            $action = $multiAction->getAction('edit');
            if ($action)
                $action->setIcon('edit')
                    ->setTitle('Úprava');

            $that = $this;
            $action = $multiAction->addActionCallback('delete', 'Smazat', function($itemId) use($that) {
                $vacation = $that->em->getVacationRepository()->find($itemId);

                $oldStartDateString = $vacation->dateStart->format('Y-m-d');
                $oldEndDateString = $vacation->dateEnd->format('Y-m-d');

                $worker = $that->em->getWorkerRepository()->find($vacation->worker->id);
                $shiftBonuses = $that->em->getShiftBonusRepository()->findBy(['worker' => $worker]);
                $shiftBonusArr = array(
                    'A' => [
                        '1' => [
                            '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                            '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                        ],
                        '2' => [
                            '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                            '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                        ],
                    ],
                    'B' => [
                        '1' => [
                            '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                            '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                        ],
                        '2' => [
                            '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                            '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                        ],
                    ],
                    'C' => [
                        '1' => [
                            '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                            '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                        ],
                        '2' => [
                            '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                            '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                        ],
                    ],
                    'D' => [
                        '1' => [
                            '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                            '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                        ],
                        '2' => [
                            '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                            '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                        ],
                    ]
                );
                foreach ($shiftBonuses as $shiftBonus) {
                    $shiftBonusArr[$shiftBonus->shift][$shiftBonus->productionLine][$shiftBonus->name][$shiftBonus->dayOfWeek][] = ['bonusEndDateString' => ($shiftBonus->dateEnd ? $shiftBonus->dateEnd->format('Y-m-d') : ''), 'bonusStartDateString' => ($shiftBonus->dateStart ? $shiftBonus->dateStart->format('Y-m-d') : ''), 'workerId' => $shiftBonus->worker->id, 'workerPosition' => ($shiftBonus->worker->workerPosition ? $shiftBonus->worker->workerPosition->id : 4)];
                }

                $insertNewSqlVal = '';
                if($oldStartDateString && $oldEndDateString) {
                    $nDateString = $oldStartDateString;
                    while($nDateString <= $oldEndDateString) {
                        $plans = $that->em->getShiftPlanRepository()->findBy(['dateString' => $nDateString]);
                        foreach ($plans as $plan) {
                            if($worker->productionLine && $worker->productionLine->id == $plan->productionLine && $worker->shift && $worker->shift == $plan->shift) {
                                $insertNewSqlVal .= '(NULL,'.$worker->id.','.$plan->id.','.($worker->workerPosition ? $worker->workerPosition->id : 4).',12),';
                            }
                            foreach ($shiftBonusArr[$plan->shift][$plan->productionLine][$plan->name][$plan->datePlan->format('N')] as $sbn) {
                                if(!$sbn['bonusEndDateString'] || $sbn['bonusEndDateString'] >= $plan->dateString) {
                                    if($sbn['bonusStartDateString'] && $sbn['bonusStartDateString'] > $plan->dateString) {
                                        continue;
                                    }
                                    $insertNewSqlVal .= '(NULL,'.$sbn['workerId'].','.$plan->id.','.$sbn['workerPosition'].',12),';
                                }
                            }
                        }

                        $newDate = new \DateTime($nDateString . ' 00:00:00');
                        $newDate->modify('+1 day');
                        $nDateString = $newDate->format('Y-m-d');
                    }
                }

                if($insertNewSqlVal) {
                    $insertNewSqlVal = substr($insertNewSqlVal, 0, -1);
                    $qb = $that->em->getConnection()->prepare("
                            INSERT INTO worker_in_plan (id, worker_id, plan_id, worker_position_id, hours) 
                            VALUES ".$insertNewSqlVal."
                            ");
                    $qb->execute();
                }
                $that->em->flush();

                $that->em->remove($vacation);
                $that->em->flush();

                $that->flashMessage('Záznam se podařilo úspěšně smazat', 'success');
                $that->redirect('this');
            });
            $action->setIcon('times')
                ->setTitle('Smazat')
                ->setConfirmation(new StringConfirmation('Opravdu chcete tento záznam smazat?'))
                ->setClass('text-danger dropdown-item');

            //$this->gridGen->addButtonDeleteCallback();

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit absence'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Vacation::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit absenci', 'success'], ['Nepodařilo se uložit absenci!', 'error']);
        $form->setRedirect('Vacation:');

        $that = $this;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            if($values->worker && $values->dateStart && $values->dateEnd) {
                $oldStartDateString = '';
                $oldEndDateString = '';
                $oldId = 0;
                if($values->id) {
                    $oldId = $values->id;
                    $vacation = $that->em->getVacationRepository()->find($values->id);
                    $oldStartDateString = $vacation->dateStart->format('Y-m-d');
                    $oldEndDateString = $vacation->dateEnd->format('Y-m-d');
                } else {
                    $vacation = new Vacation();
                }

                $worker = $that->em->getWorkerRepository()->find($values->worker);
                $newStartDate = date_create_from_format('d. m. Y', $values->dateStart);
                $newEndDate = date_create_from_format('d. m. Y', $values->dateEnd);

                $criteriaStart = new Criteria();
                //$criteriaStart->andWhere(Criteria::expr()->eq('worker', $worker));
                $criteriaStart->andWhere(Criteria::expr()->neq('id', $oldId));
                $criteriaStart->andWhere(Criteria::expr()->orX(
                    Criteria::expr()->andX(
                        Criteria::expr()->lte('dateStart', $newStartDate),
                        Criteria::expr()->gte('dateEnd', $newEndDate)
                    ),
                    Criteria::expr()->andX(
                        Criteria::expr()->gte('dateStart', $newStartDate),
                        Criteria::expr()->lte('dateEnd', $newStartDate)
                    ),
                    Criteria::expr()->andX(
                        Criteria::expr()->lte('dateStart', $newEndDate),
                        Criteria::expr()->gte('dateEnd', $newEndDate)
                    ),
                    Criteria::expr()->andX(
                        Criteria::expr()->gte('dateStart', $newStartDate),
                        Criteria::expr()->lte('dateEnd', $newEndDate)
                    )
                ));
                $overlapVacation = $that->em->getVacationRepository()->matching($criteriaStart);
                $foundCollision = 0;
                foreach ($overlapVacation as $ov) {
                    if($ov->worker && $ov->worker->id == $worker->id) {
                        $foundCollision = 1;
                        break;
                    }
                }
                if($foundCollision) {
                    $that->flashMessage('Absence v tomto termínu již existuje.', 'error');
                    $that->redrawControl('flashess');
                    return;
                }

                //$vacation = $that->formGenerator->processForm($form, $values, true);
                $vacation->setWorker($worker);
                $vacation->setVacationType($values->vacationType ? $that->em->getVacationTypeRepository()->find($values->vacationType) : NULL);
                $vacation->setDateStart($newStartDate);
                $vacation->setDateEnd($newEndDate);
                $vacation->setName($values->name);
                $vacation->setHours($values->hours);
                $vacation->setCountHours($values->countHours ? 1 : 0);

                $that->em->persist($vacation);
                $that->em->flush();

                if($vacation) {
                    $deleteVacationSql = '';

                    $maxDateString = $vacation->dateEnd->format('Y-m-d');
                    $nDateString = $vacation->dateStart->format('Y-m-d');
                    while($nDateString <= $maxDateString) {
                        $deleteVacationSql .= '(worker_in_plan.worker_id = '.$vacation->worker->id.' AND shift_plan.date_string = \''.$nDateString.'\') OR';

                        $newDate = new \DateTime($nDateString . ' 00:00:00');
                        $newDate->modify('+1 day');
                        $nDateString = $newDate->format('Y-m-d');
                    }

                    if($deleteVacationSql) {
                        $deleteVacationSql = substr($deleteVacationSql, 0, -3);
                        $qb = $that->em->getConnection()->prepare("
                            DELETE worker_in_plan FROM worker_in_plan 
                            INNER JOIN shift_plan ON shift_plan.id = worker_in_plan.plan_id 
                            WHERE ".$deleteVacationSql."
                            ");
                        $qb->execute();
                        $that->em->flush();
                    }

                    $shiftBonuses = $that->em->getShiftBonusRepository()->findBy(['worker' => $worker]);
                    $shiftBonusArr = array(
                        'A' => [
                            '1' => [
                                '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                                '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                            ],
                            '2' => [
                                '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                                '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                            ],
                        ],
                        'B' => [
                            '1' => [
                                '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                                '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                            ],
                            '2' => [
                                '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                                '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                            ],
                        ],
                        'C' => [
                            '1' => [
                                '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                                '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                            ],
                            '2' => [
                                '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                                '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                            ],
                        ],
                        'D' => [
                            '1' => [
                                '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                                '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                            ],
                            '2' => [
                                '1' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []],
                                '2' => ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []]
                            ],
                        ]
                    );
                    foreach ($shiftBonuses as $shiftBonus) {
                        $shiftBonusArr[$shiftBonus->shift][$shiftBonus->productionLine][$shiftBonus->name][$shiftBonus->dayOfWeek][] = ['bonusEndDateString' => ($shiftBonus->dateEnd ? $shiftBonus->dateEnd->format('Y-m-d') : ''), 'bonusStartDateString' => ($shiftBonus->dateStart ? $shiftBonus->dateStart->format('Y-m-d') : ''), 'workerId' => $shiftBonus->worker->id, 'workerPosition' => ($shiftBonus->worker->workerPosition ? $shiftBonus->worker->workerPosition->id : 4)];
                    }

                    $insertNewSqlVal = '';
                    if($oldStartDateString && $oldStartDateString < $vacation->dateStart->format('Y-m-d')) {
                        $nDateString = $oldStartDateString;
                        while($nDateString < $vacation->dateStart->format('Y-m-d')) {
                            $plans = $that->em->getShiftPlanRepository()->findBy(['dateString' => $nDateString]);
                            foreach ($plans as $plan) {
                                if($worker->productionLine && $worker->productionLine->id == $plan->productionLine && $worker->shift && $worker->shift == $plan->shift) {
                                    $insertNewSqlVal .= '(NULL,'.$worker->id.','.$plan->id.','.($worker->workerPosition ? $worker->workerPosition->id : 4).'),';
                                }
                                foreach ($shiftBonusArr[$plan->shift][$plan->productionLine][$plan->name][$plan->datePlan->format('N')] as $sbn) {
                                    if(!$sbn['bonusEndDateString'] || $sbn['bonusEndDateString'] >= $plan->dateString) {
                                        if($sbn['bonusStartDateString'] && $sbn['bonusStartDateString'] > $plan->dateString) {
                                            continue;
                                        }
                                        $insertNewSqlVal .= '(NULL,'.$sbn['workerId'].','.$plan->id.','.$sbn['workerPosition'].'),';
                                    }
                                }
                            }

                            $newDate = new \DateTime($nDateString . ' 00:00:00');
                            $newDate->modify('+1 day');
                            $nDateString = $newDate->format('Y-m-d');
                        }
                    }

                    if($oldEndDateString && $oldEndDateString > $vacation->dateEnd->format('Y-m-d')) {
                        $nDateString = $vacation->dateEnd->format('Y-m-d');
                        $newDate = new \DateTime($nDateString . ' 00:00:00');
                        $newDate->modify('+1 day');
                        $nDateString = $newDate->format('Y-m-d');
                        while($nDateString <= $oldEndDateString) {
                            $plans = $that->em->getShiftPlanRepository()->findBy(['dateString' => $nDateString]);
                            foreach ($plans as $plan) {
                                if($worker->productionLine && $worker->productionLine->id == $plan->productionLine && $worker->shift && $worker->shift == $plan->shift) {
                                    $insertNewSqlVal .= '(NULL,'.$worker->id.','.$plan->id.','.($worker->workerPosition ? $worker->workerPosition->id : 4).',12),';
                                }
                                foreach ($shiftBonusArr[$plan->shift][$plan->productionLine][$plan->name][$plan->datePlan->format('N')] as $sbn) {
                                    if(!$sbn['bonusEndDateString'] || $sbn['bonusEndDateString'] >= $plan->dateString) {
                                        if($sbn['bonusStartDateString'] && $sbn['bonusStartDateString'] > $plan->dateString) {
                                            continue;
                                        }
                                        $insertNewSqlVal .= '(NULL,'.$sbn['workerId'].','.$plan->id.','.$sbn['workerPosition'].',12),';
                                    }
                                }
                            }

                            $newDate = new \DateTime($nDateString . ' 00:00:00');
                            $newDate->modify('+1 day');
                            $nDateString = $newDate->format('Y-m-d');
                        }
                    }

                    if($insertNewSqlVal) {
                        $insertNewSqlVal = substr($insertNewSqlVal, 0, -1);
                        $qb = $that->em->getConnection()->prepare("
                            INSERT INTO worker_in_plan (id, worker_id, plan_id, worker_position_id, hours) 
                            VALUES ".$insertNewSqlVal."
                            ");
                        $qb->execute();
                    }
                    $that->em->flush();

                    $this->recountVacationLeft($worker);
                }
            } else {
                $that->flashMessage('Vyplňte začátek a konec absence.', 'error');
                $that->redrawControl('flashess');
                return;
            }
            $that->redirect('Vacation:default');
        };
        return $form;
    }

    public function recountVacationLeft($worker) {
        $year = date('Y');
        $startYear = new \DateTime($year. '-01-01 00:00:00');
        $endYear = new \DateTime($year. '-12-31 23:59:59');
        $vacationFund = $this->em->getVacationFundRepository()->findOneBy(['year' => $year, 'worker' => $worker]);
        if(!$vacationFund) {
            $vacationFund = new VacationFund();
            $vacationFund->setYear($year);
            $vacationFund->setWorker($worker);
            $vacationFund->setHoursBase(0);
            $vacationFund->setHoursPlus(0);
            $vacationFund->setHoursMinus(0);
            $this->em->persist($vacationFund);
            $this->em->flush();
        }
        $criteriaStart = new Criteria();
        $criteriaStart->andWhere(Criteria::expr()->eq('worker', $worker));
        $criteriaStart->andWhere(Criteria::expr()->eq('countHours', 1));
        $criteriaStart->andWhere(Criteria::expr()->gte('dateStart', $startYear));
        $criteriaStart->andWhere(Criteria::expr()->lte('dateStart', $endYear));
        $vacations = $this->em->getVacationRepository()->matching($criteriaStart);

        $vacationLeft = 0;
        $vacationLeft += intval($vacationFund->hoursBase);
        $vacationLeft += intval($vacationFund->hoursPlus);
        $vacationLeft -= intval($vacationFund->hoursMinus);
        foreach ($vacations as $vacation) {
            $vacationLeft -= intval($vacation->hours);
        }

        $worker->setHoursVacation($vacationLeft);
        $this->em->flush();
    }

    public function handleCheckVacationLeft($worker) {
        $entity = $this->em->getWorkerRepository()->find($worker);
        $this->payload->val = intval($entity->hoursVacation);
        $this->sendPayload();
    }

    public function handleHoursBaseChange() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getVacationFundRepository()->find($values['item']);
            if($values['val']) {
                $entity->setHoursBase(intval($values['val']));
            } else {
                $entity->setHoursBase(0);
            }
            $this->em->flush();
            $this->recountVacationLeft($entity->worker);
            $this->redrawControl('snippFund');
        }
    }

    public function handleHoursPlusChange() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getVacationFundRepository()->find($values['item']);
            if($values['val']) {
                $entity->setHoursPlus(intval($values['val']));
            } else {
                $entity->setHoursPlus(0);
            }
            $this->em->flush();
            $this->recountVacationLeft($entity->worker);
            $this->redrawControl('snippFund');
        }
    }

    public function handleHoursMinusChange() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getVacationFundRepository()->find($values['item']);
            if($values['val']) {
                $entity->setHoursMinus(intval($values['val']));
            } else {
                $entity->setHoursMinus(0);
            }
            $this->em->flush();
            $this->recountVacationLeft($entity->worker);
            $this->redrawControl('snippFund');
        }
    }
}