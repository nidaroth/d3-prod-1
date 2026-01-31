<?php require_once('../global/config.php');
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_FINANCE') == 0 ){
	header("location:../index");
	exit;
}

$reportOption      = "STUDENTS";
//$StudentOption     = $_POST['STUDENT_OPTION'];
$startDate         = date("Y-m-d", strtotime($_POST['START_DATE']));
$endDate           = date("Y-m-d", strtotime($_POST['END_DATE']));

$pkCampus      = "";
if (!empty($_POST['PK_CAMPUS'])) {
   //$pkCampus      = implode(",", $_POST['PK_CAMPUS']); 
	$pkCampus      = $_POST['PK_CAMPUS'];	
}
include '../global/excel-v7.4/PHPExcel/IOFactory.php';
//include '../global/excel/Classes/PHPExcel/IOFactory.php';
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

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';

$file_name 	= 'fvt_ge_students_'.time().'.xlsx';
	
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  
	
$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->setTitle("Upload File");
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line 	= 1;	
$index 	= -1;


$sSP_CALL = "CALL COMP70001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "')";
// echo $sSP_CALL;exit;
$res = $db->Execute($sSP_CALL);

$heading = array_keys($res->fields);
foreach ($heading as $key) 
{
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
	$objPHPExcel->getActiveSheet()->freezePane('A1');
}
while (!$res->EOF){
	$index = -1;
	$line++;
	foreach ($heading as $key) 
	{
	$index++;
	$cell_no = $cell[$index].$line;
	$cellValue=$res->fields[$key];

	
		if ($key == 'Student Social Security Number (AA, TA)') {
			$SSN 		= $res->fields['Student Social Security Number (AA, TA)'];
			$cellValue 	= str_replace('-','',my_decrypt('', $SSN));
		}

		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getAlignment()->setWrapText(true)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);		

	}
	$res->MoveNext();
} 
$objPHPExcel->getActiveSheet()->freezePane('A1');
// $objWriter->save($outputFileName);
// $objPHPExcel->disconnectWorksheets();
// header("location:".$outputFileName);


$excelpath = $objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
//header("location:".$outputFileName);


$response =  array(
	'filename' => $file_name,
	'hrefpath' => $outputFileName
);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit;