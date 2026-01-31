<?php
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");
if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}



function get_student_attendace_hours($PK_STUDENT_MASTER, $PK_TERM_MASTER)
{
	
global $db;
$response_arr=array();

$present_att_code_arr = array();
$res_present_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
while (!$res_present_att_code->EOF) {
	$present_att_code_arr[] = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_present_att_code->MoveNext();
}

$exc_att_code_arr = array();
$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
while (!$res_exc_att_code->EOF) {
	$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_exc_att_code->MoveNext();
}



$sort_order = " S_STUDENT_SCHEDULE.SCHEDULE_DATE ASC, S_STUDENT_SCHEDULE.START_TIME ASC ";

$cond="";
if($PK_TERM_MASTER!=""){
	$cond = " AND S_COURSE_OFFERING.PK_TERM_MASTER IN($PK_TERM_MASTER)";
}


$query = "SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) as STUD_NAME, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS BEGIN_DATE_1, IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, IF(S_STUDENT_SCHEDULE.END_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.END_TIME,'%h:%i %p'),'') AS END_TIME, IF(S_STUDENT_SCHEDULE.START_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS,ATTENDANCE_HOURS, CONCAT(S_COURSE.COURSE_CODE, ' - ', S_COURSE.TRANSCRIPT_CODE) as COURSE_CODE, SCHEDULE_TYPE, S_STUDENT_ATTENDANCE.COMPLETED AS COMPLETED_1, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE,  S_STUDENT_ATTENDANCE.PK_STUDENT_ATTENDANCE, S_COURSE_OFFERING.PK_TERM_MASTER, S_COURSE_OFFERING.PK_COURSE_OFFERING, S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL, IF(S_STUDENT_ATTENDANCE.PK_STUDENT_ATTENDANCE > 0, M_ATTENDANCE_ACTIVITY_TYPE_ATT.ATTENDANCE_ACTIVITY_TYPE, M_ATTENDANCE_ACTIVITY_TYPE_SCH.ATTENDANCE_ACTIVITY_TYPE) as ATTENDANCE_ACTIVITY_TYPE, S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, ATTENDANCE_COMMENTS,S_COURSE_OFFERING.PK_CAMPUS $field FROM 

S_STUDENT_MASTER, S_STUDENT_SCHEDULE 
LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
LEFT JOIN M_ATTENDANCE_ACTIVITY_TYPE as M_ATTENDANCE_ACTIVITY_TYPE_ATT ON  M_ATTENDANCE_ACTIVITY_TYPE_ATT.PK_ATTENDANCE_ACTIVITY_TYPE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS  
LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON  S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL 
LEFT JOIN M_ATTENDANCE_ACTIVITY_TYPE as M_ATTENDANCE_ACTIVITY_TYPE_SCH ON  M_ATTENDANCE_ACTIVITY_TYPE_SCH.PK_ATTENDANCE_ACTIVITY_TYPE = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_ATTENDANCE_ACTIVITY_TYPES   
WHERE 
S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ORDER BY $sort_order ";

// echo $query;
// exit;
	$res_course_schedule = $db->Execute($query);
	$TOTAL_HOURS 		= 0;
	$ATTENDANCE_HOURS 	= 0;
	$COMPLETED_HOURS=0;



	$res_att_act = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES, ENABLE_ATTENDANCE_COMMENTS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); //Ticket # 1037 Ticket # 1601 
	while (!$res_course_schedule->EOF) { 
		$exc_att_flag = 0;
		foreach($exc_att_code_arr as $exc_att_code) {
			if($exc_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
				$exc_att_flag = 1;
				break;
			}
		}
		
		$present_flag = 0;
		foreach($present_att_code_arr as $present_att_code) {
			if($present_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
				$present_flag = 1;
				break;
			}
		}

		if($res_course_schedule->fields['ATTENDANCE_CODE'] != 'I' ){ 
			if($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2){
				if($present_flag == 1) {

					$ATTENDANCE_HOURS 	+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
				}
			}

			if($res_course_schedule->fields['ATTENDANCE_CODE'] != 'I' && $exc_att_flag == 0){
				$TOTAL_HOURS += $res_course_schedule->fields['HOURS']; 
			}

		}
		$res_course_schedule->MoveNext();

	}

  return  $response_arr=array('ATTENDANCE_HOURS'=>$ATTENDANCE_HOURS,'HOURS'=>$TOTAL_HOURS);
}

