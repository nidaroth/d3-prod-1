<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$PK_COURSE	= $_REQUEST['val'];
$id			= $_REQUEST['id'];
$multiple	= $_REQUEST['multiple'];
$PK_COURSE_OFFERING_SCHEDULE = $_REQUEST['PK_COURSE_OFFERING_SCHEDULE'];

if($multiple == 1)
	$name = "PK_COURSE_OFFERING_SCHEDULE[]";
else
	$name = "PK_COURSE_OFFERING_SCHEDULE";
	
if($id != '')
	$id = "PK_COURSE_OFFERING_SCHEDULE_".$id;
else
	$id = "PK_COURSE_OFFERING_SCHEDULE";
	
?>
<select id="<?=$id?>" name="<?=$name?>" class="form-control" >
	<option value="" ></option>
	 <? $res_type = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE,if(START_DATE != '', DATE_FORMAT(START_DATE,'%m/%d/%Y'),'') as START_DATE, if(END_DATE != '', DATE_FORMAT(END_DATE,'%m/%d/%Y'),'') as END_DATE from S_COURSE_OFFERING_SCHEDULE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE = '$PK_COURSE' order by START_DATE ASC");
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_COURSE_OFFERING_SCHEDULE'] ?>" <? if($res_type->fields['PK_COURSE_OFFERING_SCHEDULE'] == $PK_COURSE_OFFERING_SCHEDULE) echo "selected"; ?> ><?=$res_type->fields['START_DATE'].' - '.$res_type->fields['END_DATE']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>