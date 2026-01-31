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
$selected_columns = false;
if (isset($_REQUEST['selected_columns'])) {
	if ($_REQUEST['selected_columns'] != '') {
		$selected_columns = explode(',', $_REQUEST['selected_columns']);
	}
}

error_reporting(1);
if ($_REQUEST['PK_CAMPUS'] != '') {
	$PK_CAMPUS = trim($_REQUEST['PK_CAMPUS']);
	$cond .= " AND trim(S_COMPANY_CAMPUS.PK_CAMPUS) IN ($PK_CAMPUS) ";
}

if ($_REQUEST['COMPANY_CITY'] != '') {
	$COMPANY_CITY = trim($_REQUEST['COMPANY_CITY']);
	$cond .= " AND trim(S_COMPANY.CITY) = '$COMPANY_CITY' ";
}

if ($_REQUEST['COMPANY_STATE'] != '') {
	$COMPANY_STATE = trim($_REQUEST['COMPANY_STATE']);
	$cond .= " AND trim(S_COMPANY.PK_STATES) in ($COMPANY_STATE) ";
}

if ($_REQUEST['COMPANY_NAME'] != '') {
	$COMPANY_NAME = trim($_REQUEST['COMPANY_NAME']);
	$cond .= " AND trim(S_COMPANY.COMPANY_NAME) like '%$COMPANY_NAME%' ";
}

if ($_REQUEST['COMPANY_SOURCE'] != '')
	$cond .= " AND S_COMPANY.PK_COMPANY_SOURCE IN ($_REQUEST[COMPANY_SOURCE]) ";

