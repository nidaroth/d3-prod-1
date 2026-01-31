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
$file_name 		= 'Tickets.xlsx';
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

$heading[] = 'School';
$width[]   = 20;
$heading[] = 'Ticket #';
$width[]   = 20;
$heading[] = 'Status';
$width[]   = 20;
$heading[] = 'Category';
$width[]   = 20;
$heading[] = 'Subject';
$width[]   = 20;
$heading[] = 'Priority';
$width[]   = 20;
$heading[] = 'Created On';
$width[]   = 20;
$heading[] = 'Created By';
$width[]   = 20;
$heading[] = 'Due Date';
$width[]   = 20;
$heading[] = 'Last Update On';
$width[]   = 20;
$heading[] = 'Last Update By';
$width[]   = 20;
$heading[] = 'Date Closed';
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

$res_type = $db->Execute($_SESSION['TICKET_Q']); 
while (!$res_type->EOF){
	$line++;
	
	$index = -1;
	
	$PK_TICKET 	 = $res_type->fields['PK_TICKET'];
	$INTERNAL_ID = $res_type->fields['INTERNAL_ID'];

	$res_ticket4 = $db->Execute("SELECT Z_TICKET.CREATED_ON,CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE INTERNAL_ID = '$INTERNAL_ID' AND IS_PARENT = 0 ORDER BY PK_TICKET DESC"); 
	$res_ticket5 = $db->Execute("SELECT CHANGED_ON,CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME FROM Z_TICKET_STATUS_CHANGE_HISTORY LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET_STATUS_CHANGE_HISTORY.CHANGED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE INTERNAL_ID = '$INTERNAL_ID' ORDER BY PK_TICKET_STATUS_CHANGE_HISTORY DESC"); 
	
	$LAST_UPDATE_ON = '';
	$LAST_UPDATE_BY = '';
	if(strtotime($res_ticket4->fields['CREATED_ON']) > strtotime($res_ticket5->fields['CHANGED_ON'])) {
		$LAST_UPDATE_ON = date("Y-m-d",strtotime($res_ticket4->fields['CREATED_ON']));
		$LAST_UPDATE_BY = $res_ticket4->fields['NAME'];
	} else {
		$LAST_UPDATE_ON = date("Y-m-d",strtotime($res_ticket5->fields['CHANGED_ON']));
		$LAST_UPDATE_BY = $res_ticket5->fields['NAME'];
	}

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['SCHOOL_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['TICKET_NO']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['TICKET_STATUS']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['TICKET_CATEGORY']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['SUBJECT']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['TICKET_PRIORITY']);
	
	if($res_type->fields['CREATED_DATE'] != '0000-00-00')
		$CREATED_DATE = date("Y-m-d",strtotime($res_type->fields['CREATED_DATE']));
	else
		$CREATED_DATE = '';
		
	$index++;
	$cell_no = $cell[$index].$line;
	if($CREATED_DATE != '0000-00-00'){
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $CREATED_DATE)));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14);
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	if($res_type->fields['DUE_DATE'] != '0000-00-00'){
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_type->fields['DUE_DATE'])));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14);
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	if($LAST_UPDATE_ON != '0000-00-00' && $LAST_UPDATE_ON != '' ){
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $LAST_UPDATE_ON)));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14);
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($LAST_UPDATE_BY);
	
	$index++;
	$cell_no = $cell[$index].$line;
	if($res_type->fields['CLOSED_DATE'] != '0000-00-00' && $res_type->fields['CLOSED_DATE'] != ''){
		$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_type->fields['CLOSED_DATE'])));
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14);
	}
	
	$res_type->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);