<?
define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_attendance_analysis_report.php");
require_once("check_access.php");
// error_reporting(1);
if (check_access('REPORT_REGISTRAR') == 0) {
	header("location:../index");
	exit;
}
ini_set("memory_limit", "-1");
set_time_limit(0);

if (!empty($_POST) || !empty($_REQUEST)) {

	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if ($timezone == '' || $timezone == 0)
		$timezone = 4;
	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

	$TIMEZONE = $res->fields['TIMEZONE'];

	$PK_ACCOUNT = $_SESSION['PK_ACCOUNT'];
	$month_str  = ["", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

	$_REQUEST['START_DATE'] = $_REQUEST['START_DATE_analysis'];
	$_REQUEST['eid'] = '';
	$_REQUEST['id'] = '';
	$_REQUEST['date'] = $_REQUEST['AS_OF_DATE'];
	$_REQUEST['FORMAT'] = $_REQUEST['FORMAT'];
	$_REQUEST['ENROLLMENT_TYPE'] = $_REQUEST['ENROLLMENT_TYPE'];
	#selected student ids 
	$temp = explode(",", $_REQUEST['SELECTED_PK_STUDENT_MASTER']);
	$temp = array_unique($temp, SORT_NUMERIC);
	$temp = implode(",", $temp);
	$_REQUEST['s_id'] = $temp;
	#end of selected std  ids
	$_REQUEST['incomplete'] = $_REQUEST['INCLUDE_INCOMPLETE_ATTENDANCE'];
	$_REQUEST['gpa'] = $_REQUEST['INCLUDE_GPA'];
	$_REQUEST['campus'] = $_REQUEST['PK_CAMPUS'];



	// echo "<pre>";
	// print_r($_REQUEST);
	// exit;
	// echo "<pre>";print_r($_REQUEST);exit;
	$_POST = $_REQUEST;
	/* Ticket #1194 */
	if ($_REQUEST['FORMAT'] != '') {
		$_POST['FORMAT'] = $_REQUEST['FORMAT'];
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
	if (!empty($exc_att_code_arr))
		$exclude_cond = " AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE NOT IN (" . implode(",", $exc_att_code_arr) . ") ";
	/* Ticket # 1219 */

	$cond = "";
	$_POST['START_DATE'] = $_REQUEST['START_DATE'];
	if ($_POST['START_DATE'] != '')
		$start_date_cond = $cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE >= '" . date("Y-m-d", strtotime($_POST['START_DATE'])) . "' ";
	if ($_POST['SELECT_ENROLLMENT'] == 2)
		$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
	/* Ticket #1145 */

	if ($_REQUEST['id'] != '') {
		$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '" . $_REQUEST['id'] . "' ";

		if ($_REQUEST['date'] != '')
			$end_date_or_as_of_date_cond = $cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '" . date("Y-m-d", strtotime($_REQUEST['date'])) . "' ";

		if ($_REQUEST['eid'] != '') {
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_REQUEST[eid]) ";
		} else {
			if ($_REQUEST['type'] == 2)
				$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
		}
	}

	/* Ticket # 1247 */
	if ($PK_ACCOUNT == '84') {
		if ($_REQUEST['ENROLLMENT_TYPE'] == '2') {
			$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
		}
	} else {
		if ($_REQUEST['ENROLLMENT_TYPE'] != '') {
			$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
		}
	}

	if ($_REQUEST['s_id'] != '') {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER IN ($_REQUEST[s_id]) ";
	}

	if ($_REQUEST['date'] != '')
		$cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '" . date("Y-m-d", strtotime($_REQUEST['date'])) . "' ";

	if ($_REQUEST['incomplete'] != 1) {
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
	if (!empty($_REQUEST['campus'])) {
		$PK_CAMPUS 	  = implode(',', $_REQUEST['campus']);
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	} else if (!empty($_REQUEST['PK_CAMPUS'])) {
		$PK_CAMPUS 	  = implode(',', $_REQUEST['PK_CAMPUS']);
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	// echo "select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC";exit;
	while (!$res_campus->EOF) {
		if ($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];

		if ($campus_id != '')
			$campus_id .= ',';
		$campus_id .= $res_campus->fields['PK_CAMPUS'];

		$res_campus->MoveNext();
	}
	$query = "SELECT  S_STUDENT_ATTENDANCE.COMPLETED,S_STUDENT_MASTER.LAST_NAME AS STU_NAME , S_STUDENT_MASTER.FIRST_NAME AS STU_FIRST_NAME, STUDENT_ID, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE,M_CAMPUS_PROGRAM.PK_SESSION AS M_PK_SESSION, SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS, SUM(S_COURSE_OFFERING_SCHEDULE_DETAIL.HOURS) as SCHEDULED_HOURS, M_CAMPUS_PROGRAM.HOURS, PK_STUDENT_COURSE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER, STUDENT_STATUS, S_STUDENT_MASTER.PK_STUDENT_MASTER   
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
	S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) $cond $att_com_cond
	GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ORDER BY STU_NAME  ASC, STU_FIRST_NAME ASC, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";
	// echo $query;
	// exit;
	if ($_POST['FORMAT'] == 2 || $_POST['FORMAT'] == 1) {

		$pk_sessions_arr_sql = "SELECT * FROM `M_SESSION` WHERE $PK_ACCOUNT = '$PK_ACCOUNT'";
		$pk_sessions_arr_res = $db->Execute($pk_sessions_arr_sql);
		$pk_sessions_arr = [];
		while (!$pk_sessions_arr_res->EOF) {
			$pk_sessions_arr[$pk_sessions_arr_res->fields['PK_SESSION']] = $pk_sessions_arr_res->fields['SESSION'];
			$pk_sessions_arr_res->MoveNext();
		}
		// print_r($pk_sessions_arr);exit;


		// exit; 
		//end of :Get months to iterate 


		$outputFileName;
		$objReader;
		$objPHPExcel;
		$line;
		$index;
		$cell;
		init_excel_with_header($cell, $outputFileName, $objReader, $objWriter, $objPHPExcel, $line, $index, $_REQUEST['START_DATE'], $_REQUEST['date']);
		// echo $query;exit;
		$res = $db->Execute($query);
		while (!$res->EOF) {
			$PK_STUDENT_COURSE 		= $res->fields['PK_STUDENT_COURSE'];
			$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];

			/* Ticket #1145 */

			/* Ticket # 1247 */
			$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];


			// siempre nos fijamos en el enroll ahora por que si no las repite
			// @jl 2025-05-27
			// if ($_REQUEST['ENROLLMENT_TYPE'] == 1) {
			$stud_cond = " AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";
			$tc_cond	= " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";
			$stud_cond = " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
			$tc_cond	= " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
			// } else {
			// }

			$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($campus_id) ");
			/* Ticket # 1247 */

			$res_tc = $db->Execute("SELECT SUM(HOUR) as HOUR FROM S_STUDENT_CREDIT_TRANSFER, M_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS AND SHOW_ON_TRANSCRIPT = 1 $tc_cond $start_date_cond $end_date_or_as_of_date_cond");

			$res_att = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  $att_com_cond $stud_cond $start_date_cond $end_date_or_as_of_date_cond"); //Ticket # 1247

			$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $stud_cond  $start_date_cond $end_date_or_as_of_date_cond"); //Ticket # 1247


			$cond1 = "";
			if ($_REQUEST['date'] != '')
				$cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '" . date("Y-m-d", strtotime($_REQUEST['date'])) . "' ";

			//$res_s = $db->Execute("SELECT SUM(S_STUDENT_SCHEDULE.HOURS) as SCHEDULED_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  $cond1  AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $exclude_cond");
			$SCHEDULED_HOURS = $res_s->fields['SCHEDULED_HOURS'];

			$SCHEDULED_HOURS 	 = 0;
			$COMP_SCHEDULED_HOUR = 0;
			// echo "<br>";
			// echo 
			$res_sch_sql_statement = "SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $stud_cond  $cond1";
			// echo "<br>";
			$res_sch = $db->Execute($res_sch_sql_statement); //Ticket # 1247
			while (!$res_sch->EOF) {
				$exc_att_flag = 0;
				foreach ($exc_att_code_arr as $exc_att_code) {
					if ($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
						$exc_att_flag = 1;
						break;
					}
				}

				/* Ticket # 1247 */
				if ($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0) {
					if ($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2 || $_REQUEST['incomplete'] == 1) {
						$SCHEDULED_HOURS	 += $res_sch->fields['HOURS'];
						$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];
					}
				}
				/* Ticket # 1247 */

				$res_sch->MoveNext();
			}



			// $index++;
			// $cell_no = $cell[$index] . $line;
			// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format($res->fields['HOURS'], 2));

			// $index++;
			// $cell_no = $cell[$index] . $line;
			// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format($res_tc->fields['HOUR'], 2));

			//MONTH WISE 

			## FOR TRANSFERRED HOURS WE MIGHT NOT BE ABLE TO GROUP BY MONTH AS THIS DO NOT HAVE ANY 
			// $res_tc_motnh  = $db->Execute("SELECT SUM(HOUR) as HOUR FROM S_STUDENT_CREDIT_TRANSFER, M_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS AND SHOW_ON_TRANSCRIPT = 1 $tc_cond ");
			$res_att_monthwise_sql = "SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS  , MONTH(SCHEDULE_DATE) as month,YEAR(SCHEDULE_DATE) as year FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (PK_SCHEDULE_TYPE = 1 OR PK_SCHEDULE_TYPE=2) AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  $att_com_cond $stud_cond $start_date_cond $end_date_or_as_of_date_cond GROUP BY MONTH(SCHEDULE_DATE), YEAR(SCHEDULE_DATE)";
			$res_att_monthwise = $db->Execute($res_att_monthwise_sql);
			$res_att_monthwise_arr = [];
			while (!$res_att_monthwise->EOF) {
				$res_att_monthwise_arr[$res_att_monthwise->fields['month'] . '-' . $res_att_monthwise->fields['year']] = $res_att_monthwise->fields['ATTENDANCE_HOURS'];
				$res_att_monthwise->MoveNext();
			}
			// echo "<br> <b>" . $res_att_monthwise_sql . "</b><br>";
			// exit;

			$res_ns_monthwise_sql = "SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS, MONTH(SCHEDULE_DATE) as month,YEAR(SCHEDULE_DATE) as year  FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $stud_cond  $start_date_cond $end_date_or_as_of_date_cond GROUP BY MONTH(SCHEDULE_DATE) , YEAR(SCHEDULE_DATE)";
			$res_ns_monthwise = $db->Execute($res_ns_monthwise_sql);

			$res_ns_monthwise_arr = [];
			while (!$res_ns_monthwise->EOF) {
				$res_ns_monthwise_arr[$res_ns_monthwise->fields['month'] . '-' . $res_ns_monthwise->fields['year']] = $res_ns_monthwise->fields['ATTENDANCE_HOURS'];
				$res_ns_monthwise->MoveNext();
			}

			$res_sch_monthwise_sql = "SELECT SUM(HOURS) as ATTENDANCE_HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE,MONTH(SCHEDULE_DATE) as month,YEAR(SCHEDULE_DATE) as year FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $stud_cond  $cond1 $start_date_cond $end_date_or_as_of_date_cond AND PK_ATTENDANCE_CODE!=7 AND (PK_SCHEDULE_TYPE=1 OR PK_SCHEDULE_TYPE=2) GROUP BY MONTH(SCHEDULE_DATE) , YEAR(SCHEDULE_DATE) ORDER BY YEAR(SCHEDULE_DATE) ASC , MONTH(SCHEDULE_DATE) ASC";

			// echo "<br> <b>" . $res_sch_monthwise_sql . "</b><br>";


			$res_sch_monthwise = $db->Execute($res_sch_monthwise_sql);
			$res_sch_monthwise_arr = [];
			while (!$res_sch_monthwise->EOF) {
				$res_sch_monthwise_arr[$res_sch_monthwise->fields['month'] . '-' . $res_sch_monthwise->fields['year']] = $res_sch_monthwise->fields['ATTENDANCE_HOURS'];
				$res_sch_monthwise->MoveNext();
			}
			// echo "<pre>";
			// echo "res_att_monthwise_arr";
			// print_r($res_att_monthwise_arr);
			// echo "res_ns_monthwise";
			// print_r($res_ns_monthwise_arr);
			// echo "res_sch_monthwise";
			// print_r($res_sch_monthwise_arr);



			#################################################### 

			foreach ($res_sch_monthwise_arr as $key_res => $hours_res) {
				$line++;
				$index = -1;
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_campus->fields['CAMPUS_CODE']);
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_NAME']/* . ', ' . $res->fields['STU_FIRST_NAME']*/);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_FIRST_NAME']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);



				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TERM_MASTER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_TRANSCRIPT_CODE']);

				// $ses = isset($pk_sessions_arr[$res->fields['M_PK_SESSION']]) ? $pk_sessions_arr[$res->fields['M_PK_SESSION']] : '-';
				// $index++;
				// $cell_no = $cell[$index] . $line;
				// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ses);




				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);

				$month_year = explode('-', $key_res);
				$y_month = $month_year[0];
				$y_year = $month_year[1];

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($y_year);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($y_month);

				#attended  
				$index++;
				$cell_no = $cell[$index] . $line;
				$att_hours = isset($res_att_monthwise_arr[$key_res]) ? floatval($res_att_monthwise_arr[$key_res]) : 0;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($att_hours);

				$sch_hours = isset($res_sch_monthwise_arr[$key_res]) ? floatval($res_sch_monthwise_arr[$key_res]) : 0;
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($sch_hours);

				$percentage_att = 0;
				if ($sch_hours > 0) {
					$percentage_att = round((($att_hours) / $sch_hours) * 100, 2);
				}
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($percentage_att . "%");
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->applyFromArray(
					array(
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
						)
					)
				);
				if ($_REQUEST['gpa'] == 1) {
					$gpa_cond = "";
					if ($_REQUEST['ENROLLMENT_TYPE'] == 2) {
						$gpa_cond = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
					}
					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(get_gpa());
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				}
			}






			$res->MoveNext();
		}

		if ($_REQUEST['FORMAT'] != 1) {
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			// header("location:" . $outputFileName);
			header('Content-type: application/json; charset=UTF-8');
			$data_res = [];
			$data_res['path'] = $outputFileName;
			echo json_encode($data_res);
			exit;
		} else {
			$objWriterHTML = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
			$html_from_excel  = $objWriterHTML->generateHTMLHeader(true);
			$html_from_excel .= $objWriterHTML->generateSheetData();

			// echo $html_from_excel; exit;
			// if (strpos($html_from_excel, '<col class="col0">')) {
			// 	echo "string found";
			// } else {
			// 	echo "string not found";
			// } 
			#to replace <col> tags
			// $pattern = '/\<col class="([a-z0-9]*)"\>/';
			// $html_from_excel = preg_replace($pattern, '', $html_from_excel);
			#to remove meta tags n repalce title  <col> tags
			$pattern = '/<meta(.*?)\/>/s';
			$html_from_excel = preg_replace($pattern, '', $html_from_excel);
			#to add header rows to thead spontaniously
			preg_match('/<tr class="row0">(.*?)<\/tr>/s', $html_from_excel, $match);

			// $html_from_excel = str_replace('<tbody>', '
			// <thead>
			// <tr>
			// <th>Student</th>
			// <th>Student ID</th>
			// <th>Program</th>
			// <th>Year</th>
			// <th>Month</th>
			// <th>Attended</th>
			// <th>Scheduled</th>
			// <th>Percentage</th>
			// </tr> 
			// </thead><tbody>', $html_from_excel); 

			#Add Title in html of pdf 
			str_replace('<head>', '<head><title>Monthly Attendance Analysis</title>', $html_from_excel);
			#Add thead = first row matched by preg replace 
			$html_from_excel = str_replace('<tbody>', '
			<thead>
			<tr>
		' . $match[1] . '
			</tr> 
			</thead><tbody>', $html_from_excel);

			#remove purple brackground;
			$html_from_excel = str_replace("background-color:#9b8fa0", '', $html_from_excel);
			$html_from_excel = '<style> .row0{ display : none !important}</style>' . $html_from_excel;
			// echo $html_from_excel; 
			// exit;
			$header = invoice_pdf_custom_header();

			// exit;
			// header("location:" . $outputFileName);  

			$date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());


			############# FOR PDF CODE BELOW ############
			$footer = '	
						<table width="100%" border="0"> 
							<tr>
								<td valign="top" style="font-size:10px"><i>' . $date . '</i></td>
								<td valign="top" style="font-size:10px;" align="right" colspan="2">Page <span id="page"></span> of
									<span id="topage"></span>
								</td>
								<td valign="top" style="font-size:10px;"></td>
							</tr>
						</table>';
			$header_cont = '<!DOCTYPE HTML>
						<html>
						
						<head>
							<style>
								div {
									padding-bottom: 20px !important;
								}
								td{
									text-align : center;
								}
								body,html {
									padding: 0;
									margin: 0;
									font-size:0;
								}
								table { page-break-inside:auto }
								tr    { page-break-inside:avoid; page-break-after:auto }
								thead { display:table-header-group }
								tfoot { display:table-footer-group }
							</style>
						</head>
						
						<body>
							<div> ' . $header . ' </div>
						</body>
						
						</html>';
			$html_body_cont = '
						<!DOCTYPE HTML>
						<html>
						
						<head>
							<style>
							body,html {
								padding: 0 !important;
								margin: 0 !important; 
							}
								body {
									font-size: 15px;
								}
						
								table tr {
									padding-top: 15px !important;
									border-bottom : solid 1px black
								}
	
								td{
									text-align : center !important;
									padding : 5px !important;
								}

								
								thead { display: table-header-group; }
tfoot { display: table-row-group; }
tr { page-break-inside: avoid; }
thead, tfoot {
	display: table-row-group;
  }
  table { page-break-inside:auto }
  tr    { page-break-inside:avoid; page-break-after:auto }
  thead { display:table-header-group }
  tfoot { display:table-footer-group }
							</style>
						</head>
						
						<body>' . $html_from_excel . '</body>
						
						</html>';
			$footer_cont = '
						<!DOCTYPE HTML>
						<html>
						
						<head>
							<style>
								tbody td {
									font-size: 14px !important;
								}
								body,html {
									padding: 0;
									margin: 0;
									font-size: 0;
								}
								table { page-break-inside:auto }
								tr    { page-break-inside:avoid; page-break-after:auto }
								thead { display:table-header-group }
								tfoot { display:table-footer-group }
							</style>
						</head>
						
						<body> <span></span>' . $footer . '
						
							<script>
								var vars = {};
								var x = window.location.search.substring(1).split("&");
								for (var i in x) {
									var z = x[i].split("=", 2);
									vars[z[0]] = unescape(z[1]);
								}
								document.getElementById("page").innerHTML = vars.page;
								document.getElementById("topage").innerHTML = vars.topage;
							</script>
						</body>
						
						</html>';

			$header_path = create_html_file('header_student_attendance_analysis_report_wip.html', $header_cont, "invoice");
			$content_path = create_html_file('content_student_attendance_analysis_report_wip.html', $html_body_cont, "invoice");
			$footer_path = create_html_file('footer_student_attendance_analysis_report_wip.html', $footer_cont, "invoice");
			$file_name = "Monthly_Attendance_Analysis" . "_" . $_SESSION['PK_USER'] . "_" . time() . ".pdf";
			$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 296	  --page-height 210 --margin-top 30mm --margin-left 15mm --margin-right 15mm  --margin-bottom 20mm --footer-font-size 8 --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';

			global $http_path;
			$pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);
			exec($pdfdata['exec'], $output, $retval);
			header('Content-type: application/json; charset=UTF-8');
			$data_res = [];
			$data_res['path'] = 'temp/invoice/' . $file_name;
			echo json_encode($data_res);

			unlink('../school/temp/invoice/header_student_attendance_analysis_report_wip.html');
			unlink('../school/temp/invoice/content_student_attendance_analysis_report_wip.html');
			unlink('../school/temp/invoice/footer_student_attendance_analysis_report_wip.html');
			exit;
		}
	}
}


