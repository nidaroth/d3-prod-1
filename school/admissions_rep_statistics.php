<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('REPORT_ADMISSION') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$cond1 = "";
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
	
	$query = "SELECT * FROM (SELECT 0 as PK_REPRESENTATIVE, ' Admissions Rep Not Set' as EMP_NAME
	UNION 
	SELECT PK_REPRESENTATIVE, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) AS EMP_NAME  
	FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, S_EMPLOYEE_MASTER  
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = PK_REPRESENTATIVE AND 
	S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ) AS TEMP
	GROUP BY PK_REPRESENTATIVE ORDER BY EMP_NAME ASC ";
	
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
	
	$PK_REPRESENTATIVES = '';
	$res_rep = $db->Execute($query);
	while (!$res_rep->EOF) {
		if($PK_REPRESENTATIVES != '')
			$PK_REPRESENTATIVES .= ',';
			
		$PK_REPRESENTATIVES .= $res_rep->fields['PK_REPRESENTATIVE'];
		
		$res_rep->MoveNext();
	}
	
	$res_tot_1 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
							S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS   
							WHERE 
							S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
							S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
							S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
							S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  AND PK_CAMPUS IN ($campus_id) AND 
							PK_REPRESENTATIVE IN ($PK_REPRESENTATIVES) $cond1 GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ");
						
	$res_tot_2 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
						S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS      
						WHERE 
						S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
						S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
						S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
						S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  AND PK_CAMPUS IN ($campus_id) AND 
						PK_REPRESENTATIVE IN ($PK_REPRESENTATIVES) $cond2 GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ");
						
	$res_tot_3 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
						S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS     
						WHERE 
						S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
						S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
						S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
						S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  AND PK_CAMPUS IN ($campus_id) AND 
						PK_REPRESENTATIVE IN ($PK_REPRESENTATIVES) $cond3 GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ");
	
	if($_POST['FORMAT'] == 1){
		/////////////////////////////////////////////////////////////////
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
							<td width="40%" valign="top" style="font-size:18px" >'.$SCHOOL_NAME.'</td>
							<td width="40%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Admissions Rep Statistics</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Count and Percentage of Leads by Rep</td>
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
			'margin_top' => 30,
			'margin_bottom' => 15,
			'margin_header' => 3,
			'margin_footer' => 10,
			'default_font_size' => 8
		]);
		$mpdf->autoPageBreak = true;

		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);

		$total 	= 0;
		$txt 	= '';
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="40%" ></td>
							<td width="20%" colspan="2" style="border-left:1px solid #000;border-top:1px solid #000;" align="center" >
								<b>First Date Range<br />'.$str1.'</b>
							</td>
							<td width="20%" colspan="2" style="border-left:1px solid #000;border-top:1px solid #000;" align="center" >
								<b>Second Date Range<br />'.$str2.'</b>
							</td>
							<td width="20%" colspan="2" align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="center" >
								<b>Third Date Range<br />'.$str3.'</b>
							</td>
						</tr>
						
						<tr>
							<td width="40%" style="border-left:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;" >Employee</td>
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
		
		$res_rep = $db->Execute($query);
		while (!$res_rep->EOF) {
			$PK_REPRESENTATIVE 	= $res_rep->fields['PK_REPRESENTATIVE'];
			
			$res1 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
								S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS    
								WHERE 
								S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
								S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
								S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  AND PK_CAMPUS IN ($campus_id) AND 
								PK_REPRESENTATIVE = '$PK_REPRESENTATIVE' $cond1 GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ");
								
			$res2 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
								S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS    
								WHERE 
								S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
								S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
								S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  AND PK_CAMPUS IN ($campus_id) AND 
								PK_REPRESENTATIVE = '$PK_REPRESENTATIVE' $cond2 GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ");
								
			$res3 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
								S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS    
								WHERE 
								S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
								S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
								S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  AND PK_CAMPUS IN ($campus_id) AND 
								PK_REPRESENTATIVE = '$PK_REPRESENTATIVE' $cond3 GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ");
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
						<td width="40%" style="border-left:1px solid #000;">'.trim($res_rep->fields['EMP_NAME']).'</td>
						
						<td width="10%" align="right" style="border-left:1px solid #000;">'.number_format_value_checker($res1->RecordCount()).'</td>
						<td width="10%" align="right" >'.number_format_value_checker($per1,2).' %</td>
						
						<td width="10%" align="right" style="border-left:1px solid #000;">'.number_format_value_checker($res2->RecordCount()).'</td>
						<td width="10%" align="right" >'.number_format_value_checker($per2,2).' %</td>
						
						<td width="10%" align="right" style="border-left:1px solid #000;" >'.number_format_value_checker($res3->RecordCount()).'</td>
						<td width="10%" align="right" style="border-right:1px solid #000;">'.number_format_value_checker($per3,2).' %</td>
					</tr>';
		
			$res_rep->MoveNext();
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
			
		$mpdf->WriteHTML($txt);
		$file_name = 'Admissions Rep Statistics.pdf';
		$mpdf->Output($file_name, 'D');

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
		$file_name 		= 'Admissions Rep Statistics.xlsx';
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
		
		$line 	= 1;
		$index 	= 0;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Campus(es): ".$campus_name);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->applyFromArray($style);

		$line 	= 1;
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
		$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);

		$line++;
		$index 	= -1;
		$heading[] = 'Employee';
		$width[]   = 30;
		$heading[] = 'Count';
		$width[]   = 15;
		$heading[] = 'Percentage';
		$width[]   = 15;
		$heading[] = 'Count';
		$width[]   = 15;
		$heading[] = 'Percentage';
		$width[]   = 15;
		$heading[] = 'Count';
		$width[]   = 15;
		$heading[] = 'Percentage';
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

		$objPHPExcel->getActiveSheet()->freezePane('A1');
		
		$tot_1 = $res_tot_1->RecordCount();
		$tot_2 = $res_tot_2->RecordCount();
		$tot_3 = $res_tot_3->RecordCount();
		
		$res_rep = $db->Execute($query);
		while (!$res_rep->EOF) {
			$PK_REPRESENTATIVE 	= $res_rep->fields['PK_REPRESENTATIVE'];
			
			$res1 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
								S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS    
								WHERE 
								S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
								S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
								S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  AND PK_CAMPUS IN ($campus_id) AND 
								PK_REPRESENTATIVE = '$PK_REPRESENTATIVE' $cond1 GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ");
								
			$res2 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
								S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS    
								WHERE 
								S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
								S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
								S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  AND PK_CAMPUS IN ($campus_id) AND 
								PK_REPRESENTATIVE = '$PK_REPRESENTATIVE' $cond2 GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ");
								
			$res3 = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM 
								S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS    
								WHERE 
								S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
								S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
								S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  AND PK_CAMPUS IN ($campus_id) AND 
								PK_REPRESENTATIVE = '$PK_REPRESENTATIVE' $cond3 GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ");
			$per1 = 0;
			$per2 = 0;
			$per3 = 0;
			
			if($tot_1 > 0)
				$per1 = $res1->RecordCount() / $tot_1;
				
			if($tot_2 > 0)
				$per2 = $res2->RecordCount() / $tot_2;
				
			if($tot_3 > 0)
				$per3 = $res3->RecordCount() / $tot_3;
				
		
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res_rep->fields['EMP_NAME']));
				
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res1->RecordCount());
				
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($per1);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res2->RecordCount());
				
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($per2);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res3->RecordCount());
				
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($per3);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
			
			$res_rep->MoveNext();
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
	<title><?=MNU_ADMISSIONS_REP_STATISTICS?> | <?=$title?></title>
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
							<?=MNU_ADMISSIONS_REP_STATISTICS?>
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
									</div>
									
									<br />
									<div class="row">
										<div class="col-md-12">
											<h4 class="text-themecolor"><?=LEAD_ENTRY_DATE_RANGES?></h4>
										</div>
									</div>
									
									<br />
									<div class="row">
										<div class="col-md-2" style="max-width: 13.667%;flex: 0 0 13.667%;" >
											<?=FIRST_RANGE_START?>
											<input type="text" class="form-control date required-entry" id="START_DATE_1" name="START_DATE_1" value="" >
										</div>
										<div class="col-md-2" style="max-width: 13.667%;flex: 0 0 13.667%;" >
											<?=FIRST_RANGE_END?>
											<input type="text" class="form-control date required-entry" id="END_DATE_1" name="END_DATE_1" value="" >
										</div>
										
										<div class="col-md-2" style="max-width: 13.667%;flex: 0 0 13.667%;" >
											<?=SECOND_RANGE_START?>
											<input type="text" class="form-control date required-entry" id="START_DATE_2" name="START_DATE_2" value="" >
										</div>
										<div class="col-md-2" style="max-width: 13.667%;flex: 0 0 13.667%;" >
											<?=SECOND_RANGE_END?>
											<input type="text" class="form-control date required-entry" id="END_DATE_2" name="END_DATE_2" value="" >
										</div>
										
										<div class="col-md-2" style="max-width: 13.667%;flex: 0 0 13.667%;" >
											<?=THIRD_RANGE_START?>
											<input type="text" class="form-control date required-entry" id="START_DATE_3" name="START_DATE_3" value="" >
										</div>
										<div class="col-md-2" style="max-width: 13.667%;flex: 0 0 13.667%;" >
											<?=THIRD_RANGE_END?>
											<input type="text" class="form-control date required-entry" id="END_DATE_3" name="END_DATE_3" value="" >
										</div>
										
										<div class="col-md-2" style="max-width: 13.667%;flex: 0 0 13.667%;" >
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
		
		search();
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