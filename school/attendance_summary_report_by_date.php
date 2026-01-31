<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/projected_funds.php");
require_once("check_access.php");

if (check_access('REPORT_REGISTRAR') == 0){
	header("location:../index");
	exit;
}

if (!empty($_REQUEST)) {

	$cond 		= "";
	$date_cond 	= "";
	if($_REQUEST['st'] != '' && $_REQUEST['et'] != '') {
		
		$ST = date("Y-m-d", strtotime($_REQUEST['st']));
		$ET = date("Y-m-d", strtotime($_REQUEST['et']));
		$cond 		.= " AND (S_STUDENT_SCHEDULE.SCHEDULE_DATE BETWEEN '$ST' AND '$ET')";
		$date_cond  .= " AND (S_STUDENT_SCHEDULE.SCHEDULE_DATE BETWEEN '$ST' AND '$ET')";
	} else if ($_REQUEST['st'] != '') {
		$ST = date("Y-m-d", strtotime($_REQUEST['st']));
		$cond 		.= " AND (S_STUDENT_SCHEDULE.SCHEDULE_DATE >= '$ST') ";
		$date_cond  .= " AND (S_STUDENT_SCHEDULE.SCHEDULE_DATE >= '$ST') ";
	} else if ($_REQUEST['et'] != '') {
		$ET = date("Y-m-d", strtotime($_REQUEST['et']));
		$cond 		.= " AND (S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$ET') ";
		$date_cond  .= " AND (S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$ET') ";
	}

	if ($_REQUEST['ENROLLMENT_TYPE'] == 2) {
		$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
	}

	$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER IN (" . $_REQUEST['s_id'] . ") ";

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
	$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
	$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
	$excluded_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$excluded_att_code  = "";
	$exc_att_code_arr = array();
	$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
	while (!$res_exc_att_code->EOF) {
		$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
		$res_exc_att_code->MoveNext();
	}

	$exclude_cond  = "";
	if (!empty($exc_att_code_arr))
		$exclude_cond = " AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE NOT IN (" . implode(",", $exc_att_code_arr) . ") ";

	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if (!empty($_REQUEST['campus'])) {
		$PK_CAMPUS 	 = $_REQUEST['campus'];
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}

	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	while (!$res_campus->EOF) {
		if ($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];

		if ($campus_id != '')
			$campus_id .= ',';
		$campus_id .= $res_campus->fields['PK_CAMPUS'];

		$res_campus->MoveNext();
	}

	$att_com_cond  = " AND S_STUDENT_ATTENDANCE.COMPLETED = 1 ";

	$query = "select CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STU_NAME, STUDENT_ID, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE, SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS, SUM(S_COURSE_OFFERING_SCHEDULE_DETAIL.HOURS) as SCHEDULED_HOURS, M_CAMPUS_PROGRAM.HOURS, PK_STUDENT_COURSE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER, STUDENT_STATUS, S_STUDENT_MASTER.PK_STUDENT_MASTER   
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
	S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  AND IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) $cond 
	GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";
	// echo $query;exit;	

	//echo $cond;exit; 
	if ($_REQUEST['FORMAT'] == 1) {
		/////////////////////////////////////////////////////////////////
		$browser = '';
		if (stripos($_SERVER['HTTP_USER_AGENT'], "chrome") != false)
			$browser =  "chrome";
		else if (stripos($_SERVER['HTTP_USER_AGENT'], "Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		require_once('../global/tcpdf/config/lang/eng.php');
		require_once('../global/tcpdf/tcpdf.php');


		class MYPDF extends TCPDF
		{
			public function Header()
			{
				global $db;

				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

				if ($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".", $res->fields['PDF_LOGO']);
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

				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(9);
				$this->SetTextColor(000, 000, 000);
				$this->SetX(235);
				$this->Cell(55, 8, "Attendance Report By Date Range", 0, false, 'R', 0, '', 0, false, 'M', 'L');

				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(180, 13, 290, 13, $style);
			}
			public function Footer()
			{
				global $db;
				$this->SetY(-15);
				$this->SetX(270);
				$this->SetFont('helvetica', 'I', 7);
				$this->Cell(30, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

				$this->SetY(-15);
				$this->SetX(10);
				$this->SetFont('helvetica', 'I', 7);

				$timezone = $_SESSION['PK_TIMEZONE'];
				if ($timezone == '' || $timezone == 0) {
					$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					$timezone = $res->fields['PK_TIMEZONE'];
					if ($timezone == '' || $timezone == 0)
						$timezone = 4;
				}

				$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
				$date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $res->fields['TIMEZONE'], date_default_timezone_get());

				$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
			}
		}

		$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(7, 13, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 20);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 8, '', true);

		if ($_REQUEST['ENROLLMENT_TYPE'] == 1)
			$ENROLL_TYPE = "All Enrollments";
		else
			$ENROLL_TYPE = "Current Enrollments";

		$INACTIVE_ATT_LBL = "Exclude Inactive Attendance Code: ";
		if ($_REQUEST['exc_inactive'] == 1)
			$INACTIVE_ATT_LBL .= "Yes";
		else
			$INACTIVE_ATT_LBL .= "No";

		$pdf->AddPage();

		$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
		if ($EXPECTED_GRAD_DATE != '0000-00-00')
			$EXPECTED_GRAD_DATE = date("m/d/Y", strtotime($EXPECTED_GRAD_DATE));
		else
			$EXPECTED_GRAD_DATE = '';

		$str = "";
		if ($_REQUEST['min'] != '' && $_REQUEST['max'] != '')
			$str = "Percentage Between: " . $_REQUEST['min'] . ' - ' . $_REQUEST['max'];
		else if ($_REQUEST['min'] != '')
			$str = "Percentage From: " . $_REQUEST['min'];
		else if ($_REQUEST['max'] != '')
			$str = "Percentage To: " . $_REQUEST['max'];



			$date_string  = 'All Dates'; 
			// print_r($_REQUEST['START_DATE_analysis']);

			
			if ($_REQUEST['st'] != '' && $_REQUEST['et'] != '') {
				$date_string =  'Dates Between ' . date("m/d/Y", strtotime($_REQUEST['st'])) . ' and ' . date("m/d/Y", strtotime($_REQUEST['et']));
			} else if ($_REQUEST['st'] != '' && $_REQUEST['et'] == '') {
				$date_string =  'Dates From ' . date("m/d/Y", strtotime($_REQUEST['st']));
			} else if ($_REQUEST['st'] == '' && $_REQUEST['et'] != '') {
				$date_string =  'Dates Till ' . date("m/d/Y", strtotime($_REQUEST['et']));
			}

		$txt = '<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<thead>
						<tr>
							<td width="100%" align="right">
								Campus: ' . $campus_name . '
							</td>
						</tr>
						<tr>
							<td width="100%" align="right">
								'.$date_string.'
							</td>
						</tr>';

		if ($str != '') {
			$txt .= '<tr>
										<td width="100%" align="right">' . $str . '</td>
									</tr>';
		}

		$txt .= '<tr>
							<td width="100%" align="right">' . $ENROLL_TYPE . '</td>
						</tr>
						<tr>
							<td width="100%" align="right">' . $INACTIVE_ATT_LBL . '<br /></td>
						</tr>
						<tr>
							<td width="3%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;">
								<br /><br /><b><i>#</i></b>
							</td>
							<td width="14%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;">
								<br /><br /><b><i>Student</i></b>
							</td>
							<td width="10%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;">
								<br /><br /><b><i>Student ID</i></b>
							</td>
							<td width="10%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;">
								<br /><br /><b><i>Program</i></b>
							</td>
							<td width="7%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;">
								<b><i>First Term Date</i></b>
							</td>
							<td width="12%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;">
								<br /><br /><b><i>Status</i></b>
							</td>
							<td width="8%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" align="right" >
								<b><i>Program<br />Hours</i></b>
							</td>
							<td width="9%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" align="right" >
								<b><i>Total<br />Non Scheduled</i></b>
							</td>
							<td width="7%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" align="right" >
								<b><i>Total<br />Attended</i></b>
							</td>
							<td width="7%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" align="right" >
								<b><i>Total<br />Scheduled</i></b>
							</td>
							<td width="7%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" align="right" >
								<b><i>Cumulative<br />Attendance</i></b>
							</td>
							<td width="7%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >
								<br /><br /><b><i>Percentage</i></b>
							</td>
						</tr>
					</thead>
					<tbody>';

		$res = $db->Execute($query);
		$row_index = 1;
		while (!$res->EOF) {
			$PK_STUDENT_COURSE 		= $res->fields['PK_STUDENT_COURSE'];
			$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];

			$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
			if ($_REQUEST['ENROLLMENT_TYPE'] == 1) {
				$stud_cond = " AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";
			} else {
				$stud_cond = " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
			}

			$res_att = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  $att_com_cond $stud_cond $date_cond");

			$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $stud_cond $date_cond ");

			$cond1 = "";
			if ($_REQUEST['date'] != '')
				$cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '" . date("Y-m-d", strtotime($_REQUEST['date'])) . "' ";

			//$res_s = $db->Execute("SELECT SUM(S_STUDENT_SCHEDULE.HOURS) as SCHEDULED_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  $cond1  AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $exclude_cond");
			//$SCHEDULED_HOURS = $res_s->fields['SCHEDULED_HOURS'];

			$SCHEDULED_HOURS 	 = 0;
			$COMP_SCHEDULED_HOUR = 0;
			$res_sch = $db->Execute("SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $stud_cond  $cond1 $date_cond");
			while (!$res_sch->EOF) {
				$exc_att_flag = 0;
				foreach ($exc_att_code_arr as $exc_att_code) {
					if ($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
						$exc_att_flag = 1;
						break;
					}
				}

				if (($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 || $_REQUEST['exc_inactive'] == 0) && $exc_att_flag == 0) {
					if ($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2 || $_REQUEST['incomplete'] == 1) {
						$SCHEDULED_HOURS	 += $res_sch->fields['HOURS'];
						$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];
					}
				}

				$res_sch->MoveNext();
			}

			/* Ticket # 1600  */
			$att_per = ($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']) / $COMP_SCHEDULED_HOUR * 100;
			$att_per = str_replace(",", "", number_format_value_checker($att_per, 2));
			$flag = 1;
			if ($_REQUEST['min'] != '') {
				if ($att_per >= $_REQUEST['min']) {
				} else
					$flag = 0;
			}

			if ($_REQUEST['max'] != '') {
				if ($att_per <= $_REQUEST['max']) {
				} else
					$flag = 0;
			}

			if ($flag == 1) {
				$txt .= '<tr>
									<td width="3%" style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $row_index . '</td>
									<td width="14%" style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res->fields['STU_NAME'] . '</td>
									<td width="10%" style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res->fields['STUDENT_ID'] . '</td>
									<td width="10%" style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res->fields['PROGRAM_TRANSCRIPT_CODE'] . '</td>
									
									<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res->fields['TERM_MASTER'] . '</td>
									<td width="12%" style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res->fields['STUDENT_STATUS'] . '</td>
									
									<td width="8%" style="border-left:1px solid #000;border-bottom:1px solid #000;" align="right" >' . number_format_value_checker($res->fields['HOURS'], 2) . '</td>
									<td width="9%" style="border-left:1px solid #000;border-bottom:1px solid #000;" align="right" >' . number_format_value_checker($res_ns->fields['ATTENDANCE_HOURS'], 2) . '</td>
									<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" align="right" >' . number_format_value_checker($res_att->fields['ATTENDANCE_HOURS'], 2) . '</td>
									<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" align="right" >' . number_format_value_checker($SCHEDULED_HOURS, 2) . '</td>
									<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" align="right" >' . number_format_value_checker(($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']), 2) . '</td>
									<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >' . number_format_value_checker($att_per, 2) . '%</td>
								</tr>';
								$row_index = $row_index+1;
			}
			/* Ticket # 1600  */

			$res->MoveNext();
		}
		$txt .= '</table>';

		//echo $txt;exit;
		$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

		$file_name = 'Attendance_Report_By_Date_Range_'.uniqid().'.pdf';
		/*
		if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');
		*/
		$outputFileName = $dir . $file_name;
		$outputFileName = str_replace(
			pathinfo($outputFileName, PATHINFO_FILENAME),
			pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
			$outputFileName
		);
		$pdf->Output('temp/' . $outputFileName, 'F');
		echo json_encode(['filename'=> $outputFileName ,'path'=>'temp/' . $outputFileName]);
		/////////////////////////////////////////////////////////////////
	} else if ($_REQUEST['FORMAT'] == 2) {
 
		include '../global/excel/Classes/PHPExcel/IOFactory.php';
		$cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

		$total_fields = 120;
		for ($i = 0; $i <= $total_fields; $i++) {
			if ($i <= 25)
				$cell[] = $cell1[$i];
			else {
				$j = floor($i / 26) - 1;
				$k = ($i % 26);
				//echo $j."--".$k."<br />";
				$cell[] = $cell1[$j] . $cell1[$k];
			}
		}

		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'Attendance Report By Date Range.xlsx';
		$outputFileName =  $file_name;
		$outputFileName = str_replace(
			pathinfo($outputFileName, PATHINFO_FILENAME),
			pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
			$outputFileName
		);

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

		$i = 0;
		foreach ($heading as $title) {
			$index++;
			$cell_no = $cell[$index] . $line;
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
			if ($_REQUEST['ENROLLMENT_TYPE'] == 1) {
				$stud_cond = " AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";
			} else {
				$stud_cond = " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
			}

			$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($campus_id) ");
			/* Ticket # 1247 */

			$res_att = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  $att_com_cond $stud_cond $date_cond"); //Ticket # 1247

			$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $stud_cond $date_cond "); //Ticket # 1247

			$cond1 = "";
			if ($_REQUEST['date'] != '')
				$cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '" . date("Y-m-d", strtotime($_REQUEST['date'])) . "' ";

			//$res_s = $db->Execute("SELECT SUM(S_STUDENT_SCHEDULE.HOURS) as SCHEDULED_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  $cond1  AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $exclude_cond");
			//$SCHEDULED_HOURS = $res_s->fields['SCHEDULED_HOURS'];

			$SCHEDULED_HOURS 	 = 0;
			$COMP_SCHEDULED_HOUR = 0;
			$res_sch = $db->Execute("SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $stud_cond  $cond1 $date_cond "); //Ticket # 1247
			while (!$res_sch->EOF) {
				$exc_att_flag = 0;
				foreach ($exc_att_code_arr as $exc_att_code) {
					if ($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
						$exc_att_flag = 1;
						break;
					}
				}

				/* Ticket # 1247 */
				if (($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 || $_REQUEST['exc_inactive'] == 0) && $exc_att_flag == 0) {
					if ($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2 || $_REQUEST['incomplete'] == 1) {
						$SCHEDULED_HOURS	 += $res_sch->fields['HOURS'];
						$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];
					}
				}
				/* Ticket # 1247 */

				$res_sch->MoveNext();
			}

			/* Ticket # 1600  */
			if ($COMP_SCHEDULED_HOUR > 0 && ($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']) > 0) {
				$att_per 	= (($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']) / $COMP_SCHEDULED_HOUR);
				$att_per1 	= str_replace(",", "", number_format_value_checker(($att_per * 100), 2));
			} else {
				$att_per  = 0;
				$att_per1 = 0;
			}

			$flag = 1;
			if ($_REQUEST['min'] != '') {
				if ($att_per1 >= $_REQUEST['min']) {
				} else
					$flag = 0;
			}

			if ($_REQUEST['max'] != '') {
				if ($att_per1 <= $_REQUEST['max']) {
				} else
					$flag = 0;
			}

			if ($flag == 1) {

				$line++;
				$index = -1;

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_NAME']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_campus->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TERM_MASTER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_TRANSCRIPT_CODE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res->fields['HOURS'], 2));

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_ns->fields['ATTENDANCE_HOURS'], 2));

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_att->fields['ATTENDANCE_HOURS'], 2));

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($SCHEDULED_HOURS, 2));

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']));

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($att_per);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
			}
			/* Ticket # 1600  */

			$res->MoveNext();
		}

		$objWriter->save('temp/'.$outputFileName);
		$objPHPExcel->disconnectWorksheets();
		echo json_encode(['filename'=> $outputFileName ,'path'=>'temp/'.$outputFileName]);
		// header("location:" . $outputFileName);
	}
}
