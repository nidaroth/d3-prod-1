<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/population_report_setup.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['POPULATION_REPORT'] == 0 || check_access('MANAGEMENT_POPULATION_REPORT') == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
	}
	if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
	}
	
	$camp_cond = "";
	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$campus_name = "";
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
	
	if($_POST['FORMAT'] == 1) {

		require_once '../global/mpdf/vendor/autoload.php';
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];

		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
	
		if($_POST['REPORT_OPTIONS'] == 1)
			$label = "All Enrollments";
		else
			$label = "Current Enrollment";
		
		$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="30%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="50%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Population Report</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Between: '.$_POST['START_DATE'].' - '.$_POST['END_DATE'].'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >'.$label.'</td>
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
							<td width="33%" valign="top" style="font-size:10px;" align="center" >DSIS10002</td>
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
			'default_font_size' => 9,
			'format' => [210, 296],
			'orientation' => 'L'
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
		$group_by 		= "";
		$FIELD 			= "";
		$REPORT_GROUP 	= "";
		$REPORT_OPTION 	= "";
		if($_POST['REPORT_TYPE'] == 1) {
			$LABEL 			= 'Program';
			$group_by 		= " PK_CAMPUS_PROGRAM ";
			$FIELD 			= "PROGRAM_CODE";
			$REPORT_GROUP 	= "PROGRAM";
		} else {
			$LABEL	 		= 'Program Group';
			$group_by 		= " PK_PROGRAM_GROUP ";
			$FIELD 			= "PROGRAM_GROUP";
			$REPORT_GROUP 	= "PROGRAM GROUP";
		}
		
		if($_POST['REPORT_OPTIONS'] == 1)
			$REPORT_OPTION 	= "ALL";
		else
			$REPORT_OPTION 	= "CURRENT";
		
		$txt = '<table border="1" cellspacing="0" cellpadding="4" width="100%">
					<thead>
						<tr>
							<td width="10%"  rowspan="2" align="center" >
								<b>'.$LABEL.'</b>
							</td>
							<td width="8%"  rowspan="2" align="center" >
								<b>Beginning Population</b>
							</td>
							<td width="20%"  colspan="3" align="center" >
								<b>Additions</b>
							</td>
							<td width="8%"  rowspan="2" align="center" >
								<b>Population Additions</b>
							</td>
							<td width="5%"  rowspan="2" align="center" >
								<b>Sub Total</b>
							</td>
							<td width="19%"  colspan="3" align="center" >
								<b>Subtractions</b>
							</td>
							
							<td width="7%"  rowspan="2" align="center" >
								<b>Retained Population</b>
							</td>
							<td width="7%"  rowspan="2" align="center" >
								<b>Retention Rate</b>
							</td>
							<td width="5%"  rowspan="2" align="center" >
								<b>Grads</b>
							</td>
							<td width="8%"  rowspan="2" align="center" >
								<b>Other Completers</b>
							</td>
							<td width="7%" rowspan="2" align="center" >
								<b>Ending Population</b>
							</td>
						</tr>
						<tr>
							<td width="8%" align="center" >
								<b>New Enrollments</b>
							</td>
							<td width="6%" align="center" >
								<b>Re-Entry</b>
							</td>
							<td width="6%"  align="center" >
								<b>Transfer In</b>
							</td>
							
							<td width="6%" align="center" >
								<b>Transfer Out</b>
							</td>
							<td width="5%"  align="center" >
								<b>Drops</b>
							</td>
							<td width="8%"  align="center" >
								<b>Other Withdrawals</b>
							</td>
						</tr>
					</thead>';

		$TOT_BEG_POP 				= 0;
		$TOT_NEW_ENROLLMENT 		= 0;
		$TOT_RE_ENTRY 				= 0;
		$TOT_TRANSFER_IN 			= 0;
		$TOT_POP_ADDITION 			= 0;
		$TOT_SUB_TOTAL 				= 0;
		$TOT_TRANSFER_OUT 			= 0;
		$TOT_DROPS 					= 0;
		$TOT_OTHER_WITHDRAW 		= 0;
		$TOT_RETAINED_POPULATION 	= 0;
		$TOT_RETAINED_RATE 			= 0;
		$TOT_GRADUATES 				= 0;
		$TOT_OTHER_COMPLETERS 		= 0;
		$TOT_END_PRO 				= 0;
		
		$no_records = 0;
		
		//echo "CALL DSIS10002(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_SESSION['PK_USER'].", '".$ST."', '".$ET."', '".$REPORT_OPTION."', '".$REPORT_GROUP."', 'SUMMARY' )";exit;
		$res = $db->Execute("CALL DSIS10002(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_SESSION['PK_USER'].", '".$ST."', '".$ET."', '".$REPORT_OPTION."', '".$REPORT_GROUP."', 'SUMMARY' )");
		while (!$res->EOF) { 
			
			/*$TOT_BEG_POP 				+= $res->fields['BEGINNING_POPULATION'];
			$TOT_NEW_ENROLLMENT 		+= $res->fields['NEW_ENROLLMENTS'];
			$TOT_RE_ENTRY 				+= $res->fields['RE_ENTRY'];
			$TOT_TRANSFER_IN 			+= $res->fields['TRANSFER_IN'];
			$TOT_POP_ADDITION 			+= $res->fields['POPULATION_ADDITIONS'];
			$TOT_SUB_TOTAL 				+= $res->fields['SUB_TOTAL'];
			$TOT_TRANSFER_OUT 			+= $res->fields['TRANSFER_OUT'];
			$TOT_DROPS 					+= $res->fields['DROPS'];
			$TOT_OTHER_WITHDRAW 		+= $res->fields['OTHER_WITHDRAWALS'];
			$TOT_RETAINED_POPULATION 	+= $res->fields['RETAINED_POPULATION'];
			$TOT_RETAINED_RATE 			 = $res->fields['RETENTION_RATE'];
			$TOT_GRADUATES 				+= $res->fields['GRADS'];
			$TOT_OTHER_COMPLETERS 		+= $res->fields['OTHER_COMPLETERS'];
			$TOT_END_PRO 				+= $res->fields['ENDING_POPULATION'];
			
			$no_records++;*/
			
			$b_style   = "";
			$b_style_1 = "";
			
			if(strtolower($res->fields['PROGRAM']) == "total"){
				$b_style   = "<b>";
				$b_style_1 = "</b>";
			}
			
			$txt .= '<tr>
						<td >
							'.$b_style.$res->fields['PROGRAM'].'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['BEGINNING_POPULATION']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['NEW_ENROLLMENTS']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['RE_ENTRY']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['TRANSFER_IN']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['POPULATION_ADDITIONS']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['SUB_TOTAL']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['TRANSFER_OUT']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['DROPS']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['OTHER_WITHDRAWALS']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['RETAINED_POPULATION']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['RETENTION_RATE'], 2).$b_style_1.' %
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['GRADS']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['OTHER_COMPLETERS']).$b_style_1.'
						</td>
						<td align="right" >
							'.$b_style.number_format_value_checker($res->fields['ENDING_POPULATION']).$b_style_1.'
						</td>
					</tr>';
					
			$res->MoveNext();
		}
		/*
		if($no_records > 0 && $TOT_RETAINED_RATE > 0)
			$TOT_RETAINED_RATE = $TOT_RETAINED_RATE / $no_records;
		
		$txt .= '<tr>
					<td align="center" >
						<b>Total</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_BEG_POP).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_NEW_ENROLLMENT).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_RE_ENTRY).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_TRANSFER_IN).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_POP_ADDITION).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_SUB_TOTAL).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_TRANSFER_OUT).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_DROPS).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_OTHER_WITHDRAW).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_RETAINED_POPULATION).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_RETAINED_RATE, 2).' %</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_GRADUATES).'</b>
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_OTHER_COMPLETERS).'
					</td>
					<td align="right" >
						<b>'.number_format_value_checker($TOT_END_PRO).'</b>
					</td>
				</tr>
			</table>';
		*/
		
		$txt .= '</table>';
		
		//$db->Execute("DELETE FROM S_POPULATION_REPORT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		
		$mpdf->WriteHTML($txt);
		$mpdf->Output('Population Report.pdf', 'D');
		
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
		$file_name 		= 'Population Report.xlsx';
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
		$heading[] = 'Student ID'; // DIAM-1301
		$width[]   = 20;
		$heading[] = 'Campus'; // DIAM-1301
		$width[]   = 20;
		$heading[] = 'Program Code';
		$width[]   = 20;
		$heading[] = 'Program Description'; // DIAM-1301
		$width[]   = 20;
		$heading[] = 'Program Group';
		$width[]   = 20;
		$heading[] = 'Student Status';
		$width[]   = 20;
		$heading[] = 'Start Date';
		$width[]   = 20;
		$heading[] = 'End Date';
		$width[]   = 20;
		$heading[] = 'End Date Type'; // DIAM-1301
		$width[]   = 20;
		$heading[] = 'Is Active Enrollment';
		$width[]   = 20;
		$heading[] = 'Beginning Population';
		$width[]   = 20;
		$heading[] = 'New Enrollment';
		$width[]   = 20;
		$heading[] = 'Re-Entry';
		$width[]   = 20;
		$heading[] = 'Transfer In';
		$width[]   = 20;
		$heading[] = 'Population Additions';
		$width[]   = 20;
		$heading[] = 'Sub Total';
		$width[]   = 20;
		$heading[] = 'Transfer Out';
		$width[]   = 20;
		$heading[] = 'Drops';
		$width[]   = 20;
		$heading[] = 'Other Withdrawals';
		$width[]   = 20;
		$heading[] = 'Population Subtractions';
		$width[]   = 20;
		$heading[] = 'Retained Populations';
		$width[]   = 20;
		$heading[] = 'Graduate';
		$width[]   = 20;
		$heading[] = 'Other Completer';
		$width[]   = 20;
		$heading[] = 'End Population';
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

		$objPHPExcel->getActiveSheet()->freezePane('A1');
		
		$res = $db->Execute("CALL DSIS10002(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_SESSION['PK_USER'].", '".$ST."', '".$ET."', '".$REPORT_OPTION."', '".$REPORT_GROUP."', 'DETAIL' )");
		while (!$res->EOF) { 
			
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT']);

			// DIAM-1301
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
			// End DIAM-1301
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']); // DIAM-1301
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_GROUP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
			
			$index++;
			if($res->fields['START_DATE'] != '' && $res->fields['START_DATE'] != '0000-00-00'){
				$cell_no = $cell[$index].$line;
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['START_DATE'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			}
			
			$index++;
			if($res->fields['END_DATE'] != '' && $res->fields['END_DATE'] != '0000-00-00'){
				$cell_no = $cell[$index].$line;
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['END_DATE'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			}

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['END_DATE_TYPE']); // DIAM-1301
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['IS_ACTIVE_ENROLLMENT']);
	
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGINNING_POPULATION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NEW_ENROLLMENT']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RE_ENTRY']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TRANSFER_IN']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['POPULATION_ADDITION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SUB_TOTAL']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TRANSFER_OUT']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DROP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['OTHER_WITHDRAWAL']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['POPULATION_SUBTRACTION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RETAINED_POPULATION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GRADUATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['OTHER_COMPLETER']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ENDING_POPULATION']);
			
			$res->MoveNext();
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
	<title><?=MNU_POPULATION_REPORT ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS {position: absolute;top: 55px;width: 142px}
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
							<?=MNU_POPULATION_REPORT?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<!-- Ticket # 1932   -->
									<div class="row">
										<div class="col-md-12 text-right">
											<button type="button" onclick="window.location.href='population_report_setup'" class="btn waves-effect waves-light btn-info"><?=REPORT_SETUP?></button>
											<br />
											<? $res = $db->Execute("select * from S_POPULATION_REPORT_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
											if($res->fields['EDITED_ON'] != '' && $res->fields['EDITED_ON'] != '0000-00-00 00:00:00'){
												$EDITED_BY	= $res->fields['EDITED_BY'];
												$EDITED_ON	= $res->fields['EDITED_ON'];
												$res_user = $db->Execute("SELECT CONCAT(LAST_NAME,', ', FIRST_NAME) as NAME FROM S_EMPLOYEE_MASTER, Z_USER WHERE PK_USER = '$EDITED_BY' AND Z_USER.ID =  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) "); 

												$EDITED_BY	= $res_user->fields['NAME']; 
												echo "Edited: ".$EDITED_BY." ".date("m/d/Y",strtotime($EDITED_ON));
											} ?>
										</div>
									</div>
									<!-- Ticket # 1932   -->
									
									<div class="row">
										<!-- Ticket # 1408  -->
										<div class="col-md-2" id="PK_CAMPUS_DIV"  >
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- Ticket # 1408  -->
										
										<div class="col-md-2">
											<?=REPORT_OPTION?>
											<select id="REPORT_OPTIONS" name="REPORT_OPTIONS" class="form-control" >
												<option value="1">All Enrollments</option>
												<option value="2">Current Enrollment</option>
											</select>
										</div>
										<div class="col-md-2">
											<?=REPORT_TYPE?>
											<select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" >
												<option value="1">By Program</option>
												<option value="2">By Program Group</option>
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
	
	<!-- Ticket # 1408  -->
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: ''
		});
	});
	</script>
	<!-- Ticket # 1408  -->

</body>

</html>