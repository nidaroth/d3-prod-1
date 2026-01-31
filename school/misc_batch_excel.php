<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/misc_batch.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0){
	header("location:../index");
	exit;
}

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}
$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

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
$file_name 		= 'Misc Batch.xlsx';
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

$heading[] = BATCH_NO;
$width[]   = 20;
$heading[] = CAMPUS;
$width[]   = 20;
$heading[] = BATCH_STATUS;
$width[]   = 20;
$heading[] = BATCH_DATE;
$width[]   = 20;
$heading[] = POSTED_DATE;
$width[]   = 20;
$heading[] = DESCRIPTION;
$width[]   = 40;
$heading[] = BATCH_AMOUNT.'('.DEBIT.')';
$width[]   = 50;
$heading[] = BATCH_AMOUNT.'('.CREDIT.')';
$width[]   = 50;
$heading[] = BATCH_TOTAL.'('.DEBIT.' - '.CREDIT.')';
$width[]   = 60;



$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
}	

$objPHPExcel->getActiveSheet()->freezePane('A2');

$res = $db->Execute($_SESSION['QUERY']);

while (!$res->EOF) {
		
		$BATCH_NO 		= $res->fields['BATCH_NO'];
		$BATCH_STATUS	= $res->fields['BATCH_STATUS'];
		if($res->fields['BATCH_DATE'] != '' && $res->fields['BATCH_DATE'] != '0000-00-00')
			$BATCH_DATE = date('m/d/Y',strtotime($res->fields['BATCH_DATE']));
		else
			$BATCH_DATE = '';

		$DESCRIPTION 	= $res->fields['DESCRIPTION'];
		$CREDIT			= $res->fields['CREDIT'];
		$DEBIT 			= $res->fields['DEBIT'];	
			
		if($res->fields['POSTED_DATE'] != '' && $res->fields['POSTED_DATE'] != '0000-00-00')
			$POSTED_DATE = date('m/d/Y',strtotime($res->fields['POSTED_DATE']));
		else
			$POSTED_DATE = '';
			
		$BALANCE  		= $res->fields['BALANCE'];
		$MISC_BATCH_PK_CAMPUS = $res->fields['MISC_BATCH_PK_CAMPUS'];

		$CAMPUS_STR = '';
		$res_type = $db->Execute("select CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($MISC_BATCH_PK_CAMPUS)  order by CAMPUS_CODE ASC");
		while (!$res_type->EOF) {
			if ($CAMPUS_STR != '')
				$CAMPUS_STR .= ", ";
			$CAMPUS_STR .= $res_type->fields['CAMPUS_CODE'];
			$res_type->MoveNext();
		}



		$line++;
		$index = -1;

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BATCH_NO);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CAMPUS_STR);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BATCH_STATUS);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BATCH_DATE);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($POSTED_DATE);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DESCRIPTION);		

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEBIT);	

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CREDIT);	

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BALANCE);

	$res->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);