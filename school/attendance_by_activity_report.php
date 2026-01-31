<?php session_start();
$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');
require_once("function_transcript_header.php");

require_once("pdf_custom_header.php"); //Ticket # 1588
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		/* Ticket # 1588 */
		if($_GET['id'] != ''){
				if($this->PageNo() == 1) {
					$CONTENT = pdf_custom_header($_GET['id'], $_GET['eid'], 1);
					$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
					$this->SetMargins('', 45, '');
				} else {
					$this->SetFont('helvetica', 'I', 15);
					$this->SetY(8);
					$this->SetX(10);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
					$this->SetMargins('', 25, '');
				}
			
		} else {
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			
			if($res->fields['PDF_LOGO'] != '') {
				$ext = explode(".",$res->fields['PDF_LOGO']);
				$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
			}
			
			$this->SetFont('helvetica', '', 15);
			$this->SetY(6);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetFont('helvetica', '', 8);
			$this->SetY(13);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetY(17);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['CITY'].', '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetY(21);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		}
		/* Ticket # 1588 */
		
		$this->SetFont('helvetica', 'I', 16);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(135);
		$this->Cell(55, 8, "Attendance By Activity Type", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(130, 13, 205, 13, $style);

    }
    public function Footer() {
		global $db;
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}
		
		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
			
		$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);

if($_GET['id'] == '') {
	$_GET['id'] = $_SESSION['PK_STUDENT_MASTER'];
	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
	$_GET['eid'] = $res->fields['PK_STUDENT_ENROLLMENT'];
}

$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 
if($res->RecordCount() == 0){
	header("location:manage_student?t=".$_GET['t']);
	exit;
}

/* Ticket #1145 */
$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

$excluded_att_code  = "";
$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
$excluded_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
/* Ticket #1145 */

$DATE_OF_BIRTH = $res->fields['DATE_OF_BIRTH'];

if($DATE_OF_BIRTH != '0000-00-00')
	$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
else
	$DATE_OF_BIRTH = '';
	
$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

