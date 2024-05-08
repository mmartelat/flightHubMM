<?php
require_once ("utils.php");

class Flight
{

    /***
     * 
     * This function get all infos of fligts in the file infoFligt.json
     * 
     * @return array with three keys : airlines, airports, flights
     * @return NULL if no data in the file or an issu occure in decoding
     * 
     */
    private function getInfosFlights(){
        $contenu_json = file_get_contents('./infoFlight.json');
        $data_flight = json_decode($contenu_json, true);
        if ($data_flight === null) {
            return NULL;
        } else {
            return $data_flight;
        }
    }

    /***
     * 
     * This function get an airport code value, with the name off the city the airport is in
     * 
     * @param array $airports ; array of the airport and its infos which come frome the data infoFligt.json
     * @param String $city ; name of the airport's city 
     * @return String of the airport's code
     * @return NULL if the city is not found in the airports data
     * 
     */
    private function getCodeByCity($airports, $city) {
        foreach ($airports as $airport) {
            if (strtolower($airport["city"]) === strtolower($city)) {
                return $airport["code"];
            }
        }
        return null;
    }

    /***
     * 
     * This function get an airport timezone, with airport's code
     * 
     * @param array $airports ; array of the airport and its infos which come frome the data infoFligt.json
     * @param String $code_airport ; airport's code 
     * @return String of the airport's timezone
     * @return NULL if the airport is not found in the airports data
     * 
     */
    private function getTimeZoneByCodeAirport($airports, $code_airport) {
        foreach ($airports as $airport) {
            if (strtolower($airport["code"]) === strtolower($code_airport)) {
                return $airport["timezone"];
            }
        }
        return null;
    }

    /***
     * 
     * This function get the utc airport, with airport's timezone
     * 
     * @param String $timezoneName ; airport's timezone  
     * @return String of the airport's utc in hour
     * 
     */
    private function getUTCByTimezoneName($timezoneName){
        $timezone = new DateTimeZone($timezoneName);
        $offset = $timezone->getOffset(new DateTime("now"));
        $hours = floor(abs($offset) / 3600);
        $sign = ($offset < 0) ? "-" : "+";
        return "$sign$hours";
    }

