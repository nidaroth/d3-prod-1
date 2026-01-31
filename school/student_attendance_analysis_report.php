<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_attendance_analysis_report.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST) || !empty($_GET)){
	//echo "<pre>";print_r($_POST);exit;
	
	/* Ticket #1194 */
	if($_GET['FORMAT'] != ''){
		$_POST['FORMAT'] = $_GET['FORMAT'];
	} else
		$_POST['FORMAT'] = 1;
	/* Ticket #1194 */
	
	/* Ticket #1145 */
	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
	$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
	$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
	
	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
	$excluded_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
	
	/* Ticket # 1219 */
	$excluded_att_code  = "";
	$exc_att_code_arr = array();
	$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
	while (!$res_exc_att_code->EOF) {
		$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
		$res_exc_att_code->MoveNext();
	}

	$exclude_cond  = "";
	if(!empty($exc_att_code_arr))
		$exclude_cond = " AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE NOT IN (".implode(",",$exc_att_code_arr).") ";
	/* Ticket # 1219 */
	
	$cond = "";
	if($_POST['START_DATE'] != '')
		$cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_POST['START_DATE']))."' ";
	if($_POST['SELECT_ENROLLMENT'] == 2)
		$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
	/* Ticket #1145 */
	
	if($_GET['id'] != ''){
		$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '".$_GET['id']."' ";
		
		if($_GET['date'] != '')
			$cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_GET['date']))."' ";
		
		if($_GET['eid'] != ''){
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
		} else {
			if($_GET['type'] == 2)
				$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
		}
	}
	
	/* Ticket # 1247 */
	if($_GET['ENROLLMENT_TYPE'] != '') {
		$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
	}
	
	if($_GET['s_id'] != '') {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER IN ($_GET[s_id]) ";
	}
	
	if($_GET['date'] != '')
		$cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_GET['date']))."' ";
		
	if($_GET['incomplete'] != 1) {
		$complete_cond = " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1  ";
		$att_com_cond  = " AND S_STUDENT_ATTENDANCE.COMPLETED = 1 ";
	} else {
		$complete_cond = "";
		$att_com_cond  = "";
	}
	/* Ticket # 1247 */
	
	/* Ticket #1145 */
	
	// Ticket # 1247
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
	
	$query = "select CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STU_NAME, STUDENT_ID, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE, SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS, SUM(S_COURSE_OFFERING_SCHEDULE_DETAIL.HOURS) as SCHEDULED_HOURS, M_CAMPUS_PROGRAM.HOURS, PK_STUDENT_COURSE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER, STUDENT_STATUS, S_STUDENT_MASTER.PK_STUDENT_MASTER   
	FROM 
	S_STUDENT_MASTER, S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT 
	LEFT JOIN S_TERM_MASTER ON S_STUDENT_ENROLLMENT.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	,S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE 
	LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL $complete_cond 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND S_STUDENT_ATTENDANCE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($campus_id) ) AND 
	S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) $cond 
	GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";
	
	if($_POST['FORMAT'] == 1){ /* Ticket #1194 */
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
				global $db, $campus_name;
				
				/* Ticket # 1588 */
				if($_GET['id'] != ''){
					$CONTENT = pdf_custom_header($_GET['id'], $_GET['eid'], 1);
					$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
					$this->SetMargins('', 45, '');
					
				} else {
					$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
					
					if($res->fields['PDF_LOGO'] != '') {
						$ext = explode(".",$res->fields['PDF_LOGO']);
						$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
					}
					
					$this->SetFont('helvetica', '', 15);
					$this->SetY(6);
					$this->SetX(85);
					$this->SetTextColor(000, 000, 000);
					$this->MultiCell(100, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
					
					/*$this->SetFont('helvetica', '', 8);
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
					$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');*/
				}
				/* Ticket # 1588 */
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(9);
				$this->SetTextColor(000, 000, 000);
				$this->SetX(235);
				$this->Cell(55, 8, "Attendance Analysis", 0, false, 'R', 0, '', 0, false, 'M', 'L');

				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(200, 13, 290, 13, $style);
			
				$START_DATE = '';
				if($_POST['START_DATE'] != '' )
					$START_DATE = $_POST['START_DATE'];
				else
					$START_DATE = $_GET['date'];
				
				/* Ticket # 1247 */
				$str = '';
				if($_REQUEST['ENROLLMENT_TYPE'] == 1)
					$str = 'All Enrollments: ';
				else if($_REQUEST['ENROLLMENT_TYPE'] == 2)
					$str = 'Current Enrollments: ';
					
				if($START_DATE != '')
					$str .= "As of ".$START_DATE;

				$this->SetFont('helvetica', 'I', 13);
				$this->SetY(16);
				$this->SetTextColor(000, 000, 000);
				$this->SetX(235);
				$this->Cell(55, 7, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(20);
				$this->SetX(137);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(152, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
				/* Ticket # 1247 */
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
		$pdf->SetFont('helvetica', '', 8, '', true);
		$pdf->AddPage();

		$txt .= '<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<thead>
						<tr>
							<td width="14%" style="border-bottom:1px solid #000;">
								<br /><br /><b><i>Student</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<br /><br /><b><i>Student ID</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<br /><br /><b><i>Program</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;">
								<b><i>First Term Date</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<br /><br /><b><i>Status</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >
								<b><i>Program<br />Hours</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >
								<b><i>Total<br />Transferred</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >
								<b><i>Total<br />Non Scheduled</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Total<br />Attended</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Total<br />Scheduled</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Cumulative<br />Attendance</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;" align="right" >
								<br /><br /><b><i>Percentage</i></b>
							</td>';
							
							if($_GET['gpa'] == 1) {
								$txt .= '<td width="5%" style="border-bottom:1px solid #000;" align="right" >
										<br /><br /><b><i>GPA</i></b>
									</td>';
							}
					$txt .= '</tr>
					</thead>';

				$res = $db->Execute($query);
				while (!$res->EOF) { 
						$PK_STUDENT_COURSE 		= $res->fields['PK_STUDENT_COURSE'];
						$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];
						
						
						/* Ticket #1145 */
						/* Ticket # 1219 */
						
						/* Ticket # 1247 */
						$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
						if($_GET['ENROLLMENT_TYPE'] == 1) {
							$stud_cond 	= " AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";
							$tc_cond	= " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";
						} else {
							$stud_cond 	= " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
							$tc_cond	= " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
						}
						/* Ticket # 1247 */

						$cond1 = "";
						if($_GET['date'] != '')
							$cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_GET['date']))."' ";
						
						$res_att = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  $att_com_cond $stud_cond $cond1 "); //Ticket # 1247
						
						$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $stud_cond $cond1 "); //Ticket # 1247
							
						//$res_s = $db->Execute("SELECT SUM(S_STUDENT_SCHEDULE.HOURS) as SCHEDULED_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  $cond1  AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $exclude_cond");
						$SCHEDULED_HOURS = $res_s->fields['SCHEDULED_HOURS'];
						
						$SCHEDULED_HOURS 	 = 0;
						$COMP_SCHEDULED_HOUR = 0;
						$res_sch = $db->Execute("SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $stud_cond  $cond1"); //Ticket # 1247
						while (!$res_sch->EOF) { 
							$exc_att_flag = 0;
							foreach($exc_att_code_arr as $exc_att_code) {
								if($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
									$exc_att_flag = 1;
									break;
								}
							}
							
							/* Ticket # 1247 */
							if($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0){
								if($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2 || $_GET['incomplete'] == 1) { 
									$SCHEDULED_HOURS	 += $res_sch->fields['HOURS'];
									$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];	
								}
							}	
							/* Ticket # 1247 */
							
							$res_sch->MoveNext();
						}
						
						$res_tc = $db->Execute("SELECT SUM(HOUR) as HOUR FROM S_STUDENT_CREDIT_TRANSFER, M_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS AND SHOW_ON_TRANSCRIPT = 1 $tc_cond ");
						
						$txt .= '<tr>
								<td width="14%" >'.$res->fields['STU_NAME'].'</td>
								<td width="8%" >'.$res->fields['STUDENT_ID'].'</td>
								<td width="8%" >'.$res->fields['PROGRAM_TRANSCRIPT_CODE'].'</td>
								
								<td width="7%" >'.$res->fields['TERM_MASTER'].'</td>
								<td width="8%" >'.$res->fields['STUDENT_STATUS'].'</td>
								
								<td width="8%" style="border-right:1px solid #000;" align="right" >'.number_format_value_checker($res->fields['HOURS'],2).'</td>
								<td width="7%" style="border-right:1px solid #000;" align="right" >'.number_format_value_checker($res_tc->fields['HOUR'],2).'</td>
								<td width="8%" style="border-right:1px solid #000;" align="right" >'.number_format_value_checker($res_ns->fields['ATTENDANCE_HOURS'],2).'</td>
								<td width="7%" align="right" >'.number_format_value_checker($res_att->fields['ATTENDANCE_HOURS'],2).'</td>
								<td width="7%" align="right" >'.number_format_value_checker($SCHEDULED_HOURS,2).'</td>
								<td width="7%" align="right" >'.number_format_value_checker(($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']),2).'</td>
								<td width="7%" align="right" >'.number_format_value_checker((($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']) / $COMP_SCHEDULED_HOUR * 100),2).'%</td>';
								
								if($_GET['gpa'] == 1) {
									$gpa_cond = "";
									if($_GET['ENROLLMENT_TYPE'] == 2) {
										$gpa_cond = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
									}
									$Denominator 	= 0;
									$Numerator 		= 0;
									$Numerator1 	= 0;

									/* DIAM-1540 */
									$summation_of_gpa    = 0;
									$summation_of_weight = 0;
									/* End DIAM-1540 */

									$res_course = $db->Execute("SELECT 
																	NUMERIC_GRADE, 
																	COURSE_UNITS, 
																	NUMBER_GRADE,
																	CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
																	S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
																	)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
																	CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
																	S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
																	) ELSE 0 END AS GPA_WEIGHT
																FROM 
																	S_STUDENT_COURSE, 
																	M_COURSE_OFFERING_STUDENT_STATUS, 
																	S_COURSE_OFFERING, 
																	S_COURSE, 
																	S_TERM_MASTER, 
																	M_SESSION, 
																	S_GRADE
																WHERE 
																	S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
																	AND S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $gpa_cond 
																	AND S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER 
																	AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
																	AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
																	AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE 
																	AND M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
																	AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
																	AND S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
																	AND SHOW_ON_TRANSCRIPT = 1 
																	AND CALCULATE_GPA = 1 "); /* DIAM-1540, added GPA_VALUE, GPA_WEIGHT */
									while (!$res_course->EOF) {
										$Denominator += $res_course->fields['COURSE_UNITS'];
										$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
										$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];

										/* DIAM-1540 */
										$GPA_VALULE 		  = $res_course->fields['GPA_VALUE'];
										$GPA_WEIGHT 		  = $res_course->fields['GPA_WEIGHT']; 
										$summation_of_gpa     += $GPA_VALULE;
										$summation_of_weight  += $GPA_WEIGHT;
										/* End DIAM-1540 */
										
										$res_course->MoveNext();
									}
									
									$txt .= '<td width="5%" align="right" >'.number_format_value_checker(($summation_of_gpa/$summation_of_weight),2).'</td>';
								}
							$txt .= '</tr>';
						
					$res->MoveNext();
				}
				/* Ticket # 1219 */
				
				$txt .= '</table>';
				
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Attendance_Analysis_'.uniqid().'.pdf';
		/*
		if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');
		*/
		$pdf->Output('temp/'.$file_name, 'FD');

		return $file_name;
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
		$file_name 		= 'Attendance Analysis.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				
		$line 	= 1;
		$index 	= -1;
		$heading[] = 'Student';
		$width[]   = 30;
		$heading[] = 'Student ID';
		$width[]   = 15;
		$heading[] = 'Campus';
		$width[]   = 15;
		$heading[] = 'First Term Date';
		$width[]   = 15;
		$heading[] = 'Program';
		$width[]   = 15;
		$heading[] = 'Status';
		$width[]   = 15;
		$heading[] = 'Program Hours';
		$width[]   = 15;
		$heading[] = 'Total Non Scheduled';
		$width[]   = 15;
		$heading[] = 'Total Attended';
		$width[]   = 15;
		$heading[] = 'Total Scheduled';
		$width[]   = 15;
		$heading[] = 'Cumulative Attendance';
		$width[]   = 15;
		$heading[] = 'Percentage';
		$width[]   = 15;
		
		if($_GET['gpa'] == 1) {
			$heading[] = 'GPA';
			$width[]   = 15;
		}
		
		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			
			$i++;
		}	

		$objPHPExcel->getActiveSheet()->freezePane('A1');
		
		$res = $db->Execute($query);
		while (!$res->EOF) { 
			$PK_STUDENT_COURSE 		= $res->fields['PK_STUDENT_COURSE'];
			$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];
			
			/* Ticket #1145 */
			
			/* Ticket # 1247 */
			$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
			if($_GET['ENROLLMENT_TYPE'] == 1) {
				$stud_cond = " AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";
			} else {
				$stud_cond = " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
			}
			
			$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($campus_id) ");
			/* Ticket # 1247 */

			$cond1 = "";
			if($_GET['date'] != '')
				$cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_GET['date']))."' ";
			
			$res_att = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  $att_com_cond $stud_cond $cond1 "); //Ticket # 1247
						
			$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $stud_cond $cond1 "); //Ticket # 1247
				
			//$res_s = $db->Execute("SELECT SUM(S_STUDENT_SCHEDULE.HOURS) as SCHEDULED_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  $cond1  AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $exclude_cond");
			$SCHEDULED_HOURS = $res_s->fields['SCHEDULED_HOURS'];
			
			$SCHEDULED_HOURS 	 = 0;
			$COMP_SCHEDULED_HOUR = 0;
			$res_sch = $db->Execute("SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $stud_cond  $cond1"); //Ticket # 1247
			while (!$res_sch->EOF) { 
				$exc_att_flag = 0;
				foreach($exc_att_code_arr as $exc_att_code) {
					if($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
						$exc_att_flag = 1;
						break;
					}
				}
				
				/* Ticket # 1247 */
				if($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0){
					if($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2 || $_GET['incomplete'] == 1) { 
						$SCHEDULED_HOURS	 += $res_sch->fields['HOURS'];
						$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];	
					}
				}	
				/* Ticket # 1247 */
				
				$res_sch->MoveNext();
			}
			
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_campus->fields['CAMPUS_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TERM_MASTER']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_TRANSCRIPT_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res->fields['HOURS'],2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_ns->fields['ATTENDANCE_HOURS'],2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_att->fields['ATTENDANCE_HOURS'],2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($SCHEDULED_HOURS,2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue((($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']) / $COMP_SCHEDULED_HOUR));
			$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
			
			if($_GET['gpa'] == 1) {
				$gpa_cond = "";
				if($_GET['ENROLLMENT_TYPE'] == 2) {
					$gpa_cond = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
				}
				$Denominator 	= 0;
				$Numerator 		= 0;
				$Numerator1 	= 0;

				/* DIAM-1540 */
				$summation_of_gpa    = 0;
				$summation_of_weight = 0;
				/* End DIAM-1540 */

				$res_course = $db->Execute("SELECT NUMERIC_GRADE, COURSE_UNITS, NUMBER_GRADE,
				CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
				CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC) ELSE 0 END AS GPA_WEIGHT  
				FROM
				S_STUDENT_COURSE, M_COURSE_OFFERING_STUDENT_STATUS, S_COURSE_OFFERING, S_COURSE, S_TERM_MASTER, M_SESSION, S_GRADE
				WHERE 
				S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
				S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $gpa_cond AND 
				S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
				S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
				S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
				S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
				M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION AND 
				M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND 
				S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE AND 
				SHOW_ON_TRANSCRIPT = 1 AND CALCULATE_GPA = 1"); /* DIAM-1540, added GPA_VALUE, GPA_WEIGHT */
				while (!$res_course->EOF) {
					$Denominator += $res_course->fields['COURSE_UNITS'];
					$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
					$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];

					/* DIAM-1540 */
					$GPA_VALULE 		  = $res_course->fields['GPA_VALUE'];
					$GPA_WEIGHT 		  = $res_course->fields['GPA_WEIGHT']; 
					$summation_of_gpa     += $GPA_VALULE;
					$summation_of_weight  += $GPA_WEIGHT;
					/* End DIAM-1540 */
					
					$res_course->MoveNext();
				}
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker(($summation_of_gpa/$summation_of_weight),2));
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00); /* DIAM-1540 */
			}

			$res->MoveNext();
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
	<title><?=MNU_STUDENT_ATTENDANCE_ANALYSIS_REPORT?> | <?=$title?></title>
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
							<?=MNU_STUDENT_ATTENDANCE_ANALYSIS_REPORT?>
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
											<?=SELECT_ENROLLMENT?>
											<select id="SELECT_ENROLLMENT" name="SELECT_ENROLLMENT" class="form-control" >
												<option value="1" >All Enrollments</option>
												<option value="2" >Current Enrollments</option>
											</select>
										</div>
										<div class="col-md-2">
											<?=AS_OF_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="">
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
	
	</script>

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