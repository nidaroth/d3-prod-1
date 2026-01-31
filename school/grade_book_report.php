<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if (check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0 && $_SESSION['PK_ROLES'] != 3) { //Ticket # 1472
    header("location:../index");
    exit;
}

function dfrmt($datestr , $datefrmt = "m/d/Y"){
	return date($datefrmt, strtotime($datestr));
}
function tfrmt($timestr ,$timefrmt = 'h:i A'){
	return date( $timefrmt, strtotime(' 01/01/2024 '.$timestr));
}

if(!empty($_POST) || $_GET['co_id']){ 
	//echo "<pre>";print_r($_POST);exit;
	$cond = "";
	$campus_name    = "";
	$CO_PK_CAMPUS = '';

	$PK_COURSE_OFFERING =  implode(",",$_POST['GS_PK_COURSE_OFFERING']);
	$PK_CAMPUS = implode(",",$_POST['GS_PK_CAMPUS']);	

	if(!empty($_GET['co_id']) && $_GET['co_id'] > 0) {
		$PK_COURSE_OFFERING = $_GET['co_id']; // From Grade book entry Tab

		$res_co_campus = $db->Execute("select PK_CAMPUS from S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'");
		$PK_CAMPUS = $res_co_campus->fields['PK_CAMPUS']; 
	}

	// if(!empty($PK_COURSE_OFFERING))
	// 	$cond .= " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($PK_COURSE_OFFERING) ";
	
	
	if(!empty($PK_CAMPUS)){
	
		$cond .= " AND S_COURSE_OFFERING.PK_CAMPUS IN ($PK_CAMPUS) ";
	
		$res_campus = $db->Execute("select CAMPUS_CODE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND PK_CAMPUS IN ($PK_CAMPUS) order by CAMPUS_CODE ASC");

		while (!$res_campus->EOF) {
			if ($campus_name != '')
				$campus_name .= ', ';
				$campus_name .= $res_campus->fields['CAMPUS_CODE'];
				$res_campus->MoveNext();
		}
		
	}


	
	$PK_COURSE_OFFERING_data = explode(",", $PK_COURSE_OFFERING);
	foreach ($PK_COURSE_OFFERING_data as $key => $value) {
		$PK_COURSE_OFFERING_ARR[] 	=  $value;
	}
	
	// $query = "SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING, COURSE_CODE, COURSE_DESCRIPTION   
	// FROM
	// S_COURSE, S_COURSE_OFFERING     
	// WHERE 
	// S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND   
	// S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE $cond
	// GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER By COURSE_CODE ";
	
	// $res_course = $db->Execute($query);
	// while (!$res_course->EOF) {
	// 	$PK_COURSE_OFFERING_ARR[] 	= $res_course->fields['PK_COURSE_OFFERING'];
		
	// 	$res_course->MoveNext();
	// }

	// Main loop sql result
	$stud_query = "select S_STUDENT_COURSE.PK_STUDENT_COURSE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.LAST_NAME, S_STUDENT_MASTER.FIRST_NAME, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STUD_NAME, STUDENT_ID, STUDENT_STATUS, COURSE_CODE, COURSE_DESCRIPTION, SESSION, SESSION_NO, CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,', ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME, COURSE_OFFERING_STATUS, CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO, S_STUDENT_CONTACT.HOME_PHONE, S_STUDENT_CONTACT.CELL_PHONE, S_STUDENT_CONTACT.EMAIL, M_CAMPUS_PROGRAM.CODE, COURSE_OFFERING_STUDENT_STATUS, CURRENT_TOTAL_OBTAINED, CURRENT_MAX_TOTAL, FINAL_TOTAL_OBTAINED, FINAL_MAX_TOTAL, GRADE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%Y-%m-%d' )) AS  END_DATE_1, TERM_DESCRIPTION, CAMPUS_CODE    
    from 
    S_STUDENT_MASTER 
    LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1'  
    LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
    , S_STUDENT_ENROLLMENT 
    LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
    LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
    LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
    LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
    , S_STUDENT_COURSE
    LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
    LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = FINAL_GRADE
    , S_COURSE_OFFERING 
    LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
    LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM 
    LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
    LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
    LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
    LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR 
    WHERE 
    S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
    S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
    S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND 
    S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING  $cond ";
	
	if($_POST['FORMAT'] == 1 || $_GET['FORMAT']==1) {
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

		
		$txt = ''; 
		
		foreach($PK_COURSE_OFFERING_ARR as $PK_COURSE_OFFERING){
			
			//Course Info Row
			$res_cs = $db->Execute("select CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO,FA_UNITS,  UNITS, CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,' ',S_EMPLOYEE_MASTER_INST.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME,ATTENDANCE_TYPE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 , IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.END_DATE, '%m/%d/%Y' )) AS  END_DATE_1,SESSION,SESSION_NO, COURSE_OFFERING_STATUS, COURSE_CODE, COURSE_DESCRIPTION from S_COURSE_OFFERING LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS LEFT JOIN M_ATTENDANCE_TYPE ON M_ATTENDANCE_TYPE.PK_ATTENDANCE_TYPE = S_COURSE_OFFERING.PK_ATTENDANCE_TYPE LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM, S_COURSE WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' $cond AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE");
			
			//$cond1 = " AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' "; //Ticket # 1195

			$grouped_schedule = 
			"SELECT
				MIN(SCHEDULE_DATE) AS G_START_DATE, 
				MAX(SCHEDULE_DATE) AS G_END_DATE,
				SUM(HOURS) AS G_TOTAL_HOURS,
				START_TIME,
				END_TIME,
				HOURS,
				COUNT(PK_COURSE_OFFERING_SCHEDULE_DETAIL) AS G_TOTAL_MEETINGS
			FROM
				`S_COURSE_OFFERING_SCHEDULE_DETAIL`
			WHERE
			PK_ACCOUNT = $_SESSION[PK_ACCOUNT]
			AND PK_COURSE_OFFERING = $PK_COURSE_OFFERING
				
			GROUP BY
				PK_COURSE_OFFERING_SCHEDULE,
				START_TIME,
				END_TIME";
			
			$res_grouped_schedule = $db->Execute($grouped_schedule);
			//Course Info Row

			//Table Header

			$PK_COURSE_OFFERING_GRADE_ARR = array();
			$WEIGHTED_VALUES_ARR = array();
			$result1 = $db->Execute("SELECT S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE,S_COURSE_OFFERING_GRADE.CODE,S_COURSE_OFFERING_GRADE.POINTS,S_COURSE_OFFERING_GRADE.WEIGHTED_POINTS,S_COURSE.COURSE_CODE,M_SESSION.SESSION, S_COURSE_OFFERING.SESSION_NO
				FROM S_COURSE_OFFERING_GRADE
				LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING
				LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
				LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
				WHERE S_COURSE_OFFERING_GRADE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING IN ($PK_COURSE_OFFERING) $cond
				ORDER BY S_COURSE.COURSE_CODE,M_SESSION.SESSION, S_COURSE_OFFERING.SESSION_NO, S_COURSE_OFFERING_GRADE.GRADE_ORDER, S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE ASC ");		

			//Table Header

			$secondary_line_of_scheduled_hours = '<td style="width : 35%; text-align : left !important;"><b>Course : </b> '.dfrmt($res_grouped_schedule->fields['G_START_DATE']).' to '. dfrmt($res_grouped_schedule->fields['G_END_DATE']).' - '.$res_grouped_schedule->fields['G_TOTAL_HOURS'].' hours - '.$res_grouped_schedule->fields['G_TOTAL_MEETINGS'].' meetings
			<br>
			<b>Class : </b> '.tfrmt($res_grouped_schedule->fields['START_TIME']).' to '. tfrmt($res_grouped_schedule->fields['END_TIME']).' - '.$res_grouped_schedule->fields['HOURS'].' hours </td>';

			$END_DATE_1 = ($res_cs->fields['END_DATE_1'])?" - ".$res_cs->fields['END_DATE_1']:'';

			$txt .= '<div style="page-break-before: always;">';
			$txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%" style="border:1px solid #000000;">
						<tr>
							<td align="left" width="18%" ><b>Course: '.$res_cs->fields['COURSE_CODE'].' ('.$res_cs->fields['SESSION'].'-'.$res_cs->fields['SESSION_NO'].')</b><br />'.$res_cs->fields['COURSE_DESCRIPTION'].'</td>
							<td align="center" width="12%" ><b>Term Dates</b><br />'.$res_cs->fields['BEGIN_DATE_1'].$END_DATE_1 .'</td>
							<td align="center" width="15%" ><b>Instructor</b><br />'.$res_cs->fields['INSTRUCTOR_NAME'].'</td>
							<td align="center" width="10%"><b>Room</b><br />'.$res_cs->fields['ROOM_NO'].'</td>
							<td align="center" width="10%"><b>Attendance</b><br />'.$res_cs->fields['ATTENDANCE_TYPE'].'</td>
							'.$secondary_line_of_scheduled_hours.'
							
						</tr>
					</table>';

			$txt .= '<br />';
			$txt .='<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td style="border:1px solid #e9e3e3; width:20%;" ><br /><b>Student</b></td>
								<td style="border:1px solid #e9e3e3; width:5%; text-align: center" ><br /><b>Grade</b></td>							
								<td style="border:1px solid #e9e3e3;width:10%;text-align: right;line-height:1.8;
								" ><b>Weighted<br />Total</b></td>';
								$res_cnt = $result1->RecordCount();
								$dy_width = 65/$res_cnt;
								while (!$result1->EOF) {					
									$txt .='<td  style="border:1px solid #e9e3e3; text-align: right; width:'.$dy_width.'%" ><b>'.$result1->fields['CODE'].'</b><br />'.number_format_value_checker($result1->fields['POINTS'],2).'</td>';							
									$PK_COURSE_OFFERING_GRADE_ARR[] = $result1->fields['PK_COURSE_OFFERING_GRADE'];	
									$WEIGHTED_VALUES_ARR[]= $result1->fields['WEIGHTED_POINTS'];							
									$result1->MoveNext();							
								}
					
							
							$txt .='</tr>';

			$txt .='</thead>';
						

			$res_stud = $db->Execute($stud_query." AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN ($PK_COURSE_OFFERING)" . " GROUP BY S_STUDENT_COURSE.PK_STUDENT_COURSE ORDER BY CAMPUS_CODE ASC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ");

			while (!$res_stud->EOF) {

				$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];

				$txt.= '<tr>
								<td  style="border:1px solid #e9e3e3;padding:7px;" >'.$res_stud->fields['STUD_NAME'].'</td>
								<td  style="border:1px solid #e9e3e3; text-align: center;" >'.$res_stud->fields['GRADE'].'</td>
								<td  style="border:1px solid #e9e3e3;text-align: right;" >'.$res_stud->fields['FINAL_TOTAL_OBTAINED'].'</td>';
								
							if(!empty($PK_COURSE_OFFERING_GRADE_ARR) && count($PK_COURSE_OFFERING_GRADE_ARR)>0){
								// DIAM-2389
								foreach ($PK_COURSE_OFFERING_GRADE_ARR as $PK_COURSE_OFFERING_GRADE) {
									$res_stu_grade = $db->Execute("SELECT POINTS as POINTS FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
									$Fail_Point = '';
									if ($res_stu_grade->fields['POINTS'] != "") 
									{
										$Fail_Point = $res_stu_grade->fields['POINTS'];
									}
									
									$txt.= '<td  style="border:1px solid #e9e3e3;padding:7px; text-align: right;" >'.$Fail_Point.'</td>';
							
								}
								// End DIAM-2389
							}

						$txt.= '</tr>';

				
				$res_stud->MoveNext();
			} // while end	

			$colspan = count($PK_COURSE_OFFERING_GRADE_ARR) + 3;
			 $txt.= '<tr>
			 <td style="border:0px solid #e9e3e3; width:25%;" colspan="2">
			 <span style="float:left;"><b>Weighted Values: </b></span></td>										
			 <td style="border:0px solid #e9e3e3; width:10%; text-align: right;" >'.array_sum($WEIGHTED_VALUES_ARR).'</td>';
			 
			 if(!empty($WEIGHTED_VALUES_ARR) && count($WEIGHTED_VALUES_ARR)>0){

				 foreach ($WEIGHTED_VALUES_ARR as $WEIGHTED_VALUES) {
					 $txt.= '<td  style="border:0px solid #e9e3e3; text-align: right;  width:'.$dy_width.'%">'.number_format_value_checker($WEIGHTED_VALUES,2).'</td>';
				 }
			 }

			$txt.= '</tr>';
			$txt.= '</table>';
			$txt.= '</div>';
			
				
		} //For loop end

		//echo $txt; exit;
	
		$file_name = 'grade_book_'.uniqid().'.pdf'; //Ticket # 669

		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
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
		
		
			$header_text='';
			if(!empty($campus_name)){
				$header_text = '<tr><td width="100%" align="right" style="font-size:14px;font-style: italic;font-family:helvetica;" >Campus(es): '.$campus_name.'</td></tr>';
			}

		$header = '<table width="100%" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="40%" valign="top" style="font-size:20px;" >'.$SCHOOL_NAME.'</td>
								<td width="40%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;font-style: italic;font-family:helvetica;" ><b>Grade Book</b></td>											
										</tr>'.$header_text.'										
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
		<head> <style>
		body{ font-size:15px; font-family:helvetica; }	
		table{  margin-top: 10px; }
		table tr{  padding-top: 15px !important; }
		</style>
		</head>
		<body>'.$txt.'</body></html>';
		$footer_cont= '<!DOCTYPE HTML><html><head><style>
		tbody td{ font-size:14px !important; }
		</style></head><body>'.$footer.'</body></html>';

		$header_path= create_html_file('grade_book_header.html',$header_cont,'grade_book');
		$content_path=create_html_file('grade_book_content.html',$html_body_cont,'grade_book');
		$footer_path= create_html_file('grade_book_footer.html',$footer_cont,'grade_book');
	
		sleep(2);
		$margin_top="30mm";
		// if(strlen($header)>1530){
		// $margin_top="60mm";
		// }
		exec('xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation landscape --page-size A4 --page-width 210mm  --page-height 297mm --margin-top '.$margin_top.'  --footer-spacing 8  --margin-left 5mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/grade_book/'.$file_name.' 2>&1');
				
		//echo 'temp/grade_book/'.$file_name;

		header('Content-Type: Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="' . basename($http_path.'school/temp/grade_book/'.$file_name) . '"');
		//header('Content-Length: ' . $pdfdata['filefullpath']);
		readfile('temp/grade_book/'.$file_name);

		unlink('../school/temp/grade_book/grade_book_header.html');
		unlink('../school/temp/grade_book/grade_book_content.html');
		unlink('../school/temp/grade_book/grade_book_footer.html');
		exit;	

		//Ticket # 669


		/////////////////////////////////////////////////////////////////
	} else {
		
	}
}

?>
