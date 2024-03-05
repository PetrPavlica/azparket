<?php

namespace App\Model\Utils;

use Tracy\Debugger;

class GoogleMaps
{
    private $geocodeUrl = 'https://maps.googleapis.com/maps/api/geocode/json';
    private $directionUrl = 'https://maps.googleapis.com/maps/api/directions/json';
    private $distanceUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';
    public $key = 'AIzaSyDVeSgGFIxY4H8MFkHZFOnIenIRH8ZovZ4';
    private $geocodeParams = [
        'street',
        'city',
        'zip'
    ];
    private $language = 'cs';
    private $units = 'metric';

    private function sendRequest($url, $params)
    {
        $url .= '?'.http_build_query($params);
        $r = curl_init();
        curl_setopt($r, CURLOPT_URL, $url);
        curl_setopt($r, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($r, CURLOPT_HEADER, false);
        curl_setopt($r, CURLINFO_HEADER_OUT, true);
        curl_setopt($r, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($r, CURLOPT_TIMEOUT, 3);
        $response = curl_exec($r);
        $info = curl_getinfo($r);
        $status = $info['http_code'];
        curl_close($r);

        switch($status) {
            case 200:
                break;

            default:
                throw new \Exception('Request failed. Status code: '.$status);
                break;
        }

        if (!isset($info['request_header'])) {
            throw new \Exception('Request failed');
        }

        return $response ? $response : '';
    }

    public function geocode($data)
    {
        $arr = [];
        try {
            $params = [];
            $address = '';
            foreach($data as $k => $d) {
                if (in_array($k, $this->geocodeParams) && $data[$k]) {
                    $address .= $data[$k].', ';
                }
            }
            $params['address'] = $address.'Czech Republic';
            $params['language'] = $this->language;

            if (!empty($this->key)) {
                $params['key'] = $this->key;
            }
            $arr = json_decode($this->sendRequest($this->geocodeUrl, $params), true);
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return $arr;
    }

    /**
     * Returns array containing lat and long of passed address
     *
     * @param [type] $data array indexed like $this->geocode
     * @return array|false False or array containing lat and long indexes
     */
    public function geocodeToLatLangArr($data)
    {
        $res = $this->geocode($data);
        if (isset($res['results'][0])) {
            return ['lat' => $res['results'][0]['geometry']['location']['lat'], 'lng' => $res['results'][0]['geometry']['location']['lng']];
        }
        return false;
    }

    public function directions($params)
    {
        $arr = [];
        try {
            $params['language'] = $this->language;

            if (!empty($this->key)) {
                $params['key'] = $this->key;
            }
            $arr = json_decode($this->sendRequest($this->directionUrl, $params), true);
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return $arr;
    }

    /**
     * @param array $params containing origins and destinations indexes which refers to arrays of coordinations
     * @return array containing results
     */
    public function distance($params)
    {
        //$params
        $arr = [];
        try {
            
            $origins = '';
            foreach($params['origins'] as $k => $d) {
                $origins .= $d.', ';
            }
            $params['origins'] = substr($origins, 0, -2);

            $destinations = '';
            foreach($params['destinations'] as $k => $d) {
                $destinations .= $d.', ';
            }
            $params['destinations'] = substr($destinations, 0, -2);

            $params['language'] = $this->language;
            $params['units'] = $this->units;

            if (!empty($this->key)) {
                $params['key'] = $this->key;
            }
            $arr = json_decode($this->sendRequest($this->distanceUrl, $params), true);
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return $arr;
    }

    /**
     * Returns distance in meters 
     *
     * @param [type] $data array indexed like $this->distance()
     * @return array|false False or array containing indexes distance in meters and duration in seconds
     */
    public function distanceValueAndTime($data)
    {
        $res = $this->distance($data);
        if ($res && isset($res['rows'][0]['elements'][0]) && $res['rows'][0]['elements'][0]['status'] == 'OK') {
            return ['distance' => $res['rows'][0]['elements'][0]['distance']['value'], 'duration' => $res['rows'][0]['elements'][0]['duration']['value']];
        }
        return false;
    }
}