<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_balance.php");
require_once("check_access.php");

if(check_access('REPORT_FINANCE') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	$cond = "";

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
	
	if($_POST['INCLUDE_ALL_LEADS'] == 1){
		$sts = "";
		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 1) order by STUDENT_STATUS ASC");
		while (!$res_type->EOF) {
			if($sts != '')
				$sts .= ',';
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			$res_type->MoveNext();
		}
		if($sts != '')
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
	} 
	
	/* Ticket # 1273 */
	if(!empty($_POST['PK_TERM_MASTER'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER in (".implode(",",$_POST['PK_TERM_MASTER']).") ";
	}
	
	if(!empty($_POST['PK_CAMPUS_PROGRAM'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM in (".implode(",",$_POST['PK_CAMPUS_PROGRAM']).") ";
	}
	
	if(!empty($_POST['PK_STUDENT_GROUP'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP in (".implode(",",$_POST['PK_STUDENT_GROUP']).") ";
	}
	/* Ticket # 1273 */
	
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
	
	//echo $cond;exit;
	
	if($_POST['FORMAT'] == 1)
		$soret_order = " CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, BEGIN_DATE ASC, M_CAMPUS_PROGRAM.CODE ASC ";
	else
		$soret_order = " CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";
		
	$query = "select LAST_NAME, FIRST_NAME ,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, SSN, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, STUDENT_STATUS, STUDENT_ID, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%Y-%m-%d' )) AS EXPECTED_GRAD_DATE_1, CAMPUS_CODE     
	from 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  
	LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $cond $campus_cond1 
	GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ORDER BY  $soret_order ";
	//echo $query;exit;
	
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
				$this->SetY(5); // Ticket # 1272 
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L'); // Ticket # 1272 
				$this->MultiCell(65, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);  // Ticket # 1272 
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(228);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Packaging Summary", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(180, 12, 290, 12, $style);
				
				$this->SetFont('helvetica', 'I', 9);
				$this->SetY(13);
				$this->SetX(140);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
				
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
						$str = "�Status(es): ".$str;
				}
				
				$this->SetY(18);
				$this->SetX(185);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(104, 5, $str, 0, 'R', 0, 0, '', '', true);
				
				$INCLUDE_ALL_LEADS = 'No';
				if($_POST['INCLUDE_ALL_LEADS'] == 1)
					$INCLUDE_ALL_LEADS = 'Yes';
				
				$this->SetY(23);
				$this->SetX(185);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(104, 5, "Include All Leads: ".$INCLUDE_ALL_LEADS, 0, 'R', 0, 0, '', '', true);
				
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
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Last Name</td>
							<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />First Name</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Student ID</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Campus</td>
							
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />First Term</td>
							<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Program</td>
							<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Status</td>
							<td width="3%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />AY</td>
							<td width="11%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><br /><br />Fee Amount</td>
							<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><br /><br />Award Amount</td>
							<td width="11%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><br /><br />Difference</td>
						</tr>
					</thead>';

		$TOT_BALANCE 	= 0;
		$TOT_FEE_AMT	= 0;
		$TOT_AWARD_AMT	= 0;
		$res_stud = $db->Execute($query);
		while (!$res_stud->EOF) {
			$PK_STUDENT_ENROLLMENT = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
			$res_ay = $db->Execute("SELECT * FROM (SELECT ACADEMIC_YEAR FROM S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  
			UNION
			SELECT ACADEMIC_YEAR FROM S_STUDENT_DISBURSEMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND APPROVED_DATE != '0000-00-00') as TEMP 
			GROUP BY ACADEMIC_YEAR  ");
			
			if($res_ay->RecordCount() == 0) {
				$txt 	.= '<tr>
								<td width="9%" >'.$res_stud->fields['LAST_NAME'].'</td>
								<td width="9%" >'.$res_stud->fields['FIRST_NAME'].'</td>
								<td width="8%" >'.$res_stud->fields['STUDENT_ID'].'</td>
								<td width="7%" >'.$res_stud->fields['CAMPUS_CODE'].'</td>
								<td width="7%" >'.$res_stud->fields['BEGIN_DATE_1'].'</td>
								<td width="12%" >'.$res_stud->fields['PROGRAM_CODE'].'</td>
								<td width="12%" >'.$res_stud->fields['STUDENT_STATUS'].'</td>
								<td width="3%" ></td>
								<td width="11%" align="right" ></td>
								<td width="12%" align="right" ></td>
								<td width="11%" align="right" ></td>
							</tr>';
			} else {
				while (!$res_ay->EOF) {
					$ACADEMIC_YEAR = $res_ay->fields['ACADEMIC_YEAR'];
					
					$res_amt = $db->Execute("SELECT SUM(FEE_AMOUNT) as FEE_AMOUNT FROM S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACADEMIC_YEAR = '$ACADEMIC_YEAR' ");
					$FEE_AMT = $res_amt->fields['FEE_AMOUNT'];
					
					$res_amt = $db->Execute("SELECT SUM(DISBURSEMENT_AMOUNT) as DISBURSEMENT_AMOUNT FROM S_STUDENT_DISBURSEMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND APPROVED_DATE != '0000-00-00' AND ACADEMIC_YEAR = '$ACADEMIC_YEAR' ");
					$AWARD_AMT = $res_amt->fields['DISBURSEMENT_AMOUNT'];
					
					$BALANCE = $FEE_AMT - $AWARD_AMT;
					
					$TOT_BALANCE 	+= $BALANCE;
					$TOT_FEE_AMT	+= $FEE_AMT;
					$TOT_AWARD_AMT	+= $AWARD_AMT;
		
					if($BALANCE < 0)
						$BALANCE = '('.number_format_value_checker(($BALANCE * -1),2).')';
					else
						$BALANCE = number_format_value_checker($BALANCE,2);
						
					$txt 	.= '<tr>
									<td width="9%" >'.$res_stud->fields['LAST_NAME'].'</td>
									<td width="9%" >'.$res_stud->fields['FIRST_NAME'].'</td>
									<td width="8%" >'.$res_stud->fields['STUDENT_ID'].'</td>
									<td width="7%" >'.$res_stud->fields['CAMPUS_CODE'].'</td>
									<td width="7%" >'.$res_stud->fields['BEGIN_DATE_1'].'</td>
									<td width="12%" >'.$res_stud->fields['PROGRAM_CODE'].'</td>
									<td width="12%" >'.$res_stud->fields['STUDENT_STATUS'].'</td>
									<td width="3%" >'.$ACADEMIC_YEAR.'</td>
									<td width="11%" align="right" >$ '.number_format_value_checker($FEE_AMT,2).'</td>
									<td width="12%" align="right" >$ '.number_format_value_checker($AWARD_AMT,2).'</td>
									<td width="11%" align="right" >$ '.$BALANCE.'</td>
								</tr>';
					$res_ay->MoveNext();
				}
			}
			$res_stud->MoveNext();
		}
		
		if($TOT_BALANCE < 0)
			$TOT_BALANCE = '('.number_format_value_checker(($TOT_BALANCE * -1),2).')';
		else
			$TOT_BALANCE = number_format_value_checker($TOT_BALANCE,2);
		
		$txt 	.= '<tr>
						<td width="67%" colspan="8" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Total</b></td>
						<td width="11%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>$ '.number_format_value_checker($TOT_FEE_AMT,2).'</b></td>
						<td width="12%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>$ '.number_format_value_checker($TOT_AWARD_AMT,2).'</b></td>
						<td width="11%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>$ '.$TOT_BALANCE.'</b></td>
					</tr>
				</table>';

			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Packaging Summary.pdf';
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
		$file_name 		= 'Packaging Summary.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$line++;
		$index 	= -1;
		$heading[] = 'Last Name';
		$width[]   = 15;
		$heading[] = 'First Name';
		$width[]   = 15;
		$heading[] = 'Campus';
		$width[]   = 15;
		$heading[] = 'First Term';
		$width[]   = 15;
		$heading[] = 'Program';
		$width[]   = 15;
		$heading[] = 'Status';
		$width[]   = 15;
		$heading[] = 'AY';
		$width[]   = 10;
		$heading[] = 'Fee Amount';
		$width[]   = 15;
		$heading[] = 'Award Amount';
		$width[]   = 15;
		$heading[] = 'Difference';
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
		
		$res_stud = $db->Execute($query);
		while (!$res_stud->EOF) {
			$PK_STUDENT_ENROLLMENT = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
	
			$res_ay = $db->Execute("SELECT * FROM (SELECT ACADEMIC_YEAR FROM S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  
			UNION
			SELECT ACADEMIC_YEAR FROM S_STUDENT_DISBURSEMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND APPROVED_DATE != '0000-00-00') as TEMP 
			GROUP BY ACADEMIC_YEAR  ");
			
			if($res_ay->RecordCount() == 0) {
				$line++;
					$index = -1;
			
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['LAST_NAME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['FIRST_NAME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CAMPUS_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['BEGIN_DATE_1']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['PROGRAM_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_STATUS']);
					
					/* $index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ACADEMIC_YEAR);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FEE_AMT);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($AWARD_AMT);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BALANCE); */
			} else {
				while (!$res_ay->EOF) {
					$ACADEMIC_YEAR = $res_ay->fields['ACADEMIC_YEAR'];
					
					$res_amt = $db->Execute("SELECT SUM(FEE_AMOUNT) as FEE_AMOUNT FROM S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACADEMIC_YEAR = '$ACADEMIC_YEAR' ");
					$FEE_AMT = $res_amt->fields['FEE_AMOUNT'];
					
					$res_amt = $db->Execute("SELECT SUM(DISBURSEMENT_AMOUNT) as DISBURSEMENT_AMOUNT FROM S_STUDENT_DISBURSEMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND APPROVED_DATE != '0000-00-00' AND ACADEMIC_YEAR = '$ACADEMIC_YEAR' ");
					$AWARD_AMT = $res_amt->fields['DISBURSEMENT_AMOUNT'];
					
					if($FEE_AMT == '')
						$FEE_AMT = 0;
						
					if($AWARD_AMT == '')
						$AWARD_AMT = 0;
					
					$BALANCE = $FEE_AMT - $AWARD_AMT;

					$line++;
					$index = -1;
			
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['LAST_NAME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['FIRST_NAME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CAMPUS_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['BEGIN_DATE_1']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['PROGRAM_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_STATUS']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ACADEMIC_YEAR);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FEE_AMT);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($AWARD_AMT);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BALANCE);

					$res_ay->MoveNext();
				}
			}
	
			$res_stud->MoveNext();
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
	<title><?=MNU_PACKAGING_SUMMARY?> | <?=$title?></title>
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
							<?=MNU_PACKAGING_SUMMARY?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<!-- Ticket # 1273 -->
										<div class="col-md-2 ">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-3">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- Ticket # 1273 -->
										
										<div class="col-md-1" style="padding: 0;max-width:12.333%;flex: 0 0 12.333%;" >
											<br />
											<input type="checkbox" id="INCLUDE_ALL_LEADS" value="1" >
											<?=INCLUDE_ALL_LEADS?>
										</div>
										
										<div class="col-md-2" style="flex: 0 0 10.66667%;max-width: 10.66667%;" >
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

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	
	function submit_form(val){
		var valid = new Validation('form1', {onSubmit:false});
		var result = valid.validate();
		if(result == true){ 
			document.getElementById('FORMAT').value = val
			document.form1.submit();
		}
	}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
		
		/* Ticket # 1273 */
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '<?=GROUP_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
		});
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '<?=FIRST_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '<?=PROGRAM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		/* Ticket # 1273 */
	});
	</script>
</body>

</html>