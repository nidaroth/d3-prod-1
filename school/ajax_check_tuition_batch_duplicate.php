<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}
$cond = "";

if($_REQUEST['id'] != '')
	$cond .= " AND PK_TUITION_BATCH_MASTER != '$_REQUEST[id]' ";

if($_REQUEST['TYPE'] == 1){
	$res = $db->Execute("SELECT PK_TUITION_BATCH_MASTER, PK_CAMPUS_PROGRAM FROM S_TUITION_BATCH_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '$_REQUEST[TYPE]' AND AY = '$_REQUEST[AY]' AND AP = '$_REQUEST[AP]' AND PK_TERM_MASTER = '$_REQUEST[PK_TERM_MASTER]' $cond ");
	if($res->RecordCount() == 0)
		echo "a";
	else {
	
		$flag = 1;
		while (!$res->EOF) {
			$PK_CAMPUS_PROGRAM_ARR 	= explode(",", $res->fields['PK_CAMPUS_PROGRAM']);
			$prog_id 				= explode(",", $_REQUEST['prog_id']);
			
			foreach($prog_id as $prog_id2) {
				if(in_array($prog_id2, $PK_CAMPUS_PROGRAM_ARR)) {
					$flag = 0;
					echo "b";
					exit;
				}
			}
			
			$res->MoveNext();
		}
		if($flag == 1)
			echo "a";
		else
			echo "b";
	}
	
} else if($_REQUEST['TYPE'] == 2){
	$res = $db->Execute("SELECT PK_TUITION_BATCH_MASTER, PK_COURSE_OFFERING FROM S_TUITION_BATCH_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '$_REQUEST[TYPE]' AND PK_TERM_MASTER = '$_REQUEST[PK_TERM_MASTER]' $cond ");
	if($res->RecordCount() == 0)
		echo "a";
	else {
	
		$flag = 1;
		while (!$res->EOF) {
			$PK_COURSE_OFFERING_ARR = explode(",", $res->fields['PK_COURSE_OFFERING']);
			$PK_COURSE_OFFERING1 	= explode(",", $_REQUEST['PK_COURSE_OFFERING']);
			
			foreach($PK_COURSE_OFFERING1 as $PK_COURSE_OFFERING2) {
				if(in_array($PK_COURSE_OFFERING2, $PK_COURSE_OFFERING_ARR)) {
					$flag = 0;
					echo "b";
					exit;
				}
			}
			
			$res->MoveNext();
		}
		if($flag == 1)
			echo "a";
		else
			echo "b";
	}
} else if($_REQUEST['TYPE'] == 9){
	$START_DATE = date("Y-m-d", strtotime($_REQUEST['START_DATE']));
	$END_DATE 	= date("Y-m-d", strtotime($_REQUEST['END_DATE']));
	
	$res = $db->Execute("SELECT PK_TUITION_BATCH_MASTER, PK_CAMPUS_PROGRAM FROM S_TUITION_BATCH_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '$_REQUEST[TYPE]' AND AY = '$_REQUEST[AY]' AND AP = '$_REQUEST[AP]' AND TUITION_START_DATE = '$START_DATE' AND TUITION_END_DATE = '$END_DATE' $cond ");
	if($res->RecordCount() == 0)
		echo "a";
	else {
	
		$flag = 1;
		while (!$res->EOF) {
			$PK_CAMPUS_PROGRAM_ARR 	= explode(",", $res->fields['PK_CAMPUS_PROGRAM']);
			$prog_id 				= explode(",", $_REQUEST['prog_id']);
			
			foreach($prog_id as $prog_id2) {
				if(in_array($prog_id2, $PK_CAMPUS_PROGRAM_ARR)) {
					$flag = 0;
					echo "b";
					exit;
				}
			}
			
			$res->MoveNext();
		}
		if($flag == 1)
			echo "a";
		else
			echo "b";
	}
}