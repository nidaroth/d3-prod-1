<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/projected_funds.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST) || !empty($_GET)){

	if(!empty($_GET)) {
		$_POST['PK_COURSE_OFFERING'] 	= explode(",",$_GET['co']);
		$_POST['FORMAT'] 	 			= $_GET['FORMAT'];
	}

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
	$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
	$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$exc_att_code_arr = array();
	$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
	while (!$res_exc_att_code->EOF) {
		$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
		$res_exc_att_code->MoveNext();
	}
	
	$cond = "";
	$cond .= " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN (".implode(",", $_POST['PK_COURSE_OFFERING']).") ";
	
	$campus_name  = "";
	$campus_cond  = "";
	$campus_cond1 = "";
	$campus_id	  = "";
	if($_GET['campus'] != ''){
		$PK_CAMPUS 	  = $_GET['campus'];
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	while (!$res_campus->EOF) {
		if($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];
		
		if($campus_id != '')
			$campus_id .= ',';
		$campus_id .= $res_campus->fields['PK_CAMPUS'];
		
		$res_campus->MoveNext();
	}
	
	//$campus_cond1
	$query = "Select S_STUDENT_COURSE.PK_STUDENT_COURSE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_COURSE_OFFERING.PK_COURSE_OFFERING, S_COURSE_OFFERING.PK_COURSE_OFFERING, S_COURSE_OFFERING.PK_COURSE, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS BEGIN_DATE_1, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,' ', S_EMPLOYEE_MASTER.FIRST_NAME) as INSTRUCTOR_NAME, CONCAT(S_STUDENT_MASTER.LAST_NAME,' ', S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) as STUDENT_NAME, NUMERIC_GRADE, GRADE, CALCULATE_GPA, SESSION_NO, SESSION, CAMPUS_CODE       
	FROM
	S_STUDENT_MASTER, S_STUDENT_ENROLLMENT 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS 
	, S_STUDENT_COURSE 
	LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
	, S_COURSE_OFFERING 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR
	, S_COURSE, S_TERM_MASTER  
	WHERE 
	S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND 
	S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
	S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond  $campus_cond1 
	GROUP BY S_STUDENT_COURSE.PK_STUDENT_COURSE ORDER BY BEGIN_DATE DESC, TRANSCRIPT_CODE ASC, CONCAT(S_STUDENT_MASTER.LAST_NAME,' ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC "; 
	//echo $query;exit;
	//Ticket # 1636
	
	if($_POST['FORMAT'] == 1){
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

			
		class MYPDF extends TCPDF {
			public function Header() {
				global $db, $campus_name;
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 15);
				$this->SetY(6);
				$this->SetX(85);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(100, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 17);
				$this->SetY(8);
				$this->SetTextColor(000, 000, 000);
				$this->SetX(212);
				$this->Cell(55, 8, "Attendance Analysis By Term", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(180, 13, 290, 13, $style);
				
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(14);
				$this->SetX(50);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(239, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);

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
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();

		$total 	= 0;
		$txt 	= '';

		$sub_total = 0;
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" ><br /><br /><br /><b>Term</b></td>
							<td width="13%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" ><br /><br /><br /><b>Course</b></td>
							<td width="13%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" ><br /><br /><br /><b>Instructor</b></td>
							<td width="13%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" ><br /><br /><br /><b>Student</b></td>
							<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Hours Attended</b></td>
							<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Hours Missed</b></td>
							<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Hours Scheduled</b></td>
							<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Absent Count</b></td>
							<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" ><b>Absent Hours Missed</b></td>
							<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Make Up Hours</b></td>
							<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Attendance Percentage</b></td>
							<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Numeric Grade</b></td>
							<td width="8%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;" align="right" ><br /><br /><b>Final Course Grade</b></td>
						</tr>
					</thead>';
		$res_co = $db->Execute($query);
		while (!$res_co->EOF) { 
			
			$PK_COURSE_OFFERING = $res_co->fields['PK_COURSE_OFFERING'];
			$PK_STUDENT_COURSE	= $res_co->fields['PK_STUDENT_COURSE'];
			$PK_STUDENT_MASTER	= $res_co->fields['PK_STUDENT_MASTER'];
			
			$SCHEDULED_HOUR 	 = 0;
			$COMP_SCHEDULED_HOUR = 0;
			$res_sch = $db->Execute("SELECT S_STUDENT_SCHEDULE.HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, S_STUDENT_ATTENDANCE.COMPLETED, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			while (!$res_sch->EOF) { 
				$exc_att_flag = 0;
				foreach($exc_att_code_arr as $exc_att_code) {
					if($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
						$exc_att_flag = 1;
						break;
					}
				}
				if($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0){
					$SCHEDULED_HOUR += $res_sch->fields['HOURS'];
				
					if($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2) {
						$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];	
						$tot_com_sch		 += $res_sch->fields['HOURS'];	
					}
				}	
				$res_sch->MoveNext();
			}
	
			$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");
			
			$res_abs = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as ABSENT, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS ABSENT_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE IN ($absent_att_code)  AND COMPLETED = 1 ");
			
			$res_makeup = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS MAKEUP, COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) AS MAKEUP_OLD, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS - ATTENDANCE_HOURS),0) AS TARDY_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 11 AND COMPLETED = 1 ");
	
			$missed = $COMP_SCHEDULED_HOUR - $res_attended->fields['ATTENDED_HOUR'];
			if($missed < 0)
				$missed = 0;
			
			if($SCHEDULED_HOUR > 0) {
				$attended_percentage = $res_attended->fields['ATTENDED_HOUR'] / $SCHEDULED_HOUR * 100;
			} else 
				$attended_percentage = 0;
			
			$NUMERIC_GRADE = '';
			if(trim($res_co->fields['CALCULATE_GPA']) == 1) {
				$NUMERIC_GRADE 	= $res_co->fields['NUMERIC_GRADE'];
			}
					
			$txt 	.= '<tr>
						<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" >'.$res_co->fields['BEGIN_DATE_1'].'</td>
						<td width="13%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" >'.$res_co->fields['TRANSCRIPT_CODE'].' ('.substr($res_co->fields['SESSION'],0,1).' - '. $res_co->fields['SESSION_NO'].')</td>
						<td width="13%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" >'.$res_co->fields['INSTRUCTOR_NAME'].'</td>
						<td width="13%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" >'.$res_co->fields['STUDENT_NAME'].'</td>
						<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" >'.number_format_value_checker($res_attended->fields['ATTENDED_HOUR'],2).'</td>
						<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" >'.number_format_value_checker(($missed),2).'</td>
						
						<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" >'.number_format_value_checker($SCHEDULED_HOUR,2).'</td>
						<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" >'.$res_abs->fields['ABSENT'].'</td>
						<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" >'.number_format_value_checker($res_abs->fields['ABSENT_HOUR'],2).'</td>
						<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" >'.$res_makeup->fields['MAKEUP'].'</td>
						<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" >'.number_format_value_checker($attended_percentage,2).' %</td>
						<td width="6%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" align="right" >'.$NUMERIC_GRADE.'</td>
						<td width="8%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;" align="right" >'.$res_co->fields['GRADE'].'</td>
					</tr>';
			
			$res_co->MoveNext();
		}
		$txt 	.= '</table>';
		
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Attendance_Analysis_By_Term_'.uniqid().'.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
		/////////////////////////////////////////////////////////////////
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
		$file_name 		= 'Attendance Analysis By Term.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$line = 1;
		$index 	= -1;
		$heading[] = 'Term';
		$width[]   = 15;
		$heading[] = 'Course';
		$width[]   = 30;
		$heading[] = 'Instructor';
		$width[]   = 30;
		$heading[] = 'Student';
		$width[]   = 30;
		$heading[] = 'Campus';
		$width[]   = 30;
		$heading[] = 'Hours Attended';
		$width[]   = 20;
		$heading[] = 'Hours Missed';
		$width[]   = 20;
		$heading[] = 'Hours Scheduled';
		$width[]   = 20;
		$heading[] = 'Absent Count';
		$width[]   = 20;
		$heading[] = 'Absent Hours Missed';
		$width[]   = 20;
		$heading[] = 'Make Up Hours';
		$width[]   = 20;
		$heading[] = 'Attendance Percentage';
		$width[]   = 20;
		$heading[] = 'Numeric Grade';
		$width[]   = 20;
		$heading[] = 'Final Course Grade';
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
			$PK_COURSE_OFFERING = $res_co->fields['PK_COURSE_OFFERING'];
			$PK_STUDENT_COURSE	= $res_co->fields['PK_STUDENT_COURSE'];
			$PK_STUDENT_MASTER	= $res_co->fields['PK_STUDENT_MASTER'];
			
			$SCHEDULED_HOUR 	 = 0;
			$COMP_SCHEDULED_HOUR = 0;
			$res_sch = $db->Execute("SELECT S_STUDENT_SCHEDULE.HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, S_STUDENT_ATTENDANCE.COMPLETED, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			while (!$res_sch->EOF) { 
				$exc_att_flag = 0;
				foreach($exc_att_code_arr as $exc_att_code) {
					if($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
						$exc_att_flag = 1;
						break;
					}
				}
				if($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0){
					$SCHEDULED_HOUR += $res_sch->fields['HOURS'];
				
					if($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2) {
						$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];	
						$tot_com_sch		 += $res_sch->fields['HOURS'];	
					}
				}	
				$res_sch->MoveNext();
			}
	
			$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");
			
			$res_abs = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as ABSENT, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS ABSENT_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE IN ($absent_att_code)  AND COMPLETED = 1 ");
			
			$res_makeup = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS MAKEUP, COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) AS MAKEUP_OLD, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS - ATTENDANCE_HOURS),0) AS TARDY_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 11 AND COMPLETED = 1 ");
	
			$missed = $COMP_SCHEDULED_HOUR - $res_attended->fields['ATTENDED_HOUR'];
			if($missed < 0)
				$missed = 0;
			
			if($SCHEDULED_HOUR > 0) {
				$attended_percentage = $res_attended->fields['ATTENDED_HOUR'] / $SCHEDULED_HOUR * 100;
			} else 
				$attended_percentage = 0;
			
			$NUMERIC_GRADE = '';
			if(trim($res_co->fields['CALCULATE_GPA']) == 1) {
				$NUMERIC_GRADE 	= $res_co->fields['NUMERIC_GRADE'];
			}

			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['BEGIN_DATE_1']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['TRANSCRIPT_CODE'].' ('.substr($res_co->fields['SESSION'],0,1).' - '. $res_co->fields['SESSION_NO'].')');
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['INSTRUCTOR_NAME']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['STUDENT_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['CAMPUS_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_attended->fields['ATTENDED_HOUR'],2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker(($missed),2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($SCHEDULED_HOUR,2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_abs->fields['ABSENT']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_abs->fields['ABSENT_HOUR'],2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_makeup->fields['MAKEUP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($attended_percentage,2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($NUMERIC_GRADE);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['GRADE']);
			
			$res_co->MoveNext();
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
	<title><?=MNU_ATTENDANCE_INCOMPLETE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
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
                        <h4 class="text-themecolor">
							<?=MNU_ATTENDANCE_ANALYSIS_BY_TERM?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-2">
											<select id="PK_TERM_MASTER_4" name="PK_TERM_MASTER_4" class="form-control required-entry" onchange="get_course_offering(this.value)" >
												<option value="" selected><?=FIRST_TERM?></option>
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2" id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
										
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
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
	
	function get_course_offering(val){
			jQuery(document).ready(function($) { 
				var data  = 'PK_TERM_MASTER='+document.getElementById('PK_TERM_MASTER_4').value+'&dont_show_term=1';
				var url	  = "ajax_get_course_offering_from_term";
				
				var value = $.ajax({
					url: url,	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING').className = 'required-entry';
						document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
						document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
						$("#PK_COURSE_OFFERING option[value='']").remove();
						
						$('#PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
							nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
						});
						
						var dd = document.getElementsByClassName('multiselect-native-select');
						for(var i = 0 ; i < dd.length ; i++){
							dd[i].style.width = '100%' ;
						}
					}		
				}).responseText;
			});
		}
	</script>

</body>

</html>