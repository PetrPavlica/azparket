<?php

namespace App\Model\Facade;

use App\Model\Database\EntityManager;
use App\Model\Database\Entity\MenuLanguage;
use Nette\Database\Explorer;
use Nette\Utils\DateTime;
use App\Model\Utils\TimeHelper;
use App\Model\Database\Entity\Reservation as ReservationEntity;
use Mpdf\Tag\B;

class Reservation
{

    /** @var EntityManager */
    private EntityManager $em;

    /** @var Explorer */
    protected $db;

    /**
     * Construct
     * @param EntityManager $em
     * @param Explorer $db
     */
    public function __construct(EntityManager $em, Explorer $db) {
        $this->em = $em;
        $this->db = $db;
    }

    public function checkReservationAvailability($reservationItem, $dateStr, $timeStrFrom, $timeStrTo)
    {
        /*if (is_numeric($reservation)) {
            $reservation = $this->em->getReservationRepository()->find($reservation);
            if (!$reservation) {
                return null;
            }
        }*/

        $date = \DateTime::createFromFormat('j. n. Y', $dateStr);
        $dateFrom = $date->modify('+' . TimeHelper::timeStrToMinutes($timeStrFrom . ' minutes'));
        $dateTo = (clone $date)->modify('+' . TimeHelper::timeStrToMinutes($timeStrTo . ' minutes'));


        // check if reserved
        $rs = $this->em->getReservationRepository()->createQueryBuilder('r')
            ->where(
                'r.reservationItem = :ri AND r.canceled != 1'
                . ' AND ('
                . ' (r.dateFrom <= :dateFrom AND r.dateTo > :dateFrom)'
                . ' OR (r.dateFrom < :dateTo AND r.dateTo >= :dateTo)'
                . ' OR (r.dateFrom < :dateFrom AND r.dateTo > :dateTo)'
                . ' )'
            )
            ->setParameters(['ri' => $reservationItem, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo])
            ->getQuery()->getResult();
        bdump($rs, 'resultReserved');
        if (count($rs)) {
            return false;
        }

        // check if in reservable period
        $day = date('w', $date);
        $dayToPropTrans = [
            1 => ['timeMondayFrom', 'timeMondayTo'],
            2 => ['timeTuesdayFrom', 'timeTuesdayTo'],
            3 => ['timeWednesdayFrom', 'timeWednesdayTo'],
            4 => ['timeThursdayFrom', 'timeThursdayTo'],
            5 => ['timeFridayFrom', 'timeFridayTo'],
            6 => ['timeSaturdayFrom', 'timeSaturdayTo'],
            7 => ['timeSundayFrom', 'timeSundayTo']
        ];
        $rs = $this->em->getReservationItemRepository()->createQueryBuilder('r')
            ->where(
            'r.reservationItem = :ri'
            . ' AND ('
            . ' (r.' . $dayToPropTrans[$day][0] . ' <= :dateFrom AND r.' . $dayToPropTrans[$day][1] . ' > :dateFrom)'
            . ' OR (r.' . $dayToPropTrans[$day][0] . ' < :dateTo AND r.' . $dayToPropTrans[$day][1] . ' >= :dateTo)'
            . ' OR (r.' . $dayToPropTrans[$day][0] . ' < :dateFrom AND r.' . $dayToPropTrans[$day][1] . ' > :dateTo)'
            . ' )'
        )
        ->setParameters(['ri' => $reservationItem, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo])
        ->getQuery()->getResult();
        bdump($rs, 'resultInReservablePeriod');
        if (!count($rs)) {
            return false;
        }

        return true;
    }

