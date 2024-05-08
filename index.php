<?php

header('Content-Type: application/json');

require_once ("FlightRestHandler.php");
require_once ("utils.php");

$flights_rest_handler = new FlightRestHandler();
if(!empty($_GET['departure_location']) && empty($_GET['arrival_location']) && empty($_GET['two_way'])){// Endpoint - city departure
  $flights_rest_handler->getFlightsByCityDeparture($_GET['departure_location'], $_GET['departure_date']);
}elseif(!empty($_GET['departure_location']) && !empty($_GET['arrival_location']) && empty($_GET['round_trip'])) { // Endpoint - city departure and arrival one-way
  $flights_rest_handler->getFlightsByCityDepartureArrival($_GET['departure_location'], $_GET['arrival_location'], $_GET['departure_date']);
}elseif(!empty($_GET['departure_location']) && !empty($_GET['arrival_location']) && !empty($_GET['round_trip'])) { // Endpoint - city departure and arrival round-trip
  if(empty($_GET['departure_date']) || empty($_GET['return_date'])){// Must informed departure date and return date in this endpoint
    echo json_encode(create_reponse_json(401, NULL, $msg='Unauthorized - departure_date and return_date must be informed'));
  }else{
    $flights_rest_handler->getFlightsByCityDepartureArrivalRoundTrip($_GET['departure_location'],  $_GET['arrival_location'], $_GET['departure_date'], $_GET['return_date']);
  }
}elseif (!($_GET['departure_location'])) { // Must have at least departure city
  echo json_encode(create_reponse_json(401, NULL, $msg='Unauthorized - departure_location must be informed'));
}
?>