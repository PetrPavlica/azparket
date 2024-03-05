<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\ProductInPlan;
use App\Model\Database\Entity\ProductionPlan;
use App\Model\Database\Entity\ShiftPlan;
use App\Model\Database\Entity\WorkerInPlan;
use Doctrine\Common\Collections\Criteria;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use function Symfony\Component\String\b;

class ShiftPlanPresenter extends BasePresenter
{
    /** @var \App\Model\Facade\ShiftPlan @inject */
    public $shiftFac;

    /** @var integer @persistent */
    public $week;

    /** @var integer @persistent */
    public $year;

    /** @var integer @persistent */
    public $yeara;

    /** @var string @persistent */
    public $type;

    /**
     * ACL name='Plánování směn'
     * ACL rejection='Nemáte přístup ke plánování směn.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s plánováním směn'
     */
    public function renderDefault($type, $agency = null)
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

        if(!$this->isAjax()) {
            $this->template->workerSelect = $this->shiftFac->findPairsForSelect();
        }
        $this->template->workerPositionSelect = $this->em->getWorkerPositionRepository()->findBy(['id' => [1,2,3,4,5,6]]);

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
        $this->template->agency = $agency;

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
        $spotsA = array();

        foreach ($plans as $plan) {
            $keyString = $plan->dateString . '_' . $plan->name;
            $fillWorkers = 0;
            if($plan->active) {
                if($this->type == 1) {
                    $fillWorkers = 12;
                    if($plan->name == 1 && $plan->datePlan->format('N') == 1) {
                        $fillWorkers = 9;
                    }
                } else {
                    $fillWorkers = 6;
                    if($plan->name == 1 && $plan->datePlan->format('N') == 5) {
                        $fillWorkers = 5;
                    }
                }
            }
            $spotsA[$keyString] = array();
            $spotsA[$keyString]['shift'] = $plan->shift;
            $spotsA[$keyString]['fillWorkers'] = $fillWorkers;
            $spotsA[$keyString]['workers'] = array();
            if($plan->workers && count($plan->workers)) {
                foreach ($plan->workers as $conn) {
                    if(!$conn->worker) {
                        continue;
                    }
                    if($agency) {
                        if(!$conn->worker->agency) {
                            continue;
                        }
                    }

                    $thisSpot = array();
                    $thisSpot['plan'] = null;
                    $thisSpot['name'] = $conn->worker->surname . ' ' . $conn->worker->name;
                    $thisSpot['position'] = ($conn->workerPosition ? '('.$conn->workerPosition->short.')' : '');
                    $thisSpot['desc'] = $conn->worker->surname . ' ' . $conn->worker->name . ($conn->workerPosition ? ' ('.$conn->workerPosition->short.')' : '');
                    $thisSpot['shift'] = $plan->shift;
                    $thisSpot['minusLog'] = $conn->minusLog;

                    $foundCollision = 0;
                    if($conn->manual) {
                        $sPlans = $this->em->getShiftPlanRepository()->findBy(['dateString' => $plan->dateString]);
                        $counterForSelf = 0;
                        foreach ($sPlans as $sPlan) {
                            if($sPlan->workers && count($sPlan->workers)) {
                                foreach ($sPlan->workers as $cnn) {
                                    if($cnn->worker && $conn->worker->id == $cnn->worker->id) {
                                        $counterForSelf++;
                                    }
                                }
                            }
                        }
                        if($counterForSelf > 1) {
                            $foundCollision = 1;
                        }

                        if($plan->name == 1) {
                            $newDate = new \DateTime($plan->dateString . ' 00:00:00');
                            $newDate->modify('-1 day');
                            $nDateString = $newDate->format('Y-m-d');

                            $sPlans = $this->em->getShiftPlanRepository()->findBy(['dateString' => $nDateString, 'name' => 2]);
                            foreach ($sPlans as $sPlan) {
                                if($sPlan->workers && count($sPlan->workers)) {
                                    foreach ($sPlan->workers as $cnn) {
                                        if($cnn->worker && $conn->worker->id == $cnn->worker->id) {
                                            $foundCollision = 1;
                                        }
                                    }
                                }
                            }
                        } else {
                            $newDate = new \DateTime($plan->dateString . ' 00:00:00');
                            $newDate->modify('+1 day');
                            $nDateString = $newDate->format('Y-m-d');

                            $sPlans = $this->em->getShiftPlanRepository()->findBy(['dateString' => $nDateString, 'name' => 1]);
                            foreach ($sPlans as $sPlan) {
                                if($sPlan->workers && count($sPlan->workers)) {
                                    foreach ($sPlan->workers as $cnn) {
                                        if($cnn->worker && $conn->worker->id == $cnn->worker->id) {
                                            $foundCollision = 1;
                                        }
                                    }
                                }
                            }
                        }

                        if($conn->minusLog) {
                            $style = 'background-color: #c2c2c2;'; // Gray
                        } elseif($conn->plusLog) {
                            $style = 'background-color: #eff123;'; // Yellow
                        } else {
                            $style = 'background-color: #d8fed8;'; // Green light
                        }
                        if($foundCollision) {
                            $style .= ' color: red;';
                        }
                    } else {
                        if($conn->minusLog) {
                            $style = 'background-color: #c2c2c2;'; // Gray
                        } elseif($conn->plusLog) {
                            $style = 'background-color: #eff123;'; // Yellow
                        } else {
                            $style = 'background-color: lightgreen;'; // Green
                        }
                    }
                    if($conn->worker && $conn->worker->agency) {
                        if($conn->manual) {
                            if($conn->minusLog) {
                                $style = 'background-color: #c2c2c2;'; // Gray
                            } elseif($conn->plusLog) {
                                $style = 'background-color: #ffbf7d;'; // Orange
                            } else {
                                $style = 'background-color: #daedf4;'; // Blue light
                            }
                            if($foundCollision) {
                                $style .= ' color: red;';
                            }
                        } else {
                            if($conn->minusLog) {
                                $style = 'background-color: #c2c2c2;'; // Gray
                            } elseif($conn->plusLog) {
                                $style = 'background-color: #ffbf7d;'; // Orange
                            } else {
                                $style = 'background-color: #9fd1e2;'; // Blue
                            }
                        }
                    }

                    $keyForSort = ($conn->workerPosition ? $conn->workerPosition->id : 'Z') . '-' . $conn->id;
                    $thisSpot['style'] = $style;
                    $spotsA[$keyString]['workers'][$keyForSort] = $thisSpot;
                }
            }
        }

        $fillWorkersTotal = 0;
        $realWorkersTotal = 0;
        foreach ($spotsA as $keyString => $val) {
            if($val['fillWorkers']) {
                $fillWorkersTotal += $val['fillWorkers'];
                $realWorkers = 0;
                foreach ($val['workers'] as $worker) {
                    if(!$worker['minusLog']) {
                        $realWorkers++;
                    }
                }
                if($realWorkers > $val['fillWorkers']) {
                    $realWorkersTotal += $val['fillWorkers'];
                } else {
                    $realWorkersTotal += $realWorkers;
                }
            }

            ksort($spotsA[$keyString]['workers']);
        }

        $dateLoop = clone $startDate;
        $columnsA = array(-1);
        for($n = 0; $n < 7; $n++) {
            $columnsA[$dateLoop->format('Y-m-d')] = $dateLoop->format('j. n. Y');
            $dateLoop = $dateLoop->modify('+1 days');
        }


        $is3Shift = 0;
        $testFor3Shift = $this->em->getShiftPlanRepository()->find(1215);
        if($testFor3Shift->shift == 'v') {
            $is3Shift = 1;
        }
        $this->template->is3Shift = $is3Shift;
        $this->template->fillWorkersPercent = $fillWorkersTotal ? (round($realWorkersTotal/$fillWorkersTotal*100,1).'%') : '100%';
        $this->template->baseNumSpots = $this->type == 1 ? 13 : 6;
        $this->template->cusDays = ['', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'];
        $this->template->cusFloors = [1 => 'ranní', 2 => 'noční'];
        $this->template->spotsA = $spotsA;
        $this->template->columnsA = $columnsA;
        $this->template->floorsA = array(-3,-1,1,-2,2);
        $this->template->placesA = array(1);
    }

    /**
     * ACL name='Zobrazení stránky s plánováním fondů'
     */
    public function renderFund($type)
    {
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

        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
        $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', '1'));
        $plans = $this->em->getShiftPlanRepository()->matching($criteriaStart);
        $spotsA = $spotsB = $spotsC = $spotsD = array();
        $fpdA = $fpdB = $fpdC = $fpdD = array();
        $spotsCheck = array();

        foreach ($plans as $plan) {
            $keyString = $plan->datePlan->format('n-j');
            $spotsCheck[$keyString] = $keyString;
            $keyFpd = $plan->datePlan->format('n');
            if($plan->shift == 'A') {
                $spotsA[$keyString] = $plan;
                if(!isset($fpdA[$keyFpd])) {
                    $fpdA[$keyFpd] = 0;
                }
                $fpdA[$keyFpd] += $plan->active ? 12 : 0;
            } elseif($plan->shift == 'B') {
                $spotsB[$keyString] = $plan;
                if(!isset($fpdB[$keyFpd])) {
                    $fpdB[$keyFpd] = 0;
                }
                $fpdB[$keyFpd] += $plan->active ? 12 : 0;
            } elseif($plan->shift == 'C') {
                $spotsC[$keyString] = $plan;
                if(!isset($fpdC[$keyFpd])) {
                    $fpdC[$keyFpd] = 0;
                }
                $fpdC[$keyFpd] += $plan->active ? 12 : 0;
            } elseif($plan->shift == 'D') {
                $spotsD[$keyString] = $plan;
                if(!isset($fpdD[$keyFpd])) {
                    $fpdD[$keyFpd] = 0;
                }
                $fpdD[$keyFpd] += $plan->active ? 12 : 0;
            }
        }

        $this->template->cusDays = array_merge(['M'], range(1, 31), ['F.P.D.']);
        $this->template->cusMonths = array_merge([''], range(1, 12));
        $this->template->spotsA = $spotsA;
        $this->template->spotsB = $spotsB;
        $this->template->spotsC = $spotsC;
        $this->template->spotsD = $spotsD;
        $this->template->fpdA = $fpdA;
        $this->template->fpdB = $fpdB;
        $this->template->fpdC = $fpdC;
        $this->template->fpdD = $fpdD;
        $this->template->spotsCheck = $spotsCheck;
    }

