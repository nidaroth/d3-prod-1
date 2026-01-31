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
function logtime(&$fp, $start_time_exe, $msg)
{
	$time_spent = (microtime(true) - $start_time_exe) /* / 60*/;
	fwrite($fp, "\n" . $msg . " - <b>" . round($time_spent, 2) . "</b>" . "\n");
	// dump($msg ,$time_spent);
};
if (!empty($_POST) || !empty($_GET)) {

	$start_time_exe = microtime(true);
	$fp = fopen('temp/attendance_roaster_time_log.txt', 'w');
	logtime($fp, $start_time_exe, "Started report generation");
	global $db;
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if ($timezone == '' || $timezone == 0)
		$timezone = 4;
	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

	$TIMEZONE = $res->fields['TIMEZONE'];
	//Ticket # 1194  
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
	logtime($fp, $start_time_exe, "---> Getting Course offerings - sql");
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
	logtime($fp, $start_time_exe, "**** E - Getting Course offerings - sql");
	$group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
	$order_by = " ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";

	if ($_POST['FORMAT'] == 1) {
		$wkhtml = '';
		/*
			#Algorithem - AV

			1. Iterate for COURSE_OFFERING / INSTRUCTORS 

			2. Get all students , make student row htmls and return all enrollment IDs

			3. Loop for Date range 

			4. Iterate for week while , while checking if week is valid and have minimum one scheduled class date.

			5. If yes increase $week_count ; Render the week's data 

			6. Repeat ;  CHECK IF WEEK_COUNT > 4 If YES RE-RENDER TABLE WHILE SAVING OLD HTML


		*/
		logtime($fp, $start_time_exe, "---> Iterating PK COURSE OFERINGS -  sql ");
		#1. Iterate for COURSE_OFFERING / INSTRUCTORS
		$Output_Per_Offering = [];
		$COURSE_OFFERING_HEADERS = [];
		foreach ($PK_COURSE_OFFERING_ARR as $PK_COURSE_OFFERING) {
			// echo "-------------------PK_COURSE_OFFERING :  $PK_COURSE_OFFERING ----------------- ";

			$course_info = get_course_info($PK_COURSE_OFFERING);
			list($START_DATE, $END_DATE) = get_schedule_start_and_end_dates($course_info, $PK_COURSE_OFFERING);


			$course_code_row_with_date_ranges_of_week = '';
			$language_row_with_date_numbers = '';
			$term_start_row_with_s_m_t_w_t_f_s = '';

			$COURSE_OFFERING_HEADERS[$PK_COURSE_OFFERING] = '
			
			
			<div style = "display:block; clear:both; page-break-after:always;"></div>

			<table  style="border : 2px solid black !important" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="33%" style="font-size:17px !important; padding : 6px;" ><b>Instructor: </b>' . $course_info->fields['INSTRUCTOR_NAME'] . '</td>
							<td width="33%" style="font-size:17px !important; padding : 6px;" ><b>Term: </b>' . $course_info->fields['TERM_BEGIN_DATE'] . '</td>
							<td width="33%" style="font-size:17px !important; padding : 6px;" ><b>Course Offering: </b>' . $course_info->fields['COURSE_CODE'] . '(' . $course_info->fields['SESSION'][0] . '-' . $course_info->fields['SESSION_NO'] . ')</td>
							<td width="33%" style="font-size:17px !important; padding : 6px;" ><b>Room: </b>' . $course_info->fields['ROOM_NO'] . '</td>
						</tr>
					</table>
					<br /><br />';

			// echo $txt;
			// exit;


			#################~~~~~~~~~~~###############
			#################~~~~~~~~~~~####################################
			#################~~~~~~~~~~~##################################################


			$week_count = 1;
			#2.1 Get all students - Query
			logtime($fp, $start_time_exe, "---> Get student info");

			$students = get_students_info($PK_COURSE_OFFERING);
			logtime($fp, $start_time_exe, " **** E- Get student info");

			#2.2 Make student row htmls and return all enrollment IDs
			list($PK_STUDENT_ENROLLMENTS, $PK_STUDENT_COURSES, $student_rows) = get_student_rows($students);
			//min-width : 17.96% ; max-witdh : 400px; 
			#check if enrollment and course is not empty 
			if (!empty($PK_STUDENT_ENROLLMENTS) && !empty($PK_STUDENT_COURSES)) {
				#Loop for date range
				$DATE_ITERATOR = $START_DATE;
				$temp_student_rows = $student_rows;
				$Output_Per_4_weeks  = [];
				while (strtotime($DATE_ITERATOR) < strtotime($END_DATE)) {
					# code...


					if (check_valid_week($DATE_ITERATOR, $PK_STUDENT_ENROLLMENTS, $PK_STUDENT_COURSES)) {
						// echo "<br>  we can print this week $DATE_ITERATOR to " . date("Y-m-d", strtotime($DATE_ITERATOR . ' + 6 days'));
						add_week_data_to_std_rows(
							$DATE_ITERATOR,
							$temp_student_rows,
							$course_code_row_with_date_ranges_of_week,
							$language_row_with_date_numbers,
							$term_start_row_with_s_m_t_w_t_f_s,
							$PK_STUDENT_ENROLLMENTS,
							$PK_STUDENT_COURSES
						);
						// dd($temp_student_rows);
						$week_count++;
					} else {
						#do nothing
						// echo "<br> <b>  we cannot print this week $DATE_ITERATOR to </b> " . date("Y-m-d", strtotime($DATE_ITERATOR . ' + 6 days'));

					}

					$DATE_ITERATOR = date("Y-m-d", strtotime($DATE_ITERATOR . ' + 7 days'));
					#end of four weeks 
					if ($_REQUEST['AR_DAYS'] == 'mon_fri') {
						$weeks_per_row = 4;
					} else {
						$weeks_per_row = 4;
					}
					if ($week_count > $weeks_per_row) {
						save_this_4_weeks_go_to_next($week_count, $table, $course_info, $course_code_row_with_date_ranges_of_week, $language_row_with_date_numbers, $term_start_row_with_s_m_t_w_t_f_s, $temp_student_rows, $Output_Per_4_weeks, $student_rows);
					}
				}
				if ($week_count > 1)
					save_this_4_weeks_go_to_next($week_count, $table, $course_info, $course_code_row_with_date_ranges_of_week, $language_row_with_date_numbers, $term_start_row_with_s_m_t_w_t_f_s, $temp_student_rows, $Output_Per_4_weeks, $student_rows);
			}
			$Output_Per_Offering[$PK_COURSE_OFFERING] = $Output_Per_4_weeks;
		} //<----------- Closing PK_COURSE_OFFERING_ARR 
		logtime($fp, $start_time_exe, "*** E - Iterating PK COURSE OFERINGS -  sql l ");
		$html_to_print = prepare_html_for_print($Output_Per_Offering, $COURSE_OFFERING_HEADERS);
		printwkhtml($html_to_print);
		dd("End of code");

		#################~~~~~~~~~~~##################################################
		#################~~~~~~~~~~~####################################
		#################~~~~~~~~~~~############### 
	} else {
		//do nothing ,,,, maybe ??
	}
}
function save_this_4_weeks_go_to_next(

	&$week_count,
	&$table,
	&$course_info,
	&$course_code_row_with_date_ranges_of_week,
	&$language_row_with_date_numbers,
	&$term_start_row_with_s_m_t_w_t_f_s,
	&$temp_student_rows,
	&$Output_Per_4_weeks,
	&$student_rows


) {
	$week_count = 1;

	$table = '';
	$table .=  '<style>
				table, th, td {
					border: 1px solid black;
					border-collapse: collapse;
				}
				</style>
				<table style="table-layout: fixed;" >
				<thead>
				<tr>
				<td style="width:182px; text-align:left !important; padding : 5px;" rowspan="3">
					<b>Course: ' . $course_info->fields['TRANSCRIPT_CODE'] . '(' . $course_info->fields['SESSION'][0] . '-' . $course_info->fields['SESSION_NO'] . ')<br>' . $course_info->fields['COURSE_DESCRIPTION'] . '<br> Term Start:' . $course_info->fields['BEGIN_DATE_1'] . '</b>
				</td>';


	$table .= "$course_code_row_with_date_ranges_of_week </tr>";
	$table .= "<tr>$language_row_with_date_numbers</tr>";
	$table .= "<tr>$term_start_row_with_s_m_t_w_t_f_s</tr>
	</thead>";


	$course_code_row_with_date_ranges_of_week = '';
	$language_row_with_date_numbers = '';
	$term_start_row_with_s_m_t_w_t_f_s = '';

	foreach ($temp_student_rows as $temp_student_row) {
		$table .=  $temp_student_row['html'] . '</tr>';
	}
	$table .=   '</table> <br>';

	$Output_Per_4_weeks[] = $table;
	// echo "<br>---------========= END OF FOUR WEEKS ===========-----<br>";
	// print_r($Output_Per_4_weeks);
	$table = '';
	$temp_student_rows = $student_rows;
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

	$date1 = $_REQUEST['START_DATE_NEW'];
	$date2 = $_REQUEST['AS_OF_DATE'];
	$date_string  = 'All Class Dates';
	// print_r($_REQUEST['START_DATE_analysis']);
	if ($_REQUEST['START_DATE_NEW'] != '' && $_REQUEST['AS_OF_DATE'] != '') {
		$date_string =  'Class Dates Between ' . $date1 . ' and ' . $date2;
	} else if ($_REQUEST['START_DATE_NEW'] != '' && $_REQUEST['AS_OF_DATE'] == '') {
		$date_string =  'Class Dates From ' . $date1;
	} else if ($_REQUEST['START_DATE_NEW'] == '' && $_REQUEST['AS_OF_DATE'] != '') {
		$date_string =  'Class Dates Till ' . $date2;
	}

	// $header = '<table width="100%" >
	// 		<tr>
	// 			<td width="20%" valign="top" >' . $logo . '</td>
	// 			<td width="40%" valign="top" style="font-family:helvetica;font-weight:normal;" >
	// 			<table width="100%" >
	// 				<tr><td valign="top" style="font-size:28px; text-align:left !important" >' . $SCHOOL_NAME . '</td></tr>
	// 				<tr><td valign="top" style="font-size:14px; text-align:left !important" >' . $res->fields['ADDRESS'] . ' ' . $res->fields['ADDRESS_1'] . '</td></tr>
	// 				<tr><td valign="top" style="font-size:14px; text-align:left !important" >' . $res->fields['CITY'] . ',' . $res->fields['STATE_CODE'] . ',' . $res->fields['ZIP'] . '</td></tr>
	// 				<tr><td valign="top" style="font-size:14px;text-align:left !important" >' . $res->fields['PHONE'] . '</td></tr>
	// 			</table>
	// 			</td>
	// 			<td width="40%" valign="top" >
	// 				<table width="100%" >
	// 					<tr>
	// 						<td width="100%" align="right" style="font-size:32px;border-bottom:1px solid #000;font-style: italic;font-family:helvetica;font-weight:normal;" >Monthly Attendance Analysis</td>
	// 					</tr>
	// 					<tr><td width="100%" align="right" style="font-size:16px;solid #000;font-style: italic;font-family:helvetica;font-weight:normal;" >' . $date_string . '</td></tr>										
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
										<td width="100%" align="right" style="font-size:22px;border-bottom:1px solid #000;font-style: italic;font-family:helvetica;font-weight:normal;padding-top : 20px;text-align:right !important" >Attendance Roster - Weekly</td>
									</tr>
									<tr><td width="100%" align="right" style="font-size:16px;solid #000;font-style: italic;font-family:helvetica;font-weight:normal;text-align:right !important" >' . $date_string . '</td></tr>										
								</table>
				</td>
			</tr>							
	</table>';

	return $header;
}