    public function processReservationForm($values, $userId = null, $adminMode = false, &$messages = [])
    {
       
        // check reservation
        /*if (!$this->reservationFac->checkReservationAvailability($values->reservationItem, $values->date, $values->timeFrom, $values->timeTo)) {
            $this->flashMessage('Termín je již obsazen nebo je mimo rezervovatelné období', 'warning');
            return;
        }*/


        // check res item
        $reservationItem = $this->em->getReservationItemRepository()->find($values->reservationItem);
        
        if (!$reservationItem) {
            if ($adminMode) {                
                $messages[] = ['Rezervaci se nepodařilo uložit, jelikož se nepodařilo dohledat rezervovatelnou položku', 'error'];
            } else {
                $messages[] = ['Omlouváme se, ale při odesílání došlo k chybě. Opakujte proces nebo nás kontaktujte jiným způsobem viz. menu Kontakty', 'error'];
            }
            return null;
        }

        $reservation = null;

        // edit in admin mode
        if ($adminMode) {
            if ($values->id) {
                $reservation = $this->em->getReservationRepository()->find($values->id);
                if (!$reservation) {
                    $messages[] = ['Rezervaci se nepodařilo nalézt', 'error'];
                }
            }
        }

        // time string validity
        if (!TimeHelper::checkTimeStrValid($values->timeFrom, $reservationItem->reservablePeriod) || !TimeHelper::checkTimeStrValid($values->timeTo, $reservationItem->reservablePeriod)) {
            $messages[] = ['Zvolený čas neodpovídá možnému rozsahu času. (Zvolte čas opakující se po ' . $reservationItem->reservablePeriod . ' minutách)', 'warning'];
            return null;
        }


        // convert date and times to datetimes
        $dateFrom = \DateTime::createFromFormat('j. n. Y', $values->date);

        $aux = TimeHelper::timeStrToTimeArr($values->timeFrom);
        $dateFrom->setTime($aux[0], $aux[1]);
        $aux = TimeHelper::timeStrToTimeArr($values->timeTo);
        $dateTo = (clone $dateFrom)->setTime($aux[0], $aux[1]);

        
        // prepare new reservation
        if (!$reservation) {
            $reservation = new \App\Model\Database\Entity\Reservation();

            if ($userId) {
                $reservation->setOriginator($this->em->getUserRepository()->find($userId));
            }
        }
        $reservation->setDateFrom($dateFrom);
        $reservation->setDateTo($dateTo);
        $reservation->setReservationItem($reservationItem);

        if ($reservationItem->pricePerHour) {
            $hours = ($dateTo->getTimestamp() - $dateFrom->getTimestamp()) / 3600;
            $reservation->setPrice(floor(($hours * $reservationItem->pricePerHour) * 100) / 100);
        }

        $this->em->persist($reservation);
        $this->em->flush();

        if (!isset($values->newCustomer)) {
            $values->newCustomer = null;
        }
        
        // create / udpate customer
        if (($adminMode && !$values->newCustomer && $values->customer) || (!$adminMode && $values->customer)) {
            $customer = $this->em->getCustomerRepository()->find($values->customer);
        } else if (($adminMode && $values->newCustomer) || (!$adminMode)) {
            $customer = $this->em->getCustomerRepository()->createQueryBuilder('c')
            ->where('c.email = :email AND c.name  = :name AND c.surname = :surname')
            ->setParameters(['email' => $values->email, 'name' => $values->name, 'surname' => $values->surname])
            ->getQuery()->getOneOrNullResult();

            if (!$customer) {
                $customer = new \App\Model\Database\Entity\Customer();
                $customer->setName($values->name);
                $customer->setSurname($values->surname);
                $customer->setFullname($values->name . ' ' . $values->surname);
                $customer->setEmail($values->email);
                $customer->setPhone($values->phone);
                $customer->setActive(1);
                $customer->setCreatedByReservation(1);

                $this->em->persist($customer);
                //$this->em->flush();
            } else {
                $customer->setPhone($values->phone);
            }
        }

        $reservation->setCustomer($customer);
        
        if ($adminMode) {
            try {
                $this->em->flush();
            } catch(\Exception $e) {
                $messages[] = ['Rezervaci se nepodařilo uložit', 'error'];
                return null;
            }
        } else {
            try {
                $this->em->flush();
            } catch(\Exception $e) {
                $messages[] = ['Omlouváme se, ale při odesílání došlo k chybě. Opakujte proces nebo nás kontaktujte jiným způsobem viz. menu Kontakty', 'error'];
                return null;
            }
        }

        if ($adminMode && $values->repeat) {
            if (!is_array($resArr = $this->repeatReservation($reservation, $values))) {
                $messages[] = ['Nepodařilo se založit opakované rezervace', 'error'];
            } else {
                if (count($resArr)) {
                    $messages[] = ['Založilo se ' . count($resArr) .' opakovaných rezervací', 'success'];
                } else {
                    $messages[] = ['Nebyla založena žádná opakovaná rezervace', 'warning'];
                }
            }
        }

        return $reservation;
    }

    /**
     * @param ReservationEntity $reservation Base resservation
     * @param ArrayHash $values consisting of form values w/ repeat, repeatDateTo, repeatBy, repeatByValue
     * @return array|false
     */
    private function repeatReservation($reservation, $values)
    {
        if (!$values->repeat || !$values->repeatDateTo || !$values->repeatBy || !$values->repeatByValue) {
            return false;
        }
        
        $reservationsArr = [];

        $dateFrom = $reservation->dateFrom;
        $dateTo = $reservation->dateTo;
        $repeatDateTo = \DateTime::createFromFormat('j. n. Y', $values->repeatDateTo)
            ->setTime($dateTo->format('H'), $dateTo->format('i'));

        $modifyStr = '+' . $values->repeatByValue . ' ' . $values->repeatBy;  // interval in days
        
        while (true) {
            $dateFrom->modify($modifyStr);
            $dateTo->modify($modifyStr);
            
            if ($dateTo > $repeatDateTo) {
                break;
            }

            if (false /* ($values->reservationItem, $values->date, $values->timeFrom, $values->timeTo not reservable date */) {
                continue;
            }

            $newReservation = clone $reservation;
            $newReservation->setId(null);
            //$newReservation->setReservationItem($reservation->reservationItem);
            //$newReservation->setCustomer($reservation->customer);
            $newReservation->setDateFrom(clone $dateFrom);
            $newReservation->setDateTo(clone $dateTo);

            $this->em->persist($newReservation);
            $reservationsArr[] = $newReservation;
        }
        $this->em->flush();

        return $reservationsArr;
    }

    public function cancelReservation($reservation, $userId, $adminMode)
    {
        if (is_numeric($reservation)) {
            $reservation = $this->em->getReservationRepository()->find($reservation);
            if (!$reservation) {
                return false;
            }
        }

        /*$user = $this->em->getCustomerRepository()->find($userId);
        if (!$user) {
            return false;
        }*/

        if (!$adminMode && (!$reservation->customer || $userId != $reservation->customer->id)) {
            return false;
        }
        
        try {
            $reservation->setCanceled(true);
            $this->em->flush();
            //$this->em->remove($reservation);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}