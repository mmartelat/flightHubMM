# flightHubMM
PHP coding asignement - Trip builder

Web Services to build and navigate trips for a single passenger using criteria such as
departure locations, departure dates and arrival locations.

### Prerequisites
Must have php installed and Apache (or a preferred web server) installed.

### Installation
git clone the repo and place it under the DocumentRoot directory of the web server.

### How to use

Web service adress :
http://{hostname}/flightHubMM/

Endpoint :

- 1 : /flightHubMM/?departure_location={city}&departure_date={YYYY-mm-dd}
- 2 : /flightHubMM/?departure_location={city}&arrival_location={city}&departure_date={YYYY-mm-dd}
- 3 : /flightHubMM/?departure_location={city}&arrival_location={city}&departure_date={YYYY-mm-dd}&return_date={YYYY-mm-dd}&round_trip=true

The endpoint 1 allow to get one-way trip flight knowing the city departure but not the arrival (Gives you ideas for a good week-end trip).

The endpoint 2 allow to get one-way trip flight knowing the city departure and your destination.

The endpoint 3 allow to get round-trip flight knowing the city departure and your destination.


departure_date is not required for endpoint 1 and 2. If not informed we take today's date.

City available Montreal, Vancouver, Toronto.

Every information of available flights are in infoFlight.json.


exemple :
http://{hostname}/flightHubMM/?departure_location=Montreal&arrival_location=Vancouver&departure_date=2024-05-11&return_date=2024-05-14&round_trip=true
