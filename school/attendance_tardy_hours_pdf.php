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

// require_once("pdf_custom_header.php"); //Ticket # 1588
// class MYPDF extends TCPDF {
//     public function Header() {
// 		global $db;
		
// 		/* Ticket # 1588 */
// 		if($_GET['id'] != ''){
// 			if($this->PageNo() == 1) {
// 				$CONTENT = pdf_custom_header($_GET['id'], $_GET['eid'], 1);
// 				$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
// 				$this->SetMargins('', 45, '');
// 			} else {
// 				$this->SetFont('helvetica', 'I', 15);
// 				$this->SetY(8);
// 				$this->SetX(10);
// 				$this->SetTextColor(000, 000, 000);
// 				$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
// 				$this->SetMargins('', 25, '');
// 			}
			
// 		} else {
// 			//$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

// 			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS) as ADDRESS,
// 			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS_1) as ADDRESS_1,
// 			IF(
// 			HIDE_ACCOUNT_ADDRESS_ON_REPORTS = '1',
// 			'',
// 			IF(CITY!='',CONCAT(CITY, ','),'')
// 				) AS CITY,
// 			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',STATE_CODE) as STATE_CODE,
// 			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ZIP) as ZIP,
// 			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',PHONE) as PHONE, 
// 			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',WEBSITE) as WEBSITE,HIDE_ACCOUNT_ADDRESS_ON_REPORTS FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); //DIAM-1421
			
// 			if($res->fields['PDF_LOGO'] != '') {
// 				$ext = explode(".",$res->fields['PDF_LOGO']);
// 				$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
// 			}
			
// 			$this->SetFont('helvetica', '', 15);
// 			$this->SetY(6);
// 			$this->SetX(55);
// 			$this->SetTextColor(000, 000, 000);
// 			$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
// 			$this->SetFont('helvetica', '', 8);
// 			$this->SetY(13);
// 			$this->SetX(55);
// 			$this->SetTextColor(000, 000, 000);
// 			$this->Cell(55, 8,$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
// 			$this->SetY(17);
// 			$this->SetX(55);
// 			$this->SetTextColor(000, 000, 000);
// 			$this->Cell(55, 8,$res->fields['CITY'].' '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L'); //DIAM-1421
			
// 			$this->SetY(21);
// 			$this->SetX(55);
// 			$this->SetTextColor(000, 000, 000);
// 			$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
// 		}
// 		/* Ticket # 1588 */
		
// 		$this->SetFont('helvetica', 'I', 20);
// 		$this->SetY(8);
// 		$this->SetTextColor(000, 000, 000);
// 		$this->SetX(210);
// 		$this->Cell(55, 8, "Attendance Tardy Hours", 0, false, 'L', 0, '', 0, false, 'M', 'L');

// 		$this->SetFillColor(0, 0, 0);
// 		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
// 		$this->Line(180, 13, 290, 13, $style);
		
// 		$str = "";
// 		if($_GET['st'] != '' && $_GET['et'] != '')
// 			$str = " Between ".$_GET['st'].' and '.$_GET['et'];
// 		else if($_GET['st'] != '')
// 			$str = " From ".$_GET['st'];
// 		else if($_GET['et'] != '')
// 			$str = " To ".$_GET['et'];
			
// 		$this->SetFont('helvetica', 'I', 10);
// 		$this->SetY(16);
// 		$this->SetX(210);
// 		$this->SetTextColor(000, 000, 000);
// 		$this->Cell(75, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
//     }
//     public function Footer() {
// 		global $db;
// 		$this->SetY(-15);
// 		$this->SetX(270);
// 		$this->SetFont('helvetica', 'I', 7);
// 		$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
// 		$this->SetY(-15);
// 		$this->SetX(10);
// 		$this->SetFont('helvetica', 'I', 7);
		
