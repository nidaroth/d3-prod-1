<?php

require_once("../../global/config.php");
require_once('../classes/api_key_authenticater.php');
require_once("../../global/s3-client-wrapper/s3-client-wrapper.php");
ENABLE_DEBUGGING(TRUE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);
$error_collector = [];
$success_collector = [];
try {
	create_import_entry();
} catch (\Throwable $th) {
	//throw $th;
}

$DATA = $HEADERDATA = json_decode(urldecode(file_get_contents('php://input')));

if (isset($DATA->Current)) {
	$DATA = $DATA->Current;
}
//Check API Authentication  
header('Content-Type: application/json; charset=utf-8');
$PK_ACCOUNT = API_KEY_AUTHENTICATER::api_auth($HEADERDATA);
if ($DATA == null) {
	ReturneErrorResponse(["Invalid JSON recived in incomming request. Please validate format of JSON data."], $error_collector, true);
}
$DATA->PK_ACCOUNT = $PK_ACCOUNT;

if (!empty($DATA) && $DATA != '') {
	//echo "<pre>";print_r($_POST);exit; 


	#GET STUDENT INFO FROM PROSPECT ID 
	$STUDENT_INFO = $db->Execute("SELECT * FROM `S_STUDENT_MASTER` WHERE LSQ_ID !='' AND LSQ_ID = '" . $DATA->ProspectID . "' AND PK_ACCOUNT = $PK_ACCOUNT");
	$DEPARTMENT_INFO = $db->Execute("SELECT * FROM `M_DEPARTMENT` WHERE PK_DEPARTMENT_MASTER = 2 AND PK_ACCOUNT = $PK_ACCOUNT");
	if ($STUDENT_INFO->RecordCount() > 0) {
		foreach ($DATA->fileurl as $key => $value) {
			save_document($PK_ACCOUNT, $STUDENT_INFO->fields['PK_STUDENT_MASTER'], null, $DEPARTMENT_INFO->fields['PK_DEPARTMENT'], $value);
		}
	} else {
		echo $data['error'] = "Student with given prospectID not found !";
		exit;
	}
	// header("location:student?id=".$PK_STUDENT_MASTER.'&tab=documentsTab&eid='.$_GET['eid'].'&t='.$_GET['t']);
}


