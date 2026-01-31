<?php require_once('../global/config.php');

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
$file_name 		= 'Release Notes.xlsx';
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

$heading[] = 'Type';
$width[]   = 20;
$heading[] = 'Category';
$width[]   = 20;
$heading[] = 'Date Programming Pushed to D3';
$width[]   = 20;
$heading[] = 'Subject';
$width[]   = 20;
$heading[] = 'Location';
$width[]   = 20;
$heading[] = 'Release Notes Pushed';
$width[]   = 20;
$heading[] = 'Date Release Notes Pushed to D3';
$width[]   = 20;
$heading[] = 'Knowledge Base ID';
$width[]   = 20;
$heading[] = 'Programming Notes';
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

$res_type = $db->Execute($_SESSION['REL_QRY']." ORDER BY RELEASE_NOTES_PUSHED ASC, PUSHED_TO_D3_DATE ASC, RELEASE_TYPE ASC, SUBJECT ASC, LOCATION ASC "); 
while (!$res_type->EOF){
	$line++;
	
	$index = -1;
	
	$PK_RELEASE_CATEGORY = $res_type->fields['PK_RELEASE_CATEGORY'];
	$CATEGORY = '';
	$res_cat = $db->Execute("select RELEASE_CATEGORY from M_RELEASE_CATEGORY WHERE PK_RELEASE_CATEGORY IN ($PK_RELEASE_CATEGORY) ORDER BY RELEASE_CATEGORY ASC");
	while (!$res_cat->EOF) { 
		if($CATEGORY != '')
			$CATEGORY .= ", ";
			
		$CATEGORY .= $res_cat->fields['RELEASE_CATEGORY'];
		
		$res_cat->MoveNext();
	}
	
	if($res_type->fields['PUSHED_TO_D3_DATE'] != '0000-00-00')
		$PUSHED_TO_D3_DATE = date("m/d/Y",strtotime($res_type->fields['PUSHED_TO_D3_DATE']));
	else
		$PUSHED_TO_D3_DATE = '';
		
	if($res_type->fields['RELEASE_NOTES_PUSHED_DATE'] != '0000-00-00')
		$RELEASE_NOTES_PUSHED_DATE = date("m/d/Y",strtotime($res_type->fields['RELEASE_NOTES_PUSHED_DATE']));
	else
		$RELEASE_NOTES_PUSHED_DATE = '';	

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['RELEASE_TYPE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CATEGORY);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($PUSHED_TO_D3_DATE);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['SUBJECT']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['LOCATION']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['RELEASE_NOTES_PUSHED']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($RELEASE_NOTES_PUSHED_DATE);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['KNOWLEDGEBASE_URL']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['PROGRAMMING_NOTES']);

	$res_type->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);