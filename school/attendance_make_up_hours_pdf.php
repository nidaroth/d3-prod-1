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
		if($_GET['eid'] != ''){
			if($this->PageNo() == 1) {
				$res = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$CONTENT = pdf_custom_header($res->fields['PK_STUDENT_MASTER'], $_GET['eid'], 1);
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
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(130);
		$this->Cell(55, 8, "Student Make Up Hours", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(120, 13, 205, 13, $style);
		
		$str = "";
		if($_GET['st'] != '' && $_GET['et'] != '')
			$str = " Between ".$_GET['st'].' and '.$_GET['et'];
		else if($_GET['st'] != '')
			$str = " From ".$_GET['st'];
		else if($_GET['et'] != '')
			$str = " To ".$_GET['et'];
			
		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(16);
		$this->SetX(100);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(104, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
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
$pdf->SetFont('helvetica', '', 9, '', true);

$cond = "";
if($_GET['st'] != '' && $_GET['et'] != '') {
	$ST = date("Y-m-d",strtotime($_GET['st']));
	$ET = date("Y-m-d",strtotime($_GET['et']));
	$cond .= " AND SCHEDULE_DATE BETWEEN '$ST' AND '$ET' ";
} else if($_GET['st'] != ''){
	$ST = date("Y-m-d",strtotime($_GET['st']));
	$cond .= " AND SCHEDULE_DATE >= '$ST' ";
} else if($_GET['et'] != ''){
	$ET = date("Y-m-d",strtotime($_GET['et']));
	$cond .= " AND SCHEDULE_DATE <= '$ET' ";
}

/*
if($_GET['id'] == '') {
	$_GET['id'] = $_SESSION['PK_STUDENT_MASTER'];
	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
	$_GET['eid'] = $res->fields['PK_STUDENT_ENROLLMENT'];
}*/

if($_GET['eid'] != '') {
	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_GET[eid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if($res->RecordCount() == 0){
		header("../index");
	}
	
	$PK_STUDENT_ENROLLMENTS = explode(",",$_GET['eid']);
} else {
	$res_stud = $db->Execute("select S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT  from 
	S_STUDENT_MASTER, S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE 
	WHERE 
	S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.ARCHIVED = 0 AND 
	S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 11 $cond GROUP BY S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT");
	
	while (!$res_stud->EOF) {
		$PK_STUDENT_ENROLLMENTS[] = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
		$res_stud->MoveNext();
	}
}

$_SESSION['temp_id'] = '';
foreach($PK_STUDENT_ENROLLMENTS as $PK_STUDENT_ENROLLMENT) {
	
	$res_enroll = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER,EXPECTED_GRAD_DATE,PROGRAM_TRANSCRIPT_CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER,CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS STUD_NAME, STUDENT_ID, DATE_OF_BIRTH, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM 
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
	
	$PK_CAMPUS_PROGRAM = $res_enroll->fields['PK_CAMPUS_PROGRAM'];
	$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	$PK_STUDENT_MASTER 	= $res_enroll->fields['PK_STUDENT_MASTER'];
	$DATE_OF_BIRTH 		= $res_enroll->fields['DATE_OF_BIRTH'];

	if($DATE_OF_BIRTH != '0000-00-00')
		$DATE_OF_BIRTH = date("Y-m-d",strtotime($DATE_OF_BIRTH));
	else
		$DATE_OF_BIRTH = '';
		
	$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

	$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
	if($EXPECTED_GRAD_DATE != '0000-00-00')
		$EXPECTED_GRAD_DATE = date("Y-m-d",strtotime($EXPECTED_GRAD_DATE));
	else
		$EXPECTED_GRAD_DATE = '';
		
	$pdf->STUD_NAME 			= $res_enroll->fields['STUD_NAME'];
	$pdf->PK_STUDENT_MASTER 	= $PK_STUDENT_MASTER;
	$pdf->PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT;
	$pdf->startPageGroup();
	$pdf->AddPage();

	$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td width="100%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ><b>'.$res_enroll->fields['STUD_NAME'].'</b></td>
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
			<br /><br />
			<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<thead>
					<tr>
						<td width="61%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							<b>Course</b>
						</td>
						<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							<b>Term</b>
						</td>
						<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							<b>Class Date</b>
						</td>
						<td width="15%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;;border-right:0.5px solid #000;">
							<b>Make Up Hours</b>
						</td>
					</tr>
				</thead>
				<tbody>';
		
			$res_course_schedule = $db->Execute("select IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%Y-%m-%d'),'') AS SCHEDULE_DATE1, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, IF(END_TIME != '00:00:00', DATE_FORMAT(END_TIME,'%h:%i %p'),'') AS END_TIME, IF(START_TIME != '00:00:00', DATE_FORMAT(START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, TRANSCRIPT_CODE, SCHEDULE_TYPE, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','N') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO,ATTENDANCE_HOURS, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER  from 

			S_STUDENT_SCHEDULE 
			LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
			LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
			LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
			LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
			LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
			LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
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
				$txt .= '<tr nobr="true" >
						<td width="61%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							'.$res_course_schedule->fields['TRANSCRIPT_CODE'].' ('. substr($res_course_schedule->fields['SESSION'],0,1).' - '. $res_course_schedule->fields['SESSION_NO'].')'.'
						</td>
						<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							'.$res_course_schedule->fields['TERM_MASTER'].'
						</td>
						<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							'.$res_course_schedule->fields['SCHEDULE_DATE1'].'
						</td>
						<td width="15%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;;border-right:0.5px solid #000;">
							'.number_format_value_checker($res_course_schedule->fields['ATTENDANCE_HOURS'],2).'
						</td>
					</tr>';
				$res_course_schedule->MoveNext();
			}
			
			$txt .= '<tr>
						<td width="85%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;"><b>Student Total:</b></td>
						<td width="15%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;;border-right:0.5px solid #000;">
							<b>'.number_format_value_checker($total_attended,2).'</b>
						</td>
					</tr>
				</tbody>
			</table>';
			
		//echo $txt;exit;
	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Attendance_Make_Up_Hours_'.uniqid().'.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');*/
	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	