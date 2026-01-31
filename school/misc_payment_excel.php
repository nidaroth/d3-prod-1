<?php session_start();
require_once('custom_excel_generator.php');


$browser = '';
if (stripos($_SERVER['HTTP_USER_AGENT'], "chrome") != false)
	$browser =  "chrome";
else if (stripos($_SERVER['HTTP_USER_AGENT'], "Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');
require_once("check_access.php");

if (check_access('MANAGEMENT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}

$header =   [
	"Batch #",
	"Batch Status",
	"Campus",
	"Batch Date",
	"Posted Date",
	"Description",

	"Student",
	"Student ID",
	"Ledger Code",
	"Transaction Date",
	"Debit",
	"Credit",
	"Ledger Code Desc",
	'Fee/Payment Type',	
	"AY",
	"AP",	
	"Receipt #",
	"Reference #",
	"Enrollment",
	"Term Block",
	"Prior Year",
];

//DIAM-1332
if(has_wvjc_access($_SESSION['PK_ACCOUNT'],1)){
	$header1 = [
		"Address",
		"Address 2nd Line",
		"City",
		"State",
		"Zip",
		"Country",
		"Email",
		"Other Email"

	];	
	$header = array_merge($header, $header1);

}
//DIAM-1332

// echo "<pre>";
// $data = [];
$data[] = ['*bold*' => $header];

###########
$res_check = $db->Execute("SELECT BATCH_NO, IF(BATCH_DATE = '0000-00-00','',DATE_FORMAT(BATCH_DATE, '%m/%d/%Y' )) AS BATCH_DATE, DESCRIPTION, IF(POSTED_DATE = '0000-00-00','',DATE_FORMAT(POSTED_DATE, '%m/%d/%Y' )) AS POSTED_DATE, BATCH_STATUS,MISC_BATCH_PK_CAMPUS FROM S_MISC_BATCH_MASTER LEFT JOIN M_BATCH_STATUS ON M_BATCH_STATUS.PK_BATCH_STATUS = S_MISC_BATCH_MASTER.PK_BATCH_STATUS WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND S_MISC_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
if($res_check->RecordCount() == 0){
	header("location:manage_misc_batch");
	exit;
}
$BATCH_DATE 		= $res_check->fields['BATCH_DATE'];
if($BATCH_DATE == '0000-00-00')
	$BATCH_DATE = '';
else
	$BATCH_DATE = date("m/d/Y",strtotime($BATCH_DATE));


$BATCH_NO 			= $res_check->fields['BATCH_NO'];
$BATCH_STATUS  		= $res_check->fields['BATCH_STATUS'];
$POSTED_DATE  		= $res_check->fields['POSTED_DATE'];
if($POSTED_DATE == '0000-00-00')
	$POSTED_DATE = '';
else
	$POSTED_DATE = date("m/d/Y",strtotime($POSTED_DATE));

$DESCRIPTION = $res_check->fields['DESCRIPTION'];
$MISC_BATCH_PK_CAMPUS = $res_check->fields['MISC_BATCH_PK_CAMPUS'];
$str = '';
$res_type = $db->Execute("select CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($MISC_BATCH_PK_CAMPUS)  order by CAMPUS_CODE ASC");
while (!$res_type->EOF) {
	if ($str != '')
		$str .= ", ";
	$str .= $res_type->fields['CAMPUS_CODE'];
	$res_type->MoveNext();
}


$debit_total 	= 0;
$credit_total 	= 0;
$res_disb1 = $db->Execute("select S_MISC_BATCH_DETAIL.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER from S_MISC_BATCH_DETAIL LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_MISC_BATCH_DETAIL.PK_STUDENT_MASTER LEFT JOIn S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND S_MISC_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  GROUP BY S_MISC_BATCH_DETAIL.PK_STUDENT_ENROLLMENT ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
while (!$res_disb1->EOF) { 
	$PK_STUDENT_ENROLLMENT = $res_disb1->fields['PK_STUDENT_ENROLLMENT'];
	
	$res_disb = $db->Execute("select S_MISC_BATCH_DETAIL.*, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, STUDENT_ID,CODE,LEDGER_DESCRIPTION from S_MISC_BATCH_DETAIL LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_MISC_BATCH_DETAIL.PK_AR_LEDGER_CODE LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_MISC_BATCH_DETAIL.PK_STUDENT_MASTER LEFT JOIn S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND S_MISC_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_MISC_BATCH_DETAIL.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");

	//DIAM-1332
	$ADDRESS				= '';
	$ADDRESS_1				= '';
	$CITY					= '';
	$PK_STATES				= '';
	$ZIP					= '';
	$PK_COUNTRY				= '';		
	$EMAIL					= '';
	$EMAIL_OTHER			= '';

	if(has_wvjc_access($_SESSION['PK_ACCOUNT'],1)){

		$PK_STUDENT_MASTER  = $res_disb1->fields['PK_STUDENT_MASTER'];
		$res = $db->Execute("SELECT * FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' LIMIT 0,1");
		
		if ($res->RecordCount() > 0) {

			 $PK_STUDENT_CONTACT_TYPE_MASTER = $res->fields['PK_STUDENT_CONTACT_TYPE_MASTER'];
			
				if($PK_STUDENT_CONTACT_TYPE_MASTER==1){
					$ADDRESS				= $res->fields['ADDRESS'];
					$ADDRESS_1				= $res->fields['ADDRESS_1'];
					$CITY					= $res->fields['CITY'];
					$PK_STATES				= $res->fields['PK_STATES'];
					$ZIP					= $res->fields['ZIP'];
					$PK_COUNTRY				= $res->fields['PK_COUNTRY'];		
					$EMAIL					= $res->fields['EMAIL'];
					$EMAIL_OTHER			= $res->fields['EMAIL_OTHER'];

					$res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE PK_STATES='$PK_STATES' AND ACTIVE = '1' ORDER BY STATE_NAME ASC ");
					$PK_STATES = $res_type->fields['STATE_NAME'];

					$res_type1 = $db->Execute("select PK_COUNTRY, NAME from Z_COUNTRY WHERE ACTIVE = '1' AND PK_COUNTRY = '$PK_COUNTRY' ORDER BY NAME ASC ");
					$PK_COUNTRY				= $res_type1->fields['NAME'];

				}
	
			}
			
		}
	//DIAM-1332
	
	$sub_debit_total = 0;
	$TRANS_DATE = "";
	while (!$res_disb->EOF) { 
		$res_enroll = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 , IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, IS_ACTIVE_ENROLLMENT,FUNDING FROM S_STUDENT_ENROLLMENT LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '".$res_disb->fields['PK_STUDENT_ENROLLMENT']."' ");
		
		$debit_total 		+= $res_disb->fields['DEBIT'];
		$credit_total 		+= $res_disb->fields['CREDIT'];
		
		$PRIOR_YEAR = '';
		if($res_disb->fields['PRIOR_YEAR'] == 1)
			$PRIOR_YEAR = 'Yes';
		else
			$PRIOR_YEAR = 'No';
			
		$FEE_PAYMENT_TYPE = '';
		if($res_disb->fields['PK_AR_FEE_TYPE'] > 0) {
			$res11 = $db->Execute("select AR_FEE_TYPE FROM M_AR_FEE_TYPE WHERE PK_AR_FEE_TYPE = '".$res_disb->fields['PK_AR_FEE_TYPE']."' ");
			$FEE_PAYMENT_TYPE = $res11->fields['AR_FEE_TYPE'];
		} else if($res_disb->fields['PK_AR_PAYMENT_TYPE'] > 0) {
			$res11 = $db->Execute("select AR_PAYMENT_TYPE FROM M_AR_PAYMENT_TYPE WHERE PK_AR_PAYMENT_TYPE = '".$res_disb->fields['PK_AR_PAYMENT_TYPE']."' ");
			$FEE_PAYMENT_TYPE = $res11->fields['AR_PAYMENT_TYPE'];
		}
		
		$TERM_BLOCK = '';
		$res11 = $db->Execute("select CONCAT(IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )), ' - ', DESCRIPTION) AS TERM_BLOCK from S_TERM_BLOCK WHERE PK_TERM_BLOCK = '".$res_disb->fields['PK_TERM_BLOCK']."' ");
		$TERM_BLOCK = $res11->fields['TERM_BLOCK'];
		
		if($res_disb->fields['TRANSACTION_DATE'] != '' && $res_disb->fields['TRANSACTION_DATE'] != '0000-00-00')
			$TRANS_DATE = date("m/d/Y", strtotime($res_disb->fields['TRANSACTION_DATE']));

	

	$data[] =
		[

			$BATCH_NO,
			$BATCH_STATUS,
			$str,
			$BATCH_DATE,
			$POSTED_DATE,
			$DESCRIPTION, 
			
			trim($res_disb->fields['NAME']),
			trim($res_disb->fields['STUDENT_ID']),
			$res_disb->fields['CODE'],			
			$TRANS_DATE,
			number_format_value_checker($res_disb->fields['DEBIT'],2),
			number_format_value_checker($res_disb->fields['CREDIT'],2),
			$res_disb->fields['BATCH_DETAIL_DESCRIPTION'],
			$FEE_PAYMENT_TYPE,
			$res_disb->fields['AY'],
			$res_disb->fields['AP'],
			$res_disb->fields['MISC_RECEIPT_NO'],
			$res_disb->fields['REFERENCE_NO'],
			$res_enroll->fields['BEGIN_DATE_1'].' - '.$res_enroll->fields['CODE'].' - '.$res_enroll->fields['STUDENT_STATUS'],
			$TERM_BLOCK,
			$PRIOR_YEAR,

			$ADDRESS,
			$ADDRESS_1,
			$CITY,
			$PK_STATES,
			$ZIP,
			$PK_COUNTRY,		
			$EMAIL,
			$EMAIL_OTHER
		];
	// print_r($data);
		
		$res_disb->MoveNext();
	}

	$res_disb1->MoveNext();
}
$data[]['*bold*'] =
	[
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'Total',
		number_format_value_checker($debit_total, 2),
		number_format_value_checker($credit_total, 2),
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
	];

if ($_SERVER['HTTP_HOST'] == 'localhost') {
	$db_name     = 'DSIS';
	$db_pass     = 'root';
	$mysqli = new mysqli("localhost", "root", "$db_pass", "$db_name");
	$file_path_prefix = 'DSIS_GIT/local/school/';
} else {
	$db_name     = 'DSIS';
	$db_pass     = 'DSISMySQLPa$$1!';
	$mysqli = new mysqli($db_host, "root", "$db_pass", "$db_name");
	$file_path_prefix = 'school/';
}

$file_name = 'Misc Payment.xlsx';
$outputFileName = CustomExcelGenerator::makecustom('Excel2007', 'temp/', $file_name, $data, false);
header("location:" . $outputFileName);
