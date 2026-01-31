<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}


include '../global/excel/Classes/PHPExcel/IOFactory.php';
$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$total_fields = 120;
for($i = 0 ; $i <= $total_fields ; $i++){
	if($i <= 25)
		$cell[] = $cell1[$i];
	else {
		$j = floor($i / 26) - 1;
		$k = ($i % 26);
		//echo $j."--".$k."<br />";
		$cell[] = $cell1[$j].$cell1[$k];
	}	
}
$contents = []; //Ticket # 659
$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
//$file_name 		= 'Attendance Tardy Hours.xlsx';
$file_name 		= 'Attendance Tardy Hours.csv'; //Ticket # 659
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line++;
$index 	= -1;
$heading[] = 'Student';
$width[]   = 30;
$heading[] = 'Student ID';
$width[]   = 15;
$heading[] = 'Campus';
$width[]   = 15;
$heading[] = 'First Term';
$width[]   = 15;
$heading[] = 'Program';
$width[]   = 15;
$heading[] = 'Status';
$width[]   = 15;
$heading[] = 'Course Term';
$width[]   = 15;
$heading[] = 'Course';
$width[]   = 15;
$heading[] = 'Course Description';
$width[]   = 20;
$heading[] = 'Instructor';
$width[]   = 15;
$heading[] = 'Class Date';
$width[]   = 15;
$heading[] = 'Hour Missed';
$width[]   = 15;

/* Ticket # 1601 */
$res_att_act = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES, ENABLE_ATTENDANCE_COMMENTS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_att_act->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){
	$heading[] = 'Activity Type';
	$width[]   = 20;
}

if($res_att_act->fields['ENABLE_ATTENDANCE_COMMENTS'] == 1){
	$heading[] = 'Comments';
	$width[]   = 20;
}
/* Ticket # 1601 */

$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	
	$i++;
}	
// Ticket # DIAM-659 
$_GET['st'] = $_POST['START_DATE'];
$_GET['et'] = $_POST['END_DATE'];
$_GET['comm'] = $_POST['comm'];

$cond1 = "";
if(!empty($_POST['PK_CAMPUS'])){
	$PK_CAMPUS = implode(",",$_POST['PK_CAMPUS']);
	$cond1 .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
}

if(!empty($_POST['PK_TERM_MASTER'])){
	$PK_TERM_MASTER = implode(",",$_POST['PK_TERM_MASTER']);
	$cond1 .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN ($PK_TERM_MASTER) ";
}

if(!empty($_POST['PK_CAMPUS_PROGRAM'])){
	$PK_CAMPUS_PROGRAM = implode(",",$_POST['PK_CAMPUS_PROGRAM']);
	$cond1 .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) ";
}

if(!empty($_POST['PK_STUDENT_STATUS'])){
	$PK_STUDENT_STATUS = implode(",",$_POST['PK_STUDENT_STATUS']);
	$cond1 .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($PK_STUDENT_STATUS) ";
}

if(!empty($_POST['PK_STUDENT_GROUP'])){
	$PK_STUDENT_GROUP = implode(",",$_POST['PK_STUDENT_GROUP']);
	$cond1 .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP IN ($PK_STUDENT_GROUP) ";
}
// Ticket # DIAM-659 
$cond = "";
if($_GET['st'] != '' && $_GET['et'] != '') {
	$ST = date("Y-m-d",strtotime($_GET['st']));
	$ET = date("Y-m-d",strtotime($_GET['et']));
	$cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE BETWEEN '$ST' AND '$ET' ";
} else if($_GET['st'] != ''){
	$ST = date("Y-m-d",strtotime($_GET['st']));
	$cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE >= '$ST' ";
} else if($_GET['et'] != ''){
	$ET = date("Y-m-d",strtotime($_GET['et']));
	$cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$ET' ";
}
/*
$res_stud = $db->Execute("select S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER,CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME) AS STUD_NAME, STUDENT_ID  from 
S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL  
WHERE 
S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND 
S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
S_STUDENT_MASTER.ARCHIVED = 0 AND 
S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 
S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 16 AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 $cond GROUP BY S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER ORDER BY CONCAT(trim(S_STUDENT_MASTER.LAST_NAME),', ',trim(S_STUDENT_MASTER.FIRST_NAME)) ")*/


