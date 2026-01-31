<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/unapproved_disbursement.php");
require_once("check_access.php");

if(check_access('REPORT_FINANCE') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//header("location:projected_funds_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);
	// echo "<pre>";print_r($_REQUEST);exit;
	if(isset($_REQUEST['PK_LEDGER_CODE_GROUP'])){
		include_once('unapproved_disbursements_by_code_group.php');
		exit;
	}
	if(isset($_REQUEST['PK_LEDGER_CODE_GROUP'])){
		$imploded = implode(',',$_REQUEST['PK_LEDGER_CODE_GROUP']);
		$ar_ledger_codes = $db->Execute("SELECT GROUP_CONCAT(PK_AR_LEDGER_CODES) AS CONCATED_RES FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP IN ($imploded) ");
		$ar_ledger_codes = explode(',' , $ar_ledger_codes->fields['CONCATED_RES']);
		$ar_ledger_codes = array_unique($ar_ledger_codes);
		$_POST['PK_AR_LEDGER_CODE'] = $ar_ledger_codes;
		
	}
	$cond = "";
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND DISBURSEMENT_DATE BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND DISBURSEMENT_DATE >= '$ST' ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND DISBURSEMENT_DATE <= '$ET' ";
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
	
	$ledger_cond = ""; 
	if(!empty($_POST['PK_AR_LEDGER_CODE'])) {
		$ledger_cond = " AND PK_AR_LEDGER_CODE in (".implode(",",$_POST['PK_AR_LEDGER_CODE']).") ";
	}
	
	if(!empty($_POST['PK_CAMPUS_PROGRAM'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM in (".implode(",",$_POST['PK_CAMPUS_PROGRAM']).") ";
	}
	
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
	
	$query = "select S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT,CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, ACADEMIC_YEAR, ACADEMIC_PERIOD, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%Y-%m-%d' )) AS DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT, DISBURSEMENT_STATUS ,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, SSN, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, M_CAMPUS_PROGRAM.UNITS , IF(MIDPOINT_DATE = '0000-00-00','', DATE_FORMAT(MIDPOINT_DATE, '%Y-%m-%d' )) AS MIDPOINT_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE, '%Y-%m-%d' )) AS EXPECTED_GRAD_DATE, STUDENT_STATUS, S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.PK_STUDENT_MASTER, CAMPUS_CODE, STUDENT_ID 
	FROM  
	S_STUDENT_DISBURSEMENT 
	LEFT JOIN M_DISBURSEMENT_STATUS ON M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS = S_STUDENT_DISBURSEMENT.PK_DISBURSEMENT_STATUS,  
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	, S_STUDENT_CAMPUS 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	WHERE 
	S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_DISBURSEMENT.PK_DISBURSEMENT_STATUS = 0 AND IS_ACTIVE_ENROLLMENT = 1 $cond $campus_cond1 ";
		
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
				$this->SetY(5);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(55, 8, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(115);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Unapproved Disbursements", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(108, 13, 202, 13, $style);
				
				$str = "";
				if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = " between ".$_POST['START_DATE'].' and '.$_POST['END_DATE'];
				else if($_POST['START_DATE'] != '')
					$str = " from ".$_POST['START_DATE'];
				else if($_POST['END_DATE'] != '')
					$str = " to ".$_POST['END_DATE'];
					
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, "Disbursement Date".$str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$str = "";
				if(empty($_POST['PK_STUDENT_STATUS'])) {
					$str = "All Student Status";
				} else {
					$str = "";
					$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (".implode(",",$_POST['PK_STUDENT_STATUS']).") order by STUDENT_STATUS ASC");
					while (!$res_type->EOF) {
						if($str != '')
							$str .= ',';
						$str .= $res_type->fields['STUDENT_STATUS'];
						$res_type->MoveNext();
					}
				}
				
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(19);
				$this->SetX(40);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(160, 5, $str, 0, 'R', 0, 0, '', '', true);
				
				$this->SetY(23);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
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
		$pdf->SetFont('helvetica', '', 8, '', true);
		$pdf->AddPage();
		
		$total 	= 0;
		$txt 	= '';
		$res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE FROM M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $ledger_cond ORDER BY CODE ASC ");
		while (!$res_ledger->EOF) { 
			$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];
			
			$sub_total = 0;
			$res_disp = $db->Execute($query." AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' GROUP BY S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, DISBURSEMENT_DATE ASC ");
			
			if($res_disp->RecordCount() > 0) {
			
				$txt 	.= '<h1><i>Ledger Code: '.$res_ledger->fields['CODE'].'</i></h1>';
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">';
				$txt 	.= '<tr>
								<td width="20%" style="border-bottom:1px solid #000;" ><br /><br />Student</td>
								<td width="10%" style="border-bottom:1px solid #000;" ><br /><br />SSN</td>
								<td width="11%" style="border-bottom:1px solid #000;" ><br /><br />Student ID</td>
								<td width="13%" style="border-bottom:1px solid #000;" ><br /><br />Campus</td>
								<td width="18%" style="border-bottom:1px solid #000;" ><br /><br />Status</td>
								<td width="7%" style="border-bottom:1px solid #000;" ><br /><br />GPA</td>
								<td width="10%" style="border-bottom:1px solid #000;" >Disbursement<br />Date</td>
								<td width="14%" align="right" style="border-bottom:1px solid #000;" >Disbursement<br />Amount</td>
							</tr>';
			
				while (!$res_disp->EOF) { 
				
					$SSN = $res_disp->fields['SSN'];
					if($SSN != '') {
						$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'],$SSN);
						$SSN_ORG = $SSN;
						$SSN_ARR = explode("-",$SSN);
						$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
					}
					
					$PK_STUDENT_ENROLLMENT 	= $res_disp->fields['PK_STUDENT_ENROLLMENT'];
					$PK_STUDENT_MASTER 		= $res_disp->fields['PK_STUDENT_MASTER'];
					
					$c_in_comp_tot 	= 0;
					$c_in_cu_gnu	= 0;
					/* Ticket # 1152 */
					$res_course = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING, BEGIN_DATE as BEGIN_DATE_1, FINAL_GRADE, IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, COURSE_CODE, COURSE_DESCRIPTION, GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS from S_STUDENT_COURSE LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY BEGIN_DATE_1 ASC, COURSE_CODE ASC ");	
					while (!$res_course->EOF) { 
					
						$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
						$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
						$COMPLETED_UNITS	 = 0;
						$ATTEMPTED_UNITS	 = 0;
						
						if($res_course->fields['UNITS_ATTEMPTED'] == 1)
							$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
						
						$c_in_att_tot += $ATTEMPTED_UNITS; 
							
						if($res_course->fields['UNITS_COMPLETED'] == 1) {
							$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
							$c_in_comp_tot  	+= $COMPLETED_UNITS;
						}
						
						$gnu = 0;
						if($res_course->fields['CALCULATE_GPA'] == 1) {
							$gnu 			= $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE']; 
							$c_in_cu_gnu 	+= $gnu;
						}
						
						$res_course->MoveNext();
					} 
					/* Ticket # 1152 */
					
					/* Ticket #1146 */
					if($_POST['INCLUDE_TRANSFER_CREDIT_IN_GPA'] != 2){
						$res_grade = $db->Execute("SELECT UNITS, S_GRADE.UNITS_COMPLETED, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE FROM S_STUDENT_CREDIT_TRANSFER LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); // Ticket # 1152
						while (!$res_grade->EOF) {
							$c_in_comp_tot += $res_grade->fields['UNITS'];  
							if($res_grade->fields['CALCULATE_GPA'] == 1) {
								$c_in_cu_gnu += $res_grade->fields['UNITS'] * $res_grade->fields['NUMBER_GRADE']; 
							}
							
							$res_grade->MoveNext();
						}
					}
					/* Ticket #1146 */
					
					$txt 	.= '<tr>
									<td width="20%" >'.$res_disp->fields['NAME'].'</td>
									<td width="10%" >'.$SSN_ORG.'</td>
									<td width="11%" >'.$res_disp->fields['STUDENT_ID'].'</td>
									<td width="13%" >'.$res_disp->fields['CAMPUS_CODE'].'</td>
									<td width="18%" >'.$res_disp->fields['STUDENT_STATUS'].'</td>
									<td width="7%" >'.number_format_value_checker(($c_in_cu_gnu / $c_in_comp_tot),2).'</td>
									<td width="10%" >'.$res_disp->fields['DISBURSEMENT_DATE'].'</td>
									<td width="14%" align="right" >$ '.number_format_value_checker($res_disp->fields['DISBURSEMENT_AMOUNT'],2).'</td>
								</tr>';
							
					$sub_total += $res_disp->fields['DISBURSEMENT_AMOUNT'];
					$res_disp->MoveNext();
				}
			
				$total += $sub_total;
				$txt 	.= '<tr>
								<td width="89%" style="border-top:1px solid #000;font-size:35px" ><i>Ledger Code: '.$res_ledger->fields['CODE'].' Totals: </i></td>
								<td width="14%" align="right" style="border-top:1px solid #000;font-size:35px;" align="right" >$ '.number_format_value_checker($sub_total,2).'</td>
							</tr>
						</table>';
			}
			$res_ledger->MoveNext();
		}
		
		$txt 	.= '<br /><br />
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="103%" align="right" style="font-size:45px;" align="right" ><i>Report Total $ '.number_format_value_checker($total,2).'</i></td>
						</tr>
					</table>';
		
		
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Unapproved Disbursements'.'.pdf';
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
		$file_name 		= 'Unapproved Disbursements.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$line++;
		$index 	= -1;
		
		$heading[] = 'Student';
		$width[]   = 15;
		$heading[] = 'SSN';
		$width[]   = 15;
		$heading[] = 'Student ID';
		$width[]   = 15;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 15;
		$heading[] = 'GPA';
		$width[]   = 15;
		$heading[] = 'Ledger Code';
		$width[]   = 20;
		$heading[] = 'Disbursement Date';
		$width[]   = 15;
		$heading[] = 'Disbursement Amount';
		$width[]   = 15;

		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			
			$i++;
		}
		
		$res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE FROM M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $ledger_cond ORDER BY CODE ASC ");
		while (!$res_ledger->EOF) { 
			$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];
			
			$sub_total = 0;
			$res_disp = $db->Execute($query." AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' GROUP BY S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, DISBURSEMENT_DATE ASC ");
			
			if($res_disp->RecordCount() > 0) {
				while (!$res_disp->EOF) { 
				
					$SSN = $res_disp->fields['SSN'];
					if($SSN != '') {
						$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'],$SSN);
						$SSN_ORG = $SSN;
						$SSN_ARR = explode("-",$SSN);
						$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
					}
					
					$PK_STUDENT_ENROLLMENT 	= $res_disp->fields['PK_STUDENT_ENROLLMENT'];
					$PK_STUDENT_MASTER 		= $res_disp->fields['PK_STUDENT_MASTER'];
					
					$c_in_comp_tot 	= 0;
					$c_in_cu_gnu	= 0;
					/* Ticket # 1152 */
					$res_course = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING, BEGIN_DATE as BEGIN_DATE_1, FINAL_GRADE, IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS BEGIN_DATE, COURSE_CODE, COURSE_DESCRIPTION, GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS from S_STUDENT_COURSE LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY BEGIN_DATE_1 ASC, COURSE_CODE ASC ");	
					while (!$res_course->EOF) { 
					
						$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
						$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
						$COMPLETED_UNITS	 = 0;
						$ATTEMPTED_UNITS	 = 0;
						
						if($res_course->fields['UNITS_ATTEMPTED'] == 1)
							$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
						
						$c_in_att_tot += $ATTEMPTED_UNITS; 
							
						if($res_course->fields['UNITS_COMPLETED'] == 1) {
							$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
							$c_in_comp_tot  	+= $COMPLETED_UNITS;
						}
						
						$gnu = 0;
						if($res_course->fields['CALCULATE_GPA'] == 1) {
							$gnu 			= $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE']; 
							$c_in_cu_gnu 	+= $gnu;
						}
						
						$res_course->MoveNext();
					} 
					/* Ticket # 1152 */
					
					/* Ticket #1146 */
					if($_POST['INCLUDE_TRANSFER_CREDIT_IN_GPA'] != 2){
						$res_grade = $db->Execute("SELECT UNITS, S_GRADE.UNITS_COMPLETED, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE FROM S_STUDENT_CREDIT_TRANSFER LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); // Ticket # 1152 
						while (!$res_grade->EOF) {
							$c_in_comp_tot += $res_grade->fields['UNITS'];  
							if($res_grade->fields['CALCULATE_GPA'] == 1) {
								$c_in_cu_gnu += $res_grade->fields['UNITS'] * $res_grade->fields['NUMBER_GRADE']; 
							}
							
							$res_grade->MoveNext();
						}
					}
					/* Ticket #1146 */
					
					$line++;
					$index = -1;
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['NAME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SSN_ORG);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['STUDENT_ID']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['CAMPUS_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['STUDENT_STATUS']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker(($c_in_cu_gnu / $c_in_comp_tot),2));
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_DATE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_AMOUNT']);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode('#,##0.00');
		
					$res_disp->MoveNext();
				}
			}
			$res_ledger->MoveNext();
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
	<title><?=MNU_UNAPPROVED_DISBURSEMENT?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_AR_LEDGER_CODE, #advice-required-entry-PK_STUDENT_STATUS, #advice-required-entry-PK_CAMPUS{position: absolute;top: 57px;width: 140px}
		.option_red > a > label{color:red !important}
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
							<?=MNU_UNAPPROVED_DISBURSEMENT?>
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
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2">
											<?=AWARD_LEDGER_CODES?>
											<select id="PK_AR_LEDGER_CODE" name="PK_AR_LEDGER_CODE[]" multiple class="form-control" >
												 <? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>"><?=$res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<button class="linkbutton" id="PK_AR_LEDGER_CODE_helper_text" style="display:none" type="button" onclick="ToggleLedgerSelection('PK_LEDGER_CODE_GROUP')">Use Ledger Code</button>
										</div>
										<div class="col-md-2">
											<div class="form-group">
													Ledger Code Group
														<select id="PK_LEDGER_CODE_GROUP" name="PK_LEDGER_CODE_GROUP[]" multiple class="form-control " disabled>
															<? $res_type = $db->Execute("SELECT PK_LEDGER_CODE_GROUP,LEDGER_CODE_GROUP,LEDGER_CODE_GROUP_DESC,ACTIVE from S_LEDGER_CODE_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, LEDGER_CODE_GROUP ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?php echo $res_type->fields['PK_LEDGER_CODE_GROUP'] ?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['LEDGER_CODE_GROUP'] ?><? if($res_type->fields['ACTIVE'] == 0) echo " (Inactive)"; ?></option>
															<? $res_type->MoveNext();
															} ?>
														</select>
														<style>.linkbutton{
															background: none!important;
															border: none;
															padding: 0!important;
															/*optional*/
															font-family: arial, sans-serif;
															/*input has OS specific font-family*/
															color: #069;
															text-decoration: underline;
															cursor: pointer;
															}
															.multiselect.dropdown-toggle.btn.btn-default.disabled{
																background-color:  #cbcbcb !important;
															}
														</style>
														<button class="linkbutton" id="PK_LEDGER_CODE_GROUP_helper_text" type="button" onclick="ToggleLedgerSelection('PK_AR_LEDGER_CODE')">Use Ledger Group</button>
														<script>
														var togglewith = '';
														function ToggleLedgerSelection(AlternateID = 'not_initiated'){
															jQuery(document).ready(function($) { 
																if( AlternateID != 'PK_LEDGER_CODE_GROUP'){
																	togglewith = AlternateID;
																	AlternateIDopt = document.getElementById(AlternateID);
																	if(AlternateIDopt.getAttribute('multiple') !== null){
																		$('#'+AlternateID).multiselect('disable');
																		$('#PK_LEDGER_CODE_GROUP').multiselect('enable'); 
																		add_toggerler(AlternateID);

																	}

																}else{ 
																	if(AlternateIDopt.getAttribute('multiple') !== null){ 	 
																		$('#'+AlternateID).multiselect('disable');
																		$('#'+togglewith).multiselect('enable'); 											
																		add_toggerler(AlternateID);
																	}
																}  
																});
														}
													 
														function add_toggerler(AlternateID){
															jQuery(document).ready(function($) {
																if(AlternateID != 'PK_LEDGER_CODE_GROUP'){
																	$('#'+AlternateID+'_helper_text').show();
																	$('#PK_LEDGER_CODE_GROUP_helper_text').hide();
																}else{
																	$('#'+AlternateID+'_helper_text').show();
																	$('#'+togglewith+'_helper_text').hide();
																} 
															});
														}
														</script>
													</div> 
													
											</div>
										<div class="col-md-2 ">
											<?=PROGRAM?>
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2">
											<?=STATUS?>
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									
									<div class="row m-t-40">	
										<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										
										<!-- Ticket #1146 -->
										<div class="col-md-2">
											<?=INCLUDE_TRANSFER_CREDIT_IN_GPA?>
											<select id="INCLUDE_TRANSFER_CREDIT_IN_GPA" name="INCLUDE_TRANSFER_CREDIT_IN_GPA" class="form-control required-entry" >
												<option value="1"><?=YES?></option>
												<option value="2"><?=NO?></option>
											</select>
										</div>	
										<!-- Ticket #1146 -->
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
				if($('#PK_AR_LEDGER_CODE').val() == '' && $('#PK_LEDGER_CODE_GROUP').val() == ''){
					result = false;
					console.log('PK_AR_LEDGER_CODE',$('#PK_AR_LEDGER_CODE').val());
					console.log('PK_LEDGER_CODE_GROUP',$('#PK_LEDGER_CODE_GROUP').val());
					alert("Select At Least One Ledger Code or Group")
				}
			}
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
		$('#PK_AR_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=AWARD_LEDGER_CODES?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=AWARD_LEDGER_CODES?> selected'
		});
		$('#PK_LEDGER_CODE_GROUP').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All Ledger Groups',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: 'Ledger Groups selected'
			});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
		
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
	});
	</script>

</body>

</html>