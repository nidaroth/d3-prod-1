<?php

require_once("../global/config.php");
require_once("check_access.php");
use Illuminate\Database\Capsule\Manager as CapsuleDB;
ENABLE_DEBUGGING(FALSE);
if (check_access('MANAGEMENT_REGISTRAR') == 0) {
    header("location:../index");
    exit;
}
header('Content-Type: application/json; charset=utf-8');
if ($_REQUEST['act'] == 'add_round_time') {
    // dump('INCOMMING_REQUEST_VARS' , $_REQUEST);

    #Get Request Data

    $PK_TIME_CLOCK_PROCESSOR = $_REQUEST['PK_TIME_CLOCK_PROCESSOR'];
    $PK_TIME_CLOCK_PROCESSOR_DETAIL_IDS = $_REQUEST['PK_TIME_CLOCK_PROCESSOR_DETAIL_IDS'];

    $att_date = date('Y-m-d', strtotime($_REQUEST['att_date']));

    $CHECK_IN_TIME_24_FROM = convertTo24HourFormat($_REQUEST['clock_in_from_date']);
    $CHECK_IN_TIME_24_END = convertTo24HourFormat($_REQUEST['clock_in_to_date']);
    if(compareTimes($CHECK_IN_TIME_24_END , $CHECK_IN_TIME_24_FROM) === -1){
        give_error("Invalid Time Range Given ! Clock-In 'FROM' time is greater than 'TO' time !");
    }
    $CHECK_IN_TIME_24_ROUND_OF_TO = convertTo24HourFormat($_REQUEST['round_to_clock_in_date']);

    $CHECK_OUT_TIME_24_FROM = convertTo24HourFormat($_REQUEST['clock_out_from_date']);
    $CHECK_OUT_TIME_24_END = convertTo24HourFormat($_REQUEST['clock_out_to_date']);
    if(compareTimes($CHECK_OUT_TIME_24_END , $CHECK_OUT_TIME_24_FROM) === -1){
        give_error("Invalid Time Range Given ! Clock-Out 'FROM' time is greater than 'TO' time !");
    }
    $CHECK_OUT_TIME_24_ROUND_OF_TO = convertTo24HourFormat($_REQUEST['round_to_clock_out_date']);

    $in_out_diff = GetTimeDifferance($CHECK_IN_TIME_24_ROUND_OF_TO , $CHECK_OUT_TIME_24_ROUND_OF_TO);
    $hour_part = $in_out_diff[0];
    $min_part = ($in_out_diff[1] / 60) * 100;
    $Hours_If_to_be_updated = $hour_part + $min_part;
    $POSTED = $_REQUEST['POSTED'];
    if ($POSTED == 0) {
        $cond = " AND POSTED = 0 ";
    }
   
    #IN : GET ALL & UPDATE IN TIMES & DATA IN DB Accordingly

   $IN_RESULTS = GetEligibleResults_IN_OR_OUT('IN',$PK_TIME_CLOCK_PROCESSOR,$PK_TIME_CLOCK_PROCESSOR_DETAIL_IDS,$att_date,$CHECK_IN_TIME_24_FROM,$CHECK_IN_TIME_24_END);
    //dd($IN_RESULTS);
   foreach($IN_RESULTS as $in_result){
    $REPLACE_BETWEEN_FOR_IN = ['START_TIME' => $CHECK_IN_TIME_24_FROM , 'TILL_TIME' => $CHECK_IN_TIME_24_END];
    $ORIGINAL_TIMES_IN = ['ORIGINAL_IN_TIME'=> $in_result->CHECK_IN_TIME, 'ORIGINAL_OUT_TIME'=>$in_result->CHECK_OUT_TIME];
    UpdateEligibleResults('IN' ,$PK_TIME_CLOCK_PROCESSOR,$in_result->PK_TIME_CLOCK_PROCESSOR_DETAIL,$att_date,$REPLACE_BETWEEN_FOR_IN,$ORIGINAL_TIMES_IN,$CHECK_IN_TIME_24_ROUND_OF_TO);
   }  
   $in_result = null;
   #OUT : GET ALL & UPDATE IN TIMES & DATA IN DB Accordingly

   $OUT_RESULTS = GetEligibleResults_IN_OR_OUT('OUT',$PK_TIME_CLOCK_PROCESSOR,$PK_TIME_CLOCK_PROCESSOR_DETAIL_IDS,$att_date,$CHECK_OUT_TIME_24_FROM,$CHECK_OUT_TIME_24_END);
   //dd($IN_RESULTS);
   foreach($OUT_RESULTS as $in_result){
    $REPLACE_BETWEEN_FOR_IN = ['START_TIME' => $CHECK_OUT_TIME_24_FROM , 'TILL_TIME' => $CHECK_OUT_TIME_24_END];
    $ORIGINAL_TIMES_IN = ['ORIGINAL_IN_TIME'=> $in_result->CHECK_IN_TIME, 'ORIGINAL_OUT_TIME'=>$in_result->CHECK_OUT_TIME];
    UpdateEligibleResults('OUT' ,$PK_TIME_CLOCK_PROCESSOR,$in_result->PK_TIME_CLOCK_PROCESSOR_DETAIL,$att_date,$REPLACE_BETWEEN_FOR_IN,$ORIGINAL_TIMES_IN,$CHECK_OUT_TIME_24_ROUND_OF_TO);
   } 
 
   $data = [];
   $data['success'] = "Modification Successful !";
   echo json_encode($data);
   exit;
    // header("location:time_clock_result?id=".$_GET['id'].'&t='.$_GET['t']);
}