    /**
     * ACL name='Zobrazení stránky s přehledem fondů'
     */
    public function renderOverview($type)
    {
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

        $workersHours = array();
        $workersHours['first'] = array();
        $workersHours['second'] = array();
        $workersHours['total'] = array();
        $fpd = array();
        $fpd['first'] = array('A' => 0, 'B' => 0, 'C' => 0, 'D' => 0);
        $fpd['second'] = array('A' => 0, 'B' => 0, 'C' => 0, 'D' => 0);
        $fpd['total'] = array('A' => 0, 'B' => 0, 'C' => 0, 'D' => 0);
        foreach (['01','02','03','04','05','06','07','08','09','10','11','12'] as $waKey) {
            $workersHours[intval($waKey)] = array();
            $fpd[intval($waKey)] = array('A' => 0, 'B' => 0, 'C' => 0, 'D' => 0);

            $startMonthDay = new \DateTime();
            $startMonthDay->setTimestamp(strtotime('first day of this month '.$this->yeara.'-' . $waKey . '-01'));
            $endMonthDay = new \DateTime();
            $endMonthDay->setTimestamp(strtotime('last day of this month '.$this->yeara.'-' . $waKey . '-01'));
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

            foreach ($result as $workerId => $hours) {
                if(!isset($workersHours[intval($waKey)][$workerId])) {
                    $workersHours[intval($waKey)][$workerId] = 0;
                }
                if(!isset($workersHours['first'][$workerId])) {
                    $workersHours['first'][$workerId] = 0;
                    $workersHours['second'][$workerId] = 0;
                    $workersHours['total'][$workerId] = 0;
                }
                $workersHours[intval($waKey)][$workerId] += $hours;
                if(intval($waKey) <= 6) {
                    $workersHours['first'][$workerId] += $hours;
                } else {
                    $workersHours['second'][$workerId] += $hours;
                }
                $workersHours['total'][$workerId] += $hours;
            }

            $criteriaStart = new Criteria();
            $criteriaStart->where(Criteria::expr()->gte('datePlan', $startMonthDay));
            $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endMonthDay));
            $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', '1'));
            $plans = $this->em->getShiftPlanRepository()->matching($criteriaStart);