function pdf_footer()
{
	global $TIMEZONE;
	// dd($TIMEZONE);
	$date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());

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
	return $footer;
}


function get_course_info($PK_COURSE_OFFERING)
{
	global $db;
	$course_info_sql =
		"SELECT 
			DATE_FORMAT(DEF_START_TIME,'%h:%i %p') AS START_TIME, 
			DATE_FORMAT(DEF_END_TIME,'%h:%i %p') AS END_TIME, 
			HOURS, 
			CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO,FA_UNITS,  
			UNITS, 
			CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,' ',S_EMPLOYEE_MASTER_INST.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME,ATTENDANCE_TYPE, 
			IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 , 
			IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.END_DATE, '%m/%d/%Y' )) AS  END_DATE_1,
			SESSION,
			SESSION_NO,
			COURSE_OFFERING_STATUS, 
			COURSE_CODE,
			TRANSCRIPT_CODE, 
			IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE,
			COURSE_DESCRIPTION, 
			IF(S_COURSE_OFFERING_SCHEDULE.START_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.START_DATE, '%m/%d/%Y' )) AS  SCHEDULE_START_DATE, 
			IF(S_COURSE_OFFERING_SCHEDULE.END_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.END_DATE, '%m/%d/%Y' )) AS  SCHEDULE_END_DATE
		FROM S_COURSE_OFFERING 
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
			S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE
		";
	// dds($course_info_sql);
	return $db->Execute($course_info_sql);
}