function save_document($PK_ACCOUNT, $PK_STUDENT_MASTER, $PK_STUDENT_ENROLLMENT, $PK_DEPARTMENT, $FILEURL)
{
	global $db;
	// $PK_DEPARTMENT_ARR   = $_POST['PK_DEPARTMENT']; -- no need see below
	$PK_DEPARTMENT_ARR[] = $PK_DEPARTMENT; //HARDCODED VALUE FOR REGISTRAR = 7
	unset($_POST['PK_DEPARTMENT']);

	$STUDENT_DOCUMENTS 							= $_POST;
	$STUDENT_DOCUMENTS['RECEIVED'] 				= $_POST['RECEIVED'];


	$STUDENT_DOCUMENTS['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;

	// $file_dir_1 = '../backend_assets/school/school_'.$PK_ACCOUNT.'/student/';
	$file_dir_1 = '../../backend_assets/tmp_upload/';
	if ($FILEURL != '') {

		$DOCUMENT_TYPE = str_replace("/", "-", $_POST['DOCUMENT_TYPE']);
		$DOCUMENT_TYPE = str_replace("\\", "-", $DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("&", "-", $DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("*", "-", $DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace(":", "-", $DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("?", "-", $DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("<", "-", $DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace(">", "-", $DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("|", "-", $DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace(" ", "_", $DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("=", "_", $DOCUMENT_TYPE);

		//$BASEFILENAME = preg_replace('/[^a-zA-Z.]/', '_', basename(urldecode($FILEURL)));
		$path2024 = parse_url($FILEURL, PHP_URL_PATH);	//DIAM-2341
		$BASEFILENAME =  preg_replace('/[^a-zA-Z.]/', '_', basename(urldecode($path2024))); //DIAM-2341
		$extn 			= explode(".", $BASEFILENAME);
		$iindex			= count($extn) - 1;
		$rand_string 	= time() . "_" . rand(10000, 99999);
		$file11			= $PK_STUDENT_MASTER . '_' . $DOCUMENT_TYPE . '_' . $rand_string . "_" . $BASEFILENAME;
		$extension   	= strtolower($extn[$iindex]);

		if ($extension != "php" && $extension != "js" && $extension != "html" && $extension != "htm") {
			$newfile1    = $file_dir_1 . $file11;
			// echo "Downloading file <br>" . $newfile1 . "<br>";
			try {
				file_put_contents($newfile1, fopen($FILEURL, 'r'));
			} catch (\Throwable $th) {
				var_dump($th);
				exit;
				// ReturneErrorResponse([$th], $error_collector, true);
			}

			// move_uploaded_file($_FILES['IMAGE']['tmp_name'], $newfile1);

			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/school/school_' . $PK_ACCOUNT . '/student/' . $file11;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);
			// echo "<br><b style='color:red'>$url</b><br>";
			// $STUDENT_DOCUMENTS['DOCUMENT_PATH'] = $newfile1;
			$STUDENT_DOCUMENTS['DOCUMENT_PATH'] = $url;
			$STUDENT_DOCUMENTS['DOCUMENT_NAME'] = $BASEFILENAME;
			$STUDENT_DOCUMENTS['RECEIVED'] 		= 1;
			$STUDENT_DOCUMENTS['NOTES'] 		= "Uploaded by LeadSquared API - " . date("Y-m-d");

			// delete tmp file
			// unlink($newfile1);

			if ($_POST['DATE_RECEIVED'] != '')
				$STUDENT_DOCUMENTS['DATE_RECEIVED'] = $_POST['DATE_RECEIVED'];
			else
				$STUDENT_DOCUMENTS['DATE_RECEIVED'] = date("Y-m-d");
		}
	}

	if ($STUDENT_DOCUMENTS['RECEIVED'] != 1)
		$STUDENT_DOCUMENTS['DATE_RECEIVED'] = '';

	if ($STUDENT_DOCUMENTS['DATE_RECEIVED'] != '')
		$STUDENT_DOCUMENTS['DATE_RECEIVED'] = date("Y-m-d", strtotime($STUDENT_DOCUMENTS['DATE_RECEIVED']));
	else
		$STUDENT_DOCUMENTS['DATE_RECEIVED'] = '';


	if ($STUDENT_DOCUMENTS['REQUESTED_DATE'] != '')
		$STUDENT_DOCUMENTS['REQUESTED_DATE'] = date("Y-m-d", strtotime($STUDENT_DOCUMENTS['REQUESTED_DATE']));
	else
		$STUDENT_DOCUMENTS['REQUESTED_DATE'] = '';

	/*if($STUDENT_DOCUMENTS['DATE_RECEIVED'] != '')
		$STUDENT_DOCUMENTS['DATE_RECEIVED'] = date("Y-m-d",strtotime($STUDENT_DOCUMENTS['DATE_RECEIVED']));
	else
		$STUDENT_DOCUMENTS['DATE_RECEIVED'] = '';*/

	if ($STUDENT_DOCUMENTS['FOLLOWUP_DATE'] != '')
		$STUDENT_DOCUMENTS['FOLLOWUP_DATE'] = date("Y-m-d", strtotime($STUDENT_DOCUMENTS['FOLLOWUP_DATE']));
	else
		$STUDENT_DOCUMENTS['FOLLOWUP_DATE'] = '';

	if ($_GET['id'] == '') {
		$res_type = $db->Execute("select DOCUMENT_TYPE from M_DOCUMENT_TYPE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_DOCUMENT_TYPE = '$_POST[PK_DOCUMENT_TYPE]' ");

		$STUDENT_DOCUMENTS['DOCUMENT_TYPE']  		= $res_type->fields['DOCUMENT_TYPE'];
		$STUDENT_DOCUMENTS['PK_ACCOUNT']  			= $PK_ACCOUNT;
		$STUDENT_DOCUMENTS['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
		$STUDENT_DOCUMENTS['CREATED_BY']  			= $_SESSION['PK_USER'];
		$STUDENT_DOCUMENTS['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_STUDENT_DOCUMENTS', $STUDENT_DOCUMENTS, 'insert');
		$PK_STUDENT_DOCUMENTS = $db->insert_ID();
	} /*
	NO NEED OF UPDATE OPERATION
	else {
		$STUDENT_DOCUMENTS['EDITED_BY'] = $_SESSION['PK_USER'];
		$STUDENT_DOCUMENTS['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_STUDENT_DOCUMENTS', $STUDENT_DOCUMENTS, 'update'," PK_STUDENT_DOCUMENTS = '$_GET[id]' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		$PK_STUDENT_DOCUMENTS = $_GET['id'];
	}*/

	foreach ($PK_DEPARTMENT_ARR as $PK_DEPARTMENT) {
		$res = $db->Execute("SELECT PK_STUDENT_DOCUMENTS_DEPARTMENT FROM S_STUDENT_DOCUMENTS_DEPARTMENT WHERE PK_STUDENT_DOCUMENTS = '$PK_STUDENT_DOCUMENTS' AND PK_ACCOUNT = '$PK_ACCOUNT' AND PK_DEPARTMENT = '$PK_DEPARTMENT' ");
		if ($res->RecordCount() == 0) {
			$STUDENT_DOCUMENTS_DEPARTMENT['PK_DEPARTMENT']   		= $PK_DEPARTMENT;
			$STUDENT_DOCUMENTS_DEPARTMENT['PK_STUDENT_DOCUMENTS'] 	= $PK_STUDENT_DOCUMENTS;
			$STUDENT_DOCUMENTS_DEPARTMENT['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$STUDENT_DOCUMENTS_DEPARTMENT['CREATED_BY']  			= $_SESSION['PK_USER'];
			$STUDENT_DOCUMENTS_DEPARTMENT['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_STUDENT_DOCUMENTS_DEPARTMENT', $STUDENT_DOCUMENTS_DEPARTMENT, 'insert');
			$PK_STUDENT_DOCUMENTS_DEPARTMENT_ARR[] = $db->insert_ID();
		} else {
			$PK_STUDENT_DOCUMENTS_DEPARTMENT_ARR[] = $res->fields['PK_STUDENT_DOCUMENTS_DEPARTMENT'];
		}
	}
	#What is thi
	// $cond = "";
	// if(!empty($PK_STUDENT_DOCUMENTS_DEPARTMENT_ARR))
	// 	$cond = " AND PK_STUDENT_DOCUMENTS_DEPARTMENT NOT IN (".implode(",",$PK_STUDENT_DOCUMENTS_DEPARTMENT_ARR).") ";
	// $db->Execute("DELETE FROM S_STUDENT_DOCUMENTS_DEPARTMENT WHERE PK_STUDENT_DOCUMENTS = '$PK_STUDENT_DOCUMENTS' AND PK_ACCOUNT = '$PK_ACCOUNT' $cond "); 

}

function ReturneErrorResponse(array $message_array, array &$error_collector, $exit_immidiatly = false)
{

	$error_collector = array_merge($error_collector, $message_array);
	$data['SUCCESS'] = 0;
	$data['ERROR'] = $error_collector;
	//Send response
	if ($exit_immidiatly) {
		$data = json_encode($data);
		echo $data;
		exit;
	} else {

		return $message_array;
	}
}

$ENTRY_ID = "";
function create_import_entry()
{
	global $db;
	global $ENTRY_ID;
	try {
		$headers = '';
		foreach ($_SERVER as $h => $v){
			if (preg_match('/HTTP\_(.+)/', $h, $hp)) {
				$headers .= "$h = $v \n --------------------- \n ";
			}
		}
		$input  = file_get_contents('php://input');
		$LSQ_IMPORT_DOCUMENTS_STATUS_LOG = [];
		$LSQ_IMPORT_DOCUMENTS_STATUS_LOG['request_body'] = $headers.$input;
		db_perform('LSQ_IMPORT_DOCUMENTS_STATUS_LOG', $LSQ_IMPORT_DOCUMENTS_STATUS_LOG , 'insert');
		$ENTRY_ID = $db->insert_ID();
	} catch (\Throwable $th) {
		//throw $th;
	}
}

// function update_import_entry($ENTRY_ID , $OUTPUT)
// {
// 	try {
// 		$LSQ_IMPORT_DOCUMENTS_STATUS_LOG = [];
// 		$LSQ_IMPORT_DOCUMENTS_STATUS_LOG['output'] = $OUTPUT;
// 		db_perform('LSQ_IMPORT_DOCUMENTS_STATUS_LOG', $LSQ_IMPORT_DOCUMENTS_STATUS_LOG, 'update', " id = '$ENTRY_ID' AND id != '' ");
// 	} catch (\Throwable $th) {
// 		//throw $th;
// 	}
// }
