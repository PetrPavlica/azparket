<?php

namespace App\Model\Utils;

use Tracy\Debugger;

class TimeHelper
{

    /**
     * '10:30' => 630
     *
     * @param string $timeStr
     * @return integer
     */
    public static function timeStrToMinutes($timeStr)
    {
        $parts = explode(':', $timeStr);
        if (count($parts) != 2) {
            return 0;
        }
        return intval($parts[0]) * 60 + intval($parts[1]);  
    }

    /**
     * 630 => '10:30'
     *
     * @param integer $minutes
     * @return string
     */
    public static function minutesToTimeStr($minutes)
    {
        $minutesPart = $minutes % 60;
        return floor($minutes / 60) . ':' . (strlen($minutesPart) < 2 ? '0' . $minutesPart : $minutesPart); 
    }

    /**
     * 630 => [10, 30]
     *
     * @param integer $minutes
     * @return array
     */
    public static function minutesToTimeArr($minutes)
    {
        return [floor($minutes / 60), $minutes % 60];
    }

    /**
     * '10:30'' => [10, 30]
     *
     * @param string $timeStr
     * @return array
     */
    public static function timeStrToTimeArr($timeStr)
    {
        $parts = explode(':', $timeStr);
        return [intval($parts[0]), intval($parts[1])];
    }

    /**
     * Checks time string validity
     * 
     * @param string $timeStr string of hours and minutes divided by ':'
     * @param int $period check if fully divideable
     * @return bool
     */
    public static function checkTimeStrValid($timeStr, $period = 0)
    {
        $parts = explode(':', $timeStr);
        if (count($parts) != 2 || !is_numeric($parts[0]) | !is_numeric($parts[1])) {
            return false;
        }
        $minutes = intval($parts[0]) * 60 + intval($parts[1]);  

        if ($period && $minutes % $period != 0) {
            return false;
        }
        return true;
    }
}