function get_schedule_start_and_end_dates(&$course_info, $PK_COURSE_OFFERING)
{
	global $db;

	if ($course_info->fields['SCHEDULE_START_DATE'] == '') {
		$res_det = $db->Execute("select IF(MIN(SCHEDULE_DATE) = '0000-00-00','',DATE_FORMAT(MIN(SCHEDULE_DATE), '%m/%d/%Y' )) AS SCHEDULE_DATE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'  ");
		$SCHEDULE_START_DATE = $res_det->fields['SCHEDULE_DATE'];
	} else
		$SCHEDULE_START_DATE = $course_info->fields['SCHEDULE_START_DATE'];

	if ($course_info->fields['SCHEDULE_END_DATE'] == '') {
		$res_det = $db->Execute("select IF(MAX(SCHEDULE_DATE) = '0000-00-00','',DATE_FORMAT(MAX(SCHEDULE_DATE), '%m/%d/%Y' )) AS SCHEDULE_DATE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'  ");
		$SCHEDULE_END_DATE = $res_det->fields['SCHEDULE_DATE'];
	} else
		$SCHEDULE_END_DATE = $course_info->fields['SCHEDULE_END_DATE'];

	$START_DATE = date("Y-m-d", strtotime('sunday this week', strtotime($SCHEDULE_START_DATE)));
	$END_DATE 	= $SCHEDULE_END_DATE;
	if (isset($_REQUEST['START_DATE_NEW']) && $_REQUEST['START_DATE_NEW'] != '') {
		$START_DATE = date("Y-m-d", strtotime('sunday this week', strtotime($_REQUEST['START_DATE_NEW'])));
	}
	if (isset($_REQUEST['AS_OF_DATE']) && $_REQUEST['AS_OF_DATE'] != '') {
		$END_DATE = $_REQUEST['AS_OF_DATE'];
	}

	$END_DATE = date("Y-m-d", strtotime('saturday this week', strtotime($END_DATE)));
	// dd($START_DATE , $END_DATE);
	return [$START_DATE, $END_DATE];
}
function get_students_info($PK_COURSE_OFFERING)
{
	global $db;
	$students_query = "SELECT CONCAT(LAST_NAME,', ',FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) as STUD_NAME, STUDENT_ID, S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,  PK_STUDENT_COURSE 
					FROM
					S_STUDENT_COURSE,  S_STUDENT_MASTER, S_STUDENT_ACADEMICS   
					WHERE 
					S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
					S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
					S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
					S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' 
					ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC 
					";
	return $students = $db->Execute($students_query);
}