function get_student_units_completed($PK_STUDENT_MASTER,$PK_TERM_MASTER)
{
	global $db;

	// include all transfer credits the values
	$summation_of_weight=0;
	$summation_of_gapvalue=0;
	$unit_completed=0;
	$unit_attempted=0;
	$fa_unit_completed=0;
	$fa_unit_attempted=0;
	$cond="";
	if($PK_TERM_MASTER!=""){
		$cond = " AND CO.PK_TERM_MASTER IN($PK_TERM_MASTER)";
	}

$sql_courses="(
	SELECT 
	  'TRANSFER' AS COURSE_TYPE, 
	  S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_CREDIT_TRANSFER, 
	  PK_STUDENT_MASTER, 
	  CONCAT(
		S_COURSE.COURSE_CODE, ' - ', S_COURSE.TRANSCRIPT_CODE
	  ) as COURSE_CODE, 
	  S_STUDENT_CREDIT_TRANSFER.UNITS, 
	  S_STUDENT_CREDIT_TRANSFER.FA_UNITS, 
	  CASE When S_GRADE.CALCULATE_GPA = '1' THEN Power(
		S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
	  )* S_GRADE.NUMBER_GRADE ELSE 0 END AS StudentGPAValue, 
	  CASE When S_GRADE.CALCULATE_GPA = '1' THEN Power(
		S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
	  ) Else 0 End AS StudentGPAWeight, 
	  CASE When S_GRADE.UNITS_IN_PROGRESS = '1' Then S_STUDENT_CREDIT_TRANSFER.UNITS Else 0 End AS StudentUnitsInProgress, 
	  Case When S_GRADE.UNITS_ATTEMPTED = '1' Then S_STUDENT_CREDIT_TRANSFER.UNITS Else 0 End AS StudentUnitsAttempted, 
	  Case When S_GRADE.UNITS_COMPLETED = '1' Then S_STUDENT_CREDIT_TRANSFER.UNITS Else 0 End as StudentUnitsCompleted, 
	  Case When S_GRADE.UNITS_ATTEMPTED = '1' Then S_STUDENT_CREDIT_TRANSFER.FA_UNITS Else 0 End AS StudentFAUnitsAttempted, 
	  Case When S_GRADE.UNITS_COMPLETED = '1' Then S_STUDENT_CREDIT_TRANSFER.FA_UNITS Else 0 End as StudentFAUnitsCompleted, 
	  CASE When S_GRADE.UNITS_IN_PROGRESS = '1' Then S_STUDENT_CREDIT_TRANSFER.FA_UNITS Else 0 End AS StudentFAUnitsInProgress 
	FROM 
	  S_STUDENT_CREDIT_TRANSFER 
	  LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
	  LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE 
	  LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
	WHERE 
	  S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
	  AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
	  AND SHOW_ON_TRANSCRIPT = 1 
	ORDER BY 
	  S_COURSE.COURSE_CODE ASC
  ) 
  UNION ALL 
	(
	  SELECT 
		'REGULAR' AS COURSE_TYPE, 
		COS.PK_COURSE_OFFERING, 
		COS.PK_STUDENT_MASTER, 
		CONCAT(
		  C.COURSE_CODE, ' - ', C.TRANSCRIPT_CODE
		) as COURSE_CODE, 
		C.UNITS, 
		C.FA_UNITS, 
		CASE When G.CALCULATE_GPA = '1' THEN Power(C.UNITS, G.WEIGHTED_GRADE_CALC)* G.NUMBER_GRADE ELSE 0 END AS StudentGPAValue, 
		CASE When G.CALCULATE_GPA = '1' THEN Power(C.UNITS, G.WEIGHTED_GRADE_CALC) Else 0 End AS StudentGPAWeight, 
		CASE When G.UNITS_IN_PROGRESS = '1' Then C.UNITS Else 0 End AS StudentUnitsInProgress, 
		Case When G.UNITS_ATTEMPTED = '1' Then COS.COURSE_UNITS Else 0 End AS StudentUnitsAttempted, 
		Case When G.UNITS_COMPLETED = '1' Then C.UNITS Else 0 End as StudentUnitsCompleted, 
		Case When G.UNITS_ATTEMPTED = '1' Then C.FA_UNITS Else 0 End AS StudentFAUnitsAttempted, 
		Case When G.UNITS_COMPLETED = '1' Then C.FA_UNITS Else 0 End as StudentFAUnitsCompleted, 
		CASE When G.UNITS_IN_PROGRESS = '1' Then C.FA_UNITS Else 0 End AS StudentFAUnitsInProgress 
	  FROM 
		S_STUDENT_COURSE AS COS 
		INNER JOIN S_STUDENT_MASTER AS S ON COS.PK_STUDENT_MASTER = S.PK_STUDENT_MASTER 
		AND COS.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
		INNER JOIN S_COURSE_OFFERING AS CO ON COS.PK_COURSE_OFFERING = CO.PK_COURSE_OFFERING
		LEFT JOIN S_TERM_MASTER AS ST ON ST.PK_TERM_MASTER = CO.PK_TERM_MASTER 
		INNER JOIN S_COURSE AS C ON CO.PK_COURSE = C.PK_COURSE 
		INNER JOIN S_GRADE AS G ON COS.FINAL_GRADE = G.PK_GRADE 
		LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS AS COSS ON COS.PK_COURSE_OFFERING_STUDENT_STATUS = COSS.PK_COURSE_OFFERING_STUDENT_STATUS  
	  WHERE 
		COSS.SHOW_ON_TRANSCRIPT = '1' 
		AND COS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond
	)
  ";

