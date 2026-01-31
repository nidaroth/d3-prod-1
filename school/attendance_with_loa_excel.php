<?php /*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
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

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
$file_name 		= 'Attendance Review with LOA.xlsx';
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$style = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	)
);

$line 	= 1;
$cell_no = 'G1';
$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Schedule');
$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
$marge_cells = 'G1:K1';
$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);

$cell_no = 'L1';
$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Attendance');
$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
$marge_cells = 'L1:M1';
$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);

$cell_no = 'N1';
$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Cumulative');
$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
$marge_cells = 'N1:P1';
$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);


$line++;
$index 	= -1;
$heading[] = 'Student name';
$width[]   = 30;
$heading[] = 'Student ID';
$width[]   = 15;
$heading[] = 'Status';
$width[]   = 15;
$heading[] = 'First Term';
$width[]   = 15;
$heading[] = 'Exp Grad Date';
$width[]   = 15;

$heading[] = 'Course';
$width[]   = 20;
$heading[] = 'Class Date';
$width[]   = 15;
$heading[] = 'Start Time';
$width[]   = 15;
$heading[] = 'End Time';
$width[]   = 15;
$heading[] = 'Hours';
$width[]   = 15;
$heading[] = 'Complete';
$width[]   = 15;
$heading[] = 'Code';
$width[]   = 15;
$heading[] = 'Hours';
$width[]   = 15;
$heading[] = 'Scheduled';
$width[]   = 15;
$heading[] = 'Attended';
$width[]   = 15;
$heading[] = 'Percentage';
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

/* Ticket #1145 */
$res_present_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
while (!$res_present_att_code->EOF) {
	$present_att_code_arr[] = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_present_att_code->MoveNext();
}

$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

$res_present_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
while (!$res_present_att_code->EOF) {
	$excluded_att_code_arr[] = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_present_att_code->MoveNext();
}
/* Ticket #1145 */

$PK_STUDENT_ENROLLMENTS = explode(",",$_GET['eid']);
foreach($PK_STUDENT_ENROLLMENTS as $PK_STUDENT_ENROLLMENT) {
	$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
	$PK_STUDENT_MASTER = $res_enroll->fields['PK_STUDENT_MASTER'];
	
	$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 
	
	$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
	if($EXPECTED_GRAD_DATE != '0000-00-00')
		$EXPECTED_GRAD_DATE = date("Y-m-d",strtotime($EXPECTED_GRAD_DATE));
	else
		$EXPECTED_GRAD_DATE = '';
	
	$res_course_schedule = $db->Execute("select IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%Y-%m-%d'),'') AS SCHEDULE_DATE1,S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, IF(END_TIME != '00:00:00', DATE_FORMAT(END_TIME,'%h:%i %p'),'') AS END_TIME, IF(START_TIME != '00:00:00', DATE_FORMAT(START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, TRANSCRIPT_CODE, SCHEDULE_TYPE, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','N') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO,ATTENDANCE_HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_COMMENTS   from 

	S_STUDENT_SCHEDULE 
	LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
	LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
	LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
	LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
	LEFT JOIN M_ATTENDANCE_ACTIVITY_TYPE ON M_ATTENDANCE_ACTIVITY_TYPE.PK_ATTENDANCE_ACTIVITY_TYPE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS 

	WHERE 
	S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
	S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
	S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ORDER BY SCHEDULE_DATE ASC, START_TIME ASC  ");

	$total_scheduled 			= 0;
	$total_completed_scheduled 	= 0;
	$total_attended 			= 0;
	$cum_total_scheduled		= 0;
	$cum_total_attended			= 0;
	while (!$res_course_schedule->EOF) {
	
		$present_flag = 0;
		foreach($present_att_code_arr as $present_att_code) {
			if($present_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
				$present_flag = 1;
				break;
			}
		}
		
		$exc_flag = 0;
		foreach($excluded_att_code_arr as $excluded_att) {
			if($excluded_att == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
				$exc_flag = 1;
				break;
			}
		}

		if(($res_course_schedule->fields['COMPLETED'] == 'Y' && $present_flag == 1) || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2 )
			$total_attended += $res_course_schedule->fields['ATTENDANCE_HOURS'];
		
		if($exc_flag == 0)
			$total_scheduled += $res_course_schedule->fields['HOURS'];
			
		if($res_course_schedule->fields['COMPLETED'] == 'Y') {
			$total_completed_scheduled 	+= $res_course_schedule->fields['HOURS'];
			
			if($present_flag == 1)
				$cum_total_attended	+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
		}
		
		if($res_course_schedule->fields['COMPLETED'] == 'N') {
			//$ATTENDANCE_CODE = 'P';
			$ATTENDANCE_CODE  = '';
			$ATTENDANCE_HOURS = 0; 
		} else {
			$ATTENDANCE_CODE 		= $res_course_schedule->fields['ATTENDANCE_CODE'];
			
			if($present_flag == 1)
				$ATTENDANCE_HOURS = $res_course_schedule->fields['ATTENDANCE_HOURS'];
			else
				$ATTENDANCE_HOURS = 0;
			
			if($exc_flag == 0)
				$cum_total_scheduled += $res_course_schedule->fields['HOURS'];
		}
		$per = 0;
		if($cum_total_scheduled > 0)
			$per = $cum_total_attended / $cum_total_scheduled;
			
		$line++;
		$index = -1;
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FIRST_NAME'].' '.$res->fields['MIDDLE_NAME'].' '.$res->fields['LAST_NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
		
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
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['TRANSCRIPT_CODE'].' ('. substr($res_course_schedule->fields['SESSION'],0,1).' - '. $res_course_schedule->fields['SESSION_NO'].')');
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['SCHEDULE_DATE1']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['START_TIME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['END_TIME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_course_schedule->fields['HOURS'],2));
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['COMPLETED']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ATTENDANCE_CODE);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($ATTENDANCE_HOURS,2));
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($cum_total_scheduled,2));
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($cum_total_attended,2));
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($per);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
		
		/* Ticket # 1601 */
		if($res_att_act->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['ATTENDANCE_ACTIVITY_TYPE']);
		}

		if($res_att_act->fields['ENABLE_ATTENDANCE_COMMENTS'] == 1){
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['ATTENDANCE_COMMENTS']);
		}
		/* Ticket # 1601 */
		
		$res_course_schedule->MoveNext();
	}
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);