function get_student_rows(&$students_res)
{

	$PK_STUDENT_ENROLLMENTS = [];
	$PK_STUDENT_COURSES = [];
	$student_rows = [];
	$width = '252px';
	if ($_REQUEST['AR_DAYS'] == 'mon_fri') {
		$width = '282px';
	}
	while (!$students_res->EOF) {
		# code ... 
		$PK_STUDENT_ENROLLMENTS[] = $PK_STUDENT_ENROLLMENT 	= $students_res->fields['PK_STUDENT_ENROLLMENT'];
		$PK_STUDENT_COURSES[] = $PK_STUDENT_COURSE 		= $students_res->fields['PK_STUDENT_COURSE'];
		$student_rows[$PK_STUDENT_ENROLLMENT] =
			[
				'PK_STUDENT_ENROLLMENT' => $PK_STUDENT_ENROLLMENT,
				'PK_STUDENT_COURSE' => $PK_STUDENT_COURSE,
				'html' => '<tr>
			<td style="width:' . $width . ' !important;text-align:left !important;padding : 5px;">' .
					$students_res->fields['STUDENT_ID'] . ' ' . $students_res->fields['STUD_NAME']
					. '</td>'
			];
		$students_res->MoveNext();
	}

	return [$PK_STUDENT_ENROLLMENTS, $PK_STUDENT_COURSES, $student_rows];
}
function check_valid_week($WEEK_START_DATE, $PK_STUDENT_ENROLLMENTS, $PK_STUDENT_COURSES)
{
	global $db;
	global $PK_COURSE_OFFERING;
	$WEEK_END_DATE = date("Y-m-d", strtotime($WEEK_START_DATE . ' + 6 days'));

	$check_week_sql = "SELECT SCHEDULE_DATE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND SCHEDULE_DATE BETWEEN '$WEEK_START_DATE' AND '$WEEK_END_DATE' LIMIT 1";
	$check_week_res = $db->Execute($check_week_sql);
	if ($check_week_res->RecordCount() > 0) {
		return true;
	} else {
		#check if data exists on S_STUDENT_SCHEDULE
		$check_week_sql_s = "SELECT SCHEDULE_DATE FROM S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN (" . implode(',', $PK_STUDENT_ENROLLMENTS) . ") AND  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE IN (" . implode(',', $PK_STUDENT_COURSES) . ") AND  SCHEDULE_DATE BETWEEN '$WEEK_START_DATE' AND '$WEEK_END_DATE' LIMIT 1";
		$check_week_res = $db->Execute($check_week_sql_s);
		if ($check_week_res->RecordCount() > 0) {
			return true;
		}

		// dds($check_week_sql , $check_week_sql_s);
		return false;
	}
}

