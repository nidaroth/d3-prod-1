<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/tuition_batch.php");
require_once("function_student_ledger.php");
require_once("function_update_disbursement_status.php");
require_once("function_update_estimate_fee_status.php");
require_once("function_unpost_batch_history.php");

require_once("check_access.php");

if (check_access('MANAGEMENT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	 //echo "<pre>";print_r($_POST);exit;

	if ($_POST['STS_HID'] == 3) {
		$PK_TUITION_BATCH_MASTER = $_GET['id'];
		$TUITION_BATCH_MASTER['POSTED_DATE'] 		= '';
		$TUITION_BATCH_MASTER['PK_BATCH_STATUS'] 	= $_POST['STS_HID'];
		$TUITION_BATCH_MASTER['EDITED_BY'] 			= $_SESSION['PK_USER'];
		$TUITION_BATCH_MASTER['EDITED_ON'] 			= date("Y-m-d H:i");
		db_perform('S_TUITION_BATCH_MASTER', $TUITION_BATCH_MASTER, 'update', " PK_TUITION_BATCH_MASTER = '$PK_TUITION_BATCH_MASTER' AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' ");

		$UNPOSTED_HISTORY['PK_TUITION_BATCH_MASTER']  	= $PK_TUITION_BATCH_MASTER;
		$UNPOSTED_HISTORY['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
		$UNPOSTED_HISTORY['UNPOSTED_BY'] 				= $_SESSION['PK_USER'];
		$UNPOSTED_HISTORY['UNPOSTED_ON'] 				= date("Y-m-d H:i");
		db_perform('S_TUITION_BATCH_UNPOSTED_HISTORY', $UNPOSTED_HISTORY, 'insert');

		$res_det = $db->Execute("SELECT PK_TUITION_BATCH_DETAIL, PK_STUDENT_FEE_BUDGET FROM S_TUITION_BATCH_DETAIL WHERE PK_TUITION_BATCH_MASTER = '$PK_TUITION_BATCH_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		while (!$res_det->EOF) {
			$PK_TUITION_BATCH_DETAIL = $res_det->fields['PK_TUITION_BATCH_DETAIL'];
			$PK_STUDENT_FEE_BUDGET 	 = $res_det->fields['PK_STUDENT_FEE_BUDGET'];

			$ledger_data_del['PK_TUITION_BATCH_DETAIL'] = $PK_TUITION_BATCH_DETAIL;
			delete_student_ledger($ledger_data_del);

			if ($PK_STUDENT_FEE_BUDGET > 0) {
				$STUDENT_FEE_BUDGET['PK_ESTIMATE_FEE_STATUS']  		= 4;
				$STUDENT_FEE_BUDGET['FEE_BUDGET_DEPOSITED_DATE']  	= '';
				db_perform('S_STUDENT_FEE_BUDGET', $STUDENT_FEE_BUDGET, 'update', " PK_STUDENT_FEE_BUDGET = '$PK_STUDENT_FEE_BUDGET' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			}

			$res_det->MoveNext();
		}

		header("location:tuition_batch?id=" . $PK_TUITION_BATCH_MASTER);
		exit;
	}

	$OLD_PK_BATCH_STATUS = 0;

	$TUITION_BATCH_MASTER['TUITION_BATCH_PK_CAMPUS']  	= implode(",", $_POST['TUITION_BATCH_PK_CAMPUS']);
	$TUITION_BATCH_MASTER['TYPE']  						= $_POST['TYPE'];
	$TUITION_BATCH_MASTER['PK_TERM_MASTER'] 			= $_POST['PK_TERM_MASTER'];
	$TUITION_BATCH_MASTER['STUDENT_TYPE'] 				= $_POST['STUDENT_TYPE'];
	$TUITION_BATCH_MASTER['TRANS_DATE'] 				= $_POST['TRANS_DATE'];
	$TUITION_BATCH_MASTER['COMMENTS'] 					= $_POST['COMMENTS'];
	$TUITION_BATCH_MASTER['TRANS_DATA_TYPE'] 			= $_POST['TRANS_DATA_TYPE']; // DIAM-1446

	if ($TUITION_BATCH_MASTER['TYPE'] == 1) {
		$TUITION_BATCH_MASTER['PK_CAMPUS_PROGRAM'] 	= implode(",", $_POST['PK_CAMPUS_PROGRAM']);
		$TUITION_BATCH_MASTER['AY'] 				= $_POST['AY'];
		$TUITION_BATCH_MASTER['AP'] 				= $_POST['AP'];
		$TUITION_BATCH_MASTER['PK_FEE_TYPE'] 		= $_POST['PK_FEE_TYPE'];
		$TUITION_BATCH_MASTER['OPTION_1'] 			= $_POST['OPTION_1'];
	} else if ($TUITION_BATCH_MASTER['TYPE'] == 2) {
		$TUITION_BATCH_MASTER['PK_CAMPUS_PROGRAM'] 	= implode(",", $_POST['PK_CAMPUS_PROGRAM']); // DIAM - 743, Add new filter Program
		$TUITION_BATCH_MASTER['PK_COURSE'] 			= implode(",", $_POST['PK_COURSE']);
		$TUITION_BATCH_MASTER['PK_COURSE_OFFERING'] = implode(",", $_POST['PK_COURSE_OFFERING']);
		$TUITION_BATCH_MASTER['AY'] 				= $_POST['COURSE_AY'];
		$TUITION_BATCH_MASTER['AP'] 				= $_POST['COURSE_AP'];
	} else if ($TUITION_BATCH_MASTER['TYPE'] == 7) {
		$TUITION_BATCH_MASTER['PK_CAMPUS_PROGRAM'] 	= implode(",", $_POST['PK_CAMPUS_PROGRAM']);
		$TUITION_BATCH_MASTER['AY'] 				= $_POST['AY'];
		$TUITION_BATCH_MASTER['AP'] 				= $_POST['AP'];
	} else if ($TUITION_BATCH_MASTER['TYPE'] == 9) {
		$TUITION_BATCH_MASTER['PK_CAMPUS_PROGRAM'] 	= implode(",", $_POST['PK_CAMPUS_PROGRAM']);
		$TUITION_BATCH_MASTER['AY'] 				= $_POST['AY_FEE'];
		$TUITION_BATCH_MASTER['AP'] 				= $_POST['AP_FEE'];
		$TUITION_BATCH_MASTER['TUITION_START_DATE'] = $_POST['START_DATE'];
		$TUITION_BATCH_MASTER['TUITION_END_DATE'] 	= $_POST['END_DATE'];
		$TUITION_BATCH_MASTER['PK_FEE_TYPE'] 		= $_POST['PK_FEE_TYPE'];
		$TUITION_BATCH_MASTER['OPTION_1'] 			= $_POST['OPTION_1'];

		if ($TUITION_BATCH_MASTER['TUITION_START_DATE'] != '')
			$TUITION_BATCH_MASTER['TUITION_START_DATE'] = date("Y-m-d", strtotime($TUITION_BATCH_MASTER['TUITION_START_DATE']));

		if ($TUITION_BATCH_MASTER['TUITION_END_DATE'] != '')
			$TUITION_BATCH_MASTER['TUITION_END_DATE'] = date("Y-m-d", strtotime($TUITION_BATCH_MASTER['TUITION_END_DATE']));
	}

	if ($_POST['STS_HID'] > 0)
		$TUITION_BATCH_MASTER['PK_BATCH_STATUS'] = $_POST['STS_HID'];

	if ($TUITION_BATCH_MASTER['TRANS_DATE'] != '')
		$TUITION_BATCH_MASTER['TRANS_DATE'] = date("Y-m-d", strtotime($TUITION_BATCH_MASTER['TRANS_DATE']));

	if ($TUITION_BATCH_MASTER['PK_BATCH_STATUS'] == 2)
		$TUITION_BATCH_MASTER['POSTED_DATE'] = date("Y-m-d");

	if ($_GET['id'] == '') {
		$res_acc = $db->Execute("SELECT TUITION_BATCH_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

		$TUITION_BATCH_MASTER['BATCH_NO'] 			= 'T' . $res_acc->fields['TUITION_BATCH_NO'];
		$TUITION_BATCH_MASTER['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
		$TUITION_BATCH_MASTER['CREATED_BY']  		= $_SESSION['PK_USER'];
		$TUITION_BATCH_MASTER['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('S_TUITION_BATCH_MASTER', $TUITION_BATCH_MASTER, 'insert');
		$PK_TUITION_BATCH_MASTER = $db->insert_ID();

		$NEW_BATCH_NO = $res_acc->fields['TUITION_BATCH_NO'] + 1;
		$db->Execute("UPDATE Z_ACCOUNT SET TUITION_BATCH_NO = '$NEW_BATCH_NO' WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	} else {
		$PK_TUITION_BATCH_MASTER = $_GET['id'];
		$res = $db->Execute("SELECT PK_BATCH_STATUS FROM S_TUITION_BATCH_MASTER WHERE PK_TUITION_BATCH_MASTER = '$PK_TUITION_BATCH_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$OLD_PK_BATCH_STATUS = $res->fields['PK_BATCH_STATUS'];


		$TUITION_BATCH_MASTER['EDITED_BY'] = $_SESSION['PK_USER'];
		$TUITION_BATCH_MASTER['EDITED_ON'] = date("Y-m-d H:i");
		// DIAM-993
		if($OLD_PK_BATCH_STATUS==3){
			tuition_unpost_batch_history($PK_TUITION_BATCH_MASTER,$TUITION_BATCH_MASTER);
		}

		db_perform('S_TUITION_BATCH_MASTER', $TUITION_BATCH_MASTER, 'update', " PK_TUITION_BATCH_MASTER = '$PK_TUITION_BATCH_MASTER' AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' ");
	}

	$res = $db->Execute("SELECT PK_BATCH_STATUS FROM S_TUITION_BATCH_MASTER WHERE PK_TUITION_BATCH_MASTER = '$PK_TUITION_BATCH_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$TUITION_BATCH_MASTER['PK_BATCH_STATUS'] = $res->fields['PK_BATCH_STATUS'];

	$i = 0;
	foreach ($_POST['BATCH_PK_TUITION_BATCH_DETAIL'] as $PK_TUITION_BATCH_DETAIL) {

		$TUITION_BATCH_DETAIL = array();

		$TUITION_BATCH_DETAIL['PK_STUDENT_ENROLLMENT'] 		= $_POST['BATCH_PK_STUDENT_ENROLLMENT'][$i];
		$TUITION_BATCH_DETAIL['PK_STUDENT_MASTER'] 			= $_POST['BATCH_PK_STUDENT_MASTER'][$i];
		$TUITION_BATCH_DETAIL['PK_AR_LEDGER_CODE']  		= $_POST['BATCH_PK_AR_LEDGER_CODE'][$i];
		$TUITION_BATCH_DETAIL['TRANSACTION_DATE']  			= $_POST['BATCH_TRANSACTION_DATE'][$i];
		$TUITION_BATCH_DETAIL['AMOUNT']  					= $_POST['BATCH_AMOUNT'][$i];
		$TUITION_BATCH_DETAIL['PK_TERM_BLOCK']  			= $_POST['BATCH_PK_TERM_BLOCK'][$i];
		$TUITION_BATCH_DETAIL['BATCH_DETAIL_DESCRIPTION']  	= $_POST['BATCH_DETAIL_DESCRIPTION'][$i];
		$TUITION_BATCH_DETAIL['PK_STUDENT_FEE_BUDGET']  	= $_POST['BATCH_PK_STUDENT_FEE_BUDGET'][$i];

		$TUITION_BATCH_DETAIL['TUITION_BATCH_DETAIL_AY']  					= $_POST['TUITION_BATCH_DETAIL_AY'][$i];
		$TUITION_BATCH_DETAIL['TUITION_BATCH_DETAIL_AP']  					= $_POST['TUITION_BATCH_DETAIL_AP'][$i];
		$TUITION_BATCH_DETAIL['TUITION_BATCH_DETAIL_PRIOR_YEAR']  			= $_POST['BATCH_PRIOR_YEAR'][$i];
		$TUITION_BATCH_DETAIL['TUITION_BATCH_DETAIL_PK_COURSE_OFFERING']  	= $_POST['TUITION_BATCH_DETAIL_PK_COURSE_OFFERING'][$i];
		$TUITION_BATCH_DETAIL['TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM']  	= $_POST['TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM'][$i];

		if ($TUITION_BATCH_DETAIL['TRANSACTION_DATE'] != '')
			$TUITION_BATCH_DETAIL['TRANSACTION_DATE'] = date("Y-m-d", strtotime($TUITION_BATCH_DETAIL['TRANSACTION_DATE']));

		/*$res_stu = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$TUITION_BATCH_DETAIL[PK_STUDENT_ENROLLMENT]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
		$TUITION_BATCH_DETAIL['PK_STUDENT_MASTER'] = $res_stu->fields['PK_STUDENT_MASTER'];*/

		if ($PK_TUITION_BATCH_DETAIL == '') {
			$TUITION_BATCH_DETAIL['PK_TUITION_BATCH_MASTER'] = $PK_TUITION_BATCH_MASTER;
			$TUITION_BATCH_DETAIL['PK_ACCOUNT']  			 = $_SESSION['PK_ACCOUNT'];
			$TUITION_BATCH_DETAIL['CREATED_BY']  			 = $_SESSION['PK_USER'];
			$TUITION_BATCH_DETAIL['CREATED_ON']  			 = date("Y-m-d H:i");
			db_perform('S_TUITION_BATCH_DETAIL', $TUITION_BATCH_DETAIL, 'insert');
			$PK_TUITION_BATCH_DETAIL = $db->insert_ID();

			$PK_TUITION_BATCH_DETAIL_ARR[] = $PK_TUITION_BATCH_DETAIL;
		} else {
			$TUITION_BATCH_DETAIL['EDITED_BY']  = $_SESSION['PK_USER'];
			$TUITION_BATCH_DETAIL['EDITED_ON']  = date("Y-m-d H:i");
			// DIAM-993
			if($OLD_PK_BATCH_STATUS==3){
				tuition_unpost_batch_history($PK_TUITION_BATCH_MASTER,$TUITION_BATCH_DETAIL,$PK_TUITION_BATCH_DETAIL);
			}
			
			db_perform('S_TUITION_BATCH_DETAIL', $TUITION_BATCH_DETAIL, 'update', " PK_TUITION_BATCH_DETAIL = '$PK_TUITION_BATCH_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

			$PK_TUITION_BATCH_DETAIL_ARR[] = $PK_TUITION_BATCH_DETAIL;
		}

		if ($TUITION_BATCH_MASTER['PK_BATCH_STATUS'] == 2) {
			$ledger_data['PK_TUITION_BATCH_DETAIL'] = $PK_TUITION_BATCH_DETAIL;
			$ledger_data['PK_AR_LEDGER_CODE'] 		= $TUITION_BATCH_DETAIL['PK_AR_LEDGER_CODE'];
			$ledger_data['AMOUNT'] 					= $TUITION_BATCH_DETAIL['AMOUNT'];
			$ledger_data['DATE'] 					= $TUITION_BATCH_DETAIL['TRANSACTION_DATE'];
			$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $TUITION_BATCH_DETAIL['PK_STUDENT_ENROLLMENT'];
			$ledger_data['PK_STUDENT_MASTER'] 		= $TUITION_BATCH_DETAIL['PK_STUDENT_MASTER'];
			student_ledger($ledger_data);
		}

		if ($TUITION_BATCH_MASTER['TYPE'] == 9 && $TUITION_BATCH_DETAIL['PK_STUDENT_FEE_BUDGET'] > 0) {
			if ($TUITION_BATCH_MASTER['PK_BATCH_STATUS'] == 1) {
				//hold
				$STUDENT_FEE_BUDGET['PK_ESTIMATE_FEE_STATUS']  		= 3;
				$STUDENT_FEE_BUDGET['FEE_BUDGET_DEPOSITED_DATE']  	= '';
			} else if ($TUITION_BATCH_MASTER['PK_BATCH_STATUS'] == 2) {
				//posted
				$STUDENT_FEE_BUDGET['PK_ESTIMATE_FEE_STATUS']  		= 1;
				$STUDENT_FEE_BUDGET['FEE_BUDGET_DEPOSITED_DATE']  	= $TUITION_BATCH_DETAIL['TRANSACTION_DATE'];
			} else if ($TUITION_BATCH_MASTER['PK_BATCH_STATUS'] == 3) {
				//Unpost
				$STUDENT_FEE_BUDGET['PK_ESTIMATE_FEE_STATUS']  		= 4;
				$STUDENT_FEE_BUDGET['FEE_BUDGET_DEPOSITED_DATE']  	= '';
			}

			$STUDENT_FEE_BUDGET['PK_TUITION_BATCH_DETAIL']  = $PK_TUITION_BATCH_DETAIL;
			db_perform('S_STUDENT_FEE_BUDGET', $STUDENT_FEE_BUDGET, 'update', " PK_STUDENT_FEE_BUDGET = '$TUITION_BATCH_DETAIL[PK_STUDENT_FEE_BUDGET]' AND PK_STUDENT_FEE_BUDGET > 0 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}

		$i++;
	}

	//echo $_POST['STS_HID'];exit;
	//if($_POST['STS_HID'] == 1 || $_POST['STS_HID'] == 2) {

	$cond = " ";
	if (!empty($PK_TUITION_BATCH_DETAIL_ARR))
		$cond = " AND PK_TUITION_BATCH_DETAIL NOT IN (" . implode(",", $PK_TUITION_BATCH_DETAIL_ARR) . ") ";

	$res_det = $db->Execute("SELECT PK_TUITION_BATCH_DETAIL, PK_STUDENT_FEE_BUDGET, PK_STUDENT_ENROLLMENT, PK_STUDENT_MASTER FROM S_TUITION_BATCH_DETAIL WHERE PK_TUITION_BATCH_MASTER = '$PK_TUITION_BATCH_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
	while (!$res_det->EOF) {
		$PK_TUITION_BATCH_DETAIL = $res_det->fields['PK_TUITION_BATCH_DETAIL'];
		$PK_STUDENT_FEE_BUDGET 	 = $res_det->fields['PK_STUDENT_FEE_BUDGET'];
		$PK_STUDENT_ENROLLMENT 	 = $res_det->fields['PK_STUDENT_ENROLLMENT'];
		$PK_STUDENT_MASTER 	 	 = $res_det->fields['PK_STUDENT_MASTER'];

		$db->Execute("DELETE FROM S_TUITION_BATCH_DETAIL WHERE PK_TUITION_BATCH_DETAIL = '$PK_TUITION_BATCH_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

		$ledger_data_del['PK_TUITION_BATCH_DETAIL'] = $PK_TUITION_BATCH_DETAIL;
		delete_student_ledger($ledger_data_del);

		if ($PK_STUDENT_FEE_BUDGET > 0) {
			$STUDENT_FEE_BUDGET['PK_TUITION_BATCH_DETAIL']  	= '';
			$STUDENT_FEE_BUDGET['FEE_BUDGET_DEPOSITED_DATE']  	= '';
			db_perform('S_STUDENT_FEE_BUDGET', $STUDENT_FEE_BUDGET, 'update', " PK_STUDENT_FEE_BUDGET = '$PK_STUDENT_FEE_BUDGET' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			update_estimate_fee_status($PK_STUDENT_MASTER, $PK_STUDENT_ENROLLMENT);
		}

		$res_det->MoveNext();
	}
	//}
	//exit;
	if ($_POST['STS_HID'] == 1 || $_POST['STS_HID'] == 2) {
		header("location:tuition_batch?id=" . $PK_TUITION_BATCH_MASTER);
	} else {
		header("location:manage_tuition_batch");
	}
}
if ($_GET['id'] == '') {
	$BATCH_NO 						= '';
	$TRANS_DATE	 					= date("m/d/Y");
	$COMMENTS						= '';
	$PK_TERM_MASTER	 				= '';
	$TYPE							= '';
	$STUDENT_TYPE					= '';
	$PK_CAMPUS_PROGRAM_ARR			= array();
	$PK_COURSE_ARR					= array();
	$PK_COURSE_OFFERING_ARR			= array();
	$PK_BATCH_STATUS				= '';
	$AY								= '';
	$AP								= '';
	$PK_FEE_TYPE					= 2;
	$OPTION_1						= 2; // DIAM-1357, change the selection
	$show_all 						= 0;
	$START_DATE 					= '';
	$END_DATE						= '';
	$POSTED_DATE					= '';
	$TRANS_DATA_TYPE    			= ''; // DIAM-1446
	$TUITION_BATCH_PK_CAMPUS_ARR 	= array();

	$COURSE_AY = 1;
	$COURSE_AP = 1;

	$AY	= 1;
	$AP	= 1;

	/* Ticket #849  */
	$res_camp = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res_camp->RecordCount() == 1) {
		$TUITION_BATCH_PK_CAMPUS1 		= $res_camp->fields['PK_CAMPUS'];
		$TUITION_BATCH_PK_CAMPUS_ARR	= explode(",", $TUITION_BATCH_PK_CAMPUS1);
	}
	/* Ticket #849  */

	$res_acc = $db->Execute("SELECT TUITION_BATCH_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$BATCH_NO = 'T' . $res_acc->fields['TUITION_BATCH_NO'];
} else {
	$res = $db->Execute("SELECT * FROM S_TUITION_BATCH_MASTER WHERE PK_TUITION_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res->RecordCount() == 0) {
		header("location:manage_tuition_batch");
		exit;
	}
	$BATCH_NO 						= $res->fields['BATCH_NO'];
	$TRANS_DATE  					= $res->fields['TRANS_DATE'];
	$COMMENTS  						= $res->fields['COMMENTS'];
	$PK_TERM_MASTER  				= $res->fields['PK_TERM_MASTER'];
	$TYPE							= $res->fields['TYPE'];
	$STUDENT_TYPE					= $res->fields['STUDENT_TYPE'];
	$PK_CAMPUS_PROGRAM_ARR			= explode(",", $res->fields['PK_CAMPUS_PROGRAM']);
	$PK_COURSE_ARR					= explode(",", $res->fields['PK_COURSE']);
	$PK_COURSE_OFFERING_ARR			= explode(",", $res->fields['PK_COURSE_OFFERING']);
	$PK_BATCH_STATUS				= $res->fields['PK_BATCH_STATUS'];
	$AY								= $res->fields['AY'];
	$AP								= $res->fields['AP'];
	$PK_FEE_TYPE					= $res->fields['PK_FEE_TYPE'];
	$OPTION_1						= $res->fields['OPTION_1'];
	$START_DATE						= $res->fields['TUITION_START_DATE'];
	$END_DATE						= $res->fields['TUITION_END_DATE'];
	$POSTED_DATE					= $res->fields['POSTED_DATE'];
	$TRANS_DATA_TYPE    			= $res->fields['TRANS_DATA_TYPE']; // DIAM-1446

	$COURSE_AY = $res->fields['AY'];
	$COURSE_AP = $res->fields['AP'];

	$TUITION_BATCH_PK_CAMPUS_ARR	= explode(",", $res->fields['TUITION_BATCH_PK_CAMPUS']);

	if ($TYPE == 7)
		$show_all = 1;
	else
		$show_all = 0;

	if ($TRANS_DATE == '0000-00-00')
		$TRANS_DATE = '';
	else
		$TRANS_DATE = date("m/d/Y", strtotime($TRANS_DATE));

	if ($START_DATE == '0000-00-00')
		$START_DATE = '';
	else
		$START_DATE = date("m/d/Y", strtotime($START_DATE));

	if ($END_DATE == '0000-00-00')
		$END_DATE = '';
	else
		$END_DATE = date("m/d/Y", strtotime($END_DATE));

	if ($POSTED_DATE == '0000-00-00')
		$POSTED_DATE = '';
	else
		$POSTED_DATE = date("m/d/Y", strtotime($POSTED_DATE));
}
$res = $db->Execute("SELECT BATCH_STATUS FROM M_BATCH_STATUS WHERE PK_BATCH_STATUS = '$PK_BATCH_STATUS' ");
$BATCH_STATUS = $res->fields['BATCH_STATUS'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?= TUITION_BATCH_PAGE_TITLE ?> | <?= $title ?></title>
	<style>
		.no-records-found {
			display: none;
		}

		li>a>label {
			position: unset !important;
		}

		.dropdown-menu>li>a {
			white-space: nowrap;
		}

		.table th,
		.table td {
			padding: 0.5rem;
		}

		.tableFixHead {
			overflow-y: auto;
			max-height: 500px;
		}

		.tableFixHead thead th {
			position: sticky;
			top: 0;
		}

		.tableFixHead thead th {
			background: #E8E8E8;
		}
.lds-ring {
	position: absolute;
	left: 0;
	top: 0;
	right: 0;
	bottom: 0;
	margin: auto;
	width: 64px;
	height: 64px;
}

.lds-ring div {
	box-sizing: border-box;
	display: block;
	position: absolute;
	width: 51px;
	height: 51px;
	margin: 6px;
	border: 6px solid #0066ac;
	border-radius: 50%;
	animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
	border-color: #007bff transparent transparent transparent;
}

.lds-ring div:nth-child(1) {
	animation-delay: -0.45s;
}

.lds-ring div:nth-child(2) {
	animation-delay: -0.3s;
}

.lds-ring div:nth-child(3) {
	animation-delay: -0.15s;
}

@keyframes lds-ring {
	0% {
		transform: rotate(0deg);
	}

	100% {
		transform: rotate(360deg);
	}
}

#loaders {
	position: fixed;
	width: 100%;
	z-index: 9999;
	bottom: 0;
	background-color: #2c3e50;
	display: block;
	left: 0;
	top: 0;
	right: 0;
	bottom: 0;
	opacity: 0.6;
	display: none;
}	


	</style>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="loaders" style="display: none;">
		<div class="lds-ring">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-12 align-self-center">
						<h4 class="text-themecolor"><? if ($_GET['id'] == '') echo ADD;
													else echo EDIT; ?> <?= TUITION_BATCH_PAGE_TITLE ?> </h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form class="floating-labels m-t-40" method="post" name="form1" id="form1">

									<div class="row">
										<div class="col-md-4">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40 ">
														<input type="text" class="form-control" id="BATCH_NO" name="BATCH_NO" value="<?= $BATCH_NO ?>" readonly>
														<span class="bar"></span>
														<label for="BATCH_NO"><?= BATCH_NO ?></label>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group m-b-40 ">
														<input type="text" class="form-control" id="BATCH_STATUS" value="<?= $BATCH_STATUS ?>" readonly>
														<span class="bar"></span>
														<label for="BATCH_STATUS"><?= BATCH_STATUS ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="TUITION_BATCH_PK_CAMPUS" name="TUITION_BATCH_PK_CAMPUS[]" multiple class="form-control required-entry" onchange="clear_form()">
															<? $res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by CAMPUS_CODE ASC ");
															while (!$res_type->EOF) {
																$selected = "";
																foreach ($TUITION_BATCH_PK_CAMPUS_ARR as $PK_CAMPUS) {
																	if ($res_type->fields['PK_CAMPUS'] == $PK_CAMPUS) {
																		$selected = "selected";
																		break;
																	}
																}
															?>
																<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <?= $selected ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
															<? $res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span>
													</div>
												</div>
												<?php if(check_access('MANAGEMENT_UNPOST_BATCHES')==1 && $PK_BATCH_STATUS==2  && check_global_access()==1){  ?>
												<div class="col-md-6">
													<button type="button" onclick="save_form(3)" id="UNPOST_BTN" class="btn waves-effect waves-light btn-info"><?=UNPOSTBATCH?></button>
													<span class="bar"></span>
												</div>
												<?php } ?>
											</div>

											<? 
											$trans_date_type_style 	= 'display:none';
											if ($TYPE == 1) {
												$trans_date_type_style 	= 'display:block';
											}

											if ($TYPE == 2) {
												$trans_date_type_style 	    = 'display:block';
											}

											if ($TYPE == 9) { 
												$trans_date_type_style 		= 'display:block';
											} 
											?>

											<!-- DIAM-1446  -->
											<div class="row" id="TRANSACTION_DATE_TYPE_DIV" style="<?=$trans_date_type_style?>" >
												<div class="col-md-12">
													<label for="TRANS_DATE_TYPE">Transaction Date Type</label><br><br>
													<div class="form-group m-b-40">
														<?php
														$checked = '';
														$get_id = $_GET['id'];
														if ($get_id == '') {
															$checked = 'checked';
														}
														?>
														<div class="row form-group">
															<div style="padding-left: 2rem;" class="custom-control custom-radio col-md-6">
																<input type="radio" id="BATCH_DATESaa" name="TRANS_DATA_TYPE" value="1" <?= $checked ?> <? if ($TRANS_DATA_TYPE == '1') {echo "checked";} ?> class="custom-control-input" onchange="set_batch_date(this.value)">
																<label class="custom-control-label" for="BATCH_DATESaa"><?= BATCH_DATE ?></label>
															</div>
															<div class="custom-control custom-radio col-md-6">
																<input type="radio" id="DAYS_START_DATEbb" name="TRANS_DATA_TYPE" value="2" <? if ($TRANS_DATA_TYPE == '2') {echo "checked";} ?> class="custom-control-input" onchange="set_batch_date(this.value)">
																<label class="custom-control-label" for="DAYS_START_DATEbb" id="DATE_TYPE_LBL">Days From Start Date</label>
															</div>
														</div>
													</div>
												</div>
											</div>
											<!-- End DIAM-1446  -->

											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40 ">
														<input type="text" class="form-control date required-entry" id="TRANS_DATE" name="TRANS_DATE" value="<?= $TRANS_DATE ?>" onchange="set_trans_date()"> <!-- Ticket # 1898  -->
														<span class="bar"></span>
														<label for="TRANS_DATE"><?= BATCH_DATE ?></label>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group m-b-40 ">
														<input type="text" class="form-control" id="POSTED_DATE" value="<?= $POSTED_DATE ?>" readonly>
														<span class="bar"></span>
														<label for="POSTED_DATE"><?= POSTED_DATE ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<select id="TYPE" name="TYPE" class="form-control" onchange="check_camp_selected('TYPE')" <? if ($_GET['id'] != '') echo "disabled"; ?>>
															<option></option>
															<option value="2" <? if ($TYPE == 2) echo "selected"; ?>>Course</option>
															<option value="9" <? if ($TYPE == 9) echo "selected"; ?>>Estimated Fees By Student</option> <!-- Ticket # 1151  -->

															<option value="1" <? if ($TYPE == 1) echo "selected"; ?>>Program</option>
															<? /* Ticket #1424 */
															if ($_GET['id'] > 0 && $TYPE == 7) { ?>
																<option value="7" <? if ($TYPE == 7) echo "selected"; ?>>Estimated Fees By Program</option> <!-- Ticket # 1151  -->
															<? }
															/* Ticket #1424 */ ?>


															<!--<option value="3" <? if ($TYPE == 3) echo "selected"; ?> >Unit Tuition</option>
															<option value="4" <? if ($TYPE == 4) echo "selected"; ?> >Term Tuition</option>
															<option value="5" <? if ($TYPE == 5) echo "selected"; ?> >Term Other</option>
															<option value="6" <? if ($TYPE == 6) echo "selected"; ?> >Unit Progressive</option>
															<option value="8" <? if ($TYPE == 8) echo "selected"; ?> >Scheduled Hours</option>-->
														</select>
														<span class="bar"></span>
														<label for="TYPE"><?= TYPE ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<select id="STUDENT_TYPE" name="STUDENT_TYPE" class="form-control" onchange="check_camp_selected('STUDENT_TYPE')" <? if ($_GET['id'] != '') echo "disabled"; ?>>
															<option></option>

															<option value="3" <? if ($STUDENT_TYPE == 3) echo "selected"; ?>>Course Students</option>
															<option value="2" <? if ($STUDENT_TYPE == 2) echo "selected"; ?>>Program Students</option>

															<!--<option value="1" <? if ($STUDENT_TYPE == 1) echo "selected"; ?> >All Students</option>
															<option value="4" <? if ($STUDENT_TYPE == 4) echo "selected"; ?> >Unit Students</option>
															<option value="5" <? if ($STUDENT_TYPE == 5) echo "selected"; ?> >Term Students</option>
															<option value="6" <? if ($STUDENT_TYPE == 6) echo "selected"; ?> >Selected Students</option>
															<option value="8" <? if ($STUDENT_TYPE == 7) echo "selected"; ?> >Scheduled Hours</option>-->
														</select>
														<span class="bar"></span>
														<label for="STUDENT_TYPE"><?= STUDENT_TYPE ?></label>
													</div>
												</div>
											</div>
										</div>

										<div class="col-md-1" style="flex: 0 0 3%;max-width: 3%;"></div>
										<div class="col-md-2" style="flex: 0 0 25%;max-width: 25%;">
											<div class="row">
												<div class="col-md-4">
													<?= DEBIT_TOTAL ?>
												</div>
												<div class="col-md-5" id="DEBIT_TOTAL" style="text-align:right;">
												</div>
											</div>

											<div class="row">
												<div class="col-md-4" style="border-top:1px solid #000;font-weight:bold">
													<?= BATCH_TOTAL ?>
												</div>
												<div class="col-md-5" id="BATCH_TOTAL" style="border-top:1px solid #000;text-align:right;font-weight:bold">
												</div>
											</div>
											<br />

											<? $program_style 	= 'display:none';
											$course_style 		= 'display:none';
											$ay_style 			= 'display:none';
											$ap_style 			= 'display:none';
											$ay_style_est_fee 	= 'display:none'; // DIAM-1539
											$ap_style_est_fee 	= 'display:none'; // DIAM-1539
											$FEE_TYPE_style 	= 'display:none';
											$date_range_style 	= 'display:none'; //Ticket # 1151
											//$term_style			= 'display:none';

											$course_ay_style 	= 'display:none';
											$course_ap_style 	= 'display:none';
											if ($TYPE == 1) {
												$program_style 	= 'display:block';
												$ay_style 		= 'display:block';
												$ap_style 		= 'display:block';
												$FEE_TYPE_style = 'display:block';
											}
											if ($TYPE == 7) {
												$program_style 	= 'display:block';
												$ay_style 		= 'display:block';
												$ap_style 		= 'display:block';
											}
											if ($TYPE == 9) { //Ticket # 1151
												$program_style 		= 'display:block';
												$ay_style_est_fee 	= 'display:block'; // DIAM-1539
												$ap_style_est_fee 	= 'display:block'; // DIAM-1539
												$date_range_style 	= 'display:flex';
												$FEE_TYPE_style = 'display:block';
											} //Ticket # 1151

											if ($TYPE == 2) {
												$program_style 	    = 'display:block';
												$course_style 		= 'display:block';
												$course_ay_style 	= 'display:flex';
												$course_ap_style 	= 'display:block';
												//$term_style			= 'display:block';
											} ?>

											<div class="row" id="PK_TERM_MASTER_DIV" style="<?= $term_style ?>">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control" onchange="check_camp_selected('PK_TERM_MASTER')">
															<option></option>
															<? /* Ticket #1149 - term */
															$res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
															while (!$res_type->EOF) {
																$str = $res_type->fields['BEGIN_DATE_1'] . ' - ' . $res_type->fields['END_DATE_1'] . ' - ' . $res_type->fields['TERM_DESCRIPTION'];
																if ($res_type->fields['ACTIVE'] == 0)
																	$str .= ' (Inactive)'; ?>
																<option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>" <? if ($PK_TERM_MASTER == $res_type->fields['PK_TERM_MASTER']) echo "selected"; ?> <? if ($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $str ?></option>
															<? $res_type->MoveNext();
															} /* Ticket #1149 - term */ ?>
														</select>
														<span class="bar"></span>
														<label for="PK_TERM_MASTER" id="TERM_MASTER_LBL">Term Date</label>
													</div>
												</div>
											</div>

											<!--Ticket # 1151 -->
											<div class="row" id="DATE_RANGE_DIV" style="<?= $date_range_style ?>">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="<?= $START_DATE ?>" onchange="clear_form()">
														<span class="bar"></span>
														<label for="START_DATE" id="START_DATE_LBL"><?= START_DATE ?></label>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date" id="END_DATE" name="END_DATE" value="<?= $END_DATE ?>" onchange="clear_form()">
														<span class="bar"></span>
														<label for="END_DATE" id="END_DATE_LBL"><?= END_DATE ?></label>
													</div>
												</div>
											</div>
											<!--Ticket # 1151 -->

										
											<div class="row">
												<div class="col-md-6" id="AY_DIV" style="<?= $ay_style ?>">
													<div class="form-group m-b-40">
														<div id="AY_DIV_1">
															<? $_REQUEST['val'] 			= $AY;
															$_REQUEST['show_all'] 			= $show_all;
															$_REQUEST['PK_CAMPUS_PROGRAM'] 	= implode(",", $PK_CAMPUS_PROGRAM_ARR);
															include('get_ay_from_program.php'); ?>
														</div>

														<span class="bar"></span>
														<label for="AY"><?= AY ?></label>
													</div>
												</div>

												<div class="col-md-6" id="AP_DIV" style="<?= $ap_style ?>">
													<div class="form-group m-b-40">
														<div id="AP_DIV_1">
															<? $_REQUEST['val'] 			= $AP;
															$_REQUEST['show_all'] 			= $show_all;
															$_REQUEST['PK_CAMPUS_PROGRAM'] 	= implode(",", $PK_CAMPUS_PROGRAM_ARR);
															include('get_ap_from_program.php'); ?>
														</div>

														<span class="bar"></span>
														<label for="AP"><?= AP ?></label>
													</div>
												</div>

												<!-- DIAM-1539 -->
												<div class="col-md-6" id="AY_DIV_EST_FEE" style="<?= $ay_style_est_fee ?>">
													<div class="form-group m-b-40">
														<div >
															<? $_REQUEST['val'] 			= $AY;
															$_REQUEST['show_all'] 			= $show_all;
															$_REQUEST['PK_CAMPUS_PROGRAM'] 	= implode(",", $PK_CAMPUS_PROGRAM_ARR);
															include('get_ay_from_est_fee.php'); ?>
														</div>

														<span class="bar"></span>
														<label for="AY"><?= AY ?></label>
													</div>
												</div>

												<div class="col-md-6" id="AP_DIV_EST_FEE" style="<?= $ap_style_est_fee ?>">
													<div class="form-group m-b-40">
														<div >
															<? $_REQUEST['val'] 			= $AP;
															$_REQUEST['show_all'] 			= $show_all;
															$_REQUEST['PK_CAMPUS_PROGRAM'] 	= implode(",", $PK_CAMPUS_PROGRAM_ARR);
															include('get_ap_from_est_fee.php'); ?>
														</div>

														<span class="bar"></span>
														<label for="AP"><?= AP ?></label>
													</div>
												</div>
												<!-- End DIAM-1539 -->

											</div>

											<div class="row" id="OPTION_1_DIV" style="<?= $FEE_TYPE_style ?>">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<select id="OPTION_1" name="OPTION_1" class="form-control" onchange="clear_form()">
															<option value=""></option>
															<option value="2" <? if ($OPTION_1 == 2) echo "selected"; ?>>Student in a Course ANY Term</option>
															<option value="1" <? if ($OPTION_1 == 1) echo "selected"; ?>>Student in a Course THIS Term</option>
														</select>
														<span class="bar"></span>
														<label for="OPTION_1"><?= OPTION_1 ?></label>
													</div>
												</div>
											</div>

											<div class="row" id="PK_FEE_TYPE_DIV" style="<?= $FEE_TYPE_style ?>">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<select id="PK_FEE_TYPE" name="PK_FEE_TYPE" class="form-control" onchange="clear_form()">
															<option value="1" <? if ($PK_FEE_TYPE == 1) echo "selected"; ?>>Budget Only</option>
															<option value="2" <? if ($PK_FEE_TYPE == 2) echo "selected"; ?>>Tuition & Budget</option>
														</select>
														<span class="bar"></span>
														<label for="PK_FEE_TYPE"><?= OPTION_2 ?></label>
													</div>
												</div>
											</div>

											<div class="row" id="COURSE_DIV" style="<?= $course_style ?>">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<?= COURSE ?>
														<div id="COURSE_DIV_1">
															<? $_REQUEST['def_val'] 	= $PK_COURSE_ARR;
															$_REQUEST['PK_TERM'] 		= $PK_TERM_MASTER;
															$_REQUEST['page'] 			= 'tuition';
															include('ajax_get_course_from_term.php'); ?>
														</div>
													</div>
												</div>
											</div>

											<div class="row" id="COURSE_OFFERING_DIV" style="<?= $course_style ?>">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<?= COURSE_OFFERING ?>
														<div id="COURSE_OFFERING_DIV_1">
															<? $_REQUEST['val'] 			= implode(",", $PK_COURSE_ARR);
															$_REQUEST['PK_TERM_MASTER'] 	= $PK_TERM_MASTER;
															$_REQUEST['PK_COURSE_OFFERING'] = $PK_COURSE_OFFERING_ARR;
															$_REQUEST['page'] 				= 'tuition';
															include('ajax_get_course_offering.php'); ?>
														</div>
													</div>
												</div>
											</div>

											<div class="row" id="PROGRAM_DIV" style="<?= $program_style ?>">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														 <select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_form()"> <!-- DIAM-1539, Commented Code get_ay();get_ap(); As suggested in ticket -->
															<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
															while (!$res_type->EOF) {
																$selected = "";
																foreach ($PK_CAMPUS_PROGRAM_ARR as $PK_CAMPUS_PROGRAM) {
																	if ($res_type->fields['PK_CAMPUS_PROGRAM'] == $PK_CAMPUS_PROGRAM) {
																		$selected = "selected";
																		break;
																	}
																} ?>
																<option value="<?= $res_type->fields['PK_CAMPUS_PROGRAM'] ?>" <?= $selected ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['DESCRIPTION'] ?></option>
															<? $res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
											</div>

											<div class="row" id="COURSE_AY_AP_DIV" style="<?= $course_ay_style ?>">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="COURSE_AY" name="COURSE_AY" value="<?= $COURSE_AY ?>">
														<span class="bar"></span>
														<label for="COURSE_AY"><?= AY ?></label>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="COURSE_AP" name="COURSE_AP" value="<?= $COURSE_AP ?>">
														<span class="bar"></span>
														<label for="COURSE_AP"><?= AP ?></label>
													</div>
												</div>
											</div>

										</div>
										<div class="col-md-1" style="flex: 0 0 3%;max-width: 3%;"></div>

										<div class="col-md-4">
											<div class="row">
												<div class="col-md-12" id="COURSE_DESC_DIV" style="display:none">
													If <b style="font-weight:bold;">'Type' = 'Course'</b>, applies all fees from <b style="font-weight:bold;">Setup > Registrar > Course > Course Fees</b> for:
													<ul style="margin-left:20px">
														<li>Students assigned to the selected <b style="font-weight:bold;">Term</b> and <b style="font-weight:bold;">Course/Course Offering(s).</b></li>
														<li>Where <b style="font-weight:bold;">'Course Offering Status' = 'Active'.</b></li>
														<li>Where <b style="font-weight:bold;">'Student Status'</b> is <b style="font-weight:bold;">'Post Tuition' = 'Yes.</b></li>
													</ul>
												</div>

												<div class="col-md-12" id="PROG_TUT_FEE_DESC_DIV" style="display:none">
													If <b style="font-weight:bold;">'Type' = 'Program'</b>, applies all fees by AY/AP from <b style="font-weight:bold;">Setup > Registrar > Program > Tuition & Fees</b> tab for:
													<ul style="margin-left:20px">
														<li>Students assigned to the selected <b style="font-weight:bold;">'First Term'</b></li>
														<li>Students assigned to the selected <b style="font-weight:bold;">'Program(s)'</b></li>
														<li>Where <b style="font-weight:bold;">'Student Status'</b> is <b style="font-weight:bold;">'Post Tuition' = 'Yes'</b></li>
														<li><b style="font-weight:bold;">'Estimated Fees'</b> that match the selected <b style="font-weight:bold;">'Academic Year'</b> and <b style="font-weight:bold;">'Academic Period'</b>.</b></li>
														<li>Student must be enrolled in a <b style="font-weight:bold;">'Course Offering'</b> for the selected <b style="font-weight:bold;">'THIS Term'</b> (First Term), or:</li>
														<li>Student must be enrolled in a <b style="font-weight:bold;">'Course Offering'</b> in <b style="font-weight:bold;">'ANY Term'.</b> </li>
														<li>The <b style="font-weight:bold;">'Fee Type'</b> of <b style="font-weight:bold;">'Budget'</b> or <b style="font-weight:bold;">'Tuition & Budget'</b> matches to the associated <b style="font-weight:bold;">'Estimated Fees'</b></li>
													</ul>
												</div>

												<div class="col-md-12" id="EST_FEE_BY_STUDENT_DESC_DIV" style="display:none">
													If <b style="font-weight:bold;">Type = 'Estimated Fees By Student'</b>, applies all fees by Date and AY/AP from <b style="font-weight:bold;">Finance > Finance Plan > Estimated Fees</b> tab for:
													<ul style="margin-left:20px">
														<li><b style="font-weight:bold;">'Estimated Fees'</b> whose projection date match the selected <b style="font-weight:bold;">'Term'</b>, or:</li>
														<li><b style="font-weight:bold;">'Estimated Fees'</b> whose projection date match the <b style="font-weight:bold;">'Start Date'</b> and <b style="font-weight:bold;">'End Date'</b> selected. </li>
														<li>Students assigned to the selected <b style="font-weight:bold;">'Program(s)'</b></li>
														<li>Where <b style="font-weight:bold;">'Student Status'</b> is <b style="font-weight:bold;">'Post Tuition' = 'Yes'</b></li>
														<li><b style="font-weight:bold;">'Estimated Fees'</b> that match the selected <b style="font-weight:bold;">'Academic Year'</b> and <b style="font-weight:bold;">'Academic Period'</b>.</li>
														<li>Student must be enrolled in a <b style="font-weight:bold;">'Course Offering'</b> for the selected <b style="font-weight:bold;">'THIS Term'</b> (First Term), or:</li>
														<li>Student must be enrolled in a <b style="font-weight:bold;">'Course Offering'</b> in <b style="font-weight:bold;">'ANY Term'.</b> </li>
														<li>The <b style="font-weight:bold;">'Fee Type'</b> of <b style="font-weight:bold;">'Budget'</b> or <b style="font-weight:bold;">'Tuition & Budget'</b> matches to the associated <b style="font-weight:bold;">'Estimated Fees'</b></li>
													</ul>
												</div>
											</div>
											<br />

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-20">
														<textarea class="form-control" rows="8" name="COMMENTS" id="COMMENTS"><?= $COMMENTS ?></textarea>
														<span class="bar"></span>
														<label for="COMMENTS"><?= COMMENTS ?></label>
													</div>
												</div>

												<!-- DIAM-1423 -->
												<? if ($PK_BATCH_STATUS == 2) { ?>
													<div class="col-md-12">
														<div class="form-group m-b-30" style="text-align: right;">
																<button type="button" onclick="create_student_notes()" id="CREATE_STUDENT_NOTES" class="btn waves-effect waves-light btn-info">Create Student Notes</button>
														</div>
													</div>
												<? } ?>
												<!-- End DIAM-1423 -->

												<? if ($PK_BATCH_STATUS != 2) { ?>
													<button type="button" onclick="check_tuition_batch_dup()" class="btn waves-effect waves-light btn-info"><?= BUILD_BATCH ?></button>

												<? } ?>
											</div>
										</div>


									</div>
									<br /><br />
									<div class="col-md-12">
										<div class="row">
											<div class="table-responsive tableFixHead" id="student_table1">
												<?
												$student_count = 1;
												if ($_GET['id'] != '') {
												?>
													<div class="table-responsive tableFixHead">
														<?
														$_REQUEST['student_count'] 				= $student_count;
														$_REQUEST['PK_TUITION_BATCH_MASTER'] 	= $_GET['id'];
														$_REQUEST['PK_BATCH_STATUS'] 			= $PK_BATCH_STATUS;
														$_REQUEST['TYPE'] 						= $TYPE;

														include("ajax_tuition_batch_detail.php");

														$res = $db->Execute("select PK_TUITION_BATCH_DETAIL from S_TUITION_BATCH_DETAIL WHERE PK_TUITION_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
														$student_count = $res->RecordCount() + 1;
														?>
													</div>
												<?

												}

												/*$res = $db->Execute("select PK_TUITION_BATCH_DETAIL from S_TUITION_BATCH_DETAIL WHERE PK_TUITION_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
									while (!$res->EOF) { 
										$_REQUEST['student_count'] 				= $student_count;
										$_REQUEST['PK_TUITION_BATCH_MASTER'] 	= $_GET['id'];
										
										include("ajax_tuition_batch_detail.php");
										$student_count++;
										
										$res->MoveNext();
									}*/ ?>
											</div>
										</div>
									</div>
									<br />
									<div class="row">
										<div class="col-md-6" style="flex: 0 0 53%;max-width: 53%;">
											<div style="font-weight:bold;text-align:right;">Total</div>
										</div>
										<div class="col-md-4">
											<div id="TOTAL_DIV" style="font-weight:bold;"></div>
										</div>
									</div>

									<br />
									<div class="row">
										<div class="col-md-12">
											<div style="text-align:center">

												<? if ($PK_BATCH_STATUS == 2) {
													/* ?>
													<button type="button" onclick="save_form(3)" id="UNPOST_BTN" class="btn waves-effect waves-light btn-info"><?=UNPOST?></button>
												<? */
												} else {
													if ($PK_BATCH_STATUS == 1 || $PK_BATCH_STATUS == '' || $PK_BATCH_STATUS == 3) { ?>
														<!-- Ticket # 714 -->
														<button type="button" onclick="Delete_student_record_confirm();" class="btn waves-effect waves-light btn-info" style="display:none" id="delete_student_record">Delete</button>
														<!-- Ticket # 714 -->

														<button type="button" onclick="save_form(1)" class="btn waves-effect waves-light btn-info"><?= SAVE_AS_HOLD ?></button>

														<button type="button" onclick="save_form(2)" class="btn waves-effect waves-light btn-info btn_post_ledger"><?= POST_TO_LEDGER ?></button>
													<? } else if ($PK_BATCH_STATUS == 2) { ?>
														<button type="button" onclick="save_form(0)" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>
												<? }
												} ?>

												<? if ($_GET['id'] != '') { ?>
													<button type="button" id="DOWNLOAD_BTN" class="btn waves-effect waves-light btn-info" onclick="window.location.href='tuition_payment_pdf.php?id=<?= $_GET['id'] ?>'"><?= DOWNLOAD_REPORT ?></button>
												<? } ?>

												<button type="button" class="btn waves-effect waves-light btn-dark" id="CANCEL_BTN" onclick="window.location.href='manage_tuition_batch'"><?= CANCEL ?></button>

												<input type="hidden" name="STS_HID" id="STS_HID" value='' />
												<input type="hidden" name="TOTAL_AMOUNT" id="TOTAL_AMOUNT" value='' />
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
		<? require_once("footer.php"); ?>


		<!-- Ticket # 714 -->
		<div class="modal" id="deleteModalconfirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= DELETE_CONFIRMATION ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?= DELETE_MESSAGE_GENERAL ?>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="Delete_student_record(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="Delete_student_record(0)"><?= NO ?></button>
					</div>
				</div>
			</div>
		</div>
		<!-- Ticket # 714 -->

		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= DELETE_CONFIRMATION ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?= DELETE_MESSAGE_GENERAL ?>
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)"><?= NO ?></button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="warningeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= WARNING ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?= NO_STUDENT_TO_POST ?>
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_popup_warning()"><?= OK ?></button><!--Ticket # 714-->
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="unpostModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= UNPOST_CONFIRMATION ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							Are you sure you want to unpost this Batch?
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_unpost_message(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_unpost_message(0)"><?= NO ?></button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="dulicateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= WARNING ?></h4>
					</div>
					<div class="modal-body">
						<div class="form-group" id="DUPLICATE_MSG_DIV"></div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="build_batch()" class="btn waves-effect waves-light btn-info"><?= PROCEED ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_popup('dulicateModal')"><?= CANCEL ?></button>
					</div>
				</div>
			</div>
		</div>

	</div>

	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});

			<? if ($_GET['id'] != '') { ?>
				calc_total()
				enable_post_batch_fields(document.getElementById('TYPE').value) // DIAM-1446
			<? } ?>

			<? if ($PK_BATCH_STATUS == 2) { ?>
				disableForm(document.form1)
			<? } ?>

			$('#student_table').on('post-body.bs.table', function(e) {
				jQuery(".date").datepicker({
					todayHighlight: true,
					orientation: "bottom auto"
				});
			})

			if (document.getElementById('AY'))
				document.getElementById('AY').setAttribute("onchange", "clear_form()");

			if (document.getElementById('AP'))
				document.getElementById('AP').setAttribute("onchange", "clear_form()");

			if (document.getElementById('PK_COURSE_OFFERING'))
				document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "clear_form()");

			show_desc()

		});

		/** DIAM-1423 **/
		function create_student_notes()
		{
			var str = '';
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]');
			var unique = [];
			var distinct = [];
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++)
			{

				if( !unique[PK_STUDENT_ENROLLMENT[i].value]){
					distinct.push(PK_STUDENT_ENROLLMENT[i].value);
					unique[PK_STUDENT_ENROLLMENT[i].value] = 1;
				}

				// if(str != '')
				// {
				// 	str += ',';
				// }				
				// str += PK_STUDENT_ENROLLMENT[i].value;
			}
			// alert(distinct);
			if(distinct !== "")
			{
				jQuery(document).ready(function($) {

					var data = 'enrollment_ids='+distinct;
					var value = $.ajax({
							url: "ajax_set_session_create_student_notes",
							type: "POST",
							data: data,
							async: true,
							cache: false,
							success: function(data) {
								//alert(data)
								if(data == 'success')
								{
									window.location.href='student_notes.php?p=m&batch=1';
								}
									
							}
					}).responseText;
					
				});
				
			}
			
		}
		/** End DIAM-1423 **/
	</script>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		//var form1 = new Validation('form1');

		function check_camp_selected(type) {
			jQuery(document).ready(function($) {
				var TUITION_BATCH_PK_CAMPUS = $("#TUITION_BATCH_PK_CAMPUS").val()

				if (type == "TYPE") {
					if (TUITION_BATCH_PK_CAMPUS == '') {
						alert('<?= SELECT_CAMPUS_ERROR ?>')
						document.getElementById('TYPE').value = ''
					} else {
						show_fields(document.getElementById('TYPE').value);
						clear_form();
						show_desc();
					}
				} else if (type == "STUDENT_TYPE") {
					if (TUITION_BATCH_PK_CAMPUS == '') {
						alert('<?= SELECT_CAMPUS_ERROR ?>')
						document.getElementById('STUDENT_TYPE').value = ''
					} else {
						clear_form();
					}
				} else if (type == "PK_TERM_MASTER") {
					if (TUITION_BATCH_PK_CAMPUS == '') {
						alert('<?= SELECT_CAMPUS_ERROR ?>')
						document.getElementById('PK_TERM_MASTER').value = ''
					} else {
						get_course();
						get_course_offering();
						clear_form();
					}
				}
			});
		}

		function show_desc() {
			document.getElementById('COURSE_DESC_DIV').style.display = 'none'
			document.getElementById('PROG_TUT_FEE_DESC_DIV').style.display = 'none'
			document.getElementById('EST_FEE_BY_STUDENT_DESC_DIV').style.display = 'none'

			if (document.getElementById('TYPE').value == 2)
				document.getElementById('COURSE_DESC_DIV').style.display = 'block'
			else if (document.getElementById('TYPE').value == 1)
				document.getElementById('PROG_TUT_FEE_DESC_DIV').style.display = 'block'
			else if (document.getElementById('TYPE').value == 9)
				document.getElementById('EST_FEE_BY_STUDENT_DESC_DIV').style.display = 'block'
		}

		function save_form(val) {
			var BATCH_PK_TUITION_BATCH_DETAIL = document.getElementsByName('BATCH_PK_TUITION_BATCH_DETAIL[]')
			if (BATCH_PK_TUITION_BATCH_DETAIL.length == 0) {
				jQuery(document).ready(function($) {
					$("#warningeModal").modal()
				});
			} else {
				document.getElementById('STS_HID').value = val
				if (val == 3) {
					jQuery(document).ready(function($) {
						$("#unpostModal").modal()
					});
				} else {
					var BATCH_TRANSACTION_DATE = document.getElementsByName('BATCH_TRANSACTION_DATE[]')
					if (val == 2) {
						// DIAM - 731
						show_only_selected();

						for (var i = 0; i < BATCH_TRANSACTION_DATE.length; i++) {
							//document.getElementById(BATCH_TRANSACTION_DATE[i].id).classList.add("required-entry");
						}
					} else {
						// DIAM - 731
						show_only_selected();
						for (var i = 0; i < BATCH_TRANSACTION_DATE.length; i++) {
							//document.getElementById(BATCH_TRANSACTION_DATE[i].id).classList.remove("required-entry");
						}
					}

					var valid = new Validation('form1', {
						onSubmit: false
					});
					var result = valid.validate();

					if (result == true) {
						document.getElementById('STUDENT_TYPE').disabled = false
						document.getElementById('TYPE').disabled = false

						var submit = 0
						var PK_STUDENT_DELETE = document.getElementsByName('PK_STUDENT_DELETE[]')
						for (var i = 0; i < PK_STUDENT_DELETE.length; i++) {
							if (PK_STUDENT_DELETE[i].checked == true) {
								submit = 1
								break;
							}
						}

						if (submit == 1){
							jQuery('#loaders').show();	
							jQuery('.btn_post_ledger').attr("disabled", true);
							document.form1.submit();
						}else {
							alert("Please Add Student To Batch");
						}
					}
				}
			}
		}
		//Ticket # 714
		function close_popup_warning() {
			jQuery(document).ready(function($) {
				$("#warningeModal").modal('hide');
			});
		}

		// DIAM-1446
		function enable_post_batch_fields(val) 
		{
			// DIAM-1446
			if (val == 1 || val == 9) 
			{
				document.getElementById('TERM_MASTER_LBL').innerHTML = 'First Term Date';
			}
			else
			{
				document.getElementById('TERM_MASTER_LBL').innerHTML = 'Course Term Start Date';
			}

			if (val == 9)
			{
				document.getElementById('START_DATE_LBL').innerHTML = 'Estimated Start Date';
				document.getElementById('END_DATE_LBL').innerHTML = 'Estimated End Date';
			}
			else
			{
				document.getElementById('START_DATE_LBL').innerHTML = '<?= START_DATE ?>';
				document.getElementById('END_DATE_LBL').innerHTML = '<?= END_DATE ?>';
			}

			if (val == 1) {
				document.getElementById('DATE_TYPE_LBL').innerHTML = 'Days From Start Date';
			}
			if (val == 2) {
				document.getElementById('DATE_TYPE_LBL').innerHTML = 'Course Term Start Date';
			}
			if (val == 9) {
				document.getElementById('DATE_TYPE_LBL').innerHTML = 'Estimated Fee Date';
			}
			// End DIAM-1446
		}

		//Ticket # 714
		function show_fields(val) {
			document.getElementById('TYPE').disabled = true

			document.getElementById('PROGRAM_DIV').style.display = 'none'
			document.getElementById('COURSE_DIV').style.display = 'none'
			document.getElementById('COURSE_OFFERING_DIV').style.display = 'none'
			document.getElementById('AY_DIV').style.display = 'none'
			document.getElementById('AP_DIV').style.display = 'none'
			document.getElementById('AY_DIV_EST_FEE').style.display = 'none' // DIAM-1539
			document.getElementById('AP_DIV_EST_FEE').style.display = 'none' // DIAM-1539
			document.getElementById('PK_FEE_TYPE_DIV').style.display = 'none'
			document.getElementById('OPTION_1_DIV').style.display = 'none'
			document.getElementById('DATE_RANGE_DIV').style.display = 'none'
			document.getElementById('COURSE_AY_AP_DIV').style.display = 'none'
			//document.getElementById('PK_TERM_MASTER_DIV').style.display 	= 'none'

			document.getElementById('PK_CAMPUS_PROGRAM').value = ''
			document.getElementById('PK_COURSE').value = ''

			jQuery(document).ready(function($) {
				if (val == 7) {
					get_ay()
					get_ap()
				}
			});

			// DIAM-1446
			if (val == 1 || val == 9) 
			{
				document.getElementById('TERM_MASTER_LBL').innerHTML = 'First Term Date';
			}
			else
			{
				document.getElementById('TERM_MASTER_LBL').innerHTML = 'Course Term Start Date';
			}

			if (val == 9)
			{
				document.getElementById('START_DATE_LBL').innerHTML = 'Estimated Start Date';
				document.getElementById('END_DATE_LBL').innerHTML = 'Estimated End Date';
			}
			else
			{
				document.getElementById('START_DATE_LBL').innerHTML = '<?= START_DATE ?>';
				document.getElementById('END_DATE_LBL').innerHTML = '<?= END_DATE ?>';
			}
			// End DIAM-1446

			if (val == 1) {
				document.getElementById('PROGRAM_DIV').style.display = 'block'
				document.getElementById('AY_DIV').style.display = 'block'
				document.getElementById('AP_DIV').style.display = 'block'
				document.getElementById('PK_FEE_TYPE_DIV').style.display = 'block'
				document.getElementById('OPTION_1_DIV').style.display = 'block'
				document.getElementById('TRANSACTION_DATE_TYPE_DIV').style.display = 'block' // DIAM-1446
				document.getElementById('DATE_TYPE_LBL').innerHTML = 'Days From Start Date'; // DIAM-1446
				// document.getElementById("DAYS_START_DATEbb").checked = true; // DIAM-1446

			} else if (val == 2) {
				document.getElementById('PROGRAM_DIV').style.display = 'block'; // DIAM - 743, Add new filter Program
				document.getElementById('COURSE_DIV').style.display = 'block'
				document.getElementById('COURSE_OFFERING_DIV').style.display = 'block'
				document.getElementById('COURSE_AY_AP_DIV').style.display = 'flex'
				//document.getElementById('PK_TERM_MASTER_DIV').style.display 	= 'block'
				document.getElementById('TRANSACTION_DATE_TYPE_DIV').style.display = 'block' // DIAM-1446
				document.getElementById('DATE_TYPE_LBL').innerHTML = 'Course Term Start Date'; // DIAM-1446
				document.getElementById("DAYS_START_DATEbb").checked = true; // DIAM-1446
			} else if (val == 7) {
				document.getElementById('PROGRAM_DIV').style.display = 'block'
				document.getElementById('AY_DIV').style.display = 'block'
				document.getElementById('AP_DIV').style.display = 'block'
			} else if (val == 9) {
				document.getElementById('PROGRAM_DIV').style.display = 'block'
				// document.getElementById('AY_DIV').style.display = 'block'
				// document.getElementById('AP_DIV').style.display = 'block'
				document.getElementById('AY_DIV_EST_FEE').style.display = 'block' // DIAM-1539
				document.getElementById('AP_DIV_EST_FEE').style.display = 'block' // DIAM-1539
				document.getElementById('DATE_RANGE_DIV').style.display = 'flex'
				document.getElementById('PK_FEE_TYPE_DIV').style.display = 'block'
				document.getElementById('OPTION_1_DIV').style.display = 'block'
				document.getElementById('TRANSACTION_DATE_TYPE_DIV').style.display = 'block' // DIAM-1446
				document.getElementById('DATE_TYPE_LBL').innerHTML = 'Estimated Fee Date'; // DIAM-1446
				document.getElementById("DAYS_START_DATEbb").checked = true; // DIAM-1446
			}
			else{
				document.getElementById("BATCH_DATESaa").checked = true; // DIAM-1446
			}

			document.getElementById('STUDENT_TYPE').disabled = false
			if (val == 2) {
				document.getElementById('STUDENT_TYPE').value = 3
				document.getElementById('STUDENT_TYPE').disabled = true
			} else if (val == 1 || val == 9) {
				document.getElementById('STUDENT_TYPE').value = 2
				document.getElementById('STUDENT_TYPE').disabled = true
			}

			jQuery(document).ready(function($) {
				$('.floating-labels .form-control').on('focus blur', function(e) {
					$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
				}).trigger('blur');
			});
		}

		//Ticket # 714
		function fun_select_all(event_val) {
			var str = '';
			var tot = 0;
			if (document.getElementById('DELETE_SELECT_ALL').checked == true) {
				str = true;
			} else {
				str = false;
			}

			if (event_val == 'All') {
				var PK_STUDENT_DELETE = document.getElementsByName('PK_STUDENT_DELETE[]');
				for (var i = 0; i < PK_STUDENT_DELETE.length; i++) {
					PK_STUDENT_DELETE[i].checked = str;
					if (PK_STUDENT_DELETE[i].checked) {
						tot++;
					}
				}

				if (tot > 0) {
					document.getElementById('delete_student_record').style.display = 'inline';
				} else {
					document.getElementById('delete_student_record').style.display = 'none';
				}

				// DIAM - 731
				calc_total_2()
				// End DIAM - 731

			} else if (event_val == 'Single') {

				var PK_STUDENT_DELETE = document.getElementsByName('PK_STUDENT_DELETE[]');
				for (var i = 0; i < PK_STUDENT_DELETE.length; i++) {
					//var singlecheck = PK_STUDENT_DELETE[i].checked = str;
					if (PK_STUDENT_DELETE[i].checked) {
						tot++;
					}
				}

				if (tot > 0) {
					document.getElementById('delete_student_record').style.display = 'inline';
				} else {
					document.getElementById('delete_student_record').style.display = 'none';
				}

				// DIAM - 731
				calc_total_2()
				// End DIAM - 731
			}

		}

		function Delete_student_record_confirm() {

			var BATCH_PK_TUITION_BATCH_DETAIL = document.getElementsByName('BATCH_PK_TUITION_BATCH_DETAIL[]')
			if (BATCH_PK_TUITION_BATCH_DETAIL.length == 1) {

				jQuery("#warningeModal").modal()

			} else {

				jQuery("#deleteModalconfirm").modal();

			}
		}

		function Delete_student_record(delval) {

			const pk_student_del_id = [];
			var PK_STUDENT_DELETE = document.getElementsByName('PK_STUDENT_DELETE[]');
			for (var i = 0; i < PK_STUDENT_DELETE.length; i++) {
				if (PK_STUDENT_DELETE[i].checked) {
					pk_student_del_id[i] = PK_STUDENT_DELETE[i].value;
				}
			}

			jQuery(document).ready(function($) {
				if (delval == 1) {
					for (var cnt = 0; cnt < pk_student_del_id.length; cnt++) {
						jQuery("#tuition_batch_detail_div_" + pk_student_del_id[cnt]).remove();
						calc_total();
					}
				}
				$("#deleteModalconfirm").modal("hide");
			});


		}
		//Ticket # 714

		function delete_row(id) {
			jQuery(document).ready(function($) {
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
			});
		}

		function conf_delete(val) {
			jQuery(document).ready(function($) {
				if (val == 1) {
					$("#tuition_batch_detail_div_" + $("#DELETE_ID").val()).remove();
					calc_total()
				}
				$("#deleteModal").modal("hide");
			});
		}

		function conf_unpost_message(val) {
			jQuery(document).ready(function($) {
				if (val == 1) {
					enableForm(document.form1)
					document.form1.submit();
				}
				$("#unpostModal").modal("hide");
			});
		}

		function format_val(field, id) {
			var AMOUNT = document.getElementById(field + '_' + id).value
			if (AMOUNT != '') {
				AMOUNT = parseFloat(AMOUNT)
				document.getElementById(field + '_' + id).value = AMOUNT.toFixed(2);
			}
		}

		function check_tuition_batch_dup() {

			jQuery(document).ready(function($) {
				var TUITION_BATCH_PK_CAMPUS = $("#TUITION_BATCH_PK_CAMPUS").val();
				if (TUITION_BATCH_PK_CAMPUS == '') {
					alert('<?= SELECT_CAMPUS_ERROR ?>')
				} else {
					var prog_id = '';
					var course_id = '';
					var AY = '';
					var AP = '';
					var PK_FEE_TYPE = '';
					var OPTION_1 = '';
					var START_DATE = '';
					var END_DATE = '';
					var PK_COURSE_OFFERING = '';
					var PK_TERM_MASTER = '';
					
					if (document.getElementById('TYPE').value == 1) {
						prog_id = $("#PK_CAMPUS_PROGRAM").val()
						AY = document.getElementById('AY').value;
						AP = document.getElementById('AP').value;
						OPTION_1 = document.getElementById('OPTION_1').value;
						PK_TERM_MASTER = document.getElementById('PK_TERM_MASTER').value;

						var error = "";
						if (PK_TERM_MASTER == "") {
							if (error != '')
								error += "\n";
							error += "Please Select <?= FIRST_TERM_1 ?>"
						}

						if (prog_id == "") {
							if (error != '')
								error += "\n";
							error += "Please Select <?= PROGRAM ?>"
						}

						if (AY == "" || AY == "-1") {
							if (error != '')
								error += "\n";
							error += "Please Select <?= AY ?>"
						}

						if (AP == "" || AP == "-1") {
							if (error != '')
								error += "\n";
							error += "Please Select <?= AP ?>"
						}

						if (error != '') {
							alert(error)
							return false;
						}

						var data = 'TYPE=' + document.getElementById('TYPE').value + '&id=<?= $_GET['id'] ?>&PK_TERM_MASTER=' + PK_TERM_MASTER + '&prog_id=' + prog_id + '&AY=' + AY + '&AP=' + AP + '&OPTION_1=' + OPTION_1

					} else if (document.getElementById('TYPE').value == 2) {
						prog_id = $("#PK_CAMPUS_PROGRAM").val(); // DIAM - 743, Add new filter Program
						PK_COURSE_OFFERING = $("#PK_COURSE_OFFERING").val();
						course_id = $("#PK_COURSE").val();
						PK_TERM_MASTER = document.getElementById('PK_TERM_MASTER').value;

						var error = "";
						if (PK_TERM_MASTER == "") {
							if (error != '')
								error += "\n";
							error += "Please Select <?= TERM_MASTER ?>"
						}
						//DIAM-786
						// DIAM - 743, Add new filter Program
						// if (prog_id == "") {
						// 	if (error != '')
						// 		error += "\n";
						// 	error += "Please Select <?//= PROGRAM ?>"
						// } // End DIAM - 743

						if (course_id == "") {
							if (error != '')
								error += "\n";

							error += "Please Select <?= COURSE ?>"
						}

						if (PK_COURSE_OFFERING == "") {
							if (error != '')
								error += "\n";

							error += "Please Select <?= COURSE_OFFERING ?>"
						}

						if (error != '') {
							alert(error)
							return false;
						}

						var data = 'TYPE=' + document.getElementById('TYPE').value + '&id=<?= $_GET['id'] ?>&PK_TERM_MASTER=' + PK_TERM_MASTER + '&prog_id=' + prog_id + '&PK_COURSE=' + course_id + '&PK_COURSE_OFFERING=' + PK_COURSE_OFFERING

					} else if (document.getElementById('TYPE').value == 9) {
						prog_id = $("#PK_CAMPUS_PROGRAM").val()
						AY = document.getElementById('AY').value;
						AP = document.getElementById('AP').value;
						START_DATE = document.getElementById('START_DATE').value;
						END_DATE = document.getElementById('END_DATE').value;

						var error = "";
						if (prog_id == "") {
							if (error != '')
								error += "\n";

							error += "Please Select <?= PROGRAM ?>"
						}

						if (START_DATE == "") {
							if (error != '')
								error += "\n";

							error += "Please Select <?= START_DATE ?>"
						}

						if (END_DATE == "") {
							if (error != '')
								error += "\n";

							error += "Please Select <?= END_DATE ?>"
						}

						if (AY == "" || AY == "-1") {
							if (error != '')
								error += "\n";

							error += "Please Select <?= AY ?>"
						}

						if (AP == "" || AP == "-1") {
							if (error != '')
								error += "\n";
							error += "Please Select <?= AP ?>"
						}

						if (error != '') {
							alert(error)
							return false;
						}

						var data = 'TYPE=' + document.getElementById('TYPE').value + '&id=<?= $_GET['id'] ?>&PK_TERM_MASTER=' + PK_TERM_MASTER + '&prog_id=' + prog_id + '&AY=' + AY + '&AP=' + AP + '&START_DATE=' + START_DATE + '&END_DATE=' + END_DATE + '&OPTION_1=' + OPTION_1
					}

					var value = $.ajax({
						url: "ajax_check_tuition_batch_duplicate",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							if (data == "a") {
								build_batch()
							} else {
								show_duplicate_error(document.getElementById('TYPE').value)
							}
						}
					}).responseText;
				}
			});
		}

		function show_duplicate_error(val) {
			jQuery(document).ready(function($) {
				if (val == 1) {
					document.getElementById('DUPLICATE_MSG_DIV').innerHTML = '<?= DUPLICATE_BATCH_ESTIMATED_FEE ?>';
					$("#dulicateModal").modal({
						backdrop: 'static',
						keyboard: false
					})
				} else if (val == 2) {
					document.getElementById('DUPLICATE_MSG_DIV').innerHTML = '<?= DUPLICATE_BATCH_COURSE ?>';
					$("#dulicateModal").modal({
						backdrop: 'static',
						keyboard: false
					})
				} else if (val == 9) {
					document.getElementById('DUPLICATE_MSG_DIV').innerHTML = '<?= DUPLICATE_BATCH_ESTIMATED_FEE ?>';
					$("#dulicateModal").modal({
						backdrop: 'static',
						keyboard: false
					})
				}
			})
		}

		function close_popup(id) {
			jQuery(document).ready(function($) {
				$("#" + id).modal("hide");
			})
		}

		function loader_datagrid(id) {
			if (document.getElementById(id)) {
				document.getElementById(id).innerHTML = '<tr><td><div style="position: inherit;margin-top: 0;height: 100px;z-index: 99;"><div class="datagrid-mask" style="display:block;top:35%;"></div><div class="datagrid-mask-msg" style="display:block;left:44%;top:65%"> Please wait...</div></div></td></tr>';
			}
		}

		var student_count = '<?= $student_count ?>';
		/* Ticket # 1151 */
		function build_batch() {
			document.getElementById('student_table1').innerHTML = '';
			loader_datagrid('student_table1');
			
			//document.getElementById('student_table1').style.display = 'block';
			jQuery(document).ready(function($) {
				close_popup('dulicateModal')

				var TUITION_BATCH_PK_CAMPUS = $("#TUITION_BATCH_PK_CAMPUS").val()
				if (TUITION_BATCH_PK_CAMPUS == '') {
					alert('<?= SELECT_CAMPUS_ERROR ?>')
					document.getElementById('student_table1').style.display = 'none';
				} else {
					var prog_id = '';
					var course_id = '';
					var AY = '';
					var AP = '';
					var PK_FEE_TYPE = '';
					var OPTION_1 = '';
					var START_DATE = '';
					var END_DATE = '';
					var PK_COURSE_OFFERING = '';
					var COURSE_TERM_START_DATE = ''; // DIAM-1446

					if (document.getElementById('TYPE').value == 1) {
						prog_id = $("#PK_CAMPUS_PROGRAM").val();
						AY = document.getElementById('AY').value;
						AP = document.getElementById('AP').value;
						PK_FEE_TYPE = document.getElementById('PK_FEE_TYPE').value;
						OPTION_1 = document.getElementById('OPTION_1').value;
					} else if (document.getElementById('TYPE').value == 2) {
						prog_id = $("#PK_CAMPUS_PROGRAM").val(); // DIAM - 743, Add new filter Program
						PK_COURSE_OFFERING = $("#PK_COURSE_OFFERING").val();
						course_id = $("#PK_COURSE").val();
						AY = document.getElementById('COURSE_AY').value;
						AP = document.getElementById('COURSE_AP').value;
						// DIAM-1446
						const checkedValues = [];
						Array.from(document.querySelectorAll('input[name="TRANS_DATA_TYPE"]:checked')).forEach(option => checkedValues.push(option.value));
						COURSE_TERM_START_DATE = checkedValues;
						// End DIAM-1446
					} else if (document.getElementById('TYPE').value == 7) {
						prog_id = $("#PK_CAMPUS_PROGRAM").val()
						AY = document.getElementById('AY').value;
						AP = document.getElementById('AP').value;
					} else if (document.getElementById('TYPE').value == 9) {
						prog_id = $("#PK_CAMPUS_PROGRAM").val()
						AY = document.getElementById('AY_FEE').value;
						AP = document.getElementById('AP_FEE').value;
						START_DATE = document.getElementById('START_DATE').value;
						END_DATE = document.getElementById('END_DATE').value;
						OPTION_1 = document.getElementById('OPTION_1').value;
						PK_FEE_TYPE = document.getElementById('PK_FEE_TYPE').value;

						/* Ticket # 1151 */
						var error = "";
						if (AY == "" || AY == "-1")
							error += "Please Select <?= AY ?>"

						if (AP == "" || AP == "-1") {
							if (error != '')
								error += "\n";
							error += "Please Select <?= AP ?>"
						}
						if (error != '') {
							alert(error)
							return false;
						}
						/* Ticket # 1151 */
					}

					var data = 'student_count=' + student_count + '&PK_TERM_MASTER=' + document.getElementById('PK_TERM_MASTER').value + '&TYPE=' + document.getElementById('TYPE').value + '&prog_id=' + prog_id + '&AY=' + AY + '&AP=' + AP + '&PK_FEE_TYPE=' + PK_FEE_TYPE + '&OPTION_1=' + OPTION_1 + '&TRANS_DATE=' + document.getElementById('TRANS_DATE').value + '&course_id=' + course_id + '&PK_COURSE_OFFERING=' + PK_COURSE_OFFERING + '&pk_id=<?= $_GET['id'] ?>&campus_id=' + TUITION_BATCH_PK_CAMPUS + '&START_DATE=' + START_DATE + '&END_DATE=' + END_DATE + '&COURSE_TERM_START_DATE=' + COURSE_TERM_START_DATE;

					var value = $.ajax({
						url: "ajax_tuition_batch_detail",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							//alert(data)
							document.getElementById('student_table1').innerHTML = data
							// $("#student_table tbody").empty();
							// $('#student_table tbody').append(data);

							jQuery('.date').datepicker({
								todayHighlight: true,
								orientation: "bottom auto"
							});

							var big = 0;
							var GRADE_CUNT = document.getElementsByName('student_count[]')
							for (var i = 0; i < GRADE_CUNT.length; i++) {
								if (parseFloat(GRADE_CUNT[i].value) > parseFloat(big))
									big = GRADE_CUNT[i].value
							}
							student_count = big
							student_count++;

							calc_total()
							//document.getElementById('student_table_tr').style.display = 'none';
						}
					}).responseText;
				}
			});
		}
		/* Ticket # 1151 */

		function get_ay() {
			jQuery(document).ready(function($) {
				var show_all = 0;
				if (document.getElementById('TYPE').value == 7)
					show_all = 1;
				var data = 'PK_CAMPUS_PROGRAM=' + $("#PK_CAMPUS_PROGRAM").val() + '&val=1&show_all=' + show_all;
				var value = $.ajax({
					url: "get_ay_from_program",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('AY_DIV_1').innerHTML = data
						document.getElementById('AY').setAttribute("onchange", "clear_form()");

						$('.floating-labels .form-control').on('focus blur', function(e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}
				}).responseText;
			});
		}

		function get_ap() {
			jQuery(document).ready(function($) {
				var show_all = 0;
				if (document.getElementById('TYPE').value == 7)
					show_all = 1;
				var data = 'PK_CAMPUS_PROGRAM=' + $("#PK_CAMPUS_PROGRAM").val() + '&val=1&show_all=' + show_all;
				var value = $.ajax({
					url: "get_ap_from_program",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('AP_DIV_1').innerHTML = data
						document.getElementById('AP').setAttribute("onchange", "clear_form()");

						$('.floating-labels .form-control').on('focus blur', function(e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}
				}).responseText;
			});
		}

		function get_ledger_desc(val, id) {
			jQuery(document).ready(function($) {
				var data = 'val=' + val;
				var value = $.ajax({
					url: "ajax_get_ledger_desc",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('LEDGER_DESC_DIV_' + id).innerHTML = data
					}
				}).responseText;
			});
		}

		function calc_total() {
			var total = 0;
			var BATCH_AMOUNT = document.getElementsByName('BATCH_AMOUNT[]')
			for (var i = 0; i < BATCH_AMOUNT.length; i++) {
				if (BATCH_AMOUNT[i].value != '')
					total += parseFloat(BATCH_AMOUNT[i].value)
			}
			if (document.getElementById('TOTAL_DIV')) {
				document.getElementById('TOTAL_DIV').innerHTML = "$ " + total.toFixed(2)
			}
			document.getElementById('DEBIT_TOTAL').innerHTML = "$ " + total.toFixed(2)
			document.getElementById('BATCH_TOTAL').innerHTML = "$ " + total.toFixed(2)
		}

		// DIAM - 731
		function show_only_selected() {
			//RUN DELETE ONLY IF ANY SINGLE IS SELECTED  
			if (jQuery(".delete_if_not_selected:checked").length > 0) {
				jQuery(".delete_if_not_selected:not(:checked)").parent().parent().parent().remove();
				//  jQuery(".delete_if_not_selected:not(:checked)").each(function () 
				//  {
				// 	var delete_id_arr = jQuery(this).parent().parent().parent().attr('id')
				// 	// alert("Deleting this values"+delete_id_arr);
				// 	 jQuery(this).parent().parent().parent().remove();
				//  });
			}
		}

		function calc_total_2() {
			var PK_STUDENT_DELETE = document.getElementsByName('PK_STUDENT_DELETE[]')
			var selected_count = jQuery(".delete_if_not_selected:checked").length;
			var total = 0;
			if (selected_count > 0) {
				for (var i = 0; i < PK_STUDENT_DELETE.length; i++) {

					if (PK_STUDENT_DELETE[i].checked == true) {
						var id = PK_STUDENT_DELETE[i].value;
						var BATCH_AMOUNT = document.getElementById('BATCH_AMOUNT_' + id).value

						if (BATCH_AMOUNT == '')
							BATCH_AMOUNT = 0;
						else
							BATCH_AMOUNT = parseFloat(BATCH_AMOUNT)

						total += BATCH_AMOUNT
					}
				}
			} else {
				for (var i = 0; i < PK_STUDENT_DELETE.length; i++) {
					var id = PK_STUDENT_DELETE[i].value;
					var BATCH_AMOUNT = document.getElementById('BATCH_AMOUNT_' + id).value

					if (BATCH_AMOUNT == '')
						BATCH_AMOUNT = 0;
					else
						BATCH_AMOUNT = parseFloat(BATCH_AMOUNT)

					total += BATCH_AMOUNT
				}
			}

			if (document.getElementById('TOTAL_DIV')) {
				document.getElementById('TOTAL_DIV').innerHTML = "$ " + total.toFixed(2)
			}

			//document.getElementById('AMOUNT').value = total.toFixed(2);
			document.getElementById('DEBIT_TOTAL').innerHTML = "$ " + total.toFixed(2);
			document.getElementById('BATCH_TOTAL').innerHTML = "$ " + total.toFixed(2);

			/*if (update_amt == 1)
			{
				document.getElementById('AMOUNT').value = total.toFixed(2);
			}*/
		}

		function check_number_validation(e) {
			const regex = /[^\d.]|\.(?=.*\.)/g;
			const numbers = /^\d+$/g;
			const subst = '';
			const str = e.value;
			const result = str.replace(regex, subst);
			if (str.match(numbers)) {
				e.value = result + '.00';
			} else {
				e.value = result;
			}

		}

		function paid_amount_value_change(id) {
			var BATCH_AMOUNT_NEW = document.getElementById('BATCH_AMOUNT_' + id).value;
			var BATCH_AMOUNT_CHECK = document.getElementById('BATCH_AMOUNT_' + id);
			var BATCH_AMOUNT_OLD = BATCH_AMOUNT_CHECK.getAttribute('batch-amt');
			var numbers = /[^\d.]|\.(?=.*\.)/g;
			if (BATCH_AMOUNT_NEW.match(numbers)) {
				alert("Please enter a valid amount. Please avoid spaces or other characters.");
				document.getElementById('BATCH_AMOUNT_' + id).value = BATCH_AMOUNT_OLD;
				calc_total_2();
			}
		}
		// End DIAM - 731

		function get_course() {
			if (document.getElementById('TYPE').value == 2) {
				jQuery(document).ready(function($) {
					var data = 'PK_TERM=' + document.getElementById('PK_TERM_MASTER').value + '&campus=' + $("#TUITION_BATCH_PK_CAMPUS").val();
					var value = $.ajax({
						url: "ajax_get_course_from_term",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							document.getElementById('COURSE_DIV_1').innerHTML = data
							document.getElementById('PK_COURSE').setAttribute("onchange", "get_course_offering();clear_form()");

							document.getElementById('PK_COURSE').setAttribute('multiple', true);
							document.getElementById('PK_COURSE').name = "PK_COURSE[]"

							$("#PK_COURSE option[value='']").remove();

							$('#PK_COURSE').multiselect({
								includeSelectAllOption: true,
								allSelectedText: 'All <?= COURSE ?>',
								nonSelectedText: '',
								numberDisplayed: 2,
								nSelectedText: '<?= COURSE ?> selected',
								enableCaseInsensitiveFiltering: true,
							});
						}
					}).responseText;
				});
			}
		}

		function get_course_offering() {
			if (document.getElementById('TYPE').value == 2) {
				jQuery(document).ready(function($) {
					var data = 'val=' + $("#PK_COURSE").val() + '&PK_TERM_MASTER=' + document.getElementById('PK_TERM_MASTER').value + '&campus=' + $("#TUITION_BATCH_PK_CAMPUS").val();
					var value = $.ajax({
						url: "ajax_get_course_offering",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							document.getElementById('COURSE_OFFERING_DIV_1').innerHTML = data
							document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "clear_form()");

							document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
							document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"

							$("#PK_COURSE_OFFERING option[value='']").remove();

							$('#PK_COURSE_OFFERING').multiselect({
								includeSelectAllOption: true,
								allSelectedText: 'All <?= COURSE_OFFERING ?>',
								nonSelectedText: '',
								numberDisplayed: 2,
								nSelectedText: '<?= COURSE_OFFERING ?> selected',
								enableCaseInsensitiveFiltering: true,
							});
						}
					}).responseText;
				});
			}
		}

		function disableForm(theform) {
			if (document.all || document.getElementById) {
				for (i = 0; i < theform.length; i++) {
					var formElement = theform.elements[i];
					if (true) {
						formElement.disabled = true;
					}
				}
			}
			if(document.getElementById('UNPOST_BTN')) // DIAM-1971
			{
				document.getElementById('UNPOST_BTN').disabled = false;
			}
			document.getElementById('CANCEL_BTN').disabled = false;
			document.getElementById('DOWNLOAD_BTN').disabled = false;
			document.getElementById('CREATE_STUDENT_NOTES').disabled = false; // DIAM-1423
			document.querySelectorAll('.pk_stud_enrol').forEach(el => el.disabled = false);  // DIAM-1423
		}

		function enableForm(theform) {
			if (document.all || document.getElementById) {
				for (i = 0; i < theform.length; i++) {
					var formElement = theform.elements[i];
					if (true) {
						formElement.disabled = false;
					}
				}
			}
		}

		function clear_form() {
			jQuery(document).ready(function($) {
				$("#student_table tbody").empty();
			});
		}

		function get_term(val, id) {
			jQuery(document).ready(function($) {
				var data = 'eid=' + val;
				var value = $.ajax({
					url: "ajax_student_term",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('BATCH_PK_TERM_BLOCK_' + id).value = data
					}
				}).responseText;
			});
		}

		/* Ticket # 1898 */
		function set_trans_date() {
			var TRANS_DATE = '';
			if (document.getElementById('TRANS_DATE').value != '') {
				TRANS_DATE = document.getElementById('TRANS_DATE').value;

				var BATCH_TRANSACTION_DATE = document.getElementsByName('BATCH_TRANSACTION_DATE[]')
				for (var i = 0; i < BATCH_TRANSACTION_DATE.length; i++) {
					// if (BATCH_TRANSACTION_DATE[i].value == '')
						BATCH_TRANSACTION_DATE[i].value = TRANS_DATE
				}

			}
		}
		/* Ticket # 1898 */

		/* DIAM-1446 */
		function set_batch_date(type_val) {

			var TYPE_DATA = document.getElementById('TYPE').value;						
			if(TYPE_DATA == '2' && type_val == '2')
			{	
				var str = jQuery("#PK_TERM_MASTER option:selected").text();
				var PK_TERM_MASTER = str.slice(0, 10);
				var BATCH_TRANSACTION_DATE = document.getElementsByName('BATCH_TRANSACTION_DATE[]')
				for (var i = 0; i < BATCH_TRANSACTION_DATE.length; i++) {
				// if (BATCH_TRANSACTION_DATE[i].value == '')
					BATCH_TRANSACTION_DATE[i].value = PK_TERM_MASTER
				}
			}
			else
			{
				document.getElementById('TRANS_DATE').classList.add("required-entry");
				var TRANS_DATE = document.getElementById('TRANS_DATE').value
				var BATCH_TRANSACTION_DATE = document.getElementsByName('BATCH_TRANSACTION_DATE[]')
				for (var i = 0; i < BATCH_TRANSACTION_DATE.length; i++) {
				// if (BATCH_TRANSACTION_DATE[i].value == '')
					BATCH_TRANSACTION_DATE[i].value = TRANS_DATE
				}
			}
			
		}
		/* End DIAM-1446 */
	</script>

	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_CAMPUS_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= STUD_PROGRAM ?>',
				nonSelectedText: '<?= STUD_PROGRAM ?>',
				numberDisplayed: 1,
				nSelectedText: '<?= STUD_PROGRAM ?> selected'
			});

			$('#TUITION_BATCH_PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= CAMPUS ?>',
				nonSelectedText: '<?= CAMPUS ?>',
				numberDisplayed: 1,
				nSelectedText: '<?= CAMPUS ?> selected'
			});

			$('#PK_COURSE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= COURSE ?>',
				nonSelectedText: '<?= COURSE ?>',
				numberDisplayed: 1,
				nSelectedText: '<?= COURSE ?> selected'
			});

			$('#PK_COURSE_OFFERING').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= COURSE_OFFERING ?>',
				nonSelectedText: '<?= COURSE_OFFERING ?>',
				numberDisplayed: 1,
				nSelectedText: '<?= COURSE_OFFERING ?> selected'
			});

		});
	</script>
</body>

</html>