    /***
     * 
     * This function get the arrival date of a flight in the right timezone of the arrival airport
     * 
     * @param array $flight ; array of the flight's infos which come frome the data infoFligt.json
     * @param String $departure_date ; departure date of the flight
     * @return String of the arrival date 
     * @return NULL if if no info of the flights was found or their is no timezone indicate for the airport of arrival and/or departure
     * 
     */
    private function getArrivalDate($flight, $departure_date){
        $allFlights = $this->getInfosFlights();
        if (!empty($allFlights)) {
            $timeZoneDeparture = $this->getTimeZoneByCodeAirport($allFlights['airports'], $flight['departure_airport']);
            if (!empty($timeZoneDeparture)){
                $timeZoneArrival = $this->getTimeZoneByCodeAirport($allFlights['airports'], $flight['arrival_airport']);
                if (!empty($timeZoneDeparture)){
                    //get fly time in hours and minutes for the flight
                    list($hours_fly, $min_fly) = explode(":", $flight['flight_time']);
                    $utcDeparture = $this->getUTCByTimezoneName($timeZoneDeparture);
                    $utcArrival = $this->getUTCByTimezoneName($timeZoneArrival);
                    $time_difference = $utcArrival - ($utcDeparture);
                    $dateHeure = new DateTime($departure_date.' '.$flight['departure_time']);
                    //add timezone difference to the departure date
                    $dateHeure->modify($time_difference.' hours');
                    //add the time of fly
                    $dateHeure->modify('+'.$hours_fly.' hours +'.$min_fly.' minutes');
                    //return the date only without the time
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

     /***
     * 
     * This function get all the trips flight for a departure location or arrival location
     * 
     * @param array $flights ; array of the flight's infos which come frome the data infoFligt.json
     * @param String $code ; airport's code (departure airport or arrival airport)
     * @param String $direction ; indicate if we want to find the flight with the airport'code departure or arrival 
     * @param String $date ; indicate the flight date departure if needed (actual date if no date pass to the function)
     * @return array of the trip flight that correspond to the code and direction. For each trip flight we got all the info that there is in the infoFligt.json 
     * and we had the departure date and arrival date
     * 
     */
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

     /***
     * 
     * This function get all the trips flight for a departure location or arrival location, a departure date and for a city
     * 
     * @param String $city ; airport's city (departure airport or arrival airport)
     * @param String $direction ; indicate if we want to find the flight with the airport's code departure or arrival 
     * @param String $departure_date ; indicate the flight date departure.
     * @return array of the trip flight that correspond to the city and direction 
     * @return NULL if if no info of the flights was found or no airport's code for the ciy was found
     * 
     */
    private function getAllTripCity($city, $departure_date, $direction)
    {
        //get info flights
        $allFlights = $this->getInfosFlights();
        if (!empty($allFlights)) {
            //get airport's code
            $code = $this->getCodeByCity($allFlights['airports'], $city);
            if (!empty($code)){
                //get all trip for the airport
                $flightsTrip = $this->getFlightByAirport($allFlights['flights'], $code, $direction, $departure_date);
            } else {
                return NULL;
            }
            return $flightsTrip;
        } else {
            return NULL;
        }
    }

    /***
     * 
     * This function check if the date off departure for a go trip and the return trip (of the sam round-trip) is not the same
     * 
     * @param Array $go_trip ; array of the flight's infos (which come frome the data infoFligt.json) for the go trip
     * @param Array $return_trip ;  array of the flight's infos (which come frome the data infoFligt.json) for the return trip
     * @return TRUE if the arrival of the go trip is before the departure of the return trip, or same day but same hours
     * @return FALSE if same day or if the arrival of the go trip is after the departure of the return trip
     * 
     */
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

    /***
     * 
     * This function get all the flight's trip for a city location (departur city) and a departure date
     * 
     * @param String $city ; airport's city (departure airport)
     * @param String $departure_date ;  Departure date for the flights
     * @return array of the trip flight that correspond to the city  
     * @return NULL if if no info of the flights was found or no airport's code for the ciy was found
     * 
     */
    public function getAllTripCityDeparture($city, $departure_date)
    {
        return $this->getAllTripCity($city, $departure_date, 'departure');
    }

    /***
     * 
     * This function get all the trip flight for a city location (departure city), a departure date and an arrival city
     * 
     * @param String $city ; airport's city (departure airport)
     * @param String $departure_date ;  Departure date for the flights
     * @param String $arrival_location ; airport's city (arrival airport)
     * @return array with two keys ; error = boolean to indicate thst an error occure, 
     * res = if no error array of the trip flight that correspond to the departur city  and arrival / array of the error
     * 
     */
    public function getAllTripCityDepartureArrival($city, $departure_date, $arrival_location){
        //get all the trip fligt for the departure ask
        $allFlightsDeparture = $this->getAllTripCity($city, $departure_date, 'departure');
        if(empty($allFlightsDeparture)){
            $jcode = json_encode(create_reponse_json(404, NULL, $msg='Not Found - This city ('.$city.') is not served'));
            return array("res" => $jcode, "error" => TRUE);
        }
        //get all the info flight
        $allFlights = $this->getInfosFlights();
        if (!empty($allFlights)) {
            //get airport's code for arrival location
            $code = $this->getCodeByCity($allFlights['airports'], $arrival_location);
            if (!empty($code)){
                //get all trip flight for the arrival location and within the trip fligt found for the departure location
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


    /***
     * 
     * This function get all the round-trip flights for a city location (departure city), a departure date, an arrival city and a return date
     * 
     * @param String $city ; airport's city (departure airport)
     * @param String $departure_date ;  Departure date for the flights
     * @param String $arrival_location ; airport's city (arrival airport)
     * @param String $return_date ; Return date for the return trip
     * @return array with two keys ; error = boolean to indicate thst an error occure, 
     * res = if no error array of the roud-trip flight that correspond to the departure city and arrival, a round trip is composed of a price a go_trip and a return_trip
     * / array of the error
     * 
     */
    public function getAllTripCityDepartureArrivalRoundtrip($city, $departure_date, $arrival_location, $return_date){
        //get all dtrip flight for the departure and arrival informed for the go trip
        $resDepartureTrip = $this->getAllTripCityDepartureArrival($city, $departure_date,$arrival_location);
        if($resDepartureTrip['error']){
            return $resDepartureTrip;
        }
        //get all dtrip flight for the departure and arrival informed for the return trip
        $resReturnTrip = $this->getAllTripCityDepartureArrival($arrival_location, $return_date, $city);
        if($resReturnTrip['error']){
            return $resReturnTrip;
        }
        $resRoundTrip = array();
        //loop on go trip and return trip and check is the date and hours fit 
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