if ($_REQUEST['COMPANY_DATE_CREATED_BEGIN_DATE'] != '' && $_REQUEST['COMPANY_DATE_CREATED_END_DATE'] != '') {
	$COMPANY_DATE_CREATED_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_DATE_CREATED_BEGIN_DATE']));
	$COMPANY_DATE_CREATED_END_DATE 	= date("Y-m-d", strtotime($_REQUEST['COMPANY_DATE_CREATED_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY.DATE_CREATED, '%Y-%m-%d') BETWEEN '$COMPANY_DATE_CREATED_BEGIN_DATE' AND '$COMPANY_DATE_CREATED_END_DATE' ";
} else if ($_REQUEST['COMPANY_DATE_CREATED_BEGIN_DATE'] != '') {
	$COMPANY_DATE_CREATED_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_DATE_CREATED_BEGIN_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY.DATE_CREATED, '%Y-%m-%d') >= '$COMPANY_DATE_CREATED_BEGIN_DATE' ";
} else if ($_REQUEST['COMPANY_DATE_CREATED_END_DATE'] != '') {
	$COMPANY_DATE_CREATED_END_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_DATE_CREATED_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY.DATE_CREATED, '%Y-%m-%d') <= '$COMPANY_DATE_CREATED_END_DATE' ";
}

if ($_REQUEST['COMPANY_FAX'] != '') {
	$COMPANY_FAX	= preg_replace('/[^0-9]/', '', $_REQUEST['COMPANY_FAX']);
	$cond .= " AND REPLACE(REPLACE(REPLACE(REPLACE(S_COMPANY.FAX, '(', ''), ')', ''), '-', ''),' ','') = '$COMPANY_FAX' ";
}

if ($_REQUEST['COMPANY_MAIN_CONTACT'] != '')
	$cond .= " AND S_COMPANY.PK_COMPANY_CONTACT IN ($_REQUEST[COMPANY_MAIN_CONTACT]) ";

if ($_REQUEST['COMPANY_PHONE'] != '') {
	$COMPANY_PHONE	= preg_replace('/[^0-9]/', '', $_REQUEST['COMPANY_PHONE']);
	$cond .= " AND REPLACE(REPLACE(REPLACE(REPLACE(S_COMPANY.PHONE, '(', ''), ')', ''), '-', ''),' ','') = '$COMPANY_PHONE' ";
}

if ($_REQUEST['COMPANY_SCHOOL_EMPLOYEE'] != '')
	$cond .= " AND S_COMPANY.PK_COMPANY_ADVISOR IN ($_REQUEST[COMPANY_SCHOOL_EMPLOYEE]) ";

if ($_REQUEST['COMPANY_STATUS'] != '')
	$cond .= " AND S_COMPANY.PK_PLACEMENT_COMPANY_STATUS IN ($_REQUEST[COMPANY_STATUS]) ";

if ($_REQUEST['COMPANY_TYPE'] != '')
	$cond .= " AND S_COMPANY.PK_PLACEMENT_TYPE IN ($_REQUEST[COMPANY_TYPE]) ";

if ($_REQUEST['COMPANY_WEBSITE'] != '') {
	$COMPANY_WEBSITE = trim($_REQUEST['COMPANY_WEBSITE']);
	$cond .= " AND trim(S_COMPANY.WEBSITE) = '$COMPANY_WEBSITE' ";
}

if ($_REQUEST['COMPANY_EVENT_COMPANY_CONTACT'] != '')
	$cond .= " AND S_COMPANY_EVENT.PK_COMPANY_CONTACT IN ($_REQUEST[COMPANY_EVENT_COMPANY_CONTACT]) ";

if ($_REQUEST['COMPANY_EVENT_COMPLETE'] == 1)
	$cond .= " AND S_COMPANY_EVENT.COMPLETE = 1 ";
else if ($_REQUEST['COMPANY_EVENT_COMPLETE'] == 2)
	$cond .= " AND S_COMPANY_EVENT.COMPLETE = 0 ";

if ($_REQUEST['COMPANY_EVENT_BEGIN_DATE'] != '' && $_REQUEST['COMPANY_EVENT_END_DATE'] != '') {
	$COMPANY_EVENT_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_EVENT_BEGIN_DATE']));
	$COMPANY_EVENT_END_DATE 	= date("Y-m-d", strtotime($_REQUEST['COMPANY_EVENT_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_EVENT.EVENT_DATE, '%Y-%m-%d') BETWEEN '$COMPANY_EVENT_BEGIN_DATE' AND '$COMPANY_EVENT_END_DATE' ";
} else if ($_REQUEST['COMPANY_EVENT_BEGIN_DATE'] != '') {
	$COMPANY_EVENT_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_EVENT_BEGIN_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_EVENT.EVENT_DATE, '%Y-%m-%d') >= '$COMPANY_EVENT_BEGIN_DATE' ";
} else if ($_REQUEST['COMPANY_EVENT_END_DATE'] != '') {
	$COMPANY_EVENT_END_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_EVENT_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_EVENT.EVENT_DATE, '%Y-%m-%d') <= '$COMPANY_EVENT_END_DATE' ";
}

if ($_REQUEST['COMPANY_EVENT_TYPE'] != '')
	$cond .= " AND S_COMPANY_EVENT.PK_PLACEMENT_COMPANY_EVENT_TYPE IN ($_REQUEST[COMPANY_EVENT_TYPE]) ";

if ($_REQUEST['COMPANY_EVENT_FOLLOWUP_BEGIN_DATE'] != '' && $_REQUEST['COMPANY_EVENT_FOLLOWUP_END_DATE'] != '') {
	$COMPANY_EVENT_FOLLOWUP_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_EVENT_FOLLOWUP_BEGIN_DATE']));
	$COMPANY_EVENT_FOLLOWUP_END_DATE 	= date("Y-m-d", strtotime($_REQUEST['COMPANY_EVENT_FOLLOWUP_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_EVENT.FOLLOW_UP_DATE, '%Y-%m-%d') BETWEEN '$COMPANY_EVENT_FOLLOWUP_BEGIN_DATE' AND '$COMPANY_EVENT_FOLLOWUP_END_DATE' ";
} else if ($_REQUEST['COMPANY_EVENT_FOLLOWUP_BEGIN_DATE'] != '') {
	$COMPANY_EVENT_FOLLOWUP_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_EVENT_FOLLOWUP_BEGIN_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_EVENT.FOLLOW_UP_DATE, '%Y-%m-%d') >= '$COMPANY_EVENT_FOLLOWUP_BEGIN_DATE' ";
} else if ($_REQUEST['COMPANY_EVENT_FOLLOWUP_END_DATE'] != '') {
	$COMPANY_EVENT_FOLLOWUP_END_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_EVENT_FOLLOWUP_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_EVENT.FOLLOW_UP_DATE, '%Y-%m-%d') <= '$COMPANY_EVENT_FOLLOWUP_END_DATE' ";
}

if ($_REQUEST['COMPANY_EVENT_SCHOOL_EMPLOYEE'] != '')
	$cond .= " AND S_COMPANY_EVENT.PK_COMPANY_CONTACT_EMPLOYEE IN ($_REQUEST[COMPANY_EVENT_SCHOOL_EMPLOYEE]) ";
////NEw condtitions 


if ($_REQUEST['COMPANY_JOB_FULL_PART_TIME'] != '')
	$cond .= " AND S_COMPANY_JOB.PK_FULL_PART_TIME IN ($_REQUEST[COMPANY_JOB_FULL_PART_TIME]) ";

if ($_REQUEST['COMPANY_JOB_CANCELLED_BEGIN_DATE'] != '' && $_REQUEST['COMPANY_JOB_CANCELLED_END_DATE'] != '') {
	$COMPANY_JOB_CANCELLED_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_CANCELLED_BEGIN_DATE']));
	$COMPANY_JOB_CANCELLED_END_DATE 	= date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_CANCELLED_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_JOB.JOB_CANCELED, '%Y-%m-%d') BETWEEN '$COMPANY_JOB_CANCELLED_BEGIN_DATE' AND '$COMPANY_JOB_CANCELLED_END_DATE' ";
} else if ($_REQUEST['COMPANY_JOB_CANCELLED_BEGIN_DATE'] != '') {
	$COMPANY_JOB_CANCELLED_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_CANCELLED_BEGIN_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_JOB.JOB_CANCELED, '%Y-%m-%d') >= '$COMPANY_JOB_CANCELLED_BEGIN_DATE' ";
} else if ($_REQUEST['COMPANY_JOB_CANCELLED_END_DATE'] != '') {
	$COMPANY_JOB_CANCELLED_END_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_CANCELLED_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_JOB.JOB_CANCELED, '%Y-%m-%d') <= '$COMPANY_JOB_CANCELLED_END_DATE' ";
}

