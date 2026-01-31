<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/lead_source.php");
require_once("check_access.php");

if(check_access('REPORT_ADMISSION') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$cond_lead_cource = "";
	if(!empty($_POST['PK_LEAD_SOURCE']))
		$cond_lead_cource .= " AND PK_LEAD_SOURCE IN (".implode(",",$_POST['PK_LEAD_SOURCE']).") ";
	
	$cond1 	= "";
	if($_POST['START_DATE_1'] != '' && $_POST['END_DATE_1'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE_1']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE_1']));
		$cond1 .= " AND ENTRY_DATE BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE_1'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE_1']));
		$cond1 .= " AND ENTRY_DATE >= '$ST' ";
	} else if($_POST['END_DATE_1'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE_1']));
		$cond1 .= " AND ENTRY_DATE <= '$ET' ";
	}
	
	$cond2 = "";
	if($_POST['START_DATE_2'] != '' && $_POST['END_DATE_2'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE_2']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE_2']));
		$cond2 .= " AND ENTRY_DATE BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE_2'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE_2']));
		$cond2 .= " AND ENTRY_DATE >= '$ST' ";
	} else if($_POST['END_DATE_2'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE_2']));
		$cond2 .= " AND ENTRY_DATE <= '$ET' ";
	}
	
	$cond3 = "";
	if($_POST['START_DATE_3'] != '' && $_POST['END_DATE_3'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE_3']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE_3']));
		$cond3 .= " AND ENTRY_DATE BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE_3'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE_3']));
		$cond3 .= " AND ENTRY_DATE >= '$ST' ";
	} else if($_POST['END_DATE_3'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE_3']));
		$cond3 .= " AND ENTRY_DATE <= '$ET' ";
	}
	
	$str1 = "";
	if($_POST['START_DATE_1'] != '' && $_POST['END_DATE_1'] != '') {
		$str1 = $_POST['START_DATE_1']." - ".$_POST['END_DATE_1'];
	} else if($_POST['START_DATE_1'] != ''){
		$str1 = 'From: '.$_POST['START_DATE_1'];
	} else if($_POST['END_DATE_1'] != ''){
		$str1 = 'To: '.$_POST['END_DATE_1'];
	}
	
	$str2 = "";
	if($_POST['START_DATE_2'] != '' && $_POST['END_DATE_2'] != '') {
		$str2 = $_POST['START_DATE_2']." - ".$_POST['END_DATE_2'];
	} else if($_POST['START_DATE_2'] != ''){
		$str2 = 'From: '.$_POST['START_DATE_2'];
	} else if($_POST['END_DATE_2'] != ''){
		$str2 = 'To: '.$_POST['END_DATE_2'];
	}
	
	$str3 = "";
	if($_POST['START_DATE_3'] != '' && $_POST['END_DATE_3'] != '') {
		$str3 = $_POST['START_DATE_3']." - ".$_POST['END_DATE_3'];
	} else if($_POST['START_DATE_3'] != ''){
		$str3 = 'From: '.$_POST['START_DATE_3'];
	} else if($_POST['END_DATE_3'] != ''){
		$str3 = 'To: '.$_POST['END_DATE_3'];
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
	
	if($_POST['NO_LEAD_SOURCE'] != count($_POST['PK_LEAD_SOURCE']))
		$query = "select PK_LEAD_SOURCE,CONCAT(LEAD_SOURCE,' - ',DESCRIPTION) as LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 $cond_lead_cource order by LEAD_SOURCE ASC ";
	else
		$query = "SELECT * FROM(SELECT '0' as PK_LEAD_SOURCE, ' Lead Source Not Set' as LEAD_SOURCE UNION select PK_LEAD_SOURCE,CONCAT(LEAD_SOURCE,' - ',DESCRIPTION) as LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ) AS TEMP order by LEAD_SOURCE ASC ";
		
	$PK_LEAD_SOURCES = '';
	$res_ls = $db->Execute($query);
	while (!$res_ls->EOF) {
		if($PK_LEAD_SOURCES != '')
			$PK_LEAD_SOURCES .= ',';
			
		$PK_LEAD_SOURCES .= $res_ls->fields['PK_LEAD_SOURCE'];
		
		$res_ls->MoveNext();
	}
	
	$res_tot_1 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
							S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS   
							WHERE 
							S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
							S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
							S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($campus_id) ) AND 
							S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_LEAD_SOURCE IN ($PK_LEAD_SOURCES) $cond1 ");
							
	$res_tot_2 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
						S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS   
						WHERE 
						S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
						S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
						S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($campus_id) ) AND 
						S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_LEAD_SOURCE IN ($PK_LEAD_SOURCES) $cond2 ");
						
	$res_tot_3 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
						S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS   
						WHERE 
						S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
						S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
						S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($campus_id) ) AND 
						S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_LEAD_SOURCE IN ($PK_LEAD_SOURCES) $cond3 ");
	
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
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(132);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Lead Source Statistics", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 202, 13, $style);

				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, "Count and Percentage of Leads by Source", 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(20);
				$this->SetX(98);
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
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();

		$total 	= 0;
		$txt 	= '';
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="40%" ></td>
							<td width="20%" style="border-left:1px solid #000;border-top:1px solid #000;" align="center" >
								<b>First Date Range<br />'.$str1.'</b>
							</td>
							<td width="20%" style="border-left:1px solid #000;border-top:1px solid #000;" align="center" >
								<b>Second Date Range<br />'.$str2.'</b>
							</td>
							<td width="20%" align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="center" >
								<b>Third Date Range<br />'.$str3.'</b>
							</td>
						</tr>
						
						<tr>
							<td width="40%" style="border-left:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;" >Lead Source</td>
							<td width="10%" style="border-left:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Count</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Percentage</td>
							<td width="10%" style="border-left:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Count</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Percentage</td>
							<td width="10%" style="border-left:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Count</td>
							<td width="10%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:1px solid #000;" align="right" >Percentage</td>
						</tr>
					</thead>';

		//echo $query;exit;

		$tot_1 = $res_tot_1->RecordCount();
		$tot_2 = $res_tot_2->RecordCount();
		$tot_3 = $res_tot_3->RecordCount();
		
		$res_ls = $db->Execute($query);
		while (!$res_ls->EOF) {
			$PK_LEAD_SOURCE 	= $res_ls->fields['PK_LEAD_SOURCE'];
			
			$res1 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
								S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS   
								WHERE 
								S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
								S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($campus_id) ) AND 
								S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
								PK_LEAD_SOURCE = '$PK_LEAD_SOURCE' $cond1 ");
								
			$res2 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
								S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS   
								WHERE 
								S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
								S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($campus_id) ) AND 
								S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
								PK_LEAD_SOURCE = '$PK_LEAD_SOURCE' $cond2 ");
								
			$res3 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
								S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS   
								WHERE 
								S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
								S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($campus_id) ) AND 
								S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
								PK_LEAD_SOURCE = '$PK_LEAD_SOURCE' $cond3 ");
			$per1 = 0;
			$per2 = 0;
			$per3 = 0;
			
			if($tot_1 > 0)
				$per1 = $res1->RecordCount() / $tot_1 * 100;
				
			if($tot_2 > 0)
				$per2 = $res2->RecordCount() / $tot_2 * 100;
				
			if($tot_3 > 0)
				$per3 = $res3->RecordCount() / $tot_3 * 100;
			
			$txt 	.= '<tr>
						<td width="40%" style="border-left:1px solid #000;">'.trim($res_ls->fields['LEAD_SOURCE']).'</td>
						
						<td width="10%" align="right" style="border-left:1px solid #000;">'.number_format_value_checker($res1->RecordCount()).'</td>
						<td width="10%" align="right" >'.number_format_value_checker($per1,2).' %</td>
						
						<td width="10%" align="right" style="border-left:1px solid #000;">'.number_format_value_checker($res2->RecordCount()).'</td>
						<td width="10%" align="right" >'.number_format_value_checker($per2,2).' %</td>
						
						<td width="10%" align="right" style="border-left:1px solid #000;" >'.number_format_value_checker($res3->RecordCount()).'</td>
						<td width="10%" align="right" style="border-right:1px solid #000;">'.number_format_value_checker($per3,2).' %</td>
					</tr>';
		
			$res_ls->MoveNext();
		}
		$txt 	.= '<tr>
						<td width="40%" style="border-top:1px solid #000;"><b>Totals</b></td>
						
						<td width="10%" align="right" style="border-top:1px solid #000;"><b>'.number_format_value_checker($tot_1).'</b></td>
						<td width="10%" align="right" style="border-top:1px solid #000;" ></td>
						
						<td width="10%" align="right" style="border-top:1px solid #000;" ><b>'.number_format_value_checker($tot_2).'</b></td>
						<td width="10%" align="right" style="border-top:1px solid #000;" ></td>
						
						<td width="10%" align="right" style="border-top:1px solid #000;" ><b>'.number_format_value_checker($tot_3).'</b></td>
						<td width="10%" align="right" style="border-top:1px solid #000;" ></td>
					</tr>
				</table>';

			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Lead Source Statistics'.'.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');
		*/	
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
		$file_name 		= 'Lead Source Statistics.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		/*$line 	= 1;
		$index 	= 1;
		$index2 = $index + 1;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('First Date Range');
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$marge_cells = $cell[$index].$line.':'.$cell[$index2].$line;
		$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
		$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);
		
		$index 	= 3;
		$index2 = $index + 1;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Second Date Range');
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$marge_cells = $cell[$index].$line.':'.$cell[$index2].$line;
		$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
		$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);
		
		$index 	= 5;
		$index2 = $index + 1;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Third Date Range');
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$marge_cells = $cell[$index].$line.':'.$cell[$index2].$line;
		$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
		$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);

		$line++;	
		$index 	= 1;
		$index2 = $index + 1;
		
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str1);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$marge_cells = $cell[$index].$line.':'.$cell[$index2].$line;
		$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
		$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);
		
		$index 	= 3;
		$index2 = $index + 1;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str2);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$marge_cells = $cell[$index].$line.':'.$cell[$index2].$line;
		$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
		$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);
		
		$index 	= 5;
		$index2 = $index + 1;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str3);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$marge_cells = $cell[$index].$line.':'.$cell[$index2].$line;
		$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
		$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);*/

		$line = 1;
		$index 	= -1;
		$heading[] = 'Campus';
		$width[]   = 30;
		$heading[] = 'Student Name';
		$width[]   = 15;
		$heading[] = 'Student ID';
		$width[]   = 15;
		$heading[] = 'Lead Source';
		$width[]   = 15;
		$heading[] = 'Lead Entry Date';
		$width[]   = 15;
		$heading[] = 'Contact Source';
		$width[]   = 15;
		$heading[] = 'Status';
		$width[]   = 15;
		$heading[] = 'Status Date';
		$width[]   = 15;
		$heading[] = 'Admission Rep';
		$width[]   = 15;
		$heading[] = 'Program';
		$width[]   = 15;
		$heading[] = 'First Term Date';
		$width[]   = 15;
		$heading[] = 'Session';
		$width[]   = 15;
		$heading[] = 'Contract Signed Date';
		$width[]   = 15;
		$heading[] = 'Contract End Date';
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

		if($_POST['START_DATE_1'] != '' && $_POST['END_DATE_1'] != '') {
			$ST = date("Y-m-d",strtotime($_POST['START_DATE_1']));
			$ET = date("Y-m-d",strtotime($_POST['END_DATE_1']));
			if($date_cond != '')
				$date_cond .= ' OR ';
			$date_cond .= " ENTRY_DATE BETWEEN '$ST' AND '$ET' ";
		}

		if($_POST['START_DATE_2'] != '' && $_POST['END_DATE_2'] != '') {
			$ST = date("Y-m-d",strtotime($_POST['START_DATE_2']));
			$ET = date("Y-m-d",strtotime($_POST['END_DATE_2']));
			if($date_cond != '')
				$date_cond .= ' OR ';
			$date_cond .= " ENTRY_DATE BETWEEN '$ST' AND '$ET' ";
		}

		if($_POST['START_DATE_3'] != '' && $_POST['END_DATE_3'] != '') {
			$ST = date("Y-m-d",strtotime($_POST['START_DATE_3']));
			$ET = date("Y-m-d",strtotime($_POST['END_DATE_3']));
			if($date_cond != '')
				$date_cond .= ' OR ';
			$date_cond .= " ENTRY_DATE BETWEEN '$ST' AND '$ET' ";
		}
		
		if($date_cond != '')
			$date_cond = " AND ($date_cond) ";
		
		$PK_LEAD_SOURCE = '';
		$res_ls = $db->Execute($query);
		while (!$res_ls->EOF) {
			
			if($PK_LEAD_SOURCE != '')
				$PK_LEAD_SOURCE .= ',';
				
			$PK_LEAD_SOURCE .= $res_ls->fields['PK_LEAD_SOURCE'];
			
			$res_ls->MoveNext();
		}
		
		/* Ticket # 1762 */
		$res1 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, CAMPUS_CODE, STUDENT_ID, LEAD_SOURCE, IF(S_STUDENT_ENROLLMENT.ENTRY_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ENTRY_DATE,'%Y-%m-%d' )) AS ENTRY_DATE, LEAD_CONTACT_SOURCE, STUDENT_STATUS, IF(S_STUDENT_ENROLLMENT.STATUS_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.STATUS_DATE,'%Y-%m-%d' )) AS STATUS_DATE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME, M_CAMPUS_PROGRAM.CODE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE, M_SESSION.SESSION AS SESSION, IF(S_STUDENT_ENROLLMENT.CONTRACT_SIGNED_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.CONTRACT_SIGNED_DATE,'%Y-%m-%d' )) AS CONTRACT_SIGNED_DATE, IF(S_STUDENT_ENROLLMENT.CONTRACT_END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.CONTRACT_END_DATE,'%Y-%m-%d' )) AS CONTRACT_END_DATE 
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS 
		, S_STUDENT_ENROLLMENT 
		LEFT JOIN M_LEAD_CONTACT_SOURCE ON M_LEAD_CONTACT_SOURCE.PK_LEAD_CONTACT_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_CONTACT_SOURCE
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 
		LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE  
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		LEFT JOIN M_FIRST_TERM ON M_FIRST_TERM.PK_FIRST_TERM = S_STUDENT_ENROLLMENT.FIRST_TERM 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($campus_id) ) AND 
		S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE IN ($PK_LEAD_SOURCE) $date_cond GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC, IS_ACTIVE_ENROLLMENT DESC");
		/* Ticket # 1762 */
		
		while (!$res1->EOF){
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['CAMPUS_CODE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['STU_NAME']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['STUDENT_ID']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['LEAD_SOURCE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['ENTRY_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['LEAD_CONTACT_SOURCE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['STUDENT_STATUS']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['STATUS_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['EMP_NAME']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['CODE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['BEGIN_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['SESSION']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['CONTRACT_SIGNED_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res1->fields['CONTRACT_END_DATE']));

			$res1->MoveNext();
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
	<title><?=MNU_LEAD_SOURCE_STATISTICS?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_LEAD_SOURCE{position: absolute;top: 57px;width: 140px}
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
							<?=MNU_LEAD_SOURCE_STATISTICS?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels " method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-2">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" style="max-width: 18.667%;flex: 0 0 18.667%;" >
											<?=LEAD_SOURCE?>
											<select id="PK_LEAD_SOURCE" name="PK_LEAD_SOURCE[]" multiple class="form-control required-entry">
												<? $res_type = $db->Execute("select PK_LEAD_SOURCE,LEAD_SOURCE,DESCRIPTION from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by LEAD_SOURCE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_LEAD_SOURCE']?>" ><?=$res_type->fields['LEAD_SOURCE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<input type="hidden" name="NO_LEAD_SOURCE" value="<?=$res_type->RecordCount()?>" >
										</div>
									</div>
									
									<br />
									<div class="row">
										<div class="col-md-12">
											<h4 class="text-themecolor"><?=LEAD_ENTRY_DATE_RANGES?></h4>
										</div>
									</div>
									
									<br />
									<div class="row">	
										<div class="col-md-2" style="max-width: 9.667%;flex: 0 0 9.667%;" >
											<?=FIRST_RANGE_START?>
											<input type="text" class="form-control date required-entry" id="START_DATE_1" name="START_DATE_1" value="" >
										</div>
										<div class="col-md-2" style="max-width: 9.667%;flex: 0 0 9.667%;" >
											<?=FIRST_RANGE_END?>
											<input type="text" class="form-control date required-entry" id="END_DATE_1" name="END_DATE_1" value="" >
										</div>
										
										<div class="col-md-2" style="max-width: 11.667%;flex: 0 0 11.667%;" >
											<?=SECOND_RANGE_START?>
											<input type="text" class="form-control date required-entry" id="START_DATE_2" name="START_DATE_2" value="" >
										</div>
										<div class="col-md-2" style="max-width: 11.667%;flex: 0 0 11.667%;" >
											<?=SECOND_RANGE_END?>
											<input type="text" class="form-control date required-entry" id="END_DATE_2" name="END_DATE_2" value="" >
										</div>
										
										<div class="col-md-2" style="max-width: 10.5%;flex: 0 0 10.5%;" >
											<?=THIRD_RANGE_START?>
											<input type="text" class="form-control date required-entry" id="START_DATE_3" name="START_DATE_3" value="" >
										</div>
										<div class="col-md-2" style="max-width: 9.667%;flex: 0 0 9.667%;" >
											<?=THIRD_RANGE_END?>
											<input type="text" class="form-control date required-entry" id="END_DATE_3" name="END_DATE_3" value="" >
										</div>
										
										<div class="col-md-2" style="max-width: 10.667%;flex: 0 0 10.667%;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<!-- New -->
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
		$('#PK_LEAD_SOURCE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=LEAD_SOURCE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=LEAD_SOURCE?> selected'
		});
	});
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
	});
	</script>
	
</body>

</html>