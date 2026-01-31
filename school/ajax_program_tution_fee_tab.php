<? require_once("../global/config.php"); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 

 $_GET[id]=$_POST['id'];

$program_fee_id = 0;
$res = $db->Execute("select PK_CAMPUS_PROGRAM_FEE from M_CAMPUS_PROGRAM_FEE WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' ORDER BY AY ASC, AP ASC ");
while (!$res->EOF) {
	$_REQUEST['program_fee_id'] 	  	= $program_fee_id;
	$_REQUEST['PK_CAMPUS_PROGRAM_FEE'] 	= $res->fields['PK_CAMPUS_PROGRAM_FEE'];

	include("ajax_program_fee.php");

	$program_fee_id++;

	$res->MoveNext();
} ?>
<input type="hidden" name="last_row_program_fee_id" value="<?=$program_fee_id?>" id="last_row_program_fee_id"/> <!-- 30 may 2023 -->
<input type="hidden" name="delete_row_program_fee_id" value="" id="delete_row_program_fee_id"/> <!-- //DIAM-786 -->

