<?php require_once('../global/config.php');
require_once("check_access.php");

if(check_access('MANAGEMENT_ADMISSION') == 0 && check_access('REPORT_CUSTOM_REPORT') == 0){
	header("location:../index");
	exit;
}

include '../global/excel/Classes/PHPExcel/IOFactory.php';
$cell  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ","CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN","CO","CP","CQ","CR","CS","CT","CU","CV","CW","CX","CY","CZ","DA","DB","DC","DD","DE","DF","DG","DH","DI","DJ","DK","DL","DM","DN","DO","DP","DQ","DR","DS","DT","DU","DV","DW","DX","DY","DZ","EA","EB","EC","ED","EE","EF","EG","EH","EI","EJ","EK","EL","EM","EN","EO","EP","EQ","ER","ES","ET","EU","EV","EW","EX","EY","EZ");		
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
$file_name 		= 'Documents Report.xlsx';
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

$heading[] = 'Student';
$width[]   = 20;
$heading[] = 'Email';
$width[]   = 20;
$heading[] = 'Campus'; //DIAM-1439
$width[]   = 20;
$heading[] = 'First Term';
$width[]   = 20;
$heading[] = 'Department';
$width[]   = 25;
$heading[] = 'Document';
$width[]   = 25;
$heading[] = 'Employee';
$width[]   = 20;
$heading[] = 'Requested';
$width[]   = 15;
$heading[] = 'Follow Up';
$width[]   = 15;
$heading[] = 'Received';
$width[]   = 15;
$heading[] = 'Notes';
$width[]   = 60;

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

$res = $db->Execute($_SESSION['document_report_query']); 
while (!$res->EOF){
	$line++;
	
	$PK_STUDENT_DOCUMENTS = $res->fields['PK_STUDENT_DOCUMENTS']; 
	$DEPARTMENT_NAME		= '';
	$res_dep = $db->Execute("SELECT DEPARTMENT FROM S_STUDENT_DOCUMENTS_DEPARTMENT, M_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_DOCUMENTS_DEPARTMENT.PK_DEPARTMENT AND PK_STUDENT_DOCUMENTS = '$PK_STUDENT_DOCUMENTS' ORDER BY DEPARTMENT ASC "); 
	while (!$res_dep->EOF) { 
		if($DEPARTMENT_NAME != '')
			$DEPARTMENT_NAME .= ', ';
			
		$DEPARTMENT_NAME .= $res_dep->fields['DEPARTMENT'];
		$res_dep->MoveNext();
	}

	$index = 0;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL']);

	/*DIAM-1439 */
	$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
	$res_camp_1 = $db->Execute("SELECT CAMPUS_CODE, CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_camp_1->fields['CAMPUS_CODE']);
	/*DIAM-1439 */
		
	$index++;
	if($res->fields['BEGIN_DATE_1'] != '') {
		$cell_no = $cell[$index].$line;
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['BEGIN_DATE_1'])));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEPARTMENT_NAME);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DOCUMENT_TYPE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMP_NAME']);
	
	$index++;
	if($res->fields['REQUESTED_DATE'] != '') {
		$cell_no = $cell[$index].$line;
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['REQUESTED_DATE'])));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
	}

	$index++;
	if($res->fields['FOLLOWUP_DATE'] != '') {
		$cell_no = $cell[$index].$line;
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['FOLLOWUP_DATE'])));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
	}
	
	$index++;
	if($res->fields['DATE_RECEIVED'] != '') {
		$cell_no = $cell[$index].$line;
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['DATE_RECEIVED'])));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES']);
	
	$res->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);
