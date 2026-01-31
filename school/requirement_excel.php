<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");
require_once("get_department_from_t.php");

if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
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
$file_name 		= 'Requirements.xlsx';
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle("Documents");

$line  = 1;
$index = -1;
$heading = array();
$width 	 = array();
$heading[] = 'Student Name';
$width[]   = 20;
$heading[] = 'Status';
$width[]   = 30;
$heading[] = 'Enrollment';
$width[]   = 15;
$heading[] = 'Department';
$width[]   = 15;
$heading[] = 'Requested';
$width[]   = 15;
$heading[] = 'Document';
$width[]   = 20;
$heading[] = 'Employee';
$width[]   = 30;
$heading[] = 'Follow Up Date';
$width[]   = 20;
$heading[] = 'Received';
$width[]   = 15;
$heading[] = 'Date Received';
$width[]   = 30;

$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	
	$i++;
}	

$objPHPExcel->createSheet(1);
$objPHPExcel->setActiveSheetIndex(1);
$objPHPExcel->getActiveSheet()->setTitle("Enrollment Requirements");

$line_2  = 1;
$index_2 = -1;
$heading = array();
$heading = array();
$heading[] = 'Student Name';
$width[]   = 20;
$heading[] = 'Status';
$width[]   = 30;
$heading[] = 'Enrollment';
$width[]   = 15;
$heading[] = 'Requirement';
$width[]   = 15;
$heading[] = 'Type';
$width[]   = 15;
$heading[] = 'Mandatory';
$width[]   = 15;
$heading[] = 'Completed';
$width[]   = 15;
$heading[] = 'Completed On';
$width[]   = 15;

$i = 0;
foreach($heading as $title) {
	$index_2++;
	$cell_no = $cell[$index_2].$line_2;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index_2])->setWidth($width[$i]);
	
	$i++;
}

$PK_STUDENT_ENROLLMENT_ARR = explode(",",$_GET['eid']);
foreach($PK_STUDENT_ENROLLMENT_ARR as $PK_STUDENT_ENROLLMENT){
	$res_stud = $db->Execute("SELECT  S_STUDENT_MASTER.* FROM S_STUDENT_MASTER,S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER"); 
	$PK_STUDENT_MASTER 	= $res_stud->fields['PK_STUDENT_MASTER'];
	
	$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER, CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
	
	$objPHPExcel->setActiveSheetIndex(0);
	
	$doc_dep = get_department_from_t($_GET['t']);
	$res = $db->Execute("select S_STUDENT_DOCUMENTS.PK_STUDENT_DOCUMENTS,CODE,IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, DOCUMENT_TYPE, S_STUDENT_DOCUMENTS.NOTES, IF(REQUESTED_DATE = '0000-00-00', '', DATE_FORMAT(REQUESTED_DATE,'%m/%d/%Y')) AS REQUESTED_DATE_1, IF(RECEIVED = 1,'Yes', 'No') as RECEIVED, IF(DATE_RECEIVED = '0000-00-00', '',  DATE_FORMAT(DATE_RECEIVED,'%m/%d/%Y')) AS DATE_RECEIVED, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME FROM S_STUDENT_DOCUMENTS LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_DOCUMENTS.PK_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_DOCUMENTS.PK_EMPLOYEE_MASTER , S_STUDENT_DOCUMENTS_DEPARTMENT WHERE S_STUDENT_DOCUMENTS.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_DOCUMENTS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_DOCUMENTS.PK_STUDENT_DOCUMENTS = S_STUDENT_DOCUMENTS_DEPARTMENT.PK_STUDENT_DOCUMENTS AND PK_DEPARTMENT = '$doc_dep' ORDER BY REQUESTED_DATE ASC ");
	while (!$res->EOF) {
		$line++;
		$index = -1;
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['LAST_NAME']." ".$res_stud->fields['FIRST_NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['STUDENT_STATUS']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['CODE'].' - '.$res_enroll->fields['TERM_MASTER']);
	
		$PK_STUDENT_DOCUMENTS 	= $res->fields['PK_STUDENT_DOCUMENTS']; 
		$DEPARTMENT_NAME		= '';
		$res_dep = $db->Execute("SELECT DEPARTMENT FROM S_STUDENT_DOCUMENTS_DEPARTMENT, M_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_DOCUMENTS_DEPARTMENT.PK_DEPARTMENT AND PK_STUDENT_DOCUMENTS = '$PK_STUDENT_DOCUMENTS'  ORDER BY DEPARTMENT ASC "); 
		while (!$res_dep->EOF) { 
			if($DEPARTMENT_NAME != '')
				$DEPARTMENT_NAME .= ', ';
				
			$DEPARTMENT_NAME .= $res_dep->fields['DEPARTMENT'];
			$res_dep->MoveNext();
		}
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEPARTMENT_NAME);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['REQUESTED_DATE_1']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DOCUMENT_TYPE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FOLLOWUP_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RECEIVED']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DATE_RECEIVED'].' '.$FOLLOWUP_TIME);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['REQUESTED_DATE_1']);
		
		$res->MoveNext();
	}
	
	$objPHPExcel->setActiveSheetIndex(1);
	$res = $db->Execute("SELECT PK_STUDENT_REQUIREMENT, MANDATORY AS MANDATORY_1, REQUIREMENT,IF(MANDATORY = 1,'Yes','No') as MANDATORY, IF(COMPLETED = 1,'Yes','No') as  COMPLETED, COMPLETED_ON, IF(TYPE = 1,'School', IF(TYPE = 2,'Program','') ) as TYPE, IF(COMPLETED_ON = '0000-00-00', '', DATE_FORMAT(COMPLETED_ON,'%m/%d/%Y')) AS COMPLETED_ON FROM S_STUDENT_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ORDER BY REQUIREMENT ASC");
	while (!$res->EOF) {
		$line_2++;
		$index_2 = -1;
		
		$index_2++;
		$cell_no = $cell[$index_2].$line_2;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['LAST_NAME']." ".$res_stud->fields['FIRST_NAME']);
		
		$index_2++;
		$cell_no = $cell[$index_2].$line_2;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['STUDENT_STATUS']);
		
		$index_2++;
		$cell_no = $cell[$index_2].$line_2;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['CODE'].' - '.$res_enroll->fields['TERM_MASTER']);
		
		$index_2++;
		$cell_no = $cell[$index_2].$line_2;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['REQUIREMENT']);
		
		$index_2++;
		$cell_no = $cell[$index_2].$line_2;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TYPE']);
		
		$index_2++;
		$cell_no = $cell[$index_2].$line_2;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['MANDATORY']);
		
		$index_2++;
		$cell_no = $cell[$index_2].$line_2;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPLETED']);
		
		$index_2++;
		$cell_no = $cell[$index_2].$line_2;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPLETED_ON']);
		
		$res->MoveNext();
	}
}
$objPHPExcel->setActiveSheetIndex(0);

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);