if ($_REQUEST['COMPANY_JOB_FILLED_BEGIN_DATE'] != '' && $_REQUEST['COMPANY_JOB_FILLED_END_DATE'] != '') {
	$COMPANY_JOB_FILLED_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_FILLED_BEGIN_DATE']));
	$COMPANY_JOB_FILLED_END_DATE 	= date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_FILLED_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_JOB.JOB_FILLED, '%Y-%m-%d') BETWEEN '$COMPANY_JOB_FILLED_BEGIN_DATE' AND '$COMPANY_JOB_FILLED_END_DATE' ";
} else if ($_REQUEST['COMPANY_JOB_FILLED_BEGIN_DATE'] != '') {
	$COMPANY_JOB_FILLED_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_FILLED_BEGIN_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_JOB.JOB_FILLED, '%Y-%m-%d') >= '$COMPANY_JOB_FILLED_BEGIN_DATE' ";
} else if ($_REQUEST['COMPANY_JOB_FILLED_END_DATE'] != '') {
	$COMPANY_JOB_FILLED_END_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_FILLED_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_JOB.JOB_FILLED, '%Y-%m-%d') <= '$COMPANY_JOB_FILLED_END_DATE' ";
}

if ($_REQUEST['COMPANY_JOB_NO'] != '')
	$cond .= " AND S_COMPANY_JOB.PK_STUDENT_JOB = '$_REQUEST[COMPANY_JOB_NO]' ";

if ($_REQUEST['COMPANY_JOB_POSTED_DATE_BEGIN_DATE'] != '' && $_REQUEST['COMPANY_JOB_POSTED_DATE_END_DATE'] != '') {
	$COMPANY_JOB_POSTED_DATE_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_POSTED_DATE_BEGIN_DATE']));
	$COMPANY_JOB_POSTED_DATE_END_DATE 	= date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_POSTED_DATE_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_JOB.JOB_POSTED, '%Y-%m-%d') BETWEEN '$COMPANY_JOB_POSTED_DATE_BEGIN_DATE' AND '$COMPANY_JOB_POSTED_DATE_END_DATE' ";
} else if ($_REQUEST['COMPANY_JOB_POSTED_DATE_BEGIN_DATE'] != '') {
	$COMPANY_JOB_POSTED_DATE_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_POSTED_DATE_BEGIN_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_JOB.JOB_POSTED, '%Y-%m-%d') >= '$COMPANY_JOB_POSTED_DATE_BEGIN_DATE' ";
} else if ($_REQUEST['COMPANY_JOB_POSTED_DATE_END_DATE'] != '') {
	$COMPANY_JOB_POSTED_DATE_END_DATE = date("Y-m-d", strtotime($_REQUEST['COMPANY_JOB_POSTED_DATE_END_DATE']));

	$cond .= " AND DATE_FORMAT(S_COMPANY_JOB.JOB_POSTED, '%Y-%m-%d') <= '$COMPANY_JOB_POSTED_DATE_END_DATE' ";
}

if ($_REQUEST['COMPANY_JOB_TITLE'] != '') {
	$COMPANY_JOB_TITLE = trim($_REQUEST['COMPANY_JOB_TITLE']);
	$cond .= " AND trim(S_COMPANY_JOB.JOB_TITLE) = '$COMPANY_JOB_TITLE' ";
}

if ($_REQUEST['COMPANY_JOB_PAY_AMOUNT_FROM'] != '' && $_REQUEST['COMPANY_JOB_PAY_AMOUNT_TO'] != '') {
	$cond .= " AND S_COMPANY_JOB.PAY_AMOUNT BETWEEN $_REQUEST[COMPANY_JOB_PAY_AMOUNT_FROM] AND $_REQUEST[COMPANY_JOB_PAY_AMOUNT_TO] ";
} else if ($_REQUEST['COMPANY_JOB_PAY_AMOUNT_FROM'] != '') {
	$cond .= " AND S_COMPANY_JOB.PAY_AMOUNT >= $_REQUEST[COMPANY_JOB_PAY_AMOUNT_FROM] ";
} else if ($_REQUEST['COMPANY_JOB_PAY_AMOUNT_TO'] != '') {
	$cond .= " AND S_COMPANY_JOB.PAY_AMOUNT <= $_REQUEST[COMPANY_JOB_PAY_AMOUNT_TO] ";
}
//end of new ocnditions 
$query = "SELECT * , S_COMPANY.PK_COMPANY as PK_COMPANY_ID , 
	(SELECT Count(*) FROM S_COMPANY_JOB WHERE S_COMPANY.PK_COMPANY =S_COMPANY_JOB.PK_COMPANY AND  S_COMPANY_JOB.PK_ACCOUNT = '" . $_SESSION['PK_ACCOUNT'] . "') as COMPANY_TOTAL_JOBS,
	(SELECT Count(*) FROM S_COMPANY_JOB WHERE S_COMPANY.PK_COMPANY =S_COMPANY_JOB.PK_COMPANY AND OPEN_JOB = 'Y' AND  S_COMPANY_JOB.PK_ACCOUNT = '" . $_SESSION['PK_ACCOUNT'] . "') as COMPANY_OPEN_JOB

	FROM S_COMPANY
    LEFT JOIN S_COMPANY_EVENT ON S_COMPANY_EVENT.PK_COMPANY = S_COMPANY.PK_COMPANY
	LEFT OUTER JOIN S_COMPANY_CAMPUS ON S_COMPANY_CAMPUS.PK_COMPANY = S_COMPANY.PK_COMPANY
	LEFT JOIN S_COMPANY_JOB ON S_COMPANY_JOB.PK_COMPANY = S_COMPANY.PK_COMPANY
	WHERE S_COMPANY.PK_ACCOUNT = '" . $_SESSION['PK_ACCOUNT'] . "' $cond GROUP BY S_COMPANY.PK_COMPANY
 ORDER BY COMPANY_NAME ASC";




$log_array = array('SQL_QUERY' => $query, 'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'], 'PK_USER' => $_SESSION['PK_USER'], 'REPORT_NAME' => 'Company Report Selection');
log_query($log_array);
//  echo $query;exit;
$res_type = $db->Execute($query);

$rows = [];
// $rows[] = $header = array_keys($res_type->fields);
$global_cache = new ApcCache();
while (!$res_type->EOF) {


	$row = [];
	// PK_COMPANY
	$row[] = $res_type->fields['PK_COMPANY_ID'];
	// COMPANY_NAME
	$row[] = $res_type->fields['COMPANY_NAME'];
	// COMPANY_CITY
	$row[] = $res_type->fields['CITY'];
	// DATE - COMPANY - DATE_CREATED [COMPANY_DATE_CREATED_BEGIN_DATE,COMPANY_DATE_CREATED_END_DATE]
	$row[] = $res_type->fields['DATE_CREATED'];
	// COMPANY_PHONE: 
	$row[] = $res_type->fields['PHONE'];
	// COMPANY_WEBSITE: 
	$row[] = $res_type->fields['WEBSITE'];


	//COMPANY_TOTAL_JOBS
	$row[] = $res_type->fields['COMPANY_TOTAL_JOBS'];
	//COMPANY_OPEN_JOB
	$row[] = $res_type->fields['COMPANY_OPEN_JOB'];
	// COMPANY_FAX
	// $row[] = $res_type->fields['FAX'];
	// COMPANY_SOURCE
	// $row[] = $res_type->fields['COMPANY_SOURCE'];
	// COMPANY_PHONE
	// $row[] = $res_type->fields['PHONE'];
	// foreach ($res_type->fields as $key => $value) {
	// 	$row[] = $value;
	// }
	if ($_REQUEST['show_contacts'] == 'true') {
		//$response['contactlists'][]  = $res_type->fields['contactslist']; 
		$comp_id = $res_type->fields['PK_COMPANY_ID'];
		$query = "SELECT * FROM S_COMPANY_CONTACT WHERE PK_COMPANY = $comp_id";
		$contact_list = $db->Execute($query);
		while (!$contact_list->EOF) {

			// print_r($contact_list->fields);
			// $contact_str = '';
			$contact_str = "<b>" . $contact_list->fields['NAME'] . "</b>";
			if ($contact_list->fields['TITLE'])
				$contact_str .= "<br>(" . $contact_list->fields['TITLE'] . ")";
			if ($contact_list->fields['PHONE'])
				$contact_str .= "<br>" . $contact_list->fields['PHONE'];
			if ($contact_list->fields['EMAIL'])
				$contact_str .= "<br>" . $contact_list->fields['EMAIL'];

			$contact_str .= "<br>";
			$contact_list->MoveNext();
		}
		$row[] = $contact_str;
	}


	if ($selected_columns) {

		foreach ($selected_columns as $key => $value) {

			// if ($value == 'COMPANY_MAIN_CONTACT') {
			// 	$value = 'PK_COMPANY_CONTACT';
			// }

			if (in_array($value, $multi_selects)) {

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
					if ($value == 'PK_COMPANY_CONTACT') {
						// echo "apc fetch >>";
						// print_r($cached_Data);
						// apcu_cache_info();
					}
				} else {	
					if (in_array($value, [
						'PK_COMPANY_SOURCE',
						'PK_PLACEMENT_COMPANY_STATUS',
						'PK_COMPANY_CONTACT',
						'PK_PLACEMENT_TYPE'
					]) && $res_type->fields[$value] == 0) {
						$row[] = '--';
					} else
					$row[] = $res_type->fields[$value];
				};
				// exit;
			}
		}
	}


	$rows[] = $row;
	$res_type->MoveNext();
}

##Special sql required


##APCu Cache required 
// COMPANY_MAIN_CONTACT:
// COMPANY_SCHOOL_EMPLOYEE: 
// COMPANY_STATE: 
// COMPANY_STATUS: 
// COMPANY_TYPE: 

#remove this unused filters
// COMPANY_OPEN_JOB: 
// COMPANY_TOTAL_JOBS: 






// COMPANY_EVENT_COMPANY_CONTACT: 
// COMPANY_EVENT_COMPLETE: 
// COMPANY_EVENT_BEGIN_DATE: 
// COMPANY_EVENT_END_DATE: 
// COMPANY_EVENT_TYPE: 
// COMPANY_EVENT_FOLLOWUP_BEGIN_DATE: 
// COMPANY_EVENT_FOLLOWUP_END_DATE: 
// COMPANY_EVENT_SCHOOL_EMPLOYEE: 



// COMPANY_JOB_FULL_PART_TIME: 
// COMPANY_JOB_CANCELLED_BEGIN_DATE: 
// COMPANY_JOB_CANCELLED_END_DATE: 
// COMPANY_JOB_FILLED_BEGIN_DATE: 
// COMPANY_JOB_FILLED_END_DATE: 
// COMPANY_JOB_NO: 
// COMPANY_JOB_POSTED_DATE_BEGIN_DATE: 
// COMPANY_JOB_POSTED_DATE_END_DATE: 
// COMPANY_JOB_TITLE: 
// COMPANY_JOB_OPEN_JOB: 
// COMPANY_JOB_PAY_AMOUNT_FROM: 
// COMPANY_JOB_PAY_AMOUNT_TO: 
$response['select_col'] = $selected_columns;

$response['data'] = $rows;
$response['query'] = $query;
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
