<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\ProductionProgressReportSetting;
use App\Model\Database\Entity\ShiftPlan;
use App\Model\Database\Entity\WorkerInPlan;
use Doctrine\Common\Collections\Criteria;

class ProductionProgressReportPresenter extends BasePresenter
{
    /** @var integer @persistent */
    public $week;

    /** @var integer @persistent */
    public $year;

    /** @var string @persistent */
    public $type;

    /** @var string @persistent */
    public $dateRange;

    /**
     * ACL name='Správa průběh výroby - sestavy'
     * ACL rejection='Nemáte přístup k průběhu výroby - sestavy'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);

        $this->sess->pprType = $this->getParameter('type');
    }

    /**
     * ACL name='Zobrazení stránky s průběhem výroby'
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

        $spotsA = array();

        $serverIp = $_SERVER['SERVER_NAME'] == 'galma.local' ? '192.168.100.4' : '192.168.1.112';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf("http://%s:3000/v1/reports/get-production-progress/%d/%s/%s", $serverIp, $this->type, $this->week, $this->year),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 10,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $status = $info['http_code'];
        $data = null;
        if ($status == 200) {
            $data = json_decode($response, true);
        } else if ($status == 0) {
            $this->flashMessage('API není momentálně dostupná.', 'warning');
        } else {
            $this->flashMessage('Odpověď z API není správná.', 'error');
        }

        $cusFloors = [
            1 => 'celkový počet tyčí',
            2 => 'celková plocha [m<sup>2</sup>]',
            3 => 'Ø plocha na tyč [m<sup>2</sup>]',
            /*4 => 'Ø počet závěsů za směnu',*/
            5 => 'takt - čistý',
            6 => 'takt - hrubý',
            7 => 'provozní hodiny linky',
            8 => 'celková vyrobená cena',
            9 => 'Ø vyrobená cena na tyč',
            20 => 'počet zaměstnanců',
        ];
        $itemsCount = count($cusFloors);

        if ($data) {
            foreach ($data as $index => $d) {
                for ($i = 1; $i <= $itemsCount + 1; $i++) {
                    $ss = $i;
                    if ($d['shiftS'] == 'N') {
                        $ss += $itemsCount;
                    }
                    $keyString = date('Y-m-d', $d['date']) . '_0_' . $ss;
                    $spotsA[$keyString] = array();
                    $spotsA[$keyString]['id'] = $ss;
                    $value = '';
                    switch ($i) {
                        case 1:
                            $value = $d['rodNumber'];
                            break;
                        case 2:
                            $value = round($d['areaPc'], 3);
                            break;
                        case 3:
                            if ($d['rodNumber'] > 0) {
                                $value = round($d['areaPc'] / $d['rodNumber'], 3);
                            } else {
                                $value = '0';
                            }
                            break;
                        /*case 5:
                            $value = $d['hinge'];
                            break;*/
                        case 4:
                            if ($d['rodNumber'] > 0) {
                                $init = $d['machineWorkingTime'] / $d['rodNumber'];
                                $hours = floor($init / 3600);
                                $minutes = floor(($init / 60) % 60);
                                $seconds = $init % 60;
                                $value = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                            } else {
                                $value = '00:00:00';
                            }
                            break;
                        case 5:
                            if ($d['rodNumber'] > 0) {
                                $init = $d['totalMachineWorkingTime'] / $d['rodNumber'];
                                $hours = floor($init / 3600);
                                $minutes = floor(($init / 60) % 60);
                                $seconds = $init % 60;
                                $value = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                            } else {
                                $value = '00:00:00';
                            }
                            break;
                        case 6:
                            if ($d['rodNumber'] > 0) {
                                $minus = '';
                                $init = $d['machineWorkingTime'];
                                if ($init < 0) {
                                    $init *= -1;
                                    $minus = '-';
                                }
                                $hours = floor($init / 3600);
                                $minutes = floor(($init / 60) % 60);
                                $seconds = $init % 60;
                                $value = sprintf("%s%02d:%02d:%02d", $minus, $hours, $minutes, $seconds);
                            } else {
                                $value = '00:00:00';
                            }
                            break;
                        case 7:
                            $value = round($d['price'], 3);
                            break;
                        case 8:
                            if ($d['rodNumber'] > 0) {
                                $value = round($d['price'] / $d['rodNumber'], 3);
                            } else {
                                $value = 0;
                            }
                            break;
                    }
                    $spotsA[$keyString]['name'] = $value;
                }
            }
        }

        $result = $this->em->createQuery('
            SELECT sp.name, sp.dateString, sp.datePlan,
            (
                SELECT COUNT(wip.id)
                FROM '.WorkerInPlan::class.' wip
                WHERE wip.plan = sp.id and (wip.minusLog is null or wip.minusLog = 0)
            ) as workerCount
            FROM '.ShiftPlan::class.' sp
            WHERE sp.productionLine = :line and sp.datePlan >= :startDate and sp.datePlan <= :endDate
        ')
            ->execute([
                'line' => $this->type,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
        if ($result) {
            foreach ($result as $r) {
                /** @var \DateTime $today */
                $today = clone $r['datePlan'];
                /** @var \DateTime $tomorrow */
                $tomorrow = clone $r['datePlan'];
                $today->setTime(18, 0, 0);
                $tomorrow->setTime(6,0,0);
                $tomorrow->modify('+1 day');
                if (
                    ($r['name'] == 1 && $today->getTimestamp() < time())
                    ||
                    ($r['name'] == 2 && $tomorrow->getTimestamp() < time())
                ) {
                    $floor = $r['name'] == 1 ? 1 : 3;
                    $keyString = sprintf("%s_%d_%s", $r['dateString'], 1, $floor);
                    $spotsA[$keyString] = [
                        'name' => $r['workerCount']
                    ];
                }
            }
        }

        $dateLoop = clone $startDate;
        $columnsA = array(-1);
        for($n = 0; $n < 7; $n++) {
            $columnsA[$dateLoop->format('Y-m-d')] = $dateLoop->format('j. n. Y');
            $dateLoop = $dateLoop->modify('+1 days');
        }

        $this->template->cusDays = ['', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'];
        $this->template->cusFloors = $cusFloors;
        $this->template->spotsA = $spotsA;
        $this->template->plansA = $plansA;
        $this->template->columnsA = $columnsA;
        $this->template->floorsA = [
            [-1,1,2,3,5,6,7,8,9,-2,1,2,3,5,6,7,8,9],
            [-1,20,-2,20],
            [-1,-2],
            [-1,-2],
            [-1,-2],
        ];
        $this->template->placesA = array(1);
        $this->template->groups = [
            'Výroba',
            'Personální',
            'Kvalita',
            'Chemik',
            'Údržba'
        ];
        $this->template->groupColor = [
            '#104d8e',
            '#868686',
            '#fe0000',
            '#ff6600',
            '#28aa4a'
        ];
        $this->template->tbodyVisible = $this->sess->tbodyVisible ?? [];
    }

    /**
     * ACL name='Zobrazení stránky se sumarizací průběhu výroby'
     */
    public function renderSummary($type)
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

        $settings = $this->em->getProductionProgressReportSettingRepository()->findOneBy(['line' => $this->type]);
        if ($settings) {
            $data = $this->ed->get($settings);
        } else {
            $data['line'] = $this->type;
        }
        $this['normSettingForm']->setDefaults($data);

        if (!$this->dateRange) {
            $startMonthDay = new \DateTime();
            $startMonthDay->setTimestamp(strtotime('first day of this month'));
            $endMonthDay = new \DateTime();
            $endMonthDay->setTimestamp(strtotime('last day of this month'));
            $startMonthDay->setTime(0,0,0);
            $endMonthDay->setTime(23,59,59);
            $this->dateRange = sprintf("%s 00:00:00 - %s 23:59:59", $startMonthDay->format('j. n. Y'), $endMonthDay->format('j. n. Y'));
        }

        $dateSplit = explode(' - ', $this->dateRange);
        $startDate = \DateTime::createFromFormat('j. n. Y H:i:s', $dateSplit[0]);
        $endDate = \DateTime::createFromFormat('j. n. Y H:i:s', $dateSplit[1]);

        $this->template->dateInput = sprintf("%s - %s", $startDate->format('j. n. Y H:i:s'), $endDate->format('j. n. Y H:i:s'));

        $spotsA = array();

        $serverIp = $_SERVER['SERVER_NAME'] == 'galma.local' ? '192.168.100.4' : '192.168.1.112';

        $startDateUTC = clone $startDate;
        $endDateUTC = clone $endDate;
        $startDateUTC->setTimezone(new \DateTimeZone('UTC'));
        $endDateUTC->setTimezone(new \DateTimeZone('UTC'));
        $query = http_build_query([
            'startDate' => $startDateUTC->format('Y-m-d\TH:i:s.000\Z'),
            'endDate' => $endDateUTC->format('Y-m-d\TH:i:s.000\Z')
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf("http://%s:3000/v1/reports/get-production-progress/%d?%s", $serverIp, $this->type, $query),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 10,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $status = $info['http_code'];
        $data = null;
        if ($status == 200) {
            $data = json_decode($response, true);
        } else if ($status == 0) {
            $this->flashMessage('API není momentálně dostupná.', 'warning');
        } else {
            $this->flashMessage('Odpověď z API není správná.', 'error');
        }

        $cusFloors = [
            1 => 'celkový počet tyčí',
            2 => 'celková plocha [m<sup>2</sup>]',
            3 => 'Ø plocha na tyč [m<sup>2</sup>]',
            /*4 => 'Ø počet závěsů za směnu',*/
            5 => 'takt - čistý',
            6 => 'takt - hrubý',
            7 => 'provozní hodiny linky',
            8 => 'celková vyrobená cena',
            9 => 'Ø vyrobená cena na tyč',
            10 => 'Ø počet tyčí za směnu',
            20 => 'počet zaměstnanců',
            21 => 'počet směn',
            22 => 'obsazenost směn v %',
            23 => 'mzdové náklady na dm<sup>2</sup>',
            40 => '',
            60 => '',
            80 => '',
        ];

        $totalIndex = 3;
        $shiftCount = [
            $totalIndex => 0
        ];
        if ($data) {
            foreach ($data as $index => $d) {
                $shiftS = $d['shiftS'] == 'R' ? 1 : 2;
                if (!isset($shiftCount[$shiftS])) {
                    $shiftCount[$shiftS] = 0;
                }

                $shiftCount[$shiftS]++;
                $shiftCount[$totalIndex]++;

                foreach ($cusFloors as $ii => $vv) {
                    if ($ii > 10) {
                        continue;
                    }
                    $keyString = sprintf("%s_%d_%d", $shiftS, 0, $ii);
                    $value = 0;
                    switch ($ii) {
                        case 1:
                            $value = $d['rodNumber'];
                            break;
                        case 2:
                            $value = round($d['areaPc'], 3);
                            break;
                        case 3:
                            $value = 0;
                            if (!isset($spotsA[$keyString]['areaPc'])) {
                                $spotsA[$keyString]['areaPc'] = 0;
                            }
                            $spotsA[$keyString]['areaPc'] += $d['areaPc'];
                            if (!isset($spotsA[$keyString]['rodNumber'])) {
                                $spotsA[$keyString]['rodNumber'] = 0;
                            }
                            $spotsA[$keyString]['rodNumber'] += $d['rodNumber'];
                            break;
                        case 5:
                            $value = 0;
                            if (!isset($spotsA[$keyString]['machineWorkingTime'])) {
                                $spotsA[$keyString]['machineWorkingTime'] = 0;
                            }
                            $spotsA[$keyString]['machineWorkingTime'] += $d['machineWorkingTime'];
                            if (!isset($spotsA[$keyString]['rodNumber'])) {
                                $spotsA[$keyString]['rodNumber'] = 0;
                            }
                            $spotsA[$keyString]['rodNumber'] += $d['rodNumber'];
                            break;
                        case 6:
                            $value = 0;
                            if (!isset($spotsA[$keyString]['machineWorkingTime'])) {
                                $spotsA[$keyString]['machineWorkingTime'] = 0;
                            }
                            $spotsA[$keyString]['machineWorkingTime'] += $d['totalMachineWorkingTime'];
                            if (!isset($spotsA[$keyString]['rodNumber'])) {
                                $spotsA[$keyString]['rodNumber'] = 0;
                            }
                            $spotsA[$keyString]['rodNumber'] += $d['rodNumber'];
                            break;
                        case 7:
                            $value = 0;
                            if ($d['rodNumber'] > 0) {
                                $value = $d['machineWorkingTime'];
                            }
                            break;
                        case 8:
                            $value = round($d['price'], 3);
                            break;
                        case 9:
                            $value = 0;
                            if (!isset($spotsA[$keyString]['price'])) {
                                $spotsA[$keyString]['price'] = 0;
                            }
                            $spotsA[$keyString]['price'] += $d['price'];
                            if (!isset($spotsA[$keyString]['rodNumber'])) {
                                $spotsA[$keyString]['rodNumber'] = 0;
                            }
                            $spotsA[$keyString]['rodNumber'] += $d['rodNumber'];
                            break;
                    }
                    if (!isset($spotsA[$keyString]['name'])) {
                        $spotsA[$keyString]['id'] = $ii;
                        $spotsA[$keyString]['name'] = 0;
                    }
                    $spotsA[$keyString]['name'] += $value;
                    $keyString = sprintf("%s_%d_%d", $totalIndex, 0, $ii);
                    if (!isset($spotsA[$keyString]['name'])) {
                        $spotsA[$keyString]['id'] = $ii;
                        $spotsA[$keyString]['name'] = 0;
                    }
                    $spotsA[$keyString]['name'] += $value;
                }
            }
        }

        $indexes = [];
        foreach ($spotsA as $key => $value) {
            list($id, $floor, $val) = explode('_', $key);
            $keyString = sprintf("%s_%d_%d", $totalIndex, $floor, $val);
            if (isset($value['areaPc']) && isset($value['rodNumber'])) {
                if (!isset($spotsA[$keyString]['total'])) {
                    $spotsA[$keyString]['total'] = 0;
                }
                if (!isset($spotsA[$keyString]['divider'])) {
                    $spotsA[$keyString]['divider'] = 0;
                }
                $spotsA[$key]['name'] = round($value['areaPc'] / $value['rodNumber'], 3);
                $spotsA[$keyString]['total'] += $value['areaPc'];
                $spotsA[$keyString]['divider'] += $value['rodNumber'];
                if (!in_array($keyString, $indexes)) {
                    $indexes[] = $keyString;
                }
            } elseif (isset($value['price']) && isset($value['rodNumber'])) {
                if (!isset($spotsA[$keyString]['total'])) {
                    $spotsA[$keyString]['total'] = 0;
                }
                if (!isset($spotsA[$keyString]['divider'])) {
                    $spotsA[$keyString]['divider'] = 0;
                }
                $spotsA[$key]['name'] = round($value['price'] / $value['rodNumber'], 3);
                $spotsA[$keyString]['total'] += $value['price'];
                $spotsA[$keyString]['divider'] += $value['rodNumber'];
                if (!in_array($keyString, $indexes)) {
                    $indexes[] = $keyString;
                }
            }
        }
        if (count($indexes)) {
            foreach ($indexes as $index) {
                $spotsA[$index]['name'] = $spotsA[$index]['total'] / $spotsA[$index]['divider'];
            }
        }

        for ($i = 5; $i <= 7; $i++) {
            $totalValue = 0;
            for ($ii = 1; $ii <= 3; $ii++) {
                $keyString = sprintf("%s_%d_%d", $ii, 0, $i);
                if (!isset($spotsA[$keyString]['name'])) {
                    $spotsA[$keyString]['name'] = 0;
                }
                if (isset($spotsA[$keyString]['machineWorkingTime']) && isset($spotsA[$keyString]['rodNumber'])) {
                    $spotsA[$keyString]['name'] = $spotsA[$keyString]['machineWorkingTime'] / $spotsA[$keyString]['rodNumber'];
                }
                $init = $spotsA[$keyString]['name'];
                if ($init <= 0) {
                    $spotsA[$keyString]['name'] = '';
                    continue;
                }
                if ($ii >= 1 && $ii <= 2) {
                    $totalValue += $init;

                    if (isset($spotsA[$keyString]['machineWorkingTime']) && isset($spotsA[$keyString]['rodNumber'])) {
                        $keyStringTotal = sprintf("%s_%d_%d", $totalIndex, 0, $i);
                        if (!isset($spotsA[$keyStringTotal]['machineWorkingTime'])) {
                            $spotsA[$keyStringTotal]['machineWorkingTime'] = 0;
                        }
                        $spotsA[$keyStringTotal]['machineWorkingTime'] += $spotsA[$keyString]['machineWorkingTime'];
                        if (!isset($spotsA[$keyStringTotal]['rodNumber'])) {
                            $spotsA[$keyStringTotal]['rodNumber'] = 0;
                        }
                        $spotsA[$keyStringTotal]['rodNumber'] += $spotsA[$keyString]['rodNumber'];
                    }
                }
                $hours = floor($init / 3600);
                $minutes = floor(($init / 60) % 60);
                $seconds = $init % 60;
                $spotsA[$keyString]['name'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
            }
            $keyString = sprintf("%s_%d_%d", $totalIndex, 0, $i);
            if (!isset($spotsA[$keyString]['name'])) {
                $spotsA[$keyString]['name'] = 0;
            }
            $init = $totalValue;
            if (isset($spotsA[$keyString]['machineWorkingTime']) && isset($spotsA[$keyString]['rodNumber'])) {
                $init = $spotsA[$keyString]['machineWorkingTime'] / $spotsA[$keyString]['rodNumber'];
            }
            $hours = floor($init / 3600);
            $minutes = floor(($init / 60) % 60);
            $seconds = $init % 60;
            $spotsA[$keyString]['name'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        }

        foreach ($shiftCount as $index => $value) {
            $keyString = sprintf("%s_%d_%d", $index, 1, 21);
            $spotsA[$keyString]['name'] = $value;
        }

        $result = $this->em->createQuery('
            SELECT sp.name, sp.dateString, sp.datePlan,
            (
                SELECT COUNT(wip.id)
                FROM '.WorkerInPlan::class.' wip
                WHERE wip.plan = sp.id and (wip.minusLog is null or wip.minusLog = 0)
            ) as workerCount
            FROM '.ShiftPlan::class.' sp
            WHERE sp.productionLine = :line and sp.datePlan >= :startDate and sp.datePlan <= :endDate
        ')
            ->execute([
                'line' => $this->type,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
        if ($result) {
            foreach ($result as $r) {
                /** @var \DateTime $today */
                $today = clone $r['datePlan'];
                /** @var \DateTime $tomorrow */
                $tomorrow = clone $r['datePlan'];
                $today->setTime(18, 0, 0);
                $tomorrow->setTime(6,0,0);
                $tomorrow->modify('+1 day');
                if (
                    ($r['name'] == 1 && $today->getTimestamp() < time())
                    ||
                    ($r['name'] == 2 && $tomorrow->getTimestamp() < time())
                ) {
                    $floor = 20;
                    $keyString = sprintf("%s_%d_%d", $r['name'], 1, $floor);
                    if (!isset($spotsA[$keyString])) {
                        $spotsA[$keyString] = [
                            'name' => 0
                        ];
                    }
                    $spotsA[$keyString]['name'] += $r['workerCount'];
                    $keyString = sprintf("%s_%d_%d", '3', 1, $floor);
                    if (!isset($spotsA[$keyString])) {
                        $spotsA[$keyString] = [
                            'name' => 0
                        ];
                    }
                    $spotsA[$keyString]['name'] += $r['workerCount'];
                }
            }
        }

        $numberPeoplePerShift = $monthlyLaborCosts = 0;
        if ($settings) {
            $numberPeoplePerShift = $settings->numberPeoplePerShift;
            $monthlyLaborCosts = $settings->monthlyLaborCosts;
        }

        for ($i = 1; $i <= 3 + 1; $i++) {
            $keyStringSourcePPS1 = sprintf("%s_%d_%d", $i, 1, 20);
            $keyStringSourcePPS2 = sprintf("%s_%d_%d", $i, 1, 21);
            $keyString = sprintf("%s_%d_%d", $i, 1, 22);
            $keyStringCost = sprintf("%s_%d_%d", $i, 1, 23);
            $keyStringSourceC1 = sprintf("%s_%d_%d", $i, 0, 2);

            if (isset($spotsA[$keyStringSourcePPS2]['name'])) {
                $div = $spotsA[$keyStringSourcePPS2]['name'] * $numberPeoplePerShift;
                if (isset($spotsA[$keyStringSourcePPS1]['name']) && $div > 0) {
                    $spotsA[$keyString]['name'] = round($spotsA[$keyStringSourcePPS1]['name'] / $div * 100, 3);
                } else {
                    $spotsA[$keyString]['name'] = 0;
                }
            }

            $totalAreaPc = isset($spotsA[$keyStringSourceC1]['name']) ? floatval($spotsA[$keyStringSourceC1]['name']) : 0;
            if ($totalAreaPc > 0) {
                $spotsA[$keyStringCost]['name'] = $monthlyLaborCosts / ($totalAreaPc * 100) / 100;
            } else {
                $spotsA[$keyStringCost]['name'] = '';
            }

            $keyString = sprintf("%s_%d_%d", $i, 0, 1);
            if (isset($spotsA[$keyString]['name']) && isset($spotsA[$keyStringSourcePPS2]['name'])) {
                $rodNumber = intval($spotsA[$keyString]['name']);
                $shiftCountt = intval($spotsA[$keyStringSourcePPS2]['name']);
                $keyString = sprintf("%s_%d_%d", $i, 0, 10);
                $spotsA[$keyString]['name'] = round($rodNumber / $shiftCountt, 3);
            }
        }

        foreach ($spotsA as $key => $value) {
            if (is_numeric($spotsA[$key]['name']) && is_float($spotsA[$key]['name'])) {
                $spotsA[$key]['name'] = number_format($spotsA[$key]['name'], 3, '.', ' ');
            }
        }

        $this->template->cusFloors = $cusFloors;
        $this->template->spotsA = $spotsA;
        $this->template->columnsA = [-1, 1 => 'Ranní směna', 2 => 'Noční směna', 3 => 'Celkem'];
        $this->template->floorsA = [
            [1,2,3,5,6,7,8,9,10],
            [20,21,22,23],
            [40],
            [60],
            [80],
        ];
        $this->template->placesA = array(1);
        $this->template->groups = [
            'Výroba',
            'Personální',
            'Kvalita',
            'Chemik',
            'Údržba'
        ];
        $this->template->groupColor = [
            '#104d8e',
            '#868686',
            '#fe0000',
            '#ff6600',
            '#28aa4a'
        ];
        $this->template->tbodyVisible = $this->sess->tbodyVisible ?? [];
    }

    /**
     * ACL name='Zobrazení stránky se sumarizací průběhu výroby dle směn'
     */
    public function renderShiftSummary($type)
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

        $settings = $this->em->getProductionProgressReportSettingRepository()->findOneBy(['line' => $this->type]);

        if (!$this->dateRange) {
            $startMonthDay = new \DateTime();
            $startMonthDay->setTimestamp(strtotime('first day of this month'));
            $endMonthDay = new \DateTime();
            $endMonthDay->setTimestamp(strtotime('last day of this month'));
            $startMonthDay->setTime(0,0,0);
            $endMonthDay->setTime(23,59,59);
            $this->dateRange = sprintf("%s 00:00:00 - %s 23:59:59", $startMonthDay->format('j. n. Y'), $endMonthDay->format('j. n. Y'));
        }

        $dateSplit = explode(' - ', $this->dateRange);
        $startDate = \DateTime::createFromFormat('j. n. Y H:i:s', $dateSplit[0]);
        $endDate = \DateTime::createFromFormat('j. n. Y H:i:s', $dateSplit[1]);

        $this->template->dateInput = sprintf("%s - %s", $startDate->format('j. n. Y H:i:s'), $endDate->format('j. n. Y H:i:s'));

        $spotsA = array();

        $serverIp = $_SERVER['SERVER_NAME'] == 'galma.local' ? '192.168.100.4' : '192.168.1.112';

        $startDateUTC = clone $startDate;
        $endDateUTC = clone $endDate;
        $startDateUTC->setTimezone(new \DateTimeZone('UTC'));
        $endDateUTC->setTimezone(new \DateTimeZone('UTC'));
        $query = http_build_query([
            'startDate' => $startDateUTC->format('Y-m-d\TH:i:s.000\Z'),
            'endDate' => $endDateUTC->format('Y-m-d\TH:i:s.000\Z')
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf("http://%s:3000/v1/reports/get-production-progress/%d?%s", $serverIp, $this->type, $query),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 10,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $status = $info['http_code'];
        $data = null;
        if ($status == 200) {
            $data = json_decode($response, true);
        } else if ($status == 0) {
            $this->flashMessage('API není momentálně dostupná.', 'warning');
        } else {
            $this->flashMessage('Odpověď z API není správná.', 'error');
        }

        $cusFloors = [
            1 => 'celkový počet tyčí',
            2 => 'celková plocha [m<sup>2</sup>]',
            3 => 'Ø plocha na tyč [m<sup>2</sup>]',
            /*4 => 'Ø počet závěsů za směnu',*/
            5 => 'takt - čistý',
            6 => 'takt - hrubý',
            7 => 'provozní hodiny linky',
            8 => 'celková vyrobená cena',
            9 => 'Ø vyrobená cena na tyč',
            10 => 'Ø počet tyčí za směnu',
            20 => 'počet zaměstnanců',
            21 => 'počet směn',
            22 => 'obsazenost směn v %',
            23 => 'mzdové náklady na dm<sup>2</sup>',
            40 => '',
            60 => '',
            80 => '',
        ];

        $shiftIndex = [
            'A' => 1,
            'B' => 2,
            'C' => 3,
            'D' => 4
        ];

        $totalIndex = 5;
        $shiftCount = [
            $totalIndex => 0
        ];
        if ($data) {
            foreach ($data as $index => $d) {
                $indexCol = $shiftIndex[$d['shift']];
                if (!isset($shiftCount[$indexCol])) {
                    $shiftCount[$indexCol] = 0;
                }

                $shiftCount[$indexCol]++;
                $shiftCount[$totalIndex]++;

                foreach ($cusFloors as $ii => $vv) {
                    if ($ii > 10) {
                        continue;
                    }
                    $keyString = sprintf("%s_%d_%d", $indexCol, 0, $ii);
                    $value = 0;
                    switch ($ii) {
                        case 1:
                            $value = $d['rodNumber'];
                            break;
                        case 2:
                            $value = round($d['areaPc'], 3);
                            break;
                        case 3:
                            $value = 0;
                            if (!isset($spotsA[$keyString]['areaPc'])) {
                                $spotsA[$keyString]['areaPc'] = 0;
                            }
                            $spotsA[$keyString]['areaPc'] += $d['areaPc'];
                            if (!isset($spotsA[$keyString]['rodNumber'])) {
                                $spotsA[$keyString]['rodNumber'] = 0;
                            }
                            $spotsA[$keyString]['rodNumber'] += $d['rodNumber'];
                            break;
                        case 5:
                            $value = 0;
                            if (!isset($spotsA[$keyString]['machineWorkingTime'])) {
                                $spotsA[$keyString]['machineWorkingTime'] = 0;
                            }
                            $spotsA[$keyString]['machineWorkingTime'] += $d['machineWorkingTime'];
                            if (!isset($spotsA[$keyString]['rodNumber'])) {
                                $spotsA[$keyString]['rodNumber'] = 0;
                            }
                            $spotsA[$keyString]['rodNumber'] += $d['rodNumber'];
                            break;
                        case 6:
                            $value = 0;
                            if (!isset($spotsA[$keyString]['machineWorkingTime'])) {
                                $spotsA[$keyString]['machineWorkingTime'] = 0;
                            }
                            $spotsA[$keyString]['machineWorkingTime'] += $d['totalMachineWorkingTime'];
                            if (!isset($spotsA[$keyString]['rodNumber'])) {
                                $spotsA[$keyString]['rodNumber'] = 0;
                            }
                            $spotsA[$keyString]['rodNumber'] += $d['rodNumber'];
                            break;
                        case 7:
                            $value = 0;
                            if ($d['rodNumber'] > 0) {
                                $value = $d['machineWorkingTime'];
                            }
                            break;
                        case 8:
                            $value = round($d['price'], 3);
                            break;
                        case 9:
                            $value = 0;
                            if (!isset($spotsA[$keyString]['price'])) {
                                $spotsA[$keyString]['price'] = 0;
                            }
                            $spotsA[$keyString]['price'] += $d['price'];
                            if (!isset($spotsA[$keyString]['rodNumber'])) {
                                $spotsA[$keyString]['rodNumber'] = 0;
                            }
                            $spotsA[$keyString]['rodNumber'] += $d['rodNumber'];
                            break;
                    }
                    if (!isset($spotsA[$keyString]['name'])) {
                        $spotsA[$keyString]['id'] = $ii;
                        $spotsA[$keyString]['name'] = 0;
                    }
                    $spotsA[$keyString]['name'] += $value;
                    $keyString = sprintf("%s_%d_%d", $totalIndex, 0, $ii);
                    if (!isset($spotsA[$keyString]['name'])) {
                        $spotsA[$keyString]['id'] = $ii;
                        $spotsA[$keyString]['name'] = 0;
                    }
                    $spotsA[$keyString]['name'] += $value;
                }
            }
        }

        $indexes = [];
        foreach ($spotsA as $key => $value) {
            list($id, $floor, $val) = explode('_', $key);
            $keyString = sprintf("%s_%d_%d", $totalIndex, $floor, $val);
            if (isset($value['areaPc']) && isset($value['rodNumber'])) {
                if (!isset($spotsA[$keyString]['total'])) {
                    $spotsA[$keyString]['total'] = 0;
                }
                if (!isset($spotsA[$keyString]['divider'])) {
                    $spotsA[$keyString]['divider'] = 0;
                }
                $spotsA[$key]['name'] = round($value['areaPc'] / $value['rodNumber'], 3);
                $spotsA[$keyString]['total'] += $value['areaPc'];
                $spotsA[$keyString]['divider'] += $value['rodNumber'];
                if (!in_array($keyString, $indexes)) {
                    $indexes[] = $keyString;
                }
            } elseif (isset($value['price']) && isset($value['rodNumber'])) {
                if (!isset($spotsA[$keyString]['total'])) {
                    $spotsA[$keyString]['total'] = 0;
                }
                if (!isset($spotsA[$keyString]['divider'])) {
                    $spotsA[$keyString]['divider'] = 0;
                }
                $spotsA[$key]['name'] = round($value['price'] / $value['rodNumber'], 3);
                $spotsA[$keyString]['total'] += $value['price'];
                $spotsA[$keyString]['divider'] += $value['rodNumber'];
                if (!in_array($keyString, $indexes)) {
                    $indexes[] = $keyString;
                }
            }
        }
        if (count($indexes)) {
            foreach ($indexes as $index) {
                $spotsA[$index]['name'] = $spotsA[$index]['total'] / $spotsA[$index]['divider'];
            }
        }

        for ($i = 5; $i <= 7; $i++) {
            $totalValue = 0;
            for ($ii = 1; $ii <= count($shiftIndex); $ii++) {
                $keyString = sprintf("%s_%d_%d", $ii, 0, $i);
                if (!isset($spotsA[$keyString]['name'])) {
                    $spotsA[$keyString]['name'] = 0;
                }
                if (isset($spotsA[$keyString]['machineWorkingTime']) && isset($spotsA[$keyString]['rodNumber'])) {
                    $spotsA[$keyString]['name'] = $spotsA[$keyString]['machineWorkingTime'] / $spotsA[$keyString]['rodNumber'];
                }
                $init = $spotsA[$keyString]['name'];
                if ($init <= 0) {
                    $spotsA[$keyString]['name'] = '';
                    continue;
                }
                if ($ii >= 1 && $ii <= 4) {
                    $totalValue += $init;

                    if (isset($spotsA[$keyString]['machineWorkingTime']) && isset($spotsA[$keyString]['rodNumber'])) {
                        $keyStringTotal = sprintf("%s_%d_%d", $totalIndex, 0, $i);
                        if (!isset($spotsA[$keyStringTotal]['machineWorkingTime'])) {
                            $spotsA[$keyStringTotal]['machineWorkingTime'] = 0;
                        }
                        $spotsA[$keyStringTotal]['machineWorkingTime'] += $spotsA[$keyString]['machineWorkingTime'];
                        if (!isset($spotsA[$keyStringTotal]['rodNumber'])) {
                            $spotsA[$keyStringTotal]['rodNumber'] = 0;
                        }
                        $spotsA[$keyStringTotal]['rodNumber'] += $spotsA[$keyString]['rodNumber'];
                    }
                }
                $hours = floor($init / 3600);
                $minutes = floor(($init / 60) % 60);
                $seconds = $init % 60;
                $spotsA[$keyString]['name'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
            }
            $keyString = sprintf("%s_%d_%d", $totalIndex, 0, $i);
            if (!isset($spotsA[$keyString]['name'])) {
                $spotsA[$keyString]['name'] = 0;
            }
            $init = $totalValue;
            if (isset($spotsA[$keyString]['machineWorkingTime']) && isset($spotsA[$keyString]['rodNumber'])) {
                $init = $spotsA[$keyString]['machineWorkingTime'] / $spotsA[$keyString]['rodNumber'];
            }
            $hours = floor($init / 3600);
            $minutes = floor(($init / 60) % 60);
            $seconds = $init % 60;
            $spotsA[$keyString]['name'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        }

        foreach ($shiftCount as $index => $value) {
            $keyString = sprintf("%s_%d_%d", $index, 1, 21);
            $spotsA[$keyString]['name'] = $value;
        }

        $result = $this->em->createQuery('
            SELECT sp.name, sp.dateString, sp.datePlan, sp.shift,
            (
                SELECT COUNT(wip.id)
                FROM '.WorkerInPlan::class.' wip
                WHERE wip.plan = sp.id and (wip.minusLog is null or wip.minusLog = 0)
            ) as workerCount
            FROM '.ShiftPlan::class.' sp
            WHERE sp.productionLine = :line and sp.datePlan >= :startDate and sp.datePlan <= :endDate
        ')->execute([
            'line' => $this->type,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        if ($result) {
            foreach ($result as $r) {
                /** @var \DateTime $today */
                $today = clone $r['datePlan'];
                /** @var \DateTime $tomorrow */
                $tomorrow = clone $r['datePlan'];
                $today->setTime(18, 0, 0);
                $tomorrow->setTime(6,0,0);
                $tomorrow->modify('+1 day');
                if (
                    ($r['name'] == 1 && $today->getTimestamp() < time())
                    ||
                    ($r['name'] == 2 && $tomorrow->getTimestamp() < time())
                ) {
                    $index = $shiftIndex[$r['shift']];
                    $floor = 20;
                    $keyString = sprintf("%s_%d_%d", $index, 1, $floor);
                    if (!isset($spotsA[$keyString])) {
                        $spotsA[$keyString] = [
                            'name' => 0
                        ];
                    }
                    $spotsA[$keyString]['name'] += $r['workerCount'];
                    $keyString = sprintf("%s_%d_%d", $totalIndex, 1, $floor);
                    if (!isset($spotsA[$keyString])) {
                        $spotsA[$keyString] = [
                            'name' => 0
                        ];
                    }
                    $spotsA[$keyString]['name'] += $r['workerCount'];
                }
            }
        }

        $numberPeoplePerShift = $monthlyLaborCosts = 0;
        if ($settings) {
            $numberPeoplePerShift = $settings->numberPeoplePerShift;
            $monthlyLaborCosts = $settings->monthlyLaborCosts;
        }

        for ($i = 1; $i <= count($shiftIndex) + 1; $i++) {
            $keyStringSourcePPS1 = sprintf("%s_%d_%d", $i, 1, 20);
            $keyStringSourcePPS2 = sprintf("%s_%d_%d", $i, 1, 21);
            $keyString = sprintf("%s_%d_%d", $i, 1, 22);
            $keyStringCost = sprintf("%s_%d_%d", $i, 1, 23);
            $keyStringSourceC1 = sprintf("%s_%d_%d", $i, 0, 2);

            if (isset($spotsA[$keyStringSourcePPS2]['name'])) {
                $div = $spotsA[$keyStringSourcePPS2]['name'] * $numberPeoplePerShift;
                if (isset($spotsA[$keyStringSourcePPS1]['name']) && $div > 0) {
                    $spotsA[$keyString]['name'] = round($spotsA[$keyStringSourcePPS1]['name'] / $div * 100, 3);
                } else {
                    $spotsA[$keyString]['name'] = 0;
                }
            }

            $totalAreaPc = isset($spotsA[$keyStringSourceC1]['name']) ? floatval($spotsA[$keyStringSourceC1]['name']) : 0;
            if ($totalAreaPc > 0) {
                $spotsA[$keyStringCost]['name'] = $monthlyLaborCosts / ($totalAreaPc * 100) / 100;
            } else {
                $spotsA[$keyStringCost]['name'] = '';
            }

            $keyString = sprintf("%s_%d_%d", $i, 0, 1);
            if (isset($spotsA[$keyString]['name']) && isset($spotsA[$keyStringSourcePPS2]['name'])) {
                $rodNumber = intval($spotsA[$keyString]['name']);
                $shiftCountt = intval($spotsA[$keyStringSourcePPS2]['name']);
                $keyString = sprintf("%s_%d_%d", $i, 0, 10);
                $spotsA[$keyString]['name'] = round($rodNumber / $shiftCountt, 3);
            }
        }

        foreach ($spotsA as $key => $value) {
            if (is_numeric($spotsA[$key]['name']) && is_float($spotsA[$key]['name'])) {
                $spotsA[$key]['name'] = number_format($spotsA[$key]['name'], 2, '.', ' ');
            }
        }

        $this->template->cusFloors = $cusFloors;
        $this->template->spotsA = $spotsA;
        $this->template->columnsA = [
            -1,
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'Celkem'
        ];
        $this->template->floorsA = [
            [1,2,3,5,6,7,8,9,10],
            [20,21,22],
            [40],
            [60],
            [80],
        ];
        $this->template->placesA = array(1);
        $this->template->groups = [
            'Výroba',
            'Personální',
            'Kvalita',
            'Chemik',
            'Údržba'
        ];
        $this->template->groupColor = [
            '#104d8e',
            '#868686',
            '#fe0000',
            '#ff6600',
            '#28aa4a'
        ];
        $this->template->tbodyVisible = $this->sess->tbodyVisible ?? [];
    }

    /**
     * ACL name='Formulář pro editaci norem'
     */
    public function createComponentNormSettingForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ProductionProgressReportSetting::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit normy', 'success'], ['Nepodařilo se uložit normy!', 'error']);
        $form->setRedirect('this');
        return $form;
    }

    public function handleToggleTbody($groupId, $visible)
    {
        $groupId = intval($groupId);
        $visible = $visible == 'true';
        if (!isset($this->sess->tbodyVisible)) {
            $this->sess->tbodyVisible = [];
        }
        $this->sess->tbodyVisible[$groupId] = $visible;
        die;
    }
}