<?php
require_once("../common_classes/apc_cache.php");
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/custom_report.php");
require_once("../language/menu.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");
require_once("../language/student_report_selection.php");
require_once("check_access.php");

if (check_access('REPORT_CUSTOM_REPORT') == 0) {
	header("location:../index");
	exit;
}

error_reporting(0); 


$S_CUSTOM_COMPANY_REPORT['FILTER_NAME'] = $_REQUEST['FILTER_NAME'];
$S_CUSTOM_COMPANY_REPORT['SELECTED'] = $_REQUEST['data_selected']; 
if(isset($_REQUEST['filter_id']) && $_REQUEST['filter_id'] != '')
$PK_CUSTOM_COMPANY_REPORT = $_REQUEST['filter_id']; 
else 
$PK_CUSTOM_COMPANY_REPORT = false;

unset($_REQUEST['FILTER_NAME'],$_REQUEST['SAVE_CONTINUE'],$_REQUEST['data_selected']);

$filter_settings = [];
foreach ($_REQUEST as $key => $value) {
	$filter_settings[$key] = $value;
} 

$S_CUSTOM_COMPANY_REPORT['FIELDS_TO_SHOW']  = implode(',',$_REQUEST['selected_columns']); 
$S_CUSTOM_COMPANY_REPORT['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT']; 
$S_CUSTOM_COMPANY_REPORT['CREATED_BY'] =  $_SESSION['PK_USER']; 
$S_CUSTOM_COMPANY_REPORT['PK_USER'] =  $_SESSION['PK_USER']; 
$S_CUSTOM_COMPANY_REPORT['CREATED_ON'] = date("Y-m-d H:i"); 
$S_CUSTOM_COMPANY_REPORT['FILTERS'] = json_encode($filter_settings); 

if($PK_CUSTOM_COMPANY_REPORT){
	// echo "updating";
	echo "$PK_CUSTOM_COMPANY_REPORT";
	 
	db_perform('S_CUSTOM_COMPANY_REPORT', $S_CUSTOM_COMPANY_REPORT, 'update', " PK_CUSTOM_COMPANY_REPORT = '$PK_CUSTOM_COMPANY_REPORT' AND PK_USER = '$_SESSION[PK_USER]' ");
}else{
	db_perform('S_CUSTOM_COMPANY_REPORT', $S_CUSTOM_COMPANY_REPORT, 'insert');
	echo $db->insert_ID();
}





