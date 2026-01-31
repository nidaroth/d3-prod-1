<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST) || !empty($_GET)){ //Ticket # 1194  
	//echo "<pre>";print_r($_POST);exit;
	
	/* Ticket # 1194   */
	if(!empty($_GET)){
		$_POST['START_DATE'] 			= $_GET['st'];
		$_POST['END_DATE'] 				= $_GET['et'];
		$_POST['PRINT_TYPE'] 			= $_GET['pt'];
		$_POST['PK_COURSE_OFFERING'] 	= explode(",",$_GET['co']);
		$_POST['INSTRUCTOR'] 			= explode(",", $_GET['ins']);
		$_POST['PK_TERM_MASTER'] 		= $_GET['tm'];
		$_POST['FORMAT'] 				= 1;
	}
	/* Ticket # 1194   */
	
	$cond = "";
	
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE >= '$ST' ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$ET' ";
	}
	
	if($_POST['PRINT_TYPE'] == 1) {
		$PK_COURSE_OFFERING = implode(",",$_POST['PK_COURSE_OFFERING']);
		$cond .= " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($PK_COURSE_OFFERING) ";
	} else if($_POST['PRINT_TYPE'] == 2) {
		$INSTRUCTOR = implode(",",$_POST['INSTRUCTOR']);
		$cond .= " AND S_COURSE_OFFERING.PK_TERM_MASTER IN ($_POST[PK_TERM_MASTER]) AND INSTRUCTOR IN ($INSTRUCTOR) ";
	}
	
	$campus_name  = "";
	$campus_cond  = "";
	$campus_cond1 = "";
	$campus_id	  = "";
	if(!empty($_GET['campus'])){
		$PK_CAMPUS 	  = $_GET['campus'];
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
	
	$query = "SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING, TRANSCRIPT_CODE, COURSE_DESCRIPTION   
	FROM
	S_COURSE, S_COURSE_OFFERING, S_COURSE_OFFERING_SCHEDULE_DETAIL    
	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING $cond  
	GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER By TRANSCRIPT_CODE ";
	
	$res_course = $db->Execute($query);
	while (!$res_course->EOF) {
		$PK_COURSE_OFFERING_ARR[] 	= $res_course->fields['PK_COURSE_OFFERING'];
		
		$res_course->MoveNext();
	}

	$stud_query = "SELECT S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) as STUD_NAME, STUDENT_ID, IF(S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE, '%m/%d/%Y' )) AS SCHEDULE_DATE, IF(S_COURSE_OFFERING_SCHEDULE_DETAIL.START_TIME = '00:00:00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE_DETAIL.START_TIME, '%h:%i %p' )) AS START_TIME, IF(S_COURSE_OFFERING_SCHEDULE_DETAIL.END_TIME = '00:00:00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE_DETAIL.END_TIME, '%h:%i %p' )) AS END_TIME, S_COURSE_OFFERING_SCHEDULE_DETAIL.HOURS, ROOM_NO 
	FROM
	S_STUDENT_SCHEDULE, S_COURSE_OFFERING, S_COURSE_OFFERING_SCHEDULE_DETAIL 
	LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_CAMPUS_ROOM
	, S_STUDENT_MASTER, S_STUDENT_ACADEMICS   
	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING AND 
	S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND 
	S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER $cond  ";

	
	$query = "SELECT S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL, IF(S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE, '%m/%d/%Y' )) AS SCHEDULE_DATE, IF(S_COURSE_OFFERING_SCHEDULE_DETAIL.START_TIME = '00:00:00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE_DETAIL.START_TIME, '%h:%i %p' )) AS START_TIME, IF(S_COURSE_OFFERING_SCHEDULE_DETAIL.END_TIME = '00:00:00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE_DETAIL.END_TIME, '%h:%i %p' )) AS END_TIME, S_COURSE_OFFERING_SCHEDULE_DETAIL.HOURS, ROOM_NO 
	FROM
	S_COURSE_OFFERING, S_COURSE_OFFERING_SCHEDULE_DETAIL 
	LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_CAMPUS_ROOM 
	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING $cond  ";
	
	$group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
	$order_by = " ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";
	
	if($_POST['FORMAT'] == 1) {
		/////////////////////////////////////////////////////////////////
		$browser = '';
		if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
			$browser =  "chrome";
		else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		require_once('../global/tcpdf/config/lang/eng.php');
		require_once('../global/tcpdf/tcpdf.php');

			
		class MYPDF extends TCPDF {
			public function Header() {
				global $db, $campus_name;
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 13);
				$this->SetY(3);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(58, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 14);
				$this->SetY(9);
				$this->SetX(133);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Attendance Daily Sign In Sheet", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 202, 13, $style);
				
				$this->SetFont('helvetica', '', 10);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $this->campus, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(21);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $this->term, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(26);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $this->class_date, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(31);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $this->course, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(36);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $this->session, 0, false, 'R', 0, '', 0, false, 'M', 'L');

			}
			public function Footer() {
				global $db;
				
				$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 12");
		
				$BASE = -28 - $res_type->fields['FOOTER_LOC'];
				$this->SetY($BASE);
				$this->SetX(10);
				$this->SetFont('helvetica', '', 7);
				
				// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
				$CONTENT = nl2br($res_type->fields['CONTENT']);
				$this->MultiCell(190, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
				
				/*$this->SetY(-25);
				$this->SetX(10);
				$this->SetFont('helvetica', 'I', 12);
				$this->Cell(30, 10, "Instructor Signature", 0, false, 'C', 0, '', 0, false, 'T', 'M');
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(7, 273, 70, 273, $style);
				
				$this->SetY(-25);
				$this->SetX(80);
				$this->SetFont('helvetica', 'I', 12);
				$this->Cell(30, 10, "Date", 0, false, 'C', 0, '', 0, false, 'T', 'M');
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(90, 273, 120, 273, $style);*/
				
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
		$pdf->SetMargins(7, 40, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		
		$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 12");
		$BREAK_VAL = 20 + $res_type->fields['FOOTER_LOC'];
		$pdf->SetAutoPageBreak(TRUE, $BREAK_VAL);
	
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		
		foreach($PK_COURSE_OFFERING_ARR as $PK_COURSE_OFFERING){
			
			$res_cs = $db->Execute("select DATE_FORMAT(DEF_START_TIME,'%h:%i %p') AS START_TIME, DATE_FORMAT(DEF_END_TIME,'%h:%i %p') AS END_TIME, HOURS, CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO,FA_UNITS,  UNITS, CONCAT(S_EMPLOYEE_MASTER_INST.LAST_NAME,', ',S_EMPLOYEE_MASTER_INST.FIRST_NAME) AS INSTRUCTOR_NAME,ATTENDANCE_TYPE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 , IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, SESSION,SESSION_NO, COURSE_OFFERING_STATUS, TRANSCRIPT_CODE, COURSE_DESCRIPTION, CAMPUS_CODE, CONCAT(CODE,' - ',ATTENDANCE_CODE) as ATTENDANCE_CODE, IF(S_COURSE_OFFERING_SCHEDULE.START_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.START_DATE, '%m/%d/%Y' )) AS CLASS_START_DATE, IF(S_COURSE_OFFERING_SCHEDULE.END_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.END_DATE, '%m/%d/%Y' )) AS CLASS_END_DATE  
			from S_COURSE_OFFERING 
			LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_COURSE_OFFERING.PK_ATTENDANCE_CODE 
			LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS 
			LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
			LEFT JOIN M_ATTENDANCE_TYPE ON M_ATTENDANCE_TYPE.PK_ATTENDANCE_TYPE = S_COURSE_OFFERING.PK_ATTENDANCE_TYPE 
			LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
			LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
			LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR 
			LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM, S_COURSE_OFFERING_SCHEDULE,S_COURSE 
			WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE");
			
			$res_cs_det = $db->Execute("SELECT COUNT(PK_COURSE_OFFERING_SCHEDULE_DETAIL) as NO_MEETINGS, SUM(HOURS) as TOTAL_HOURS FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'  AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			
			$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY SCHEDULE_DATE ASC "); 
			while (!$res_build1->EOF) {
				$dates[] = $res_build1->fields['SCHEDULE_DATE'];
				$res_build1->MoveNext();
			}
			
			$DAYS_A = array();
			foreach($dates as $date) {
				$N = date("N",strtotime($date));
				if($N == 1)
					$DAYS_A[$N] = 'M';
				else if($N == 2)
					$DAYS_A[$N] = 'Tu';
				else if($N == 3)
					$DAYS_A[$N] = 'W';
				else if($N == 4)
					$DAYS_A[$N] = 'Th';
				else if($N == 5)
					$DAYS_A[$N] = 'F';
				else if($N == 6)
					$DAYS_A[$N] = 'Sa';
				else if($N == 7)
					$DAYS_A[$N] = 'Su';
			}
			ksort($DAYS_A);

			$cond1 = " AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ";

			$res_sch = $db->Execute($query." ".$cond." ".$cond1." GROUP BY S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL ORDER BY S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE ASC");
			while (!$res_sch->EOF) {
				$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res_sch->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
				
				$pdf->class_date 	= "Class Dates: ".$res_cs->fields['CLASS_START_DATE'].' - '.$res_cs->fields['CLASS_END_DATE'];
				$pdf->campus 		= "Campus: ".$res_cs->fields['CAMPUS_CODE'];
				$pdf->term 			= "Term: ".$res_cs->fields['BEGIN_DATE_1'].' - '.$res_cs->fields['END_DATE_1']." - ".$res_cs->fields['TERM_DESCRIPTION'];
				$pdf->course 		= "Course: ".$res_cs->fields['TRANSCRIPT_CODE'].' - '.$res_cs->fields['COURSE_DESCRIPTION'];
				$pdf->session 		= "Session: (".substr($res_cs->fields['SESSION'],0,1).' - '.$res_cs->fields['SESSION_NO'].')';
				$pdf->AddPage();
				
				$txt = '<table border="1" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td align="center" width="20%" ><br /><br /><br /><b>Instructor</b></td>
								<td align="center" width="10%"><br /><br /><br /><b>Room</b></td>
								<td align="center" width="12%"><br /><br /><br /><b>Attendance Type</b></td>
								<td align="center" width="11%"><b>Default Attendance Code</b></td>
								
								<td align="center" width="9%"><b>Total Scheduled Hours</b></td>
								<td align="center" width="8%"><br /><br /><b>Class Meetings</b></td>
								<td align="center" width="16%"><br /><br /><br /><b>Days Of Week</b></td>
								<td align="center" width="14%"><br /><br /><br /><b>Course Status</b></td>
							</tr>
							<tr>
								<td align="center" >'.$res_cs->fields['INSTRUCTOR_NAME'].'</td>
								<td align="center" >'.$res_cs->fields['ROOM_NO'].'</td>
								<td align="center" >'.$res_cs->fields['ATTENDANCE_TYPE'].'</td>
								<td align="center" >'.$res_cs->fields['ATTENDANCE_CODE'].'</td>
								
								<td align="center" >'.$res_cs_det->fields['TOTAL_HOURS'].'</td>
								<td align="center" >'.$res_cs_det->fields['NO_MEETINGS'].'</td>
								<td align="center" >'.implode(", ",$DAYS_A).'</td>
								<td align="center" >'.$res_cs->fields['COURSE_OFFERING_STATUS'].'</td>
							</tr>
						</table>';

				$txt .= '<br />';
				$txt .='<table border="0" cellspacing="0" cellpadding="15" width="100%">
							<thead>
								<tr>
									<td width="100%" ><b style="font-size:40px" >Scheduled Class Date: '.$res_sch->fields['SCHEDULE_DATE'].' - '.date("l",strtotime($res_sch->fields['SCHEDULE_DATE'])).' - '.$res_sch->fields['START_TIME'].' - '.$res_sch->fields['END_TIME'].' - '.$res_sch->fields['ROOM_NO'].' - '.number_format_value_checker($res_sch->fields['HOURS'], 2).' Hours</b><br /></td>
								</tr>
								<tr>
									<td width="33%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student</b></td>
									<td width="33%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student ID</b></td>
									<td width="33%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student Signature</b></td>
								</tr>
							</thead>';
				$cond3 = " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' ";
				$res_stud = $db->Execute($stud_query." ".$cond3." ".$group_by." ".$order_by);
				//echo $stud_query." ".$cond3." ".$group_by." ".$order_by."<br /><br />";
				while (!$res_stud->EOF) {
					$txt 	.= '<tr>
								<td width="33%" style="border-bottom:1px solid #000;" >'.$res_stud->fields['STUD_NAME'].'</td>
								<td width="33%" style="border-bottom:1px solid #000;" >'.$res_stud->fields['STUDENT_ID'].'</td>
								<td width="33%" style="border-bottom:1px solid #000;" ></td>
							</tr>';
					
					$res_stud->MoveNext();
				}
				
				$txt 	.= '</table>';

				//echo $txt;exit;
				$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
				
				$res_sch->MoveNext();
			}
		}
		//exit;
		$file_name = 'Attendance_Daily_Sign_In_Sheet_'.uniqid().'.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
		/////////////////////////////////////////////////////////////////
	} else {
		header("location:attendance_report_excel?eid=".$PK_STUDENT_ENROLLMENT);
	}
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_ATTENDANCE_DAILY_SIGN_IN_SHEET?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_ATTENDANCE_DAILY_SIGN_IN_SHEET?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels " method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-3 ">
											<select id="PRINT_TYPE" name="PRINT_TYPE"  class="form-control" onchange="get_course_offering()" >
												<option value="1">Print By Selected Course Offering</option>
												<option value="2">Print By Selected Instructor</option>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)" >
												<option value="" selected><?=FIRST_TERM?></option>
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
										
										<div class="col-md-2">
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" placeholder="<?=START_DATE?>" >
										</div>
										
										<div class="col-md-2">
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" placeholder="<?=END_DATE?>" >
										</div>
										
										<div class="col-md-1">
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
										</div>
										
									</div>
									<div class="row">
										<div class="col-md-2 align-self-center ">
										</div>
										<div class="col-md-8 align-self-center "></div>
										<div class="col-md-2 ">
											
											<!-- New -->
											<!--<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>-->
											
										</div>
									</div>
									
									<br /><br /><br /><br />
									<input type="hidden" name="FORMAT" id="FORMAT" >
                                </form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	
	</script>
	
	<script type="text/javascript">
		function get_course_offering(){
			jQuery(document).ready(function($) { 
				var PRINT_TYPE = document.getElementById('PRINT_TYPE').value
				if(PRINT_TYPE == 1) {
					var data  = 'PK_TERM_MASTER='+document.getElementById('PK_TERM_MASTER').value+'&dont_show_term=1';
					var url	  = "ajax_get_course_offering_from_term";
				} else {
					var data  = 'PK_TERM_MASTER='+document.getElementById('PK_TERM_MASTER').value;
					var url	  = "ajax_get_course_offering_instructor_from_term";
				}
				//alert(data)
				var value = $.ajax({
					url: url,	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						if(PRINT_TYPE == 1) {
							document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
							document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
							document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
							$("#PK_COURSE_OFFERING option[value='']").remove();
							
							$('#PK_COURSE_OFFERING').multiselect({
								includeSelectAllOption: true,
								allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
								nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
								numberDisplayed: 2,
								nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
							});
						} else {
							document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
							document.getElementById('INSTRUCTOR').setAttribute('multiple', true);
							document.getElementById('INSTRUCTOR').name = "INSTRUCTOR[]"
							$("#INSTRUCTOR option[value='']").remove();
							
							$('#INSTRUCTOR').multiselect({
								includeSelectAllOption: true,
								allSelectedText: 'All <?=INSTRUCTOR?>',
								nonSelectedText: '<?=INSTRUCTOR?>',
								numberDisplayed: 2,
								nSelectedText: '<?=INSTRUCTOR?> selected'
							});
						}
					}		
				}).responseText;
			});
		}
		function get_course_details(){
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function submit_form(val){
		jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		});
	}
	</script>

</body>

</html>