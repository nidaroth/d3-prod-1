<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_attendance_analysis_report.php");
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

$cond = "";
if($_GET['dt'] != ''){
	$ET = date("Y-m-d",strtotime($_GET['dt']));
	$cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$ET' ";
	$cond2 = " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$ET' ";
}

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
$file_name 		= 'Attendance Absences By Course.xlsx';
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line   = 1;
$index 	= -1;

$heading[] = 'Program';
$width[]   = 20;
$heading[] = 'Name';
$width[]   = 20;
$heading[] = 'Student ID';
$width[]   = 20;
$heading[] = 'Campus';
$width[]   = 20;
$heading[] = 'First Term';
$width[]   = 20;
$heading[] = 'Home Phone';
$width[]   = 20;
$heading[] = 'Mobile Phone';
$width[]   = 20;
$heading[] = 'Other Phone';
$width[]   = 20;
$heading[] = 'Email';
$width[]   = 20;
$heading[] = 'Course Term Start';
$width[]   = 20;
$heading[] = 'Instructor';
$width[]   = 20;
$heading[] = 'Course';
$width[]   = 20;
$heading[] = 'Session';
$width[]   = 20;
$heading[] = 'Session Number';
$width[]   = 20;
$heading[] = 'Last Day Attended';
$width[]   = 20;
$heading[] = 'Consecutive Days Absent';
$width[]   = 20;
$heading[] = 'Absent Count';
$width[]   = 20;
$heading[] = 'Present Count';
$width[]   = 20;
$heading[] = 'Scheduled Days';
$width[]   = 20;
$heading[] = 'Scheduled Hours';
$width[]   = 20;
$heading[] = 'Attended Hours';
$width[]   = 20;
$heading[] = 'Attendance Percentage';
$width[]   = 20;

$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
}	

$objPHPExcel->getActiveSheet()->freezePane('A2');

/* Ticket #1145 */
$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

$excluded_att_code  = "";
$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
$excluded_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
/* Ticket #1145 */

/* Ticket # 1194  */
$course_term_cond = "";
if($_GET['t_id'] != '') {
	$course_term_cond = " AND S_STUDENT_COURSE.PK_TERM_MASTER IN ($_GET[t_id]) ";
}

/* Ticket # 1341 */
if($_GET['co'] != '') {
	$course_term_cond = " AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN ($_GET[co]) ";
}
/* Ticket # 1341 */

$res_prog = $db->Execute("select M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM, PROGRAM_TRANSCRIPT_CODE  from 
S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL, M_CAMPUS_PROGRAM, S_STUDENT_COURSE   
WHERE 
S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT AND 
S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 
S_STUDENT_MASTER.ARCHIVED = 0 AND 
S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND 
M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM AND 
S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND 
S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code) $cond $course_term_cond GROUP BY M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM ORDER BY PROGRAM_TRANSCRIPT_CODE ASC ");

/* Ticket # 1194  */

