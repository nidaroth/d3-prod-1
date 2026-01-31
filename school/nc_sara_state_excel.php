<?php

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

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}

header('Content-Type: application/json; charset=utf-8');


$states = $db->Execute(
"SELECT Z_STATES.PK_STATES,STATE_NAME,STATE_CODE,CODE,STATUS,COMMENT,DATE_APPROVED 
FROM `Z_STATES` 
LEFT JOIN Z_COUNTRY ON Z_STATES.PK_COUNTRY = Z_COUNTRY.PK_COUNTRY 
LEFT JOIN NC_SARA_STATE_AUTHORIZATION ON NC_SARA_STATE_AUTHORIZATION.PK_STATES = Z_STATES.PK_STATES
WHERE NC_SARA_STATE_AUTHORIZATION.PK_ACCOUNT = $_SESSION[PK_ACCOUNT]
ORDER BY CODE DESC,STATE_NAME,STATE_CODE;"
);
$status_arr = [];

$status_arr[] = " ";
$status_arr[] = "Authorized";
$status_arr[] = "Exempt";
$status_arr[] = "Licensed";
$status_arr[] = "NC-SARA Authorized";
$status_arr[] = "Not Applicable";
$status_arr[] = "Not Authorized";
$status_arr[] = "Pending";

$data = [];
$header =  ['State' , 'State Abbreviation' , 'Country' , 'Completed State Process' , 'Date Authorized' , 'Comments'];
$data[] = ['*bold*' => $header];
while ( !$states->EOF ) {
    # code...
    $data_row['State'] = $states->fields['STATE_NAME'];
    $data_row['Code'] = $states->fields['STATE_CODE'];
    $data_row['County'] = $states->fields['CODE'];
    $data_row['STATUS'] = $status_arr[$states->fields['STATUS']];
    if( $states->fields['DATE_APPROVED'] == '0000-00-00'){
        $data_row['DATE_APPROVED'] = '';
    }else{
        $data_row['DATE_APPROVED'] = $states->fields['DATE_APPROVED'];
    }
    
    $data_row['COMMENT'] =  $states->fields['COMMENT'];
    $data[] = $data_row;
    $states->MoveNext();
}


$file_name = 'NC_SARA_State_Authorization.xlsx';

try {
    //code...
    if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'deac_report') !== false) {
        $file_name =  "DEAC_7_State_Authorization_Distance_Education.xlsx";
    }  
} catch (\Throwable $th) {
    //throw $th;
}
$outputFileName = $file_name;
$outputFileName = str_replace(
    pathinfo($outputFileName, PATHINFO_FILENAME),
    pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
    $outputFileName
);
// dd($data);
$output = CustomExcelGenerator::makecustom('Excel2007', 'temp/', $outputFileName, $data );
// dd("File Generated ", $output);
$response["file_name"] = $outputFileName;
$response["path"] =  $output; 
echo json_encode($response);