$PK_STUDENT_ENROLLMENTS = explode(",",$_GET['eid']);
foreach($PK_STUDENT_ENROLLMENTS as $PK_STUDENT_ENROLLMENT) {
	$pdf->AddPage();
	
	$pdf->STUD_NAME = $res->fields['LAST_NAME'].", ".$res->fields['FIRST_NAME']." ".$res->fields['MIDDLE_NAME'];
	
	$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,PROGRAM_TRANSCRIPT_CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS EMP_NAME, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
	$PK_CAMPUS_PROGRAM = $res_enroll->fields['PK_CAMPUS_PROGRAM'];

	$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
	if($EXPECTED_GRAD_DATE != '0000-00-00')
		$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
	else
		$EXPECTED_GRAD_DATE = '';
		
	$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");	

	$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td width="100%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ><b>'.$res->fields['LAST_NAME'].', '.$res->fields['FIRST_NAME'].' '.$res->fields['MIDDLE_NAME'].'</b></td>
				</tr>
				<tr>
					<td style="border-left:0.5px solid #000;border-top:0.5px solid #000" width="100%" width="34%" >
						'.transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
					</td>
					<td style="border-left:0.5px solid #000;border-top:0.5px solid #000" width="100%" width="34%" >
						'.transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
					</td>
					<td style="border-left:0.5px solid #000;border-right:0.5px solid #000;border-top:0.5px solid #000" width="100%" width="32%" >
						'.transcript_header($res_report_header->fields['BOX_7'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
					</td>
				</tr>
				<tr>
					<td width="34%" style="border-left:0.5px solid #000;" >
						'.transcript_header($res_report_header->fields['BOX_2'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
					</td>
					<td  width="34%" style="border-left:0.5px solid #000;" >
						'.transcript_header($res_report_header->fields['BOX_5'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
					</td>
					<td  width="32%" style="border-left:0.5px solid #000;border-right:0.5px solid #000;" >
						'.transcript_header($res_report_header->fields['BOX_8'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
					</td>
				</tr>
				<tr>
					<td width="34%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000" >
						'.transcript_header($res_report_header->fields['BOX_3'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
					</td>
					<td  width="34%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000" >
						'.transcript_header($res_report_header->fields['BOX_6'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
					</td>
					<td  width="32%" style="border-left:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000" width="100%" width="32%"  >
						'.transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
					</td>
				</tr>
			</table>';
		
			$TOTAL_HOURS_SCHEDULED 	= 0;
			$TOTAL_HOURS_COMPLETED 	= 0;
			$TOTAL_HOURS_REMAINING 	= 0;
			
			$res_course_schedule1 = $db->Execute("select S_STUDENT_COURSE.PK_TERM_MASTER 
			from 
			S_STUDENT_MASTER, 
			S_STUDENT_SCHEDULE LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE 
			LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
			LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER 
			LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
			LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
			LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
			LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
			LEFT JOIN M_ATTENDANCE_ACTIVITY_TYPE ON M_ATTENDANCE_ACTIVITY_TYPE.PK_ATTENDANCE_ACTIVITY_TYPE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS 
			LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE 
			LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL 
			WHERE 
			S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$_GET[id]' AND 
			S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
			S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
			GROUP BY S_STUDENT_COURSE.PK_TERM_MASTER ORDER BY S_TERM_MASTER.BEGIN_DATE ASC ");
			
			while (!$res_course_schedule1->EOF) {
				$PK_TERM_MASTER = $res_course_schedule1->fields['PK_TERM_MASTER'];
				
				/* Ticket #1145 */
				$res_course_schedule = $db->Execute("select M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, PK_STUDENT_ATTENDANCE, S_STUDENT_ATTENDANCE.PK_STUDENT_ATTENDANCE, S_COURSE_OFFERING.PK_TERM_MASTER, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%m/%d/%Y'),'') AS BEGIN_DATE, S_COURSE_OFFERING.PK_COURSE_OFFERING, COURSE_CODE,   S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL, ATTENDANCE_ACTIVITY_TYPE, SUM(S_STUDENT_SCHEDULE.HOURS) as SCHEDULED_HOURS, IFNULL(S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS , 0) as PK_ATTENDANCE_ACTIVITY_TYPESS 
				from 
				S_STUDENT_MASTER, 
				S_STUDENT_SCHEDULE LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE 
				LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
				LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER 
				LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
				LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
				LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
				LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
				LEFT JOIN M_ATTENDANCE_ACTIVITY_TYPE ON M_ATTENDANCE_ACTIVITY_TYPE.PK_ATTENDANCE_ACTIVITY_TYPE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS 
				LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE 
				LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL 
				WHERE 
				S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$_GET[id]' AND 
				S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
				S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
				S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' 
				GROUP BY S_STUDENT_COURSE.PK_COURSE_OFFERING, IFNULL(S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS , 0) ORDER BY S_STUDENT_SCHEDULE.SCHEDULE_DATE ASC, S_STUDENT_SCHEDULE.START_TIME ASC");

				$TERM_TOTAL_HOURS_SCHEDULED 	= 0;
				$TERM_TOTAL_HOURS_COMPLETED 	= 0;
				$TERM_TOTAL_HOURS_REMAINING 	= 0;
				
				if($res_course_schedule->RecordCount() > 0){
					$txt .= '<br /><br />
						<table border="0" cellspacing="0" cellpadding="2" width="100%">
							<thead>
								<tr>
									<td width="10%" style="border-bottom:1px solid #000;">
										<b>Term</b>
									</td>
									<td width="22%" style="border-bottom:1px solid #000;">
										<b>Course</b>
									</td>
									<td width="12%" style="border-bottom:1px solid #000;">
										<b>Activity Type</b>
									</td>
									
									<td width="14%" style="border-bottom:1px solid #000;" align="right" >
										<b>Hours Scheduled</b>
									</td>
									<td width="14%" style="border-bottom:1px solid #000;" align="right" >
										<b>Hours Completed</b>
									</td>
									<td width="14%" style="border-bottom:1px solid #000;"align="right" >
										<b>Hours Remaining</b>
									</td>
									<td width="17%" style="border-bottom:1px solid #000;" align="right" >
										<b>Percentage Completed</b>
									</td>
								</tr>
							</thead>
							<tbody>';
					
					while (!$res_course_schedule->EOF) {
						$PK_COURSE_OFFERING 			= $res_course_schedule->fields['PK_COURSE_OFFERING'];
						$PK_ATTENDANCE_ACTIVITY_TYPESS 	= $res_course_schedule->fields['PK_ATTENDANCE_ACTIVITY_TYPESS'];
						
						/* Ticket #1145 */
						$res_att_hour = $db->Execute("select SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS 
						from 
						S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_MASTER, S_STUDENT_COURSE
						WHERE 
						S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
						S_STUDENT_ATTENDANCE.COMPLETED = 1 AND 
						S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
						S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE AND 
						S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$_GET[id]' AND 
						S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
						S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' AND 
						S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
						S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS = '$PK_ATTENDANCE_ACTIVITY_TYPESS' AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");
						
						if($excluded_att_code != '')
							$excluded_att_code .= ',7';
						else
							$excluded_att_code = '7';
						
						if($excluded_att_code != ''){
							$res_exc_hour = $db->Execute("select SUM(S_STUDENT_SCHEDULE.HOURS) as EXC_HOURS 
							from 
							S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_MASTER, S_STUDENT_COURSE
							WHERE 
							S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
							S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
							S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE AND 
							S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$_GET[id]' AND 
							S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
							S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' AND 
							S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
							S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS = '$PK_ATTENDANCE_ACTIVITY_TYPESS' AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($excluded_att_code) ");
							
						}

						$COURSE = $res_course_schedule->fields['COURSE_CODE'].' ('. $res_course_schedule->fields['SESSION'].' - '. $res_course_schedule->fields['SESSION_NO'].')';
						
						$SCHEDULED_HOURS = $res_course_schedule->fields['SCHEDULED_HOURS'] - $res_exc_hour->fields['EXC_HOURS'];
						$TOTAL_HOURS_SCHEDULED 	+= $SCHEDULED_HOURS;
						$TOTAL_HOURS_COMPLETED 	+= $res_att_hour->fields['ATTENDANCE_HOURS'];
						$TOTAL_HOURS_REMAINING 	+= $SCHEDULED_HOURS - $res_att_hour->fields['ATTENDANCE_HOURS'];
						
						$TERM_TOTAL_HOURS_SCHEDULED 	+= $SCHEDULED_HOURS;
						$TERM_TOTAL_HOURS_COMPLETED 	+= $res_att_hour->fields['ATTENDANCE_HOURS'];
						$TERM_TOTAL_HOURS_REMAINING 	+= $SCHEDULED_HOURS - $res_att_hour->fields['ATTENDANCE_HOURS'];
						
						$per 					 = 0;
						if($res_att_hour->fields['ATTENDANCE_HOURS'] > 0 && $SCHEDULED_HOURS > 0)
							$per = $res_att_hour->fields['ATTENDANCE_HOURS'] / $SCHEDULED_HOURS * 100;
							
						$txt .= '<tr nobr="true" >
									<td width="10%" >'.$res_course_schedule->fields['BEGIN_DATE'].'</td>
									<td width="22%" >'.$COURSE.'</td>
									<td width="12%" >'.$res_course_schedule->fields['ATTENDANCE_ACTIVITY_TYPE'].'</td>
									<td width="14%" align="right" >'.number_format_value_checker($SCHEDULED_HOURS,2).'</td>
									<td width="14%" align="right" >'.number_format_value_checker($res_att_hour->fields['ATTENDANCE_HOURS'],2).'</td>
									<td width="14%" align="right" >'.number_format_value_checker(($SCHEDULED_HOURS - $res_att_hour->fields['ATTENDANCE_HOURS']),2).'</td>
									<td width="17%" align="right" >'.number_format_value_checker($per,2).'</td>
								</tr>';
						$res_course_schedule->MoveNext();
					}
					
					if($TERM_TOTAL_HOURS_SCHEDULED > 0 && $TERM_TOTAL_HOURS_COMPLETED > 0)
						$per = $TERM_TOTAL_HOURS_COMPLETED / $TERM_TOTAL_HOURS_SCHEDULED * 100;
					
					$txt .= '<tr nobr="true" >
										<td width="10%" style="border-top:1px solid #000;" > </td>
										<td width="22%" style="border-top:1px solid #000;" > </td>
										<td width="12%" style="border-top:1px solid #000;" ><b>Term Total:</b></td>
										<td width="14%" style="border-top:1px solid #000;" align="right" >'.number_format_value_checker($TERM_TOTAL_HOURS_SCHEDULED,2).'</td>
										<td width="14%" style="border-top:1px solid #000;" align="right" >'.number_format_value_checker($TERM_TOTAL_HOURS_COMPLETED,2).'</td>
										<td width="14%" style="border-top:1px solid #000;" align="right" >'.number_format_value_checker(($TERM_TOTAL_HOURS_REMAINING),2).'</td>
										<td width="17%" style="border-top:1px solid #000;" align="right" >'.number_format_value_checker($per,2).'</td>
									</tr>
								</tbody>
							</table>';
				}	
				$res_course_schedule1->MoveNext();
			}
			
			if($TOTAL_HOURS_SCHEDULED > 0 && $TOTAL_HOURS_COMPLETED > 0)
				$per = $TOTAL_HOURS_COMPLETED / $TOTAL_HOURS_SCHEDULED * 100;
				
			$txt .= '<br /><br /><table border="0" cellspacing="0" cellpadding="2" width="100%">
						<tr nobr="true" >
								<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" > </td>
								<td width="22%" style="border-top:1px solid #000;border-bottom:1px solid #000;" > </td>
								<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Grand Total:</b></td>
								<td width="14%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >'.number_format_value_checker($TOTAL_HOURS_SCHEDULED,2).'</td>
								<td width="14%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >'.number_format_value_checker($TOTAL_HOURS_COMPLETED,2).'</td>
								<td width="14%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >'.number_format_value_checker(($TOTAL_HOURS_REMAINING),2).'</td>
								<td width="17%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >'.number_format_value_checker($per,2).'</td>
							</tr>
						</tbody>
					</table>';
			
		//echo $txt;exit;
	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Attendance By Activity Type.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	