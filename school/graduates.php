<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/projected_funds.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

// DIAM-709
function get_gpa_total($PK_STUDENT_MASTER,$enrollment_id)
{
	global $db;

	$summation_of_gpa      = 0;
	$summation_of_weight   = 0;

	$sap_array = array();
	$en_cond = "AND PK_STUDENT_ENROLLMENT IN ($enrollment_id)";

	$sql = "SELECT S_COURSE.TRANSCRIPT_CODE, 
				CREDIT_TRANSFER_STATUS, 
				S_COURSE.COURSE_DESCRIPTION, 
				S_STUDENT_CREDIT_TRANSFER.UNITS, 
				S_COURSE.FA_UNITS, 
				S_STUDENT_CREDIT_TRANSFER.TC_NUMERIC_GRADE, 
				S_STUDENT_CREDIT_TRANSFER.GRADE, 
				PK_STUDENT_ENROLLMENT, 
				S_STUDENT_CREDIT_TRANSFER.PK_GRADE, 
				S_GRADE.NUMBER_GRADE, 
				S_GRADE.CALCULATE_GPA, 
				S_GRADE.UNITS_ATTEMPTED, 
				S_GRADE.UNITS_COMPLETED, 
				S_GRADE.UNITS_IN_PROGRESS, 
				CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
				CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC) ELSE 0 END AS GPA_WEIGHT 
			FROM 
				S_STUDENT_CREDIT_TRANSFER 
				LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE 
				LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
				LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
			WHERE 
				S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
				AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
				AND SHOW_ON_TRANSCRIPT = 1 
				AND S_GRADE.CALCULATE_GPA = 1 
				$en_cond ";
	// echo $sql;exit;
	$res_tc = $db->Execute($sql);
	while (!$res_tc->EOF) 
	{

		$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE']; 
		$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT']; 

		$summation_of_gpa     += $TC_GPA_VALULE;
		$summation_of_weight  += $TC_GPA_WEIGHT;
		
		
		$res_tc->MoveNext();
	}

	$sql_course="SELECT TRANSCRIPT_CODE, 
						COURSE_DESCRIPTION, 
						S_STUDENT_COURSE.PK_COURSE_OFFERING, 
						FINAL_GRADE, 
						GRADE, 
						NUMERIC_GRADE, 
						NUMBER_GRADE, 
						CALCULATE_GPA, 
						UNITS_ATTEMPTED, 
						WEIGHTED_GRADE_CALC, 
						UNITS_COMPLETED, 
						UNITS_IN_PROGRESS, 
						COURSE_UNITS, 
						CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
						S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
						)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
						CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
						S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
						) ELSE 0 END AS GPA_WEIGHT 
					FROM 
						S_STUDENT_COURSE 
						LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
						LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
						LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE, 
						M_COURSE_OFFERING_STUDENT_STATUS 
					WHERE 
						S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'	
						AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
						AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
						AND SHOW_ON_TRANSCRIPT = 1
						AND S_GRADE.CALCULATE_GPA = 1 
						$en_cond 
					ORDER BY 
						TRANSCRIPT_CODE ASC";
	// echo $sql_course;exit;					
	$res_course = $db->Execute($sql_course);	
	while (!$res_course->EOF) { 
		
		if($res_course->fields['CALCULATE_GPA'] == 1) 
		{

			$GPA_VALULE 			= $res_course->fields['GPA_VALUE']; 
			$GPA_WEIGHT 			= $res_course->fields['GPA_WEIGHT']; 

			$summation_of_gpa    += $GPA_VALULE;
			$summation_of_weight += $GPA_WEIGHT;
		}

		$res_course->MoveNext();
	}

	$cumulative_gpa_total = $summation_of_gpa/$summation_of_weight;

	$sap_array = array();
	$sap_array['gpa_total'] = $cumulative_gpa_total;
	
	return $sap_array;

}
// End DIAM-709

