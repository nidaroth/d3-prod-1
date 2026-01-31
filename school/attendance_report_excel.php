<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
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
$file_name 		= 'Attendance Report.xlsx';
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

$cell_no = 'H1';
$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Schedule');
$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
$marge_cells = 'H1:M1';
$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);

$cell_no = 'N1';
$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Attendance');
$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
$marge_cells = 'N1:P1';
$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);

$line++;
$index 	= -1;

$heading[] = 'Name';
$width[]   = 20;
$heading[] = 'ID';
$width[]   = 20;
$heading[] = 'Program';
$width[]   = 20;
$heading[] = 'Status';
$width[]   = 20;
$heading[] = 'First Term';
$width[]   = 20;
$heading[] = 'Exp Grad Date';
$width[]   = 20;

$heading[] = 'Course';
$width[]   = 20;
$heading[] = 'Type';
$width[]   = 20;
$heading[] = 'Class Date';
$width[]   = 20;
$heading[] = 'Start Time';
$width[]   = 20;
$heading[] = 'End Time';
$width[]   = 20;
$heading[] = 'Hours';
$width[]   = 20;
$heading[] = 'Complete';
$width[]   = 20;
$heading[] = 'Code';
$width[]   = 20;
$heading[] = 'Hours';
$width[]   = 20;
$heading[] = 'Cumulative';
$width[]   = 20;

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

$heading[] = 'Created By';
$width[]   = 20;
$heading[] = 'Date/Time Stamp';
$width[]   = 20;

$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	$i++;
}	

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
$TIMEZONE = $res->fields['TIMEZONE'];

$objPHPExcel->getActiveSheet()->freezePane('A2');

$PK_STUDENT_ENROLLMENTS = explode(",",$_GET['eid']);
$klm = 0;
/* Ticket # 1144  */
foreach($PK_STUDENT_ENROLLMENTS as $PK_STUDENT_ENROLLMENT) {
	$res = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
	$PK_STUDENT_MASTER_arr[$res->fields['PK_STUDENT_MASTER']] = $res->fields['PK_STUDENT_MASTER'];
}
/* Ticket # 1144  */

foreach($PK_STUDENT_MASTER_arr as $PK_STUDENT_MASTER) { ///* Ticket # 1144  */
	$klm++;
	/* Ticket # 1144  */
	$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
	
	$PK_STUDENT_MASTER  = $res_enroll->fields['PK_STUDENT_MASTER'];
	
	$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 

	$DATE_OF_BIRTH = $res->fields['DATE_OF_BIRTH'];

	if($DATE_OF_BIRTH != '0000-00-00')
		$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
	else
		$DATE_OF_BIRTH = '';
		
	$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

	$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
	if($EXPECTED_GRAD_DATE != '0000-00-00')
		$EXPECTED_GRAD_DATE = date("Y-m-d",strtotime($EXPECTED_GRAD_DATE));
	else
		$EXPECTED_GRAD_DATE = '';
	
	//Ticket # 1144  
	$res_course_schedule = $db->Execute("select IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%Y-%m-%d %a'),'') AS SCHEDULE_DATE1,S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, IF(END_TIME != '00:00:00', DATE_FORMAT(END_TIME,'%h:%i %p'),'') AS END_TIME, IF(START_TIME != '00:00:00', DATE_FORMAT(START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, TRANSCRIPT_CODE, SCHEDULE_TYPE, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','N') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO,ATTENDANCE_HOURS, S_STUDENT_ATTENDANCE.CREATED_ON, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS CREATED_BY, ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_COMMENTS 
	from 
	S_STUDENT_SCHEDULE 
	LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
	LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
	LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
	LEFT JOIN M_ATTENDANCE_ACTIVITY_TYPE ON M_ATTENDANCE_ACTIVITY_TYPE.PK_ATTENDANCE_ACTIVITY_TYPE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS 
	LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
	LEFT JOIN Z_USER ON Z_USER.PK_USER = S_STUDENT_ATTENDANCE.CREATED_BY  
	LEFT JOIN S_EMPLOYEE_MASTER ON Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) 
	WHERE 
	S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
	S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) AND 
	S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ORDER BY SCHEDULE_DATE ASC, START_TIME ASC  ");

	$total_scheduled 			= 0;
	$total_completed_scheduled 	= 0;
	$total_attended 			= 0;
	$cum_total					= 0;
	
	/*if($res_course_schedule->RecordCount() > 0 && $klm > 1)
		$line += 2;*/
	while (!$res_course_schedule->EOF) {

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
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['CODE'].' - '.$res_enroll->fields['DESCRIPTION']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['STUDENT_STATUS']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['TERM_MASTER']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($EXPECTED_GRAD_DATE);
	
		if(($res_course_schedule->fields['COMPLETED'] == 'Y' && $res_course_schedule->fields['ATTENDANCE_CODE'] == 'P') || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2 )
			$total_attended += $res_course_schedule->fields['ATTENDANCE_HOURS'];
		
		$total_scheduled += $res_course_schedule->fields['HOURS'];
		if($res_course_schedule->fields['COMPLETED'] == 'Y') {
			$total_completed_scheduled 	+= $res_course_schedule->fields['HOURS'];
			$cum_total					+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
		}
		
		if($res_course_schedule->fields['COMPLETED'] == 'N') {
			//$ATTENDANCE_CODE = 'P';
			$ATTENDANCE_CODE  = '';
			$ATTENDANCE_HOURS = 0; 
		} else {
			$ATTENDANCE_CODE  = $res_course_schedule->fields['ATTENDANCE_CODE'];
			$ATTENDANCE_HOURS = $res_course_schedule->fields['ATTENDANCE_HOURS'];
		}
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['TRANSCRIPT_CODE'].' ('. substr($res_course_schedule->fields['SESSION'],0,1).' - '. $res_course_schedule->fields['SESSION_NO'].')');
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['SCHEDULE_TYPE']);
		
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
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($cum_total,2));
		
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
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['CREATED_BY']);
		
		if($res_course_schedule->fields['CREATED_ON'] != '0000-00-00 00:00:00' && $res_course_schedule->fields['CREATED_ON'] != '') {
			$date = convert_to_user_date($res_course_schedule->fields['CREATED_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($date);
		}
		
		$res_course_schedule->MoveNext();
	}
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);