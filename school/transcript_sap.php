<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");
require_once("function_transcript_header.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
include_once($_ENV['REAL_PATH'] . "/global/av_wkhtmltopdf/av_wkhtmltopdf.php");

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
if (check_access('REPORT_REGISTRAR') == 0) {
	header("location:../index");
	exit;
}
class MYPDF extends TCPDF
{
	public function Header()
	{
		global $db;

		if ($_SESSION['temp_id'] == $this->PK_STUDENT_ENROLLMENT) {
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(10);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME, 0, false, 'L', 0, '', 0, false, 'M', 'L');

			$this->SetFont('helvetica', 'I', 17);
			$this->SetY(8);
			$this->SetTextColor(000, 000, 000);
			$this->SetX(85);
			$this->Cell(55, 8, "Program Grade Book Progress Report Card", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		} else
			$_SESSION['temp_id'] = $this->PK_STUDENT_ENROLLMENT;
	}
	public function Footer()
	{
		global $db;

		$this->SetY(-28);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);

		$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 9");

		$BASE = -28 - $res_type->fields['FOOTER_LOC'];
		$this->SetY($BASE);
		$this->SetX(10);
		$this->SetFont('helvetica', '', 7);

		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		$CONTENT = nl2br($res_type->fields['CONTENT']);
		$this->MultiCell(190, 20, $CONTENT, 0, 'L', 0, 0, '', '', true, '', true, true); //Ticket # 1234 

		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page ' . $this->getPageNumGroupAlias() . ' of ' . $this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

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
function bootpdf()
{



	global $db;
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
	$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 15, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	/* Ticket # 1234 */
	$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 9");
	$BREAK_VAL = 30 + $res_type->fields['FOOTER_LOC'];
	$pdf->SetAutoPageBreak(TRUE, $BREAK_VAL);
	/* Ticket # 1234 */
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 8, '', true);

	$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$LOGO = '';
	if ($res->fields['PDF_LOGO'] != '')
		$LOGO = '<img src="' . $res->fields['PDF_LOGO'] . '" />';

	return $pdf;
}



$res_type1 = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
if (!empty($_POST)) {
	$wkhtmlObj = new Av_wkhtmltopdf();
	$wkhtmlObj->reportname = 'OFFICIAL TRANSCRIPT';
	$wkhtmlObj->reportdescription = 'Details ';
	if($_REQUEST['dt'] != ''){
		$wkhtmlObj->reportdescription .= "<div style='font-size : 14px'> As of Date : ".date('m/d/Y', $_REQUEST['dt'])."</div>";
	}
	$wkhtmlObj->setMargins(67, 10, 20, 10);

	// echo "<pre>"; print_r($_POST);

	### INITIATION 
	$pdf = bootpdf();
	$_REQUEST['report_type'] = 1;


	#get date , student ids and enrollment ids 
	$student = $db->Execute("SELECT GROUP_CONCAT(PK_STUDENT_MASTER) AS PK_STUDENTS  FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT IN ($_REQUEST[eid])");
	$PK_STUDENT_MASTER_ARR = array_unique(explode(",", $student->fields['PK_STUDENTS']));

	$date_cond  = "";
	$date_cond1 = "";

	if ($_REQUEST['dt'] != '') {
		$date = date("Y-m-d", strtotime($_REQUEST['dt']));
		$date_cond  = " AND COMPLETED_DATE <= '$date' ";
		$date_cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$date' ";
	}

	/* Get Attendance Codes */
	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
	$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
	$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

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
	/* End -  Get Attendance Codes */

	#for each student id and student enrollment  
	foreach ($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
		$res_stu = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); //Ticket # 1157 

		$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");

		if ($_REQUEST['eid'] != '')
			$en_cond1 = " AND PK_STUDENT_ENROLLMENT IN ($_REQUEST[eid]) ";
		else
			$en_cond1 = " AND IS_ACTIVE_ENROLLMENT = 1 ";



		#AV - Get Individual entrollments from selected enrollments for this stodent 

		$enrollments = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'  $en_cond1");


		// dump($enrollments);

		$PDF_CONTENT = [];
		$PDF_HEADER = [];
		while (!$enrollments->EOF) {
			$txt = '';

			$enrollment = $enrollments->fields['PK_STUDENT_ENROLLMENT'];
			$pdf->startPageGroup();
			$pdf->AddPage();
			if ($wkhtmlObj->content != '') {
				// $wkhtmlObj->addpagebreak();
			}
			$PK_STUDENT_ENROLLMENT = $enrollments->fields['PK_STUDENT_ENROLLMENT'];

			$res_term = $db->Execute("SELECT CODE,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$enrollment' "); 

			$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STUDENT_STATUS, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE,SESSION, HOURS, UNITS, BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, M_ENROLLMENT_STATUS.DESCRIPTION AS ENROLLMENT_STATUS FROM S_STUDENT_ENROLLMENT LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond1 ORDER By BEGIN_DATE_1 DESC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC");


			$SCHEDULED_HOUR 	 = 0;
			$COMP_SCHEDULED_HOUR = 0;
			$res_sch = $db->Execute("SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) $date_cond1");
			while (!$res_sch->EOF) {
				$exc_att_flag = 0;
				foreach ($exc_att_code_arr as $exc_att_code) {
					if ($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
						$exc_att_flag = 1;
						break;
					}
				}
				if ($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0) {
					$SCHEDULED_HOUR += $res_sch->fields['HOURS'];

					if ($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2) {
						$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];
					}
				}
				$res_sch->MoveNext();
			}

			$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) $date_cond1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");

			$res_attended_all = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) $date_cond1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code)  ");


			// $pdf->AddPage();

			$txt .= '
			<style>
        /* Style for the container div */
        .table-container {
			display: -webkit-box;
            display: flex !important;
            justify-content: space-between !important;
            margin: 20px !important;
			
        }

        /* Style for the first table with 38% width */
        .table1 {
            width: 38% !important;
			margin-right:3% !important;
			margin-top:40px;
        }

        /* Style for the second table with 58% width */
        .table2 {
            width: 58% !important;
			margin-top:40px;
        }

        /* Style for each table */
        table {
            border-collapse: collapse !important;
            width: 100% !important;
            margin-bottom: 20px !important;
			font-size : 14px;
        } 

        th, td {
            padding: 10px;
            text-align: left !important;
        }
		body{
		font-size : 14px;
		}
    </style>
	<!-- Container div for tables -->
