<?php
require_once("Flights.php");
require_once ("utils.php");
		
class FlightRestHandler {
    /***
     * 
     * Return all the flights that leave from the city requested at the date informed. 
     * If no date are informed it takes the actual date as default.
     * One-way flight
     * 
     * @param String $depature_location Location departure of the flight requested.
     * @param String $departure_date Date of the departure for the flight requested (Format YYYY-mm-dd).
     * @return Json
     * 
     */
    function getFlightsByCityDeparture($depature_location, $departure_date) {	
        //Check format date and if not to far in the future
		$res = checkInputDate($departure_date);
        if(!empty($res)) {
            echo $res;
            exit;
        }
        $flight = new Flight();
        //Get all the flight that leave form the departure location (No matter the arrival location)
        $rawData = $flight->getAllTripCityDeparture($depature_location,$departure_date);
        if(!empty($rawData) ){
            $count = count($rawData);
            $rawData['count_flights'] = $count;
            $res = json_encode(create_reponse_json(200, $rawData, $msg='flight from - '.$_GET['departure_location']));
        }else {
            $res = json_encode(create_reponse_json(404, $rawData, $msg='Not Found - This city is not served'));
        }
        echo $res;
	}

    /***
     * 
     * Return all the flights that leave from the city requested at the date informed and to the arrival location requested.
     * If no date are informed it takes the actual date as default.
     * One-way flight
     * 
     * @param String $depature_location Departure location of the flight requested.
     * @param String $arrival_location Arrival location of the flight requested.
     * @param String $departure_date Date of the departure for the flight requested (Format YYYY-mm-dd).
     * @return Json
     * 
     */
    function getFlightsByCityDepartureArrival($depature_location, $arrival_location, $departure_date) {	
        //Check format date and if not to far in the future
		$res = checkInputDate($departure_date);
        if(!empty($res)) {
            echo $res;
            exit;
        }
        $flight = new Flight();
        //Get all the flight that leave form the departure location and go to the arrival location
        $rawData = $flight->getAllTripCityDepartureArrival($depature_location,$departure_date, $arrival_location);
        if(!$rawData['error']){
            $count = count($rawData['res']);
            $rawData['res']['count_flights'] = $count;
            $res = json_encode(create_reponse_json(200, $rawData['res'], $msg='flight from - '.$_GET['departure_location']));
        }else {
            $res = $rawData['res'];
        }

        echo $res;
	}

    /***
     * 
     * Return list of pairs of one-ways flights : one that leave from the city requested (depature_location) at the date informed (departure_date) and to the arrival location requested (arrival_location).
     * And the return flight from the city requested (arrival_location) at the date informed (return_date) and to the arrival location requested (depature_location)
     * If no date are informed it takes the actual date as default.
     * round-trip flight
     * 
     * @param String $depature_location Departure location of the flight requested.
     * @param String $arrival_location Arrival location of the flight requested.
     * @param String $departure_date Date of departure for the flight requested (Format YYYY-mm-dd).
     * @param String $return_date Date of departure for the return flight requested (Format YYYY-mm-dd).
     * @return Json
     * 
     */
    function getFlightsByCityDepartureArrivalRoundTrip($depature_location, $arrival_location, $departure_date, $return_date) {	
        //Check format date and if not to far in the future
		$res = checkInputDate($departure_date);
        if(!empty($res)) {
            echo $res;
            exit;
        }
        //Check format date and if not to far in the future
        $res = checkInputDate($return_date);
        if(!empty($res)) {
            echo $res;
            exit;
        }
        //Check if date depature and returne are not on the same day
        if(isInputDateEqual($departure_date, $return_date)){
            echo json_encode(create_reponse_json(403, $NULL, $msg='Forbidden - You can not enter same date for departure and return'));
            exit;
        }
        $flight = new Flight();
        //Get all the pairs of flight that leave form the departure location at the departure date, go to the arrival location and come back a the return date
        $rawData = $flight->getAllTripCityDepartureArrivalRoundtrip($depature_location,$departure_date, $arrival_location, $return_date);
        if(!$rawData['error']){
            $count = count($rawData['res']);
            $rawData['res']['count_round_trip'] = $count;
            $res = json_encode(create_reponse_json(200, $rawData['res'], $msg='flight grom - '.$_GET['departure_location']));
        }else {
            $res = $rawData['res'];
        }

        echo $res;
	}
}

?>