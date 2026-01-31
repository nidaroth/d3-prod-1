<?php /*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');
require_once("check_access.php");
require_once("function_transcript_header.php");

if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}

$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";

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
			if($_SESSION['temp_id'] != $this->PK_STUDENT_ENROLLMENT){
				$_SESSION['temp_id'] = $this->PK_STUDENT_ENROLLMENT;
				
				$CONTENT = pdf_custom_header($this->PK_STUDENT_MASTER, $this->PK_STUDENT_ENROLLMENT, 1);
				$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
				$this->SetMargins('', 45, '');
				
			} else {
				$this->SetFont('helvetica', 'I', 18);
				$this->SetY(10);
				$this->SetTextColor(000, 000, 000);
				$this->SetX(10);
				$this->Cell(55, 8, $this->STUD_NAME, 0, false, 'L', 0, '', 0, false, 'M', 'L');
			}
		}
		/* Ticket # 1588 */
		
		$this->SetFont('helvetica', 'I', 17);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(127);
		$this->Cell(55, 8, "Attendance Review with LOA", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(120, 13, 205, 13, $style);
		
    }
    public function Footer() {
		global $db; 
		
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
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
		
		/*$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(8, 279, 202, 279, $style);*/
    }
}

$_SESSION['temp_id'] = '';
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 19);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);

/* Ticket #1145 */
$res_present_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
while (!$res_present_att_code->EOF) {
	$present_att_code_arr[] = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_present_att_code->MoveNext();
}

$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

$excluded_att_code_arr = array();
$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
while (!$res_exc_att_code->EOF) {
	$excluded_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_exc_att_code->MoveNext();
}

/* Ticket #1145 */

