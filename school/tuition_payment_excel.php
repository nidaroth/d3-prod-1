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
	"Type",
	"Course Term",

	"Student",
	"Student ID",
	"Ledger Code",
	"Ledger Code Desc",
	"Transaction Date",
	"Disbursement Amount (Debit)",
	"Batch Detail",	
	"AY",
	"AP",	
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
$res_check = $db->Execute("SELECT S_TUITION_BATCH_MASTER.*, IF(TYPE = 1,'Program', IF(TYPE = 2,'Course',IF(TYPE = 7,'Estimated Other Fee',''))) AS TYPE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 , IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1 FROM S_TUITION_BATCH_MASTER LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_TUITION_BATCH_MASTER.PK_TERM_MASTER WHERE PK_TUITION_BATCH_MASTER = '$_GET[id]' AND S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
if($res_check->RecordCount() == 0){
	header("location:manage_tuition_batch");
	exit;
}


$res = $db->Execute("SELECT S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER, IF(TRANS_DATE = '0000-00-00', '', DATE_FORMAT(TRANS_DATE,'%m/%d/%Y') ) as TRANS_DATE, IF(POSTED_DATE = '0000-00-00', '', DATE_FORMAT(POSTED_DATE,'%m/%d/%Y') ) as POSTED_DATE, BATCH_NO, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER ,BATCH_STATUS, IF(TYPE = 1,'Program', IF(TYPE = 2,'Course',IF(TYPE = 7,'Estimated Fees By Program', IF(TYPE = 9,'Estimated Fees By Student','')))) AS TYPE, S_TUITION_BATCH_MASTER.PK_BATCH_STATUS, TUITION_BATCH_PK_CAMPUS, SUM(AMOUNT) as DEBIT FROM S_TUITION_BATCH_MASTER LEFT JOIN S_TUITION_BATCH_DETAIL ON S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_TUITION_BATCH_MASTER.PK_BATCH_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_TUITION_BATCH_MASTER.PK_TERM_MASTER WHERE S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = '$_GET[id]' AND S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

$TYPE 				= $res->fields['TYPE'];
$TRANS_DATE 		= $res->fields['TRANS_DATE'];
if($TRANS_DATE == '0000-00-00')
	$TRANS_DATE = '';
else
	$TRANS_DATE = date("m/d/Y",strtotime($TRANS_DATE));


		$BATCH_NO 			= $res->fields['BATCH_NO'];
		$BATCH_STATUS  		= $res->fields['BATCH_STATUS'];
		$POSTED_DATE  		= $res->fields['POSTED_DATE'];
		if($POSTED_DATE == '0000-00-00')
			$POSTED_DATE = '';
		else
			$POSTED_DATE = date("m/d/Y",strtotime($POSTED_DATE));

		$TUITION_BATCH_PK_CAMPUS = $res->fields['TUITION_BATCH_PK_CAMPUS'];

		
		$TERM_MASTER 		= $res->fields['TERM_MASTER'];
		if($TERM_MASTER == '0000-00-00')
			$TERM_MASTER = '';
		else
			$TERM_MASTER = date("m/d/Y",strtotime($TERM_MASTER));
		

		$res_disb_count = $db->Execute("select PK_STUDENT_MASTER from 
		S_TUITION_BATCH_DETAIL 
		WHERE 
		PK_TUITION_BATCH_MASTER = '$_GET[id]' AND S_TUITION_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY PK_STUDENT_MASTER  ");
		
		if($TYPE == 2)
			$order_by = " CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, COURSE_BATCH_DESC ASC, CODE ASC ";
		else
			$order_by = " CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, CODE ASC ";
	
		$total 	= 0;
		$res_disb1 = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) AS STUD_NAME, STUDENT_ID, M_AR_LEDGER_CODE.CODE, BATCH_DETAIL_DESCRIPTION, AMOUNT, IF(TRANSACTION_DATE = '0000-00-00','',DATE_FORMAT(TRANSACTION_DATE, '%m/%d/%Y' )) AS TRANSACTION_DATE, TUITION_BATCH_DETAIL_AY, TUITION_BATCH_DETAIL_AP, IF(TUITION_BATCH_DETAIL_PRIOR_YEAR = 1, 'Yes', 'No') as TUITION_BATCH_DETAIL_PRIOR_YEAR, CONCAT(IF(S_TERM_BLOCK.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_BLOCK.BEGIN_DATE, '%m/%d/%Y' )), ' - ', DESCRIPTION) AS TERM_BLOCK, PK_STUDENT_ENROLLMENT, TUITION_BATCH_DETAIL_PK_COURSE_OFFERING, CONCAT(COURSE_CODE, ' (', SUBSTRING(SESSION, 1, 1), '-', SESSION_NO, ') - ', IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') ) AS COURSE_BATCH_DESC,S_STUDENT_MASTER.PK_STUDENT_MASTER  
		from 
		S_TUITION_BATCH_DETAIL 
		LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = TUITION_BATCH_DETAIL_PK_COURSE_OFFERING 
		LEFT JOIN M_SESSION on M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
		LEFT JOIN S_COURSE on S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
		LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_TUITION_BATCH_DETAIL.PK_STUDENT_MASTER 
		LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_TUITION_BATCH_DETAIL.PK_AR_LEDGER_CODE 
		LEFT JOIN S_TERM_BLOCK ON S_TERM_BLOCK.PK_TERM_BLOCK = S_TUITION_BATCH_DETAIL.PK_TERM_BLOCK
		WHERE 
		PK_TUITION_BATCH_MASTER = '$_GET[id]' AND S_TUITION_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY $order_by ");
	$str = '';
	$res_type = $db->Execute("select CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($TUITION_BATCH_PK_CAMPUS)  order by CAMPUS_CODE ASC");
	while (!$res_type->EOF) {
		if ($str != '')
			$str .= ", ";
		$str .= $res_type->fields['CAMPUS_CODE'];
		$res_type->MoveNext();
	}
###########
$total = 0;
while (!$res_disb1->EOF) { 
	$PK_STUDENT_ENROLLMENT	= $res_disb1->fields['PK_STUDENT_ENROLLMENT'];
	$PK_COURSE_OFFERING		= $res_disb1->fields['TUITION_BATCH_DETAIL_PK_COURSE_OFFERING'];
	
	$total 	+= $res_disb1->fields['AMOUNT'];
		
	$res_enroll = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 , IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, IS_ACTIVE_ENROLLMENT,FUNDING FROM S_STUDENT_ENROLLMENT LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");

	$BEGIN_DATE_1 	= trim($res_enroll->fields['BEGIN_DATE_1']);
	$CODE 			= trim($res_enroll->fields['CODE']);
	$STUDENT_STATUS = trim($res_enroll->fields['STUDENT_STATUS']);
	$FUNDING 		= trim($res_enroll->fields['FUNDING']);
	
	if($TYPE == 2) {
		$BATCH_DETAIL = $res_disb1->fields['COURSE_BATCH_DESC'];
	} else if($TYPE == 1 || $TYPE == 9) {
		$res_type = $db->Execute("SELECT CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_ENROLLMENT > 0 AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$BATCH_DETAIL = $res_type->fields['CODE'].' - '.$res_type->fields['BEGIN_DATE_1'];
	}

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

	$data[] =
		[

			$BATCH_NO,
			$BATCH_STATUS,
			$str,
			$TRANS_DATE,
			$POSTED_DATE,
			$TYPE, 
			$TERM_MASTER,
			$res_disb1->fields['STUD_NAME'],
			$res_disb1->fields['STUDENT_ID'],
			$res_disb1->fields['CODE'],
			$res_disb1->fields['BATCH_DETAIL_DESCRIPTION'],
			$res_disb1->fields['TRANSACTION_DATE'],
			number_format_value_checker($res_disb1->fields['AMOUNT'], 2),
			$BATCH_DETAIL,
			$res_disb1->fields['TUITION_BATCH_DETAIL_AY'],
			$res_disb1->fields['TUITION_BATCH_DETAIL_AP'],
			$BEGIN_DATE_1.' - '.$CODE.' - '.$STUDENT_STATUS,
			$res_disb1->fields['TERM_BLOCK'],
			$res_disb1->fields['TUITION_BATCH_DETAIL_PRIOR_YEAR'],			
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
		'',
		'',
		'Total',
		number_format_value_checker($total, 2),
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

$file_name = 'Tuition Payment.xlsx';
$outputFileName = CustomExcelGenerator::makecustom('Excel2007', 'temp/', $file_name, $data, false);
header("location:" . $outputFileName);
