<?php

/***
 * 
 * This function an array to encode in json
 * 
 * @param String $code status du retour
 * @param String $msg error or succes message
 * @param Array $array 
 * @return array with three keys : code, msg, results
 * 
 */
function create_reponse_json($code, $res, $msg=NULL) {
	$array['code'] = $code;
	$array['msg'] = $msg;
	$array['result'] = $res;

	return($array);
}


/***
 * 
 * This function check if the date as the right format Y-m-d
 * 
 * @param String $dateString date to check
 * @param Boolean 
 * 
 */
function checkDateFormat($dateString){
    $format = "Y-m-d";

    $dateTimeObject = DateTime::createFromFormat($format, $dateString);

    if ($dateTimeObject !== false) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/***
 * 
 * This function check if the date is less than a year from now
 * 
 * @param String $dateString date to check
 * @param Boolean 
 * 
 */
function checkDateTime($dateString){
    $inputDateTime = new DateTime($dateString);

    $today = date("Y-m-d");
    $todayTime = new DateTime($today);
    $todayTime->modify("+365 day");

    if($todayTime>=$inputDateTime ){
        return TRUE;
    }else{ 
        return FALSE;
    }
}

/***
 * 
 * This function check format and time of a date
 * 
 * @param String $dateString date to check
 * @param Boolean 
 * 
 */
function checkInputDate($date){
    $res=NULL;
    if(!empty($date) && !checkDateFormat($date)){
        $res = json_encode(create_reponse_json(401, NULL, $msg='Unauthorized - departure_date and arrival_date must have this format : YYYY-mm-dd'));
    } elseif(!empty($date) && !checkDateTime($date)) {
        $res = json_encode(create_reponse_json(401, NULL, $msg='Unauthorized - departure_date and arrival_date must be less than a year from now'));
    }
    return $res;
}

/***
 * 
 * This function check if two date are the same
 * 
 * @param String $dateStringDeparture date departure to check
 * @param String $dateStringArrivl date arrival to check
 * @param Boolean 
 * 
 */
function isInputDateEqual($dateStringDeparture, $dateStringArrivl){
    $inputDateTimeDeparture = new DateTime($dateStringDeparture);
    $inputDateTimeArrival = new DateTime($dateStringArrivl);

    if($inputDateTimeDeparture==$inputDateTimeArrival ){
        return TRUE;
    }else{ 
        return FALSE;
    }
}
?>