function convertTo24HourFormat($inputTime)
{
    // Use DateTime to handle time conversion
    $dateTime = DateTime::createFromFormat('h:i A', $inputTime);

    // Check if the conversion was successful
    if ($dateTime === false) {
        $data = [];
        $data['error'] = "Error: Invalid time format. Please use the format HH:mm AM/PM.";
        echo json_encode($data);
        exit;
    }

    // Convert to 24-hour format
    $outputTime = $dateTime->format('H:i:s');
    
    return $outputTime;
}


function GetTimeDifferance($timeField1 , $timeField2){

// Set a common date
$commonDate = "2023-01-01";

// Create DateTime objects from the time strings with the common date
$dateTime1 = new DateTime("$commonDate $timeField1");
$dateTime2 = new DateTime("$commonDate $timeField2");

// Calculate the difference
$timeDifference = $dateTime1->diff($dateTime2);

// Access the difference in hours, minutes, and seconds
$hours = $timeDifference->h;
$minutes = $timeDifference->i;
$seconds = $timeDifference->s;
return [$hours , $minutes , $seconds];
}




function GetEligibleResults_IN_OR_OUT($IN_OR_OUT ,$PK_TIME_CLOCK_PROCESSOR,$PK_TIME_CLOCK_PROCESSOR_DETAIL_IDS,$att_date, $TIME1 , $TIME2 ){ 
    if($IN_OR_OUT == 'IN'){
        $time_condition  = " AND ( CHECK_IN_TIME BETWEEN '$TIME1' AND '$TIME2' ) ";
    }else if($IN_OR_OUT == 'OUT'){
        $time_condition  = " AND ( CHECK_OUT_TIME BETWEEN '$TIME1' AND '$TIME2' ) ";
    }else{
        exit;
    }

    $query = "SELECT
                PK_TIME_CLOCK_PROCESSOR_DETAIL,
                CONCAT(LAST_NAME, ' ', FIRST_NAME) AS NAME,
                MESSAGE,
                SCHEDULE_FOUND,
                CHECK_IN_DATE,
                CHECK_IN_TIME,
                CHECK_OUT_TIME,
                ATTENDANCE_HOUR,
                PK_ATTENDANCE_CODE,
                S_STUDENT_MASTER.PK_STUDENT_MASTER,
                PK_STUDENT_ENROLLMENT,
                PK_COURSE_OFFERING,
                STUDENT_ID,
                SUBTIME(CHECK_OUT_TIME, CHECK_IN_TIME) AS TIME_DIFF,
                S_TIME_CLOCK_PROCESSOR_DETAIL.BADGE_ID,
                BREAK_IN_MIN,
                NOT_FOUND_ON_FILE,
                PK_ATTENDANCE_ACTIVITY_TYPE,
                PK_COURSE_OFFERING_SCHEDULE_DETAIL
            FROM
                S_TIME_CLOCK_PROCESSOR_DETAIL
            LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_TIME_CLOCK_PROCESSOR_DETAIL.PK_STUDENT_MASTER
            WHERE
                S_TIME_CLOCK_PROCESSOR_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                AND S_TIME_CLOCK_PROCESSOR_DETAIL.PK_TIME_CLOCK_PROCESSOR = '$PK_TIME_CLOCK_PROCESSOR' 
                AND PK_TIME_CLOCK_PROCESSOR_DETAIL IN ($PK_TIME_CLOCK_PROCESSOR_DETAIL_IDS)
                AND ( CHECK_IN_DATE = '$att_date' AND CHECK_OUT_DATE = '$att_date')
                AND POSTED = 0
                $time_condition 
            ORDER BY
                CONCAT(LAST_NAME, ' ', FIRST_NAME),
                CHECK_IN_DATE,
                CHECK_IN_TIME ASC";
                
    $result = CapsuleDB::select($query);
    return $result;
}


