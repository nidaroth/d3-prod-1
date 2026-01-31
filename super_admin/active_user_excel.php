<?php 
ini_set("memory_limit","3000M");
ini_set("max_execution_time","600");
require_once('../global/config.php');


include '../global/excel/Classes/PHPExcel/IOFactory.php';
$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$total_fields = 320;
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

$dir 			= '../school/temp/';
$inputFileType  = 'Excel2007';

$outputFileName = $dir."active_user.xlsx";

$where = " Z_ACTIVE_USERS.PK_ACCOUNT != 1 ";

$PK_ACCOUNT = $_GET['acc'];
$USER_TYPE 	= $_GET['type'];
$START_DATE = $_GET['st'];
$TO_DATE 	= $_GET['ed'];

if($START_DATE != '' && $TO_DATE != '') {
	$ST = date("Y-m-d",strtotime($START_DATE));
	$ET = date("Y-m-d",strtotime($TO_DATE));
	$where .= " AND DATE BETWEEN '$ST' AND '$ET' ";
} else if($START_DATE != ''){
	$ST = date("Y-m-d",strtotime($START_DATE));
	$where .= " AND DATE >= '$ST' ";
} else if($TO_DATE != ''){
	$ET = date("Y-m-d",strtotime($TO_DATE));
	$where .= " AND DATE <= '$ET' ";
}

if($PK_ACCOUNT != '')
	$where .= " AND Z_ACTIVE_USERS.PK_ACCOUNT = '$PK_ACCOUNT' ";

if($USER_TYPE != '')
	$where .= " AND USER_TYPE  = '$USER_TYPE' ";
	
$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line 	= 1;	
$index 	= -1;
$i		= 0;

$sheet = $objPHPExcel->createSheet(0);
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle('Summary');
$heading = array();
$width  = array();

$heading[] = 'School Name';
$width[]   = 20;
if($USER_TYPE == '' || $USER_TYPE == 1){
	$heading[] = 'School User';
	$width[]   = 20;
}
if($USER_TYPE == '' || $USER_TYPE == 2){
	$heading[] = 'Faculty';
	$width[]   = 20;
}
 if($USER_TYPE == '' || $USER_TYPE == 3){
	$heading[] = 'Student';
	$width[]   = 20;
}
if($USER_TYPE == ''){
	$heading[] = 'School Total';
	$width[]   = 20;
}

foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	$i++;
}
$objPHPExcel->getActiveSheet()->freezePane('A2');

//////////////

$cond = " PK_ACCOUNT != 1 ";
if($PK_ACCOUNT != '')
	$cond .= " AND PK_ACCOUNT = '$PK_ACCOUNT' ";
											
$total_school_user  = 0; 
$total_school_faculty  = 0; 
$total_school_student  = 0; 
$total_school_all_user = 0; 
$res_dep = $db->Execute("select PK_ACCOUNT,SCHOOL_NAME from Z_ACCOUNT WHERE $cond  ORDER BY SCHOOL_NAME ASC ");
while (!$res_dep->EOF) { 
	$PK_ACCOUNT = $res_dep->fields['PK_ACCOUNT'];
	
	$res_sc_user = $db->Execute("SELECT PK_ACTIVE_USERS FROM Z_ACTIVE_USERS LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_ACTIVE_USERS.PK_ACCOUNT  WHERE $where AND Z_ACTIVE_USERS.PK_ACCOUNT = '$PK_ACCOUNT' AND USER_TYPE = '1' GROUP BY PK_USER ");  
	$res_faculty = $db->Execute("SELECT PK_ACTIVE_USERS FROM Z_ACTIVE_USERS LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_ACTIVE_USERS.PK_ACCOUNT  WHERE $where AND Z_ACTIVE_USERS.PK_ACCOUNT = '$PK_ACCOUNT' AND USER_TYPE = '2' GROUP BY PK_USER ");
	$res_student = $db->Execute("SELECT PK_ACTIVE_USERS FROM Z_ACTIVE_USERS LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_ACTIVE_USERS.PK_ACCOUNT  WHERE $where AND Z_ACTIVE_USERS.PK_ACCOUNT = '$PK_ACCOUNT' AND USER_TYPE = '3' GROUP BY PK_USER ");
	
	$total_school 			= $res_sc_user->RecordCount() + $res_faculty->RecordCount() + $res_student->RecordCount();
	$total_school_user 	   += $res_sc_user->RecordCount(); 
	$total_school_faculty  += $res_faculty->RecordCount(); 
	$total_school_student  += $res_student->RecordCount(); 
	$total_school_all_user += $total_school; 
	
	$line++;
	$index = 0;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_dep->fields['SCHOOL_NAME']);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	
	if($USER_TYPE == '' || $USER_TYPE == 1){
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sc_user->RecordCount());
	}
	
	if($USER_TYPE == '' || $USER_TYPE == 2){
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_faculty->RecordCount());
	}
	
	if($USER_TYPE == '' || $USER_TYPE == 3){
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_student->RecordCount());
	}
	
	if($USER_TYPE == ''){
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($total_school);
	}
	
	$res_dep->MoveNext();
}

/////////////////////////////////////////////////////////////////
	
$sheet = $objPHPExcel->createSheet(1);
$objPHPExcel->setActiveSheetIndex(1);
$objPHPExcel->getActiveSheet()->setTitle('Detail');

$heading = array();
$width  = array();
$heading[] = 'Date';
$width[]   = 20;
$heading[] = 'School Name';
$width[]   = 20;
$heading[] = 'User Type';
$width[]   = 20;
$heading[] = 'Name';
$width[]   = 20;
$heading[] = 'User ID';
$width[]   = 20;
$i = 0;
$line 	= 1;	
$index 	= -1;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	$i++;
}
$objPHPExcel->getActiveSheet()->freezePane('A2');
	
$res_type = $db->Execute("SELECT Z_ACTIVE_USERS.PK_ACCOUNT,PK_ACTIVE_USERS,DATE,CONCAT(LAST_NAME,' ',FIRST_NAME) as NAME, IF(USER_TYPE = 1, 'School User',IF(USER_TYPE = 2, 'Faculty',IF(USER_TYPE = 3, 'Student',''))) as USER_TYPE, SCHOOL_NAME, LOGIN_ID FROM Z_ACTIVE_USERS LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_ACTIVE_USERS.PK_ACCOUNT WHERE $where ORDER BY SCHOOL_NAME ASC, CONCAT(LAST_NAME,' ',FIRST_NAME) ASC");
while (!$res_type->EOF){
	$line++;
	$index = -1;
	$PK_STUDENT_ENROLLMENT	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
	
	$index++;
	$cell_no = $cell[$index].$line;
	if($res_type->fields['DATE'] != '0000-00-00'){ 
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_type->fields['DATE'])));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14);
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['SCHOOL_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['USER_TYPE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['LOGIN_ID']);
	
	$res_type->MoveNext();
}

$objPHPExcel->setActiveSheetIndex(0);
$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);
	