function is_scheduled_date($DATE, $PK_STUDENT_ENROLLMENTS, $PK_STUDENT_COURSES)
{
	global $db;
	global $PK_COURSE_OFFERING;

	$check_week_sql = "SELECT SCHEDULE_DATE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND SCHEDULE_DATE = '$DATE' LIMIT 1";
	$check_week_res = $db->Execute($check_week_sql);
	if ($check_week_res->RecordCount() > 0) {
		return true;
	} else {
		#check if data exists on S_STUDENT_SCHEDULE
		$check_week_sql_s = "SELECT SCHEDULE_DATE FROM S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN (" . implode(',', $PK_STUDENT_ENROLLMENTS) . ") AND  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE IN (" . implode(',', $PK_STUDENT_COURSES) . ") AND  SCHEDULE_DATE = '$DATE' LIMIT 1";
		$check_week_res = $db->Execute($check_week_sql_s);
		if ($check_week_res->RecordCount() > 0) {

			return true;
		}

		// dds($check_week_sql , $check_week_sql_s);
		return false;
	}
}

function add_week_data_to_std_rows(
	$START_DATE,
	&$student_rows,
	&$course_code_row_with_date_ranges_of_week,
	&$language_row_with_date_numbers,
	&$term_start_row_with_s_m_t_w_t_f_s,
	$PK_STUDENT_ENROLLMENTS,
	$PK_STUDENT_COURSES
) {
	global $db;
	/* Styles Setting */
	if ($_REQUEST['AR_DAYS'] == 'mon_fri') {
		$header_size = '182px;';
		$dt_range_width = '256px !important;';
	} else {
		$header_size = '182px;';
		$dt_range_width = '256px !important;';
	}


	$colspan = $_REQUEST['AR_DAYS'] == 'mon_fri' ? 5 : 7;
	$course_code_row_with_date_ranges_of_week .= '<td style=" width :  ' . $dt_range_width . ' ; padding : 5px; border : 2px solid black;" colspan="' . $colspan . '"> ' . date("M d", strtotime($START_DATE)) . ' - ' . date("M d", strtotime($START_DATE . ' + 6 days')) . '</td>';
	$END_DATE = date("Y-m-d", strtotime($START_DATE . ' + 6 days'));
	$first_flag = true;
	// dump('student_rows b4' , $student_rows);

	for ($i = 0; $i <= 6; $i++) {
		$DATE = date("Y-m-d", strtotime($START_DATE . ' + ' . $i . ' days'));
		if (
			!($_REQUEST['AR_DAYS'] == 'mon_fri'
				&&
				in_array(strtolower(date("l", strtotime($START_DATE . ' + ' . $i . ' days'))), ['sunday', 'saturday'])
			)
			&& (strtotime($DATE) <= strtotime($END_DATE))
		) {
			$styler = " ";
			if (is_scheduled_date($DATE, $PK_STUDENT_ENROLLMENTS, $PK_STUDENT_COURSES)) {
				$styler = ' style="border : 2px solid black;background : #cbcbcb" ';
			} else {
				$styler = " style='border : 2px solid black;' ";
			}
			$language_row_with_date_numbers .= "<td $styler  class='att_field'>" . (date('d', strtotime($DATE))) . "</td>";
			$term_start_row_with_s_m_t_w_t_f_s .= "<td $styler  class='att_field'>" . (date('l', strtotime($DATE))[0]) . "</td>";
		}
	}
	for ($i = 0; $i <= 6; $i++) {
		$DATE = date("Y-m-d", strtotime($START_DATE . ' + ' . $i . ' days'));

		foreach ($student_rows as $row_key => $student_row) {

			if (
				!($_REQUEST['AR_DAYS'] == 'mon_fri'
					&&
					in_array(strtolower(date("l", strtotime($START_DATE . ' + ' . $i . ' days'))), ['sunday', 'saturday'])
				)
				&& (strtotime($DATE) <= strtotime($END_DATE))
			) {


				$PK_STUDENT_ENROLLMENT = $student_row['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_COURSE = $student_row['PK_STUDENT_COURSE'];
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
					// $ATTENDANCE_HOURS = '<table width="100%" ><tr><td style="border: 1px solid #FF0000;" >' . $ATTENDANCE_HOURS . '</td></tr></table>';
					$ATTENDANCE_HOURS = '<td width="36px" style="border: 2px solid #FF0000; border-collapse: collapse; "  class="att_field">' . $ATTENDANCE_HOURS . '</td>';
				} else {

					$ATTENDANCE_HOURS = '<td class="att_field" width="36px">' . $ATTENDANCE_HOURS . '</td>';
				}
				// dump('before',$student_row);
				$student_rows[$row_key]['html'] = $student_rows[$row_key]['html'] . ' ' . $ATTENDANCE_HOURS;
				// dump('$ATTENDANCE_HOURS' , $ATTENDANCE_HOURS);
				// dump('student_row after' , $student_row);
				/* Ticket # 1270 */
			}
		}
		$first_flag = false;
	}
	// dump('Changed rows AFTER', $student_rows);
	return $student_row;
}


