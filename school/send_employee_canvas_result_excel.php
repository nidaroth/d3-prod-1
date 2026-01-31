<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/employee.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT ENABLE_CANVAS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ENABLE_CANVAS'] == 0) {
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

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

$CHK_PK_EMPLOYEE_MASTER		= isset($_REQUEST['CHK_PK_EMPLOYEE_MASTER']) ? ($_REQUEST['CHK_PK_EMPLOYEE_MASTER']) : '';

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
$file_name 		= 'Canvas_Send_Instructor.xlsx';
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line 	= 1;	
$index 	= -1;

$heading[] = CAMPUS;
$width[]   = 20;
$heading[] = INSTRUCTOR;
$width[]   = 20;
$heading[] = EMAIL;
$width[]   = 20;
$heading[] = SENT_ON;
$width[]   = 20;
$heading[] = SENT_BY;
$width[]   = 20;
$heading[] = SENT;
$width[]   = 20;
$heading[] = MESSAGE;
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

$where = "";
if(!empty($CHK_PK_EMPLOYEE_MASTER))
{
	$where .= " AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER IN (".$CHK_PK_EMPLOYEE_MASTER.") ";
}

$sQuery_Final = "SELECT CAMPUS_CODE
						,S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER
						,S_EMPLOYEE_MASTER.EMAIL AS EMAIL
						,CONCAT(S_EMPLOYEE_MASTER.LAST_NAME, ', ', S_EMPLOYEE_MASTER.FIRST_NAME) AS INSTRUCTOR_NAME 
					FROM 
						S_EMPLOYEE_MASTER 
						LEFT JOIN S_EMPLOYEE_MASTER_CANVAS ON S_EMPLOYEE_MASTER_CANVAS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
						-- LEFT JOIN S_EMPLOYEE_COURSE_CANVAS ON S_EMPLOYEE_COURSE_CANVAS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER_CANVAS.PK_EMPLOYEE_MASTER 
						-- LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_EMPLOYEE_COURSE_CANVAS.PK_COURSE_OFFERING 
						LEFT JOIN S_EMPLOYEE_CAMPUS ON S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER
						LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS
					WHERE 
						S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
						AND S_EMPLOYEE_MASTER.IS_FACULTY = 1
						$where
					GROUP BY 
						S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER
					ORDER BY 
						CAMPUS_CODE ASC, 
						S_EMPLOYEE_MASTER.LAST_NAME ASC, 
						S_EMPLOYEE_MASTER.FIRST_NAME ASC, 
						S_EMPLOYEE_MASTER_CANVAS.CREATED_ON ASC ";

$res_type = $db->Execute($sQuery_Final);
while (!$res_type->EOF) {
	$PK_EMPLOYEE_MASTER = $res_type->fields['PK_EMPLOYEE_MASTER'];
	$res1 = $db->Execute("SELECT SUCCESS, S_EMPLOYEE_MASTER_CANVAS.CREATED_ON, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME,MESSAGE FROM S_EMPLOYEE_MASTER_CANVAS LEFT JOIN Z_USER ON Z_USER.PK_USER = S_EMPLOYEE_MASTER_CANVAS.CREATED_BY AND PK_USER_TYPE IN (1,2) LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE  S_EMPLOYEE_MASTER_CANVAS.PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ORDER BY S_EMPLOYEE_MASTER_CANVAS.CREATED_ON DESC");	

	$res_campus = $db->Execute("SELECT GROUP_CONCAT(CAMPUS_CODE) AS CAMPUS_CODE FROM S_CAMPUS, S_EMPLOYEE_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS  AND PK_EMPLOYEE_MASTER = '".$PK_EMPLOYEE_MASTER."' ORDER BY CAMPUS_CODE ASC ");

	$CAMPUS_CODE = $res_campus->fields['CAMPUS_CODE'];

	$line++;
	$index = -1;

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CAMPUS_CODE);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['INSTRUCTOR_NAME']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['EMAIL']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(convert_to_user_date($res1->fields['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get()));

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res1->fields['NAME']);
	
	$SENT = 'N';
	if($res1->RecordCount() > 0)
		$SENT = 'Y';
		
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SENT);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res1->fields['MESSAGE']);
	
	$res_type->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);