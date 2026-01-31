<? require_once("../global/config.php"); 


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
$file_name 		= 'Student Change log.xlsx';
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

$heading[] = "PK_STUDENT_TRACK_CHANGES";
$width[]   = 20;
$heading[] = "PK_ACCOUNT";
$width[]   = 20;
$heading[] = "SCHOOL_NAME";
$width[]   = 20;
$heading[] = "LAST_NAME";
$width[]   = 20;
$heading[] = "FIRST_NAME";
$width[]   = 20;
$heading[] = "MIDDLE_NAME";
$width[]   = 20;
$heading[] = "PK_STUDENT_MASTER";
$width[]   = 20;
$heading[] = "PK_STUDENT_ENROLLMENT";
$width[]   = 20;
$heading[] = "OLD_VALUE";
$width[]   = 20;
$heading[] = "OLD_VALUE - PK_TERM_MASTER";
$width[]   = 20;
$heading[] = "NEW_VALUE";
$width[]   = 20;
$heading[] = "NEW_VALUE - PK_TERM_MASTER";
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

$res_type = $db->Execute("SELECT PK_STUDENT_TRACK_CHANGES, S_STUDENT_TRACK_CHANGES.PK_ACCOUNT, SCHOOL_NAME, LAST_NAME, FIRST_NAME, MIDDLE_NAME, S_STUDENT_TRACK_CHANGES.PK_STUDENT_MASTER, S_STUDENT_TRACK_CHANGES.PK_STUDENT_ENROLLMENT,  OLD_VALUE, NEW_VALUE  FROM S_STUDENT_TRACK_CHANGES, Z_ACCOUNT, S_STUDENT_MASTER 
WHERE 
S_STUDENT_TRACK_CHANGES.PK_ACCOUNT = Z_ACCOUNT.PK_ACCOUNT AND 
S_STUDENT_TRACK_CHANGES.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
`FIELD_NAME` = 'First Term Date' and DATE_FORMAT(CHANGED_ON,'%Y-%m-%d') >= '2022-08-01' AND OLD_VALUE != '' 
order BY S_STUDENT_TRACK_CHANGES.PK_ACCOUNT, PK_STUDENT_ENROLLMENT ASC, PK_STUDENT_TRACK_CHANGES ASC ");
while (!$res_type->EOF) {
	$PK_ACCOUNT = $res_type->fields['PK_ACCOUNT'];
	$OLD_VALUE  = $res_type->fields['OLD_VALUE'];
	$NEW_VALUE  = $res_type->fields['NEW_VALUE'];
	
	$res1 = $db->Execute("SELECT GROUP_CONCAT(PK_TERM_MASTER SEPARATOR ', ') as PK_TERM_MASTER FROM S_TERM_MASTER WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y') = '$OLD_VALUE' ");
	$OLD_VALUE_PK = $res1->fields['PK_TERM_MASTER'];
	
	$res1 = $db->Execute("SELECT GROUP_CONCAT(PK_TERM_MASTER SEPARATOR ', ') as PK_TERM_MASTER FROM S_TERM_MASTER WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y') = '$NEW_VALUE' ");
	$NEW_VALUE_PK = $res1->fields['PK_TERM_MASTER'];
	
	$line++;
	$index = -1;

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['PK_STUDENT_TRACK_CHANGES']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['PK_ACCOUNT']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['SCHOOL_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['LAST_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['FIRST_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['MIDDLE_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['PK_STUDENT_MASTER']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['PK_STUDENT_ENROLLMENT']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['OLD_VALUE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($OLD_VALUE_PK);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['NEW_VALUE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($NEW_VALUE_PK);
	
	$res_type->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);