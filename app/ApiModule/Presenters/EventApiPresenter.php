<?php

namespace App\ApiModule\Presenters;

use Nette;
use PDO;
use PDOException;
use Nette\Utils\DateTime;

class EventApiPresenter extends BaseApiPresenter
{
    public function startup() {
        parent::startup();
        $this->sess = $this->session->getSection('backend');
    }

    public function actionGetEvents() {
        $values = $this->getPost();

        if (isset($values['type'])) {
            if ($values['type'] == 0) {
                $this->actionGetEventsVisit($values);
            } else if ($values['type'] == 1) {
                $this->actionGetEventsWorkerTender($values);
            } else {
                $this->actionGetEventsExternServiceVisit($values);
            }
        }
    }

    public function actionChangeEvent() {
        $values = $this->getPost();

        if (isset($values['type'])) {
            if ($values['type'] == 0) {
                $this->actionChangeEventVisit($values);
            } else if ($values['type'] == 1) {
                $this->actionChangeEventWorkerTender($values);
            } else {
                $this->actionChangeEventExternServiceVisit($values);
            }
        }
    }

    /* start Worker Tender */
    private function actionGetEventsWorkerTender($values)
    {
        $events = array(); // Get events for calendar

        $qb = $this->em->createQueryBuilder();
        $qb->select('wt')
            ->from(\App\Model\Database\Entity\WorkerTender::class, 'wt')
            ->where('wt.tenderDate >= ' .  DateTime::from(strtotime('first day of this week 0:00:00'))->format('Y-m-d'))
            ->orderBy('wt.name', 'ASC');
        ;
        $tenders = $qb->getQuery()->getResult();

        foreach ($tenders as $tender) {
            $timeS = preg_split( "/(:|-|\.|,)/", $tender->timeStart);
            $timeE = preg_split( "/(:|-|\.|,)/", $tender->timeEnd);

            $name = $tender->name . ' (' . count($tender->workers) . ')';
            $events[$tender->id] = ['id' => $tender->id, 'title' => $name, 'desc' => '',
                'color' => ('#a8eaff'), 'y' => intval($tender->tenderDate->format('Y')),
                'm' => (intval($tender->tenderDate->format('n'))-1), 'd' => intval($tender->tenderDate->format('j')),
                'sh' => (isset($timeS[0]) ? intval($timeS[0]) : ''), 'sm' => (isset($timeS[1]) ? intval($timeS[1]) : ''),
                'eh' => (isset($timeE[0]) ? intval($timeE[0]) : ''), 'em' => (isset($timeE[1]) ? intval($timeE[1]) : '')
            ];
        }

        $this->sendSuccess(array('events' => $events));
    }

    private function actionChangeEventWorkerTender($values) {

        $newDate = $values['y'] . '-' . sprintf('%02d', $values['m']) . '-' . sprintf('%02d', $values['d']);
        $newStart = $values['sh'] . ':' . sprintf('%02d', $values['sm']);
        $newEnd = $values['eh'] . ':' . sprintf('%02d', $values['em']);

        $tender = $this->em->getWorkerTenderRepository()->find($values['id']);
        if($tender) {
            $tender->setTenderDate(new \DateTime($newDate));
            $tender->setTimeStart($newStart);
            $tender->setTimeEnd($newEnd);
            $this->em->flush();
        }

        $this->sendSuccess('Success');
    }
    /* end Worker Tender */

    /* start Visit */
    private function actionGetEventsVisit($values) {
        $events = array(); // Get events for calendar

        $arrDisplay = array();
        if($this->sess->displayed) {
            $result = array_keys($this->sess->displayed, true);
            foreach ($result as $dispId) {
                $arrDisplay[] = str_replace('displayed', '', $dispId);
            }
        }

        $visits = $this->em->getVisitRepository()->findAll();

        foreach ($visits as $visit) {
            if (!$visit->dateStart) {
                continue;
            }

            if($visit->worker && $arrDisplay) {
                $found = 0;
                foreach ($visit->worker as $wov) {
                    if ($wov->worker) {
                        if (in_array($wov->worker->id, $arrDisplay)) {
                            $found = 1;
                        }
                    }
                }
                if(!$found) {
                    continue;
                }
            }

            $this->setEventTimes($visit);
            $events[] = $this->formatEvent($visit);
        }

        $this->sendSuccess(array('events' => $events));
    }

