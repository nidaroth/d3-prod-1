<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/ecm_ledger.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT ECM FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_TITLE_IV_SERVICER') == 0 || $res_add_on->fields['ECM'] == 0){
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
$file_name 		= 'ECM Import Result.xlsx';
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line 	= 1;	
$index 	= -1;

$heading[] = SSN;
$width[]   = 20;
$heading[] = STUDENT;
$width[]   = 20;
$heading[] = IMPORT_RESULT;
$width[]   = 20;
$heading[] = LEDGER_CODE_1;
$width[]   = 20;
$heading[] = DISBURSEMENT_DATE;
$width[]   = 20;
$heading[] = DISBURSEMENT_AMOUNT;
$width[]   = 20;
$heading[] = ECM_DISBURSEMENT_DATE;
$width[]   = 20;
$heading[] = ECM_DISBURSEMENT_AMOUNT;
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

$res_type = $db->Execute($_SESSION['query']);
while (!$res_type->EOF) {

	$line++;
	$index = -1;
	
	$PK_STUDENT_MASTER = $res_type->fields['PK_STUDENT_MASTER'];

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(my_decrypt('',$res_type->fields['SSN']));
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['MESSAGE']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['CODE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	if($res_type->fields['DISBURSEMENT_DATE'] != '' && $res_type->fields['DISBURSEMENT_DATE'] != '0000-00-00')
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("m/d/Y",strtotime($res_type->fields['DISBURSEMENT_DATE'])));
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['DISBURSEMENT_AMOUNT']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	if($res_type->fields['ECM_DISBURSEMENT_DATE'] != '' && $res_type->fields['ECM_DISBURSEMENT_DATE'] != '0000-00-00')
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("m/d/Y",strtotime($res_type->fields['ECM_DISBURSEMENT_DATE'])));
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['ECM_DISBURSEMENT_AMOUNT']);
		
	
	$res_type->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);