<?php

namespace App\Components\Reservation;

interface IReservationControlFactory {

    /** @return ReservationControl */
    function create($cParams = []): ReservationControl;
}