while (!$res_prog->EOF) {
	$PK_CAMPUS_PROGRAM = $res_prog->fields['PK_CAMPUS_PROGRAM'];
	
	////////////////////////////
	/* Ticket # 1194  */
	$res_course_schedule = $db->Execute("select TRANSCRIPT_CODE, SESSION, SESSION_NO, IF(COURSE_TERM.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(COURSE_TERM.BEGIN_DATE, '%Y-%m-%d' )) AS COURSE_TERM_DATE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME) AS STUD_NAME, STUDENT_ID, IF(S_TERM_MASTER1.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER1.BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER, S_STUDENT_COURSE.PK_COURSE_OFFERING, CAMPUS_CODE, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) as INSTRUCTOR_NAME
	from 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	LEFT JOIN S_TERM_MASTER AS S_TERM_MASTER1 ON S_TERM_MASTER1.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	, S_STUDENT_COURSE
	LEFT JOIN S_TERM_MASTER AS COURSE_TERM ON S_STUDENT_COURSE.PK_TERM_MASTER = COURSE_TERM.PK_TERM_MASTER 
	, S_COURSE_OFFERING 
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR  
	,S_COURSE_OFFERING_SCHEDULE_DETAIL, S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE 
	
	WHERE 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
	S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING AND 
	S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 
	S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND 
	PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND 
	S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1  $cond $course_term_cond  
	GROUP BY S_STUDENT_COURSE.PK_STUDENT_COURSE ORDER BY CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) ASC, COURSE_TERM.BEGIN_DATE ASC, TRANSCRIPT_CODE ASC, SESSION ASC, SESSION_NO ASC");
	//AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code)

	while (!$res_course_schedule->EOF) {
		$PK_STUDENT_MASTER 		= $res_course_schedule->fields['PK_STUDENT_MASTER'];
		$PK_STUDENT_ENROLLMENT 	= $res_course_schedule->fields['PK_STUDENT_ENROLLMENT'];
		$PK_COURSE_OFFERING		= $res_course_schedule->fields['PK_COURSE_OFFERING'];
		
		$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
		
		$res_last = $db->Execute("SELECT IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE = '0000-00-00','', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE, '%Y-%m-%d' )) AS SCHEDULE_DATE 
		FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE 
		WHERE 
		S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
		PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
		S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
		S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE AND 
		S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) $cond ORDER BY S_STUDENT_SCHEDULE.SCHEDULE_DATE DESC "); 
		
		/* Ticket #1145 */
		$res_att_hour = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDED_HOUR 
		FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE 
		WHERE 
		S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
		PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
		S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
		S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE AND 
		S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.COMPLETED = 1 AND 
		S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) $cond  "); 
		
		/*$CONSECUTIVE_DAYS_ABSENT = 0;
		$res_abs = $db->Execute("SELECT S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE 
		FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL 
		WHERE 
		S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
		PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
		S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
		S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 
		S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code) $cond ORDER BY S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE DESC "); 
		if($res_abs->RecordCount() > 0)
			$CONSECUTIVE_DAYS_ABSENT = 1;
				
		while (!$res_abs->EOF) {
			$LEAVE_DATE = date('Y-m-d',(strtotime( '-1 day' , strtotime($res_abs->fields['SCHEDULE_DATE'])))); 

			$res_abs1 = $db->Execute("SELECT S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE 
			FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL 
			WHERE 
			S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
			PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
			S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
			S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 
			S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code) AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE = '$LEAVE_DATE' "); 
		
			if($res_abs1->RecordCount() > 0)
				$CONSECUTIVE_DAYS_ABSENT++;
			else {
				//if($CONSECUTIVE_DAYS_ABSENT > 0) {
					//$CONSECUTIVE_DAYS_ABSENT++;
					break;
				//}
			}
			
			$res_abs->MoveNext();
		}*/
		
		$CONSECUTIVE_DAYS_ABSENT 	= 0;
			$absent_att_code_arr		= array();
			$absent_att_code_arr 		= explode(",",$absent_att_code);

			$res_abs = $db->Execute("SELECT S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE, S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
			FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL 
			WHERE 
			S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
			S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
			S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 
			PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
			S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_ATTENDANCE.COMPLETED = 1 $cond ORDER BY S_STUDENT_SCHEDULE.SCHEDULE_DATE DESC "); 

			if($res_abs->RecordCount() > 0) {
				$SCHEDULE_DATE		 = $res_abs->fields['SCHEDULE_DATE'];
				$PK_STUDENT_SCHEDULE = $res_abs->fields['PK_STUDENT_SCHEDULE'];
				
				$res_abs1 = $db->Execute("SELECT S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE 
				FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL 
				WHERE 
				S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
				PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
				S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
				S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 
				S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$SCHEDULE_DATE' ORDER BY S_STUDENT_SCHEDULE.SCHEDULE_DATE DESC  "); 

				while (!$res_abs1->EOF) {
					$is_absent = 0;
					foreach($absent_att_code_arr as $exc_att_code) {
						if($exc_att_code == $res_abs1->fields['PK_ATTENDANCE_CODE']) {
							$is_absent = 1;
							//break;
						}
					}
				
					if($is_absent == 0)
						break;
					else
						$CONSECUTIVE_DAYS_ABSENT++;
					
					$res_abs1->MoveNext();
				}
			}
			
			$res_abs = $db->Execute("SELECT S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE 
			FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL 
			WHERE 
			S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
			PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
			S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
			S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 
			S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code) $cond ORDER BY S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE DESC "); 
		
		/* Ticket #1145 */
		$res_sch = $db->Execute("SELECT COUNT(S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE) as NO, SUM(HOURS) as SCHEDULED_HOUR 
		FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE 
		WHERE 
		S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
		PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
		S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
		S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE AND 
		S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond  "); 

		if($excluded_att_code != '')
			$excluded_att_code .= ',7';
		else
			$excluded_att_code = '7';
		if($excluded_att_code != ''){
			$res_exc_hour = $db->Execute("select COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as EXC_COUNT, SUM(HOURS) as  EXC_HOUR  
			from 
			S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE
			WHERE 
			S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
			S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE AND 
			S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
			S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
			S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($excluded_att_code) $cond ");
		}
		/* Ticket #1145 */
		
		$HOME_PHONE 	= preg_replace( '/[^0-9]/', '',$res_address->fields['HOME_PHONE']);
		$OTHER_PHONE 	= preg_replace( '/[^0-9]/', '',$res_address->fields['OTHER_PHONE']);
		$CELL_PHONE 	= preg_replace( '/[^0-9]/', '',$res_address->fields['CELL_PHONE']);


		if($HOME_PHONE != '')
			$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
			
		if($OTHER_PHONE != '')
			$OTHER_PHONE = '('.$OTHER_PHONE[0].$OTHER_PHONE[1].$OTHER_PHONE[2].') '.$OTHER_PHONE[3].$OTHER_PHONE[4].$OTHER_PHONE[5].'-'.$OTHER_PHONE[6].$OTHER_PHONE[7].$OTHER_PHONE[8].$OTHER_PHONE[9];
			
		if($CELL_PHONE != '')
			$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
			
		$line++;
		$index = -1;
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_prog->fields['PROGRAM_TRANSCRIPT_CODE']); //Ticket # 1194  
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['STUD_NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['STUDENT_ID']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['CAMPUS_CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['TERM_MASTER']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($HOME_PHONE);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CELL_PHONE);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($OTHER_PHONE);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_address->fields['EMAIL']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['COURSE_TERM_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['INSTRUCTOR_NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['TRANSCRIPT_CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['SESSION']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['SESSION_NO']);
	
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_last->fields['SCHEDULE_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CONSECUTIVE_DAYS_ABSENT);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_abs->RecordCount());
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_last->RecordCount());
		
		$SCH_COUNT 		= $res_sch->fields['NO'] - $res_exc_hour->fields['EXC_COUNT'];
		$SCHEDULED_HOUR = $res_sch->fields['SCHEDULED_HOUR'] - $res_exc_hour->fields['EXC_HOUR'];
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SCH_COUNT);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SCHEDULED_HOUR);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att_hour->fields['ATTENDED_HOUR']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(($res_att_hour->fields['ATTENDED_HOUR'] / $SCHEDULED_HOUR));
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

		$res_course_schedule->MoveNext();
	}
	///////////////////////////////
	$res_prog->MoveNext();
}


$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);