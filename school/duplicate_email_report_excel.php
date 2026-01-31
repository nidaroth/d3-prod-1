<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/duplicate_email_report.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
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
$file_name 		= 'Duplicate-Email.xlsx';
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

$heading[] = EMAIL;
$width[]   = 20;
$heading[] = LAST_NAME;
$width[]   = 20;
$heading[] = FIRST_NAME;
$width[]   = 20;
$heading[] = STUDENT_ID;
$width[]   = 20;
$heading[] = STATUS;
$width[]   = 20;
$heading[] = ARCHIVED;
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

$res_email = $db->Execute($_SESSION['QUERY']);
while (!$res_email->EOF) {
	$EMAIL 	 = $res_email->fields['EMAIL'];

	$res_type = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, FIRST_NAME,LAST_NAME, STUDENT_ID, If(ARCHIVED = 1,'Yes', 'No') as ARCHIVED FROM S_STUDENT_MASTER, S_STUDENT_CONTACT, S_STUDENT_ACADEMICS WHERE EMAIL = '$EMAIL' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 AND S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC"); 
	while (!$res_type->EOF){
		$line++;
		
		$PK_STUDENT_MASTER = $res_type->fields['PK_STUDENT_MASTER']; 
		$res_enroll = $db->Execute("select PK_STUDENT_ENROLLMENT, STUDENT_STATUS FROM S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS = M_STUDENT_STATUS.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
		
		$index = -1;
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($EMAIL);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['LAST_NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['FIRST_NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STUDENT_ID']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['STUDENT_STATUS']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['ARCHIVED']);
		
		$res_type->MoveNext();
	}
	$res_email->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);