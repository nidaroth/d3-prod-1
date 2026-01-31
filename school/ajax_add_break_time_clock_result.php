<?
require_once("../global/config.php"); 
require_once("function_attendance.php");
require_once("check_access.php");
if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
//ENABLE_DEBUGGING(true);

header('Content-Type: application/json; charset=utf-8');

$PK_TIME_CLOCK_PRO  = $_REQUEST['PK_TIME_CLOCK_PROCESSOR'];
$PK_TIME_CLOCK_IDS  = $_REQUEST['PK_TIME_CLOCK_PROCESSOR_DETAIL_IDS'];
$attendance_date    = date("Y-m-d",strtotime($_REQUEST['attendance_date']));
$min_time           = $_REQUEST['min_time'];

$break_begin        = convertTo24HourFormat($_REQUEST['break_begin']);
$break_end          = convertTo24HourFormat($_REQUEST['break_end']);

$in_out_diff        = GetTimeDifferance($break_begin , $break_end);
$hour_part          = $in_out_diff[0];
$min_part           = ($in_out_diff[1] / 60);
$add_break_time     = $hour_part + $min_part;
$final_break_time   = number_format_value_checker($add_break_time,2);
//dd($final_break_time,$hour_part,$min_part);

// echo $final_break_time;
// echo "<br>";

// echo $break_begin.' | '.$break_end;exit;
// echo "<pre>";
// print_r($_REQUEST);exit;

$query = "SELECT PK_TIME_CLOCK_PROCESSOR_DETAIL, 
                CONCAT(LAST_NAME, ' ', FIRST_NAME) as NAME, 
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
                AND S_TIME_CLOCK_PROCESSOR_DETAIL.PK_TIME_CLOCK_PROCESSOR = '$PK_TIME_CLOCK_PRO'
                AND S_TIME_CLOCK_PROCESSOR_DETAIL.PK_TIME_CLOCK_PROCESSOR_DETAIL IN ($PK_TIME_CLOCK_IDS)
                AND S_TIME_CLOCK_PROCESSOR_DETAIL.CHECK_IN_DATE = '$attendance_date' AND S_TIME_CLOCK_PROCESSOR_DETAIL.CHECK_OUT_DATE = '$attendance_date'
                -- AND S_TIME_CLOCK_PROCESSOR_DETAIL.CHECK_IN_TIME = '$break_begin' AND S_TIME_CLOCK_PROCESSOR_DETAIL.CHECK_OUT_TIME = '$break_end'
            ORDER BY 
                CONCAT(LAST_NAME, ' ', FIRST_NAME), 
                CHECK_IN_DATE, 
                CHECK_IN_TIME ASC ";
        
$res_type = $db->Execute($query);
while (!$res_type->EOF) 
{  
    $attendnace_hour = $res_type->fields['ATTENDANCE_HOUR'];
    $PK_TIME_CLOCK_PROCESSOR_DETAIL = $res_type->fields['PK_TIME_CLOCK_PROCESSOR_DETAIL']; 

    $data = array();

    if($attendnace_hour  >= $min_time)
    {
        if($min_time >= $final_break_time)
        {
            $db->Execute("UPDATE S_TIME_CLOCK_PROCESSOR_DETAIL SET BREAK_IN_MIN = '$final_break_time' WHERE S_TIME_CLOCK_PROCESSOR_DETAIL.PK_TIME_CLOCK_PROCESSOR_DETAIL = '$PK_TIME_CLOCK_PROCESSOR_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
            
           
        }
        else{
            $data['error_data'] = "Not Match data.";
            echo json_encode($data);
            exit;
        }
    }
    else{
        $data['error_data'] = "Not Match data.";
        echo json_encode($data);
        exit;
    }
    
    $res_type->MoveNext();
}
$data['success'] = "success";
echo json_encode($data);
exit;

function convertTo24HourFormat($inputTime)
{
    // Use DateTime to handle time conversion
    $dateTime = DateTime::createFromFormat('H:i A', $inputTime);

    // Check if the conversion was successful
    if ($dateTime === false) {
        $data = array();
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
?>