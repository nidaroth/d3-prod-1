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
	// fwrite($fp, "\n" . $msg . " - <b>" . round($time_spent, 2) . "</b>" . "\n");
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
		if (in_array('Unassigned', $_REQUEST['INSTRUCTOR'])) {
			$INSTRUCTOR = $_POST['INSTRUCTOR'];
			foreach ($INSTRUCTOR as $key => $value) {
				if ($value == "Unassigned") {
					unset($INSTRUCTOR[$key]);
				}
			}
			$INSTRUCTOR = implode(",", $INSTRUCTOR);
			$cond_instructors = " AND ( INSTRUCTOR IN ($INSTRUCTOR) OR INSTRUCTOR IS NULL OR INSTRUCTOR = 0) ";
		} else {

			$INSTRUCTOR = implode(",", $_POST['INSTRUCTOR']);
			$cond_instructors = " AND INSTRUCTOR IN ($INSTRUCTOR) ";
		}

		$cond .= " AND S_COURSE_OFFERING.PK_TERM_MASTER = '$_POST[PK_TERM_MASTER]' $cond_instructors ";
	}
	logtime($fp, $start_time_exe, "---> Getting Course offerings - sql");



	$query =
		"SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING, TRANSCRIPT_CODE, COURSE_DESCRIPTION
FROM S_COURSE_OFFERING
LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE  
LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING
WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
    $cond
GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING
ORDER BY TRANSCRIPT_CODE";



	// echo $query;
	// exit;
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
			// echo "PK_COURSE_OFFERING".$PK_COURSE_OFFERING;
			// echo "-------------------PK_COURSE_OFFERING :  $PK_COURSE_OFFERING ----------------- ";

			$course_info = get_course_info($PK_COURSE_OFFERING);
			list($START_DATE, $END_DATE) = get_schedule_start_and_end_dates($course_info, $PK_COURSE_OFFERING);

			// print_r(" START DATE : $START_DATE   - END DATE : $END_DATE COURSE_CODE :" . $course_info->fields['COURSE_CODE']);
			// exit;
			$course_code_row_with_date_ranges_of_week = '';
			$language_row_with_date_numbers = '';
			$term_start_row_with_s_m_t_w_t_f_s = '';
			$term_header_date_range =  date("m/d/Y", strtotime(getThisWeeksMondayOrSelected($START_DATE))) . ' - ' . date("m/d/Y", strtotime(getThisWeeksFridayOrSelected($END_DATE)));
			$COURSE_OFFERING_HEADERS[$PK_COURSE_OFFERING] = '
			
			<style>
			.term_header {
				max-width : 100% !important; text-align : right !important; font-size : 15px !important;
			}
			</style>
			<div style = "display:block; clear:both; page-break-after:always;"></div>

			<table  class="noborder" cellspacing="0" cellpadding="3" width="100%">

			<tr class="noborder"><td class="noborder term_header"> Campus : ' . $course_info->fields['CAMPUS_CODE'] . '</td> </tr>
			<tr class="noborder"><td class="noborder term_header"> Term : ' . $course_info->fields['TERM_BEGIN_DATE'] . '</td></tr>
			<tr class="noborder"><td class="noborder term_header"> Course Offering : ' . $course_info->fields['COURSE_CODE'] . '(' . $course_info->fields['SESSION'][0] . '-' . $course_info->fields['SESSION_NO'] . ')</td>  </tr>
			<tr class="noborder"><td class="noborder term_header"> Room : ' . $course_info->fields['ROOM_NO'] . '</td> </tr>
			<tr class="noborder"><td class="noborder term_header"> Instructor : ' . $course_info->fields['INSTRUCTOR_NAME'] . ' </tr>
			<tr class="noborder"><td class="noborder term_header"> Class Week :  ' . $term_header_date_range . ' </td></tr>
			<tr class="noborder"><td class="noborder term_header"> Start Time : ' . $course_info->fields['START_TIME'] . '</td> </tr>
			</table>';

			if ($START_DATE == null || $END_DATE == null) {
				$COURSE_OFFERING_HEADERS[$PK_COURSE_OFFERING] .= " <div style='color : black; font-size:14px; text-align:center; margin-top : 25px;'> 
				Sorry, the schedule for this course cannot be generated at this time due to missing valid start and end dates as schedule is not generated when creating this course. 

				Weekly Sign In sheet still can be generated if you input both the Scheduled Start Date and Schedule End Date in the form. If you encounter any difficulties, we recommend creating a schedule from the 'Manage' menu, under 'Course Offering' > 'Schedule' tab.
				</div>";
				// echo "hello";
				// exit;
			}
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

					$seven_day_header = '';
					if (check_valid_week($DATE_ITERATOR, $PK_STUDENT_ENROLLMENTS, $PK_STUDENT_COURSES)) {
						// echo "<br>  we can print this week $DATE_ITERATOR to " . date("Y-m-d", strtotime($DATE_ITERATOR . ' + 6 days')).$course_info->fields['COURSE_CODE'];

						// print_r($temp_student_rows);
						// exit;
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
						$seven_day_header = '<td style ="width:130px !important">'  .
							date("m/d/Y", strtotime($DATE_ITERATOR . ' + 0 days')) . '<br> Monday' . ' </td><td style ="width:130px !important">' .
							date("m/d/Y", strtotime($DATE_ITERATOR . ' + 1 days')) . '<br> Tuesday' . ' </td><td style ="width:130px !important">' .
							date("m/d/Y", strtotime($DATE_ITERATOR . ' + 2 days')) . '<br> Wednesday' . ' </td><td style ="width:130px !important">' .
							date("m/d/Y", strtotime($DATE_ITERATOR . ' + 3 days')) . '<br> Thursday' . ' </td><td style ="width:130px !important">' .
							date("m/d/Y", strtotime($DATE_ITERATOR . ' + 4 days')) . '<br> Friday' . ' </td> ';
						$week_count++;
					} else {
						#do nothing
						// echo "<br> <b>  we cannot print this week $DATE_ITERATOR to </b> " . date("Y-m-d", strtotime($DATE_ITERATOR . ' + 6 days'));

					}

					$DATE_ITERATOR = date("Y-m-d", strtotime($DATE_ITERATOR . ' + 7 days'));
					#end of four weeks 
					if ($_REQUEST['AR_DAYS'] == 'mon_fri') {
						$weeks_per_row = 1;
					} else {
						$weeks_per_row = 1;
					}
					if ($week_count > $weeks_per_row) {
						save_this_4_weeks_go_to_next($week_count, $table, $course_info, $course_code_row_with_date_ranges_of_week, $language_row_with_date_numbers, $term_start_row_with_s_m_t_w_t_f_s, $temp_student_rows, $Output_Per_4_weeks, $student_rows, $DATE_ITERATOR, $seven_day_header);
					}
				}
				if ($week_count > 1)
					save_this_4_weeks_go_to_next($week_count, $table, $course_info, $course_code_row_with_date_ranges_of_week, $language_row_with_date_numbers, $term_start_row_with_s_m_t_w_t_f_s, $temp_student_rows, $Output_Per_4_weeks, $student_rows, $DATE_ITERATOR, $seven_day_header);
				$Output_Per_Offering[$PK_COURSE_OFFERING] = $Output_Per_4_weeks;
			} else {
				$COURSE_OFFERING_HEADERS[$PK_COURSE_OFFERING] .= " <div style='color : black; font-size:14px; text-align:center; margin-top : 25px;'> 
				Selected course do not have any active student.
				</div>";
				$Output_Per_Offering[$PK_COURSE_OFFERING] = '';
				$Output_Per_4_weeks  = [];
			}
		} //<----------- Closing PK_COURSE_OFFERING_ARR 

		$html_to_print = prepare_html_for_print($Output_Per_Offering, $COURSE_OFFERING_HEADERS);
		// exit; 
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
	&$student_rows,
	$DATE_ITERATOR,
	$seven_day_header


) {
	$week_count = 1;

	$table = '';
	// $table .=  '<style>
	// 			table, th, td {
	// 				border: 1px solid black;
	// 				border-collapse: collapse;
	// 			}
	// 			</style>
	// 			<table style="table-layout: fixed;" >
	// 			<thead>
	// 			<tr>
	// 			<td style="width:182px; text-align:left !important; padding : 5px;" rowspan="3">
	// 				<b>Course: ' . $course_info->fields['TRANSCRIPT_CODE'] . '(' . $course_info->fields['SESSION'][0] . '-' . $course_info->fields['SESSION_NO'] . ')<br>' . $course_info->fields['COURSE_DESCRIPTION'] . '<br> Term Start:' . $course_info->fields['BEGIN_DATE_1'] . '</b>
	// 			</td>';

	$table .=  '<style>
	table, th, td {
		border: 1px solid black;
		border-collapse: collapse;
	}
	</style>
	<table style="table-layout: fixed; " >
	<thead>
	<tr>
	<td style="width:142px; text-align:left !important; padding : 5px;" rowspan="1">
		 Student
	</td>
	<td style="width:100px; text-align:left !important; padding : 5px;" rowspan="1">
		 Student ID
	</td>
	<td style="width:85px !important; text-align:left !important; padding : 5px;" rowspan="1">
		 Course Offering Student Status
	</td>';





	$table .= "$seven_day_header </tr>";
	// $table .= "<tr>$language_row_with_date_numbers</tr>";
	// $table .= "<tr>$term_start_row_with_s_m_t_w_t_f_s</tr>
	// </thead>";
	$table .= "</thead>";

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
										<td width="100%" align="right" style="font-size:21px;border-bottom:1px solid #000;font-style: italic;font-family:helvetica;font-weight:normal;padding-top : 20px;text-align:right !important" >Attendance Roster - Weekly Sign In</td>
									</tr>
									<tr><td width="100%" align="right" style="font-size:14px !important;solid #000;font-style: italic;font-family:helvetica;font-weight:normal;text-align:right !important" >' . $date_string . '</td></tr>										
								</table>
				</td>
			</tr>							
	</table>';

	return $header;
}

