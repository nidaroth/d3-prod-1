<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_balance.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//header("location:projected_funds_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);
	
	$cond = "";
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND EXPECTED_GRAD_DATE BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND EXPECTED_GRAD_DATE >= '$ST' ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND EXPECTED_GRAD_DATE <= '$ET' ";
	}
	
	if(!empty($_POST['PK_STUDENT_STATUS'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".implode(",",$_POST['PK_STUDENT_STATUS']).") ";
	} else {
		$sts = "";
		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
		while (!$res_type->EOF) {
			if($sts != '')
				$sts .= ',';
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			$res_type->MoveNext();
		}
		
		if($sts != '')
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
	}
	
	/* Ticket # 1359  */
	$campus_name  = "";
	$campus_cond  = "";
	$campus_cond1 = "";
	$campus_id	  = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	  = implode(",",$_POST['PK_CAMPUS']);
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
	/* Ticket # 1359  */

	//echo $cond;exit;
	$query = "select CONCAT(LAST_NAME,', ', FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME ,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d' )) AS BEGIN_DATE_1, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%Y-%m-%d' )) AS EXPECTED_GRAD_DATE_1, IF(ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(ORIGINAL_EXPECTED_GRAD_DATE, '%Y-%m-%d' )) AS ORIGINAL_EXPECTED_GRAD_DATE_1, IF(GRADE_DATE = '0000-00-00', '', DATE_FORMAT(GRADE_DATE, '%Y-%m-%d' )) AS GRADE_DATE, SSN, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, STUDENT_STATUS, STUDENT_ID, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.PK_STUDENT_MASTER, CAMPUS_CODE, IF(LDA = '0000-00-00', '', DATE_FORMAT(LDA, '%Y-%m-%d' )) AS LDA, IF(DETERMINATION_DATE = '0000-00-00', '', DATE_FORMAT(DETERMINATION_DATE, '%Y-%m-%d' )) AS DETERMINATION_DATE, SESSION    
	from 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	,M_STUDENT_STATUS 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $campus_cond1 
	ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";
	
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
				$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(235);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Expected Graduates", 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(180, 13, 290, 13, $style);
				
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(16);
				$this->SetX(140);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
				
				$str = "";
				if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = " Between ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
				else if($_POST['START_DATE'] != '')
					$str = " From ".$_POST['START_DATE'];
				else if($_POST['END_DATE'] != '')
					$str = " To ".$_POST['END_DATE'];

				$this->SetY(23);
				$this->SetX(140);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(150, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$str = "";
				if(empty($_POST['PK_STUDENT_STATUS'])) {
					$str = "All Student Status";
				} else {
					$str = "";
					$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (".implode(",",$_POST['PK_STUDENT_STATUS']).") order by STUDENT_STATUS ASC");
					while (!$res_type->EOF) {
						if($str != '')
							$str .= ', ';
						$str .= $res_type->fields['STUDENT_STATUS'];
						$res_type->MoveNext();
					}
					
					if($str != '')
						$str = "Status(es): ".$str;
				}
				
				$this->SetY(26);
				$this->SetX(140);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(150, 5, $str, 0, 'R', 0, 0, '', '', true);
				
			}
			public function Footer() {
				global $db;
				
				$this->SetY(-15);
				$this->SetX(180);
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
		$pdf->SetMargins(7, 35, 7);
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
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="11%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Student</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Student ID</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />First Term</td>
							<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Program</td>
							<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Status</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Original Expected<br />Grad Date</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Expected<br />Grad Date</td>
							<td width="5%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />GPA</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Home Phone</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Mobile Phone</td>
							<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Address</td>
						</tr>
					</thead>';

		$res_fa = $db->Execute($query);
		while (!$res_fa->EOF) { 
			$PK_STUDENT_MASTER 		= $res_fa->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_ENROLLMENT 	= $res_fa->fields['PK_STUDENT_ENROLLMENT'];
			
			$Denominator 	= 0;
			$Numerator 		= 0;
			$Numerator1 	= 0;
			/* Ticket # 1152 */
			$res_course = $db->Execute("select NUMERIC_GRADE, COURSE_UNITS, NUMBER_GRADE  
			FROM
			S_STUDENT_COURSE, M_COURSE_OFFERING_STUDENT_STATUS, S_COURSE_OFFERING, S_COURSE, S_TERM_MASTER, M_SESSION, S_GRADE
			WHERE 
			S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
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
			/* Ticket # 1152 */
				
			$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
			
			$HOME_PHONE 	= preg_replace( '/[^0-9]/', '',$res_add->fields['HOME_PHONE']);
			$CELL_PHONE 	= preg_replace( '/[^0-9]/', '',$res_add->fields['CELL_PHONE']);

			if($HOME_PHONE != '')
				$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
	
			if($CELL_PHONE != '')
				$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
			
			$txt 	.= '<tr>
						<td width="11%" >'.$res_fa->fields['NAME'].'</td>
						<td width="8%"  >'.$res_fa->fields['STUDENT_ID'].'</td>
						<td width="7%"  >'.$res_fa->fields['BEGIN_DATE_1'].'</td>
						<td width="9%" >'.$res_fa->fields['PROGRAM_CODE'].'</td>
						<td width="9%" >'.$res_fa->fields['STUDENT_STATUS'].'</td>
						<td width="8%" >'.$res_fa->fields['ORIGINAL_EXPECTED_GRAD_DATE_1'].'</td>
						<td width="8%" >'.$res_fa->fields['EXPECTED_GRAD_DATE_1'].'</td>
						<td width="5%" >'.number_format_value_checker(($Numerator1/$Denominator),2).'</td>
						<td width="8%" >'.$HOME_PHONE.'</td>
						<td width="8%" >'.$CELL_PHONE.'</td>
						<td width="20%" >'.$res_add->fields['ADDRESS'].'<br />'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'</td>
					</tr>';
					
			$res_fa->MoveNext();
		}
		
		$txt 	.= '</table>';
		
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Expected Graduates'.'.pdf';
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
		$file_name 		= 'Expected Graduates.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= -1;

		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Session';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 20;
		$heading[] = 'Original Grad Date';
		$width[]   = 20;
		$heading[] = 'Expected Grad Date';
		$width[]   = 20;
		$heading[] = 'Determination Date';
		$width[]   = 20;
		$heading[] = 'Grad Date';
		$width[]   = 20;
		$heading[] = 'LDA';
		$width[]   = 20;
		$heading[] = 'GPA';
		$width[]   = 10;
		$heading[] = 'Email';
		$width[]   = 20;
		$heading[] = 'Home Phone';
		$width[]   = 20;
		$heading[] = 'Mobile Phone';
		$width[]   = 20;
		$heading[] = 'Other Phone';
		$width[]   = 20;
		$heading[] = 'Address';
		$width[]   = 50;
		
		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			$i++;
		}	

		$objPHPExcel->getActiveSheet()->freezePane('A1');
	
		$res_fa = $db->Execute($query);
		while (!$res_fa->EOF) { 
			$PK_STUDENT_MASTER 		= $res_fa->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_ENROLLMENT 	= $res_fa->fields['PK_STUDENT_ENROLLMENT'];
			
			$Denominator 	= 0;
			$Numerator 		= 0;
			$Numerator1 	= 0;
			/* Ticket # 1152 */
			$res_course = $db->Execute("select NUMERIC_GRADE, COURSE_UNITS, NUMBER_GRADE  
			FROM
			S_STUDENT_COURSE, M_COURSE_OFFERING_STUDENT_STATUS, S_COURSE_OFFERING, S_COURSE, S_TERM_MASTER, M_SESSION, S_GRADE 
			WHERE 
			S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
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
			/* Ticket # 1152 */

			$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
			
			$HOME_PHONE 	= preg_replace( '/[^0-9]/', '',$res_add->fields['HOME_PHONE']);
			$CELL_PHONE 	= preg_replace( '/[^0-9]/', '',$res_add->fields['CELL_PHONE']);
			$OTHER_PHONE 	= preg_replace( '/[^0-9]/', '',$res_add->fields['OTHER_PHONE']);

			if($HOME_PHONE != '')
				$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
	
			if($CELL_PHONE != '')
				$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
				
			if($OTHER_PHONE != '')
				$OTHER_PHONE = '('.$OTHER_PHONE[0].$OTHER_PHONE[1].$OTHER_PHONE[2].') '.$OTHER_PHONE[3].$OTHER_PHONE[4].$OTHER_PHONE[5].'-'.$OTHER_PHONE[6].$OTHER_PHONE[7].$OTHER_PHONE[8].$OTHER_PHONE[9];

			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT_ID']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['CAMPUS_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['BEGIN_DATE_1'] != '') {
				$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['BEGIN_DATE_1'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old - FORMAT_DATE_XLSX14
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROGRAM_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['SESSION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['ORIGINAL_EXPECTED_GRAD_DATE_1'] != '') {
				$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['ORIGINAL_EXPECTED_GRAD_DATE_1'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old - FORMAT_DATE_XLSX14
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['EXPECTED_GRAD_DATE_1'] != '') {
				$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['EXPECTED_GRAD_DATE_1'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old - FORMAT_DATE_XLSX14
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['DETERMINATION_DATE'] != '') {
				$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['DETERMINATION_DATE'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old - FORMAT_DATE_XLSX14
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['GRADE_DATE'] != '') {
				$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['GRADE_DATE'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old - FORMAT_DATE_XLSX14
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['LDS'] != '') {
				$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['LDS'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old - FORMAT_DATE_XLSX14
			}
			
			if($Numerator1 > 0 && $Denominator > 0)
				$GPA = $Numerator1/$Denominator;
			else 
				$GPA = 0;

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($GPA);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode ("0.00");
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_add->fields['EMAIL']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($HOME_PHONE);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CELL_PHONE);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($OTHER_PHONE);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_add->fields['ADDRESS']."\r".$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP']);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getAlignment()->setWrapText(true);
			
			$res_fa->MoveNext();
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
	<title><?=MNU_EXPECTED_GRADUATES?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_STUDENT_STATUS{position: absolute;top: 57px;width: 140px}
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
							<?=MNU_EXPECTED_GRADUATES?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<!-- Ticket # 1359 -->
										<div class="col-md-2" >
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- Ticket # 1359 -->
										
										<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE ?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										<div class="col-md-4">
											<?=STATUS?> <!-- Ticket # 1359 -->
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" style="padding: 0;max-width:10.667%;flex: 0 0 10.667%;" >
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
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});
		
		/* Ticket # 1359 */
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		/* Ticket # 1359 */
	});
	</script>
</body>

</html>