            foreach ($plans as $plan) {
                if(in_array($plan->shift, ['A', 'B', 'C', 'D'])) {
                    $fpd[intval($waKey)][$plan->shift] += $plan->active ? 12 : 0;
                    if(intval($waKey) <= 6) {
                        $fpd['first'][$plan->shift] += $plan->active ? 12 : 0;
                    } else {
                        $fpd['second'][$plan->shift] += $plan->active ? 12 : 0;
                    }
                    $fpd['total'][$plan->shift] += $plan->active ? 12 : 0;
                }
            }
        }

        $workers = $this->em->getWorkerRepository()->findBy(['active' => 1, 'workerPosition' => [1,2,3,4,5,6]], ['surname' => 'ASC']);
        $this->template->workers = $workers;
        $this->template->workersHours = $workersHours;
        $this->template->fpd = $fpd;
        $this->template->cusMonths = ['',1,2,3,4,5,6,'first',7,8,9,10,11,12,'second','total'];
        $this->template->fpd = $fpd;
    }

    public function handlePlanFromThisWeek($type) {
        $weekStart = new \DateTime();
        $weekStart->setISODate($this->year, $this->week);
        $weekStart = new \DateTime($weekStart->format('Y').'-'.$weekStart->format('m').'-'.$weekStart->format('d').' 00:00:00');
        $startDate = clone $weekStart;
        $endDate = clone $weekStart;
        $endDate = $endDate->modify('+6 days');
        $endDate = new \DateTime($endDate->format('Y') . '-12-31');
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);

        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
        $plans = $this->em->getShiftPlanRepository()->matching($criteriaStart);

        $workers = $this->em->getWorkerRepository()->findBy(['active' => 1, 'workerPosition' => [1,2,3,4,5,6]]);
        $workerArr = array(
            'A' => ['1' => [], '2' => [], 'N' => []],
            'B' => ['1' => [], '2' => [], 'N' => []],
            'C' => ['1' => [], '2' => [], 'N' => []],
            'D' => ['1' => [], '2' => [], 'N' => []],
            'N' => ['1' => [], '2' => [], 'N' => []]
        );
        $workerChangeArr = array(
            'A' => ['1' => [], '2' => [], 'N' => []],
            'B' => ['1' => [], '2' => [], 'N' => []],
            'C' => ['1' => [], '2' => [], 'N' => []],
            'D' => ['1' => [], '2' => [], 'N' => []],
            'N' => ['1' => [], '2' => [], 'N' => []]
        );
        foreach ($workers as $worker) {
            if($worker->shift) {
                if($worker->shift != 8) {
                    if($worker->productionLine && ($worker->productionLine->id == 1 || $worker->productionLine->id  == 2)) {
                        $workerArr[$worker->shift][$worker->productionLine->id][$worker->id] = $worker;
                    } else {
                        $workerArr[$worker->shift]['N'][$worker->id] = $worker;
                    }
                }
            } else {
                if($worker->productionLine && ($worker->productionLine->id == 1 || $worker->productionLine->id  == 2)) {
                    $workerArr['N'][$worker->productionLine->id][$worker->id] = $worker;
                } else {
                    $workerArr['N']['N'][$worker->id] = $worker;
                }
            }
            if($worker->startDateChange) {
                if($worker->shiftChange) {
                    if($worker->shiftChange != 8) {
                        if($worker->productionLineChange && ($worker->productionLineChange->id == 1 || $worker->productionLineChange->id  == 2)) {
                            $workerChangeArr[$worker->shiftChange][$worker->productionLineChange->id][$worker->id] = $worker;
                        } elseif($worker->productionLine && ($worker->productionLine->id == 1 || $worker->productionLine->id  == 2)) {
                            $workerChangeArr[$worker->shiftChange][$worker->productionLine->id][$worker->id] = $worker;
                        } else {
                            $workerChangeArr[$worker->shiftChange]['N'][$worker->id] = $worker;
                        }
                    }
                } elseif($worker->shift) {
                    if($worker->shift != 8) {
                        if($worker->productionLineChange && ($worker->productionLineChange->id == 1 || $worker->productionLineChange->id  == 2)) {
                            $workerChangeArr[$worker->shift][$worker->productionLineChange->id][$worker->id] = $worker;
                        } elseif($worker->productionLine && ($worker->productionLine->id == 1 || $worker->productionLine->id  == 2)) {
                            $workerChangeArr[$worker->shift][$worker->productionLine->id][$worker->id] = $worker;
                        } else {
                            $workerChangeArr[$worker->shift]['N'][$worker->id] = $worker;
                        }
                    }
                } else {
                    if($worker->productionLineChange && ($worker->productionLineChange->id == 1 || $worker->productionLineChange->id  == 2)) {
                        $workerChangeArr['N'][$worker->productionLineChange->id][$worker->id] = $worker;
                    } elseif($worker->productionLine && ($worker->productionLine->id == 1 || $worker->productionLine->id  == 2)) {
                        $workerChangeArr['N'][$worker->productionLine->id][$worker->id] = $worker;
                    } else {
                        $workerChangeArr['N']['N'][$worker->id] = $worker;
                    }
                }
            }
        }


        $shiftBonuses = $this->em->getShiftBonusRepository()->findAll();
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
            $shiftBonusArr[$shiftBonus->shift][$shiftBonus->productionLine][$shiftBonus->name][$shiftBonus->dayOfWeek][] =
                ['bonusEndDateString' => ($shiftBonus->dateEnd ? $shiftBonus->dateEnd->format('Y-m-d') : ''),
                    'bonusStartDateString' => ($shiftBonus->dateStart ? $shiftBonus->dateStart->format('Y-m-d') : ''),
                    'workerId' => $shiftBonus->worker->id,
                    'startDateString' => ($shiftBonus->worker->startDate ? $shiftBonus->worker->startDate->format('Y-m-d') : ''),
                    'endDateString' => ($shiftBonus->worker->endDate ? $shiftBonus->worker->endDate->format('Y-m-d') : ''),
                    'workerPosition' => ($shiftBonus->worker->workerPosition ? $shiftBonus->worker->workerPosition->id : 4)
                ];
        }

        $deleteOldSqlVal = '';
        $insertNewSqlVal = '';
        $changeManualSqlVal = '';
        $insertNewCheck = array();
        foreach ($plans as $plan) {
            $planWorkersArr = [];
            $planWorkersToConnect = [];
            if($type) {
                $planWorkers = $this->em->getWorkerInPlanRepository()->findBy(['manual' => 1, 'plan' => $plan]);
                foreach ($planWorkers as $planWorker) {
                    $planWorkersArr[$planWorker->worker->id] = $planWorker->worker->id;
                    $planWorkersToConnect[$planWorker->worker->id] = $planWorker->id;
                }
            }
            $deleteOldSqlVal .= $plan->id . ',';
            if($plan->active) {
                if($planWorkersArr) {
                    foreach ($workerArr[$plan->shift][$plan->productionLine] as $wrk)  {
                        if($wrk->startDate && $wrk->startDate->format('Y-m-d') > $plan->dateString) {
                            continue;
                        }
                        if($wrk->endDate && $wrk->endDate->format('Y-m-d') < $plan->dateString) {
                            continue;
                        }
                        if($wrk->startDateChange && $wrk->startDateChange->format('Y-m-d') <= $plan->dateString) {
                            continue;
                        }
                        if(isset($insertNewCheck[$wrk->id.'_'.$plan->id])) {
                            continue;
                        }
                        $insertNewCheck[$wrk->id.'_'.$plan->id] = 1;

                        if(!in_array($wrk->id, $planWorkersArr)) {
                            $insertNewSqlVal .= '(NULL,'.$wrk->id.','.$plan->id.','.($wrk->workerPosition ? $wrk->workerPosition->id : 4).',12),';
                        } else {
                            $changeManualSqlVal .= $planWorkersToConnect[$wrk->id] . ',';
                        }
                    }
                    foreach ($workerChangeArr[$plan->shift][$plan->productionLine] as $wrk)  {
                        if($wrk->startDate && $wrk->startDate->format('Y-m-d') > $plan->dateString) {
                            continue;
                        }
                        if($wrk->endDate && $wrk->endDate->format('Y-m-d') < $plan->dateString) {
                            continue;
                        }
                        if($wrk->startDateChange && $wrk->startDateChange->format('Y-m-d') > $plan->dateString) {
                            continue;
                        }
                        if(isset($insertNewCheck[$wrk->id.'_'.$plan->id])) {
                            continue;
                        }
                        $insertNewCheck[$wrk->id.'_'.$plan->id] = 1;

                        if(!in_array($wrk->id, $planWorkersArr)) {
                            $insertNewSqlVal .= '(NULL,'.$wrk->id.','.$plan->id.','.($wrk->workerPosition ? $wrk->workerPosition->id : 4).',12),';
                        } else {
                            $changeManualSqlVal .= $planWorkersToConnect[$wrk->id] . ',';
                        }
                    }
                    foreach ($shiftBonusArr[$plan->shift][$plan->productionLine][$plan->name][$plan->datePlan->format('N')] as $sbn) {
                        if($sbn['startDateString'] && $sbn['startDateString'] > $plan->dateString) {
                            continue;
                        }
                        if($sbn['endDateString'] && $sbn['endDateString'] < $plan->dateString) {
                            continue;
                        }
                        if(!$sbn['bonusEndDateString'] || $sbn['bonusEndDateString'] >= $plan->dateString) {
                            if($sbn['bonusStartDateString'] && $sbn['bonusStartDateString'] > $plan->dateString) {
                                continue;
                            }
                            if(isset($insertNewCheck[$sbn['workerId'].'_'.$plan->id])) {
                                continue;
                            }
                            $insertNewCheck[$sbn['workerId'].'_'.$plan->id] = 1;

                            if(!in_array($sbn['workerId'], $planWorkersArr)) {
                                $insertNewSqlVal .= '(NULL,'.$sbn['workerId'].','.$plan->id.','.$sbn['workerPosition'].',12),';
                            } else {
                                $changeManualSqlVal .= $planWorkersToConnect[$sbn['workerId']] . ',';
                            }
                        }
                    }
                } else {
                    foreach ($workerArr[$plan->shift][$plan->productionLine] as $wrk)  {
                        if($wrk->startDate && $wrk->startDate->format('Y-m-d') > $plan->dateString) {
                            continue;
                        }
                        if($wrk->endDate && $wrk->endDate->format('Y-m-d') < $plan->dateString) {
                            continue;
                        }
                        if($wrk->startDateChange && $wrk->startDateChange->format('Y-m-d') <= $plan->dateString) {
                            continue;
                        }
                        if(isset($insertNewCheck[$wrk->id.'_'.$plan->id])) {
                            continue;
                        }
                        $insertNewCheck[$wrk->id.'_'.$plan->id] = 1;

                        $insertNewSqlVal .= '(NULL,'.$wrk->id.','.$plan->id.','.($wrk->workerPosition ? $wrk->workerPosition->id : 4).',12),';
                    }
                    foreach ($workerChangeArr[$plan->shift][$plan->productionLine] as $wrk)  {
                        if($wrk->startDate && $wrk->startDate->format('Y-m-d') > $plan->dateString) {
                            continue;
                        }
                        if($wrk->endDate && $wrk->endDate->format('Y-m-d') < $plan->dateString) {
                            continue;
                        }
                        if($wrk->startDateChange && $wrk->startDateChange->format('Y-m-d') > $plan->dateString) {
                            continue;
                        }
                        if(isset($insertNewCheck[$wrk->id.'_'.$plan->id])) {
                            continue;
                        }
                        $insertNewCheck[$wrk->id.'_'.$plan->id] = 1;

                        $insertNewSqlVal .= '(NULL,'.$wrk->id.','.$plan->id.','.($wrk->workerPosition ? $wrk->workerPosition->id : 4).',12),';
                    }
                    foreach ($shiftBonusArr[$plan->shift][$plan->productionLine][$plan->name][$plan->datePlan->format('N')] as $sbn) {
                        if($sbn['startDateString'] && $sbn['startDateString'] > $plan->dateString) {
                            continue;
                        }
                        if($sbn['endDateString'] && $sbn['endDateString'] < $plan->dateString) {
                            continue;
                        }
                        if(!$sbn['bonusEndDateString'] || $sbn['bonusEndDateString'] >= $plan->dateString) {
                            if($sbn['bonusStartDateString'] && $sbn['bonusStartDateString'] > $plan->dateString) {
                                continue;
                            }
                            if(isset($insertNewCheck[$sbn['workerId'].'_'.$plan->id])) {
                                continue;
                            }
                            $insertNewCheck[$sbn['workerId'].'_'.$plan->id] = 1;

                            $insertNewSqlVal .= '(NULL,'.$sbn['workerId'].','.$plan->id.','.$sbn['workerPosition'].',12),';
                        }
                    }
                }
            }
        }
        if($deleteOldSqlVal) {
            $deleteOldSqlVal = substr($deleteOldSqlVal, 0, -1);
        }
        if($insertNewSqlVal) {
            $insertNewSqlVal = substr($insertNewSqlVal, 0, -1);
        }
        if($changeManualSqlVal) {
            $changeManualSqlVal = substr($changeManualSqlVal, 0, -1);
        }

        if($deleteOldSqlVal) {
            $qb = $this->em->getConnection()->prepare("
                DELETE FROM worker_in_plan 
                WHERE plan_id IN (".$deleteOldSqlVal.")
                " . ($type ? " AND (manual = 0 OR manual IS NULL)" : "") . "
                ");
            $qb->execute();
        }
        if($insertNewSqlVal) {
            $qb = $this->em->getConnection()->prepare("
                INSERT INTO worker_in_plan (id, worker_id, plan_id, worker_position_id, hours) 
                VALUES ".$insertNewSqlVal."
                ");
            $qb->execute();
        }
        $this->em->flush();
        if($changeManualSqlVal) {
            $qb = $this->em->getConnection()->prepare("
                UPDATE worker_in_plan SET manual = NULL
                WHERE id IN (".$changeManualSqlVal.")
                ");
            $qb->execute();
            $this->em->flush();
        }


        $criteriaVacation = new Criteria();
        $criteriaVacation->where(Criteria::expr()->gte('dateEnd', $startDate));
        $criteriaVacation->andWhere(Criteria::expr()->lte('dateStart', $endDate));
        $vacations = $this->em->getVacationRepository()->matching($criteriaVacation);
        $deleteVacationSql = '';
        $checkDateStart = $startDate->format('Y-m-d');
        $checkDateEnd = $endDate->format('Y-m-d');
        foreach ($vacations as $vacation) {
            $maxDateString = $vacation->dateEnd->format('Y-m-d');
            $nDateString = $vacation->dateStart->format('Y-m-d');
            while($nDateString <= $maxDateString) {
                if($nDateString >= $checkDateStart && $nDateString <= $checkDateEnd) {
                    $deleteVacationSql .= '(worker_in_plan.worker_id = '.$vacation->worker->id.' AND shift_plan.date_string = \''.$nDateString.'\') OR';
                }

                $newDate = new \DateTime($nDateString . ' 00:00:00');
                $newDate->modify('+1 day');
                $nDateString = $newDate->format('Y-m-d');
            }
        }

        if($deleteVacationSql) {
            $deleteVacationSql = substr($deleteVacationSql, 0, -3);
            $qb = $this->em->getConnection()->prepare("
                DELETE worker_in_plan FROM worker_in_plan 
                INNER JOIN shift_plan ON shift_plan.id = worker_in_plan.plan_id 
                WHERE ".$deleteVacationSql."
                ");
            $qb->execute();
            $this->em->flush();
        }

        $this->redirect('ShiftPlan:default', ['type' => $this->type, 'week' => $this->week, 'year' => $this->year]);
    }

    public function handlePlanThisWeek() {
        $values = $this->request->getPost();

        $weekStart = new \DateTime();
        $weekStart->setISODate($this->year, $this->week);
        $weekStart = new \DateTime($weekStart->format('Y').'-'.$weekStart->format('m').'-'.$weekStart->format('d').' 00:00:00');
        $startDate = clone $weekStart;
        $endDate = clone $weekStart;
        $startDate->setTime(0,0,0);
        $endDate = $endDate->modify('+6 days');
        $endDate->setTime(23,59,59);

        $productionLine = array(1, 2);
        foreach ($productionLine as $lineId) {
            $criteriaStart = new Criteria();
            $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
            $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
            $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', $lineId));
            $oldPlans = $this->em->getShiftPlanRepository()->matching($criteriaStart);
            $needPlans = 0;
            if($oldPlans && count($oldPlans)) {
                foreach ($oldPlans as $plan) {
                    if($plan->workers && count($plan->workers)) {
                        foreach ($plan->workers as $conn) {
                            $this->em->remove($conn);
                        }
                    }
                }
                $this->em->flush();
            } else {
                $needPlans = 1;
            }


            $workerPositions = $this->em->getWorkerPositionRepository()->findBy([]);
            $positionArr = array();
            foreach ($workerPositions as $workerPosition) {
                $positionArr[$workerPosition->id] = $workerPosition;
            }


            $workers = $this->em->getWorkerRepository()->findBy(['active' => 1, 'workerPosition' => [1,2,3,4,5,6]]);
            $workersArr = $workersHours = array();
            $workersHours[$startDate->format('Y-m')] = array();
            $workersHours[$endDate->format('Y-m')] = array();

            $aworkersAnyShift = $aworkersA = $aworkersB = $aworkersC = $aworkersD = array();
            $aworkersAnyShiftOther = $aworkersAOther = $aworkersBOther = $aworkersCOther = $aworkersDOther = array();
            $workersAnyShift = $workersA = $workersB = $workersC = $workersD = array();
            $workersAnyShiftOther = $workersAOther = $workersBOther = $workersCOther = $workersDOther = array();
            foreach ($workers as $worker) {
                if(!$worker->productionLine || $worker->productionLine->id == $lineId) {
                    if($worker->shift == 'A') {
                        if(!$worker->agency && $worker->timeFund == 0) {
                            $aworkersA[$worker->id] = $worker;
                        } else {
                            $aworkersAOther[$worker->id] = $worker;
                        }
                    } elseif($worker->shift == 'B') {
                        if(!$worker->agency && $worker->timeFund == 0) {
                            $aworkersB[$worker->id] = $worker;
                        } else {
                            $aworkersBOther[$worker->id] = $worker;
                        }
                    } elseif($worker->shift == 'C') {
                        if(!$worker->agency && $worker->timeFund == 0) {
                            $aworkersC[$worker->id] = $worker;
                        } else {
                            $aworkersCOther[$worker->id] = $worker;
                        }
                    } elseif($worker->shift == 'D') {
                        if(!$worker->agency && $worker->timeFund == 0) {
                            $aworkersD[$worker->id] = $worker;
                        } else {
                            $aworkersDOther[$worker->id] = $worker;
                        }
                    } elseif($worker->shift != '8') {
                        if(!$worker->agency && $worker->timeFund == 0) {
                            $aworkersAnyShift[$worker->id] = $worker;
                        } else {
                            $aworkersAnyShiftOther[$worker->id] = $worker;
                        }
                    }
                    $workersArr[$worker->id] = $worker;
                }

                foreach ($workersHours as $waKey => $waValue) {
                    $workersHours[$waKey][$worker->id] = 0;
                }
            }

            foreach ($workersHours as $waKey => $waValue) {
                $startMonthDay = new \DateTime();
                $startMonthDay->setTimestamp(strtotime('first day of this month ' . $waKey . '-01'));
                $endMonthDay = new \DateTime();
                $endMonthDay->setTimestamp(strtotime('last day of this month ' . $waKey . '-01'));
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

                foreach ($result as $workerId => $hours) {
                    $workersHours[$waKey][$workerId] += $hours;
                }
            }

            foreach ($workersHours as $waKey => $waValue) {
                asort($workersHours[$waKey]);
            }

            foreach ($workersHours as $waKey => $waValue) {
                foreach ($waValue as $workerId => $hours) {
                    if(isset($aworkersA[$workerId])) {
                        $workersA[$workerId] = $aworkersA[$workerId];
                    } elseif(isset($aworkersB[$workerId])) {
                        $workersB[$workerId] = $aworkersB[$workerId];
                    } elseif(isset($aworkersC[$workerId])) {
                        $workersC[$workerId] = $aworkersC[$workerId];
                    } elseif(isset($aworkersD[$workerId])) {
                        $workersD[$workerId] = $aworkersD[$workerId];
                    } elseif(isset($aworkersAnyShift[$workerId])) {
                        $workersAnyShift[$workerId] = $aworkersAnyShift[$workerId];
                    } elseif(isset($aworkersAOther[$workerId])) {
                        $workersAOther[$workerId] = $aworkersAOther[$workerId];
                    } elseif(isset($aworkersBOther[$workerId])) {
                        $workersBOther[$workerId] = $aworkersBOther[$workerId];
                    } elseif(isset($aworkersCOther[$workerId])) {
                        $workersCOther[$workerId] = $aworkersCOther[$workerId];
                    } elseif(isset($aworkersDOther[$workerId])) {
                        $workersDOther[$workerId] = $aworkersDOther[$workerId];
                    } elseif(isset($aworkersAnyShiftOther[$workerId])) {
                        $workersAnyShiftOther[$workerId] = $aworkersAnyShiftOther[$workerId];
                    }
                }
                break;
            }

            $startDateLast = clone $startDate;
            $startDateLast = $startDateLast->modify('-7 days');
            $endDateLast = clone $startDateLast;
            $startDateLast->setTime(0,0,0);
            $endDateLast = $endDateLast->modify('+6 days');
            $endDateLast->setTime(23,59,59);

            $shiftTrans = ['D' => 'B', 'B' => 'C', 'C' => 'A', 'A' => 'D'];

            $criteriaStart = new Criteria();
            $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDateLast));
            $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDateLast));
            $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', $lineId));
            $lastPlans = $this->em->getShiftPlanRepository()->matching($criteriaStart);
            if($needPlans) {
                foreach ($lastPlans as $plan) {
                    $newPlan = new ShiftPlan();
                    $newPlan->setName($plan->name);
                    $newPlan->setProductionLine($plan->productionLine);
                    $newPlan->setShift($shiftTrans[$plan->shift]);
                    $nDate = clone $plan->datePlan;
                    $nDate->modify('+7 days');
                    $newPlan->setDateString($nDate->format('Y-m-d'));
                    $newPlan->setDatePlan($nDate);

                    $this->em->persist($newPlan);
                }
            }
            $this->em->flush();

            $criteriaStart = new Criteria();
            $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
            $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
            $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', $lineId));
            $criteriaStart->andWhere(Criteria::expr()->eq('active', 1));
            $thisPlans = $this->em->getShiftPlanRepository()->matching($criteriaStart);

            $workersAnyShift = $this->shuffle_assoc($workersAnyShift);
            $takenWorkersA = $takenWorkersB = $takenWorkersC = $takenWorkersD = array();
            $takeWorkerToShifts = ['takenWorkersA' => 'workersA','takenWorkersB' => 'workersB','takenWorkersC' => 'workersC','takenWorkersD' => 'workersD'];
            $takeWorkerToShiftsOther = ['takenWorkersA' => 'workersAOther','takenWorkersB' => 'workersBOther','takenWorkersC' => 'workersCOther','takenWorkersD' => 'workersDOther'];
            $specialWorkers = array();
            foreach ($thisPlans as $plan) {
                $takeShift = 'workersD';
                $takeShiftOther = 'workersDOther';
                $takeWorkers = 'takenWorkersD';

                $takeShiftBonus = 'workersA';
                $takeShiftOtherBonus = 'workersAOther';
                $takeWorkersBonus = 'takenWorkersA';
                if($plan->shift == 'A') {
                    $takeShift = 'workersA';
                    $takeShiftOther = 'workersAOther';
                    $takeWorkers = 'takenWorkersA';

                    $takeShiftBonus = 'workersD';
                    $takeShiftOtherBonus = 'workersDOther';
                    $takeWorkersBonus = 'takenWorkersD';
                } elseif($plan->shift == 'B') {
                    $takeShift = 'workersB';
                    $takeShiftOther = 'workersBOther';
                    $takeWorkers = 'takenWorkersB';

                    $takeShiftBonus = 'workersC';
                    $takeShiftOtherBonus = 'workersCOther';
                    $takeWorkersBonus = 'takenWorkersC';
                } elseif($plan->shift == 'C') {
                    $takeShift = 'workersC';
                    $takeShiftOther = 'workersCOther';
                    $takeWorkers = 'takenWorkersC';

                    $takeShiftBonus = 'workersB';
                    $takeShiftOtherBonus = 'workersBOther';
                    $takeWorkersBonus = 'takenWorkersB';
                }

                if(!$$takeWorkers) {
                    $findLeaders = 0;
                    foreach($$takeShift as $worker) {
                        if($worker->workerPosition && $worker->workerPosition->id == 1) {
                            if($this->check_no_vacation($worker, $plan->datePlan)) {
                                $$takeWorkers[$worker->id] = $worker;
                                unset($$takeShift[$worker->id]);
                                $findLeaders++;
                                $specialWorkers[$worker->id] = '1';
                            }
                        }

                        if($findLeaders >= 1) {
                            break;
                        }
                    }
                    if($findLeaders < 1) {
                        foreach($workersAnyShift as $worker) {
                            if($worker->workerPosition && $worker->workerPosition->id == 1) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($workersAnyShift[$worker->id]);
                                    $findLeaders++;
                                    $specialWorkers[$worker->id] = '1';
                                }
                            }

                            if($findLeaders >= 1) {
                                break;
                            }
                        }
                    }
                    if($findLeaders < 1) {
                        foreach($$takeShift as $worker) {
                            if($worker->workerPosition && $worker->workerPosition->id == 2) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($$takeShift[$worker->id]);
                                    $findLeaders++;
                                    $specialWorkers[$worker->id] = '1';
                                }
                            }

                            if($findLeaders >= 1) {
                                break;
                            }
                        }
                    }
                    if($findLeaders < 1) {
                        foreach($$takeShiftOther as $worker) {
                            if($worker->workerPosition && $worker->workerPosition->id == 2) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($$takeShiftOther[$worker->id]);
                                    $findLeaders++;
                                    $specialWorkers[$worker->id] = '1';
                                }
                            }

                            if($findLeaders >= 1) {
                                break;
                            }
                        }
                    }

                    $findLeadersSub = 0;
                    foreach($$takeShift as $worker) {
                        if($worker->workerPosition && $worker->workerPosition->id == 2) {
                            if($this->check_no_vacation($worker, $plan->datePlan)) {
                                $$takeWorkers[$worker->id] = $worker;
                                unset($$takeShift[$worker->id]);
                                $findLeadersSub++;
                                $specialWorkers[$worker->id] = '2';
                            }
                        }

                        if($findLeadersSub >= 1) {
                            break;
                        }
                    }
                    if($findLeadersSub < 1) {
                        foreach($$takeShiftOther as $worker) {
                            if($worker->workerPosition && $worker->workerPosition->id == 2) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($$takeShiftOther[$worker->id]);
                                    $findLeadersSub++;
                                    $specialWorkers[$worker->id] = '2';
                                }
                            }

                            if($findLeadersSub >= 1) {
                                break;
                            }
                        }
                    }
                    if($findLeadersSub < 1) {
                        foreach($workersAnyShift as $worker) {
                            if($worker->workerPosition && $worker->workerPosition->id == 2) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($workersAnyShift[$worker->id]);
                                    $findLeadersSub++;
                                    $specialWorkers[$worker->id] = '2';
                                }
                            }

                            if($findLeadersSub >= 1) {
                                break;
                            }
                        }
                    }
                    if($findLeadersSub < 1) {
                        foreach($workersAnyShiftOther as $worker) {
                            if($worker->workerPosition && $worker->workerPosition->id == 2) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($workersAnyShiftOther[$worker->id]);
                                    $findLeadersSub++;
                                    $specialWorkers[$worker->id] = '2';
                                }
                            }

                            if($findLeadersSub >= 1) {
                                break;
                            }
                        }
                    }
                    if($findLeadersSub < 1) {
                        foreach($$takeShift as $worker) {
                            if($worker->workerPosition && $worker->workerPosition->id == 1) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($$takeShift[$worker->id]);
                                    $findLeadersSub++;
                                    $specialWorkers[$worker->id] = '2';
                                }
                            }

                            if($findLeadersSub >= 1) {
                                break;
                            }
                        }
                    }

                    if($lineId == 1) {
                        $findPackage = 0;
                        foreach($$takeShift as $worker) {
                            if($worker->workerPosition && $worker->workerPosition->id == 3) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($$takeShift[$worker->id]);
                                    $findPackage++;
                                    $specialWorkers[$worker->id] = '3';
                                }
                            }

                            if($findPackage >= 2) {
                                break;
                            }
                        }
                        if($findPackage < 2) {
                            foreach($workersAnyShift as $worker) {
                                if($worker->workerPosition && $worker->workerPosition->id == 3) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShift[$worker->id]);
                                        $findPackage++;
                                        $specialWorkers[$worker->id] = '3';
                                    }
                                }

                                if($findPackage >= 2) {
                                    break;
                                }
                            }
                        }
                        if($findPackage < 2) {
                            foreach($$takeShiftOther as $worker) {
                                if($worker->workerPosition && $worker->workerPosition->id == 3) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($$takeShiftOther[$worker->id]);
                                        $findPackage++;
                                        $specialWorkers[$worker->id] = '3';
                                    }
                                }

                                if($findPackage >= 2) {
                                    break;
                                }
                            }
                        }
                        if($findPackage < 2) {
                            foreach($workersAnyShiftOther as $worker) {
                                if($worker->workerPosition && $worker->workerPosition->id == 3) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShiftOther[$worker->id]);
                                        $findPackage++;
                                        $specialWorkers[$worker->id] = '3';
                                    }
                                }

                                if($findPackage >= 2) {
                                    break;
                                }
                            }
                        }
                    }

                    $findMainHang = 0;
                    foreach($$takeShift as $worker) {
                        if(!$worker->agency && $worker->timeFund == 0 && $worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                            if($this->check_no_vacation($worker, $plan->datePlan)) {
                                $$takeWorkers[$worker->id] = $worker;
                                unset($$takeShift[$worker->id]);
                                $findMainHang++;
                                $specialWorkers[$worker->id] = '28';
                            }
                        }

                        if($findMainHang) {
                            break;
                        }
                    }
                    if(!$findMainHang) {
                        foreach($workersAnyShift as $worker) {
                            if(!$worker->agency && $worker->timeFund == 0 && $worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($workersAnyShift[$worker->id]);
                                    $findMainHang++;
                                    $specialWorkers[$worker->id] = '28';
                                }
                            }

                            if($findMainHang) {
                                break;
                            }
                        }
                    }

                    if($lineId == 1) {
                        $findManipulMen = 0;
                        $findManipulWomen = 0;
                        foreach($$takeShift as $worker) {
                            if($worker->workerPosition && $worker->workerPosition->id == 4 && $findManipulMen < 4) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($$takeShift[$worker->id]);
                                    $findManipulMen++;
                                }
                            }

                            if($worker->workerPosition && $worker->workerPosition->id == 5 && $findManipulWomen < 4) {
                                if($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($$takeShift[$worker->id]);
                                    $findManipulWomen++;
                                }
                            }

                            if($findManipulMen >= 4 && $findManipulWomen >= 4) {
                                break;
                            }
                        }

                        if($findManipulMen < 4 || $findManipulWomen < 4) {
                            foreach($$takeShiftOther as $worker) {
                                if($worker->workerPosition && $worker->workerPosition->id == 4 && $findManipulMen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($$takeShiftOther[$worker->id]);
                                        $findManipulMen++;
                                    }
                                }

                                if($worker->workerPosition && $worker->workerPosition->id == 5 && $findManipulWomen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($$takeShiftOther[$worker->id]);
                                        $findManipulWomen++;
                                    }
                                }

                                if($findManipulMen >= 4 && $findManipulWomen >= 4) {
                                    break;
                                }
                            }
                        }

                        if($findManipulMen < 4 || $findManipulWomen < 4) {
                            foreach($workersAnyShift as $worker) {
                                if($worker->workerPosition && $worker->workerPosition->id == 4 && $findManipulMen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShift[$worker->id]);
                                        $findManipulMen++;
                                    }
                                }

                                if($worker->workerPosition && $worker->workerPosition->id == 5 && $findManipulWomen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShift[$worker->id]);
                                        $findManipulWomen++;
                                    }
                                }

                                if($findManipulMen >= 4 && $findManipulWomen >= 4) {
                                    break;
                                }
                            }
                        }

                        if($findManipulMen < 4 || $findManipulWomen < 4) {
                            foreach($workersAnyShiftOther as $worker) {
                                if($worker->workerPosition && $worker->workerPosition->id == 4 && $findManipulMen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShiftOther[$worker->id]);
                                        $findManipulMen++;
                                    }
                                }

                                if($worker->workerPosition && $worker->workerPosition->id == 5 && $findManipulWomen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShiftOther[$worker->id]);
                                        $findManipulWomen++;
                                    }
                                }

                                if($findManipulMen >= 4 && $findManipulWomen >= 4) {
                                    break;
                                }
                            }
                        }

                        if($findManipulMen < 4 || $findManipulWomen < 4) {
                            foreach($$takeShift as $worker) {
                                if($worker->workerPosition && $worker->workerPosition->id == 5 && $findManipulMen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($$takeShift[$worker->id]);
                                        $findManipulMen++;
                                    }
                                }

                                if($worker->workerPosition && $worker->workerPosition->id == 4 && $findManipulWomen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($$takeShift[$worker->id]);
                                        $findManipulWomen++;
                                    }
                                }

                                if($findManipulMen >= 4 && $findManipulWomen >= 4) {
                                    break;
                                }
                            }
                        }

                        if($findManipulMen < 4 || $findManipulWomen < 4) {
                            foreach($$takeShiftOther as $worker) {
                                if($worker->workerPosition && $worker->workerPosition->id == 5 && $findManipulMen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($$takeShiftOther[$worker->id]);
                                        $findManipulMen++;
                                    }
                                }

                                if($worker->workerPosition && $worker->workerPosition->id == 4 && $findManipulWomen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($$takeShiftOther[$worker->id]);
                                        $findManipulWomen++;
                                    }
                                }

                                if($findManipulMen >= 4 && $findManipulWomen >= 4) {
                                    break;
                                }
                            }
                        }

                        if($findManipulMen < 4 || $findManipulWomen < 4) {
                            foreach($workersAnyShift as $worker) {
                                if($worker->workerPosition && $worker->workerPosition->id == 5 && $findManipulMen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShift[$worker->id]);
                                        $findManipulMen++;
                                    }
                                }

                                if($worker->workerPosition && $worker->workerPosition->id == 4 && $findManipulWomen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShift[$worker->id]);
                                        $findManipulWomen++;
                                    }
                                }

                                if($findManipulMen >= 4 && $findManipulWomen >= 4) {
                                    break;
                                }
                            }
                        }

                        if($findManipulMen < 4 || $findManipulWomen < 4) {
                            foreach($workersAnyShiftOther as $worker) {
                                if($worker->workerPosition && $worker->workerPosition->id == 5 && $findManipulMen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShiftOther[$worker->id]);
                                        $findManipulMen++;
                                    }
                                }

                                if($worker->workerPosition && $worker->workerPosition->id == 4 && $findManipulWomen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShiftOther[$worker->id]);
                                        $findManipulWomen++;
                                    }
                                }

                                if($findManipulMen >= 4 && $findManipulWomen >= 4) {
                                    break;
                                }
                            }
                        }

                        if(($findManipulMen + $findManipulWomen) < 8) {
                            foreach($$takeShiftOtherBonus as $worker) {
                                if($worker->workerPosition && in_array($worker->workerPosition->id, [4,5]) && $findManipulMen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        $findManipulMen++;
                                    }
                                }

                                if($worker->workerPosition && in_array($worker->workerPosition->id, [4,5]) && $findManipulWomen < 4) {
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        $findManipulWomen++;
                                    }
                                }

                                if(($findManipulMen + $findManipulWomen) >= 8) {
                                    break;
                                }
                            }
                        }

                        if(($findManipulMen + $findManipulWomen) < 8) {
                            foreach($$takeShiftBonus as $worker) {
                                if($worker->timeFund != 0) {
                                    if($worker->workerPosition && in_array($worker->workerPosition->id, [4,5]) && $findManipulMen < 4) {
                                        if($this->check_no_vacation($worker, $plan->datePlan)) {
                                            $$takeWorkers[$worker->id] = $worker;
                                            $findManipulMen++;
                                        }
                                    }

                                    if($worker->workerPosition && in_array($worker->workerPosition->id, [4,5]) && $findManipulWomen < 4) {
                                        if($this->check_no_vacation($worker, $plan->datePlan)) {
                                            $$takeWorkers[$worker->id] = $worker;
                                            $findManipulWomen++;
                                        }
                                    }
                                }

                                if(($findManipulMen + $findManipulWomen) >= 8) {
                                    break;
                                }
                            }
                        }

                        if(($findManipulMen + $findManipulWomen) < 8) {
                            foreach($$takeWorkersBonus as $worker) {
                                if($worker->timeFund != 0) {
                                    if($worker->workerPosition && in_array($worker->workerPosition->id, [4,5]) && $findManipulMen < 4) {
                                        if($this->check_no_vacation($worker, $plan->datePlan)) {
                                            $$takeWorkers[$worker->id] = $worker;
                                            $findManipulMen++;
                                        }
                                    }

                                    if($worker->workerPosition && in_array($worker->workerPosition->id, [4,5]) && $findManipulWomen < 4) {
                                        if($this->check_no_vacation($worker, $plan->datePlan)) {
                                            $$takeWorkers[$worker->id] = $worker;
                                            $findManipulWomen++;
                                        }
                                    }
                                }

                                if(($findManipulMen + $findManipulWomen) >= 8) {
                                    break;
                                }
                            }
                        }
                    } else {
                        $findManipul = 0;
                        foreach($$takeShift as $worker) {
                            if ($worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                if ($this->check_no_vacation($worker, $plan->datePlan)) {
                                    $$takeWorkers[$worker->id] = $worker;
                                    unset($$takeShift[$worker->id]);
                                    $findManipul++;
                                }
                            }

                            if($findManipul >= 3) {
                                break;
                            }
                        }

                        if($findManipul < 3) {
                            foreach($$takeShiftOther as $worker) {
                                if ($worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                    if ($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($$takeShiftOther[$worker->id]);
                                        $findManipul++;
                                    }
                                }

                                if($findManipul >= 3) {
                                    break;
                                }
                            }
                        }

                        if($findManipul < 3) {
                            foreach($workersAnyShift as $worker) {
                                if ($worker->workerPosition && in_array($worker->workerPosition->id, [4,5])){
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShift[$worker->id]);
                                        $findManipul++;
                                    }
                                }

                                if($findManipul >= 3) {
                                    break;
                                }
                            }
                        }

                        if($findManipul < 3) {
                            foreach($workersAnyShiftOther as $worker) {
                                if ($worker->workerPosition && in_array($worker->workerPosition->id, [4,5])){
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        unset($workersAnyShiftOther[$worker->id]);
                                        $findManipul++;
                                    }
                                }

                                if($findManipul >= 3) {
                                    break;
                                }
                            }
                        }

                        if($findManipul < 3) {
                            foreach($$takeShiftOtherBonus as $worker) {
                                if ($worker->workerPosition && in_array($worker->workerPosition->id, [4,5])){
                                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                                        $$takeWorkers[$worker->id] = $worker;
                                        $findManipul++;
                                    }
                                }

                                if($findManipul >= 3) {
                                    break;
                                }
                            }
                        }

                        if($findManipul < 3) {
                            foreach($$takeShiftBonus as $worker) {
                                if ($worker->timeFund != 0) {
                                    if ($worker->workerPosition && in_array($worker->workerPosition->id, [4, 5])) {
                                        if ($this->check_no_vacation($worker, $plan->datePlan)) {
                                            $$takeWorkers[$worker->id] = $worker;
                                            $findManipul++;
                                        }
                                    }
                                }

                                if($findManipul >= 3) {
                                    break;
                                }
                            }
                        }

                        if($findManipul < 3) {
                            foreach($$takeWorkersBonus as $worker) {
                                if ($worker->timeFund != 0) {
                                    if ($worker->workerPosition && in_array($worker->workerPosition->id, [4, 5])) {
                                        if ($this->check_no_vacation($worker, $plan->datePlan)) {
                                            $$takeWorkers[$worker->id] = $worker;
                                            $findManipul++;
                                        }
                                    }
                                 }

                                if($findManipul >= 3) {
                                    break;
                                }
                            }
                        }
                    }
                }

                $alreadyTaken = array();
                foreach($$takeWorkers as $worker) {
                    if($this->check_no_vacation($worker, $plan->datePlan)) {
                        $workInPlan = new WorkerInPlan();
                        $workInPlan->setWorker($worker);
                        $workInPlan->setPlan($plan);
                        $workInPlan->setMinusLog(0);
                        $workInPlan->setPlusLog(0);
                        $workInPlan->setHours('12');
                        if(isset($specialWorkers[$worker->id])) {
                            $workInPlan->setWorkerPosition($positionArr[$specialWorkers[$worker->id]]);
                        } else {
                            $workInPlan->setWorkerPosition($worker->workerPosition);
                        }
                        $this->em->persist($workInPlan);
                    } else {
                        $found = NULL;
                        $foundPos = NULL;
                        foreach($$takeWorkerToShifts[$takeWorkers] as $wrk) {
                            if(!isset($alreadyTaken[$wrk->id])) {
                                if($specialWorkers[$worker->id] == '1') {
                                    if($wrk->workerPosition && $worker->workerPosition->id == 1) {
                                        if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                            $found = $wrk;
                                            $foundPos = '1';
                                        }
                                    }
                                    if($wrk->workerPosition && $worker->workerPosition->id == 2) {
                                        if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                            $found = $wrk;
                                            $foundPos = '1';
                                        }
                                    }

                                    if($found) {
                                        break;
                                    }
                                } elseif($specialWorkers[$worker->id] == '2') {
                                    if($wrk->workerPosition && $worker->workerPosition->id == 1) {
                                        if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                            $found = $wrk;
                                            $foundPos = '2';
                                        }
                                    }
                                    if($wrk->workerPosition && $worker->workerPosition->id == 2) {
                                        if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                            $found = $wrk;
                                            $foundPos = '2';
                                        }
                                    }

                                    if($found) {
                                        break;
                                    }
                                } elseif($specialWorkers[$worker->id] == '3') {
                                    if($wrk->workerPosition && $worker->workerPosition->id == 3) {
                                        if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                            $found = $wrk;
                                            $foundPos = '3';
                                        }
                                    }

                                    if($found) {
                                        break;
                                    }
                                } elseif($specialWorkers[$worker->id] == '28') {
                                    if(!!$worker->agency && $worker->timeFund == 0 && $worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                        if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                            $found = $wrk;
                                            $foundPos = '28';
                                        }
                                    }

                                    if($found) {
                                        break;
                                    }
                                } elseif($specialWorkers[$worker->id] == '4') {
                                    if($wrk->workerPosition && $worker->workerPosition->id == 4) {
                                        if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                            $found = $wrk;
                                            $foundPos = '4';
                                        }
                                    }

                                    if($found) {
                                        break;
                                    }
                                } elseif($specialWorkers[$worker->id] == '5') {
                                    if($wrk->workerPosition && $worker->workerPosition->id == 5) {
                                        if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                            $found = $wrk;
                                            $foundPos = '5';
                                        }
                                    }

                                    if($found) {
                                        break;
                                    }
                                } else {
                                    if($worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                        if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                            $found = $wrk;
                                            $foundPos = $worker->workerPosition->id;
                                        }
                                    }

                                    if($found) {
                                        break;
                                    }
                                }
                            }
                        }

                        if(!$found) {
                            foreach($$takeWorkerToShiftsOther[$takeWorkers] as $wrk) {
                                if(!isset($alreadyTaken[$wrk->id])) {
                                    if($specialWorkers[$worker->id] == '1') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 1) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '1';
                                            }
                                        }
                                        if($wrk->workerPosition && $worker->workerPosition->id == 2) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '1';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '2') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 1) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '2';
                                            }
                                        }
                                        if($wrk->workerPosition && $worker->workerPosition->id == 2) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '2';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '3') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 3) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '3';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '28') {
                                        if(!!$worker->agency && $worker->timeFund == 0 && $worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '28';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '4') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 4) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '4';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '5') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 5) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '5';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } else {
                                        if($worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = $worker->workerPosition->id;
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        if(!$found) {
                            foreach($workersAnyShift as $wrk) {
                                if(!isset($alreadyTaken[$wrk->id])) {
                                    if($specialWorkers[$worker->id] == '1') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 1) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '1';
                                            }
                                        }
                                        if($wrk->workerPosition && $worker->workerPosition->id == 2) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '1';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '2') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 1) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '2';
                                            }
                                        }
                                        if($wrk->workerPosition && $worker->workerPosition->id == 2) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '2';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '3') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 3) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '3';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '28') {
                                        if(!!$worker->agency && $worker->timeFund == 0 && $worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '28';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '4') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 4) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '4';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '5') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 5) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '5';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } else {
                                        if($worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = $worker->workerPosition->id;
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        if(!$found) {
                            foreach($workersAnyShiftOther as $wrk) {
                                if(!isset($alreadyTaken[$wrk->id])) {
                                    if($specialWorkers[$worker->id] == '1') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 1) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '1';
                                            }
                                        }
                                        if($wrk->workerPosition && $worker->workerPosition->id == 2) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '1';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '2') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 1) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '2';
                                            }
                                        }
                                        if($wrk->workerPosition && $worker->workerPosition->id == 2) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '2';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '3') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 3) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '3';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '28') {
                                        if(!!$worker->agency && $worker->timeFund == 0 && $worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '28';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '4') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 4) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '4';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } elseif($specialWorkers[$worker->id] == '5') {
                                        if($wrk->workerPosition && $worker->workerPosition->id == 5) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = '5';
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    } else {
                                        if($worker->workerPosition && in_array($worker->workerPosition->id, [4,5])) {
                                            if($this->check_no_vacation($wrk, $plan->datePlan)) {
                                                $found = $wrk;
                                                $foundPos = $worker->workerPosition->id;
                                            }
                                        }

                                        if($found) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        if($found) {
                            $workInPlan = new WorkerInPlan();
                            $workInPlan->setWorker($found);
                            $workInPlan->setPlan($plan);
                            $workInPlan->setWorkerPosition($positionArr[$foundPos]);
                            $workInPlan->setMinusLog(0);
                            $workInPlan->setPlusLog(0);
                            $workInPlan->setHours('12');
                            $this->em->persist($workInPlan);
                        }
                    }
                }
            }
            $this->em->flush();
        }

        $this->redirect('ShiftPlan:default', ['type' => $this->type, 'week' => $this->week, 'year' => $this->year]);
    }

    function check_no_vacation($worker, $dateCheck) {
        $criteriaVacation = new Criteria();
        $criteriaVacation->where(Criteria::expr()->lte('dateStart', $dateCheck));
        $criteriaVacation->andWhere(Criteria::expr()->gte('dateEnd', $dateCheck));
        $criteriaVacation->andWhere(Criteria::expr()->eq('worker', $worker));
        $checkVacation = $this->em->getVacationRepository()->matching($criteriaVacation);
        if($checkVacation && count($checkVacation)) {
            return false;
        } else {
            return true;
        }
    }

    function shuffle_assoc($list) {
        if (!is_array($list)) return $list;

        $keys = array_keys($list);
        shuffle($keys);
        $random = array();
        foreach ($keys as $key) {
            $random[$key] = $list[$key];
        }
        return $random;
    }

    public function handleCheckSpotPlan() {
        $values = $this->request->getPost();
        $stats = explode('_', $values['spot']);

        $modalSpot = $this->em->getShiftPlanRepository()->findOneBy(['dateString' => $stats[0], 'name' => $stats[1], 'productionLine' => $stats[2]]);
        if(!$modalSpot) {
            $clickDay = new \DateTime();
            $clickDay->setTimestamp(strtotime('monday this week ' . $stats[0]));
            $weekStart = new \DateTime();
            $weekStart->setISODate($clickDay->format('Y'), $clickDay->format('W'));
            $weekStart = new \DateTime($weekStart->format('Y').'-'.$weekStart->format('m').'-'.$weekStart->format('d').' 00:00:00');
            $weekStart = $weekStart->modify('-1 week');
            $startDate = clone $weekStart;
            $endDate = clone $weekStart;

            $startDate->setTime(0,0,0);
            $endDate = $endDate->modify('+6 days');
            $endDate->setTime(23,59,59);

            $criteriaStart = new Criteria();
            $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
            $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
            $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', $this->type));
            $plans = $this->em->getShiftPlanRepository()->matching($criteriaStart);
            $shiftTrans = ['D' => 'B', 'B' => 'C', 'C' => 'A', 'A' => 'D'];

            if($plans) {
                foreach ($plans as $plan) {
                    $newPlan = new ShiftPlan();
                    $newPlan->setName($plan->name);
                    $newPlan->setProductionLine($plan->productionLine);
                    $newPlan->setShift($shiftTrans[$plan->shift]);
                    $nDate = clone $plan->datePlan;
                    $nDate->modify('+7 days');
                    $newPlan->setDateString($nDate->format('Y-m-d'));
                    $newPlan->setDatePlan($nDate);

                    $this->em->persist($newPlan);
                }
                $this->em->flush();

                $modalSpot = $this->em->getShiftPlanRepository()->findOneBy(['dateString' => $stats[0], 'name' => $stats[1], 'productionLine' => $stats[2]]);
            }
        }

        $startMonthDay = new \DateTime();
        $startMonthDay->setTimestamp(strtotime('first day of this month ' . $stats[0]));
        $endMonthDay = new \DateTime();
        $endMonthDay->setTimestamp(strtotime('last day of this month ' . $stats[0]));
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
        $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', $this->type));
        $plans = $this->em->getShiftPlanRepository()->matching($criteriaStart);
        $fpd = array('A' => 0, 'B' => 0, 'C' => 0, 'D' => 0);
        foreach ($plans as $plan) {
            if(in_array($plan->shift, ['A', 'B', 'C', 'D'])) {
                $fpd[$plan->shift] += $plan->active ? 12 : 0;
            }
        }

        $this->template->modalSpot = $modalSpot;
        $this->template->modalSpotWorkers = $this->em->getWorkerInPlanRepository()->findBy(['plan' => $modalSpot], ['workerPosition' => 'ASC']);
        $this->template->modalSpotHours = $result;
        $this->template->fpd = $fpd;
        $this->template->workerSelect = $this->shiftFac->findPairsForSelect($modalSpot->dateString, $modalSpot->name, $modalSpot->productionLine);
        $this->redrawControl('planModal');
    }

    public function handleRemoveWorkerSpot() {
        $values = $this->request->getPost();
        $entity = $this->em->getWorkerInPlanRepository()->find($values['plan']);
        $this->em->remove($entity);
        $this->em->flush();

        $this->redirect('this');
    }

    public function handleSwitchShiftSpot() {
        $values = $this->request->getPost();
        $plan = $this->em->getShiftPlanRepository()->find($values['plan']);
        $snippName = 'snippFundA';
        if($plan) {
            $snippName = 'snippFund'.$plan->shift;
            $entities = $this->em->getShiftPlanRepository()->findBy(['dateString' => $plan->dateString, 'name' => $plan->name, 'shift' => $plan->shift]);
            if($plan->active) {
                foreach ($entities as $ent) {
                    $ent->setActive(0);

                    $qb = $this->em->getConnection()->prepare("
                        DELETE FROM worker_in_plan 
                        WHERE plan_id IN (".$ent->id.")
                        ");
                    $qb->execute();
                }
            } else {
                $shiftBonuses = $this->em->getShiftBonusRepository()->findAll();
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
                    $shiftBonusArr[$shiftBonus->shift][$shiftBonus->productionLine][$shiftBonus->name][$shiftBonus->dayOfWeek][] =
                        ['bonusEndDateString' => ($shiftBonus->dateEnd ? $shiftBonus->dateEnd->format('Y-m-d') : ''),
                            'bonusStartDateString' => ($shiftBonus->dateStart ? $shiftBonus->dateStart->format('Y-m-d') : ''),
                            'workerId' => $shiftBonus->worker->id,
                            'startDateString' => ($shiftBonus->worker->startDate ? $shiftBonus->worker->startDate->format('Y-m-d') : ''),
                            'endDateString' => ($shiftBonus->worker->endDate ? $shiftBonus->worker->endDate->format('Y-m-d') : ''),
                            'workerPosition' => ($shiftBonus->worker->workerPosition ? $shiftBonus->worker->workerPosition->id : 4)
                        ];
                }

                $workers = $this->em->getWorkerRepository()->findBy(['active' => 1, 'workerPosition' => [1,2,3,4,5,6]]);
                $workerArr = array(
                    'A' => ['1' => [], '2' => [], 'N' => []],
                    'B' => ['1' => [], '2' => [], 'N' => []],
                    'C' => ['1' => [], '2' => [], 'N' => []],
                    'D' => ['1' => [], '2' => [], 'N' => []],
                    'N' => ['1' => [], '2' => [], 'N' => []]
                );
                $workerChangeArr = array(
                    'A' => ['1' => [], '2' => [], 'N' => []],
                    'B' => ['1' => [], '2' => [], 'N' => []],
                    'C' => ['1' => [], '2' => [], 'N' => []],
                    'D' => ['1' => [], '2' => [], 'N' => []],
                    'N' => ['1' => [], '2' => [], 'N' => []]
                );
                foreach ($workers as $worker) {
                    if($worker->shift) {
                        if($worker->shift != 8) {
                            if($worker->productionLine && ($worker->productionLine->id == 1 || $worker->productionLine->id  == 2)) {
                                $workerArr[$worker->shift][$worker->productionLine->id][$worker->id] = $worker;
                            } else {
                                $workerArr[$worker->shift]['N'][$worker->id] = $worker;
                            }
                        }
                    } else {
                        if($worker->productionLine && ($worker->productionLine->id == 1 || $worker->productionLine->id  == 2)) {
                            $workerArr['N'][$worker->productionLine->id][$worker->id] = $worker;
                        } else {
                            $workerArr['N']['N'][$worker->id] = $worker;
                        }
                    }
                    if($worker->startDateChange) {
                        if($worker->shiftChange) {
                            if($worker->shiftChange != 8) {
                                if($worker->productionLineChange && ($worker->productionLineChange->id == 1 || $worker->productionLineChange->id  == 2)) {
                                    $workerChangeArr[$worker->shiftChange][$worker->productionLineChange->id][$worker->id] = $worker;
                                } elseif($worker->productionLine && ($worker->productionLine->id == 1 || $worker->productionLine->id  == 2)) {
                                    $workerChangeArr[$worker->shiftChange][$worker->productionLine->id][$worker->id] = $worker;
                                } else {
                                    $workerChangeArr[$worker->shiftChange]['N'][$worker->id] = $worker;
                                }
                            }
                        } elseif($worker->shift) {
                            if($worker->shift != 8) {
                                if($worker->productionLineChange && ($worker->productionLineChange->id == 1 || $worker->productionLineChange->id  == 2)) {
                                    $workerChangeArr[$worker->shift][$worker->productionLineChange->id][$worker->id] = $worker;
                                } elseif($worker->productionLine && ($worker->productionLine->id == 1 || $worker->productionLine->id  == 2)) {
                                    $workerChangeArr[$worker->shift][$worker->productionLine->id][$worker->id] = $worker;
                                } else {
                                    $workerChangeArr[$worker->shift]['N'][$worker->id] = $worker;
                                }
                            }
                        } else {
                            if($worker->productionLineChange && ($worker->productionLineChange->id == 1 || $worker->productionLineChange->id  == 2)) {
                                $workerChangeArr['N'][$worker->productionLineChange->id][$worker->id] = $worker;
                            } elseif($worker->productionLine && ($worker->productionLine->id == 1 || $worker->productionLine->id  == 2)) {
                                $workerChangeArr['N'][$worker->productionLine->id][$worker->id] = $worker;
                            } else {
                                $workerChangeArr['N']['N'][$worker->id] = $worker;
                            }
                        }
                    }
                }

                $insertNewSqlVal = '';
                foreach ($entities as $ent) {
                    $ent->setActive(1);

                    foreach ($workerArr[$plan->shift][$plan->productionLine] as $wrk)  {
                        if($wrk->startDate && $wrk->startDate->format('Y-m-d') > $plan->dateString) {
                            continue;
                        }
                        if($wrk->endDate && $wrk->endDate->format('Y-m-d') < $plan->dateString) {
                            continue;
                        }
                        if($wrk->startDateChange && $wrk->startDateChange->format('Y-m-d') <= $plan->dateString) {
                            continue;
                        }
                        if(isset($insertNewCheck[$wrk->id.'_'.$plan->id])) {
                            continue;
                        }
                        $insertNewCheck[$wrk->id.'_'.$plan->id] = 1;

                        $insertNewSqlVal .= '(NULL,'.$wrk->id.','.$plan->id.','.($wrk->workerPosition ? $wrk->workerPosition->id : 4).',12),';
                    }
                    foreach ($workerChangeArr[$plan->shift][$plan->productionLine] as $wrk)  {
                        if($wrk->startDate && $wrk->startDate->format('Y-m-d') > $plan->dateString) {
                            continue;
                        }
                        if($wrk->endDate && $wrk->endDate->format('Y-m-d') < $plan->dateString) {
                            continue;
                        }
                        if($wrk->startDateChange && $wrk->startDateChange->format('Y-m-d') > $plan->dateString) {
                            continue;
                        }
                        if(isset($insertNewCheck[$wrk->id.'_'.$plan->id])) {
                            continue;
                        }
                        $insertNewCheck[$wrk->id.'_'.$plan->id] = 1;

                        $insertNewSqlVal .= '(NULL,'.$wrk->id.','.$plan->id.','.($wrk->workerPosition ? $wrk->workerPosition->id : 4).',12),';
                    }
                    foreach ($shiftBonusArr[$plan->shift][$plan->productionLine][$plan->name][$plan->datePlan->format('N')] as $sbn) {
                        if($sbn['startDateString'] && $sbn['startDateString'] > $plan->dateString) {
                            continue;
                        }
                        if($sbn['endDateString'] && $sbn['endDateString'] < $plan->dateString) {
                            continue;
                        }
                        if(!$sbn['bonusEndDateString'] || $sbn['bonusEndDateString'] >= $plan->dateString) {
                            if($sbn['bonusStartDateString'] && $sbn['bonusStartDateString'] > $plan->dateString) {
                                continue;
                            }
                            if(isset($insertNewCheck[$sbn['workerId'].'_'.$plan->id])) {
                                continue;
                            }
                            $insertNewCheck[$sbn['workerId'].'_'.$plan->id] = 1;

                            $insertNewSqlVal .= '(NULL,'.$sbn['workerId'].','.$plan->id.','.$sbn['workerPosition'].',12),';
                        }
                    }
                }

                if($insertNewSqlVal) {
                    $insertNewSqlVal = substr($insertNewSqlVal, 0, -1);
                    $qb = $this->em->getConnection()->prepare("
                            INSERT INTO worker_in_plan (id, worker_id, plan_id, worker_position_id, hours) 
                            VALUES ".$insertNewSqlVal."
                            ");
                    $qb->execute();
                }
                $this->em->flush();
            }
        }

        $this->em->flush();
        $this->redrawControl($snippName);
        //$this->redirect('this');
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

                $workInPlan = new WorkerInPlan();
                $workInPlan->setWorker($worker);
                $workInPlan->setPlan($plan);
                $workInPlan->setWorkerPosition($workerPosition ? $workerPosition : $worker->workerPosition);
                $workInPlan->setManual(1);
                $workInPlan->setMinusLog(0);
                $workInPlan->setPlusLog(0);
                $workInPlan->setHours('12');
                $this->em->persist($workInPlan);
                $this->em->flush();
            }

            $that->redirect('this');
        };

        return $form;
    }

    public function handleExportFunds($year) {
        $startDate = new \DateTime($year.'-01-01 00:00:00');
        $endDate = new \DateTime($year.'-12-31 23:59:59');

        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
        $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', '1'));
        $plans = $this->em->getShiftPlanRepository()->matching($criteriaStart);
        $spotsA = $spotsB = $spotsC = $spotsD = array();
        $fpdA = $fpdB = $fpdC = $fpdD = array();
        $spotsCheck = array();

        foreach ($plans as $plan) {
            $keyString = $plan->datePlan->format('n-j');
            $spotsCheck[$keyString] = $keyString;
            $keyFpd = $plan->datePlan->format('n');
            if($plan->shift == 'A') {
                $spotsA[$keyString] = $plan;
                if(!isset($fpdA[$keyFpd])) {
                    $fpdA[$keyFpd] = 0;
                }
                $fpdA[$keyFpd] += $plan->active ? 12 : 0;
            } elseif($plan->shift == 'B') {
                $spotsB[$keyString] = $plan;
                if(!isset($fpdB[$keyFpd])) {
                    $fpdB[$keyFpd] = 0;
                }
                $fpdB[$keyFpd] += $plan->active ? 12 : 0;
            } elseif($plan->shift == 'C') {
                $spotsC[$keyString] = $plan;
                if(!isset($fpdC[$keyFpd])) {
                    $fpdC[$keyFpd] = 0;
                }
                $fpdC[$keyFpd] += $plan->active ? 12 : 0;
            } elseif($plan->shift == 'D') {
                $spotsD[$keyString] = $plan;
                if(!isset($fpdD[$keyFpd])) {
                    $fpdD[$keyFpd] = 0;
                }
                $fpdD[$keyFpd] += $plan->active ? 12 : 0;
            }
        }

        $cusDays = array_merge(['M'], range(1, 31), ['F.P.D.']);
        $cusMonths = array_merge([''], range(1, 12));

        $document = new Spreadsheet();
        $document->setActiveSheetIndex(0);

        $sheet = $document->getActiveSheet();
        $sheet->setTitle('A');
        $fpdTotalA = 0;
        $currRow = 1;
        foreach($cusMonths as $monthName) {
            $currCol = 1;
            foreach($cusDays as $dayName) {
                if ($monthName == '') {
                    $sheet->setCellValueByColumnAndRow($currCol, $currRow, $dayName);
                } else {
                    if (is_numeric($dayName)) {
                        if (isset($spotsA[$monthName.'-'.$dayName])) {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, ($spotsA[$monthName.'-'.$dayName]->name == 1 ? 'R' : 'N'));
                            if($spotsA[$monthName.'-'.$dayName]->active) {
                                $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('48e14f');
                            } else {
                                $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('b2b2b2');
                            }
                        } elseif (isset($spotsCheck[$monthName.'-'.$dayName])) {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, 'v');
                            $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('b2b2b2');
                        }
                    } else {
                        if ($dayName == 'M') {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, $monthName);
                        } else {
                            if (isset($fpdA[$monthName])) {
                                $sheet->setCellValueByColumnAndRow($currCol, $currRow, $fpdA[$monthName]);
                                $fpdTotalA += $fpdA[$monthName];
                            }
                        }
                    }
                }
                $currCol++;
            }
            $currRow++;
        }
        $sheet->setCellValueByColumnAndRow(33, $currRow, $fpdTotalA);

        $sheet = new Worksheet();
        $sheet->setTitle('B');
        $document->addSheet($sheet);
        $fpdTotalB = 0;
        $currRow = 1;
        foreach($cusMonths as $monthName) {
            $currCol = 1;
            foreach($cusDays as $dayName) {
                if ($monthName == '') {
                    $sheet->setCellValueByColumnAndRow($currCol, $currRow, $dayName);
                } else {
                    if (is_numeric($dayName)) {
                        if (isset($spotsB[$monthName.'-'.$dayName])) {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, ($spotsB[$monthName.'-'.$dayName]->name == 1 ? 'R' : 'N'));
                            if($spotsB[$monthName.'-'.$dayName]->active) {
                                $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('48e14f');
                            } else {
                                $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('b2b2b2');
                            }
                        } elseif (isset($spotsCheck[$monthName.'-'.$dayName])) {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, 'v');
                            $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('b2b2b2');
                        }
                    } else {
                        if ($dayName == 'M') {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, $monthName);
                        } else {
                            if (isset($fpdB[$monthName])) {
                                $sheet->setCellValueByColumnAndRow($currCol, $currRow, $fpdB[$monthName]);
                                $fpdTotalB += $fpdB[$monthName];
                            }
                        }
                    }
                }
                $currCol++;
            }
            $currRow++;
        }
        $sheet->setCellValueByColumnAndRow(33, $currRow, $fpdTotalB);

        $sheet = new Worksheet();
        $sheet->setTitle('C');
        $document->addSheet($sheet);
        $fpdTotalC = 0;
        $currRow = 1;
        foreach($cusMonths as $monthName) {
            $currCol = 1;
            foreach($cusDays as $dayName) {
                if ($monthName == '') {
                    $sheet->setCellValueByColumnAndRow($currCol, $currRow, $dayName);
                } else {
                    if (is_numeric($dayName)) {
                        if (isset($spotsC[$monthName.'-'.$dayName])) {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, ($spotsC[$monthName.'-'.$dayName]->name == 1 ? 'R' : 'N'));
                            if($spotsC[$monthName.'-'.$dayName]->active) {
                                $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('48e14f');
                            } else {
                                $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('b2b2b2');
                            }
                        } elseif (isset($spotsCheck[$monthName.'-'.$dayName])) {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, 'v');
                            $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('b2b2b2');
                        }
                    } else {
                        if ($dayName == 'M') {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, $monthName);
                        } else {
                            if (isset($fpdC[$monthName])) {
                                $sheet->setCellValueByColumnAndRow($currCol, $currRow, $fpdC[$monthName]);
                                $fpdTotalC += $fpdC[$monthName];
                            }
                        }
                    }
                }
                $currCol++;
            }
            $currRow++;
        }
        $sheet->setCellValueByColumnAndRow(33, $currRow, $fpdTotalC);

        $sheet = new Worksheet();
        $sheet->setTitle('D');
        $document->addSheet($sheet);
        $fpdTotalD = 0;
        $currRow = 1;
        foreach($cusMonths as $monthName) {
            $currCol = 1;
            foreach($cusDays as $dayName) {
                if ($monthName == '') {
                    $sheet->setCellValueByColumnAndRow($currCol, $currRow, $dayName);
                } else {
                    if (is_numeric($dayName)) {
                        if (isset($spotsD[$monthName.'-'.$dayName])) {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, ($spotsD[$monthName.'-'.$dayName]->name == 1 ? 'R' : 'N'));
                            if($spotsD[$monthName.'-'.$dayName]->active) {
                                $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('48e14f');
                            } else {
                                $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('b2b2b2');
                            }
                        } elseif (isset($spotsCheck[$monthName.'-'.$dayName])) {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, 'v');
                            $sheet->getStyleByColumnAndRow($currCol, $currRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('b2b2b2');
                        }
                    } else {
                        if ($dayName == 'M') {
                            $sheet->setCellValueByColumnAndRow($currCol, $currRow, $monthName);
                        } else {
                            if (isset($fpdD[$monthName])) {
                                $sheet->setCellValueByColumnAndRow($currCol, $currRow, $fpdD[$monthName]);
                                $fpdTotalD += $fpdD[$monthName];
                            }
                        }
                    }
                }
                $currCol++;
            }
            $currRow++;
        }
        $sheet->setCellValueByColumnAndRow(33, $currRow, $fpdTotalD);

        $pathName = 'exp/planovani-fondu-'.$year.'.xlsx';
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($document, "Xlsx");
        $writer->save($pathName);

        $response = new FileResponse($pathName, 'planovani-fondu-'.$year.'.xlsx');
        $this->sendResponse($response);
    }

    public function switchBackToFourShift() {
        //return array();

        $startDateLast = new \DateTime('2022-06-27');
        $endDateLast = new \DateTime('2022-07-03');
        $startDateLast->setTime(0,0,0);
        $endDateLast->setTime(23,59,59);
        $testingDay = new \DateTime('2022-07-03');
        $totalEndDay = new \DateTime('2023-12-31');

        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDateLast));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDateLast));
        //$criteriaStart->andWhere(Criteria::expr()->eq('productionLine', 1));
        $lastPlans = $this->em->getShiftPlanRepository()->matching($criteriaStart);

        $shiftTransCol = array();
        $shiftTransCol[0] = ['D' => 'D', 'B' => 'B', 'C' => 'C', 'A' => 'A'];
        $shiftTransCol[1] = ['D' => 'B', 'B' => 'C', 'C' => 'A', 'A' => 'D'];
        $shiftTransCol[2] = ['D' => 'C', 'B' => 'A', 'C' => 'D', 'A' => 'B'];
        $shiftTransCol[3] = ['D' => 'A', 'B' => 'D', 'C' => 'B', 'A' => 'C'];

        $addDays = 7;
        while($testingDay <= $totalEndDay) {
            $shiftTrans = $shiftTransCol[intval(($addDays / 7) % 4)];
            foreach ($lastPlans as $plan) {
                $nDate = clone $plan->datePlan;
                $nDate->modify('+'.$addDays.' days');

                $newPlan = $this->em->getShiftPlanRepository()->findOneBy(['dateString' => $nDate->format('Y-m-d'), 'name' => $plan->name, 'productionLine' => $plan->productionLine]);
                if(!$newPlan) {
                    $newPlan = new ShiftPlan();
                }

                $newPlan->setName($plan->name);
                $newPlan->setProductionLine($plan->productionLine);
                $newPlan->setShift($shiftTrans[$plan->shift]);
                $newPlan->setDateString($nDate->format('Y-m-d'));
                $newPlan->setDatePlan($nDate);
                $newPlan->setActive(1);
                $this->em->persist($newPlan);
            }

            $testingDay->modify('+7 days');
            $addDays += 7;
        }
        $this->em->flush();

        return array();
    }

    public function yearPlanManual() {
        $startDateLast = new \DateTime('2022-06-06');
        $endDateLast = new \DateTime('2022-06-12');
        $startDateLast->setTime(0,0,0);
        $endDateLast->setTime(23,59,59);
        $testingDay = new \DateTime('2022-06-12');
        $totalEndDay = new \DateTime('2023-12-31');

        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDateLast));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDateLast));
        $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', 1));
        $lastPlans = $this->em->getShiftPlanRepository()->matching($criteriaStart);

        $shiftTransCol = array();
        $shiftTransCol[0] = ['B' => 'B', 'C' => 'C', 'A' => 'A', 'v' => 'v'];
        $shiftTransCol[1] = ['B' => 'C', 'A' => 'B', 'C' => 'A', 'v' => 'v'];
        $shiftTransCol[2] = ['B' => 'A', 'A' => 'C', 'C' => 'B', 'v' => 'v'];

        $addDays = 7;
        while($testingDay <= $totalEndDay) {
            $shiftTrans = $shiftTransCol[intval(($addDays / 7) % 3)];
            foreach ($lastPlans as $plan) {
                $nDate = clone $plan->datePlan;
                $nDate->modify('+'.$addDays.' days');

                $newPlan = $this->em->getShiftPlanRepository()->findOneBy(['dateString' => $nDate->format('Y-m-d'), 'name' => $plan->name, 'productionLine' => $plan->productionLine]);
                if(!$newPlan) {
                    $newPlan = new ShiftPlan();
                }

                $newPlan->setName($plan->name);
                $newPlan->setProductionLine($plan->productionLine);
                $newPlan->setShift($shiftTrans[$plan->shift]);
                $newPlan->setDateString($nDate->format('Y-m-d'));
                $newPlan->setDatePlan($nDate);
                if($nDate->format('N') == 6 || $nDate->format('N') == 7) {
                    $newPlan->setActive(0);
                } else {
                    $newPlan->setActive(1);
                }
                $this->em->persist($newPlan);
            }

            $testingDay->modify('+7 days');
            $addDays += 7;
        }
        $this->em->flush();

        /*
        $shiftTransCol = array();
        $shiftTransCol[0] = ['D' => 'D', 'B' => 'B', 'C' => 'C', 'A' => 'A'];
        $shiftTransCol[1] = ['D' => 'B', 'B' => 'C', 'C' => 'A', 'A' => 'D'];
        $shiftTransCol[2] = ['D' => 'C', 'B' => 'A', 'C' => 'D', 'A' => 'B'];
        $shiftTransCol[3] = ['D' => 'A', 'B' => 'D', 'C' => 'B', 'A' => 'C'];

        $addDays = 7;
        while($testingDay <= $totalEndDay) {
            $shiftTrans = $shiftTransCol[intval(($addDays / 7) % 4)];
            foreach ($lastPlans as $plan) {
                $newPlan = new ShiftPlan();
                $newPlan->setName($plan->name);
                $newPlan->setProductionLine($plan->productionLine);
                $newPlan->setShift($shiftTrans[$plan->shift]);
                $nDate = clone $plan->datePlan;
                $nDate->modify('+'.$addDays.' days');
                $newPlan->setDateString($nDate->format('Y-m-d'));
                $newPlan->setDatePlan($nDate);
                $newPlan->setActive(1);

                $this->em->persist($newPlan);

            }

            $testingDay->modify('+7 days');
            $addDays += 7;
        }
        $this->em->flush();*/

        die;
    }


}