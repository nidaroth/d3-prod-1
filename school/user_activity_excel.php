<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/user_activity.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0){
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
$file_name 		= 'User Activity.xlsx';
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

$heading[] = USER_TYPE;
$width[]   = 20;
$heading[] = USER_NAME;
$width[]   = 20;
$heading[] = LOGIN_ID;
$width[]   = 20;
$heading[] = LOGIN_TIME;
$width[]   = 30;
$heading[] = LOGOUT_TIME;
$width[]   = 30;

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
		
		$LOGIN_TIME = convert_to_user_date($res->fields['LOGIN_TIME'],'l, M d, Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
	
		if($res->fields['LOGOUT_TIME'] == '' || $res->fields['LOGOUT_TIME'] == '0000-00-00 00:00:00')
			$LOGOUT_TIME = 'User did not log out';
		else
			$LOGOUT_TIME = convert_to_user_date($res->fields['LOGOUT_TIME'],'l, M d, Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());

		$line++;
		$index = -1;

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ROLES']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['USER_ID']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($LOGIN_TIME);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($LOGOUT_TIME);

	$res->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);