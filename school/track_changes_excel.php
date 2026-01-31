<?php require_once('../global/config.php');
require_once("../language/student.php");
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('ADMISSION_ACCESS') == 0 && check_access('REGISTRAR_ACCESS') == 0 && check_access('FINANCE_ACCESS') == 0 && check_access('ACCOUNTING_ACCESS') == 0 && check_access('PLACEMENT_ACCESS') == 0 ){ 
	header("location:../index");
	exit;
}

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
$file_name 		= 'change_history.xlsx';
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

$heading[] = FIELD_NAME;
$width[]   = 20;
$heading[] = OLD_VALUE;
$width[]   = 20;
$heading[] = NEW_VALUE;
$width[]   = 20;
$heading[] = CHANGED_BY;
$width[]   = 20;
$heading[] = CHANGED_ON;
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

$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$timezone = $res->fields['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0)
	$timezone = 4;
	
$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
$TIMEZONE = $res_tz->fields['TIMEZONE'];

$res_type = $db->Execute($_SESSION['REPORT_QUERY']); 
while (!$res_type->EOF){
	$line++;
	
	$index = -1;
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['FIELD_NAME']);
	
	if($res_type->fields['FIELD_NAME'] == 'SSN') {
		$SSN = $res_type->fields['OLD_VALUE'];
		if($SSN != '') {
			$SSN = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
		}
		$OLD_VALUE = $SSN;
	} else
		$OLD_VALUE = $res_type->fields['OLD_VALUE'];
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($OLD_VALUE);
	
	if($res_type->fields['FIELD_NAME'] == 'SSN') {
		$SSN = $res_type->fields['NEW_VALUE'];
		if($SSN != '') {
			$SSN = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
		}
		$NEW_VALUE = $SSN;
	} else
		$NEW_VALUE = $res_type->fields['NEW_VALUE'];
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($NEW_VALUE);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	if($res_type->fields['CHANGED_ON'] != '0000-00-00 00:00:00'){
		$CHANGED_ON = convert_to_user_date($res_type->fields['CHANGED_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
		
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime($CHANGED_ON)))));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CHANGED_ON);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14);
	}
	
	$res_type->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);