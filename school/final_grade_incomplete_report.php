<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");
ini_set('memory_limit', '-1');
ini_set("pcre.backtrack_limit", "50000000");
set_time_limit(0);
if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}

if(!empty($_GET)){

	if($_GET['format'] != '')
		$_POST['FORMAT'] = $_GET['format'];
	
	$campus_name 	= "";
	$campus_cond 	= "";
	$campus_cond1 	= "";
	$campus_id	 	= "";
	if($_GET['campus'] != ''){
		$PK_CAMPUS 	 = $_GET['campus'];
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
	
	$terms = "";
	$res_term = $db->Execute("select IF(BEGIN_DATE != '0000-00-00',DATE_FORMAT(BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE from S_TERM_MASTER WHERE PK_TERM_MASTER IN ($_GET[term]) ORDER BY BEGIN_DATE ASC  ");
	while (!$res_term->EOF) {
		if($terms != '')
			$terms .= ', ';
		$terms .= $res_term->fields['TERM_BEGIN_DATE'];
	
		$res_term->MoveNext();
	}
	
	$query = "SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING,COURSE_CODE, LMS_CODE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d'),'') AS TERM_BEGIN_DATE, CAMPUS_CODE ,CONCAT(EMP_INSTRUCTOR.LAST_NAME,', ',EMP_INSTRUCTOR.FIRST_NAME) AS INSTRUCTOR_NAME,SESSION,SESSION_NO, ROOM_NO, S_COURSE_OFFERING.ROOM_SIZE, S_COURSE_OFFERING.CLASS_SIZE, COURSE_OFFERING_STATUS, S_COURSE_OFFERING.INSTRUCTOR, S_COURSE_OFFERING.PK_CAMPUS, TRANSCRIPT_CODE, COURSE_DESCRIPTION 
	FROM 
	S_COURSE_OFFERING 
	LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
	LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM  
	LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS 
	LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR 
	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($_GET[co_id]) 
	ORDER BY CAMPUS_CODE ASC, BEGIN_DATE ASC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ";
	
	//echo $query;exit;
		
	if($_POST['FORMAT'] == 1){
		require_once '../global/mpdf/vendor/autoload.php';
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
		
		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
			
		$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="30%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="50%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Final Grade Incomplete</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
									</tr>
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
					
		$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
						</tr>
					</table>';
		
		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 7,
			'margin_right' => 5,
			'margin_top' => 35,
			'margin_bottom' => 15,
			'margin_header' => 3,
			'margin_footer' => 10,
			'default_font_size' => 9,
			'format' => [210, 296],
			'orientation' => 'L'
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
		$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>Campus</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<b><i>Term</i></b>
							</td>
							<td width="15%" style="border-bottom:1px solid #000;">
								<b><i>Course Code</i></b>
							</td>
							<td width="22%" style="border-bottom:1px solid #000;">
								<b><i>Course Description</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<b><i>Session</i></b>
							</td>
							<td width="13%" style="border-bottom:1px solid #000;">
								<b><i>Instructor</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>Course Offering Status</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<b><i>Final Grade Incomplete</i></b>
							</td>
							<td width="6%" style="border-bottom:1px solid #000;">
								<b><i>Total Students</i></b>
							</td>
						</tr>
					</thead>';
		$res = $db->Execute($query);			
		while (!$res->EOF) { 
			$PK_COURSE_OFFERING = $res->fields['PK_COURSE_OFFERING'];
			$res_co_count = $db->Execute("SELECT COUNT(PK_STUDENT_COURSE) AS NO_STUD FROM S_STUDENT_COURSE, S_GRADE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE FINAL_GRADE = PK_GRADE AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND UNITS_IN_PROGRESS = 1 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ");
			
			$res_co_count_1 = $db->Execute("SELECT COUNT(PK_STUDENT_COURSE) AS NO_STUD FROM S_STUDENT_COURSE, S_GRADE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE FINAL_GRADE = PK_GRADE AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ");
			
			$txt .= '<tr>
						<td >'.$res->fields['CAMPUS_CODE'].'</td>
						<td >'.$res->fields['TERM_BEGIN_DATE'].'</td>
						<td >'.$res->fields['COURSE_CODE'].'</td>
						<td >'.$res->fields['COURSE_DESCRIPTION'].'</td>
						<td >'.substr($res->fields['SESSION'],0,1).' - '.$res->fields['SESSION_NO'].'</td>
						<td >'.$res->fields['INSTRUCTOR_NAME'].'</td>
						<td >'.$res->fields['COURSE_OFFERING_STATUS'].'</td>
						<td >'.$res_co_count->fields['NO_STUD'].'</td>
						<td >'.$res_co_count_1->fields['NO_STUD'].'</td>
					</tr>';
			$res->MoveNext();
		}
		$txt .= '</table>';
	
		$mpdf->WriteHTML($txt);
		if($_GET['download_via_js'] == 'yes'){
			$outputFileName = 'temp/Final_Grade_Incomplete.pdf';
			$outputFileName = str_replace(
				pathinfo($outputFileName, PATHINFO_FILENAME),
				pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
				$outputFileName
			);
			$filename = $mpdf->Output($outputFileName, 'F');
			header('Content-type: application/json; charset=UTF-8');
			$data_res = [];
			$data_res['path'] = $outputFileName;
			$data_res['filename'] = str_replace('temp/','',$outputFileName);
			echo json_encode($data_res);  
			exit;
		}
		$mpdf->Output('Final Grade Incomplete.pdf', 'D');
		
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
		$file_name 		= 'Final Grade Incomplete.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$line = 1;
		$index 	= -1;
		$heading[] = 'Campus';
		$width[]   = 30;
		$heading[] = 'Term';
		$width[]   = 30;
		$heading[] = 'Course Code';
		$width[]   = 30;
		$heading[] = 'Course Description';
		$width[]   = 30;
		$heading[] = 'Session';
		$width[]   = 30;
		$heading[] = 'Session Number';
		$width[]   = 30;
		$heading[] = 'Instructor';
		$width[]   = 30;
		$heading[] = 'Secondary Instructor';
		$width[]   = 20;
		$heading[] = 'Course Offering Status';
		$width[]   = 20;
		$heading[] = 'Room';
		$width[]   = 20;
		$heading[] = 'Final Grade Incomplete';
		$width[]   = 20;
		$heading[] = 'Total Students';
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

		$res_co = $db->Execute($query);
		while (!$res_co->EOF) { 
			
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['CAMPUS_CODE']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['TERM_BEGIN_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['COURSE_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['COURSE_DESCRIPTION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['SESSION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['SESSION_NO']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['INSTRUCTOR_NAME']);

			$PK_COURSE_OFFERING = $res_co->fields['PK_COURSE_OFFERING'];
			$ASSISTANT_NAME = '';
			$res_ass = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) AS ASSISTANT_NAME FROM S_COURSE_OFFERING_ASSISTANT, S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING_ASSISTANT.ASSISTANT AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING_ASSISTANT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			while (!$res_ass->EOF) { 
				if($ASSISTANT_NAME != '')
					$ASSISTANT_NAME .= ', ';
				$ASSISTANT_NAME .= $res_ass->fields['ASSISTANT_NAME'];
				
				$res_ass->MoveNext();
			}
		
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ASSISTANT_NAME);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['COURSE_OFFERING_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['ROOM_NO']);
			
			$res_co_count = $db->Execute("SELECT COUNT(PK_STUDENT_COURSE) AS NO_STUD FROM S_STUDENT_COURSE, S_GRADE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE FINAL_GRADE = PK_GRADE AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND UNITS_IN_PROGRESS = 1 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ");
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co_count->fields['NO_STUD']);
			
			$res_co_count = $db->Execute("SELECT COUNT(PK_STUDENT_COURSE) AS NO_STUD FROM S_STUDENT_COURSE, S_GRADE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE FINAL_GRADE = PK_GRADE AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ");
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co_count->fields['NO_STUD']);
				
			$res_co->MoveNext();
		}
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		if($_GET['download_via_js'] == 'yes'){
			header('Content-type: application/json; charset=UTF-8');
			$data_res = [];
			$data_res['path'] = $outputFileName;
			$data_res['filename'] = str_replace('temp/','',$outputFileName);
			echo json_encode($data_res);  
			exit;
		}
		header("location:".$outputFileName);
	}
}