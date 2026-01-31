<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_attendance_analysis_report.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST) || !empty($_GET)){ //Ticket # 1194
	//echo "<pre>";print_r($_POST);exit;
	
	/* Ticket # 1194 */
	if(!empty($_GET)){
		$_POST['AS_OF_DATE'] 	= $_GET['AS_OF_DATE'];
		$_POST['GROUP_BY'] 		= $_GET['GROUP_BY'];
		$_POST['FORMAT'] 		= $_GET['FORMAT'];
		$_POST['PK_CAMPUS'] 	= $_GET['campus'];
	}
	/* Ticket # 1194 */
	//DIAM-1685
	$wh_cond = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	  = $_POST['PK_CAMPUS'];
		$wh_cond = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	//DIAM-1685

	/* Ticket # 824 */
	$course_term_cond = "";
	if($_GET['t_id'] != '') {
		$course_term_cond = " AND S_STUDENT_COURSE.PK_TERM_MASTER IN ($_GET[t_id]) ";
	}
	/* Ticket # 824 */
	
	$AS_OF_DATE = date("Y-m-d", strtotime($_POST['AS_OF_DATE']));
	
	/* Ticket #1145 */
	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
	$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
	$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
	
	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
	$excluded_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$exclude_cond  = "";
	if($excluded_att_code != '')
		$exclude_cond = " AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE NOT IN ($excluded_att_code) ";
	/* Ticket #1145 */
	
	$PK_CAMPUS_PROGRAMS 	= array();
	$CAMPUS_PROGRAMS_NAME 	= array();
	if($_POST['GROUP_BY'] == 1) {
		/* Ticket # 824 */
		$res_prog = $db->Execute("select M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM, M_CAMPUS_PROGRAM.CODE, M_CAMPUS_PROGRAM.DESCRIPTION  from 
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
		S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code) AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$AS_OF_DATE' $course_term_cond 
		GROUP BY M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM ORDER BY CODE ASC ");
		/* Ticket # 824 */
		
		while (!$res_prog->EOF) {
			$PK_CAMPUS_PROGRAMS[]   = $res_prog->fields['PK_CAMPUS_PROGRAM'];
			$CAMPUS_PROGRAMS_NAME[] = $res_prog->fields['CODE'].' '.$res_prog->fields['DESCRIPTION'];
			
			$res_prog->MoveNext();
		}
	} else {
		$PK_CAMPUS_PROGRAMS[]   = -1;
		$CAMPUS_PROGRAMS_NAME[] = '';
	}
	//DIAM-1685
	$query = "select COURSE_CODE, M_SESSION.SESSION as SESSION, SESSION_NO, IF(COURSE_TERM.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(COURSE_TERM.BEGIN_DATE, '%Y-%m-%d' )) AS COURSE_TERM_DATE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STUD_NAME, STUDENT_ID, IF(S_TERM_MASTER1.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER1.BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER, S_STUDENT_COURSE.PK_COURSE_OFFERING, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, STUDENT_STATUS, COURSE_TERM.BEGIN_DATE as COURSE_TERM_DATE_1, CAMPUS_CODE, M_STUD_SESSION.SESSION as STUD_SESSION, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) as INSTRUCTOR_NAME
	from 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	LEFT JOIN M_SESSION as M_STUD_SESSION On M_STUD_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
	LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN S_TERM_MASTER AS S_TERM_MASTER1 ON S_TERM_MASTER1.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	, S_STUDENT_COURSE
	LEFT JOIN S_TERM_MASTER AS COURSE_TERM ON S_STUDENT_COURSE.PK_TERM_MASTER = COURSE_TERM.PK_TERM_MASTER 
	, S_COURSE_OFFERING 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = INSTRUCTOR 
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
	S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND 
	S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code) AND 
	S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$AS_OF_DATE'  $course_term_cond $wh_cond"; /// Ticket # 824
	
	$group_by = " GROUP BY S_STUDENT_COURSE.PK_STUDENT_COURSE ";
	$order_by = " ORDER BY  CONCAT(S_STUDENT_MASTER.LAST_NAME,' ',S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, COURSE_TERM_DATE_1 ASC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ";
	
	//echo $query.$order_by;exit;
		
	if($_POST['FORMAT'] == 1){
	
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
				global $db;
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 15);
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', 'I', 14);
				$this->SetY(9);
				$this->SetX(208);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Attendance Daily Absents By Course", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(180, 13, 290, 13, $style);
				
				$campus_name  = "";
				$campus_cond  = "";
				$campus_cond1 = "";
				$campus_id	  = "";
				if(!empty($_POST['PK_CAMPUS'])){
					$PK_CAMPUS 	  = $_POST['PK_CAMPUS'];
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
				
				$this->SetY(13);
				$this->SetX(140);
				$this->SetTextColor(000, 000, 000);
				$this->SetFont('helvetica', 'I', 10);
				$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);

				$str = 'As of Date: '.date("m/d/Y", strtotime($_POST['AS_OF_DATE']));
				
				$this->SetY(20);
				$this->SetX(185);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(104, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$group_by = "No Grouping";
				if($_POST['GROUP_BY'] == 1)
					$group_by = "Group By Program";	
		
				$this->SetY(25);
				$this->SetX(185);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(104, 5, "Group By: ".$group_by, 0, false, 'R', 0, '', 0, false, 'M', 'L');
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
		$pdf->SetMargins(7, 31, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();

		$total 	= 0;
		$txt 	= '';

		$sub_total = 0;
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="17%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Student</td>
							<td width="5%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Campus</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />First Term</td>							
							<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Program</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Status</td>
							
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Course<br />Term Start</td>
							<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Course</td>
							
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Consecutive<br />Days Absent</td>
							<td width="5%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Absent<br />Count</td>
							
							<td width="5%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Present Count</td>
							<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Scheduled<br />Days</td>
							<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Attendance<br />Percentage</td>
						</tr>
					</thead>';

		foreach($PK_CAMPUS_PROGRAMS as $key => $PK_CAMPUS_PROGRAMS_1){
			$cond = "";
			if($PK_CAMPUS_PROGRAMS_1 != -1 ) { 
				$cond = " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAMS_1' ";
			}
			//echo $query." ".$cond." ".$group_by." ".$order_by."<br /><br />";exit;

			$prog_index = 0;
			$res_course_schedule = $db->Execute($query." ".$cond." ".$group_by." ".$order_by);
			while (!$res_course_schedule->EOF) { 
				$PK_STUDENT_MASTER 		= $res_course_schedule->fields['PK_STUDENT_MASTER'];
				$PK_STUDENT_ENROLLMENT 	= $res_course_schedule->fields['PK_STUDENT_ENROLLMENT'];
				$PK_COURSE_OFFERING		= $res_course_schedule->fields['PK_COURSE_OFFERING'];
				
				if($prog_index == 0) {
					$prog_index++;
					
					if($PK_CAMPUS_PROGRAMS_1 != -1 ) {
						$txt .= '<tr>
								<td width="10%" >
									<b style="font-size:40px"><i>Program</i></b>
								</td>
								<td width="90%" >
									<i style="font-size:40px">'.$CAMPUS_PROGRAMS_NAME[$key].'</i>
								</td>
							</tr>';
							
						$cond = " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAMS_1' ";
					}
				}

				/* Ticket #1145 */
				$res_last = $db->Execute("SELECT IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE = '0000-00-00','', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE, '%m/%d/%Y' )) AS SCHEDULE_DATE 
				FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE 
				WHERE 
				S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
				PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
				S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
				S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE AND 
				S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
				S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$AS_OF_DATE' AND 
				S_STUDENT_ATTENDANCE.COMPLETED = 1 AND 
				S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ORDER BY S_STUDENT_SCHEDULE.SCHEDULE_DATE DESC "); 
		
				$res_att_hour = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDED_HOUR 
				FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE 
				WHERE 
				S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
				PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
				S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
				S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE AND 
				S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.COMPLETED = 1 AND 
				S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$AS_OF_DATE'  "); 

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
				S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code) AND 
				S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND 
				S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$AS_OF_DATE' ORDER BY S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE DESC "); 
				
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
				}
				
				if($CONSECUTIVE_DAYS_ABSENT == 0 && $res_abs->RecordCount() > 0)
					$CONSECUTIVE_DAYS_ABSENT++;
					
				*/
				
				/* Ticket #824 */
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
				S_STUDENT_ATTENDANCE.COMPLETED = 1 AND 
				S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$AS_OF_DATE' ORDER BY S_STUDENT_SCHEDULE.SCHEDULE_DATE DESC "); 

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
				S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code) AND 
				S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND 
				S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$AS_OF_DATE' ORDER BY S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE DESC "); 
				/* Ticket #824 */
				
				/* Ticket #1145 */
				$res_sch = $db->Execute("SELECT COUNT(S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE) as NO, SUM(HOURS) as SCHEDULED_HOUR 
				FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE 
				WHERE 
				S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
				PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
				S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
				S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE AND 
				S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$AS_OF_DATE'  "); 

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
					S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($excluded_att_code) AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$AS_OF_DATE' ");
				}
				
				$SCH_COUNT 		= $res_sch->fields['NO'] - $res_exc_hour->fields['EXC_COUNT'];
				$SCHEDULED_HOUR = $res_sch->fields['SCHEDULED_HOUR'] - $res_exc_hour->fields['EXC_HOUR'];
				/* Ticket #1145 */

				$txt 	.= '<tr>
							<td width="17%" >'.$res_course_schedule->fields['STUD_NAME'].'</td>
							<td width="5%"  >'.$res_course_schedule->fields['CAMPUS_CODE'].'</td>
							<td width="7%"  >'.$res_course_schedule->fields['TERM_MASTER'].'</td>						
							<td width="12%" >'.$res_course_schedule->fields['PROGRAM_CODE'].'</td>
							<td width="8%" >'.$res_course_schedule->fields['STUDENT_STATUS'].'</td>
							
							<td width="7%" >'.$res_course_schedule->fields['COURSE_TERM_DATE'].'</td>
							<td width="12%" >'.$res_course_schedule->fields['COURSE_CODE'].' ('. substr($res_course_schedule->fields['SESSION'],0,1).' - '. $res_course_schedule->fields['SESSION_NO'].')'.'</td>
							
							<td width="10%" align="right" >'.$CONSECUTIVE_DAYS_ABSENT.'</td>
							<td width="5%" align="right">'.$res_abs->RecordCount().'</td>
							
							<td width="5%" align="right" >'.$res_last->RecordCount().'</td>
							<td width="6%" align="right" >'.$SCH_COUNT.'</td>
							<td width="6%" align="right" >'.number_format_value_checker(($res_att_hour->fields['ATTENDED_HOUR'] / $SCHEDULED_HOUR * 100),2).' %</td>
						</tr>';
				
				$res_course_schedule->MoveNext();
			}
		}
		
		$txt 	.= '</table>';

		//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Attendance_Daily_Absents_By_Course_'.uniqid().'.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
		/////////////////////////////////////////////////////////////////
	} else if($_POST['FORMAT'] == 2){
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
		$file_name 		= 'Attendance Daily Absents By Course.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$line = 1;
		$index 	= -1;
		$heading[] = 'Student';
		$width[]   = 30;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Session';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 20;
		$heading[] = 'Course Term Start';
		$width[]   = 20;
		$heading[] = 'Course';
		$width[]   = 20;
		$heading[] = 'Session';
		$width[]   = 20;
		$heading[] = 'Session Number';
		$width[]   = 20;
		$heading[] = 'Instructor';
		$width[]   = 20;
		$heading[] = 'Consecutive Days Absent';
		$width[]   = 20;
		$heading[] = 'Absent Count';
		$width[]   = 20;
		$heading[] = 'Present Count';
		$width[]   = 20;
		$heading[] = 'Sched Days';
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
			
			$i++;
		}

		$res_course_schedule = $db->Execute($query." ".$group_by." ".$order_by);
		while (!$res_course_schedule->EOF) { 
			$PK_STUDENT_MASTER 		= $res_course_schedule->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_ENROLLMENT 	= $res_course_schedule->fields['PK_STUDENT_ENROLLMENT'];
			$PK_COURSE_OFFERING		= $res_course_schedule->fields['PK_COURSE_OFFERING'];
			$PK_CAMPUS_PROGRAM		= $res_course_schedule->fields['PK_CAMPUS_PROGRAM'];
			
			//$res_prog = $db->Execute("select CODE, DESCRIPTION  from M_CAMPUS_PROGRAM WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			
			/* Ticket #1145 */
			$res_last = $db->Execute("SELECT IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE = '0000-00-00','', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE, '%m/%d/%Y' )) AS SCHEDULE_DATE 
			FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE 
			WHERE 
			S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
			PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
			S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
			S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE AND 
			S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$AS_OF_DATE' AND 
			S_STUDENT_ATTENDANCE.COMPLETED = 1 AND 
			S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ORDER BY S_STUDENT_SCHEDULE.SCHEDULE_DATE DESC "); 
	
			$res_att_hour = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDED_HOUR 
			FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE 
			WHERE 
			S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
			PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
			S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
			S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE AND 
			S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.COMPLETED = 1 AND 
			S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$AS_OF_DATE'  "); 
			
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
			S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND 
			S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code) AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$AS_OF_DATE' ORDER BY S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE DESC "); 
			
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
				S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND 
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
			}
			*/
			
			/* Ticket #824 */
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
			S_STUDENT_ATTENDANCE.COMPLETED = 1 AND 
			S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$AS_OF_DATE' ORDER BY S_STUDENT_SCHEDULE.SCHEDULE_DATE DESC "); 

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
			S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($absent_att_code) AND 
			S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND 
			S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$AS_OF_DATE' ORDER BY S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE DESC "); 
			/* Ticket #824 */
			
			/* Ticket #1145 */
			$res_sch = $db->Execute("SELECT COUNT(S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE) as NO, SUM(HOURS) as SCHEDULED_HOUR 
			FROM S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE 
			WHERE 
			S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
			PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
			S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
			S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE AND 
			S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$AS_OF_DATE'  "); 

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
				S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($excluded_att_code) AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$AS_OF_DATE' ");
			}
			
			$SCH_COUNT 		= $res_sch->fields['NO'] - $res_exc_hour->fields['EXC_COUNT'];
			$SCHEDULED_HOUR = $res_sch->fields['SCHEDULED_HOUR'] - $res_exc_hour->fields['EXC_HOUR'];
			/* Ticket #1145 */
			
			$line++;
			$index = -1;
			
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
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['PROGRAM_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['STUD_SESSION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['COURSE_TERM_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['COURSE_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['SESSION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['SESSION_NO']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['INSTRUCTOR_NAME']);
		
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CONSECUTIVE_DAYS_ABSENT);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_abs->RecordCount());
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_last->RecordCount());
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SCH_COUNT);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(($res_att_hour->fields['ATTENDED_HOUR'] / $SCHEDULED_HOUR));
			$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
	
			$res_course_schedule->MoveNext();
		}
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
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
	<title><?=MNU_ATTENDANCE_DAILY_ABSENTS_BY_COURSE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_ATTENDANCE_DAILY_ABSENTS_BY_COURSE ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels " method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-2">
											<?=AS_OF_DATE?>
											<input type="text" class="form-control date" id="AS_OF_DATE" name="AS_OF_DATE" value="" >
										</div>
										
										<div class="col-md-2 ">
											<?=GROUP?>
											<select id="GROUP_BY" name="GROUP_BY"  class="form-control" >
												<option value="">No Grouping</option>
												<option value="1">Group By Program</option>
											</select>
										</div>
										
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<!-- New -->
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
								</div>
							</div>
						</div>
					</div>
				</form>
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
		
		search();
	});
	
	function submit_form(val){
		document.getElementById('FORMAT').value = val
		document.form1.submit();
	}
	</script>

</body>

</html>
