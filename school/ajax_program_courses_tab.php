<? require_once("../global/config.php"); 

	if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
		header("location:../index");
		exit;
	} 

	$_GET['id']=$_POST['id'];
	$USE_TRANSCRIPT_GROUP=$_POST['USE_TRANSCRIPT_GROUP'];

	$program_course_id = 2;
	$res = $db->Execute("select PK_CAMPUS_PROGRAM_COURSE from M_CAMPUS_PROGRAM_COURSE WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY COURSE_ORDER ASC ");
	while (!$res->EOF) {
		$_REQUEST['USE_TRANSCRIPT_GROUP']		= $USE_TRANSCRIPT_GROUP; //Ticket # 1603
		$_REQUEST['program_course_id'] 	  		= $program_course_id;
		$_REQUEST['PK_CAMPUS_PROGRAM_COURSE'] 	= $res->fields['PK_CAMPUS_PROGRAM_COURSE'];

		include("ajax_program_course.php");

		$program_course_id++;

		$res->MoveNext();
	} 
?>
<input type="hidden" name="last_row_program_course_id" value="<?=$program_course_id?>" id="last_row_program_course_id"/> <!-- 30 may 2023 -->