// echo $sql_courses;
// exit;
	$result=$db->Execute($sql_courses);
	while (!$result->EOF) { 
		    if($PK_TERM_MASTER!=""){
				if($result->fields['COURSE_TYPE']=="REGULAR"){
					$unit_completed  += $result->fields['StudentUnitsCompleted'];
					$unit_attempted += $result->fields['StudentUnitsAttempted'];
					$fa_unit_completed += $result->fields['StudentFAUnitsCompleted'];
					$fa_unit_attempted += $result->fields['StudentFAUnitsAttempted'];
					$summation_of_weight += $result->fields['StudentGPAWeight'];;
					$summation_of_gapvalue += $result->fields['StudentGPAValue'];
				}
			}else{
				$unit_completed  += $result->fields['StudentUnitsCompleted'];
				$unit_attempted += $result->fields['StudentUnitsAttempted'];
				$fa_unit_completed += $result->fields['StudentFAUnitsCompleted'];
				$fa_unit_attempted += $result->fields['StudentFAUnitsAttempted'];
				$summation_of_weight += $result->fields['StudentGPAWeight'];;
				$summation_of_gapvalue += $result->fields['StudentGPAValue'];
			}
		$result->MoveNext();
	}

	$row=get_student_attendace_hours($PK_STUDENT_MASTER,$PK_TERM_MASTER);

	
	$response_arr=array();
	$GPA=0;
	if($summation_of_weight>0){
	$GPA= ($summation_of_gapvalue/$summation_of_weight);
	}

	if($unit_completed!=0){
	$response_arr['UNIT_COMPLETED']=$unit_completed;
	}
	if($unit_attempted!=0){
	$response_arr['UNIT_ATTEMPTED']=$unit_attempted;
	}
	if($fa_unit_completed!=0){
	$response_arr['FA_UNIT_COMPLETED']=$fa_unit_completed;
	}
	if($fa_unit_attempted!=0){
	$response_arr['FA_UNIT_ATTEMPTED']=$fa_unit_attempted;
	}
	if($row['ATTENDANCE_HOURS']!=0){
	$response_arr['ATTENDACE_HOURS']=$row['ATTENDANCE_HOURS'];
	}
	if($row['HOURS']!=0){
	$response_arr['SCHEDULE_HOURS']=$row['HOURS'];
	}
	//$response_arr['SESSION_UNIQUE_ID']=;
	if($GPA!=0){
	$response_arr['TERM_GPA']=$GPA;
	}

	if(count($response_arr)>0){
		$response_arr['PK_ACCOUNT']=$_SESSION['PK_ACCOUNT'];
		$response_arr['PK_STUDENT_MASTER']=$PK_STUDENT_MASTER;
		$response_arr['PK_USER']=$_SESSION['PK_USER'];
		return $response_arr;
	}else{
		return 0;
	}


}

