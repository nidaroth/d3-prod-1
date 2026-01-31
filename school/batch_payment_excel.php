<?php session_start();
require_once('cutome_excel_generator.php');


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
	"Batch Check #",
	"Batch Total",
	"Batch Comments",


	"Student",
	"Student ID",
	"Ledger Code",
	"Disbursement Date",
	"Transaction Date",
	"Disbursement Amount (Credit)",
	"Batch Detail",
	"Payment Type",
	"AY",
	"AP",
	"Check #",
	"Receipt #",
	"Status",
	"Enrollment",
	"Term Block",
	"Prior Year",
	"Message"
];

//DIAM-1332
//if(has_wvjc_access($_SESSION['PK_ACCOUNT'],1)){
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

//}
//DIAM-1332

// echo "<pre>";
// $data = [];
$data[] = ['*bold*' => $header];
$res_disb = $db->Execute("select S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL,S_PAYMENT_BATCH_DETAIL.PK_STUDENT_ENROLLMENT, IF(BATCH_TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(BATCH_TRANSACTION_DATE, '%Y-%m-%d' )) AS  BATCH_TRANSACTION_DATE, S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL, S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE.CODE AS LEDGER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME,RECEIPT_NO, BATCH_NO, ACADEMIC_YEAR, ACADEMIC_PERIOD,BATCH_DETAIL_DESCRIPTION, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%Y-%m-%d' )) AS DISBURSEMENT_DATE1, DISBURSEMENT_AMOUNT, IF(DEPOSITED_DATE = '0000-00-00','', DATE_FORMAT(DEPOSITED_DATE, '%m/%d/%Y' )) AS DEPOSITED_DATE, BATCH_PAYMENT_STATUS, BATCH_NO,RECEIVED_AMOUNT, IF(PRIOR_YEAR = 1,'Yes', IF(PRIOR_YEAR = 2,'No','')) AS PRIOR_YEAR_1, PRIOR_YEAR,PK_DETAIL_TYPE, DETAIL ,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, S_PAYMENT_BATCH_DETAIL.CHECK_NO AS STUD_CHECK_NO, STUDENT_ID, CAMPUS_CODE, DISBURSEMENT_TYPE,S_STUDENT_MASTER.PK_STUDENT_MASTER 
		from 
		S_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL 
		LEFT JOIN M_BATCH_PAYMENT_STATUS ON M_BATCH_PAYMENT_STATUS.PK_BATCH_PAYMENT_STATUS = S_PAYMENT_BATCH_DETAIL.PK_BATCH_PAYMENT_STATUS 
		LEFT JOIN S_TERM_BLOCK ON S_TERM_BLOCK.PK_TERM_BLOCK = S_PAYMENT_BATCH_DETAIL.PK_TERM_BLOCK , S_STUDENT_DISBURSEMENT 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE
		, S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		WHERE 
		S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER AND 
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL AND 
		S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_ENROLLMENT AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = '$_GET[id]'
		GROUP  BY S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL
		ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, DISBURSEMENT_DATE ASC, M_AR_LEDGER_CODE.CODE ASC ");

###########
$res = $db->Execute("SELECT * FROM S_PAYMENT_BATCH_MASTER WHERE PK_PAYMENT_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if ($res->RecordCount() == 0) {
	header("location:manage_batch_payment");
	exit;
}
$BATCH_NO 			= $res->fields['BATCH_NO'];
$DATE_RECEIVED  	= $res->fields['DATE_RECEIVED'];
$POSTED_DATE  		= $res->fields['POSTED_DATE'];
$PK_AR_LEDGER_CODE  = $res->fields['PK_AR_LEDGER_CODE'];
$CHECK_NO  			= $res->fields['CHECK_NO'];
$AMOUNT				= $res->fields['AMOUNT'];
$COMMENTS  			= $res->fields['COMMENTS'];
$PK_BATCH_STATUS	= $res->fields['PK_BATCH_STATUS'];
$BATCH_PK_CAMPUS1	= $res->fields['BATCH_PK_CAMPUS'];
$TRANS_DATA_TYPE    = $res->fields['TRANS_DATA_TYPE']; // DAIM - 86,88
$BATCH_PK_CAMPUS_ARR	= explode(",", $res->fields['BATCH_PK_CAMPUS']);
$PK_AR_LEDGER_CODE_ARR  = explode(",", $res->fields['PK_AR_LEDGER_CODE']);

$START_DATE	 		= $res->fields['PAYMENT_BATCH_START_DATE'];
$END_DATE	 		= $res->fields['PAYMENT_BATCH_END_DATE'];

if ($START_DATE == '0000-00-00')
	$START_DATE = '';
else
	$START_DATE = date("m/d/Y", strtotime($START_DATE));

if ($END_DATE == '0000-00-00')
	$END_DATE = '';
else
	$END_DATE = date("m/d/Y", strtotime($END_DATE));

$res = $db->Execute("SELECT BATCH_STATUS FROM M_BATCH_STATUS WHERE PK_BATCH_STATUS = '$PK_BATCH_STATUS' ");
$BATCH_STATUS = $res->fields['BATCH_STATUS'];

if ($DATE_RECEIVED == '0000-00-00')
	$DATE_RECEIVED = '';
else
	$DATE_RECEIVED = date("m/d/Y", strtotime($DATE_RECEIVED));

if ($POSTED_DATE == '0000-00-00')
	$POSTED_DATE = '';
else
	$POSTED_DATE = date("m/d/Y", strtotime($POSTED_DATE));



$str = '';
$res_type = $db->Execute("select CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($BATCH_PK_CAMPUS1)  order by CAMPUS_CODE ASC");
while (!$res_type->EOF) {
	if ($str != '')
		$str .= ", ";
	$str .= $res_type->fields['CAMPUS_CODE'];
	$res_type->MoveNext();
}
###########
$posted_total = 0;
while (!$res_disb->EOF) {
	$posted_total += $res_disb->fields['RECEIVED_AMOUNT'];
	$DETAIL = '';
	if ($res_disb->fields['PK_DETAIL_TYPE'] == 4) {
		$DETAIL1 = $res_disb->fields['DETAIL'];
		$res_det1 = $db->Execute("select AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_PAYMENT_TYPE = '$DETAIL1' ");
		$DETAIL = $res_det1->fields['AR_PAYMENT_TYPE'];
	}

	$PK_STUDENT_ENROLLMENT = $res_disb->fields['PK_STUDENT_ENROLLMENT'];
	$res_en_2 = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

	$SPLIT = '';
	if ($res_disb->fields['DISBURSEMENT_TYPE'] == 1)
		$SPLIT = "Split";

	//DIAM-1332
	$ADDRESS				= '';
	$ADDRESS_1				= '';
	$CITY					= '';
	$PK_STATES				= '';
	$ZIP					= '';
	$PK_COUNTRY				= '';		
	$EMAIL					= '';
	$EMAIL_OTHER			= '';

	//if(has_wvjc_access($_SESSION['PK_ACCOUNT'],1)){

		$PK_STUDENT_MASTER  = $res_disb->fields['PK_STUDENT_MASTER'];
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
			
	//	}
	//DIAM-1332

	$data[] =
		[

			$BATCH_NO,
			$BATCH_STATUS,
			$str,
			$DATE_RECEIVED,
			$POSTED_DATE,
			$CHECK_NO,
			$AMOUNT,
			$COMMENTS,
			trim($res_disb->fields['NAME']),
			trim($res_disb->fields['STUDENT_ID']),
			$res_disb->fields['LEDGER'],
			$res_disb->fields['DISBURSEMENT_DATE1'],
			$res_disb->fields['BATCH_TRANSACTION_DATE'],
			number_format_value_checker($res_disb->fields['RECEIVED_AMOUNT'], 2),
			$res_en_2->fields['CODE'] . ' - ' . $res_en_2->fields['BEGIN_DATE_1'],
			$DETAIL,
			$res_disb->fields['ACADEMIC_YEAR'],
			$res_disb->fields['ACADEMIC_PERIOD'],
			$res_disb->fields['STUD_CHECK_NO'],
			$res_disb->fields['RECEIPT_NO'],
			$res_disb->fields['BATCH_PAYMENT_STATUS'],
			$res_en_2->fields['BEGIN_DATE_1'] . ' - ' . $res_en_2->fields['CODE'] . ' - ' . $res_en_2->fields['STUDENT_STATUS'] . ' - ' . $res_disb->fields['CAMPUS_CODE'],
			$res_disb->fields['BEGIN_DATE_1'],
			$res_disb->fields['PRIOR_YEAR_1'],
			$SPLIT,
			$ADDRESS,
			$ADDRESS_1,
			$CITY,
			$PK_STATES,
			$ZIP,
			$PK_COUNTRY,		
			$EMAIL,
			$EMAIL_OTHER
		];
	// echo "<br> --->Added data in data array --------- <br>";
	// print_r($data);

	$res_disb->MoveNext();
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
		'',
		'Total',
		number_format_value_checker($posted_total, 2),
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
		''
	];


// print_r($data);

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

$file_name = 'Batch_Payment.xlsx';

// print_r($data);exit;
$outputFileName = CustomExcelGenerator::makecustom('Excel2007', 'temp/', $file_name, $data, false);
// $outputFileName = CustomExcelGenerator::make('Excel2007', 'temp/', $file_name, $data, $header);
// echo$response['path'] = $outputFileName = $file_path_prefix . $outputFileName;
// $response['name'] =  $file_name;
// $response['files'] = $files.".xlsx";
header("location:" . $outputFileName);
// echo json_encode($outputFileName);