function printwkhtml($html_to_print)
{
	$header = invoice_pdf_custom_header();
	$footer = pdf_footer();



	$header_cont = '<!DOCTYPE HTML>
						<html>
						
						<head>
							<style>
								div {
									padding-bottom: 20px !important;
								}
								td{
									text-align : center !important;
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
					 
								.new-page {
								  page-break-before: always;
								}
							 
							body,html {
								padding: 0 !important;
								margin: 0 !important; 
							}
								body {
									font-size: 21px;
								}
						table{
							table-layout: fixed;
						}
						table td{
							table-layout: fixed;
						}
						
						td:not(:first-child){
							max-width : 37px !important;
						}';


	if ($_REQUEST['AR_DAYS'] == 'mon_fri') {
		$width = '292px';
		$html_body_cont .= 'td:first-child{
		max-width : ' . $width . ' !important;
		
	}';
	} else {
		$width = '252px';
		$html_body_cont .= ' td:first-child{
			max-width : ' . $width . ' !important;
			
		} ';
	}
	$html_body_cont .= '
						tr:first-child{
							padding : 10px !important;
						}
								table tr {
									padding-top: 15px !important;
									border-bottom : solid 1px black
								
								}
								.att_field{ width : 37px !important; height : 37px !important; padding : 0px !important}

								td{
									text-align : center !important;
									/* padding : 5px !important; */
									font-size : 17px !important; 
								}

								
								thead { display: table-header-group; }