/////////////////////////////////////////////////////////////////
$wh_cond ='';
if(!empty($PK_STUDENT_MASTER)){
$wh_cond .= " AND SE.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) ";
}

$terms = "";
if(!empty($PK_TERM_MASTER1)){
    $wh_cond .= " AND CO.PK_TERM_MASTER IN ($PK_TERM_MASTER1) ";

    $res_term = $db->Execute("select IF(BEGIN_DATE != '0000-00-00',DATE_FORMAT(BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE from S_TERM_MASTER WHERE PK_TERM_MASTER IN ($PK_TERM_MASTER1) ORDER BY BEGIN_DATE ASC ");
        while (!$res_term->EOF) {
            if($terms != '')
                $terms .= ', ';
            $terms .= $res_term->fields['TERM_BEGIN_DATE'];	
            $res_term->MoveNext();
        }        
        if(count(explode(',',$terms)) > 8){
        $terms = "Multiple Terms Selected";
        }
}


$campus_name='';
if(!empty($PK_CAMPUS1)){

    $wh_cond .= " AND CO.PK_CAMPUS IN ($PK_CAMPUS1) ";
    $res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($PK_CAMPUS1) order by CAMPUS_CODE ASC");
        while (!$res_campus->EOF) {
            if($campus_name != '')
                $campus_name .= ', ';
            $campus_name .= $res_campus->fields['CAMPUS_CODE'];			
            $res_campus->MoveNext();
        }    
}

$inner_join_cond ='';
if($EXCLUDE_NON_PROGRAM_COURSES==1){
 //  $inner_join_cond ='INNER JOIN M_CAMPUS_PROGRAM_COURSE AS PC ON P.PK_CAMPUS_PROGRAM = PC.PK_CAMPUS_PROGRAM AND CO.PK_COURSE = PC.PK_COURSE';
}

$txt = '';  
$txt .= '<div style="page-break-before: always;">';
$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
<thead>
    <tr>
        <td width="15%" style="border-bottom:1px solid #000;">
            <b><i>Student Name</i></b>
        </td>
        <td width="10%" style="border-bottom:1px solid #000;">
             <b><i>Student ID</i></b>
        </td>
        <td width="10%" style="border-bottom:1px solid #000;">
            <b><i>Campus</i></b>
        </td>
        <td width="8%" style="border-bottom:1px solid #000;">
            <b><i>First Term</i></b>
        </td>
        <td width="13%" style="border-bottom:1px solid #000;">
            <b><i>Program</i></b>
        </td>
        <td width="10%" style="border-bottom:1px solid #000;">
            <b><i>Exp. Grad Date</i></b>
        </td>
        <td width="10%" style="border-bottom:1px solid #000;">
            <b><i>Units <br/>Attempted</i></b>
        </td>
        <td width="10%" style="border-bottom:1px solid #000;">
            <b><i>Completed <br/>Units</i></b>
        </td>
        <td width="10%" style="border-bottom:1px solid #000;">
            <b><i>FA Units</i></b>
        </td>
        <td width="7%" style="border-bottom:1px solid #000;">
            <b><i>Hours</i></b>
        </td>
    </tr>
</thead>';

 
$sql_query ="SELECT CONCAT(S.LAST_NAME, ', ', S.FIRST_NAME) AS STUDENT
,COALESCE(SA.STUDENT_ID,'NO STUDENT ID') AS STUDENT_ID,SC.CAMPUS_CODE
,P.CODE AS PROGRAM
,DATE_FORMAT(T.BEGIN_DATE,'%m/%d/%Y') AS FIRST_TERM_DATE
,IF(EXPECTED_GRAD_DATE = '0000-00-00',
           '',
           DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y')
       ) AS EXPECTED_GRAD_DATE