function UpdateEligibleResults($IN_OR_OUT,$PK_TIME_CLOCK_PROCESSOR,$PK_TIME_CLOCK_PROCESSOR_DETAIL, $att_date, $REPLACE_BETWEEN,$ORIGINAL_TIMES,$TIME_TO_BECHANGED){
    $TIME1 = $REPLACE_BETWEEN['START_TIME'];
    $TIME2 = $REPLACE_BETWEEN['TILL_TIME'];
    if($IN_OR_OUT == 'IN'){
        $time_condition  = " AND ( CHECK_IN_TIME BETWEEN '$TIME1' AND '$TIME2' ) ";

        $in_out_diff = GetTimeDifferance($ORIGINAL_TIMES['ORIGINAL_OUT_TIME'] , $TIME_TO_BECHANGED);
        $hour_part = $in_out_diff[0];
        $min_part = ($in_out_diff[1] / 60);
        $Hours_If_to_be_updated = number_format_value_checker_new($hour_part + $min_part , 2);
        $update_str = " CHECK_IN_TIME = '$TIME_TO_BECHANGED' , ATTENDANCE_HOUR = '$Hours_If_to_be_updated' ";
    }else if($IN_OR_OUT == 'OUT'){
        $time_condition  = " AND ( CHECK_OUT_TIME BETWEEN '$TIME1' AND '$TIME2' ) ";

        $in_out_diff = GetTimeDifferance($TIME_TO_BECHANGED,$ORIGINAL_TIMES['ORIGINAL_IN_TIME']);
        $hour_part = $in_out_diff[0];
        $min_part = ($in_out_diff[1] / 60);
        $Hours_If_to_be_updated = number_format_value_checker_new($hour_part + $min_part , 2);
        $update_str = " CHECK_OUT_TIME = '$TIME_TO_BECHANGED' , ATTENDANCE_HOUR = '$Hours_If_to_be_updated' ";
    }else{
        exit;
    }
    $update_sql =  "UPDATE 
    `S_TIME_CLOCK_PROCESSOR_DETAIL` 
    SET 
    $update_str
    WHERE 
    S_TIME_CLOCK_PROCESSOR_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
    AND S_TIME_CLOCK_PROCESSOR_DETAIL.PK_TIME_CLOCK_PROCESSOR = '$PK_TIME_CLOCK_PROCESSOR' 
    AND PK_TIME_CLOCK_PROCESSOR_DETAIL IN ($PK_TIME_CLOCK_PROCESSOR_DETAIL)
    AND ( CHECK_IN_DATE = '$att_date' AND CHECK_OUT_DATE = '$att_date')
    $time_condition"; 
    try {
        CapsuleDB::update($update_sql); 
    } catch (\Throwable $th) {
        $data['error'] = "ERROR : Some issue ocurred while updating the  data ! Update Failed for $PK_TIME_CLOCK_PROCESSOR_DETAIL. Further operation paused";
        echo json_encode($data);
        exit;
    }
    

    
}
function compareTimes($time1, $time2) {
    list($hours1, $minutes1, $seconds1) = explode(":", $time1);
    list($hours2, $minutes2, $seconds2) = explode(":", $time2);

    if ($hours1 < $hours2) {
        return -1;
    } elseif ($hours1 > $hours2) {
        return 1;
    } else {
        // If hours are equal, compare minutes
        if ($minutes1 < $minutes2) {
            return -1;
        } elseif ($minutes1 > $minutes2) {
            return 1;
        } else {
            // If minutes are equal, compare seconds
            if ($seconds1 < $seconds2) {
                return -1;
            } elseif ($seconds1 > $seconds2) {
                return 1;
            } else {
                return 0; // Both times are equal
            }
        }
    }
}
function give_error($msg){
    $data['simple_error'] = "ERROR : ".$msg.". Please try again !";
    echo json_encode($data);
    exit;
}