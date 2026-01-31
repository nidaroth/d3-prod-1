<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/batch_payment.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0){
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
$file_name 		= 'Payment Batch.xlsx';
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

$heading[] = BATCH_NO;
$width[]   = 20;
$heading[] = CAMPUS;
$width[]   = 20;
$heading[] = BATCH_STATUS;
$width[]   = 20;
$heading[] = BATCH_DATE;
$width[]   = 20;
$heading[] = POSTED_DATE;
$width[]   = 20;
$heading[] = CHECK_NO;
$width[]   = 40;
$heading[] = LEDGER_CODES;
$width[]   = 50;
$heading[] = BATCH_AMOUNT.'('.CREDIT.')';
$width[]   = 50;



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
		
		$BATCH_NO 		= $res->fields['BATCH_NO'];
		$BATCH_STATUS	= $res->fields['BATCH_STATUS'];
		if($res->fields['DATE_RECEIVED'] != '' && $res->fields['DATE_RECEIVED'] != '0000-00-00')
			$DATE_RECEIVED = date('m/d/Y',strtotime($res->fields['DATE_RECEIVED']));
		else
			$DATE_RECEIVED = '';
			
			
		if($res->fields['POSTED_DATE'] != '' && $res->fields['POSTED_DATE'] != '0000-00-00')
			$POSTED_DATE = date('m/d/Y',strtotime($res->fields['POSTED_DATE']));
		else
			$POSTED_DATE = '';
		
		$CHECK_NO			= $res->fields['CHECK_NO'];

		$CODE = '';
		$PK_PAYMENT_BATCH_MASTER = $res->fields['PK_PAYMENT_BATCH_MASTER'];
		$res_led = $db->Execute("SELECT CODE FROM S_PAYMENT_BATCH_DETAIL, M_AR_LEDGER_CODE, S_STUDENT_DISBURSEMENT WHERE PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' AND S_PAYMENT_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT GROUP BY S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE ORDER BY CODE ASC "); 
		while (!$res_led->EOF) { 
			if($CODE != '')
				$CODE .= ', ';
			$CODE .= $res_led->fields['CODE'];
			
			$res_led->MoveNext();
		}
		$row['CODE'] = $CODE;

		$CREDIT_AMOUNT  		= $res->fields['AMOUNT'];
		$BATCH_PK_CAMPUS = $res->fields['BATCH_PK_CAMPUS'];

		$CAMPUS_STR = '';
		$res_type = $db->Execute("select CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($BATCH_PK_CAMPUS)  order by CAMPUS_CODE ASC");
		while (!$res_type->EOF) {
			if ($CAMPUS_STR != '')
				$CAMPUS_STR .= ", ";
			$CAMPUS_STR .= $res_type->fields['CAMPUS_CODE'];
			$res_type->MoveNext();
		}



		$line++;
		$index = -1;

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BATCH_NO);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CAMPUS_STR);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BATCH_STATUS);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DATE_RECEIVED);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($POSTED_DATE);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CHECK_NO);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CODE);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CREDIT_AMOUNT);
		

	$res->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);