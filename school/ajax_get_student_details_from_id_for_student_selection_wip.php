<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/student.php");
require_once("../common_classes/apc_cache.php");
require_once("../language/menu.php");
require_once("../language/student_report_selection.php");
require_once("../language/student_contact.php");


require_once("check_access.php");
if ($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '') {
	header("location:../index");
	exit;
}

if ($_REQUEST['id'] != '') {
	$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT,FIELDS_TO_SHOW,LAST_QUERY from S_SELECT_STUDENT_FILTER WHERE PK_SELECT_STUDENT_FILTER = '$_REQUEST[id]' AND PK_USER = '$_SESSION[PK_USER]' ");

	$old_FIELDS_TO_SHOW = $res_type->fields['FIELDS_TO_SHOW'];
	$old_query = $res_type->fields['LAST_QUERY'];

	$PK_STUDENT_ENROLLMENT_ARR 	= explode(",", $res_type->fields['PK_STUDENT_ENROLLMENT']);
} else
	$PK_STUDENT_ENROLLMENT_ARR = array();


$csv_train = $res_type->fields['PK_STUDENT_ENROLLMENT'];
if ($_REQUEST['str'] != '') {
	if ($res_type->fields['PK_STUDENT_ENROLLMENT'] != '') {
		$csv_train .= ',';
	}
	$csv_train .=  $_REQUEST['str'];
}
if ($_REQUEST['str1'] != '') {

	if ($csv_train != '') {
		$csv_train .= ',';
	}
	$csv_train .= $_REQUEST['str1'];
}

$_REQUEST['str'] = $csv_train;
// print_r($_REQUEST);
// echo $csv_train;

$PK_STUDENT_ENROLLMENT = $_REQUEST['str'];

$cond = "";
// if ($_REQUEST['str1'] != '')
// $cond = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT NOT IN (" . $_REQUEST['str1'] . ") ";

if (isset($_REQUEST['take_old_query']) == 'yes') {
} else {
	$query = $_SESSION['custome_query_cache'];
}

$selected_columns = false;
if (isset($_REQUEST['selected_columns'])) {
	if ($_REQUEST['selected_columns'] != '') {
		$selected_columns = explode(',', $_REQUEST['selected_columns']);
	}
}


$multi_selects = [
	"PK_CITIZENSHIP",
	"PK_COUNTRY_CITIZEN",
	"PK_DRIVERS_LICENSE_STATE",
	"PK_HIGHEST_LEVEL_OF_EDU",
	"PK_MARITAL_STATUS",
	"PK_RACE",
	"PK_STATE_OF_RESIDENCY",
	"PK_1098T_REPORTING_TYPE",
	"PK_REPRESENTATIVE",
	"PK_CAMPUS",
	"PK_SPECIAL",
	"PK_LEAD_CONTACT_SOURCE",
	"PK_DISTANCE_LEARNING",
	"PK_DROP_REASON",
	"PK_ENROLLMENT_STATUS",
	"PK_FUNDING",
	"FIRST_TERM",
	"PK_LEAD_SOURCE",
	"PK_PLACEMENT_STATUS",
	"PK_CAMPUS_PROGRAM",
	"PK_SAP_GROUP",
	"PK_SESSION",
	"PK_STUDENT_STATUS",
	"PK_STUDENT_GROUP",
	"EVENT_EMPLOYEE",
	"EVENT_OTHER",
	"EVENT_STATUS",
	"EVENT_TYPE",
	"INTERNAL_MSG_SENT_FROM",
	"NOTES_EMPLOYEE",
	"NOTE_STATUS",
	"NOTE_TYPE",
	"PROBATION_LEVEL",
	"PROBATION_STATUS",
	"PROBATION_TYPE",
	"TASK_EMPLOYEE",
	"TASK_OTHER",
	"TASK_PRIORITY",
	"TASK_STATUS",
	"TASK_TYPE",
	"TEXT_DEPARTMENT",
	"TEXT_EMPLOYEE",
	"INTERNAL_MSG_SENT_FROM",
	"DOC_DEPARTMENT",
	"DOC_EMPLOYEE",
	"REQUIREMENT_CATEGORY",
	"OTHER_EDU_EDU_TYPE",
	"OTHER_EDU_SCHOOL_STATE",
	"DISBURSEMENT_AWARD_YEAR",
	"DISBURSEMENT_LEDGER_CODE",
	"DISBURSEMENT_STATUS",
	"ESTIMATEDDOC_EMPLOYEE",
	"REQUIREMENT_CATEGORY",
	"OTHER_EDU_EDU_TYPE",
	"OTHER_EDU_SCHOOL_STATE",
	"DISBURSEMENT_AWARD_YEAR",
	"DISBURSEMENT_LEDGER_CODE",
	"DISBURSEMENT_STATUS",
	"ESTIMATED_FEES_FEE_TYPE",
	"COMPANY_MAIN_CONTACT",
	"COMPANY_SCHOOL_EMPLOYEE",
	"COMPANY_SCHOOL_EMPLOYEE",
	"COMPANY_STATE",
	"COMPANY_STATUS",
	"COMPANY_TYPE",
	"COMPANY_EVENT_COMPANY_CONTACT",
	"COMPANY_EVENT_TYPE",
	"COMPANY_EVENT_SCHOOL_EMPLOYEE",
	"LEDGER_BATCH_DETAIL",
	"LEDGER_CODE",
	"LEDGER_PAYMENT_TYPE_DETAIL",
	"LEDGER_TERM_BLOCK",
	"STUDENT_JOB_STATUS",
	"STUDENT_JOB_TYPE",
	"STUDENT_JOB_PAY_TYPE",
	"STUDENT_JOB_SOC_CODE",
	"STUDENT_JOB_VERIFICATION_SOURCE",
	"STUDENT_JOB_COMPANY_NAME"

];

$query = str_replace('WHERE', " WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) $cond AND ", $query);
// echo $query;
// exit;

$res_type = $db->Execute($query);
$global_cache = new ApcCache();
$data = [];
while (!$res_type->EOF) {
	// echo "<pre>";
	// print_r($res_type->fields);
	// exit;
	$row = [];

	// $row[] = ''; // for checkbox
	$row[] = $res_type->fields['PK_STUDENT_ENROLLMENT'];
	$row[] = $res_type->fields['PK_STUDENT_MASTER'];
	$row[] = $res_type->fields['STU_NAME'];
	$row[] = $res_type->fields['STUDENT_ID'];
	$row[] = $global_cache->GetValueFromCache('PK_CAMPUS', $res_type->fields['PK_CAMPUS']);
	$row[] = $res_type->fields['BEGIN_DATE'];
	$row[] = $global_cache->GetValueFromCache('PK_CAMPUS_PROGRAM', $res_type->fields['PK_CAMPUS_PROGRAM']);
	$row[] = $res_type->fields['STUDENT_STATUS'];
	$row[] = $global_cache->GetValueFromCache('PK_STUDENT_GROUP', $res_type->fields['PK_STUDENT_GROUP']);





	// echo "<pre>";
	// print_r("hello there");
	// print_r($res_type->fields);
	// exit;


	if ($selected_columns && $_REQUEST['str'] != '') {

		foreach ($selected_columns as $key => $value) {
			if (in_array($value, $multi_selects)) {
				// $debug_key = 'PK_FUNDING';
				// if($value == "$debug_key"){
				// 	echo $debug_key." FOUND";
				// }
				#use either this block OR ...
				// $filter_key = array_search($value, array_column($filters, 0));
				// $row[$filters[$filter_key][1]] = $global_cache->GetValueFromCache($value, $res_type->fields[$value]);
				#...OR(continued)
				$row[] = $global_cache->GetValueFromCache($value, $res_type->fields[$value]);
			}
			//else if(){
			// #check for select and dates and other type of fields
			//}
			else {
				//  if($value == 'IPEDS_ETHNICITY'){
				// 	echo "what is IPEDS_ETHNICITY ?".$global_cache->GetValueFromCache($value, $res_type->fields[$value])."<<";exit;
				//  }
				$cached_Data = $global_cache->GetValueFromCache($value, $res_type->fields[$value]);
				if ($cached_Data != null) {
					$row[] = $cached_Data;
				} else {
					if ($value == 'ENROLLMENT_PK_TERM_BLOCK' && $res_type->fields[$value] == 0) {
						$row[] = '--';
					} else
						$row[] = $res_type->fields[$value];
				};
				// exit;
			}
		}
	}
	// echo "<pre>";
	// print_r($row);
	// exit;
	//if()

	#check if data is of multi-select type if yes fetch from cache 
	$data[] = $row;




	$res_type->MoveNext();
}
// print_r($data);

foreach ($data as $dt_index_row => $dt_indexed_row) { ?>

	<tr id="stu_tr_<?= $data[$dt_index_row][0] ?>">
		<td> <input type="checkbox" name="DELETE_PK_STUDENT_ENROLLMENT[]" class="deletecheckBoxClass" value="<?= $data[$dt_index_row][0] ?>" onclick="del_check_all_remove()">

			<input type="hidden" name="PK_STUDENT_ENROLLMENT_1[]" value="<?= $data[$dt_index_row][0] ?>">
			<input type="hidden" name="PK_STUDENT_MASTER_1[]" value="<?= $data[$dt_index_row][1] ?>">
		</td>
		<?php foreach ($dt_indexed_row as $dt_data_key => $dt_data_val) {
			if ($dt_data_key != 0 && $dt_data_key != 1) {  ?>
				<td> <?= $dt_data_val ?> </td>
		<?php }
		} ?>
	</tr>
<?php }
// $response['data'] = $data;
// $response['query'] = $query;
// preg_match_all('/\b([A-Z_]+)\b/u', $cond, $matches);
// $response['condition'] = $matches;
// header('Content-Type: application/json; charset=utf-8');
// echo json_encode($response);