function pdf_footer()
{
	global $TIMEZONE;
	global $db;
	// dd($TIMEZONE);
	$date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());


	$PK_PDF_FOOTER = 12;
	$res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = '$PK_PDF_FOOTER' AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER "); //AND PK_CAMPUS = '$PK_CAMPUS'

	$CONTENT = nl2br($res_type->fields['CONTENT']);



	$footer = '				<table width="100%" border="0"> 
								<tr>
									<td valign="top" style="font-size:10px ; text-align:center">' . $CONTENT . '</td>
								</tr>
							</table>
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
			CAMPUS_CODE,
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
			LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS
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
function getThisWeeksMondayOrSelected($selectedDate)
{
	// Check if the selected date is a Monday
	if (date("N", strtotime($selectedDate)) == 1) {
		return $selectedDate; // Return the selected date if it's Monday
	} else if (date("N", strtotime($selectedDate)) == 7) {
		$pastMonday = date("Y-m-d", strtotime('next Monday', strtotime($selectedDate)));
		return $pastMonday; //Return closest monday
	} else {
		// Calculate the past Monday from the selected date
		$pastMonday = date("Y-m-d", strtotime('last Monday', strtotime($selectedDate)));
		return $pastMonday;
	}
}

function getThisWeeksFridayOrSelected($selectedDate)
{
	// Check if the selected date is a Friday (5 corresponds to Friday)
	if (date("N", strtotime($selectedDate)) == 5) {
		return $selectedDate; // Return the selected date if it's Friday
	} else if (date("N", strtotime($selectedDate)) == 6) {
		$thisWeeksFriday = date("Y-m-d", strtotime('last Friday', strtotime($selectedDate)));
		return $thisWeeksFriday;
	} else {
		// Calculate this week's Friday from the selected date
		$thisWeeksFriday = date("Y-m-d", strtotime('this Friday', strtotime($selectedDate)));
		return $thisWeeksFriday;
	}
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
	#get latest monday w.r.t. either DB's MIN SCHEDULE_DATE or USER Inputed START DATE
	if ($SCHEDULE_START_DATE == null) {
		if (isset($_REQUEST['START_DATE_NEW'])) {
			if ($_REQUEST['START_DATE_NEW'] != null || $_REQUEST['START_DATE_NEW'] != '') {
				$START_DATE = getThisWeeksMondayOrSelected($_REQUEST['START_DATE_NEW']);
			}
		} else {
			$START_DATE = null;
		}
	} else {

		if ((strtotime($_REQUEST['START_DATE_NEW']) >  strtotime($SCHEDULE_START_DATE)) && $_REQUEST['START_DATE_NEW'] != '') {
			$START_DATE = getThisWeeksMondayOrSelected($_REQUEST['START_DATE_NEW']);
		} else {
			$START_DATE = getThisWeeksMondayOrSelected($SCHEDULE_START_DATE);
		}
	}
	#get latest monday w.r.t. either DB's MAX SCHEDULE_DATE or USER Inputed END DATE

	if ($SCHEDULE_END_DATE == null) {
		if (isset($_REQUEST['AS_OF_DATE'])) {
			if ($_REQUEST['AS_OF_DATE'] != null || $_REQUEST['AS_OF_DATE'] != '') {
				$END_DATE = getThisWeeksFridayOrSelected($_REQUEST['AS_OF_DATE']);
			}
		} else {
			$END_DATE = null;
		}
	} else {
		if ((strtotime($_REQUEST['AS_OF_DATE']) <  strtotime($SCHEDULE_END_DATE)) && $_REQUEST['AS_OF_DATE'] != '') {
			$END_DATE = getThisWeeksFridayOrSelected($_REQUEST['AS_OF_DATE']);
		} else {
			$END_DATE = getThisWeeksFridayOrSelected($SCHEDULE_END_DATE);
		}
	}
	if ($_REQUEST['START_DATE_NEW']) {
		$START_DATE = getThisWeeksMondayOrSelected($_REQUEST['START_DATE_NEW']);
	}
	if ($_REQUEST['AS_OF_DATE'] != '') {
		$END_DATE = getThisWeeksFridayOrSelected($_REQUEST['AS_OF_DATE']);
	}

	// if (isset($_REQUEST['START_DATE_NEW']) && $_REQUEST['START_DATE_NEW'] != '') {
	// 	$START_DATE = date("Y-m-d", strtotime('sunday this week', strtotime($_REQUEST['START_DATE_NEW'])));
	// }
	// if (isset($_REQUEST['AS_OF_DATE']) && $_REQUEST['AS_OF_DATE'] != '') {
	// 	$END_DATE = $_REQUEST['AS_OF_DATE'];
	// }

	// $END_DATE = date("Y-m-d", strtotime('saturday this week', strtotime($END_DATE)));
	// dd($START_DATE , $END_DATE);
	return [$START_DATE, $END_DATE];
}
function get_students_info($PK_COURSE_OFFERING)
{
	global $db;
	// $students_query = "SELECT CONCAT(LAST_NAME,', ',FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) as STUD_NAME, STUDENT_ID, S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,  PK_STUDENT_COURSE 
	// 				FROM
	// 				S_STUDENT_COURSE,  S_STUDENT_MASTER, S_STUDENT_ACADEMICS   
	// 				WHERE 
	// 				S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	// 				S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	// 				S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
	// 				S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' 
	// 				ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC 
	// 				";

	$students_query = "
SELECT
	CONCAT(LAST_NAME, ', ', FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) as STUD_NAME,
	STUDENT_ID,
	S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,
	PK_STUDENT_COURSE,
	STUDENT_STATUS
FROM
	S_STUDENT_COURSE
LEFT JOIN
	S_STUDENT_MASTER ON S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
LEFT JOIN
	S_STUDENT_ACADEMICS ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER
LEFT JOIN
S_STUDENT_ENROLLMENT ON S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 	
WHERE
	S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
	AND S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'
ORDER BY
	TRIM(CONCAT(LAST_NAME, ', ', FIRST_NAME, ' ', S_STUDENT_MASTER.MIDDLE_NAME)) ASC;
 ";
	return $students = $db->Execute($students_query);
}