<div class="table-container">
<!-- First table with 38% width -->
<div class="table1">
			<table><thead>';

			$tot_session_req 	= 0;
			$tot_session_com 	= 0;
			$tot_hour_req 		= 0;
			$tot_hour_com 		= 0;
			$tot_point_req		= 0;
			$tot_point_com 		= 0;
			$res_test_type_sql =  "SELECT S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE, GRADE_BOOK_TYPE from 
			S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
			WHERE 
			M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
			M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND
			PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND 
			S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			COMPLETED_DATE != '0000-00-00' $date_cond 
			GROUP BY S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE ORDER BY GRADE_BOOK_TYPE ASC ";
			$res_test_type = $db->Execute($res_test_type_sql);

			// dump("res_test_type", $res_test_type);
			$test_header = false;
			while (!$res_test_type->EOF) {

				if ($res_test_type->fields['GRADE_BOOK_TYPE'] == 'Test') {
					if ($test_header == false) {
						$txt .= "<tr ><th style='border-bottom : 1px solid black'><b>Test<br/>Description</b></th><th style='border-bottom : 1px solid black'><b>Average <br>Grade</b></th></tr></thead><tbody>";
						$test_header = true;
					}
					// echo "<b>TEST HERE </b>" . $res_test_type->fields['GRADE_BOOK_TYPE'];
					$PK_GRADE_BOOK_TYPE = $res_test_type->fields['PK_GRADE_BOOK_TYPE'];

					$type_tot_session_req 	= 0;
					$type_tot_session_com 	= 0;
					$type_tot_hour_req 		= 0;
					$type_tot_hour_com 		= 0;
					$type_tot_point_req		= 0;
					$type_tot_point_com 	= 0;

					$res_code = $db->Execute("SELECT M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE, CODE, M_GRADE_BOOK_CODE.DESCRIPTION  from 
								S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
								WHERE 
								M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
								M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
								PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND 
								S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								COMPLETED_DATE != '0000-00-00' $date_cond AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE'
								GROUP BY M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE ORDER BY CODE ASC");

					while (!$res_code->EOF) {
						$PK_GRADE_BOOK_CODE = $res_code->fields['PK_GRADE_BOOK_CODE'];
						// $txt .= '<tr> 
						// 			<td width="95%" ><b>' . $res_code->fields['DESCRIPTION'] . '</b></td> 
						// 		';
						if ($_REQUEST['report_type'] == 1 || $_REQUEST['report_type'] == 2) {
							//$txt .= '<td width="10%" >Completed<br />Date</b></td>';
						}



						$sub_tot_session_req 	= 0;
						$sub_tot_session_com 	= 0;
						$sub_tot_hour_req 		= 0;
						$sub_tot_hour_com 		= 0;
						$sub_tot_point_req		= 0;
						$sub_tot_point_com 		= 0;
						$res_test = $db->Execute("SELECT PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT, CODE, M_GRADE_BOOK_CODE.DESCRIPTION GRADE_BOOK_TYPE, COMPLETED_DATE, SESSION_REQUIRED, SESSION_COMPLETED, HOUR_REQUIRED, HOUR_COMPLETED, POINTS_REQUIRED, POINTS_COMPLETED, S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.DESCRIPTION, DATE_FORMAT(COMPLETED_DATE, '%m/%d/%Y') as COMPLETED_DATE_1 from 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
										WHERE 
										M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
										M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
										PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
										COMPLETED_DATE != '0000-00-00' $date_cond AND 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND 
										M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = '$PK_GRADE_BOOK_CODE' 
										ORDER BY CODE ASC, COMPLETED_DATE ASC");

						while (!$res_test->EOF) {
							// dd($res_test);
							$sub_tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
							$sub_tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
							$sub_tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
							$sub_tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
							$sub_tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
							$sub_tot_point_com 		+= $res_test->fields['POINTS_COMPLETED'];

							$type_tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
							$type_tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
							$type_tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
							$type_tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
							$type_tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
							$type_tot_point_com 	+= $res_test->fields['POINTS_COMPLETED'];

							$tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
							$tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
							$tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
							$tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
							$tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
							$tot_point_com 		+= $res_test->fields['POINTS_COMPLETED'];

							if ($_REQUEST['report_type'] == 1 || $_REQUEST['report_type'] == 2) {
								// $txt .= '<tr>
								// 							<td width="5%" >&nbsp;</td>
								// 							<td width="11.00%" ></td>';
								// if ($_REQUEST['report_type'] == 1 || $_REQUEST['report_type'] == 2) {
								// 	$txt .= '<td width="10%" style="font-size:22px" >' . $res_test->fields['COMPLETED_DATE_1'] . '</td>';
								// }
								// $txt .= '<td width="10.71%" style="font-size:22px" >' . $res_test->fields['SESSION_REQUIRED'] . '</td>
								// 							<td width="10.71%" style="font-size:22px" >' . $res_test->fields['SESSION_COMPLETED'] . '</td>
								// 							<td width="10.71%" style="font-size:22px" >' . $res_test->fields['HOUR_REQUIRED'] . '</td>
								// 							<td width="10.71%" style="font-size:22px" >' . $res_test->fields['HOUR_COMPLETED'] . '</td>
								// 							<td width="10.71%" style="font-size:22px" >' . $res_test->fields['POINTS_REQUIRED'] . '</td>
								// 							<td width="10.71%" style="font-size:22px" >' . $res_test->fields['POINTS_COMPLETED'] . '</td>
								// 							<td width="10.71%" style="font-size:22px" ></td>
								// 						</tr>';
							}
							$res_test->MoveNext();
						}

						// $txt .= '<tr>
						// 							<td width="5%" >&nbsp;</td>
						// 							<td width="11.0%" style="border-top:1px solid #000;" >Total</b></td>';
						// if ($_REQUEST['report_type'] == 1 || $_REQUEST['report_type'] == 2) {
						// 	$txt .= '<td style="border-top:1px solid #000;" width="10%" ></td>';
						// }
						$txt .= '<tr>
						<td>' . $res_code->fields['DESCRIPTION'] . '</td>
						<td>'
							. number_format_value_checker(($sub_tot_point_com / $sub_tot_point_req * 100), 2)
							. '</td></tr>';

						$res_code->MoveNext();
					}

					
				}
				$res_test_type->MoveNext();
			}
			$txt .= '</tbody></table> </div> ';

			$res_test_type = $db->Execute($res_test_type_sql);
			$lab_table  = false;
			while (!$res_test_type->EOF) {

				if ($res_test_type->fields['GRADE_BOOK_TYPE'] == 'Lab') {
					if ($lab_table == false) {
						$txt .= '
						<!-- Second table with 58% width -->
						<div class="table2">
						<table><thead>';
						$txt .= '<tr style="border-bottom : 1px solid black" > 
									<th width="25%"  ><b>Lab <br/> Description</b></th>
									<th width="25%" ><b>Sessions<br />Required</b></th>
									<th width="25%" ><b>Sessions<br />Completed</b></th>
									<th width="25%" ><b>Sessions<br />Remaining</b></th> 
								</tr></thead><tbody>';
						$lab_table = true;
					}
					// echo "<b>TEST HERE </b>".$res_test_type->fields['GRADE_BOOK_TYPE'];
					$PK_GRADE_BOOK_TYPE = $res_test_type->fields['PK_GRADE_BOOK_TYPE'];

					$type_tot_session_req 	= 0;
					$type_tot_session_com 	= 0;
					$type_tot_hour_req 		= 0;
					$type_tot_hour_com 		= 0;
					$type_tot_point_req		= 0;
					$type_tot_point_com 	= 0;
					// $txt .= '<tr>
					// 						<td width="100%" ><b>' . $res_test_type->fields['GRADE_BOOK_TYPE'] . '</b></td>
					// 					</tr>';
					$res_code = $db->Execute("SELECT M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE, CODE, M_GRADE_BOOK_CODE.DESCRIPTION  from 
								S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
								WHERE 
								M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
								M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
								PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND 
								S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								COMPLETED_DATE != '0000-00-00' $date_cond AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE'
								GROUP BY M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE ORDER BY CODE ASC");

					while (!$res_code->EOF) {
						$PK_GRADE_BOOK_CODE = $res_code->fields['PK_GRADE_BOOK_CODE'];
						// $txt .= '
						// 					 ';
						// if ($_REQUEST['report_type'] == 1 || $_REQUEST['report_type'] == 2) {
						// 	$txt .= '<td width="10%" >Completed<br />Date</b></td>';
						// }



						$sub_tot_session_req 	= 0;
						$sub_tot_session_com 	= 0;
						$sub_tot_hour_req 		= 0;
						$sub_tot_hour_com 		= 0;
						$sub_tot_point_req		= 0;
						$sub_tot_point_com 		= 0;
						$res_test = $db->Execute("select PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT, CODE, M_GRADE_BOOK_CODE.DESCRIPTION GRADE_BOOK_TYPE, COMPLETED_DATE, SESSION_REQUIRED, SESSION_COMPLETED, HOUR_REQUIRED, HOUR_COMPLETED, POINTS_REQUIRED, POINTS_COMPLETED, S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.DESCRIPTION, DATE_FORMAT(COMPLETED_DATE, '%m/%d/%Y') as COMPLETED_DATE_1 from 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
										WHERE 
										M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
										M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
										PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
										COMPLETED_DATE != '0000-00-00' $date_cond AND 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND 
										M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = '$PK_GRADE_BOOK_CODE' 
										ORDER BY CODE ASC, COMPLETED_DATE ASC");

						while (!$res_test->EOF) {
							$sub_tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
							$sub_tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
							$sub_tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
							$sub_tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
							$sub_tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
							$sub_tot_point_com 		+= $res_test->fields['POINTS_COMPLETED'];

							$type_tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
							$type_tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
							$type_tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
							$type_tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
							$type_tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
							$type_tot_point_com 	+= $res_test->fields['POINTS_COMPLETED'];

							$tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
							$tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
							$tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
							$tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
							$tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
							$tot_point_com 		+= $res_test->fields['POINTS_COMPLETED'];

							// if ($_REQUEST['report_type'] == 1 || $_REQUEST['report_type'] == 2) {
							// 	$txt .= '<tr>
							// 								<td width="5%" >&nbsp;</td>
							// 								<td width="11.00%" ></td>';
							// 	if ($_REQUEST['report_type'] == 1 || $_REQUEST['report_type'] == 2) {
							// 		$txt .= '<td width="10%" style="font-size:22px" >' . $res_test->fields['COMPLETED_DATE_1'] . '</td>';
							// 	}
							// 	$txt .= '<td width="10.71%" style="font-size:22px" >' . $res_test->fields['SESSION_REQUIRED'] . '</td>
							// 								<td width="10.71%" style="font-size:22px" >' . $res_test->fields['SESSION_COMPLETED'] . '</td>
							// 								<td width="10.71%" style="font-size:22px" >' . $res_test->fields['HOUR_REQUIRED'] . '</td>
							// 								<td width="10.71%" style="font-size:22px" >' . $res_test->fields['HOUR_COMPLETED'] . '</td>
							// 								<td width="10.71%" style="font-size:22px" >' . $res_test->fields['POINTS_REQUIRED'] . '</td>
							// 								<td width="10.71%" style="font-size:22px" >' . $res_test->fields['POINTS_COMPLETED'] . '</td>
							// 								<td width="10.71%" style="font-size:22px" ></td>
							// 							</tr>';
							// }
							$res_test->MoveNext();
						}

						// $txt .= '<tr>
						// 			<td>'.$res_code->fields['DESCRIPTION'].'</td>';
						// if ($_REQUEST['report_type'] == 1 || $_REQUEST['report_type'] == 2) {
						// 	$txt .= '<td style="border-top:1px solid #000;" width="10%" ></td>';
						// }

						$txt .= '<tr>
									<td>' . $res_code->fields['DESCRIPTION'] . '</td>
									<td>' . $sub_tot_session_req . '</td>
									<td>' . $sub_tot_session_com . '</td>
									<td>' . ($sub_tot_session_req - $sub_tot_session_com) . '</td>
								</tr>';

						$res_code->MoveNext();
					}

				
				}
				$res_test_type->MoveNext();
			}
			$txt .= '</tbody></table> </div></div>';
			if ($res_test_type->RecordCount() > 0) {


				//TOTAL TABLE 
				$cond1 = "";
				$cond2 = "";
				$cond3 = "";
				$cond4 = "";

				$cond1 = " AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";
				$cond2 = " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";
				$cond3 = " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";
				$cond4 = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";
				$res_grade = $db->Execute("select SUM(POINTS_REQUIRED) as POINTS_REQUIRED, SUM(POINTS_COMPLETED) as POINTS_COMPLETED from S_STUDENT_PROGRAM_GRADE_BOOK_INPUT LEFT JOIN M_GRADE_BOOK_CODE ON M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND COMPLETED_DATE != '0000-00-00' $cond1 ");
				$per = $res_grade->fields['POINTS_COMPLETED'] / $res_grade->fields['POINTS_REQUIRED'] * 100;

				$res_course_schedule = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) as STUD_NAME, IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, IF(S_STUDENT_SCHEDULE.END_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.END_TIME,'%h:%i %p'),'') AS END_TIME, IF(S_STUDENT_SCHEDULE.START_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, COURSE_CODE, SCHEDULE_TYPE, S_STUDENT_ATTENDANCE.COMPLETED AS COMPLETED_1, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, PK_STUDENT_ATTENDANCE,ATTENDANCE_HOURS FROM  
					S_STUDENT_MASTER, S_STUDENT_SCHEDULE 
					LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
					LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
					LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
					LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
					LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
					LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
					LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
					LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON  S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL
					WHERE 
					S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
					S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
					S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond2 ");
				$TOTAL_HOURS 		= 0;
				$ATTENDANCE_HOURS 	= 0;
				while (!$res_course_schedule->EOF) {

					if ($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2) {
						$ATTENDANCE_HOURS 	+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
					}
					if (($res_course_schedule->fields['ATTENDANCE_CODE'] != 'I' && $res_course_schedule->fields['COMPLETED_1'] == 1) || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2)
						$TOTAL_HOURS += $res_course_schedule->fields['HOURS'];

					$res_course_schedule->MoveNext();
				}

				//transfer hours 
				$res_trans = $db->Execute("SELECT IFNULL(SUM(HOUR),0) as HOUR FROM S_STUDENT_CREDIT_TRANSFER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $cond3 ");
				$TRANSFER_HOURS = $res_trans->fields['HOUR'];
				//required hours 
				$res_prog = $db->Execute("SELECT SUM(HOURS) as HOURS FROM S_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM $cond4 ");
				$TOTAL_REQUIRED_HOURS = $res_prog->fields['HOURS'];

				$txt .= '
			
			
			<style>
				.main-container {
					
					display: -webkit-box; /* wkhtmltopdf uses this one */
					display: flex;
				margin: 0 auto;
				margin-top : 45px;
				max-width: 600px; /* Set a max-width if needed */
				border : 0px solid black;
				}

				.column {
				flex: 1;
				padding: 10px;
				border: 1px solid #000;
				-webkit-box-flex: 1;
    			-webkit-flex: 1;
				}
			</style>
			<div class="main-container">
				<div class="column"> 
				<p>Accumulative GPA : ' . number_format_value_checker($per, 2) . '</p>
				<p>Scheduled Hours : 	' . number_format_value_checker($TOTAL_HOURS, 2) . '			</p>
				<p>Accumulative Attendance : ' . number_format_value_checker((($ATTENDANCE_HOURS + $TRANSFER_HOURS) / $TOTAL_REQUIRED_HOURS) * 100, 2) . '%	</p>
				</div>
				<div class="column"> 
				<p>Total Required Hours : ' . number_format_value_checker($TOTAL_REQUIRED_HOURS, 2) . ' </p>
				<p>Total Transfer Hours : ' . number_format_value_checker($TRANSFER_HOURS, 2) . ' </p>
				<p>Total Attended Hours : ' . number_format_value_checker($ATTENDANCE_HOURS, 2) . ' </p>
				<p>Total Hours Remaining : ' . number_format_value_checker($TOTAL_REQUIRED_HOURS - $ATTENDANCE_HOURS - $TRANSFER_HOURS, 2) . '</p>
				</div>
			</div>
			<div style="text-align : center; font-size : 14px;  margin-top : 55px;"> Signature - School Official ___________________________________</div>
			
			';
			}
			//END OF TOTAL TABLE 
			if ($txt != '') {


				$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING,UNITS,HOURS, EXPECTED_GRAD_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$enrollment' "); 



				$std_header = '
			
			
				<style>
					.main-container2 {
						
					display: -webkit-box; /* wkhtmltopdf uses this one */
					display: flex;
					margin: 0 auto;
					margin-top : 6px;
					margin-bottom : 10px;
					max-width: 1200px; /* Set a max-width if needed */ 
					}
	
					.column2 {
					font-size: 14px;
					flex: 1;
					padding: 3px; 
					-webkit-box-flex: 1;
					-webkit-flex: 1; 
					}
				</style>
				<div style="width:100% ; height : 5px;border-top: 2px #c0c0c0 solid" ></div>
				<div class="main-container2" >
					<div class="column2"> 
					' . $res_stu->fields['NAME'] . '<br/>
					' . $res_add->fields['ADDRESS'] . '<br/>
					' . $res_add->fields['CITY'] . ', ' . $res_add->fields['STATE_CODE'] . ' ' . $res_add->fields['ZIP'] . '<br/>
					' . $res_add->fields['COUNTRY'] . '<br/>
					</div>
					<div class="column2"> 
					ID : ' . $res_stu->fields['STUDENT_ID'] . '<br/> 
					Student Status : Grad <br/>
					'.transcript_header('PROGRAM_CODE_DESCRIPTION' , " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$enrollment' ").'<br/>
					Program Hours : ' . number_format_value_checker($res_enroll->fields['HOURS'], 2) . '<br/>
					Program Units : ' . number_format_value_checker($res_enroll->fields['UNITS'], 2) .' <br/>
					First Term : '.$res_term->fields['BEGIN_DATE_1'].'<br/>
					'.transcript_header('GRADE_DATE' , " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$enrollment' ").'<br/>
					
					</div>
				</div>
				<div style="width:100% ; height : 5px;border-top: 2px #c0c0c0 solid" ></div>
				';
				$wkhtmlObj->addheader($std_header);
				$wkhtmlObj->addContent($txt);
			}

			// echo($txt);
			// echo $txt;
			// exit;

			$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
			$enrollments->MoveNext();
		}
	}

	// echo $txt;
	// exit;  
	$wkhtmlObj->output();


	// $pdf->Output("temp/test.pdf", 'F');
	// header('Content-Type: application/json; charset=utf-8');
	// $data = [];
	// $data['path'] = "temp/test.pdf";
	// echo json_encode($data);
	return;
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
	<title> Official Transcript | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		.dropdown-menu>li>a {
			white-space: nowrap;
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
					<div class="col-md-5 align-self-center">
						<h5 class="text-themecolor">Report</h5>
						<h4>Official Transcript - Details</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
								<input type="hidden" name="SELECTED_PK_STUDENT_MASTER" id="SELECTED_PK_STUDENT_MASTER" value="">
								<div class="p-20">
									<div class="d-flex">
										<div class="col-12 col-sm-12 ">

											<div class="row">

												<div class="col-md-2 align-self-center" id="ENROLLMENT_TYPE_1_div">
													<select id="ENROLLMENT_TYPE_1" name="ENROLLMENT_TYPE_1" class="form-control"><!-- DIAM757 //onchange="search()"-->
														<option value="1">All Enrollments</option>
														<option value="2">Current Enrollment</option>
													</select>
												</div>

												<div class="col-md-2 " id="AS_OF_DATE_div">
													<input type="text" class="form-control date required-entry" id="AS_OF_DATE" name="AS_OF_DATE" placeholder="<?= AS_OF_DATE ?>">
												</div>

											</div>
											<br>
											<div class="row">
												<div class="col-2 col-sm-2" id="PK_CAMPUS_DIV">
													<div class="form-group m-b-40">
														<select id="PK_CAMPUS" name="PK_CAMPUS[]" class="form-control" multiple>
															<?
															while (!$res_type1->EOF) {
																if ($res_type1->RecordCount() == 1)
																	$selected = 'selected'; ?>
																<option value="<?= $res_type1->fields['PK_CAMPUS'] ?>" <?= $selected ?>><?= $res_type1->fields['CAMPUS_CODE'] ?></option>
															<? $res_type1->MoveNext();
															} ?>
														</select>

														<span class="bar"></span>
														<!-- <label for="PK_CAMPUS"><?= CAMPUS ?></label> -->
													</div>
												</div>

												<div class="col-md-2 " id="PK_TERM_MASTER_DIV">
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control"> <? $res_type = $db->Execute("select ACTIVE,PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'   order by ACTIVE DESC,BEGIN_DATE DESC");
																																		while (!$res_type->EOF) { ?>
															<option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['BEGIN_DATE_1'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
															</option>
														<? $res_type->MoveNext();
																																		} ?>
													</select>
												</div>

												<div class="col-md-2 " id="PK_CAMPUS_PROGRAM_DIV">
													<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control">
														<? $res_type = $db->Execute("select ACTIVE,PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CODE ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?= $res_type->fields['PK_CAMPUS_PROGRAM'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['DESCRIPTION'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
															</option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>

												<div class="col-md-2 " id="PK_STUDENT_STATUS_DIV">
													<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
														<? $res_type = $db->Execute("select ACTIVE,PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0  order by ACTIVE DESC,STUDENT_STATUS ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?= $res_type->fields['PK_STUDENT_STATUS'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
															</option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>

												<div class="col-md-2 " id="PK_STUDENT_GROUP_DIV">
													<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control">
														<? $res_type = $db->Execute("select ACTIVE,PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,STUDENT_GROUP ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?= $res_type->fields['PK_STUDENT_GROUP'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['STUDENT_GROUP'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
															</option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
												<div class="col-3 col-sm-3 ">
													<button type="button" onclick="search()" id="SEARCH_BTN" class="btn waves-effect waves-light btn-info">Search</button>
													<input type="hidden" name="FORMAT" id="FORMAT">
													<button type="button" onclick="get_report()" id="EXCEL_BTN" class="btn waves-effect waves-light btn-info">PDF</button>
													<input type="hidden" name="FORMAT" id="FORMAT">
												</div>
												 

											</div>

										</div>
									</div>
								</div>
							</form>
							<div class="p-20">
								<br />
								<div id="student_div"></div>
							</div>
						</div>
					</div>
				</div>


			</div>
		</div>

		<? require_once("footer.php"); ?>

		<?php if ($report_error != "") { ?>
			<div class="modal" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1">Warning</h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="form-group" style="color: red;font-size: 15px;">
								<b><?php echo $report_error; ?></b>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" data-dismiss="modal" class="btn waves-effect waves-light btn-info">Cancel</button>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>

	</div>

	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>

	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
		var error = '<?php echo  $report_error; ?>';
		jQuery(document).ready(function($) {
			if (error != "") {
				jQuery('#errorModal').modal();
			}
			$('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});

		});

		function fun_select_all() {
			var str = '';
			if (document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;

			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {

			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= "Campus" ?>',
				nonSelectedText: '<?= "Campus" ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= "Campus" ?> selected'
			});

			$('#PK_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: '<?= STATUS ?>',
				nonSelectedText: '<?= STATUS ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= STATUS ?> selected'
			});

			$('#PK_TERM_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: '<?= 'All First Terms' ?>',
				nonSelectedText: '<?= FIRST_TERM ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= FIRST_TERM ?> selected'
			});

			$('#PK_STUDENT_GROUP').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= GROUP_CODE ?>',
				nonSelectedText: '<?= GROUP_CODE ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= GROUP_CODE ?> selected'
			});

			$('#PK_CAMPUS_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: '<?= ALL_PROGRAM ?>',
				nonSelectedText: '<?= PROGRAM ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= PROGRAM ?> selected'
			});
		});

		function search() {
			jQuery(document).ready(function($) {
				var data = 'PK_STUDENT_GROUP=' + $('#PK_STUDENT_GROUP').val() + '&PK_TERM_MASTER=' + $('#PK_TERM_MASTER').val() + '&PK_CAMPUS_PROGRAM=' + $('#PK_CAMPUS_PROGRAM').val() + '&PK_STUDENT_STATUS=' + $('#PK_STUDENT_STATUS').val() + '&show_check=1' + '&ENROLLMENT=' + $('#ENROLLMENT_TYPE_1').val()+'&dt='+$('#AS_OF_DATE').val();
				var value = $.ajax({
					url: "ajax_search_student_for_reports",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('student_div').innerHTML = data
					}
				}).responseText;
			});
		}

		function get_report() {
			jQuery(document).ready(function($) {
				//get values 
				// Student Ids, Studenent Enrollments, Date

				var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
				var eid = '';
				for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
					if (PK_STUDENT_ENROLLMENT[i].checked == true) {
						console.log("checked", PK_STUDENT_ENROLLMENT[i], PK_STUDENT_ENROLLMENT[i].value);
						if (eid == '') {
							eid = eid + PK_STUDENT_ENROLLMENT[i].value
						} else {
							eid = eid + ',' + PK_STUDENT_ENROLLMENT[i].value;
						}
					}
				}

				var data = 'PK_STUDENT_GROUP=' + $('#PK_STUDENT_GROUP').val() + '&PK_TERM_MASTER=' + $('#PK_TERM_MASTER').val() + '&PK_CAMPUS_PROGRAM=' + $('#PK_CAMPUS_PROGRAM').val() + '&PK_STUDENT_STATUS=' + $('#PK_STUDENT_STATUS').val() + '&show_check=1' + '&eid=' + eid+'&dt='+$('#AS_OF_DATE').val();
				var value = $.ajax({
					url: "transcript_sap",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						// document.getElementById('student_div').innerHTML = data

						const text = window.location.href;
						const word = '/school';
						const textArray = text.split(word); // ['This is ', ' text...']
						const result = textArray.shift();
						// alert(result + '/school/' + data.path);
						downloadDataUrlFromJavascript("Transcript SAP", result + '/school/' + data.path)

					}
				}).responseText;
			});
		}

		function downloadDataUrlFromJavascript(filename, dataUrl) {

			// Construct the 'a' element
			var link = document.createElement("a");
			link.download = filename;
			link.target = "_blank";

			// Construct the URI
			link.href = dataUrl;
			document.body.appendChild(link);
			link.click();

			// Cleanup the DOM
			document.body.removeChild(link);
			delete link;
		}
	</script>

	<?php $report_error = ""; ?>

</body>

</html>