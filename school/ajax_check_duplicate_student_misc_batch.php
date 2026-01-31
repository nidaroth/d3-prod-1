<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$CHECK_DUPLICATE_STD = array();
$PK_STUDENT_MASTER_ARR =array();
$BATCH_PK_STUDENT_MASTER =$_POST['BATCH_PK_STUDENT_MASTER'];
$BATCH_PK_AR_LEDGER_CODE = $_POST['BATCH_PK_AR_LEDGER_CODE'];
$BATCH_PK_STUDENT_ENROLLMENT = $_POST['BATCH_PK_STUDENT_ENROLLMENT'];
$BATCH_CREDIT =  $_POST['BATCH_CREDIT'];
$BATCH_DEBIT = $_POST['BATCH_DEBIT'];
$i=0;


// print_r($BATCH_PK_STUDENT_ENROLLMENT); die;
foreach ($BATCH_PK_STUDENT_MASTER  as $key => $value) {
	
	$BATCH_DETAIL_QUERY = "SELECT PK_MISC_BATCH_MASTER,PK_STUDENT_MASTER FROM S_MISC_BATCH_DETAIL 
	WHERE PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' 			 
	AND PK_STUDENT_MASTER='$value' 
	AND PK_STUDENT_ENROLLMENT='$BATCH_PK_STUDENT_ENROLLMENT[$i]'
	AND PK_AR_LEDGER_CODE='$BATCH_PK_AR_LEDGER_CODE[$i]'
	AND MISC_RECEIPT_NO =''
	AND PK_MISC_BATCH_MASTER !='$_GET[id]'"; 	
		
		if(!empty($BATCH_DEBIT[$i]))
			$BATCH_DETAIL_QUERY .= " AND DEBIT = '$BATCH_DEBIT[$i]'";

		if(!empty($BATCH_CREDIT[$i]))	
			$BATCH_DETAIL_QUERY .= " AND CREDIT = '$BATCH_CREDIT[$i]'"; 
		
	$BATCH_DETAIL_QUERY .= " AND ACTIVE =1 ORDER BY PK_MISC_BATCH_DETAIL DESC";
	//echo $BATCH_DETAIL_QUERY;
	$res_duplicate_check_student= $db->Execute($BATCH_DETAIL_QUERY); 	
	if($res_duplicate_check_student->RecordCount()!=0){			
		$CHECK_DUPLICATE_STD[] = $res_duplicate_check_student->fields["PK_MISC_BATCH_MASTER"];
		$PK_STUDENT_MASTER_ARR[] = $res_duplicate_check_student->fields["PK_STUDENT_MASTER"];
	
	}
	$i++;
}



if(!empty($CHECK_DUPLICATE_STD)){

	$STUDENT_CHECK_IN_BATCH = $CHECK_DUPLICATE_STD;
	$STUDENT_CHECK_IN_BATCH_STR = implode(',',$STUDENT_CHECK_IN_BATCH);
	
	$RES_BATCH_NO_QUERY=$db->Execute("SELECT BATCH_NO FROM S_MISC_BATCH_MASTER WHERE PK_MISC_BATCH_MASTER IN ('$STUDENT_CHECK_IN_BATCH_STR') AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]'"); 	

	while (!$RES_BATCH_NO_QUERY->EOF) {
	$BATCH_NO =$RES_BATCH_NO_QUERY->fields;
	$RES_BATCH_NO_QUERY->MoveNext();
	}
}

	
$EXIST_STUDENT_NAME ='';
if(!empty($PK_STUDENT_MASTER_ARR)){
	$PK_STUDENT_MASTER_STR = implode(",",$PK_STUDENT_MASTER_ARR);
	$stud_res = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER_STR) AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' ORDER BY NAME ASC");
			
	$EXIST_STUDENT_NAME .='<ul>';
	while (!$stud_res->EOF) { 
		$EXIST_STUDENT_NAME .='<li>'.$stud_res->fields['NAME'].' exists in #'.$BATCH_NO['BATCH_NO'].'</li>';
		$stud_res->MoveNext();
	}
	$EXIST_STUDENT_NAME .='</ul>';
}
$EXIST_STUDENT_NAME .='<p>Do you want to continue?</p>';

$student_count =  count($CHECK_DUPLICATE_STD);
if($student_count==1){
	$title = 'Student already exists in another batch!';
}else{
	$title = 'Students already exists in another batch!';
}

echo json_encode(array('EXIST_STUDENT_COUNT'=>$student_count,'EXIST_STUDENT_NAME'=>$EXIST_STUDENT_NAME,'Mtitle'=>$title));