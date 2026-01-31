<?php require_once("global/config.php");
if($_POST['type'] == 'USER_ID'){
	$USER_ID = $_POST['USER_ID'];
	
	$cond = "";
	if($_POST['id'] != '')
		$cond = " AND PK_USER != '$_POST[id]' ";
			
	$result = $db->Execute("select count(*) as count from Z_USER where USER_ID = '$USER_ID' $cond ");
	if ($result->fields['count'] == 0){
		echo "0"; 
	} else {
		echo "1"; 
	}
} else if($_POST['type'] == 'SSN'){
	$result = $db->Execute("select CHECK_SSN from Z_ACCOUNT where PK_ACCOUNT = '$_POST[k]' ");
	if($result->fields['CHECK_SSN'] == 0)
		echo "0"; 
	else {
		$SSN = $_POST['SSN'];
		$result = $db->Execute("select PK_STUDENT_MASTER,SSN from S_STUDENT_MASTER where PK_ACCOUNT = '$_POST[k]' AND SSN != '' ");
		if ($result->RecordCount() == 0){
			echo "0"; 
		} else {
			$flag = 1;
			while (!$result->EOF) {
				$SSN1 = my_decrypt($_POST['k'].$result->fields['PK_STUDENT_MASTER'],$result->fields['SSN']);
				if($SSN1 == $SSN) {
					$flag = 0;
					break;
				}
				
				$result->MoveNext();
			}
			if($flag == 1)
				echo "0"; 
			else
				echo "1"; 
		}
	}
} else if($_POST['type'] == 'LEDGER_CODE'){
	$CODE = $_POST['CODE'];
	$result = $db->Execute("select CODE from M_AR_LEDGER_CODE where PK_ACCOUNT = '$_POST[k]' AND CODE = '$CODE'  AND PK_AR_LEDGER_CODE != '$_POST[id]' ");
	if ($result->RecordCount() == 0){
		echo "0"; 
	} else {
		echo "1"; 
	}
} else if($_POST['type'] == 'STUDENT_ID'){
	$STUDENT_ID = trim($_POST['STUDENT_ID']);
	/*if($STUDENT_ID == '')
		echo "1"; 
	else {*/
		$cond = "";
		if($_POST['id'] != '')
			$cond = " AND PK_STUDENT_MASTER != '$_POST[id]' ";
			
		$result = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_ACADEMICS where PK_ACCOUNT = '$_POST[k]' AND STUDENT_ID = '$STUDENT_ID' $cond ");
		if ($result->RecordCount() == 0){
			echo "0"; 
		} else {
			echo "1"; 
		}
	//}
} else if($_POST['type'] == 'STUD_CODE'){
	$STUD_CODE = $_POST['STUD_CODE'];
	$result = $db->Execute("select STUD_CODE from Z_ACCOUNT where STUD_CODE = '$STUD_CODE' AND PK_ACCOUNT != '$_POST[id]' ");
	if ($result->RecordCount() == 0){
		echo "0"; 
	} else {
		echo "1"; 
	}
} else if($_POST['type'] == 'STUD_NO'){
	$STUD_NO = $_POST['STUD_NO'];
	//echo $STUD_NO.' ------ select STUD_NO from Z_ACCOUNT where PK_ACCOUNT = '.$_POST[k];
	$result = $db->Execute("select STUD_NO from Z_ACCOUNT where PK_ACCOUNT = '$_POST[id]' ");
	if($STUD_NO >= $result->fields['STUD_NO']){
		echo "0"; 
	} else {
		echo "1"; 
	}
} /* Ticket # 1044  */
else if($_POST['type'] == 'COURSE_CODE'){
	$COURSE_CODE = $_POST['COURSE_CODE'];
	$cond = "";
	if($_POST['id'] != '')
		$cond = " AND PK_COURSE != '$_POST[id]' ";
		
	$result = $db->Execute("select COURSE_CODE from S_COURSE where PK_ACCOUNT = '$_POST[k]' AND COURSE_CODE = '$COURSE_CODE' $cond ");
	if ($result->RecordCount() == 0){
		echo "0"; 
	} else {
		echo "1"; 
	}
}
/* Ticket # 1044  */

/* Ticket # 1052  */
else if($_POST['type'] == 'PROGRAM_CODE'){
	$CODE = $_POST['CODE'];
	$cond = "";
	if($_POST['id'] != '')
		$cond = " AND PK_CAMPUS_PROGRAM != '$_POST[id]' ";		
	$result = $db->Execute("select CODE from M_CAMPUS_PROGRAM where PK_ACCOUNT = '$_POST[k]' AND CODE = '$CODE' $cond ");
	if ($result->RecordCount() == 0){
		echo "0"; 
	} else {
		echo "1"; 
	}
}
/* Ticket # 1052  */
?>