    private function actionChangeEventVisit($values) {

        $visit = $this->em->getVisitRepository()->find($values['id']);
        if($visit) {
            $timeFrame = $values['sh'] . ':' . sprintf('%02d', $values['sm']);
            $date = new DateTime();
            $date->setDate($values['y'], $values['m'], $values['d']);
            $visit->setOnceTimes($timeFrame);
            $visit->setDateStart($date);

            $stMinutes = (intval($values['sh']) * 60) + intval($values['sm']);
            $endMinutes = (intval($values['eh']) * 60) + intval($values['em']);
            $durMinutes = $endMinutes - $stMinutes;
            $finMinutes = $durMinutes % 60;
            $durHours = ($durMinutes - $finMinutes) / 60;
            $visit->setDurationHours($durHours);
            $visit->setDurationMinutes($finMinutes);
            $this->em->flush($visit);
        }

        $this->sendSuccess('Success');
    }

    private function setEventTimes(\App\Model\Database\Entity\Visit &$visit)
    {
        $time = $this->parseTime($visit->onceTimes);
        $visit->dateStart->setTime($time->hours, $time->minutes);
    }

    private function parseTime($timeString)
    {
        $result = [
            'minutes' => 0,
            'hours' => 0
        ];
        $time = explode(':', $timeString);
        if (count($time) === 2) {
            $result['hours'] = $time[0];
            $result['minutes'] = $time[1];
        }
        return (object) $result;
    }

    private function formatEvent(\App\Model\Database\Entity\Visit $visit, $startDate=null)
    {
        $startDate = $startDate ? $startDate : $visit->dateStart;

        if ($visit->durationHours == -1) {
            $durationHours = 1;
        } else {
            $durationHours = $visit->durationHours;
        }

        $sMins = ($startDate->format('H') * 60) + $startDate->format('i');
        $sMins += ($durationHours * 60) + $visit->durationMinutes;
        $finMinutes = $sMins % 60;
        $finHours = ($sMins - $finMinutes) / 60;

        if ($visit->worker) {
            if (isset($visit->worker[0]) && $visit->worker[0]->worker) {
                $color = (isset($visit->worker[0]) && $visit->worker[0]->worker->calendarColor) ? '#' . $visit->worker[0]->worker->calendarColor : '#3174ad';
            } else {
                $color = '#3174ad';
            }
        } else {
            $color = '#3174ad';
        }
        return [
            'id' => $visit->id,
            'title' => ($visit->traffic ? $visit->traffic->name . ' - ' : '') . $visit->name,
            'desc' => '',
            'color' => $color,
            'y' => $startDate->format('Y'),
            'm' => $startDate->format('n') - 1,
            'd' => $startDate->format('j'),
            'sh' => $startDate->format('H'),
            'sm' => $startDate->format('i'),
            'eh' => $finHours,
            'em' => $finMinutes
        ];
    }
    /* end Visit */

    /* start Extern Service Visit */
    private function actionGetEventsExternServiceVisit($values)
    {
        $events = array(); // Get events for calendar

        $qb = $this->em->createQueryBuilder();
        $qb->select('s')
            ->from(\App\Model\Database\Entity\ExternServiceVisit::class, 's')
            ->where('s.visitDate >= ' .  DateTime::from(strtotime('first day of this week 0:00:00'))->format('Y-m-d'))
            ->orderBy('s.name', 'ASC');
        ;
        $visits = $qb->getQuery()->getResult();

        foreach ($visits as $sv) {
            $name = $sv->name . ' (' . count($sv->machines) . ')';
            $events[$sv->id] = ['id' => $sv->id, 'title' => $name, 'desc' => '',
                'color' => ('#'. ($sv->calendarColor ? $sv->calendarColor : 'a8eaff')), 'y' => intval($sv->visitDate->format('Y')),
                'm' => (intval($sv->visitDate->format('n'))-1), 'd' => intval($sv->visitDate->format('j')),
                //'sh' => '0', 'sm' => '0',
                //'eh' => '23', 'em' => '59'
                'sh' => '', 'sm' => '',
                'eh' => '', 'em' => ''
            ];
        }

        $this->sendSuccess(array('events' => $events));
    }

    private function actionChangeEventExternServiceVisit($values) {

        $newDate = $values['y'] . '-' . sprintf('%02d', $values['m']) . '-' . sprintf('%02d', $values['d']);

        $tender = $this->em->getExternServiceVisitRepository()->find($values['id']);
        if($tender) {
            $tender->setVisitDate(new \DateTime($newDate));
            $this->em->flush();
        }

        $this->sendSuccess('Success');
    }
    /* end Extern Service Visit */

}