// 		$timezone = $_SESSION['PK_TIMEZONE'];
// 		if($timezone == '' || $timezone == 0) {
// 			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
// 			$timezone = $res->fields['PK_TIMEZONE'];
// 			if($timezone == '' || $timezone == 0)
// 				$timezone = 4;
// 		}
		
// 		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
// 		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
		
// 		$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
//     }
// }

// $pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
// $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
// $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// $pdf->SetMargins(7, 31, 7);
// $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
// //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// $pdf->SetAutoPageBreak(TRUE, 20);
// $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// $pdf->setLanguageArray($l);
// $pdf->setFontSubsetting(true);
// $pdf->SetFont('helvetica', '', 8, '', true);
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
$cond_date = "";

if($_GET['st'] != '' && $_GET['et'] != '') {
	$ST = date("Y-m-d",strtotime($_GET['st']));
	$ET = date("Y-m-d",strtotime($_GET['et']));
	$cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE BETWEEN '$ST' AND '$ET' ";
	$cond_date .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE BETWEEN '$ST' AND '$ET' ";

} else if($_GET['st'] != ''){
	$ST = date("Y-m-d",strtotime($_GET['st']));
	$cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE >= '$ST' ";
	$cond_date .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE >= '$ST' ";
} else if($_GET['et'] != ''){
	$ET = date("Y-m-d",strtotime($_GET['et']));
	$cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$ET' ";
	$cond_date .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$ET' ";
}


/*
if($_GET['id'] == '') {
	$_GET['id'] = $_SESSION['PK_STUDENT_MASTER'];
	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
	$_GET['eid'] = $res->fields['PK_STUDENT_ENROLLMENT'];
}*/

if($_GET['id'] != '') {
	$res = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if($res->RecordCount() == 0){
		header("../index");
	}
	
	$PK_STUDENT_MASTERS = explode(",",$_GET['id']);
} else {
	$res_stud = $db->Execute("SELECT S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER from S_STUDENT_MASTER, S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL,S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 16 AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 $cond $cond1 GROUP BY S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER ORDER BY CONCAT(trim(S_STUDENT_MASTER.LAST_NAME),', ',trim(S_STUDENT_MASTER.FIRST_NAME),' ',trim(S_STUDENT_MASTER.MIDDLE_NAME)) ");
	
	while (!$res_stud->EOF) {
		$PK_STUDENT_MASTERS[] = $res_stud->fields['PK_STUDENT_MASTER'];
		$res_stud->MoveNext();
	}
}
//$pdf->AddPage();

// echo implode(',',$PK_STUDENT_MASTERS);
// die;

