<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/program.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}
if($_REQUEST['new_pid'] > 0) {
	$res = $db->Execute("select * from M_CAMPUS_PROGRAM_REQUIREMENT WHERE PK_CAMPUS_PROGRAM = '$_REQUEST[pid]' ");
	while (!$res->EOF) { 
		
		$PROGRAM_REQUIREMENT['PK_REQUIREMENT_CATEGORY']  	= $res->fields['PK_REQUIREMENT_CATEGORY'];
		$PROGRAM_REQUIREMENT['REQUIREMENT']  				= $res->fields['REQUIREMENT'];
		$PROGRAM_REQUIREMENT['MANDATORY'] 	 				= $res->fields['MANDATORY'];
		$PROGRAM_REQUIREMENT['ACTIVE'] 		 				= $res->fields['ACTIVE'];
		$PROGRAM_REQUIREMENT['PK_CAMPUS_PROGRAM']  			= $_REQUEST['new_pid'];
		$PROGRAM_REQUIREMENT['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
		$PROGRAM_REQUIREMENT['CREATED_BY']  				= $_SESSION['PK_USER'];
		$PROGRAM_REQUIREMENT['CREATED_ON']  				= date("Y-m-d H:i");
		db_perform('M_CAMPUS_PROGRAM_REQUIREMENT', $PROGRAM_REQUIREMENT, 'insert');
		
		$res->MoveNext();
	}
}