function get_cells()
{
	$cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
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
	return $cell;
}

function init_excel_with_header(&$cell, &$outputFileName, &$objReader, &$objWriter, &$objPHPExcel, &$line, &$index, $start_date, $end_date)
{
	include '../global/excel/Classes/PHPExcel/IOFactory.php';
	$cell = get_cells();
	$dir 			= 'temp/';
	$inputFileType  = 'Excel2007';
	$file_name 		= 'Attendance Analysis.xlsx';
	$outputFileName = $dir . $file_name;
	$outputFileName = str_replace(
		pathinfo($outputFileName, PATHINFO_FILENAME),
		pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
		$outputFileName
	);

	$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
	$objReader->setIncludeCharts(TRUE);
	$objPHPExcel = new PHPExcel();
	$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');



	$line 	= 1;
	$index = -1;

	///
	// $index++;
	// $cell_no = $cell[$index] . $line;
	// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Attendance Monthly Analysis Report");
	// $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	// $objPHPExcel->getActiveSheet()->mergeCells('A1:C1');

	// $line++;
	// $index = -1;
	// $index++;
	// $cell_no = $cell[$index] . $line;
	// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Class Dates between ".$start_date .' and '. $end_date);
	// $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	// $objPHPExcel->getActiveSheet()->mergeCells('A2:C2');

	// $line++;

	$index 	= -1;
	///
	$heading[] = 'Campus';
	$width[]   = 15;
	// if ($_REQUEST['FORMAT'] != 1) {
	// 	$heading[] = 'Student';
	// 	$width[]   = 34;
	// } else {
	// 	$heading[] = 'Student';
	// 	$width[]   = 45;
	// }

	$heading[] = 'Last Name';
	$width[]   = 17;
	$heading[] = 'First Name';
	$width[]   = 17;
	$heading[] = 'Student ID';
	$width[]   = 20;

	$heading[] = 'First Term';
	$width[]   = 20;
	$heading[] = 'Program';
	$width[]   = 25;
	// $heading[] = 'Session';
	// $width[]   = 15;
	$heading[] = 'Status';
	$width[]   = 10;

	$heading[] = 'Year';
	$width[]   = 12;
	$heading[] = 'Month';
	$width[]   = 12;
	$heading[] = 'Attended';
	$width[]   = 14;
	$heading[] = 'Scheduled';
	$width[]   = 17;
	$heading[] = 'Percentage';
	$width[]   = 15;

	if ($_REQUEST['gpa'] == 1) {
		$heading[] = 'GPA';
		$width[]   = 12;
	}



	$i = 0;
	// print_r($heading);
	foreach ($heading as $title) {
		$index++;
		$cell_no = $cell[$index] . $line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
		$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
		if (strpos($title, '*') !== false) {
			$objPHPExcel->getActiveSheet()->mergeCells($cell[$index - 2] . "1:" . $cell_no);
		}

		$i++;
	}


	//apply header style 
	$lcolumn =  $objPHPExcel->getActiveSheet()->getHighestDataColumn();
	$lrow =  $objPHPExcel->getActiveSheet()->getHighestDataRow();
	$objPHPExcel->getActiveSheet()->getStyle("A1:$lcolumn" . '1')->applyFromArray(
		array(
			// 'fill' => array(
			// 	'type' => PHPExcel_Style_Fill::FILL_SOLID,
			// 	'color' => array('rgb' => '9b8fa0')
			// ),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			)
		)
	);
	$objPHPExcel->getActiveSheet()->freezePane('A1');
	$objPHPExcel->getActiveSheet()->getStyle("K:K")->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet()
		->getStyle('A1:' . $lcolumn . $lrow)
		->getAlignment()
		->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	//end of header style
}



