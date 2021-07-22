<?php
require_once dirname(__FILE__) . '/autoload.php';
use MaxMind\Db\Reader;
require_once dirname(__FILE__) . "/../remote/remote.php";

class GeoIPInfo{
    /** city
     * Array
     * (
     * [isoCode] => CN
     * [country_name] => China
     * [cn_country_name] => 中国
     * [zones_name] => Beijing
     * [zones_ios] => BJ
     * [city_name] => Beijing
     * [city_code] => 
     * [latitude] => 39.9289
     * [longitude] => 116.3883
     * ) 
     * 
    */
    public $reader;
    public $record;
    public $ip;
    public $latitude;
    public $longitude;
    public $timeZone;
    public $cityName;
    public $provinceName;
    public $countryName;
    function __construct($ip){
        //if(!$ip && !strlen($ip)) exit();
        $path = dirname(__FILE__) . '/GeoLite2-City.mmdb';
        $reader = new Reader($path);
        $record = $reader->get($ip);
        if(null == $record) {
            echo "NO RECORD for $ip <br>"; 
            exit;
        }

        $this->ip     = $ip;
        $this->reader = $reader;
        $this->record = $record;
        /**
         * city               - names
         * continent          - names
         * country            - names
         * subdivisions       - names
         * location
         * registered_country - names
         */
         $this->latitude     = $this->record['location']['latitude'];
         $this->longitude    = $this->record['location']['longitude'];
         $this->timeZone     = $this->record['location']['time_zone'];

         $this->cityName     = $this->record['city']['names']['zh-CN'];
         $this->countryName  = $this->record['country']['names']['zh-CN'];
         $this->provinceName = $this->record['subdivisions'][0]['names']['zh-CN'];
        /*
        $reader = new Reader('/path/to/GeoLite2-City.mmdb');  
        $record = $reader->city($_SERVER['REMOTE_ADDR']);
        $this->city = Array();

        $this->city['isoCode'] = $record->country->isoCode;
        $this->city['country_name'] = $record->country->name;
        $this->city['cn_country_name'] = $record->country->names['zh-CN'];
        $this->city['zones_name'] = $record->mostSpecificSubdivision->name;
        $this->city['zones_ios'] = $record->mostSpecificSubdivision->isoCode;
        $this->city['city_name'] = $record->city->name;
        $this->city['city_code'] = $record->postal->code;
        $this->city['latitude'] = $record->location->latitude;
        $this->city['longitude'] = $record->location->longitude;
        */

        //$this->countryName = $record->country->names['zh-CN'];
        //$this->cityName    = $record->city->name;
    }
};

class CurrentGeoIPInfo extends GeoIPInfo {
    function __construct(){
        $currentIP = remoteIP();
        parent::__construct($currentIP);
    }
};
?>