//659 filter
$res_stud = $db->Execute("SELECT S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER,CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME) AS STUD_NAME, STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE,S_COURSE_OFFERING_SCHEDULE_DETAIL,S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 16 AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 $cond1 $cond GROUP BY S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER ORDER BY STUD_NAME");
// die;

while (!$res_stud->EOF) {
	$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];
	
	$res_course_schedule = $db->Execute("SELECT TRANSCRIPT_CODE, SESSION, SESSION_NO, IF(COURSE_TERM.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(COURSE_TERM.BEGIN_DATE, '%Y-%m-%d' )) AS COURSE_TERM_DATE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME) AS STUD_NAME, STUDENT_ID, IF(S_TERM_MASTER1.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER1.BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER, S_STUDENT_COURSE.PK_COURSE_OFFERING, M_CAMPUS_PROGRAM.CODE AS PROGRAM_CODE, COURSE_DESCRIPTION,IF( S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE = '0000-00-00','',DATE_FORMAT( S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE, '%Y-%m-%d' )) AS SCHEDULE_DATE, ATTENDANCE_HOURS, S_STUDENT_SCHEDULE.HOURS, ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_COMMENTS, CAMPUS_CODE, STUDENT_STATUS, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) AS  INSTRUCTOR FROM S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN S_TERM_MASTER AS S_TERM_MASTER1 ON S_TERM_MASTER1.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER AS COURSE_TERM ON S_STUDENT_COURSE.PK_TERM_MASTER = COURSE_TERM.PK_TERM_MASTER, S_COURSE_OFFERING LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION,S_COURSE_OFFERING_SCHEDULE_DETAIL,S_STUDENT_ATTENDANCE LEFT JOIN M_ATTENDANCE_ACTIVITY_TYPE ON M_ATTENDANCE_ACTIVITY_TYPE.PK_ATTENDANCE_ACTIVITY_TYPE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS, S_STUDENT_SCHEDULE WHERE S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 16 $cond GROUP BY S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL");
	
	$i = 1;
	$total = 0;
	while (!$res_course_schedule->EOF) {
		$line++;
		$index = -1;
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUD_NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_ID']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['CAMPUS_CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['TERM_MASTER']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['PROGRAM_CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['STUDENT_STATUS']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['COURSE_TERM_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['TRANSCRIPT_CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['COURSE_DESCRIPTION']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['INSTRUCTOR']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['SCHEDULE_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format(($res_course_schedule->fields['HOURS'] - $res_course_schedule->fields['ATTENDANCE_HOURS']),2));
		
		/* Ticket # 1601 */
		$ATTENDANCE_ACTIVITY_TYPE ='';
		if($res_att_act->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['ATTENDANCE_ACTIVITY_TYPE']);

			$ATTENDANCE_ACTIVITY_TYPE = $res_course_schedule->fields['ATTENDANCE_ACTIVITY_TYPE'];
		}

		$ATTENDANCE_COMMENTS ='';
		if($res_att_act->fields['ENABLE_ATTENDANCE_COMMENTS'] == 1){
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['ATTENDANCE_COMMENTS']);
			$ATTENDANCE_COMMENTS = $res_course_schedule->fields['ATTENDANCE_COMMENTS'];
		}
		/* Ticket # 1601 */
		//Ticket # 659
		$contents[]=array(
			$res_stud->fields['STUD_NAME'],
			$res_stud->fields['STUDENT_ID'],
			$res_course_schedule->fields['CAMPUS_CODE'],
			$res_course_schedule->fields['TERM_MASTER'],
			$res_course_schedule->fields['PROGRAM_CODE'],
			$res_course_schedule->fields['STUDENT_STATUS'],
			$res_course_schedule->fields['COURSE_TERM_DATE'],
			$res_course_schedule->fields['TRANSCRIPT_CODE'],
			$res_course_schedule->fields['COURSE_DESCRIPTION'],
			$res_course_schedule->fields['INSTRUCTOR'],
			$res_course_schedule->fields['SCHEDULE_DATE'],
			number_format(($res_course_schedule->fields['HOURS'] - $res_course_schedule->fields['ATTENDANCE_HOURS']),2),
			$ATTENDANCE_ACTIVITY_TYPE,
			$ATTENDANCE_COMMENTS
		);
		//Ticket # 659
		$res_course_schedule->MoveNext();
	}
	
	$res_stud->MoveNext();
}
/*
$res_stud = $db->Execute("select S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT  from 
S_STUDENT_MASTER, S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE 
WHERE 
S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND 
S_STUDENT_MASTER.ARCHIVED = 0 AND 
S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 11 $cond GROUP BY S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT");

while (!$res_stud->EOF) {
	$PK_STUDENT_ENROLLMENT = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
	
	$res_enroll = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER,EXPECTED_GRAD_DATE,CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER,CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME, CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) AS STUD_NAME, STUDENT_ID, DATE_OF_BIRTH FROM 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
	LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER"); 

	$PK_STUDENT_MASTER 	= $res_enroll->fields['PK_STUDENT_MASTER'];

	$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
	if($EXPECTED_GRAD_DATE != '0000-00-00')
		$EXPECTED_GRAD_DATE = date("Y-m-d",strtotime($EXPECTED_GRAD_DATE));
	else
		$EXPECTED_GRAD_DATE = '';
		
	$res_course_schedule = $db->Execute("select IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%Y-%m-%d'),'') AS SCHEDULE_DATE1, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, IF(END_TIME != '00:00:00', DATE_FORMAT(END_TIME,'%h:%i %p'),'') AS END_TIME, IF(START_TIME != '00:00:00', DATE_FORMAT(START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, TRANSCRIPT_CODE, SCHEDULE_TYPE, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','N') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO,ATTENDANCE_HOURS, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER, ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_COMMENTS      from 

	S_STUDENT_SCHEDULE 
	LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
	LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
	LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
	LEFT JOIN M_ATTENDANCE_ACTIVITY_TYPE ON M_ATTENDANCE_ACTIVITY_TYPE.PK_ATTENDANCE_ACTIVITY_TYPE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS 
	LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_ATTENDANCE.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	WHERE 
	S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
	S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
	S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 11 $cond ORDER BY SCHEDULE_DATE ASC, START_TIME ASC  ");

	$total_attended = 0;
	while (!$res_course_schedule->EOF) {
		$total_attended += $res_course_schedule->fields['ATTENDANCE_HOURS'];
		
		$line++;
		$index = -1;
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['STUD_NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['STUDENT_ID']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['STUDENT_STATUS']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['TERM_MASTER']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($EXPECTED_GRAD_DATE);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['TERM_MASTER']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['TRANSCRIPT_CODE'].' ('. substr($res_course_schedule->fields['SESSION'],0,1).' - '. $res_course_schedule->fields['SESSION_NO'].')');
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['SCHEDULE_DATE1']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format($res_course_schedule->fields['ATTENDANCE_HOURS'],2));
		
		/* Ticket # 1601 */
		/* if($res_att_act->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['ATTENDANCE_ACTIVITY_TYPE']);
		}

		if($res_att_act->fields['ENABLE_ATTENDANCE_COMMENTS'] == 1){
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['ATTENDANCE_COMMENTS']);
		}*/
		/* Ticket # 1601 */
/*
		$res_course_schedule->MoveNext();
	}
		
	$res_stud->MoveNext();
}*/
//Ticket # 659
// $objWriter->save($outputFileName);
// $objPHPExcel->disconnectWorksheets();
// header("location:".$outputFileName);


if(isset($_POST['ajaxpost']) && $_POST['ajaxpost']==1){
	
	header('Content-type: application/json; charset=UTF-8');
	$data_res = [];
	$data_res['path'] = $outputFileName;
	$file_name = explode("/",$outputFileName);
	$data_res['filename'] = $file_name[1];
	$outputFileName = fopen($outputFileName, "w");
	fputcsv($outputFileName, $heading);
	foreach($contents as $content){
		fputcsv($outputFileName, $content);
	}
	fclose($outputFileName);
	echo json_encode($data_res);

}else{

	header('Content-Type: text/csv');
	header('Content-Disposition: attachment;filename=' . $outputFileName);
	$outputFileName = fopen($outputFileName, "w");
	fputcsv($outputFileName, $heading);
	foreach($contents as $content){
		fputcsv($outputFileName, $content);
	}
	fclose($outputFileName);
}
exit;
//Ticket # 659
