<? require_once("../global/config.php"); 
/*if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['ADMIN_PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}*/

$date = '';
if($_REQUEST['date'] != '') {
	$date = date("Y-m-d", strtotime($_REQUEST['date']));
	
	$res_cs = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_REQUEST[val]' AND SCHEDULE_DATE = '$date' ");
	$_REQUEST['def'] = $res_cs->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
}	
?>
<select id="PK_COURSE_OFFERING_SCHEDULE_DETAIL" name="PK_COURSE_OFFERING_SCHEDULE_DETAIL" class="form-control" onchange="clear_div()" >
	<option ></option>
	<? $res_cs = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL, CONCAT(DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),' ',DATE_FORMAT(START_TIME,'%h:%i %p'),' - ',DATE_FORMAT(END_TIME,'%h:%i %p')) AS SCHEDULE_DATE_1, IF(COMPLETED = 1,' - Completed','') AS COMPLETED from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_REQUEST[val]' ORDER BY SCHEDULE_DATE ASC, START_TIME ASC ");
	while (!$res_cs->EOF) { ?>
		<option value="<?=$res_cs->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL']?>" <? if($_REQUEST['def'] == $res_cs->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL']) echo "selected"; ?> ><?=$res_cs->fields['SCHEDULE_DATE_1'].$res_cs->fields['COMPLETED'] ?></option>
	<?	$res_cs->MoveNext();
	} ?>
</select>