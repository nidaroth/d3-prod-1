<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
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
$file_name 		= 'Attendance.xlsx';
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

$heading[] = STUDENT_NAME;
$width[]   = 20;
$heading[] = COURSE;
$width[]   = 20;
$heading[] = TYPE;
$width[]   = 20;
if($_GET['detail_view'] == 1) {
	$heading[] = CLASS_DATE;
	$width[]   = 20;
	$heading[] = START_TIME;
	$width[]   = 20;
	$heading[] = END_TIME;
	$width[]   = 20;
	$heading[] = HOURS;
	$width[]   = 20;
	$heading[] = COMPLETED;
	$width[]   = 20;
	$heading[] = ATT_CODE;
	$width[]   = 20;
}
$heading[] = ATT_HOUR;
$width[]   = 20;

/* Ticket # 1601 */
$res_att_act = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES, ENABLE_ATTENDANCE_COMMENTS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_att_act->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){
	$heading[] = 'Activity Type';
	$width[]   = 20;
}

if($res_att_act->fields['ENABLE_ATTENDANCE_COMMENTS'] == 1){
	$heading[] = 'Comments';
	$width[]   = 20;
}
/* Ticket # 1601 */

$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	$i++;
}	

$objPHPExcel->getActiveSheet()->freezePane('A2');

$res_type = $db->Execute($_SESSION['query']);
while (!$res_type->EOF) {
	if($_GET['show_inactive'] == 1 || ($_GET['show_inactive'] == 0 && $res_type->fields['ATTENDANCE_CODE'] != 'I') ){ 

		$line++;
		$index = -1;
		
		if($_GET['detail_view'] == 1) 
			$COURSE = $res_type->fields['COURSE_CODE']; 
		else 
			$COURSE = $res_type->fields['COURSE_CODE'].' ('. $res_type->fields['SESSION'].' - '. $res_type->fields['SESSION_NO'].')';
			
		if($_GET['detail_view'] == 1) 
			$TYPE = $res_type->fields['SCHEDULE_TYPE']; 
		else 
			$TYPE = "Summary";

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STUD_NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($COURSE);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TYPE);
		
		if($_GET['detail_view'] == 1) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['SCHEDULE_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['START_TIME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['END_TIME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['HOURS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['COMPLETED']);
			
			if($res_type->fields['COMPLETED_1'] == 1 || $res_type->fields['PK_SCHEDULE_TYPE'] == 2 || $res_type->fields['ATTENDANCE_CODE'] == 'I')
				$ATTENDANCE_CODE = $res_type->fields['ATTENDANCE_CODE'];
			else
				$ATTENDANCE_CODE = '';
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ATTENDANCE_CODE);
		}
		
		if($res_type->fields['COMPLETED_1'] == 1 || $res_type->fields['PK_SCHEDULE_TYPE'] == 2 || $res_type->fields['ATTENDANCE_CODE'] == 'I') 
			$ATTENDANCE_HOURS = $res_type->fields['ATTENDANCE_HOURS'];
		else
			$ATTENDANCE_HOURS = '';
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ATTENDANCE_HOURS);
		
		/* Ticket # 1601 */
		if($res_att_act->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['ATTENDANCE_ACTIVITY_TYPE']);
		}

		if($res_att_act->fields['ENABLE_ATTENDANCE_COMMENTS'] == 1){
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['ATTENDANCE_COMMENTS']);
		}
		/* Ticket # 1601 */
	}
	
	$res_type->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);