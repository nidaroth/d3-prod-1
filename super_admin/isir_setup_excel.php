<? require_once("../global/config.php"); 
require_once("../language/common.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
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

$dir 			= '../school/temp/';
$inputFileType  = 'Excel2007';
$file_name 		= 'ISIR Setup.xlsx';
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

$heading[] = 'Field #';
$width[]   = 20;
$heading[] = 'Heading';
$width[]   = 20;
$heading[] = 'Start';
$width[]   = 20;
$heading[] = 'End';
$width[]   = 20;
$heading[] = 'DSIS Fields';
$width[]   = 20;
$heading[] = 'Has Legend';
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

$res = $db->Execute("select FIELD_NO, START, END, HEADING, DSIS_FIELD_NAME, FIELD_NAME, IF(HAS_LEDGEND = 1,'Yes', '') as HAS_LEDGEND from Z_ISIR_SETUP_DETAIL LEFT JOIN Z_ISIR_DSIS_FIELDS ON Z_ISIR_DSIS_FIELDS.DSIS_FIELD = Z_ISIR_SETUP_DETAIL.DSIS_FIELD_NAME WHERE PK_ISIR_SETUP_MASTER = '$_GET[id]' ORDER BY PK_ISIR_SETUP_DETAIL");
while (!$res->EOF) {

		$line++;
		$index = -1;

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FIELD_NO']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HEADING']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['START']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['END']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FIELD_NAME']);
	
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HAS_LEDGEND']);

	$res->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);