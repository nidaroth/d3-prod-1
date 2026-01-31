<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");
error_reporting(0);
if (check_access('REPORT_REGISTRAR') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST) || !empty($_GET)) { //Ticket # 1194  
	// echo "<pre>";print_r($_POST);exit;
	$cond = "";

	/* Ticket # 1194   */
	if (!empty($_GET)) {
		$_POST['PRINT_TYPE'] 			= $_GET['pt'];
		$_POST['PK_COURSE_OFFERING'] 	= explode(",", $_GET['co']);
		$_POST['INSTRUCTOR'] 			= explode(",", $_GET['ins']);
		$_POST['PK_TERM_MASTER'] 		= $_GET['tm'];
		$_POST['FORMAT'] 				= 1;
	}
	/* Ticket # 1194   */

	if ($_POST['PRINT_TYPE'] == 1) {
		$PK_COURSE_OFFERING = implode(",", $_POST['PK_COURSE_OFFERING']);
		$cond .= " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($PK_COURSE_OFFERING) ";
	} else if ($_POST['PRINT_TYPE'] == 2) {
		$INSTRUCTOR = implode(",", $_POST['INSTRUCTOR']);
		$cond .= " AND S_COURSE_OFFERING.PK_TERM_MASTER = '$_POST[PK_TERM_MASTER]' AND INSTRUCTOR IN ($INSTRUCTOR) ";
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

	$group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
	$order_by = " ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";

	if ($_POST['FORMAT'] == 1) {
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

				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

				if ($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".", $res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}

				$this->SetFont('helvetica', '', 15);
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');

				$this->SetFont('helvetica', 'I', 14);
				$this->SetY(9);
				$this->SetX(160);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Attendance Roster", 0, false, 'L', 0, '', 0, false, 'M', 'L');

				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(140, 13, 202, 13, $style);
			}
			public function Footer()
			{
				global $db;
				$this->SetY(-15);
				$this->SetX(180);
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

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(2, 31, 2);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 6, '', true);
		// 		echo ">>";
		// print_r($PK_COURSE_OFFERING_ARR);
		$wkhtml = '';
		foreach ($PK_COURSE_OFFERING_ARR as $PK_COURSE_OFFERING) {
			$pdf->AddPage();
			$txt = '';

			/*
			$res_cs = $db->Execute("select DATE_FORMAT(DEF_START_TIME,'%h:%i %p') AS START_TIME, DATE_FORMAT(DEF_END_TIME,'%h:%i %p') AS END_TIME, HOURS, CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO,FA_UNITS,  UNITS, CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,' ',S_EMPLOYEE_MASTER_INST.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME,ATTENDANCE_TYPE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 , IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.END_DATE, '%m/%d/%Y' )) AS  END_DATE_1,SESSION,SESSION_NO, COURSE_OFFERING_STATUS, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(S_COURSE_OFFERING_SCHEDULE.START_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.START_DATE, '%m/%d/%Y' )) AS  SCHEDULE_START_DATE, S_COURSE_OFFERING_SCHEDULE.END_DATE AS SCHEDULE_END_DATE from 
			S_COURSE_OFFERING 
			LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
			LEFT JOIN M_ATTENDANCE_TYPE ON M_ATTENDANCE_TYPE.PK_ATTENDANCE_TYPE = S_COURSE_OFFERING.PK_ATTENDANCE_TYPE 
			LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
			LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
			LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR 
			LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM
			, S_COURSE_OFFERING_SCHEDULE 
			,S_COURSE 
			WHERE 
			S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
			S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
			S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE");
			*/

			$res_cs = $db->Execute("select DATE_FORMAT(DEF_START_TIME,'%h:%i %p') AS START_TIME, DATE_FORMAT(DEF_END_TIME,'%h:%i %p') AS END_TIME, HOURS, CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO,FA_UNITS,  UNITS, CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,' ',S_EMPLOYEE_MASTER_INST.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME,ATTENDANCE_TYPE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 , IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.END_DATE, '%m/%d/%Y' )) AS  END_DATE_1,SESSION,SESSION_NO, COURSE_OFFERING_STATUS, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(S_COURSE_OFFERING_SCHEDULE.START_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.START_DATE, '%m/%d/%Y' )) AS  SCHEDULE_START_DATE, S_COURSE_OFFERING_SCHEDULE.END_DATE AS SCHEDULE_END_DATE from 
			S_COURSE_OFFERING 
			LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
			LEFT JOIN M_ATTENDANCE_TYPE ON M_ATTENDANCE_TYPE.PK_ATTENDANCE_TYPE = S_COURSE_OFFERING.PK_ATTENDANCE_TYPE 
			LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
			LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
			LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR 
			LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM
			LEFT JOIN S_COURSE_OFFERING_SCHEDULE ON S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
			,S_COURSE 
			WHERE 
			S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
			S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE");

			if ($res_cs->fields['SCHEDULE_START_DATE'] == '') {
				$res_det = $db->Execute("select IF(MIN(SCHEDULE_DATE) = '0000-00-00','',DATE_FORMAT(MIN(SCHEDULE_DATE), '%m/%d/%Y' )) AS SCHEDULE_DATE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'  ");
				$SCHEDULE_START_DATE = $res_det->fields['SCHEDULE_DATE'];
			} else
				$SCHEDULE_START_DATE = $res_cs->fields['SCHEDULE_START_DATE'];

			if ($res_cs->fields['SCHEDULE_START_DATE'] == '') {
				$res_det = $db->Execute("select IF(MAX(SCHEDULE_DATE) = '0000-00-00','',DATE_FORMAT(MAX(SCHEDULE_DATE), '%m/%d/%Y' )) AS SCHEDULE_DATE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'  ");
				$SCHEDULE_END_DATE = $res_det->fields['SCHEDULE_DATE'];
			} else
				$SCHEDULE_END_DATE = $res_cs->fields['SCHEDULE_END_DATE'];

			$start = 0;
			$START_DATE = date("Y-m-d", strtotime('last sunday this week', strtotime($SCHEDULE_START_DATE)));
			$END_DATE 	= $SCHEDULE_END_DATE;
			if (isset($_REQUEST['START_DATE_NEW']) && $_REQUEST['START_DATE_NEW'] != '') {
				// echo  "<br>";
				// echo  $_REQUEST['START_DATE_NEW']."<br>";
				// echo date("Y-m-d" , strtotime($_REQUEST['START_DATE_NEW']))."<br>";
				// echo 
				$START_DATE = date("Y-m-d", strtotime('last sunday this week', strtotime($_REQUEST['START_DATE_NEW'])));

				// exit;
			}
			if (isset($_REQUEST['AS_OF_DATE']) && $_REQUEST['AS_OF_DATE'] != '') {
				$END_DATE = $_REQUEST['AS_OF_DATE'];
				// echo 
				// exit;
			}
			$END_DATE = date("Y-m-d", strtotime('saturday this week', strtotime($END_DATE)));

			$txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="33%" style="font-size:35px" ><b>Instructor: </b>' . $res_cs->fields['INSTRUCTOR_NAME'] . '</td>
							<td width="33%" style="font-size:35px" ><b>Room: </b>' . $res_cs->fields['ROOM_NO'] . '</td>
							<td width="34%" style="font-size:35px" ><b>Class Start: </b>' . $res_sch->fields['START_TIME'] . ' ' . $SCHEDULE_START_DATE . '</td>
						</tr>
					</table><br /><br />';
			$mno = 0;
			for (; strtotime($START_DATE) <= strtotime($END_DATE);) {
				$flag2 = $flag3 = $flag4 = false;
				if ($mno > 0)
					$txt .= '<br /><br /><br /><br />';

				$txt .= '<table border="0" cellspacing="0" cellpadding="1" width="100%">
							<thead>
								<tr>
									<td width="17.96%" style="border-top:1px solid #000;border-right:1px solid #000;;border-left:1px solid #000;" ><b>Course: ' . $res_cs->fields['TRANSCRIPT_CODE'] . '</b></td>';

				$header_size = 20.5;
				if ($_REQUEST['AR_DAYS'] == 'mon_fri')
					$header_size = 14.64;

				$txt .= '<td width="' . $header_size . '%" style="border-top:1px solid #000;border-right:1px solid #000;text-align:center;" >
												' . date("M d", strtotime($START_DATE)) . ' - ' . date("M d", strtotime($START_DATE . ' + 6 days')) . '
											</td>';
				if (strtotime($START_DATE . ' + 7 days') <= strtotime($END_DATE)) {
					$txt .= '<td width="' . $header_size . '%" style="border-top:1px solid #000;border-right:1px solid #000;text-align:center;" >' . date("M d", strtotime($START_DATE . ' + 7 days')) . ' - ' . date("M d", strtotime($START_DATE . ' + 13 days')) . '</td>';
					$flag2 = true;
				}

				if (strtotime($START_DATE . ' + 14 days') <= strtotime($END_DATE)) {
					$txt .= '<td width="' . $header_size . '%" style="border-top:1px solid #000;border-right:1px solid #000;text-align:center;" >
												' . date("M d", strtotime($START_DATE . ' + 14 days')) . ' - ' . date("M d", strtotime($START_DATE . ' + 20 days')) . '
											</td>';
					$flag3 = true;
				}
				if (strtotime($START_DATE . ' + 21 days') <= strtotime($END_DATE)) {
					$txt .= '<td width="' . $header_size . '%" style="border-top:1px solid #000;border-right:1px solid #000;text-align:center;" >
												' . date("M d", strtotime($START_DATE . ' + 21 days')) . ' - ' . date("M d", strtotime($START_DATE . ' + 27 days')) . '
											</td>';
					$flag4 = true;
				}
				$txt .= '</tr>
								
								<tr>
									<td width="17.96%" style="border-right:1px solid #000;;border-left:1px solid #000;" >' . $res_cs->fields['COURSE_DESCRIPTION'] . '</td>';

				if (!isset($_REQUEST['AR_DAYS']) || $_REQUEST['AR_DAYS'] == 'sun_sat') {
					$txt .= '<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >S</td>';
					$txt .= '<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >M</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >W</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >F</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >S</td>';
					if ($flag2 === true) {

						$txt .= '		<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >S</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >M</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >W</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >F</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >S</td>';
					}

					if ($flag3 === true) {

						$txt .= '	<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >S</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >M</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >W</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >F</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >S</td>';
					}

					if ($flag4 === true) {

						$txt .= '<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >S</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >M</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >W</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >F</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >S</td>';
					}
				} else {
					$txt .= '
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >M</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >W</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >F</td>
									';

					if ($flag2 === true) {
						$txt .= '			<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >M</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >W</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >F</td>
									';
					}
					if ($flag3 === true) {
						$txt .= '			<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >M</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >W</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >F</td>
									';
					}

					if ($flag4 === true) {
						$txt .= '			<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >M</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >W</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >T</td>
										<td width="2.93%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >F</td>';
					}
				}


				$txt .= '</tr>
								<tr>
									<td width="17.96%" style="border-right:1px solid #000;;border-left:1px solid #000;border-bottom:1px solid #000;" >Term Start: ' . $res_cs->fields['BEGIN_DATE_1'] . '</td>';
				for ($i = 0; $i <= 27; $i++) {
					if (!($_REQUEST['AR_DAYS'] == 'mon_fri' && in_array(strtolower(date("l", strtotime($START_DATE . ' + ' . $i . ' days'))), ['sunday', 'saturday'])) && (strtotime($START_DATE . ' + ' . $i . ' days') <= strtotime($END_DATE))) {
						$txt .= '<td width="2.93%" align="center" style="border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >' . date("d", strtotime($START_DATE . ' + ' . $i . ' days')) . '</td>';
					}
				}

				$txt .= '</tr>
							</thead>';

				$res_stud = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) as STUD_NAME, STUDENT_ID, S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,  PK_STUDENT_COURSE 
				FROM
				S_STUDENT_COURSE,  S_STUDENT_MASTER, S_STUDENT_ACADEMICS   
				WHERE 
				S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
				S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
				S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
				S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' 
				ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ");
				//echo $query." ".$cond." ".$group_by." ".$order_by."<br /><br />";
				while (!$res_stud->EOF) {
					$PK_STUDENT_ENROLLMENT 	= $res_stud->fields['PK_STUDENT_ENROLLMENT'];
					$PK_STUDENT_COURSE 		= $res_stud->fields['PK_STUDENT_COURSE'];

					$txt .= '<tr>
								<td width="17.96%" style="border-bottom:1px solid #000;border-right:1px solid #000;;border-left:1px solid #000;" >' . $res_stud->fields['STUDENT_ID'] . ' ' . $res_stud->fields['STUD_NAME'] . '</td>';
					for ($i = 0; $i <= 27; $i++) {
						$DATE = date("Y-m-d", strtotime($START_DATE . ' + ' . $i . ' days'));

						if (!($_REQUEST['AR_DAYS'] == 'mon_fri' && in_array(strtolower(date("l", strtotime($START_DATE . ' + ' . $i . ' days'))), ['sunday', 'saturday'])) && (strtotime($DATE) <= strtotime($END_DATE))) {


							$res = $db->Execute("select ATTENDANCE_HOURS, S_STUDENT_ATTENDANCE.COMPLETED, M_ATTENDANCE_CODE.CODE  from S_STUDENT_ATTENDANCE, M_ATTENDANCE_CODE, S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND SCHEDULE_DATE = '$DATE' AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE  AND (S_STUDENT_ATTENDANCE.COMPLETED = 1 OR PK_SCHEDULE_TYPE = 2)");

							$ATTENDANCE_HOURS = '';
							if ($res->RecordCount() > 0) {
								if ($res->fields['CODE'] == 'I')
									$ATTENDANCE_HOURS = 'I';
								else
									$ATTENDANCE_HOURS = number_format($res->fields['ATTENDANCE_HOURS'], 2);
							}

							/* Ticket # 1270 */
							if ($ATTENDANCE_HOURS === '0.00') {
								$ATTENDANCE_HOURS = '<table width="100%" ><tr><td style="border: 1px solid #FF0000;" >' . $ATTENDANCE_HOURS . '</td></tr></table>';
							}
							/* Ticket # 1270 */

							$txt .= '<td width="2.93%" align="center" style="border-bottom:1px solid #000;border-right:1px solid #000;" >' . $ATTENDANCE_HOURS . '</td>';
						}
					}
					$txt .= '</tr>';

					$res_stud->MoveNext();
				}

				$txt .= '</table>';

				$START_DATE = date("Y-m-d", strtotime($START_DATE . ' + 28 days'));

				$mno++;
			}

			//echo $txt;exit;
			$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
			$wkhtml .= $txt;
		}

	



		//exit;
		$file_name = 'Attendance_Roster_'.uniqid().'.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
		if (!isset($_REQUEST['ACTION'])) {
			$pdf->Output('temp/' . $file_name, 'FD');
		} else {
			$pdf->Output('temp/' . $file_name, 'F');
			header('Content-Type: application/json; charset=utf-8');
			$res = [];
			$res['path'] = 'temp/' . $file_name;
			echo json_encode($res);
		}

		return $file_name;
		/////////////////////////////////////////////////////////////////
	} else {
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
	<title><?= MNU_ATTENDANCE_ROSTER ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}
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
						<h4 class="text-themecolor"><?= MNU_ATTENDANCE_ROSTER ?></h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form class="floating-labels " method="post" name="form1" id="form1">
									<div class="row" style="padding-bottom:10px;">
										<div class="col-md-3 ">
											<select id="PRINT_TYPE" name="PRINT_TYPE" class="form-control" onchange="get_course_offering()">
												<option value="1">Print By Selected Course Offering</option>
												<option value="2">Print By Selected Instructor</option>
											</select>
										</div>

										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)">
												<option value="" selected><?= TERM ?></option>
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>"><?= $res_type->fields['BEGIN_DATE_1'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV">
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control">
												<option value=""><?= COURSE_OFFERING_PAGE_TITLE ?></option>
											</select>
										</div>

										<div class="col-md-1">
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?= PDF ?></button>
										</div>

									</div>
									<div class="row">
										<div class="col-md-2 align-self-center ">
										</div>
										<div class="col-md-8 align-self-center "></div>
										<div class="col-md-2 ">

											<!-- New -->
											<!--<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>-->

										</div>
									</div>

									<br /><br /><br /><br />
									<input type="hidden" name="FORMAT" id="FORMAT">
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
		function get_course_offering() {
			jQuery(document).ready(function($) {
				var PRINT_TYPE = document.getElementById('PRINT_TYPE').value
				if (PRINT_TYPE == 1) {
					var data = 'PK_TERM_MASTER=' + document.getElementById('PK_TERM_MASTER').value + '&dont_show_term=1';
					var url = "ajax_get_course_offering_from_term";
				} else {
					var data = 'PK_TERM_MASTER=' + document.getElementById('PK_TERM_MASTER').value;
					var url = "ajax_get_course_offering_instructor_from_term";
				}
				//alert(data)
				var value = $.ajax({
					url: url,
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						if (PRINT_TYPE == 1) {
							document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
							document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
							document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
							$("#PK_COURSE_OFFERING option[value='']").remove();

							$('#PK_COURSE_OFFERING').multiselect({
								includeSelectAllOption: true,
								allSelectedText: 'All <?= COURSE_OFFERING_PAGE_TITLE ?>',
								nonSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?>',
								numberDisplayed: 2,
								nSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?> selected'
							});
						} else {
							document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
							document.getElementById('INSTRUCTOR').setAttribute('multiple', true);
							document.getElementById('INSTRUCTOR').name = "INSTRUCTOR[]"
							$("#INSTRUCTOR option[value='']").remove();

							$('#INSTRUCTOR').multiselect({
								includeSelectAllOption: true,
								allSelectedText: 'All <?= INSTRUCTOR ?>',
								nonSelectedText: '<?= INSTRUCTOR ?>',
								numberDisplayed: 2,
								nSelectedText: '<?= INSTRUCTOR ?> selected'
							});
						}
					}
				}).responseText;
			});
		}

		function get_course_details() {}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function submit_form(val) {
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();
				if (result == true) {
					document.getElementById('FORMAT').value = val
					document.form1.submit();
				}
			});
		}
	</script>

</body>

</html>