tfoot { display: table-row-group; }
tr { page-break-inside: avoid; }
thead { display: table-header-group; }
tfoot { display: table-row-group; }
tr { page-break-inside: avoid; }
							</style>
						</head>
						
						<body>' . $html_to_print . '</body>
						
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


	$header_path = create_html_file('header.html', $header_cont, "invoice");
	$content_path = create_html_file('content.html', $html_body_cont, "invoice");
	$footer_path = create_html_file('footer.html', $footer_cont, "invoice");
	$file_name = "Monthly_Attendance_Analysis.pdf";
	$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 296 --page-height 210 --margin-top 40mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';
	global $http_path;
	$pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);
	exec($pdfdata['exec'], $output, $retval);
	header('Content-type: application/json; charset=UTF-8');
	$data_res = [];
	$data_res['path'] = 'temp/invoice/' . $file_name;
	echo json_encode($data_res);
	exit;
}

function prepare_html_for_print($Output_Per_Offering, $COURSE_OFFERING_HEADERS)
{

	$Total_table_html = '';

	foreach ($Output_Per_Offering as $PK => $Course_offering) {
		$Total_table_html .= $COURSE_OFFERING_HEADERS[$PK];
		foreach ($Course_offering as $table) {
			$Total_table_html .= $table;
		}
	}
	return $Total_table_html;
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
					// document.form1.submit();

					var form = $("#form1");
					var url = 'attendance_roster';
					$.ajax({
						type: "POST",
						url: url,
						data: form.serialize(),
						success: function(data) {

							// Ajax call completed successfully
							// alert("Form Submited Successfully");
							const text = window.location.href;
							const word = '/school';
							const textArray = text.split(word); // ['This is ', ' text...']
							const result = textArray.shift();
							downloadDataUrlFromJavascript("Attendance Roster - Weekly Report.pdf", result + '/school/' + data.path)

						},
						error: function(data) {

							// Some error in ajax call
							// alert("some Error");
						}
					});
				}
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

</body>

</html>