function get_gpa()
{
	global $db;
	global $PK_ACCOUNT;;
	global $gpa_cond;
	global $PK_STUDENT_MASTER;
	$Denominator 	= 0;
	$Numerator 		= 0;
	$Numerator1 	= 0;

	$res_course = $db->Execute("select NUMERIC_GRADE, COURSE_UNITS, NUMBER_GRADE  
	FROM
	S_STUDENT_COURSE, M_COURSE_OFFERING_STUDENT_STATUS, S_COURSE_OFFERING, S_COURSE, S_TERM_MASTER, M_SESSION, S_GRADE
	WHERE 
	S_STUDENT_COURSE.PK_ACCOUNT = '$PK_ACCOUNT' AND 
	S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $gpa_cond AND 
	S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
	S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
	S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
	S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
	M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION AND 
	M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND 
	S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE AND 
	SHOW_ON_TRANSCRIPT = 1 AND CALCULATE_GPA = 1");
	while (!$res_course->EOF) {
		$Denominator += $res_course->fields['COURSE_UNITS'];
		$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
		$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];

		$res_course->MoveNext();
	}

	return number_format_value_checker(($Numerator1 / $Denominator), 2);
}
function invoice_pdf_custom_header()
{
	global $db;
	global $http_path;

	$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$SCHOOL_NAME 	= $res->fields['SCHOOL_NAME'];
	$PDF_LOGO 	 	= $res->fields['PDF_LOGO'];

	$logo = "";
	if ($PDF_LOGO != '') {
		//$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
		$PDF_LOGO = str_replace('../', $http_path, $PDF_LOGO);
		$logo = '<img src="' . $PDF_LOGO . '" width="100px" />';
	}

	// $header = '<table width="100%" >
	// 				<tr>
	// 					<td width="15%" valign="top" >' . $logo . '</td>
	// 					<td width="45%" valign="top" style="font-size:25px;font-family: helvetica;padding-top:20px;" >' . $SCHOOL_NAME . '</td>
	// 					<td width="40%" valign="top" >
	// 						<table width="100%" >
	// 							<tr>
	// 								<td align="right" style="font-size:28px;border-bottom:1px solid #000;font-family: helvetica;font-style: italic;" >Monthly Attendance Analysis</td>
	// 							</tr> 
	// 							<tr>
	// 								<td colspan="3" align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" ></td>
	// 							</tr> 
	// 						</table>
	// 					</td>
	// 				</tr>

	// 			</table>';

	$date1 = $_REQUEST['START_DATE_analysis'];


	$date2 = $_REQUEST['AS_OF_DATE'];
	$date_string  = 'All Class Dates';
	// print_r($_REQUEST['START_DATE_analysis']);
	if ($_REQUEST['START_DATE_analysis'] != '' && $_REQUEST['AS_OF_DATE'] != '') {
		// echo "con 1"; exit;
		// echo
		$date_string =  'Class Dates Between ' . $date1 . ' and ' . $date2;
	} else if ($_REQUEST['START_DATE_analysis'] != '' && $_REQUEST['AS_OF_DATE'] == '') {
		// echo "con 12"; exit;
		// echo
		$date_string =  'Class Dates From ' . $date1;
	} else if ($_REQUEST['START_DATE_analysis'] == '' && $_REQUEST['AS_OF_DATE'] != '') {
		// echo "con 13"; exit;
		// echo
		$date_string =  'Class Dates Till ' . $date2;
	}

	// 			$header = '<table width="100%" >
	// 		<tr>
	// 			<td width="20%" valign="top" >'.$logo.'</td>
	// 			<td width="40%" valign="top" style="font-family:helvetica;font-weight:normal;" >
	// 			<table width="100%" >
	// 				<tr><td valign="top" style="font-size:28px; text-align:left !important ; margin-top:30px" >'.$SCHOOL_NAME.'</td></tr>'.
	// 				// <tr><td valign="top" style="font-size:14px; text-align:left !important" >'.$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'].'</td></tr>
	// 				// <tr><td valign="top" style="font-size:14px; text-align:left !important" >'.$res->fields['CITY'].','.$res->fields['STATE_CODE'].','.$res->fields['ZIP'].'</td></tr>
	// 				// <tr><td valign="top" style="font-size:14px;text-align:left !important" >'.$res->fields['PHONE'].'</td></tr>
	// 			'</table>
	// 			</td>
	// 			<td width="40%" valign="top" >
	// 				<table width="100%" >
	// 					<tr>
	// 						<td width="100%" align="right" style="font-size:22px;border-bottom:1px solid #000;font-style: italic;font-family:helvetica;font-weight:normal;" >Monthly Attendance Analysis</td>
	// 					</tr>
	// 					<tr><td width="100%" align="right" style="font-size:16px;solid #000;font-style: italic;font-family:helvetica;font-weight:normal;" >'.$date_string.'</td></tr>										
	// 				</table>
	// 			</td>
	// 		</tr>							
	// </table>';
	$header = '<table width="100%" >
			<tr>
				<td width="15%" valign="top" style="text-align:left" >' . $logo . '</td>
				<td width="40%" valign="top" style="font-family:helvetica;font-weight:normal;" >
				<table width="100%" >
					<tr><td valign="top" style="font-size:28px; text-align: left; margin-left 20px;padding-top:25px;" >' . $SCHOOL_NAME . '</td></tr>
					</table>
				</td>
				<td width="40%" valign="top" >
				<table width="100%" >
									<tr>
										<td width="100%" align="right" style="text-align:right !important;font-size:22px;border-bottom:1px solid #000;font-style: italic;font-family:helvetica;font-weight:normal;padding-top : 20px;" >Monthly Attendance Analysis</td>
									</tr>
									<tr><td width="100%" align="right" style="text-align:right !important;font-size:16px;solid #000;font-style: italic;font-family:helvetica;font-weight:normal;" >' . $date_string . '</td></tr>										
								</table>
				</td>
			</tr>							
	</table>';
	return $header;
}
