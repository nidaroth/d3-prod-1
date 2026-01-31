<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$PK_TERM	= $_REQUEST['PK_TERM'];
$UNION 		= "";

$course_name = "PK_COURSE";
$course_id 	 = "PK_COURSE";
$multiple 	 = "";

if($_REQUEST['page'] == "tuition" && !empty($_REQUEST['def_val'])) {
	$PK_COURSE1 = implode(",", $_REQUEST['def_val']);
	$UNION = " UNION select S_COURSE.PK_COURSE, COURSE_CODE, S_COURSE.ACTIVE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE IN ($PK_COURSE1) ";
	
	$course_name = "PK_COURSE[]";
	$course_id 	 = "PK_COURSE";
	$multiple 	 = "multiple";
}
?>
<select id="<?=$course_id?>" name="<?=$course_name?>" <?=$multiple?> class="form-control" >
	<? if($_REQUEST['page'] != "tuition") { ?>
	<option value="" ></option>		
	<? } ?>
	
	<? $res_type = $db->Execute("SELECT * FROM (select S_COURSE.PK_COURSE, COURSE_CODE, S_COURSE.ACTIVE from S_COURSE_OFFERING, S_COURSE WHERE S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND PK_TERM_MASTER IN ($PK_TERM) $UNION ) as TEMP GROUP BY PK_COURSE order by ACTIVE DESC, COURSE_CODE ASC ");
	while (!$res_type->EOF) { 
		if($res_type->fields['ACTIVE'] == 0)
			$str .= ' (Inactive)'; 
			
		$flag = 0;
		if($_REQUEST['page'] == "tuition"){ 
			if(in_array($res_type->fields['PK_COURSE'], $_REQUEST['def_val']))
				$flag = 1;
		}  ?>
		<option value="<?=$res_type->fields['PK_COURSE']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> <? if($flag == 1) echo "selected"; ?> ><?=$res_type->fields['COURSE_CODE'] ?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>