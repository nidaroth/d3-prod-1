<? require_once("../global/config.php");

	if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
		header("location:../index");
		exit;
	} 

	$_GET[id]=$_POST['id'];

	$award_id = 0;
	$res_prog_fee = $db->Execute("select PK_CAMPUS_PROGRAM_AWARD from M_CAMPUS_PROGRAM_AWARD WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	while (!$res_prog_fee->EOF) {
		$_REQUEST['award_id'] 				 = $award_id;
		$_REQUEST['PK_CAMPUS_PROGRAM_AWARD'] = $res_prog_fee->fields['PK_CAMPUS_PROGRAM_AWARD'];

		include("ajax_program_award.php");
		$award_id++;

		$res_prog_fee->MoveNext();
	}
?>
<input type="hidden" name="last_row_program_award_id" value="<?=$award_id?>" id="last_row_program_award_id"/> <!-- 30 may 2023 -->

