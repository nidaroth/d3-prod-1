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

require_once("pdf_custom_header.php"); //Ticket # 1588
	
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		/* Ticket # 1588 */
		if($_GET['id'] != ''){
			$CONTENT = pdf_custom_header($_GET['id'], $_GET['eid'], 1);
			$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
			$this->SetMargins('', 45, '');
			
		} else {
		
			//$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS) as ADDRESS,
			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS_1) as ADDRESS_1,
			IF(
			HIDE_ACCOUNT_ADDRESS_ON_REPORTS = '1',
			'',
			IF(CITY!='',CONCAT(CITY, ','),'')
				) AS CITY,
			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',STATE_CODE) as STATE_CODE,
			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ZIP) as ZIP,
			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',PHONE) as PHONE, 
			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',WEBSITE) as WEBSITE,HIDE_ACCOUNT_ADDRESS_ON_REPORTS FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); //DIAM-1421
			
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
			$this->Cell(55, 8,$res->fields['CITY'].' '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L'); //DIAM-1421
			
			$this->SetY(21);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		}
		/* Ticket # 1588 */
		
		$this->SetFont('helvetica', 'I', 17);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(200);
		$this->Cell(55, 8, "Attendance Absences By Course", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(180, 13, 290, 13, $style);
		
		$str = "";
		if($_GET['dt'] != '')
			$str = 'As of Date: '.date("D, F d, Y ", strtotime($_GET['dt']));
			
		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(16);
		$this->SetX(185);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(104, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		/* Ticket # 1341  */
		$campus_name  = "";
		$campus_cond  = "";
		$campus_cond1 = "";
		$campus_id	  = "";
		if(!empty($_POST['PK_CAMPUS'])){
			$PK_CAMPUS 	  = implode(",",$_POST['PK_CAMPUS']);
			$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
			$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		}
		
		$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
		while (!$res_campus->EOF) {
			if($campus_name != '')
				$campus_name .= ', ';
			$campus_name .= $res_campus->fields['CAMPUS_CODE'];
			
			if($campus_id != '')
				$campus_id .= ',';
			$campus_id .= $res_campus->fields['PK_CAMPUS'];
			
			$res_campus->MoveNext();
		}
		
		$this->SetY(19);
		$this->SetX(140);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
		
		$str = "";
		$res_campus = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER IN ($_GET[t_id]) order by BEGIN_DATE DESC");
		while (!$res_campus->EOF) {
			if($str != '')
				$str .= ', ';
			$str .= $res_campus->fields['BEGIN_DATE_1'];
			
			$res_campus->MoveNext();
		}
		
		$this->SetY(28);
		$this->SetX(140);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(150, 5, "Term(s): ".$str, 0, 'R', 0, 0, '', '', true);
		
		/* Ticket # 1341  */
    }
    public function Footer() {
		global $db;
		$this->SetY(-15);
		$this->SetX(270);
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

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 36, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 9, '', true);

$cond = "";
if($_GET['dt'] != ''){
	$ET = date("Y-m-d",strtotime($_GET['dt']));
	$cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$ET' ";
	$cond2 = " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$ET' ";
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

/*
if($_GET['id'] == '') {
	$_GET['id'] = $_SESSION['PK_STUDENT_MASTER'];
	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
	$_GET['eid'] = $res->fields['PK_STUDENT_ENROLLMENT'];
}*/

if($_GET['id'] != '') {
	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if($res->RecordCount() == 0){
		header("../index");
	}
	$cond .= " AND S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER = '$_GET[id]' ";
	
}

/* Ticket #1145 */
/* Ticket # 1194  */
$course_term_cond = "";
if($_GET['t_id'] != '') {
	$course_term_cond = " AND S_STUDENT_COURSE.PK_TERM_MASTER IN ($_GET[t_id]) "; //Ticket # 1341
}

/* Ticket # 1341 */
if($_GET['co'] != '') {
	$course_term_cond = " AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN ($_GET[co]) ";
}
/* Ticket # 1341 */

$res_prog = $db->Execute("select M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM  from 
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
	$PK_CAMPUS_PROGRAMS[] = $res_prog->fields['PK_CAMPUS_PROGRAM'];
	$res_prog->MoveNext();
}


$pdf->AddPage();

$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
			<thead>
				<tr>
					<td width="14%" style="border-bottom:1px solid #000">
						<b><br />Student</b>
					</td>
					<td width="7%" style="border-bottom:1px solid #000">
						<b><br />First Term</b>
					</td>
					<td width="7%" style="border-bottom:1px solid #000">
						<b>Course<br />Term Start</b>
					</td>
					<td width="14%" style="border-bottom:1px solid #000">
						<b><br />Course Offering</b>
					</td>
					<td width="14%" style="border-bottom:1px solid #000">
						<b><br />Instructor</b>
					</td>
					<td width="7%" style="border-bottom:1px solid #000">
						<b>Last Day Attended</b>
					</td>
					<td width="5%" style="border-bottom:1px solid #000" align="right" >
						<b>Absent Count</b>
					</td>
					<td width="5%" style="border-bottom:1px solid #000" align="right">
						<b>Present Count</b>
					</td>
					<td width="7%" style="border-bottom:1px solid #000" align="right">
						<b>Scheduled Days</b>
					</td>
					
					<td width="7%" style="border-bottom:1px solid #000" align="right" >
						<b>Scheduled Hours</b>
					</td>
					<td width="6%" style="border-bottom:1px solid #000" align="right" >
						<b>Attended Hours</b>
					</td>
					<td width="8%" style="border-bottom:1px solid #000" align="right" >
						<b>Attndance Percentage</b>
					</td>
				</tr>
			</thead>';
if(!empty($PK_CAMPUS_PROGRAMS)){		
	
	foreach($PK_CAMPUS_PROGRAMS as $PK_CAMPUS_PROGRAM) {
		$res_prog = $db->Execute("SELECT PROGRAM_TRANSCRIPT_CODE, DESCRIPTION FROM M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ");
		
		$txt .= '<tr>
					<td width="100%" >
						<b><i style="font-size:40px">'.$res_prog->fields['PROGRAM_TRANSCRIPT_CODE'].' - '.$res_prog->fields['DESCRIPTION'].'</i></b>
					</td>
				</tr>';
		/* Ticket #1145 */
		//Ticket # 1194 
		$res_course_schedule = $db->Execute("select TRANSCRIPT_CODE, SESSION, SESSION_NO, IF(COURSE_TERM.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(COURSE_TERM.BEGIN_DATE, '%Y-%m-%d' )) AS COURSE_TERM_DATE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STUD_NAME, STUDENT_ID, IF(S_TERM_MASTER1.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER1.BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER, S_STUDENT_COURSE.PK_COURSE_OFFERING, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) as INSTRUCTOR_NAME   
		from 
		S_STUDENT_MASTER 
		LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
		, S_STUDENT_ENROLLMENT
		LEFT JOIN S_TERM_MASTER AS S_TERM_MASTER1 ON S_TERM_MASTER1.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		, S_STUDENT_COURSE
		LEFT JOIN S_TERM_MASTER AS COURSE_TERM ON S_STUDENT_COURSE.PK_TERM_MASTER = COURSE_TERM.PK_TERM_MASTER 
		, S_COURSE_OFFERING 
		LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR  
		LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
		LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
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
		S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 $cond $course_term_cond 
		GROUP BY S_STUDENT_COURSE.PK_STUDENT_COURSE ORDER BY COURSE_TERM.BEGIN_DATE ASC, TRANSCRIPT_CODE ASC, SESSION ASC, SESSION_NO ASC, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC");
		//AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code)

		$first_line = 0;
		$pre_co		= "";
		while (!$res_course_schedule->EOF) {
			$first_line++;
			$PK_STUDENT_MASTER 		= $res_course_schedule->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_ENROLLMENT 	= $res_course_schedule->fields['PK_STUDENT_ENROLLMENT'];
			$PK_COURSE_OFFERING		= $res_course_schedule->fields['PK_COURSE_OFFERING'];
			
			$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
			
			/* Ticket #1145 */
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
			
			/*
			$CONSECUTIVE_DAYS_ABSENT = 0;
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
			$WORK_PHONE 	= preg_replace( '/[^0-9]/', '',$res_address->fields['WORK_PHONE']);
			$CELL_PHONE 	= preg_replace( '/[^0-9]/', '',$res_address->fields['CELL_PHONE']);

			if($HOME_PHONE != '')
				$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
				
			if($WORK_PHONE != '')
				$WORK_PHONE = '('.$WORK_PHONE[0].$WORK_PHONE[1].$WORK_PHONE[2].') '.$WORK_PHONE[3].$WORK_PHONE[4].$WORK_PHONE[5].'-'.$WORK_PHONE[6].$WORK_PHONE[7].$WORK_PHONE[8].$WORK_PHONE[9];
				
			if($CELL_PHONE != '')
				$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];

			/* Ticket #1145  */
			$SCH_COUNT 		= $res_sch->fields['NO'] - $res_exc_hour->fields['EXC_COUNT'];
			$SCHEDULED_HOUR = $res_sch->fields['SCHEDULED_HOUR'] - $res_exc_hour->fields['EXC_HOUR'];
			
			$border = "";
			if($res_course_schedule->fields['PK_COURSE_OFFERING'] != $pre_co) {
				$pre_co = $res_course_schedule->fields['PK_COURSE_OFFERING'];
				if($first_line != 1)
					$border = "border-top:1px solid #ccc;";
			}
			
			$txt .= '<tr nobr="true" >
						<td width="14%" style="'.$border.'" >'.$res_course_schedule->fields['STUD_NAME'].'</td>
						<td width="7%" style="'.$border.'" >'.$res_course_schedule->fields['TERM_MASTER'].'</td>
						<td width="7%" style="'.$border.'" >'.$res_course_schedule->fields['COURSE_TERM_DATE'].'</td>
						<td width="14%" style="'.$border.'" >'.$res_course_schedule->fields['TRANSCRIPT_CODE'].' ('. substr($res_course_schedule->fields['SESSION'],0,1).' - '. $res_course_schedule->fields['SESSION_NO'].')'.'</td>
						<td width="14%" style="'.$border.'" >'.$res_course_schedule->fields['INSTRUCTOR_NAME'].'</td>
						<td width="7%" style="'.$border.'" >'.$res_last->fields['SCHEDULE_DATE'].'</td>
						<td width="5%" style="'.$border.'" align="right" >'.$res_abs->RecordCount().'</td>
						<td width="5%" style="'.$border.'" align="right" >'.$res_last->RecordCount().'</td>
						<td width="7%" style="'.$border.'" align="right" >'.$SCH_COUNT.'</td>
						<td width="7%" style="'.$border.'" align="right" >'.$SCHEDULED_HOUR.'</td>
						<td width="6%" style="'.$border.'" align="right" >'.$res_att_hour->fields['ATTENDED_HOUR'].'</td>
						<td width="8%" style="'.$border.'" align="right" >'.number_format_value_checker(($res_att_hour->fields['ATTENDED_HOUR'] / $SCHEDULED_HOUR * 100),2).' %</td>
					</tr>';
			$res_course_schedule->MoveNext();
		}
	}
}	
$txt .= '</table>';

$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'Attendance_Absences_By_Course_'.uniqid().'.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	
