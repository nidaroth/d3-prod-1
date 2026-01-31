<?php require_once('../global/config.php');
require_once("check_access.php");

if(check_access('REPORT_ADMISSION') == 0 ){
	header("location:../index");
	exit;
}

include '../global/excel/Classes/PHPExcel/IOFactory.php';
$cell  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ","CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN","CO","CP","CQ","CR","CS","CT","CU","CV","CW","CX","CY","CZ","DA","DB","DC","DD","DE","DF","DG","DH","DI","DJ","DK","DL","DM","DN","DO","DP","DQ","DR","DS","DT","DU","DV","DW","DX","DY","DZ","EA","EB","EC","ED","EE","EF","EG","EH","EI","EJ","EK","EL","EM","EN","EO","EP","EQ","ER","ES","ET","EU","EV","EW","EX","EY","EZ");		
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
$file_name 		= 'Task Report.xlsx';
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line = 1;	
$index = -1;

$heading[] = 'Lead';
$width[]   = 20;
$heading[] = 'Campus';
$width[]   = 20;
$heading[] = 'Student ID';
$width[]   = 20;
$heading[] = 'First Term Date';
$width[]   = 20;
$heading[] = 'Program';
$width[]   = 15;
$heading[] = 'Status';
$width[]   = 15;
$heading[] = 'Task Date';
$width[]   = 15;
$heading[] = 'Task Time';
$width[]   = 20;
$heading[] = 'Task Type';
$width[]   = 20;
$heading[] = 'Task Status';
$width[]   = 20;
$heading[] = 'Task Other';
$width[]   = 20;
$heading[] = 'Priority';
$width[]   = 20;
$heading[] = 'Follow Up Date';
$width[]   = 15;
$heading[] = 'Follow Up Time';
$width[]   = 15;
$heading[] = 'Employee';
$width[]   = 15;
$heading[] = 'Created By';
$width[]   = 15;
$heading[] = 'Home Phone';
$width[]   = 20;
$heading[] = 'Mobile Email';
$width[]   = 20;
$heading[] = 'Email';
$width[]   = 40;
$heading[] = 'Comments';
$width[]   = 40;

$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	$i++;
}
$objPHPExcel->getActiveSheet()->freezePane('A2');

$res = $db->Execute($_SESSION['task_report_query']." AND S_STUDENT_TASK.PK_STUDENT_MASTER IN ($_GET[sid]) ORDER BY STU_NAME ASC, STUDENT_ID ASC, TASK_DATE ASC, TASK_TIME ASC "); 

while (!$res->EOF){
	$line++;
	
	$TASK_TIME = '';
	if($res->fields['TASK_TIME'] != '00-00-00') 
		$TASK_TIME = date("h:i A", strtotime($res->fields['TASK_TIME']));
		
	$FOLLOWUP_TIME = "";
	if($res->fields['FOLLOWUP_TIME'] != '00-00-00') 
		$FOLLOWUP_TIME = date("h:i A", strtotime($res->fields['FOLLOWUP_TIME']));
	
	/*if($EMP_NAME != $res->fields['EMP_NAME']){
		$EMP_NAME = $res->fields['EMP_NAME'];
		
		$index = 0;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($EMP_NAME);
		$line++;
	}*/
	
	$index = 0;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE_1']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CODE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
	
	$index++;
	if($res->fields['TASK_DATE_1'] != '') {
		$cell_no = $cell[$index].$line;
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['TASK_DATE_1'])));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old - FORMAT_DATE_XLSX14
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TASK_TIME);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TASK_TYPE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TASK_STATUS']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EVENT_OTHER']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES_PRIORITY']);
	
	$index++;
	if($res->fields['FOLLOWUP_DATE'] != '') {
		$cell_no = $cell[$index].$line;
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['FOLLOWUP_DATE'])));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2); // old - FORMAT_DATE_XLSX14
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FOLLOWUP_TIME);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMP_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREATED_BY_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HOME_PHONE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CELL_PHONE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES']);
	
	$res->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);