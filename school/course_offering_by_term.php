<? 
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/projected_funds.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

/* Ticket #1225 */
if(!empty($_POST) || $_GET['p'] == 'r'){
	if($_GET['p'] == 'r') {
		$_POST['PK_TERM_MASTER']	= $_GET['tm'];
		$_POST['FORMAT']			= $_GET['FORMAT'];
		$_POST['INCLUDE_STUDENTS']	= $_GET['INCLUDE_STUDENTS'];

	}
	
	$campus_name  = "";
	$campus_cond  = "";
	$campus_cond1 = "";
	$campus_id	  = "";
	if(!empty($_GET['campus'])){
		$PK_CAMPUS 	  = $_GET['campus'];
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_COURSE_OFFERING.PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$res_campus = $db->Execute("select PK_CAMPUS,OFFICIAL_CAMPUS_NAME from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by OFFICIAL_CAMPUS_NAME ASC");
	while (!$res_campus->EOF) {
		if($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['OFFICIAL_CAMPUS_NAME'];
		
		if($campus_id != '')
			$campus_id .= ',';
		$campus_id .= $res_campus->fields['PK_CAMPUS'];
		
		$res_campus->MoveNext();
	}
	
	$order_by_123 = "";
	if($_POST['FORMAT'] == 1)
		$order_by_123 = " ORDER BY S_TERM_MASTER.BEGIN_DATE DESC, TRANSCRIPT_CODE ASC, SESSION ASC, SESSION_NO ASC ";
	else
		$order_by_123 = " ORDER BY OFFICIAL_CAMPUS_NAME ASC, S_TERM_MASTER.BEGIN_DATE DESC, TRANSCRIPT_CODE ASC, SESSION ASC, SESSION_NO ASC ";
	
	$cond = "";
	if($_GET['co_id'] != '')
		$cond = " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($_GET[co_id]) ";

	$res_co = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING,TRANSCRIPT_CODE, SESSION_NO, SESSION, DEF_START_TIME, DEF_END_TIME, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d'),'') AS TERM_BEGIN_DATE, CONCAT(LAST_NAME,', ', FIRST_NAME) as INSTRUCTOR, IF(S_COURSE_OFFERING_SCHEDULE.START_DATE != '0000-00-00',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.START_DATE,'%m/%d/%Y'),'') AS START_DATE, COURSE_DESCRIPTION, ROOM_NO, UNITS, HOURS, FA_UNITS, OFFICIAL_CAMPUS_NAME
	from 
	S_COURSE_OFFERING 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR 
	LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN S_COURSE_OFFERING_SCHEDULE ON S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 

	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_TERM_MASTER IN ($_POST[PK_TERM_MASTER]) $cond $order_by_123 ");
	/* Ticket #1225 */
	
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
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(212);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Course Offering By Term", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(200, 13, 290, 13, $style);
		
				$str = "";
				$res_type = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_TERM_MASTER IN ($_POST[PK_TERM_MASTER]) AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY BEGIN_DATE DESC");
				while (!$res_type->EOF) {
					if($res_type->fields['BEGIN_DATE_1'] != '') {
						if($str != '')
							$str .= ', ';
							
						$str .= $res_type->fields['BEGIN_DATE_1'];
					}
					$res_type->MoveNext();
				}
				
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(13);
				$this->SetX(138);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(152, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(20);
				$this->SetX(138);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(152, 5, "Term(s): ".$str, 0, 'R', 0, 0, '', '', true);
				
				$INCLUDE_STUDENTS = 'No';
				if($_GET['INCLUDE_STUDENTS'] == 1)
					$INCLUDE_STUDENTS = 'Yes';
					
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(27);
				$this->SetX(138);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(152, 5, "Include Students: ".$INCLUDE_STUDENTS, 0, 'R', 0, 0, '', '', true);
				
				
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
		$pdf->SetMargins(7, 34, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		
		$txt   = "";
		$index = 0;
		while (!$res_co->EOF) { 
			$index++;
			$PK_COURSE_OFFERING = $res_co->fields['PK_COURSE_OFFERING'];
			
			$CLASS_METTINGS_A = array();
			$PK_CAMPUS_ROOM_A = array();
			$TIME_A 		  = array();
			$DAYS1_A 		  = array();
			
			if($_POST['INCLUDE_STUDENTS'] == 1)
				$txt = '';
				
			if($index == 1 || $_POST['INCLUDE_STUDENTS'] == 1) {
				$pdf->AddPage();
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Term</b></td>
								<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Course</b></td>
								<td width="5%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Units</b></td>
								<td width="5%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>FA Units</b></td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Hours</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Students</b></td>
								<td width="11%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Instructor</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Room</b></td>
								<td width="14%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Class Meetings</b></td>
								<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Days of Week</b></td>
								<td width="13%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Class Times</b></td>
							</tr>';
			}
			
			$res_build = $db->Execute("select SCHEDULE_DATE,PK_CAMPUS_ROOM,START_TIME,END_TIME from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY PK_CAMPUS_ROOM,START_TIME,END_TIME ORDER BY SCHEDULE_DATE ASC, START_TIME ASC"); 
			while (!$res_build->EOF) {
				//$SCHEDULE_DATE 	= $res_build->fields['SCHEDULE_DATE'];
				$PK_CAMPUS_ROOM = $res_build->fields['PK_CAMPUS_ROOM'];
				$START_TIME 	= $res_build->fields['START_TIME'];
				$END_TIME 		= $res_build->fields['END_TIME'];
				
				$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND START_TIME = '$START_TIME' AND END_TIME = '$END_TIME'  ORDER BY SCHEDULE_DATE ASC "); 
				$SCHEDULE_DATE = $res_build1->fields['SCHEDULE_DATE'];
				
				$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND START_TIME = '$START_TIME' AND END_TIME = '$END_TIME'  ORDER BY SCHEDULE_DATE DESC "); 
				$SCHEDULE_DATE1 = $res_build1->fields['SCHEDULE_DATE'];
				
				$CLASS_METTINGS_A[] = date("Y-m-d",strtotime($SCHEDULE_DATE)).' to '.date("Y-m-d",strtotime($SCHEDULE_DATE1));
				$PK_CAMPUS_ROOM_A[] = $PK_CAMPUS_ROOM;
				$TIME_A[]			= date("h:i A",strtotime($START_TIME)).' to '.date("h:i A",strtotime($END_TIME));
				
				$dates = array();
				
				$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND START_TIME = '$START_TIME' AND END_TIME = '$END_TIME' AND SCHEDULE_DATE BETWEEN '$SCHEDULE_DATE' AND  '$SCHEDULE_DATE1' ORDER BY SCHEDULE_DATE ASC "); 
				while (!$res_build1->EOF) {
					$dates[] = $res_build1->fields['SCHEDULE_DATE'];
					$res_build1->MoveNext();
				}
				
				$DAYS_A = array();
				foreach($dates as $date) {
					$N = date("N",strtotime($date));
					if($N == 1)
						$DAYS_A[$N] = 'M';
					else if($N == 2)
						$DAYS_A[$N] = 'Tu';
					else if($N == 3)
						$DAYS_A[$N] = 'W';
					else if($N == 4)
						$DAYS_A[$N] = 'Th';
					else if($N == 5)
						$DAYS_A[$N] = 'F';
					else if($N == 6)
						$DAYS_A[$N] = 'Sa';
					else if($N == 7)
						$DAYS_A[$N] = 'Su';
				}
				ksort($DAYS_A);
				$DAYS1_A[] = implode(", ",$DAYS_A);
				
				$res_build->MoveNext();
			}
			
			$res_stud = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, M_CAMPUS_PROGRAM.CODE, STUDENT_STATUS, PK_STUDENT_COURSE, STUDENT_ID, OFFICIAL_CAMPUS_NAME, COURSE_OFFERING_STUDENT_STATUS 
			FROM 
			S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_COURSE 
			LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
			, S_STUDENT_ENROLLMENT 
			LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
			LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
			LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
			LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
			WHERE 
			PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND 
			S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
			S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ");
			
			$txt .= '<tr>
						<td width="8%"  >'.$res_co->fields['TERM_BEGIN_DATE'].'</td>
						<td width="12%"  >'.$res_co->fields['TRANSCRIPT_CODE'].' ('. substr($res_co->fields['SESSION'],0,1).' - '. $res_co->fields['SESSION_NO'].')</td>
						<td width="5%" align="right" >'.$res_co->fields['UNITS'].'</td>
						<td width="5%" align="right" >'.$res_co->fields['FA_UNITS'].'</td>
						<td width="6%" align="right" >'.$res_co->fields['HOURS'].'</td>
						<td width="7%" align="right" >'.$res_stud->RecordCount().'</td>
						<td width="11%" >'.$res_co->fields['INSTRUCTOR'].'</td>';
						
						$txt .= '<td width="7%" >';
						foreach($PK_CAMPUS_ROOM_A as $key => $PK_CAMPUS_ROOM){
							$res = $db->Execute("SELECT ROOM_NO FROM M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM  = '$PK_CAMPUS_ROOM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
							$txt .= $res->fields['ROOM_NO'].'<br />';
						}
						$txt .= '</td>';
						
						$txt .= '<td width="14%" >';
								foreach($CLASS_METTINGS_A as $key => $val) {
									$txt .= $val.'<br />';
								}
						$txt .='</td>
						<td width="12%" >';
						foreach($DAYS1_A as $key => $val){
							$txt .= $val.'<br />';
						}
						$txt .= '</td>';
						
						$txt .= '<td width="13%" >';
						foreach($TIME_A as $key => $val){
							$txt .= $val.'<br />';
						}
						$txt .= '</td>
					</tr>';
					
					if($_POST['INCLUDE_STUDENTS'] == 1) {
						$txt .= '</table><br /><br />
								<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<tr>
										<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student</b></td>
										<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student ID</b></td>
										<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Campus</b></td>
										<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Program</b></td>
										<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Status</b></td>
										<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Course Offering Status</b></td>
									</tr>';
						while (!$res_stud->EOF) {
							$txt .= '<tr>
										<td width="15%" >'.$res_stud->fields['NAME'].'</td>
										<td width="15%" >'.$res_stud->fields['STUDENT_ID'].'</td>
										<td width="15%" >'.$res_stud->fields['OFFICIAL_CAMPUS_NAME'].'</td>
										<td width="20%" >'.$res_stud->fields['CODE'].'</td>
										<td width="15%" >'.$res_stud->fields['STUDENT_STATUS'].'</td>
										<td width="20%" >'.$res_stud->fields['COURSE_OFFERING_STUDENT_STATUS'].'</td>
									</tr>';
							$res_stud->MoveNext();
						}
						$txt 	.= '</table>';
						
						$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
					}
					
			$res_co->MoveNext();
		}
		
		if($_POST['INCLUDE_STUDENTS'] != 1) {
			$txt 	.= '</table>';
			//echo $txt;exit;
			$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		}
		
		$file_name = 'Course Offering By Term.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
		/////////////////////////////////////////////////////////////////
	} else if($_POST['FORMAT'] == 2) {
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
		$file_name 		= 'Course Offering By Term.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		if($_POST['INCLUDE_STUDENTS'] == 1) {
			$objPHPExcel->createSheet(1);
			$objPHPExcel->setActiveSheetIndex(1);
			$objPHPExcel->getActiveSheet()->setTitle("Students");
			
			$line_2  = 1;
			$index_2 = -1;
			$heading = array();
			$width   = array();
			$heading[] = 'Course';
			$width[]   = 20;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Course Offering Student Status';
			$width[]   = 20;
			
			$i = 0;
			foreach($heading as $title) {
				$index_2++;
				$cell_no = $cell[$index_2].$line_2;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index_2])->setWidth($width[$i]);
				
				$i++;
			}
		}
		
		$objPHPExcel->setActiveSheetIndex(0);

		$line  = 1;
		$index = -1;
		$heading = array();
		$width 	 = array();
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Term';
		$width[]   = 20;
		$heading[] = 'Course Code';
		$width[]   = 20;
		$heading[] = 'Description';
		$width[]   = 30;
		$heading[] = 'Session';
		$width[]   = 15;
		$heading[] = 'Session Number';
		$width[]   = 15;
		$heading[] = 'Units';
		$width[]   = 15;
		$heading[] = 'FA Units';
		$width[]   = 15;
		$heading[] = 'Hours';
		$width[]   = 15;
		$heading[] = 'Students';
		$width[]   = 15;
		$heading[] = 'Instructor';
		$width[]   = 20;
		$heading[] = 'Room';
		$width[]   = 15;
		$heading[] = 'Class Meetings';
		$width[]   = 30;
		$heading[] = 'Days of Week';
		$width[]   = 20;
		$heading[] = 'Class Times';
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
		
		while (!$res_co->EOF) {
			$PK_COURSE_OFFERING = $res_co->fields['PK_COURSE_OFFERING'];
	
			$CLASS_METTINGS_A = array();
			$PK_CAMPUS_ROOM_A = array();
			$TIME_A 		  = array();
			$DAYS1_A 		  = array();
			
			$res_build = $db->Execute("select SCHEDULE_DATE,PK_CAMPUS_ROOM,START_TIME,END_TIME from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY PK_CAMPUS_ROOM,START_TIME,END_TIME ORDER BY SCHEDULE_DATE ASC, START_TIME ASC"); 
			while (!$res_build->EOF) {
				//$SCHEDULE_DATE 	= $res_build->fields['SCHEDULE_DATE'];
				$PK_CAMPUS_ROOM = $res_build->fields['PK_CAMPUS_ROOM'];
				$START_TIME 	= $res_build->fields['START_TIME'];
				$END_TIME 		= $res_build->fields['END_TIME'];
				
				$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND START_TIME = '$START_TIME' AND END_TIME = '$END_TIME'  ORDER BY SCHEDULE_DATE ASC "); 
				$SCHEDULE_DATE = $res_build1->fields['SCHEDULE_DATE'];
				
				$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND START_TIME = '$START_TIME' AND END_TIME = '$END_TIME'  ORDER BY SCHEDULE_DATE DESC "); 
				$SCHEDULE_DATE1 = $res_build1->fields['SCHEDULE_DATE'];
				
				$CLASS_METTINGS_A[] = date("Y-m-d",strtotime($SCHEDULE_DATE)).' to '.date("Y-m-d",strtotime($SCHEDULE_DATE1));
				$PK_CAMPUS_ROOM_A[] = $PK_CAMPUS_ROOM;
				$TIME_A[]			= date("h:i A",strtotime($START_TIME)).' to '.date("h:i A",strtotime($END_TIME));
				
				$dates = array();
				
				$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND START_TIME = '$START_TIME' AND END_TIME = '$END_TIME' AND SCHEDULE_DATE BETWEEN '$SCHEDULE_DATE' AND  '$SCHEDULE_DATE1' ORDER BY SCHEDULE_DATE ASC "); 
				while (!$res_build1->EOF) {
					$dates[] = $res_build1->fields['SCHEDULE_DATE'];
					$res_build1->MoveNext();
				}
				
				$DAYS_A = array();
				foreach($dates as $date) {
					$N = date("N",strtotime($date));
					if($N == 1)
						$DAYS_A[$N] = 'M';
					else if($N == 2)
						$DAYS_A[$N] = 'Tu';
					else if($N == 3)
						$DAYS_A[$N] = 'W';
					else if($N == 4)
						$DAYS_A[$N] = 'Th';
					else if($N == 5)
						$DAYS_A[$N] = 'F';
					else if($N == 6)
						$DAYS_A[$N] = 'Sa';
					else if($N == 7)
						$DAYS_A[$N] = 'Su';
				}
				ksort($DAYS_A);
				$DAYS1_A[] = implode(", ",$DAYS_A);
				
				$res_build->MoveNext();
			}
			
			$res_stud = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, M_CAMPUS_PROGRAM.CODE, STUDENT_STATUS, PK_STUDENT_COURSE, STUDENT_ID, OFFICIAL_CAMPUS_NAME, COURSE_OFFERING_STUDENT_STATUS 
			FROM 
			S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_COURSE 
			LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
			, S_STUDENT_ENROLLMENT 
			LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
			LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
			LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
			LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
			WHERE 
			PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND 
			S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
			S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ");
			
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['OFFICIAL_CAMPUS_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['TERM_BEGIN_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['TRANSCRIPT_CODE']);
			
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
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['UNITS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['FA_UNITS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['HOURS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->RecordCount());
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['INSTRUCTOR']);
			
			$txt = "";
			foreach($PK_CAMPUS_ROOM_A as $key => $val) {
				$res = $db->Execute("SELECT ROOM_NO FROM M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM  = '$val' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
				if($res->RecordCount() > 0){
					if($txt != '')
						$txt .= "\r";
					$txt .= $res->fields['ROOM_NO'];
				}
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($txt);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getAlignment()->setWrapText(true);
			
			$txt = "";
			foreach($CLASS_METTINGS_A as $key => $val) {
				if($txt != '')
					$txt .= "\r";
				$txt .= $val;
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($txt);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getAlignment()->setWrapText(true);
			
			$txt = "";
			foreach($DAYS1_A as $key => $val) {
				if($txt != '')
					$txt .= "\r";
				$txt .= $val;
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($txt);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getAlignment()->setWrapText(true);
			
			$txt = "";
			foreach($TIME_A as $key => $val) {
				if($txt != '')
					$txt .= "\r";
				$txt .= $val;
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($txt);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getAlignment()->setWrapText(true);
			
			if($_POST['INCLUDE_STUDENTS'] == 1) {
				$objPHPExcel->setActiveSheetIndex(1);
				$objPHPExcel->getActiveSheet()->setTitle("Students");
				
				while (!$res_stud->EOF) {
					$line_2++;
					$index_2 =-1;
					
					$index_2++;
					$cell_no = $cell[$index_2].$line_2;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['TRANSCRIPT_CODE'].' ('. substr($res_co->fields['SESSION'],0,1).' - '. $res_co->fields['SESSION_NO'].')');
					
					$index_2++;
					$cell_no = $cell[$index_2].$line_2;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['NAME']);
					
					$index_2++;
					$cell_no = $cell[$index_2].$line_2;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_ID']);
					
					$index_2++;
					$cell_no = $cell[$index_2].$line_2;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['OFFICIAL_CAMPUS_NAME']);
					
					$index_2++;
					$cell_no = $cell[$index_2].$line_2;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CODE']);
					
					$index_2++;
					$cell_no = $cell[$index_2].$line_2;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_STATUS']);
					
					$index_2++;
					$cell_no = $cell[$index_2].$line_2;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['COURSE_OFFERING_STUDENT_STATUS']);
			
					$res_stud->MoveNext();
				}
			}
			
			$objPHPExcel->setActiveSheetIndex(0);
			
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
	<title><?=MNU_COURSE_OFFERING_BY_TERM?> | <?=$title?></title>
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
							<?=MNU_COURSE_OFFERING_BY_TERM ?>
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
											<?=TERM?>
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2">
											<br /><br />
											<input type="checkbox" id="INCLUDE_STUDENTS" name="INCLUDE_STUDENTS" value="1" >
											<?=INCLUDE_STUDENTS ?>
										</div>
									
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
											<!-- New -->
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
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
	
	function submit_form(val){
		document.getElementById('FORMAT').value = val
		document.form1.submit();
	}
	</script>
	

</body>

</html>