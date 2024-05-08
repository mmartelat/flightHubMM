<?php
require_once ("utils.php");

class Flight
{

    private $airline = NULL;
    private $number = NULL;
    private $departure_airport = NULL;
    private $arrival_airport = NULL;
    private $departure_time = NULL;
    private $arrival_time = NULL;
    private $price = NULL;
    


    private function getInfosFlights(){
        $contenu_json = file_get_contents('./infoFlight.json');
        $data_flight = json_decode($contenu_json, true);
        if ($data_flight === null) {
            return NULL;
        } else {
            return $data_flight;
        }
    }

    private function getCodeByCity($airports, $city) {
        foreach ($airports as $airport) {
            if (strtolower($airport["city"]) === strtolower($city)) {
                return $airport["code"];
            }
        }
        return null;
    }

    private function getTimeZoneByCodeAirport($airports, $code_airport) {
        foreach ($airports as $airport) {
            if (strtolower($airport["code"]) === strtolower($code_airport)) {
                return $airport["timezone"];
            }
        }
        return null;
    }

    private function getUTCByTimezoneName($timezoneName){
        $timezone = new DateTimeZone($timezoneName);
        $offset = $timezone->getOffset(new DateTime("now"));
        $hours = floor(abs($offset) / 3600);
        $sign = ($offset < 0) ? "-" : "+";
        return "$sign$hours";
    }

    private function getArrivalDate($flight, $departure_date){
        $allFlights = $this->getInfosFlights();
        if (!empty($allFlights)) {
            $timeZoneDeparture = $this->getTimeZoneByCodeAirport($allFlights['airports'], $flight['departure_airport']);
            if (!empty($timeZoneDeparture)){
                $timeZoneArrival = $this->getTimeZoneByCodeAirport($allFlights['airports'], $flight['arrival_airport']);
                if (!empty($timeZoneDeparture)){
                    list($hours_fly, $min_fly) = explode(":", $flight['flight_time']);
                    $utcDeparture = $this->getUTCByTimezoneName($timeZoneDeparture);
                    $utcArrival = $this->getUTCByTimezoneName($timeZoneArrival);
                    $time_difference = $utcArrival - ($utcDeparture);
                    $dateHeure = new DateTime($departure_date.' '.$flight['departure_time']);
                    $dateHeure->modify($time_difference.' hours');
                    $dateHeure->modify('+'.$hours_fly.' hours +'.$min_fly.' minutes');
                    return $dateHeure->format("Y-m-d");
                } else {
                    return NULL;
                }
            } else {
                return NULL;
            }
        }else {
            return NULL;
        }
    }

    private function getFlightByAirport($flights, $code, $direction, $date=NULL){
        $flights_list = array();
        $key = $direction."_airport";
        foreach ($flights as $flight) {
            if ($flight[$key] == $code) {
                if(empty($date)){
                    $flight["departure_date"] = date("Y-m-d");
                    $flight['arrival_date'] = $this->getArrivalDate($flight, date("Y-m-d"));
                    array_push($flights_list, $flight);
                }else{
                    $flight["departure_date"] = $date;
                    $flight['arrival_date'] = $this->getArrivalDate($flight, $date);
                    array_push($flights_list, $flight);
                }
            }
        }
        return $flights_list;

    }

    private function getAllTripCity($city, $departure_date, $direction)
    {
        $allFlights = $this->getInfosFlights();
        if (!empty($allFlights)) {
            $code = $this->getCodeByCity($allFlights['airports'], $city);
            if (!empty($code)){
                $flightsTrip = $this->getFlightByAirport($allFlights['flights'], $code, $direction, $departure_date);
            } else {
                return NULL;
            }
            return $flightsTrip;
        } else {
            return NULL;
        }
    }

    private function isRoundTripDateOk($go_trip, $return_trip){
        $go_date_time = new DateTime($go_trip['arrival_date']);
        $return_date_time = new DateTime($return_trip['departure_date']);
        if($go_date_time<$return_date_time){
            return TRUE;
        }elseif($go_date_time==$return_date_time){
            if($go_trip['arrival_time'] < $return_trip['departure_time']){
                return TRUE;
            }else{
                return FALSE;
            }
        }else{
            return FALSE;
        }
    }

    public function getAllTripCityDeparture($city, $departure_date)
    {
        return $this->getAllTripCity($city, $departure_date, 'departure');
    }

    public function getAllTripCityDepartureArrival($city, $departure_date, $arrival_location){
        $allFlightsDeparture = $this->getAllTripCity($city, $departure_date, 'departure');
        if(empty($allFlightsDeparture)){
            $jcode = json_encode(create_reponse_json(404, NULL, $msg='Not Found - This city ('.$city.') is not served'));
            return array("res" => $jcode, "error" => TRUE);
        }
        $allFlights = $this->getInfosFlights();
        if (!empty($allFlights)) {
            $code = $this->getCodeByCity($allFlights['airports'], $arrival_location);
            if (!empty($code)){
                $flightsTrip = $this->getFlightByAirport($allFlightsDeparture, $code, 'arrival',$departure_date);
                if(!empty($flightsTrip)){
                    return array("res" => $flightsTrip, "error" => FALSE);
                }else{
                    $jcode = json_encode(create_reponse_json(404, NULL , $msg='Not Found - No trip from '.$city.' to '.$arrival_location));
                    return array("res" => $jcode, "error" => TRUE);
                }
            } else {
                $jcode = json_encode(create_reponse_json(404, NULL, $msg='Not Found - This city ('.$arrival_location.') is not served'));
                return array("res" => $jcode, "error" => TRUE);
            }
        }else {
            $jcode = json_encode(create_reponse_json(404, NULL, $msg='Not Found - No city served'));
            return array("res" => $jcode, "error" => TRUE);
        }
    }


    public function getAllTripCityDepartureArrivalRoundtrip($city, $departure_date, $arrival_location, $return_date){
        $resDepartureTrip = $this->getAllTripCityDepartureArrival($city, $departure_date,$arrival_location);
        if($resDepartureTrip['error']){
            return $resDepartureTrip;
        }
        $resReturnTrip = $this->getAllTripCityDepartureArrival($arrival_location, $return_date, $city);
        if($resReturnTrip['error']){
            return $resReturnTrip;
        }
        $resRoundTrip = array();
        foreach($resDepartureTrip['res'] as $go_trip){
            foreach($resReturnTrip['res'] as $return_trip){
                if($this->isRoundTripDateOk($go_trip, $return_trip)){
                    $price = $go_trip['price'] + $return_trip['price'];
                    $roundTrip = array("price" => $price, "go_trip" => $go_trip, "return_trip" => $return_trip);
                    array_push($resRoundTrip, $roundTrip);
                }
            }
        }
        return array("res" => $resRoundTrip, "error" => FALSE);
    }
}

?>