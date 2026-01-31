<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");
// ENABLE_DEBUGGING(TRUE);
ini_set('memory_limit', '-1');
ini_set("pcre.backtrack_limit", "50000000");
set_time_limit(0);

if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}
$cond =''; //DIAM-1753
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
	$res_term = $db->Execute("select IF(BEGIN_DATE != '0000-00-00',DATE_FORMAT(BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE from S_TERM_MASTER WHERE PK_TERM_MASTER IN ($_GET[term]) ORDER BY BEGIN_DATE ASC ");
	while (!$res_term->EOF) {
		if($terms != '')
			$terms .= ', ';
		$terms .= $res_term->fields['TERM_BEGIN_DATE'];
	
		$res_term->MoveNext();
	}
	
	// Ticket # DIAM-587
	if(count(explode(',',$terms)) > 8){
		$terms = "Multiple Terms Selected";
		}
	// Ticket # DIAM-587

	$grad = "";
	$res_grad = $db->Execute("select GRADE FROM S_GRADE WHERE PK_GRADE IN ($_GET[grade]) ORDER BY GRADE ASC ");
	while (!$res_grad->EOF) {
		if($grad != '')
			$grad .= ', ';
		$grad .= $res_grad->fields['GRADE'];
	
		$res_grad->MoveNext();
	}
	//DIAM-1753
	if(!empty($_GET['co_id'])){
	 $cond = "AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN ($_GET[co_id])";
	}
	//DIAM-1753
	$query = "select S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_MASTER.LAST_NAME, S_STUDENT_MASTER.FIRST_NAME, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_GROUP, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d' )) AS BEGIN_DATE_1, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STUDENT_ID, CAMPUS_CODE, IF(S_TERM_MASTER_1.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER_1.BEGIN_DATE, '%Y-%m-%d' )) AS COURSE_TERM, COURSE_CODE, COURSE_DESCRIPTION, CONCAT(EMP_INSTRUCTOR.LAST_NAME,', ',EMP_INSTRUCTOR.FIRST_NAME) AS INSTRUCTOR_NAME, COURSE_OFFERING_STUDENT_STATUS, GRADE, SESSION, SESSION_NO, ROOM_NO, COURSE_OFFERING_STATUS, S_STUDENT_COURSE.PK_COURSE_OFFERING     
	FROM 
	S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	, S_STUDENT_COURSE 
	LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
	LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE  
	, S_COURSE_OFFERING 
	LEFT JOIN S_TERM_MASTER as S_TERM_MASTER_1 ON S_TERM_MASTER_1.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION  
	LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR 
	LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM  
	LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	, M_STUDENT_STATUS 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
	M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) $cond AND 
	S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
	S_STUDENT_COURSE.FINAL_GRADE IN ($_GET[grade]) 
	GROUP BY S_STUDENT_COURSE.PK_STUDENT_COURSE 
	ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC, S_TERM_MASTER.BEGIN_DATE ASC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ";
	
	// echo $query;exit;
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
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Final Grade Analysis By Student</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Term(s): '.$terms.'</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Grade(s): '.$grad.'</td>
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
			'default_font_size' => 9
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
		$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="15%" style="border-bottom:1px solid #000;">
								<b><i>Student</i></b>
							</td>
							<td width="12%" style="border-bottom:1px solid #000;">
								<b><i>Campus</i></b>
							</td>
							<td width="12%" style="border-bottom:1px solid #000;">
								<b><i>Status</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>Term</i></b>
							</td>
							<td width="15%" style="border-bottom:1px solid #000;">
								<b><i>Course</i></b>
							</td>
							<td width="15%" style="border-bottom:1px solid #000;">
								<b><i>Instructor</i></b>
							</td>
							<td width="14%" style="border-bottom:1px solid #000;">
								<b><i>Course Offering Student Status</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;">
								<b><i>Final Grade</i></b>
							</td>
						</tr>
					</thead>';
		$res = $db->Execute($query);			
		while (!$res->EOF) { 
			
			$txt .= '<tr>
						<td >'.$res->fields['STU_NAME'].'</td>
						<td >'.$res->fields['CAMPUS_CODE'].'</td>
						<td >'.$res->fields['STUDENT_STATUS'].'</td>
						<td >'.$res->fields['COURSE_TERM'].'</td>
						<td >'.$res->fields['COURSE_CODE'].' ('.substr($res->fields['SESSION'],0,1).'-'.$res->fields['SESSION_NO'].')</td>
						<td >'.$res->fields['INSTRUCTOR_NAME'].'</td>
						<td >'.$res->fields['COURSE_OFFERING_STUDENT_STATUS'].'</td>
						<td >'.$res->fields['GRADE'].'</td>
					</tr>';
			$res->MoveNext();
		}
		$txt .= '</table>';
		$mpdf->WriteHTML($txt);
		if($_GET['download_via_js'] == 'yes'){
			$outputFileName = 'temp/Final_Grade_Analysis_By_Student.pdf';
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
		}else{
			$mpdf->Output('Final Grade Analysis By Student.pdf', 'D');
		}
		
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
		$file_name 		= 'Final Grade Analysis By Student.xlsx';
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
		$heading[] = 'Last Name';
		$width[]   = 30;
		$heading[] = 'First Name';
		$width[]   = 30;
		$heading[] = 'Campus';
		$width[]   = 30;
		$heading[] = 'First Term';
		$width[]   = 30;
		$heading[] = 'Program';
		$width[]   = 30;
		$heading[] = 'Status';
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
		$width[]   = 30;
		$heading[] = 'Course Offering Status';
		$width[]   = 30;
		$heading[] = 'Room';
		$width[]   = 30;
		$heading[] = 'Course Offering Student Status';
		$width[]   = 30;
		$heading[] = 'Final Grade';
		$width[]   = 30;
		
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
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['FIRST_NAME']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['LAST_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['CAMPUS_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['BEGIN_DATE_1']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['COURSE_TERM']);
			
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
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['COURSE_OFFERING_STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['GRADE']);
				
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