,SS.STUDENT_STATUS
,SUM((CASE WHEN G.UNITS_ATTEMPTED = 1 THEN C.FA_UNITS  ELSE  0 END)) AS FA_UNITS_ATTEMPTED
,SUM((CASE WHEN G.UNITS_ATTEMPTED = 1 THEN C.UNITS  ELSE  0 END)) AS UNITS_ATTEMPTED
,SUM((CASE WHEN G.UNITS_ATTEMPTED = 1 THEN C.HOURS  ELSE  0 END)) AS HOURS_ATTEMPTED
,SUM((CASE WHEN G.UNITS_COMPLETED = 1 THEN COS.COURSE_UNITS  ELSE  0 END)) AS UNIT_COMPLETED
,GROUP_CONCAT(DISTINCT DATE_FORMAT(CO_TERM.BEGIN_DATE,'%m/%d/%Y') ORDER BY CO_TERM.BEGIN_DATE) AS COURSE_TERMS
,COUNT(*) AS COURSES_ATTEMPTED
,GROUP_CONCAT(C.COURSE_CODE ORDER BY C.COURSE_CODE) AS COURSES,SE.PK_STUDENT_ENROLLMENT, S.PK_STUDENT_MASTER

FROM S_STUDENT_ENROLLMENT AS SE
INNER JOIN S_STUDENT_MASTER AS S ON SE.PK_STUDENT_MASTER = S.PK_STUDENT_MASTER
INNER JOIN S_STUDENT_ACADEMICS AS SA ON S.PK_STUDENT_MASTER = SA.PK_STUDENT_MASTER
INNER JOIN M_CAMPUS_PROGRAM AS P ON SE.PK_CAMPUS_PROGRAM = P.PK_CAMPUS_PROGRAM
LEFT JOIN M_ENROLLMENT_STATUS_SCALE_MASTER AS ESSM On P.PK_ENROLLMENT_STATUS_SCALE_MASTER = ESSM.PK_ENROLLMENT_STATUS_SCALE_MASTER
INNER JOIN M_STUDENT_STATUS AS SS ON SE.PK_STUDENT_STATUS = SS.PK_STUDENT_STATUS
INNER JOIN S_TERM_MASTER AS T ON SE.PK_TERM_MASTER = T.PK_TERM_MASTER
INNER JOIN S_STUDENT_COURSE AS COS ON SE.PK_STUDENT_ENROLLMENT = COS.PK_STUDENT_ENROLLMENT
INNER JOIN S_COURSE_OFFERING AS CO ON COS.PK_COURSE_OFFERING = CO.PK_COURSE_OFFERING
$inner_join_cond 
INNER JOIN S_TERM_MASTER AS CO_TERM ON CO.PK_TERM_MASTER = CO_TERM.PK_TERM_MASTER
INNER JOIN M_COURSE_OFFERING_STUDENT_STATUS AS COSS ON COS.PK_COURSE_OFFERING_STUDENT_STATUS = COSS.PK_COURSE_OFFERING_STUDENT_STATUS
INNER JOIN S_COURSE AS C ON CO.PK_COURSE = C.PK_COURSE
INNER JOIN S_GRADE AS G ON COS.FINAL_GRADE = G.PK_GRADE
LEFT JOIN M_ENROLLMENT_STATUS AS ES ON SE.PK_ENROLLMENT_STATUS = ES.PK_ENROLLMENT_STATUS
LEFT JOIN S_STUDENT_CAMPUS SSC	ON SSC.PK_STUDENT_ENROLLMENT = SE.PK_STUDENT_ENROLLMENT
INNER JOIN S_CAMPUS AS SC ON SC.PK_CAMPUS = SSC.PK_CAMPUS
WHERE SE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $wh_cond
AND SS.ADMISSIONS = 0
AND COSS.SHOW_ON_TRANSCRIPT = 1
GROUP BY SE.PK_STUDENT_ENROLLMENT

ORDER BY CONCAT(S.LAST_NAME, ', ', S.FIRST_NAME)
,SA.STUDENT_ID
,T.BEGIN_DATE
,P.CODE
,SS.STUDENT_STATUS";	

//echo $sql_query; exit;
$res = $db->Execute($sql_query);

