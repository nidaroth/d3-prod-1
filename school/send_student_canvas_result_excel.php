<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
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

$CHK_PK_STUDENT_MASTER		= isset($_REQUEST['CHK_PK_STUDENT_MASTER']) ? ($_REQUEST['CHK_PK_STUDENT_MASTER']) : '';

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
$file_name 		= 'Canvas_Send_Student.xlsx';
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

$heading[] = CAMPUS;
$width[]   = 20;
$heading[] = STUDENT;
$width[]   = 20;
$heading[] = STUDENT_ID;
$width[]   = 20;
$heading[] = EMAIL;
$width[]   = 20;
$heading[] = FIRST_TERM;
$width[]   = 20;
$heading[] = PROGRAM;
$width[]   = 20;
$heading[] = STATUS;
$width[]   = 20;
$heading[] = SENT_ON;
$width[]   = 20;
$heading[] = SENT_BY;
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

$where = " S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND ARCHIVED = 0  AND IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT   AND S_COURSE_OFFERING.LMS_ACTIVE = '1' AND  S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_TERM_MASTER.LMS_ACTIVE = '1' AND STUDENT_ID != '' ";

if(!empty($CHK_PK_STUDENT_MASTER))
{
	$where .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER IN (".$CHK_PK_STUDENT_MASTER.") ";
}

$query_canvas_send_student = "SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER,PK_REPRESENTATIVE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS NAME,S_STUDENT_MASTER.ARCHIVED,PK_STUDENT_STATUS_MASTER, STUDENT_ID, S_TERM_MASTER.BEGIN_DATE,STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE AS PROGRAM, EMAIL, CAMPUS_CODE,S_COURSE_OFFERING.CO_EXTERNAL_ID AS EXTERNAL_ID, S_STUDENT_ACADEMICS.STUDENT_ID AS STUDENT_ID, S_STUDENT_CONTACT.EMAIL AS EMAIL
FROM 
S_STUDENT_MASTER
LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 
, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS
, S_STUDENT_COURSE, S_COURSE_OFFERING  
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS 
WHERE " . $where. " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ORDER BY CAMPUS_CODE ASC, S_STUDENT_MASTER.LAST_NAME ASC, S_STUDENT_MASTER.FIRST_NAME ASC";

$res_type = $db->Execute($query_canvas_send_student);
while (!$res_type->EOF) {

	$PK_STUDENT_MASTER 		= $res_type->fields['PK_STUDENT_MASTER'];
	$PK_STUDENT_ENROLLMENT 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
	$res1 = $db->Execute("SELECT SUCCESS, S_STUDENT_MASTER_CANVAS.CREATED_ON, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME,MESSAGE, IF(S_STUDENT_MASTER_CANVAS.SUCCESS = 1,'Success','Failed') as STATUS FROM S_STUDENT_MASTER_CANVAS LEFT JOIN Z_USER ON Z_USER.PK_USER = S_STUDENT_MASTER_CANVAS.CREATED_BY AND PK_USER_TYPE IN (1,2) LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY S_STUDENT_MASTER_CANVAS.CREATED_ON DESC ");	
	
	$SENT = 'N';
	if($res1->RecordCount() > 0)
		$SENT = 'Y';
	
	// $CAMPUS = "";
	// $res_campus = $db->Execute("SELECT OFFICIAL_CAMPUS_NAME FROM S_CAMPUS, S_STUDENT_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
	// while (!$res_campus->EOF) { 
	// 	if($CAMPUS != '')
	// 		$CAMPUS .= ', ';
			
	// 	$CAMPUS .= $res_campus->fields['OFFICIAL_CAMPUS_NAME'];
	// 	$res_campus->MoveNext();
	// }
	
	$line++;
	$index = -1;

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['CAMPUS_CODE']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['NAME']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STUDENT_ID']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['EMAIL']);

	if($res_type->fields['BEGIN_DATE'] != '' && $res_type->fields['BEGIN_DATE'] != '0000-00-00')
		$BEGIN_DATE = date("m/d/Y",strtotime($res_type->fields['BEGIN_DATE']));
	else
		$BEGIN_DATE = '';
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BEGIN_DATE);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['PROGRAM']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STUDENT_STATUS']);	
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(convert_to_user_date($res1->fields['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get()));
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res1->fields['NAME']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res1->fields['MESSAGE']);
	
	$res_type->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);

?>