if(!empty($_POST)){

	$cond = "";
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND (S_STUDENT_ENROLLMENT.GRADE_DATE BETWEEN '$ST' AND '$ET')";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND (S_STUDENT_ENROLLMENT.GRADE_DATE >= '$ST' ) ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND (S_STUDENT_ENROLLMENT.GRADE_DATE <= '$ET' ) ";
	}
	//echo $cond;exit;
	
	$campus_name  = "";
	$campus_cond  = "";
	$campus_cond1 = "";
	$campus_id	  = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	  = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$query = "SELECT CONCAT(LAST_NAME, ', ', FIRST_NAME) AS NAME, 
					IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00', '', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d')) AS BEGIN_DATE_1, 
					IF(EXPECTED_GRAD_DATE = '0000-00-00', '', DATE_FORMAT(EXPECTED_GRAD_DATE, '%Y-%m-%d')) AS EXPECTED_GRAD_DATE_1, 
					IF(GRADE_DATE = '0000-00-00', '', DATE_FORMAT(GRADE_DATE, '%Y-%m-%d')) AS GRADE_DATE_1, 
					IF(LDA = '0000-00-00', '', DATE_FORMAT(LDA, '%Y-%m-%d')) AS LDA, 
					SSN, 
					M_CAMPUS_PROGRAM.CODE AS PROGRAM_CODE, 
					STUDENT_STATUS, 
					STUDENT_ID, 
					S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, 
					S_STUDENT_MASTER.PK_STUDENT_MASTER,
					SESSION, 
					IF(ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00', '', 
					DATE_FORMAT(ORIGINAL_EXPECTED_GRAD_DATE, '%Y-%m-%d')) AS ORIGINAL_EXPECTED_GRAD_DATE, 
					CAMPUS_CODE 
				FROM 
					S_STUDENT_MASTER 
					LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER, 
					S_STUDENT_ENROLLMENT 
					LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
					LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
					LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
					LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
					LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS, 
					M_STUDENT_STATUS 
				WHERE 
					S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER 
					AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
					AND COMPLETED = 1 
					AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $campus_cond1 
				ORDER BY 
					CONCAT(LAST_NAME, ', ', FIRST_NAME) ASC, 
					S_TERM_MASTER.BEGIN_DATE ASC, 
					M_CAMPUS_PROGRAM.CODE ASC ";
	
	/////////////////////////////////////////////////////////////////
	if($_POST['FORMAT'] == 1){
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
				global $db;
				
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
				$this->SetX(165);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Graduates", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 202, 13, $style);
				
				$str = "";
				if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = " Between: ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
				else if($_POST['START_DATE'] != '')
					$str = " From ".$_POST['START_DATE'];
				else if($_POST['END_DATE'] != '')
					$str = " To ".$_POST['END_DATE'];
					
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
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
				
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(21);
				$this->SetX(52);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
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

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
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

		$res_fa = $db->Execute($query);

		$txt  = '';
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td width="100%" align="right" ><i>Student Count: '.$res_fa->RecordCount().'</i></td>
					</tr>
				</table>
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="13%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br />Student</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br />Student ID</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br />Campus</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br />First Term</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br />Program</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br />Session</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br />Status</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Original Expected<br />Grad Date</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Expected <br />Grad Date</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br />Grad Date</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br />LDA</td>
							<td width="4%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br />GPA</td>
						</tr>
					</thead>';
		
			while (!$res_fa->EOF) { 

				// DIAM-709
				$PK_STUDENT_MASTER 		 = $res_fa->fields['PK_STUDENT_MASTER'];
				$PK_STUDENT_ENROLLMENT   = $res_fa->fields['PK_STUDENT_ENROLLMENT'];

				$final_array		 	 = get_gpa_total($PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT);
				$GPA 					 = $final_array['gpa_total'];
				// End DIAM-709

				$txt 	.= '<tr>
							<td width="13%" >'.$res_fa->fields['NAME'].'</td>
							<td width="10%"  >'.$res_fa->fields['STUDENT_ID'].'</td>
							<td width="10%"  >'.$res_fa->fields['CAMPUS_CODE'].'</td>
							<td width="8%"  >'.$res_fa->fields['BEGIN_DATE_1'].'</td>
							<td width="10%" >'.$res_fa->fields['PROGRAM_CODE'].'</td>
							<td width="8%" >'.$res_fa->fields['SESSION'].'</td>
							<td width="8%" >'.$res_fa->fields['STUDENT_STATUS'].'</td>
							<td width="8%" >'.$res_fa->fields['ORIGINAL_EXPECTED_GRAD_DATE'].'</td>
							<td width="8%" >'.$res_fa->fields['EXPECTED_GRAD_DATE_1'].'</td>
							<td width="8%" >'.$res_fa->fields['GRADE_DATE_1'].'</td>
							<td width="8%" >'.$res_fa->fields['LDA'].'</td>
							<td width="4%" >'.number_format_value_checker($GPA,2).'</td>
						</tr>';
						
				$res_fa->MoveNext();
			}
			$txt 	.= '</table>';
		
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Graduates_'.uniqid().'.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
		/////////////////////////////////////////////////////////////////
	} else if($_POST['FORMAT'] == 2){
		
		$file_name = "Graduates.xlsx";
					
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
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

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
		$heading[] = 'Original Expected Grad Date';
		$width[]   = 20;
		$heading[] = 'Expected Grad Date';
		$width[]   = 20;
		$heading[] = 'Grad Date';
		$width[]   = 20;
		$heading[] = 'LDA';
		$width[]   = 20;
		$heading[] = 'GPA';
		$width[]   = 20;
		
		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
		}	

		$objPHPExcel->getActiveSheet()->freezePane('A1');
		
		$TOT_CREDIT = 0;
		$TOT_DEBIT 	= 0;
		
		$res_fa = $db->Execute($query);
		while (!$res_fa->EOF) {

			// DIAM-709
			$PK_STUDENT_MASTER 		 = $res_fa->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_ENROLLMENT   = $res_fa->fields['PK_STUDENT_ENROLLMENT'];

			$final_array		 	 = get_gpa_total($PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT);
			$GPA 					 = number_format_value_checker($final_array['gpa_total'],2);
			// End DIAM-709
			
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
			if($res_fa->fields['BEGIN_DATE_1'] != '' ) {
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['BEGIN_DATE_1'])));
				$objPHPExcel->getActiveSheet()->setCellValue($cell_no,$dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old-FORMAT_DATE_XLSX14
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
			if($res_fa->fields['ORIGINAL_EXPECTED_GRAD_DATE'] != '' ) {
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['ORIGINAL_EXPECTED_GRAD_DATE'])));
				$objPHPExcel->getActiveSheet()->setCellValue($cell_no,$dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old-FORMAT_DATE_XLSX14
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['EXPECTED_GRAD_DATE_1'] != '' ) {
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['EXPECTED_GRAD_DATE_1'])));
				$objPHPExcel->getActiveSheet()->setCellValue($cell_no,$dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old-FORMAT_DATE_XLSX14
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['GRADE_DATE_1'] != '' ) {
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['GRADE_DATE_1'])));
				$objPHPExcel->getActiveSheet()->setCellValue($cell_no,$dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old-FORMAT_DATE_XLSX14
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['LDA'] != '' ) {
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['LDA'])));
				$objPHPExcel->getActiveSheet()->setCellValue($cell_no,$dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old-FORMAT_DATE_XLSX14
			}

			// DIAM-709
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($GPA);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			// End DIAM-709
			
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
	<title><?=MNU_GRADUATES?> | <?=$title?></title>
	<style>
		#advice-required-entry-PK_CAMPUS{position: absolute;top: 65px;width: 140px}
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
							<?=MNU_GRADUATES?>
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
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="clear_search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
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
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
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
</body>

</html>