while (!$res->EOF) {

    $PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
    $PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
    //$studentdata =  get_student_units_completed($PK_STUDENT_MASTER,$PK_TERM_MASTER1);                    
                
    $txt .='<tbody>';
    $txt .= '<tr>
                <td >'.$res->fields['STUDENT'].'</td>
                <td >'.$res->fields['STUDENT_ID'].'</td>
                <td >'.$res->fields['CAMPUS_CODE'].'</td>
                <td >'.$res->fields['FIRST_TERM_DATE'].'</td>
                <td >'.$res->fields['PROGRAM'].'</td>
                <td >'.$res->fields['EXPECTED_GRAD_DATE'].'</td>
				<td >'.number_format_value_checker( $res->fields['UNITS_ATTEMPTED'] , 2).'</td>
                <td >'.number_format_value_checker( $res->fields['UNIT_COMPLETED'] , 2).'</td>
                <td >'.number_format_value_checker( $res->fields['FA_UNITS_ATTEMPTED'] , 2).'</td>
                <td >'.number_format_value_checker( $res->fields['HOURS_ATTEMPTED'] ,2).'</td>
            </tr>';
    $txt .='</tbody>';

       
        $res->MoveNext();
     }
   
    $txt .= '</table>';
    $txt .= '</div>';
 
 //echo $txt; exit;
  
$file_name = 'Units_Attempted_By_Term_'.uniqid().'.pdf'; 

$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$logo='';
if($res->fields['PDF_LOGO'] != '')
    $PDF_LOGO =$res->fields['PDF_LOGO'];
    
    if($PDF_LOGO != ''){
        $PDF_LOGO=str_replace('../',$http_path,$PDF_LOGO);
        $logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
    }

 if(!empty($campus_name)){
    $campus_name = 'Campus(es): '.$campus_name;
 }   

 if(!empty($terms)){
    $terms = 'Term(s): '.$terms;
 } 

$SCHOOL_NAME ='';
if($res->fields['SCHOOL_NAME'] != '')
    $SCHOOL_NAME =$res->fields['SCHOOL_NAME'];

    $header = '<table width="100%" >
    <tr>
        <td width="20%" valign="top" >'.$logo.'</td>
        <td width="30%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
        <td width="50%" valign="top" >
            <table width="100%" >
                <tr>
                    <td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Units Attempted By Term
                    </b></td>
                </tr>
                <tr>
                    <td width="100%" align="right" style="font-size:13px;" >'.$campus_name.'</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="3" width="100%" align="right" style="font-size:13px;" >'.$terms.'</td>
    </tr>   
</table>';


$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
    $res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
    $timezone = $res->fields['PK_TIMEZONE'];
    if($timezone == '' || $timezone == 0)
        $timezone = 4;
}

$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
                        

$footer = '<table width="100%">
<tr>
    <td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
    <td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
    <td></td>							
</tr>
</table>';				


$header_cont= '<!DOCTYPE HTML>
<html>
	<head>
		<style>
			div{ padding-bottom:20px !important; }	
		</style>
	</head>
	<body>
		<div> '.$header.' </div>
	</body>
</html>';
$html_body_cont = '<!DOCTYPE HTML>
<html>
	<head> 
		<style>
			body{ font-size:12px; font-family:helvetica; }	
			table{  margin-top: 16px; }
			table tr{  padding-top: 15px !important; }
		</style>
	</head>
<body>'.$txt.'</body>
</html>';
$footer_cont= '<!DOCTYPE HTML>
<html>
	<head>
		<style>
			tbody td{ font-size:15px !important; }
		</style>
	</head>
	<body>'.$footer.'</body>
</html>';

$header_path= create_html_file('header.html',$header_cont,'units_attempted_by_term');
$content_path=create_html_file('content.html',$html_body_cont,'units_attempted_by_term');
$footer_path= create_html_file('footer.html',$footer_cont,'units_attempted_by_term');

sleep(2);
$margin_top="30mm";
// if(strlen($header)>1530){
// $margin_top="60mm";
// }
exec('xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 230mm --page-height 297mm --margin-top '.$margin_top.'  --footer-spacing 8  --margin-left 5mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/units_attempted_by_term/'.$file_name.' 2>&1');


header('Content-Type: Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($http_path.'school/temp/units_attempted_by_term/'.$file_name) . '"');
//header('Content-Length: ' . $pdfdata['filefullpath']);
readfile('temp/units_attempted_by_term/'.$file_name);
unlink('../school/temp/units_attempted_by_term/'.$file_name); // unlink file after download
exit;
?>