function get_student_rows(&$students_res)
{

	$PK_STUDENT_ENROLLMENTS = [];
	$PK_STUDENT_COURSES = [];
	$student_rows = [];
	$width = '200px';
	if ($_REQUEST['AR_DAYS'] == 'mon_fri') {
		$width = '200px';
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
					$students_res->fields['STUD_NAME']
					. '</td>
				<td style="width: 85px !important;text-align:left !important;padding : 5px;">' .
					$students_res->fields['STUDENT_ID']
					. '</td><td style="width:85px !important;text-align:left !important;padding : 5px;">' .
					$students_res->fields['STUDENT_STATUS']
					. '</td>'


			];
		$students_res->MoveNext();
	}

	return [$PK_STUDENT_ENROLLMENTS, $PK_STUDENT_COURSES, $student_rows];
}
function check_valid_week($WEEK_START_DATE, $PK_STUDENT_ENROLLMENTS, $PK_STUDENT_COURSES)
{

	// global $db;
	// global $PK_COURSE_OFFERING;
	// $WEEK_END_DATE = date("Y-m-d", strtotime($WEEK_START_DATE . ' + 6 days'));

	// $check_week_sql = "SELECT SCHEDULE_DATE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND SCHEDULE_DATE BETWEEN '$WEEK_START_DATE' AND '$WEEK_END_DATE' LIMIT 1";
	// $check_week_res = $db->Execute($check_week_sql);
	// if ($check_week_res->RecordCount() > 0) {
	// 	return true;
	// } else {
	// 	#check if data exists on S_STUDENT_SCHEDULE
	// 	$check_week_sql_s = "SELECT SCHEDULE_DATE FROM S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN (" . implode(',', $PK_STUDENT_ENROLLMENTS) . ") AND  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE IN (" . implode(',', $PK_STUDENT_COURSES) . ") AND  SCHEDULE_DATE BETWEEN '$WEEK_START_DATE' AND '$WEEK_END_DATE' LIMIT 1";
	// 	$check_week_res = $db->Execute($check_week_sql_s);
	// 	if ($check_week_res->RecordCount() > 0) {
	// 		return true;
	// 	}

	// 	// dds($check_week_sql , $check_week_sql_s);
	// 	return false;
	// }
	return true;
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
		$header_size = '102px;';
		$dt_range_width = '256px !important;';
	} else {
		$header_size = '102px;';
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


				$ATTENDANCE_HOURS = '<td class="att_field" width="100px"></td>';
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
							/*max-width : 37px !important;*/
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
									font-size : 14px !important; 
								}

								
								thead { display: table-header-group; }
tfoot { display: table-row-group; }
tr { page-break-inside: avoid; }
thead { display: table-header-group; }
tfoot { display: table-row-group; }
tr { page-break-inside: avoid; }

.noborder {border: none !important}
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


	$header_path = create_html_file('header_attendance_roster_new_one_week_per_page.html', $header_cont, "invoice");
	$content_path = create_html_file('content_attendance_roster_new_one_week_per_page.html', $html_body_cont, "invoice");
	$footer_path = create_html_file('footer_attendance_roster_new_one_week_per_page.html', $footer_cont, "invoice");
	$file_name = "Monthly_Attendance_Analysis_" . uniqid() . ".pdf";
	$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 210 --page-height 296 --margin-top 40mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';
	global $http_path;
	$pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);
	exec($pdfdata['exec'], $output, $retval);
	header('Content-type: application/json; charset=UTF-8');
	$data_res = [];
	$data_res['path'] = 'temp/invoice/' . $file_name;
	echo json_encode($data_res);

	unlink('../school/temp/invoice/header_attendance_roster_new_one_week_per_page.html');
	unlink('../school/temp/invoice/content_attendance_roster_new_one_week_per_page.html');
	unlink('../school/temp/invoice/footer_attendance_roster_new_one_week_per_page.html');
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
