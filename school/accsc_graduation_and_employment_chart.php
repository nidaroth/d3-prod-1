<? ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/accsc.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT ACCSC FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ACCSC'] == 0 || $res->fields['ACCSC'] == '') {
	header("location:../index");
	exit;
}

if(!empty($_POST)){

	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	if($_POST['REPORT_OPTION'] == 1)
		$REPORT_OPTION = "Program";
	else
		$REPORT_OPTION = "Program Group";
		
	if($_POST['FORMAT'] == 1)
		$_POST['REPORT_TYPE'] = 1;
	else if($_POST['FORMAT'] == 2)
		$_POST['REPORT_TYPE'] = 2;
	
	$ST = '';
	$ET = '';
	if($_POST['START_DATE'] != '')
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		
	if($_POST['END_DATE'] != '')
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
	
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
	
	if($_POST['GROUP_BY_PROGRAM_CODE'] == 1)
		$label = "Program Group";
	else
		$label = "Program";
		
	if($_POST['GROUP_BY_PROGRAM_CODE'] == 1)
		$GROUP_BY_PROGRAM_CODE = "TRUE";
	else
		$GROUP_BY_PROGRAM_CODE = "FALSE";
	
	if($_POST['FORMAT'] == 1) {
	// 	require_once '../global/mpdf/vendor/autoload.php';
		
	// 	$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	// 	$SCHOOL_NAME 	= $res->fields['SCHOOL_NAME'];
	// 	$PDF_LOGO 	 	= $res->fields['PDF_LOGO'];
	// 	$SCHOOL_ADDRESS = trim($res->fields['ADDRESS']." ".$res->fields['ADDRESS_1'])."<br />".$res->fields['CITY']." ".$res->fields['STATE_CODE']." ".$res->fields['ZIP']."<br />Phone: ".$res->fields['PHONE'];
		
	// 	$logo = "";
	// 	if($PDF_LOGO != '')
	// 		$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';

	// 	$term_starts = "";
	// 	if($_POST['ENROLLMENT_START_MONTH'] == 1)
	// 		$term_starts = " Jan ".$_POST['ENROLLMENT_START_YEAR']." to Dec ".$_POST['ENROLLMENT_START_YEAR'];
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 2)
	// 		$term_starts = " Feb ".$_POST['ENROLLMENT_START_YEAR']." to Jan ".($_POST['ENROLLMENT_START_YEAR'] + 1);
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 3)
	// 		$term_starts = " Mar ".$_POST['ENROLLMENT_START_YEAR']." to Feb ".($_POST['ENROLLMENT_START_YEAR'] + 1);
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 4)
	// 		$term_starts = " Apr ".$_POST['ENROLLMENT_START_YEAR']." to Mar ".($_POST['ENROLLMENT_START_YEAR'] + 1);
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 5)
	// 		$term_starts = " May ".$_POST['ENROLLMENT_START_YEAR']." to Apr ".($_POST['ENROLLMENT_START_YEAR'] + 1);
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 6)
	// 		$term_starts = " Jun ".$_POST['ENROLLMENT_START_YEAR']." to May ".($_POST['ENROLLMENT_START_YEAR'] + 1);
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 7)
	// 		$term_starts = " Jul ".$_POST['ENROLLMENT_START_YEAR']." to Jun ".($_POST['ENROLLMENT_START_YEAR'] + 1);
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 8)
	// 		$term_starts = " Aug ".$_POST['ENROLLMENT_START_YEAR']." to Jul ".($_POST['ENROLLMENT_START_YEAR'] + 1);
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 9)
	// 		$term_starts = " Sep ".$_POST['ENROLLMENT_START_YEAR']." to Aug ".($_POST['ENROLLMENT_START_YEAR'] + 1);
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 10)
	// 		$term_starts = " Oct ".$_POST['ENROLLMENT_START_YEAR']." to Sep ".($_POST['ENROLLMENT_START_YEAR'] + 1);
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 11)
	// 		$term_starts = " Nov ".$_POST['ENROLLMENT_START_YEAR']." to Oct ".($_POST['ENROLLMENT_START_YEAR'] + 1);
	// 	else if($_POST['ENROLLMENT_START_MONTH'] == 12)
	// 		$term_starts = " Dec ".$_POST['ENROLLMENT_START_YEAR']." to Nov ".($_POST['ENROLLMENT_START_YEAR'] + 1);
			

	// 	$timezone = $_SESSION['PK_TIMEZONE'];
	// 	if($timezone == '' || $timezone == 0) {
	// 		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	// 		$timezone = $res->fields['PK_TIMEZONE'];
	// 		if($timezone == '' || $timezone == 0)
	// 			$timezone = 4;
	// 	}
		
	// 	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	// 	$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
					
	// 	$footer = '<table width="100%" >
	// 					<tr>
	// 						<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
	// 						<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>COMP40001</i></td>
	// 						<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
	// 					</tr>
	// 				</table>';
		
	// 	$mpdf = new \Mpdf\Mpdf([
	// 		'margin_left' => 7,
	// 		'margin_right' => 5,
	// 		'margin_top' => 35,
	// 		'margin_bottom' => 15,
	// 		'margin_header' => 3,
	// 		'margin_footer' => 10,
	// 		'default_font_size' => 9,
	// 		'format' => [210, 296],
	// 		'orientation' => 'L'
	// 	]);
	// 	$mpdf->autoPageBreak = true;
		
	// 	$mpdf->SetHTMLFooter($footer);
		
	// 	$k 		= 1;
	// 	$txt 	= "";
	// 	echo "CALL COMP40001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['ENROLLMENT_START_YEAR']."','".$_POST['ENROLLMENT_START_MONTH']."', 'Chart', '".$REPORT_OPTION."')"; exit;
	// 	$res = $db->Execute("CALL COMP40001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['ENROLLMENT_START_YEAR']."','".$_POST['ENROLLMENT_START_MONTH']."', 'Chart', '".$REPORT_OPTION."')");
	// 	while (!$res->EOF) {

	// 		if($k == 1 || $PROGRAM != $res->fields['PROGRAM']) {
	// 			$k 	= 1;
	// 			$PROGRAM 		= $res->fields['PROGRAM'];
	// 			$PROGRAM_LENGTH = $res->fields['MONTHS'];
	// 			$TERM_STARTS	= $res->fields['TERM_STARTS'];
				
	// 			$header = '<table width="100%" >
	// 						<tr>
	// 							<td width="20%" valign="top" >'.$logo.'</td>
	// 							<td width="30%" valign="top" ><span style="font-size:20px">'.$SCHOOL_NAME.'</span><br />'.$SCHOOL_ADDRESS.'</td>
	// 							<td width="50%" valign="top" >
	// 								<table width="100%" >
	// 									<tr>
	// 										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Graduation & Employment Chart</b></td>
	// 									</tr>
	// 									<tr>
	// 										<td width="100%" align="right" style="font-size:13px;" ><i>Term Starts From: '.$term_starts.'</i></td>
	// 									</tr>
	// 									<tr>
	// 										<td width="100%" align="right" style="font-size:13px;" ><i>Program: '.$PROGRAM.'</i></td>
	// 									</tr>
	// 									<tr>
	// 										<td width="100%" align="right" style="font-size:13px;" ><i>Program Length in Months: '.$PROGRAM_LENGTH.'</i></td>
	// 									</tr>
	// 									<tr>
	// 										<td width="100%" align="right" style="font-size:13px;" ><i>No. of Term Start in Period: '.$TERM_STARTS.'</i></td>
	// 									</tr>
	// 								</table>
	// 							</td>
	// 						</tr>
	// 					</table>';
	// 		}

	// 		$tot 		= 0;
	// 		$percentage = 0;
			
	// 		$tot_2	 = 0;
	// 		$tot_5	 = 0;
	// 		$tot_7 	= 0;
	// 		$tot_9 	= 0;
	// 		$tot_13 = 0;
			
	// 		if($k == 2)
	// 			$tot_2 = $res->fields['M01'] + $res->fields['M02'] + $res->fields['M03'] + $res->fields['M04'] + $res->fields['M05'] + $res->fields['M06'] + $res->fields['M07'] + $res->fields['M08'] + $res->fields['M09'] + $res->fields['M010'] + $res->fields['M11'] + $res->fields['M12'];
	// 		else if($k == 5)
	// 			$tot_5 = $res->fields['M01'] + $res->fields['M02'] + $res->fields['M03'] + $res->fields['M04'] + $res->fields['M05'] + $res->fields['M06'] + $res->fields['M07'] + $res->fields['M08'] + $res->fields['M09'] + $res->fields['M010'] + $res->fields['M11'] + $res->fields['M12'];
	// 		else if($k == 7)
	// 			$tot_7 = $res->fields['M01'] + $res->fields['M02'] + $res->fields['M03'] + $res->fields['M04'] + $res->fields['M05'] + $res->fields['M06'] + $res->fields['M07'] + $res->fields['M08'] + $res->fields['M09'] + $res->fields['M010'] + $res->fields['M11'] + $res->fields['M12'];
	// 		else if($k == 9)
	// 			$tot_9 = $res->fields['M01'] + $res->fields['M02'] + $res->fields['M03'] + $res->fields['M04'] + $res->fields['M05'] + $res->fields['M06'] + $res->fields['M07'] + $res->fields['M08'] + $res->fields['M09'] + $res->fields['M010'] + $res->fields['M11'] + $res->fields['M12'];
	// 		else if($k == 13)
	// 			$tot_13 = $res->fields['M01'] + $res->fields['M02'] + $res->fields['M03'] + $res->fields['M04'] + $res->fields['M05'] + $res->fields['M06'] + $res->fields['M07'] + $res->fields['M08'] + $res->fields['M09'] + $res->fields['M010'] + $res->fields['M11'] + $res->fields['M12'];
	// 		else if($k == 14)
	// 			$tot_14 = $res->fields['M01'] + $res->fields['M02'] + $res->fields['M03'] + $res->fields['M04'] + $res->fields['M05'] + $res->fields['M06'] + $res->fields['M07'] + $res->fields['M08'] + $res->fields['M09'] + $res->fields['M010'] + $res->fields['M11'] + $res->fields['M12'];
				
	// 		$tot = $res->fields['M01'] + $res->fields['M02'] + $res->fields['M03'] + $res->fields['M04'] + $res->fields['M05'] + $res->fields['M06'] + $res->fields['M07'] + $res->fields['M08'] + $res->fields['M09'] + $res->fields['M010'] + $res->fields['M11'] + $res->fields['M12'];
			
	// 		if($k == 1 || $k == 10 || $k == 15 || $k == 19 || $k == 20) {
	// 			if($k == 1) {
	// 				$txt = '<table cellspacing="0" cellpadding="4" width="100%" >';
	// 				$per_sign 	= "";
	// 				$tot 		= "Total";
	// 				$percentage = "";
	// 			} else  {
	// 				$per_sign 	= "%";
	// 			}
				
	// 			if($k == 10){
	// 				if($tot > 0 && $tot_2 > 0)
	// 					$percentage = number_format(($tot/$tot_7), 2);
	// 				else
	// 					$percentage = 0;
	// 			} else if($k == 15){
	// 				if($tot > 0 && $tot_2 > 0)
	// 					$percentage = number_format(($tot_14/$tot_13), 2);
	// 				else
	// 					$percentage = 0;
	// 			} else if($k == 19){
	// 				if($tot > 0 && $tot_2 > 0)
	// 					$percentage = number_format(($tot/$tot_5), 2);
	// 				else
	// 					$percentage = 0;
	// 			}
	// 			 $per_sign == '' ? $val_M01 = $res->fields['M01'] : $val_M01 = number_format_value_checker($res->fields['M01'] , 2) ;
	// 			 $per_sign == '' ? $val_M02 = $res->fields['M02'] : $val_M02 = number_format_value_checker($res->fields['M02'] , 2) ;
	// 			 $per_sign == '' ? $val_M03 = $res->fields['M03'] : $val_M03 = number_format_value_checker($res->fields['M03'] , 2) ;
	// 			 $per_sign == '' ? $val_M04 = $res->fields['M04'] : $val_M04 = number_format_value_checker($res->fields['M04'] , 2) ;
	// 			 $per_sign == '' ? $val_M05 = $res->fields['M05'] : $val_M05 = number_format_value_checker($res->fields['M05'] , 2) ;
	// 			 $per_sign == '' ? $val_M06 = $res->fields['M06'] : $val_M06 = number_format_value_checker($res->fields['M06'] , 2) ;
	// 			 $per_sign == '' ? $val_M07 = $res->fields['M07'] : $val_M07 = number_format_value_checker($res->fields['M07'] , 2) ;
	// 			 $per_sign == '' ? $val_M08 = $res->fields['M08'] : $val_M08 = number_format_value_checker($res->fields['M08'] , 2) ;
	// 			 $per_sign == '' ? $val_M09 = $res->fields['M09'] : $val_M09 = number_format_value_checker($res->fields['M09'] , 2) ;
	// 			 $per_sign == '' ? $val_M10 = $res->fields['M10'] : $val_M10 = number_format_value_checker($res->fields['M10'] , 2) ;
	// 			 $per_sign == '' ? $val_M11 = $res->fields['M11'] : $val_M11 = number_format_value_checker($res->fields['M11'] , 2) ;
	// 			 $per_sign == '' ? $val_M12 = $res->fields['M12'] : $val_M12 = number_format_value_checker($res->fields['M12'] , 2) ;
				
	// 			$fixed_total_perc = $per_sign != '' ? number_format_value_checker($val_M01 + $val_M02 + $val_M03 + $val_M04 + $val_M05 + $val_M06 + $val_M07 + $val_M08 + $val_M09 + $val_M10 + $val_M11 + $val_M12, 2) : $tot;
				 
				
	// 			if(in_array($k , [1,10,15,19,20])){
	// 				$bropen = "<b>";
	// 				$brclose = "</b>";
	// 			}else{
	// 				$bropen = "";
	// 				$brclose = "";
	// 			}
	// 			$txt .= '<tr>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;border-left:1px solid #000;" width="25%" > '.$bropen.$res->fields['DESCRIPTION'].$brclose.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;border-left:1px solid #000;" width="5.5%" align="right" >'.$val_M01.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M02.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M03.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M04.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M05.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M06.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M07.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M08.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M09.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M10.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M11.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="5.5%" align="right" >'.$val_M12.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;border-left:1px solid #000;"  width="5.5%" align="right" >'.$fixed_total_perc.$per_sign.'</td>
	// 						<td style="border-top:1px solid #000;border-bottom:1px solid #000;border-right:1px solid #000;" width="5.5%" align="right" >'.$percentage.' </td>
	// 					</tr>';
	// 		} else {
	// 			$per_sign = '';
	// 			$percentage = 0;
				
	// 			if($k == 2)
	// 				$percentage = '';
	// 			else if($k == 3 || $k == 4) {
	// 				if($tot > 0 && $tot_2 > 0)
	// 					$percentage = $tot/$tot_2;
	// 				else
	// 					$percentage = 0;
	// 			} else if($k == 6 || $k == 7 || $k == 8) {
	// 				if($tot > 0 && $tot_5 > 0)
	// 					$percentage = $tot/$tot_5;
	// 				else
	// 					$percentage = 0;
	// 			} else if($k == 9) {
	// 				if($tot > 0 && $tot_7 > 0)
	// 					$percentage = $tot/$tot_7;
	// 				else
	// 					$percentage = 0;
	// 			} else if($k == 11 || $k == 12 || $k == 13 || $k == 14) {
	// 				if($tot > 0 && $tot_9 > 0)
	// 					$percentage = $tot/$tot_9;
	// 				else
	// 					$percentage = 0;
	// 			} else if($k == 16 || $k == 17 || $k == 18) {
	// 				if($tot > 0 && $tot_13 > 0)
	// 					$percentage = $tot/$tot_13;
	// 				else
	// 					$percentage = 0;
	// 			} 
				
	// 			if($percentage > 0)
	// 				$percentage = number_format($percentage,2);
				
	// 			$txt .= '<tr>
	// 						<td style="border-left:1px solid #000;" >'.$res->fields['DESCRIPTION'].'</td>
	// 						<td style="border-left:1px solid #000;" align="right" >'.$res->fields['M01'].'</td>
	// 						<td align="right" >'.$res->fields['M02'].'</td>
	// 						<td align="right" >'.$res->fields['M03'].'</td>
	// 						<td align="right" >'.$res->fields['M04'].'</td>
	// 						<td align="right" >'.$res->fields['M05'].'</td>
	// 						<td align="right" >'.$res->fields['M06'].'</td>
	// 						<td align="right" >'.$res->fields['M07'].'</td>
	// 						<td align="right" >'.$res->fields['M08'].'</td>
	// 						<td align="right" >'.$res->fields['M09'].'</td>
	// 						<td align="right" >'.$res->fields['M10'].'</td>
	// 						<td align="right" >'.$res->fields['M11'].'</td>
	// 						<td align="right" >'.$res->fields['M12'].'</td>
	// 						<td style="border-left:1px solid #000;" align="right" >'.$res->fields['TOTAL'].$per_sign.'</td>
	// 						<td style="border-right:1px solid #000;" align="right" >'.$percentage.'</td>
	// 					</tr>';
	// 		}
			
	// 		if($k == 20) {
	// 			$txt .= "</table>";
	// 			$mpdf->SetHTMLHeader($header);
	// 			$mpdf->AddPage();
	// 			$mpdf->WriteHTML($txt);
	// 			// echo $txt;
	// 			$txt = "";
	// 		}
			
	// 		$k++;
	// 		$res->MoveNext();
	// 	}
	// 	// exit;
	// 	$mpdf->Output("ACCSC Graduation and Employment.pdf", 'D');
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
	$file_name 		= 'ACCSC Employment Verification Source Report.xlsx';
	$outputFileName = $dir.$file_name ;
	
	$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 

	$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
	$objReader->setIncludeCharts(TRUE);
	//$objPHPExcel   = $objReader->load('../../global/excel/Template/Licensure_Certification_Exam_Pass_Rates.xlsx');
	$objPHPExcel = new PHPExcel();
	$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	$line 	= 1;	
	$index 	= 0;

	$index 	= -1;
	$heading[] = 'Program';
	$width[]   = 20;
	$heading[] = 'Months Duration';
	$width[]   = 20;
	$heading[] = 'Term Start';
	$width[]   = 20;
	$heading[] = 'Description';
	$width[]   = 20;
	$heading[] = '1';
	$width[]   = 20;
	$heading[] = '2';
	$width[]   = 20;
	$heading[] = '3';
	$width[]   = 20;
	$heading[] = '4';
	$width[]   = 20;
	$heading[] = '5';
	$width[]   = 20;
	$heading[] = '6';
	$width[]   = 20;
	$heading[] = '7';
	$width[]   = 20;
	$heading[] = '8';
	$width[]   = 20;
	$heading[] = '9';
	$width[]   = 20;
	$heading[] = '10';
	$width[]   = 20;
	$heading[] = '11';
	$width[]   = 20;
	$heading[] = '12';
	$width[]   = 20;
	$heading[] = 'Total';
	$width[]   = 20;
	$heading[] = 'AVG';
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
		$res = $db->Execute("CALL COMP40001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['ENROLLMENT_START_YEAR']."','".$_POST['ENROLLMENT_START_MONTH']."', 'Chart', '".$REPORT_OPTION."')");	while (!$res->EOF) {
		$line++;
		$index = -1; 
				

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM']);
		

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['MONTHS']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TERM_STARTS']);
		
		 

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DESCRIPTION']);

		if(in_array(substr($res->fields['DESCRIPTION'], 0, 2) , ['10' ,'15' , '19','20'])){
			$res->fields['M01'] = number_format_value_checker($res->fields['M01'] , 2).'%';
			$res->fields['M02'] = number_format_value_checker($res->fields['M02'] , 2).'%';
			$res->fields['M03'] = number_format_value_checker($res->fields['M03'] , 2).'%';
			$res->fields['M04'] = number_format_value_checker($res->fields['M04'] , 2).'%';
			$res->fields['M05'] = number_format_value_checker($res->fields['M05'] , 2).'%';
			$res->fields['M06'] = number_format_value_checker($res->fields['M06'] , 2).'%';
			$res->fields['M07'] = number_format_value_checker($res->fields['M07'] , 2).'%';
			$res->fields['M08'] = number_format_value_checker($res->fields['M08'] , 2).'%';
			$res->fields['M09'] = number_format_value_checker($res->fields['M09'] , 2).'%';
			$res->fields['M10'] = number_format_value_checker($res->fields['M10'] , 2).'%';
			$res->fields['M11'] = number_format_value_checker($res->fields['M11'] , 2).'%';
			$res->fields['M12'] = number_format_value_checker($res->fields['M12'] , 2).'%';
			$res->fields['TOTAL'] = number_format_value_checker($res->fields['TOTAL'] , 2).'%';
			$res->fields['AVG'] = number_format_value_checker($res->fields['AVG'] , 2).'%';
		}

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M01']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M02']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M03']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M04']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M05']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M06']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M07']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M08']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M09']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M10']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M11']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['M12']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TOTAL']);
				
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AVG']);




		$res->MoveNext();
	}
	$objPHPExcel->getActiveSheet()->getStyle("B:B")->getNumberFormat()->setFormatCode('0.00');
	$objWriter->save($outputFileName);
	$objPHPExcel->disconnectWorksheets();
	header("location:".$outputFileName);

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
		$file_name 		= 'ACCSC Graduation and Employment.xlsx';
		$outputFileName = $dir.$file_name;

		$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );
		
		if($_POST['REPORT_TYPE'] == 1) {
			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../global/excel/Template/Graduation_Employment_Chart.xlsx');
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			$objPHPExcel->setActiveSheetIndex(1);
			$objPHPExcel->getActiveSheet()->setTitle('Glossary 1');
			$worksheet_2 = $objPHPExcel->getActiveSheet()->copy();
			$objPHPExcel->getActiveSheet()->setTitle('temp Glossary');
			
			$objPHPExcel->removeSheetByIndex(2);
			$objPHPExcel->setActiveSheetIndex(0);
			
			$res_acc = $db->Execute("SELECT PHONE, EMAIL, CITY, STATE_CODE, ZIP FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$objPHPExcel->getActiveSheet()->getCell('I4')->setValue($_SESSION['SCHOOL_NAME']);
			$objPHPExcel->getActiveSheet()->getCell('T4')->setValue(date("m/d/Y"));
			$objPHPExcel->getActiveSheet()->getCell('O6')->setValue($res_acc->fields['PHONE']);
			$objPHPExcel->getActiveSheet()->getCell('S6')->setValue($res_acc->fields['EMAIL']);
			$objPHPExcel->getActiveSheet()->getCell('C6')->setValue($res_acc->fields['CITY']);
			$objPHPExcel->getActiveSheet()->getCell('H6')->setValue($res_acc->fields['STATE_CODE']);
			$objPHPExcel->getActiveSheet()->getCell('K6')->setValue($res_acc->fields['ZIP']);
		
			$k = 0;
			$line_1 		= 11;
			$sheet_no 		= 2;
			$worksheet_1 	= $objPHPExcel->getActiveSheet()->copy();

			$res = $db->Execute("CALL COMP40001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['ENROLLMENT_START_YEAR']."','".$_POST['ENROLLMENT_START_MONTH']."', 'Chart', '".$REPORT_OPTION."')");
			while (!$res->EOF) {
				if($k == 0) {
					$PROGRAM 		= $res->fields['PROGRAM'];
					$PROGRAM_LENGTH = $res->fields['MONTHS'];
					$objPHPExcel->getActiveSheet()->setTitle($PROGRAM);
				} else {
					if($PROGRAM != $res->fields['PROGRAM']) {
						$PROGRAM 		= $res->fields['PROGRAM'];
						$PROGRAM_LENGTH = $res->fields['MONTHS'];
						
						$sheet2 = clone $worksheet_1;
						$objPHPExcel->addSheet($sheet2);
						$objPHPExcel->setActiveSheetIndex($sheet_no);
						$objPHPExcel->getActiveSheet()->setTitle($PROGRAM);
						
						$sheet_no++;
					}
				}
				
				$k++;

				if(trim(strtolower($res->fields['DESCRIPTION'])) == "01. class start date (month/year):")
					$line = $line_1 + 1;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "02. number started:")
					$line = $line_1 + 2;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "03. transfers to another program:")
					$line = $line_1 + 3;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "04. transfers from another program:")
					$line = $line_1 + 4;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "05. total starts +/- transfers:"){
					$res->MoveNext();
					continue;
				} else if(trim(strtolower($res->fields['DESCRIPTION'])) == "06. unavailable for graduation:")
					$line = $line_1 + 6;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "07. available for graduation:") {
					$res->MoveNext();
					continue;
				} else if(trim(strtolower($res->fields['DESCRIPTION'])) == "08. withdrawn/terminated:")
					$line = $line_1 + 8;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "09. grads within 150% of program length:") {
					$res->MoveNext();
					continue;
				} else if(trim(strtolower($res->fields['DESCRIPTION'])) == "10. graduation rate:") {
					$res->MoveNext();
					continue;
				} else if(trim(strtolower($res->fields['DESCRIPTION'])) == "11. graduates - further education:")
					$line = $line_1 + 11;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "12. graduates - unavailable for employment:")
					$line = $line_1 + 12;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "13. graduates - available for employment:") {
					$res->MoveNext();
					continue;
				} else if(trim(strtolower($res->fields['DESCRIPTION'])) == "14. graduates - employed in field:")
					$line = $line_1 + 14;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "15. employment rate:") {
					$res->MoveNext();
					continue;
				} else if(trim(strtolower($res->fields['DESCRIPTION'])) == "16. graduates - unrelated occupations:")
					$line = $line_1 + 16;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "17. graduates - unemployed:")
					$line = $line_1 + 17;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "18. graudates - unknown:")
					$line = $line_1 + 18;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "19. non-graduated students who obtained training related employment:")
					$line = $line_1 + 19;
				else if(trim(strtolower($res->fields['DESCRIPTION'])) == "20. employment rate with non-graduated students who obtained training related employment:") {
					$res->MoveNext();
					continue;
				}
				
				$cell = 'H'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M01']);
			
				$cell = 'I'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M02']);
				
				$cell = 'J'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M03']);
				
				$cell = 'K'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M04']);
				
				$cell = 'L'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M05']);
				
				$cell = 'M'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M06']);
				
				$cell = 'N'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M07']);
				
				$cell = 'O'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M08']);
				
				$cell = 'P'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M09']);
				
				$cell = 'Q'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M10']);
				
				$cell = 'R'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M11']);
				
				$cell = 'S'.$line;
				$objPHPExcel->getActiveSheet()->getCell($cell)->setValue($res->fields['M12']);

				$objPHPExcel->getActiveSheet()->getCell('C8')->setValue($PROGRAM);
				$objPHPExcel->getActiveSheet()->getCell('S8')->setValue($PROGRAM_LENGTH);
				
				$res->MoveNext();
			}
			
			if($sheet_no > 2) {
				
				$sheet2 = clone $worksheet_2;
				$objPHPExcel->addSheet($sheet2);
				$objPHPExcel->setActiveSheetIndex($sheet_no);
				$objPHPExcel->getActiveSheet()->setTitle('Glossary');
				
				//$objPHPExcel->removeSheetByIndex(2);
				$objPHPExcel->removeSheetByIndex(1);
				
				/*$sheet2 = clone $worksheet_1;
				$objPHPExcel->addSheet($sheet2);
				$objPHPExcel->setActiveSheetIndex($sheet_no);
				$objPHPExcel->getActiveSheet()->setTitle('Glossary');*/
			}
			
			$objPHPExcel->setActiveSheetIndex(0);

		} else if($_POST['REPORT_TYPE'] == 2) {
			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/Licensure_Certification_Exam_Pass_Rates.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			$line 	= 1;	
			$index 	= 0;
	
			$index 	= -1;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'Program Months';
			$width[]   = 20;
			$heading[] = 'Last Name';
			$width[]   = 20;
			$heading[] = 'First Name';
			$width[]   = 20;
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'Placement Status';
			$width[]   = 20;
			$heading[] = 'Transfer-In';
			$width[]   = 20;
			$heading[] = 'Transfer-Out';
			$width[]   = 20;
			$heading[] = 'Begin Date';
			$width[]   = 20;
			$heading[] = 'Start Date Month Year';
			$width[]   = 20;
			$heading[] = 'Report Month';
			$width[]   = 20;

			$width[]   = 20;
			$heading[] = '01';
			$width[]   = 10;
			$heading[] = '02';
			$width[]   = 10;
			$heading[] = '03';
			$width[]   = 10;
			$heading[] = '04';
			$width[]   = 10;
			$heading[] = '05';
			$width[]   = 10;
			$heading[] = '06';
			$width[]   = 10;
			$heading[] = '07';
			$width[]   = 10;
			$heading[] = '08';
			$width[]   = 10;
			$heading[] = '09';
			$width[]   = 10;
			$heading[] = '10';
			$width[]   = 10;
			$heading[] = '11';
			$width[]   = 10;
			$heading[] = '12';
			$width[]   = 10;
			$heading[] = '13';
			$width[]   = 10;
			$heading[] = '14';
			$width[]   = 10;
			$heading[] = '15';
			$width[]   = 10;
			$heading[] = '16';
			$width[]   = 10;
			$heading[] = '17';
			$width[]   = 10;
			$heading[] = '18';
			$width[]   = 10;
			$heading[] = '19';
			$width[]   = 10;
			$heading[] = '20';
			$width[]   = 10;
			
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
			$res = $db->Execute("CALL COMP40001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['ENROLLMENT_START_YEAR']."','".$_POST['ENROLLMENT_START_MONTH']."', 'Detail', '".$REPORT_OPTION."')");
			while (!$res->EOF) {
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['MONTHS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LAST_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FIRST_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PLACEMENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TRANSFER_IN']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TRANSFER_OUT']);
			
				$BEGIN_DATE = '';
				if($res->fields['BEGIN_DATE'] != '' && $res->fields['BEGIN_DATE'] != '0000-00-00')
					$BEGIN_DATE = date("m/d/Y", strtotime($res->fields['BEGIN_DATE']));
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BEGIN_DATE);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['START_DATE_MONTH_YEAR']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['REPORT_MONTH']);
								
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_01']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_02']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_03']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_04']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_05']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_06']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_07']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_08']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_09']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_10']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_11']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_12']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_13']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_14']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_15']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_16']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_17']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_18']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_19']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_20']);
				
				$res->MoveNext();
			}
		}
		$objPHPExcel->getActiveSheet()->getStyle("D:D")->getNumberFormat()->setFormatCode('0.00');
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
	}
	exit;
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
	<title><?=MNU_ACCSC_GRA_EMP_CHART ?> | <?=$title?></title>
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
							<?=MNU_ACCSC_GRA_EMP_CHART ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row form-group">
									<div class="col-md-2">
											<?=REPORT_TYPE ?>
											<select id="FORMAT" name="FORMAT" class="form-control"> 
											<option value="1" >Summary</option>	
											<option value="2" >Detail</option>
												
											</select>
										</div>
										<div class="col-md-3 ">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" 
													<?php if($res_type->RecordCount() === 1){
														echo " selected ";
													}
													?>
													
													><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 ">
											<?=ENROLLMENT_START_YEAR?>
											<select id="ENROLLMENT_START_YEAR" name="ENROLLMENT_START_YEAR" class="form-control" >
												<option value=""></option>
												<? for($i = 2025 ; $i >= 2015 ; $i--){ ?>
												<option value="<?=$i?>" ><?=$i?></option>
												<? } ?>
											</select>
										</div>
										<div class="col-md-2">
											<?=ENROLLMENT_START_MONTH?>
											<select id="ENROLLMENT_START_MONTH" name="ENROLLMENT_START_MONTH" class="form-control" >
												<option value=""></option>
												<? for($i = 1 ; $i <= 12 ; $i++){ ?>
												<option value="<?=$i?>" ><?=$i?></option>
												<? } ?>
											</select>
										</div>
										<div class="col-md-2">
											<?=REPORT_OPTIONS ?>
											<select id="REPORT_OPTION" name="REPORT_OPTION" class="form-control" >
												<option value="1" >Program</option>
												<option value="2" >Program Group</option>
											</select>
										</div>
										<div class="col-md-1" style="padding: 0;" >
											<br />
											<!-- <button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button> -->
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<!-- <input type="hidden" name="FORMAT" id="FORMAT" > -->
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
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
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
		// document.getElementById('FORMAT').value = val
		document.form1.submit();
	}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 1,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>
</body>

</html>