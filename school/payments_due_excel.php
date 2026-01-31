<?php session_start();
$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');

require_once("check_access.php");

if(check_access('REPORT_ACCOUNTING') == 0 && check_access('ACCOUNTING_ACCESS') == 0){
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

$campus_cond  = "";
$campus_cond1 = "";
$campus_id	  = "";
if(!empty($_GET['campus'])){
	$PK_CAMPUS 	  = $_GET['campus'];
	$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
}

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
$file_name 		= 'Payments Due.xlsx';
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

$heading[] = 'Student';
$width[]   = 20;
$heading[] = 'ID';
$width[]   = 20;
$heading[] = 'Campus';
$width[]   = 20;
$heading[] = 'Program';
$width[]   = 20;
$heading[] = 'Status';
$width[]   = 20;
$heading[] = 'First Term';
$width[]   = 20;
$heading[] = 'Due Date';
$width[]   = 20;
$heading[] = 'Invoice Description';
$width[]   = 20;
$heading[] = 'Amount';
$width[]   = 20;

$heading[] = 'Address';
$width[]   = 25;
$heading[] = 'Address 2nd Line';
$width[]   = 25;
$heading[] = 'City';
$width[]   = 20;
$heading[] = 'State';
$width[]   = 20;
$heading[] = 'Zip';
$width[]   = 20;
$heading[] = 'Country';
$width[]   = 20;
$heading[] = 'Email';
$width[]   = 20;
$heading[] = 'Other Email';
$width[]   = 20; 

$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
}	

$objPHPExcel->getActiveSheet()->freezePane('A1');

$cond 		= "";
$FROM_DATE 	= $_GET['st'];
$TO_DATE 	= $_GET['et'];
if($FROM_DATE != '' && $TO_DATE != ''){
	$FROM_DATE 	= date('Y-m-d',strtotime($FROM_DATE));
	$TO_DATE 	= date('Y-m-d',strtotime($TO_DATE));
	
	$cond .= " AND DISBURSEMENT_DATE BETWEEN '$FROM_DATE' AND '$TO_DATE' ";
} else if($FROM_DATE != ''){
	$FROM_DATE 	= date('Y-m-d',strtotime($FROM_DATE));
	$cond .= " AND DISBURSEMENT_DATE >= '$FROM_DATE' ";
} else if($TO_DATE != ''){
	$TO_DATE 	= date('Y-m-d',strtotime($TO_DATE));
	$cond .= " AND DISBURSEMENT_DATE <= '$TO_DATE' ";
}

$PK_STUDENT_ENROLLMENT_ARR = explode(",",$_GET['eid']);
foreach($PK_STUDENT_ENROLLMENT_ARR as $PK_STUDENT_ENROLLMENT) {
	$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE  S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  "); 
	$PK_STUDENT_MASTER = $res_enroll->fields['PK_STUDENT_MASTER'];
	
	$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 
	if($res->RecordCount() == 0){
		header("location:../index");
		exit;
	}
	
	$res_ledger = $db->Execute("select S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT, DISBURSEMENT_AMOUNT, IF(DISBURSEMENT_DATE != '0000-00-00', DATE_FORMAT(DISBURSEMENT_DATE,'%Y-%m-%d'),'') AS  DISBURSEMENT_DATE_1, CODE, INVOICE_DESCRIPTION from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_PAYMENT_BATCH_DETAIL = 0 AND PK_DISBURSEMENT_STATUS IN (2) AND INVOICE = 1  AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE $cond ORDER BY DISBURSEMENT_DATE ASC");
	$total = 0;
	while (!$res_ledger->EOF) {
		$line++;
		$index = -1;
		
		$PK_STUDENT_ENROLLMENT = $res_enroll->fields['PK_STUDENT_ENROLLMENT'];
		$res_campus = $db->Execute("SELECT S_CAMPUS.CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  $campus_cond1 "); 
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LAST_NAME'].', '.$res->fields['FIRST_NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_campus->fields['CAMPUS_CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['STUDENT_STATUS']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['TERM_MASTER']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['DISBURSEMENT_DATE_1']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CODE'].' - '.$res_ledger->fields['INVOICE_DESCRIPTION']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['DISBURSEMENT_AMOUNT']);

		#Additional Columns added 1374
		
		$res_address_new = $db->Execute("SELECT * FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' LIMIT 0,1"); 
		
		if ($res_address_new->RecordCount() > 0) {

			$PK_STUDENT_CONTACT_TYPE_MASTER = $res_address_new->fields['PK_STUDENT_CONTACT_TYPE_MASTER'];
		   
			   if($PK_STUDENT_CONTACT_TYPE_MASTER==1){
				   $ADDRESS				= $res_address_new->fields['ADDRESS'];
				   $ADDRESS_1				= $res_address_new->fields['ADDRESS_1'];
				   $CITY					= $res_address_new->fields['CITY'];
				   $PK_STATES				= $res_address_new->fields['PK_STATES'];
				   $ZIP					= $res_address_new->fields['ZIP'];
				   $PK_COUNTRY				= $res_address_new->fields['PK_COUNTRY'];		
				   $EMAIL					= $res_address_new->fields['EMAIL'];
				   $EMAIL_OTHER			= $res_address_new->fields['EMAIL_OTHER'];

				   $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE PK_STATES='$PK_STATES' AND ACTIVE = '1' ORDER BY STATE_NAME ASC ");
				   $PK_STATES = $res_type->fields['STATE_NAME'];

				   $res_type1 = $db->Execute("select PK_COUNTRY, NAME from Z_COUNTRY WHERE ACTIVE = '1' AND PK_COUNTRY = '$PK_COUNTRY' ORDER BY NAME ASC ");
				   $PK_COUNTRY				= $res_type1->fields['NAME'];

			   }
		}

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ADDRESS);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ADDRESS_1);


		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CITY);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($PK_STATES);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ZIP);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($PK_COUNTRY);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($EMAIL);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($EMAIL_OTHER);

		$res_ledger->MoveNext();
	}
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);