// Ticket # 659
$tablewidth='90%';
$table_td_width='30%';
if($_GET['comm'] == 1){
	$tablewidth='100%';
	$table_td_width='';
}
// Ticket # 659
//die;
if(!empty($PK_STUDENT_MASTERS)){

	$PK_STUDENT_MASTERS_VALUES_STR = implode(',',$PK_STUDENT_MASTERS);
	$res_course_schedule=array();

	
	/*$res_course_schedule_hours = $db->Execute("CREATE TEMPORARY TABLE TARDY_RESULT AS SELECT TRANSCRIPT_CODE, S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER as PK_STUDENT_MASTER_ATTENDANCE,SESSION, SESSION_NO, IF(COURSE_TERM.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(COURSE_TERM.BEGIN_DATE, '%m/%d/%Y' )) AS COURSE_TERM_DATE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.MIDDLE_NAME) AS STUD_NAME, STUDENT_ID, IF(S_TERM_MASTER1.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER1.BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER, S_STUDENT_COURSE.PK_COURSE_OFFERING, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE AS PROGRAM_CODE, COURSE_DESCRIPTION,IF( S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE = '0000-00-00','',DATE_FORMAT( S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE, '%m/%d/%Y' )) AS SCHEDULE_DATE, ATTENDANCE_HOURS, S_STUDENT_SCHEDULE.HOURS, STUDENT_STATUS, CAMPUS_CODE, ATTENDANCE_COMMENTS     
		from 
		S_STUDENT_MASTER 
		LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
		, S_STUDENT_ENROLLMENT 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		LEFT JOIN S_TERM_MASTER AS S_TERM_MASTER1 ON S_TERM_MASTER1.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM  
		, S_STUDENT_COURSE
		LEFT JOIN S_TERM_MASTER AS COURSE_TERM ON S_STUDENT_COURSE.PK_TERM_MASTER = COURSE_TERM.PK_TERM_MASTER 
		, S_COURSE_OFFERING 
		LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
		LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
		,S_COURSE_OFFERING_SCHEDULE_DETAIL, S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE 
		
		WHERE 
		S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTERS_VALUES_STR) AND
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
		S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
		S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
		S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 
		S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND 
		S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 16 AND S_STUDENT_ENROLLMENT.IS_ACTIVE_ENROLLMENT=1 $cond GROUP BY S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER,S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL");*/

		$res_course_schedule_hours = $db->Execute("CREATE TEMPORARY TABLE TARDY_RESULT AS SELECT
		S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER as PK_STUDENT_MASTER_ATTENDANCE,S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,
		STATUS_DATE,
		CONCAT(
			IF(
				S_TERM_MASTER.BEGIN_DATE = '0000-00-00',
				'',
				DATE_FORMAT(
					S_TERM_MASTER.BEGIN_DATE,
					'%m/%d/%Y'
				)
			),
			' - ',
			CODE,
			' - ',
			STUDENT_STATUS,
			'-',
			CAMPUS_CODE
		) AS ENROLLMENT,
		IF(
				S_TERM_MASTER.BEGIN_DATE = '0000-00-00',
				'',
				DATE_FORMAT(
					S_TERM_MASTER.BEGIN_DATE,
					'%m/%d/%Y'
				)
			)AS TERM_MASTER,
			CODE AS PROGRAM_CODE,
			STUDENT_STATUS,
			CAMPUS_CODE,
		IS_ACTIVE_ENROLLMENT,
		IF(
			COURSE_TERM.BEGIN_DATE = '0000-00-00',
			'',
			DATE_FORMAT(
				COURSE_TERM.BEGIN_DATE,
				'%m/%d/%Y'
			)
		) AS COURSE_TERM_DATE,
		TRANSCRIPT_CODE,
		COURSE_DESCRIPTION,
		IF(
			S_STUDENT_SCHEDULE.SCHEDULE_DATE = '0000-00-00',
			'',
			DATE_FORMAT(
				S_STUDENT_SCHEDULE.SCHEDULE_DATE,
				'%m/%d/%Y'
			)
		) AS SCHEDULE_DATE,
		S_STUDENT_ATTENDANCE.ATTENDANCE_HOURS,
		S_STUDENT_SCHEDULE.HOURS,
		S_STUDENT_ATTENDANCE.ATTENDANCE_COMMENTS,
		S_STUDENT_COURSE.PK_STUDENT_COURSE,
		S_STUDENT_ATTENDANCE.COMPLETED FROM S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.IS_ACTIVE_ENROLLMENT = 1 LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS
	
	LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER 
	LEFT JOIN S_TERM_MASTER AS COURSE_TERM ON S_STUDENT_COURSE.PK_TERM_MASTER = COURSE_TERM.PK_TERM_MASTER 
	LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE
	.PK_COURSE_OFFERING
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_STUDENT_SCHEDULE ON S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE
	LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
	WHERE
		S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTERS_VALUES_STR) AND
		S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 16 $cond_date GROUP BY S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER,S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE");

		 $temp_result = $db->Execute("SELECT * FROM TARDY_RESULT");
		 $schedule_array=array();	
		 while (!$temp_result->EOF) { 
			$schedule_array[$temp_result->fields['PK_STUDENT_MASTER_ATTENDANCE']][]=$temp_result->fields;
			$temp_result->MoveNext();
		 }
		//  echo "<pre>";
		// print_r($schedule_array);
		// exit;
	
	$txt ='';
	$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%">';
	foreach($PK_STUDENT_MASTERS as $PK_STUDENT_MASTER) { 

		
		 $res_stud = $db->Execute("SELECT CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.MIDDLE_NAME) AS STUD_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		//$pdf->STUD_NAME = $res_course_schedule->fields['STUD_NAME'];
		// Ticket # 659
		$txt .= '<tr nobr="true" >
					<td width="100%" >
						<table border="0" cellspacing="0" cellpadding="5" width="'.$tablewidth.'">
							<tr>
								<td width="'.$table_td_width.'" ><b style="font-size:18px">'.$res_stud->fields['STUD_NAME'].'</b><br /></td>
							</tr>
							<tr>
								<td style="width: 20%;border-bottom:1px solid #000" ><b>Enrollment</b></td>
								<td style="width: 10%;border-bottom:1px solid #000" ><b>Course Term</b></td>
								<td style="width: 10%;border-bottom:1px solid #000" ><b>Course</b></td>
								<td style="width: 20%;border-bottom:1px solid #000" ><b>Course Description</b></td>
								<td style="width: 15%;border-bottom:1px solid #000" align="right" ><b>Class Date</b></td>
								<td style="width: 15%;border-bottom:1px solid #000" align="right" ><b>Hours Missed</b></td>';
								
								if($_GET['comm'] == 1)
									$txt .= '<td style="width: 10%;border-bottom:1px solid #000" align="right"><b>Comments</b></td>';
		$txt .= '</tr>';

		// $res_course_schedule = $db->Execute("SELECT TRANSCRIPT_CODE, SESSION, SESSION_NO, IF(COURSE_TERM.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(COURSE_TERM.BEGIN_DATE, '%m/%d/%Y' )) AS COURSE_TERM_DATE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.MIDDLE_NAME) AS STUD_NAME, STUDENT_ID, IF(S_TERM_MASTER1.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER1.BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER, S_STUDENT_COURSE.PK_COURSE_OFFERING, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE AS PROGRAM_CODE, COURSE_DESCRIPTION,IF( S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE = '0000-00-00','',DATE_FORMAT( S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE, '%m/%d/%Y' )) AS SCHEDULE_DATE, ATTENDANCE_HOURS, S_STUDENT_SCHEDULE.HOURS, STUDENT_STATUS, CAMPUS_CODE, ATTENDANCE_COMMENTS FROM 	S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER AS S_TERM_MASTER1 ON S_TERM_MASTER1.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER AS COURSE_TERM ON S_STUDENT_COURSE.PK_TERM_MASTER = COURSE_TERM.PK_TERM_MASTER, S_COURSE_OFFERING	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION,S_COURSE_OFFERING_SCHEDULE_DETAIL, S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE WHERE S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 		S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 		S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 	S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 16 $cond GROUP BY S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL");

		$i = 1;
		$total = 0;
		/*while (!$res_course_schedule->EOF) { // Ticket # 659
			$txt .= '<tr>
						<td style="" >'.$i.'. '.$res_course_schedule->fields['TERM_MASTER'].' - '.$res_course_schedule->fields['PROGRAM_CODE'].' - '.$res_course_schedule->fields['STUDENT_STATUS'].' - '.$res_course_schedule->fields['CAMPUS_CODE'].'</td>
						<td style="">'.$res_course_schedule->fields['COURSE_TERM_DATE'].'</td>
						<td style="" >'.$res_course_schedule->fields['TRANSCRIPT_CODE'].'</td>
						<td style="" >'.$res_course_schedule->fields['COURSE_DESCRIPTION'].'</td>
						<td style="" align="right" >'.$res_course_schedule->fields['SCHEDULE_DATE'].'</td>
						<td style="" align="right" >'.number_format(($res_course_schedule->fields['HOURS'] - $res_course_schedule->fields['ATTENDANCE_HOURS']),2).'</td>';
						
						if($_GET['comm'] == 1)
							$txt .= '<td style="" >'.$res_course_schedule->fields['ATTENDANCE_COMMENTS'].'</td>';
					$txt .= '</tr>';
			$i++;
			$total += ($res_course_schedule->fields['HOURS'] - $res_course_schedule->fields['ATTENDANCE_HOURS']);
			$res_course_schedule->MoveNext();
		}*/

	
		if(!empty($schedule_array[$PK_STUDENT_MASTER])){
		
		foreach($schedule_array[$PK_STUDENT_MASTER] as $key => $fields){
			/*$txt .= '<tr>
						<td style="" >'.$i.'. '.$fields['TERM_MASTER'].' - '.$fields['PROGRAM_CODE'].' - '.$fields['STUDENT_STATUS'].' - '.$fields['CAMPUS_CODE'].'</td>
						<td style="">'.$fields['COURSE_TERM_DATE'].'</td>
						<td style="" >'.$fields['TRANSCRIPT_CODE'].'</td>
						<td style="" >'.$fields['COURSE_DESCRIPTION'].'</td>
						<td style="" align="right" >'.$fields['SCHEDULE_DATE'].'</td>
						<td style="" align="right" >'.number_format(($fields['HOURS'] - $fields['ATTENDANCE_HOURS']),2).'</td>';*/
						$txt .= '<tr>
						<td style="" >'.$i.'. '.$fields['ENROLLMENT'].'</td>
						<td style="">'.$fields['COURSE_TERM_DATE'].'</td>
						<td style="" >'.$fields['TRANSCRIPT_CODE'].'</td>
						<td style="" >'.$fields['COURSE_DESCRIPTION'].'</td>
						<td style="" align="right" >'.$fields['SCHEDULE_DATE'].'</td>
						<td style="" align="right" >'.number_format(($fields['HOURS'] - $fields['ATTENDANCE_HOURS']),2).'</td>';
						
						if($_GET['comm'] == 1)
							$txt .= '<td style="" >'.$fields['ATTENDANCE_COMMENTS'].'</td>';
					$txt .= '</tr>';
			$i++;
			$total += ($fields['HOURS'] - $fields['ATTENDANCE_HOURS']);
		}
		}
		// Ticket # 659
		$txt1 ='';
		if($_GET['comm'] == 1)
		$txt1 = '<td style="" ></td>';		
		// Ticket # 659
		$txt .= '<tr>				
					<td style="" ></td>
					<td style="" ></td>
					<td style="" ></td>
					<td style="" ></td>
					<td style="border-top:1px solid #000;" align="right" ><i><b>Student Totals:</b></i></td>
					<td style="border-top:1px solid #000;" align="right" ><i>'.number_format($total,2).'</i></td>
					'.$txt1.'	
				</tr>
				<tr>
					<td style="" ></td>
					<td style="" ></td>
					<td style="" ></td>
					<td style="" ></td>
					<td style="" align="right" ><i><b>Number of Tardies:</b></i></td>
					<td style="" align="right" ><i>'.count($schedule_array[$PK_STUDENT_MASTER]).'</i></td>
					'.$txt1.'
				</tr>
			</table>
		</td>
	</tr>';
			// Ticket # 659						
			//<td style="" align="right" ><i>'.$res_course_schedule->RecordCount().'</i></td>

			//echo $txt;exit;
		
	}
	$txt .= '</table>';
}else{
	$txt ='<table border="0" cellspacing="0" cellpadding="0" width="100%"></table>';
}

//echo $txt; die;
// $pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align=''); 
//$file_name = 'Attendance Tardy Hours.pdf';

/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');*/
	
//$pdf->Output('temp/'.$file_name, 'FD');
//return $file_name;	
//die;

//// Ticket # 659
$file_name = 'Attendance_Tardy_Hours_'.uniqid().'.pdf';
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
$logo='';
if($res->fields['PDF_LOGO'] != '')
	$PDF_LOGO =$res->fields['PDF_LOGO'];
	
	if($PDF_LOGO != ''){
		//$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
		$PDF_LOGO=str_replace('../',$http_path,$PDF_LOGO);
		$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
	}


	$SCHOOL_NAME ='';
	if($res->fields['SCHOOL_NAME'] != '')
		$SCHOOL_NAME =$res->fields['SCHOOL_NAME'];


	$str = "";
	if($_GET['st'] != '' && $_GET['et'] != '')
	$str = " Between ".$_GET['st'].' and '.$_GET['et'];
	else if($_GET['st'] != '')
	$str = " From ".$_GET['st'];
	else if($_GET['et'] != '')
	$str = " To ".$_GET['et'];

	$header = '<table width="100%" >
			<tr>
				<td width="20%" valign="top" >'.$logo.'</td>
				<td width="40%" valign="top" style="font-family:helvetica;font-weight:normal;" >
				<table width="100%" >
					<tr><td valign="top" style="font-size:28px;" >'.$SCHOOL_NAME.'</td></tr>
					<tr><td valign="top" style="font-size:14px;" >'.$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'].'</td></tr>
					<tr><td valign="top" style="font-size:14px;" >'.$res->fields['CITY'].' '.$res->fields['STATE_CODE'].','.$res->fields['ZIP'].'</td></tr>
					<tr><td valign="top" style="font-size:14px;" >'.$res->fields['PHONE'].'</td></tr>
				</table>
				</td>
				<td width="40%" valign="top" >
					<table width="100%" >
						<tr>
							<td width="100%" align="right" style="font-size:32px;border-bottom:1px solid #000;font-style: italic;font-family:helvetica;font-weight:normal;" >Attendance Tardy Hours</td>
						</tr>
						<tr><td width="100%" align="right" style="font-size:16px;solid #000;font-style: italic;font-family:helvetica;font-weight:normal;" >'.$str.'</td></tr>										
					</table>
				</td>
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
			body{ font-size:15px; font-family:helvetica; }	
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
$foldername = 'tardy_hours';
$header_path= create_html_file('header_attendance_tardy_hours_pdf.html',$header_cont,$foldername);
$content_path=create_html_file('content_attendance_tardy_hours_pdf.html',$html_body_cont,$foldername);
$footer_path= create_html_file('footer_attendance_tardy_hours_pdf.html',$footer_cont,$foldername);

sleep(1);
$margin_top="40mm";
exec('xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation landscape --page-size A4 --page-width 200mm  --page-height 296mm --margin-top '.$margin_top.'  --footer-spacing 8  --margin-left 5mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/'.$foldername.'/'.$file_name.' 2>&1');


if(isset($_POST['ajaxpost']) && $_POST['ajaxpost']==1){
	header('Content-type: application/json; charset=UTF-8');
	$data_res = [];
	$data_res['path'] = 'temp/'.$foldername.'/'.$file_name;
	$data_res['filename'] = $file_name;
	echo json_encode($data_res);
}else{
	header('Content-Type: Content-Type: application/pdf');
	header('Content-Disposition: attachment; filename="' . basename($http_path.'school/temp/'.$foldername.'/'.$file_name) . '"');
	readfile('temp/'.$foldername.'/'.$file_name);
}

unlink('../school/temp/'.$foldername.'/header_attendance_tardy_hours_pdf.html');
unlink('../school/temp/'.$foldername.'/content_attendance_tardy_hours_pdf.html');
unlink('../school/temp/'.$foldername.'/footer_attendance_tardy_hours_pdf.html');
exit;	
// Ticket # 659
