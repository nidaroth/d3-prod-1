<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/duplicate_email_report.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
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
$file_name 		= 'Course Master List.xlsx';
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

$heading[] = 'Course Code';
$width[]   = 20;
$heading[] = 'Transcript Code';
$width[]   = 20;
$heading[] = 'Course Description';
$width[]   = 20;
$heading[] = 'FA Units';
$width[]   = 20;
$heading[] = 'Units';
$width[]   = 20;
$heading[] = 'Hours';
$width[]   = 20;
$heading[] = 'Active Status';
$width[]   = 20;
$heading[] = 'Class Size';
$width[]   = 20;
$heading[] = 'Default Att. Code';
$width[]   = 20;
$heading[] = 'Course Fees';
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

$res = $db->Execute($_SESSION['query']." ORDER BY S_COURSE.ACTIVE DESC, COURSE_CODE ASC ");
while (!$res->EOF) {
	
	$line++;
	$index = -1;

	$PK_COURSE 	= $res->fields['PK_COURSE'];
	$res_fee 	= $db->Execute("SELECT SUM(FEE_AMT) as FEE_AMT FROM S_COURSE_FEE WHERE PK_COURSE = '$PK_COURSE' ");
	$FEE 		= $res_fee->fields['FEE_AMT'];
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COURSE_CODE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TRANSCRIPT_CODE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COURSE_DESCRIPTION']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FA_UNITS']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['UNITS']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HOURS']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ACTIVE_1']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['MAX_CLASS_SIZE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CODE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FEE);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode("0.00");
	
	$res->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);