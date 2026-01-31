<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/title_iv.php");
require_once("check_access.php");

if(check_access('REPORT_FINANCE') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){

	$cond = "";
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND TRANSACTION_DATE BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND TRANSACTION_DATE >= '$ST' ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND TRANSACTION_DATE <= '$ET' ";
	}
	
	$PK_AR_LEDGER_CODE_ARR = array();
	$res_led = $db->Execute("SELECT PK_AR_LEDGER_CODE FROM M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ");
	while (!$res_led->EOF) { 
		$PK_AR_LEDGER_CODE_ARR[] = $res_led->fields['PK_AR_LEDGER_CODE'];
		$res_led->MoveNext();
	}
	$PK_AR_LEDGER_CODE = implode(",",$PK_AR_LEDGER_CODE_ARR);
	
	/* Ticket # 1319  */
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
	$query = "select S_STUDENT_LEDGER.PK_STUDENT_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, SSN, STUDENT_ID, CAMPUS_CODE      
	from 
	S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_LEDGER, M_AR_LEDGER_CODE, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE AND CREDIT != 0 AND 
	S_STUDENT_LEDGER.PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE) AND M_AR_LEDGER_CODE.TYPE = 1 AND TITLE_IV = 1 $cond $campus_cond1 
	GROUP BY S_STUDENT_LEDGER.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ";

	$query2 = "select CREDIT AS AMOUNT 
	from 
	S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_LEDGER, M_AR_LEDGER_CODE, S_STUDENT_CAMPUS    
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE AND CREDIT != 0 $cond $campus_cond1 ";
	/* Ticket # 1319  */
	
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR[] = 9;
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR[] = 7;
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR[] = 10;
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR[] = 8;
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR[] = 5;
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR[] = 6;
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR[] = 4;
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR[] = 2;
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR[] = 3;
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR[] = 1;
	
	$PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER = array();
	foreach($PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR as $PK_TITLE_IV_RECIPIENTS_CATEGORY) {
		$PK_AR_LEDGER_CODE = array();
		$res_cat_led = $db->Execute("SELECT M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER.PK_AR_LEDGER_CODE FROM M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER, M_AR_LEDGER_CODE WHERE PK_TITLE_IV_RECIPIENTS_CATEGORY = '$PK_TITLE_IV_RECIPIENTS_CATEGORY' AND M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_AR_LEDGER_CODE.TYPE = 1 AND TITLE_IV = 1");
		while (!$res_cat_led->EOF) {
			$PK_AR_LEDGER_CODE[] = $res_cat_led->fields['PK_AR_LEDGER_CODE'];
			$res_cat_led->MoveNext();
		}
		$PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER[] = implode(",",$PK_AR_LEDGER_CODE);
	}
	
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
				global $db,$campus_name;
				
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
				$this->SetX(195);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Title IV Recipients By Category", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(185, 13, 292, 13, $style);
				
				$str = "Selected Ledger Transactions Between ";
				if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str .= $_POST['START_DATE'].' - '.$_POST['END_DATE'];
				else if($_POST['START_DATE'] != '')
					$str .= "From ".$_POST['START_DATE'];
				else if($_POST['END_DATE'] != '')
					$str .= "As of Date: ".$_POST['END_DATE'];

				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(18);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
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
		
		$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:1px solid #000;" rowspan="2" align="center" ><br /><br /><b>Student</b></td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:1px solid #000;" rowspan="2" align="center" ><br /><br /><b>SSN</b></td>';
							
							foreach($PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR as $PK_TITLE_IV_RECIPIENTS_CATEGORY) {
								$res_cat = $db->Execute("SELECT CODE FROM M_TITLE_IV_RECIPIENTS_CATEGORY WHERE PK_TITLE_IV_RECIPIENTS_CATEGORY='$PK_TITLE_IV_RECIPIENTS_CATEGORY'"); 
								
								$txt .= '<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:1px solid #000;" align="center" ><b>'.$res_cat->fields['CODE'].'</b></td>';
							}
							
							$txt .= '<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" rowspan="2" align="center" ><br /><br /><b>Current Status</b></td>
						</tr>
						
						<tr>
							
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >Disbursed<br />Refunded</td>
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >Disbursed<br />Refunded</td>
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >Disbursed<br />Refunded</td>
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >Disbursed<br />Refunded</td>
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >Disbursed<br />Refunded</td>
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >Disbursed<br />Refunded</td>
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >Disbursed<br />Refunded</td>
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >Disbursed<br />Refunded</td>
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >Disbursed<br />Refunded</td>
							<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-left:1px solid #000;" align="right" >Disbursed<br />Refunded</td>
							
						</tr>
					</thead>';

		$Disbursed_TOT = array();
		$Refunded_TOT  = array();
		$STUD_TOT 	   = array();
		$res_en = $db->Execute($query);
		while (!$res_en->EOF) {
			$sno++;
			$PK_STUDENT_MASTER 	= $res_en->fields['PK_STUDENT_MASTER'];
			$SSN 				= $res_en->fields['SSN'];
			$SSN_DE  			= my_decrypt('',$SSN);
			$END_DATE 			= '';
			$res_cur_sts = $db->Execute("SELECT STUDENT_STATUS,PK_END_DATE, IF(LDA = '0000-00-00','', DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, IF(DROP_DATE = '0000-00-00','', DATE_FORMAT(DROP_DATE, '%m/%d/%Y' )) AS DROP_DATE, IF(GRADE_DATE = '0000-00-00','', DATE_FORMAT(GRADE_DATE, '%m/%d/%Y' )) AS GRADE_DATE, IF(DETERMINATION_DATE = '0000-00-00','', DATE_FORMAT(DETERMINATION_DATE, '%m/%d/%Y' )) AS DETERMINATION_DATE FROM S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_ACTIVE_ENROLLMENT = 1");
			if($res_cur_sts->fields['PK_END_DATE'] == 2)
				$END_DATE = $res_cur_sts->fields['LDA'];
			else if($res_cur_sts->fields['PK_END_DATE'] == 3)
				$END_DATE = $res_cur_sts->fields['DROP_DATE'];
			else if($res_cur_sts->fields['PK_END_DATE'] == 4)
				$END_DATE = $res_cur_sts->fields['GRADE_DATE'];
			else if($res_cur_sts->fields['PK_END_DATE'] == 5)
				$END_DATE = $res_cur_sts->fields['DETERMINATION_DATE'];
			
			$txt .= '<tr>
						<td width="12%" >'.$res_en->fields['NAME'].'</td>
						<td width="8%" >'.$SSN_DE.'</td>';
						
						
						foreach($PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER as $key => $PK_AR_LEDGER_CODE) {
							$res_ledger = $db->Execute($query2." AND S_STUDENT_LEDGER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_LEDGER.PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE) ");
							$flag  = 1;
							$flag1 = 1;
							$Disbursed 		= 0;
							$Refunded  		= 0;
							$sub_tot_stud 	= 0;
							while (!$res_ledger->EOF) {
								if($res_ledger->fields['AMOUNT'] > 0) {
									$Disbursed += $res_ledger->fields['AMOUNT'];
								}
								
								if($res_ledger->fields['AMOUNT'] < 0) {
									$Refunded += $res_ledger->fields['AMOUNT'];
								}
								
								//if($Disbursed > 0 && $flag == 1) {
								if($res_ledger->fields['AMOUNT'] > 0) {
									$flag = 0;
									$sub_tot_stud++;
								}
								
								//if($Refunded < 0 && $flag1 == 1) {
								if($res_ledger->fields['AMOUNT'] < 0 ) {
									$flag1 = 0;
									$sub_tot_stud++;
								}
								
								$res_ledger->MoveNext();
							}
							
							$Disbursed_TOT[$key] += $Disbursed;
							$Refunded_TOT[$key]  += $Refunded;
							$STUD_TOT[$key] 	 += $sub_tot_stud;
							
							$Disbursed = number_format_value_checker($Disbursed,2);
							$Refunded  = '('.number_format_value_checker(($Refunded * -1),2).')';
							
							$txt .= '<td width="7%" align="right" >$'.$Disbursed.'<br />$'.$Refunded.'</td>';
						}
						
						$txt .= '<td width="10%" >'.$res_cur_sts->fields['STUDENT_STATUS'].'<br />'.$END_DATE.'</td>
					</tr>';
		
			
			$res_en->MoveNext();
		}
		
		$txt 	.= '<tr>
						<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="center" >Total Students:<br />'.$res_en->RecordCount().'</td>
						<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Total Disbursed<br />Total Refunded<br />Tot Students Disb</td>';
						
						foreach($PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER as $key => $PK_AR_LEDGER_CODE) {
							$Disbursed = number_format_value_checker($Disbursed_TOT[$key],2);
							$Refunded  = '('.number_format_value_checker(($Refunded_TOT[$key] * -1),2).')';
							
							$txt .= '<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >$'.$Disbursed.'<br />$'.$Refunded.'<br />'.$STUD_TOT[$key].'</td>';
						}
					$txt .= '<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ></td>
						</tr>
				</table>';
				
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		
		$file_name = 'Title IV Recipients By Category.pdf';
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
		$file_name 		= 'Title IV Recipients By Category.xlsx';
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
		$index 	= 3;
		
		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		
		foreach($PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR as $PK_TITLE_IV_RECIPIENTS_CATEGORY) {
			$res_cat = $db->Execute("SELECT CODE FROM M_TITLE_IV_RECIPIENTS_CATEGORY WHERE PK_TITLE_IV_RECIPIENTS_CATEGORY='$PK_TITLE_IV_RECIPIENTS_CATEGORY'"); 
			
			$index++;
			
			$index2 = $index + 1;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cat->fields['CODE']);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$marge_cells = $cell[$index].$line.':'.$cell[$index2].$line;
			$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
			$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);
			
			$index++;
		}

		$line 	= 2;	
		$index 	= -1;

		$heading = array();
		$width   = array();
		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'SSN';
		$width[]   = 20;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		
		for($i = 1 ; $i <= count($PK_TITLE_IV_RECIPIENTS_CATEGORY_ARR) ; $i++){
			$heading[] = 'Disbursed';
			$width[]   = 20;
			
			$heading[] = 'Refunded';
			$width[]   = 20;
		}
		$heading[] = 'Current Status';
		$width[]   = 20;
		
		$heading[] = 'End Date';
		$width[]   = 20;
		
		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
		}	

		$objPHPExcel->getActiveSheet()->freezePane('A2');
		
		$Disbursed_TOT[] = array();
		$Refunded_TOT[]  = array();
		$STUD_TOT[] 	 = array();
		$res_en = $db->Execute($query);
		while (!$res_en->EOF) {
			$sno++;
			$PK_STUDENT_MASTER 	= $res_en->fields['PK_STUDENT_MASTER'];
			$SSN 				= $res_en->fields['SSN'];
			$SSN_DE  			= my_decrypt('',$SSN);
			$END_DATE 			= '';
			$res_cur_sts = $db->Execute("SELECT STUDENT_STATUS,PK_END_DATE, IF(LDA = '0000-00-00','', DATE_FORMAT(LDA, '%Y-%m-%d' )) AS LDA, IF(DROP_DATE = '0000-00-00','', DATE_FORMAT(DROP_DATE, '%Y-%m-%d' )) AS DROP_DATE, IF(GRADE_DATE = '0000-00-00','', DATE_FORMAT(GRADE_DATE, '%Y-%m-%d' )) AS GRADE_DATE, IF(DETERMINATION_DATE = '0000-00-00','', DATE_FORMAT(DETERMINATION_DATE, '%Y-%m-%d' )) AS DETERMINATION_DATE FROM S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_ACTIVE_ENROLLMENT = 1");
			if($res_cur_sts->fields['PK_END_DATE'] == 2)
				$END_DATE = $res_cur_sts->fields['LDA'];
			else if($res_cur_sts->fields['PK_END_DATE'] == 3)
				$END_DATE = $res_cur_sts->fields['DROP_DATE'];
			else if($res_cur_sts->fields['PK_END_DATE'] == 4)
				$END_DATE = $res_cur_sts->fields['GRADE_DATE'];
			else if($res_cur_sts->fields['PK_END_DATE'] == 5)
				$END_DATE = $res_cur_sts->fields['DETERMINATION_DATE'];
				
			$line++;
			$index = -1;
		
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SSN_DE);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['STUDENT_ID']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['CAMPUS_CODE']);

			foreach($PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER as $PK_AR_LEDGER_CODE) {
				$res_ledger = $db->Execute($query2." AND S_STUDENT_LEDGER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_LEDGER.PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE) ");
				
				$Disbursed 		= 0;
				$Refunded  		= 0;
				$sub_tot_stud 	= 0;
				while (!$res_ledger->EOF) {
					if($res_ledger->fields['AMOUNT'] > 0) {
						$Disbursed += $res_ledger->fields['AMOUNT'];
					}
					
					if($res_ledger->fields['AMOUNT'] < 0) {
						$Refunded += $res_ledger->fields['AMOUNT'];
					}
	
					$res_ledger->MoveNext();
				}
	
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($Disbursed);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($Refunded);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cur_sts->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($END_DATE);

			$res_en->MoveNext();
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
	<title><?=MNU_TITLE_IV_RECIPIENTS_DETAIL ?> | <?=$title?></title>
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
							<?=MNU_TITLE_IV_RECIPIENTS_BY_CATEGORY ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<!-- Ticket # 1319  -->
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
										<!-- Ticket # 1319  -->
										
										<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE ?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										<div class="col-md-2" >
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
		
		/* Ticket # 1319  */
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		/* Ticket # 1319  */
	});
	</script>
</body>

</html>