$PK_STUDENT_ENROLLMENTS = explode(",",$_GET['eid']);
foreach($PK_STUDENT_ENROLLMENTS as $PK_STUDENT_ENROLLMENT) {
	
	$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,PROGRAM_TRANSCRIPT_CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
	$PK_STUDENT_MASTER = $res_enroll->fields['PK_STUDENT_MASTER'];
	
	$PK_CAMPUS_PROGRAM = $res_enroll->fields['PK_CAMPUS_PROGRAM'];
	$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 
	$pdf->STUD_NAME 			= $res->fields['LAST_NAME'].", ".$res->fields['FIRST_NAME']." ".$res->fields['MIDDLE_NAME'];
	$pdf->PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT;
	$pdf->PK_STUDENT_MASTER 	= $PK_STUDENT_MASTER;
	$pdf->startPageGroup();
	$pdf->AddPage();
	
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

	$txt = '<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="width:75%" >
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
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
						</table>
					</td>
					<td style="width:3%" ></td>
					<td style="width:22%" >
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="100%" align="center" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ><b>LOA</b></td>
							</tr>
							<tr>
								<td width="50%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" align="center"><b>Begin Date</b></td>
								<td width="50%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" align="center"><b>End Date</b></td>
							</tr>';
							
							$res_loa = $db->Execute("select IF(S_STUDENT_LOA.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_LOA.BEGIN_DATE, '%Y-%m-%d' )) AS LOA_BEGIN_DATE ,IF(S_STUDENT_LOA.END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_LOA.END_DATE, '%Y-%m-%d' )) AS LOA_END_DATE, DATEDIFF(S_STUDENT_LOA.END_DATE, S_STUDENT_LOA.BEGIN_DATE) AS NO_OF_DAYS FROM S_STUDENT_LOA  WHERE S_STUDENT_LOA.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_LOA.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ORDER BY S_STUDENT_LOA.BEGIN_DATE ");
							while (!$res_loa->EOF) {
								$txt .= '<tr>
											<td width="50%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" align="center">'.$res_loa->fields['LOA_BEGIN_DATE'].'</td>
											<td width="50%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" align="center">'.$res_loa->fields['LOA_END_DATE'].'</td>
										</tr>';
								$res_loa->MoveNext();
							}
							
				$txt .= '</table>
					</td>
				</tr>
			</table>
			<br /><br />
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<thead>
					<tr>
						<td width="18%">
						</td>
						<td width="43%" align="center" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							<b>Schedule</b>
						</td>
						<td width="14%" align="center" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							<b>Attendance</b>
						</td>
						<td width="25%" align="center" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;">
							<b>Cumulative</b>
						</td>
					</tr>
					<tr>
						<td width="18%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Course</b>
						</td>
						<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Class Date</b>
						</td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Start Time</b>
						</td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>End Time</b>
						</td>
						<td width="9%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Hours</b>
						</td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Complete</b>
						</td>
						<td width="5%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Code</b>
						</td>
						<td width="9%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Hours</b>
						</td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Scheduled</b>
						</td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Attended</b>
						</td>
						<td width="9%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >
							<b>Percentage</b>
						</td>
					</tr>
				</thead>
				<tbody>';
				
			$res_tc = $db->Execute("SELECT S_COURSE.COURSE_CODE FROM S_STUDENT_CREDIT_TRANSFER LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER , M_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS AND SHOW_ON_TRANSCRIPT = 1");
			while (!$res_tc->EOF) {
				$txt .= '<tr nobr="true" >
							<td width="18%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">Transfer</td>
							<td width="10%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
								'.$res_tc->fields['COURSE_CODE'].'
							</td>
							<td width="8%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;"></td>
							<td width="8%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;"></td>
							<td width="9%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;"></td>
							<td width="8%" align="center" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;"></td>
							<td width="5%" align="center" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;"></td>
							<td width="9%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;"></td>
							<td width="8%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" ></td>
							<td width="8%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" ></td>
							<td width="9%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" ></td>
						</tr>';
				
				$res_tc->MoveNext();
			}	
		
			$res_course_schedule = $db->Execute("select IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE1 ,S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, IF(END_TIME != '00:00:00', DATE_FORMAT(END_TIME,'%h:%i %p'),'') AS END_TIME, IF(START_TIME != '00:00:00', DATE_FORMAT(START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, TRANSCRIPT_CODE, SCHEDULE_TYPE, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','N') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO,ATTENDANCE_HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE  from 

			S_STUDENT_SCHEDULE 
			LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
			LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
			LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
			LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
			LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
			LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
			LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE

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
					$per = $cum_total_attended / $cum_total_scheduled * 100;
				$txt .= '<tr nobr="true" >
						<td width="18%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
							'.$res_course_schedule->fields['TRANSCRIPT_CODE'].' ('. substr($res_course_schedule->fields['SESSION'],0,1).' - '. $res_course_schedule->fields['SESSION_NO'].')'.'
						</td>
						<td width="10%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
							'.$res_course_schedule->fields['SCHEDULE_DATE1'].'
						</td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
							'.$res_course_schedule->fields['START_TIME'].'
						</td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
							'.$res_course_schedule->fields['END_TIME'].'
						</td>
						<td width="9%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
							'.number_format_value_checker($res_course_schedule->fields['HOURS'],2).'
						</td>
						<td width="8%" align="center" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
							'.$res_course_schedule->fields['COMPLETED'].'
						</td>
						<td width="5%" align="center" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
							'.$ATTENDANCE_CODE.'
						</td>
						<td width="9%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
							'.number_format_value_checker($ATTENDANCE_HOURS,2).'
						</td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" >
							'.number_format_value_checker($cum_total_scheduled,2).'
						</td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" >
							'.number_format_value_checker($cum_total_attended,2).'
						</td>
						<td width="9%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" >
							'.number_format_value_checker($per,2).' %
						</td>
					</tr>';
				$res_course_schedule->MoveNext();
			}
			
			$res_tc = $db->Execute("SELECT SUM(HOUR) as HOUR FROM S_STUDENT_CREDIT_TRANSFER, M_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS AND SHOW_ON_TRANSCRIPT = 1");
			
			$txt .= '<tr>
						<td width="18%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Transferred: '.number_format_value_checker($res_tc->fields['HOUR'],2).'</b></td>
						<td width="57%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ></td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>'.number_format_value_checker($cum_total_scheduled,2).' </b>
						</td>
						<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>'.number_format_value_checker($cum_total_attended,2).' </b>
						</td>
						<td width="9%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;;border-right:0.5px solid #000;">
							<b>'.number_format_value_checker($per,2).' %</b>
						</td>
					</tr>
				</tbody>
			</table>';
			
		//echo $txt;exit;
	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Attendance_Report_'.uniqid().'.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	