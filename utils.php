<?php
function create_reponse_json($code, $data, $msg=NULL) {
	$array['code'] = $code;
	$array['msg'] = $msg;
	$array['result'] = $data;

	return($array);
}

function checkDateFormat($dateString){
    $format = "Y-m-d";

    $dateTimeObject = DateTime::createFromFormat($format, $dateString);

    if ($dateTimeObject !== false) {
        return TRUE;
    } else {
        return FALSE;
    }
}

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

function checkInputDate($date){
    $res=NULL;
    if(!empty($date) && !checkDateFormat($date)){
        $res = json_encode(create_reponse_json(401, NULL, $msg='Unauthorized - departure_date and arrival_date must have this format : YYYY-mm-dd'));
    } elseif(!empty($date) && !checkDateTime($date)) {
        $res = json_encode(create_reponse_json(401, NULL, $msg='Unauthorized - departure_date and arrival_date must be less than a year from now'));
    }
    return $res;
}

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