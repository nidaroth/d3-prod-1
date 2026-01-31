<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/student.php");
require_once("check_access.php");

$cond = "";
if(isset($_POST['FILTER_ACTIVE_SATATUS']) && $_POST['FILTER_ACTIVE_SATATUS'] == 1){
	$cond .= " AND ACTIVE = 1 ";
}elseif(isset($_POST['FILTER_ACTIVE_SATATUS'])  && $_POST['FILTER_ACTIVE_SATATUS'] == 0){
	$cond .= " AND ACTIVE = 0 ";
}


$SEARCH = isset($_POST['SEARCH']) ? mysql_real_escape_string($_POST['SEARCH']) : '';
if($SEARCH != '')
	$cond .= " AND (STUDENT_GROUP  like '%$SEARCH%')";

//echo $cond;
?>
<? $res_s = $db->Execute("select PK_STUDENT_GROUP from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
$PK_STUDENT_GROUP_ARR = explode(",",$res_s->fields['PK_STUDENT_GROUP']);

$res_type = $db->Execute("select PK_STUDENT_GROUP, STUDENT_GROUP,ACTIVE from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by ACTIVE DESC,STUDENT_GROUP ASC");
while (!$res_type->EOF) { 
	$checked = "";
	if(!empty($PK_STUDENT_GROUP_ARR)){
		foreach($PK_STUDENT_GROUP_ARR as $PK_STUDENT_GROUP){
			if($res_type->fields['PK_STUDENT_GROUP'] == $PK_STUDENT_GROUP)
				$checked = "checked";
		}
	} ?>
	<div class="col-md-12">
		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
			<input type="checkbox" class="custom-control-input" id="PK_STUDENT_GROUP_<?=$res_type->fields['PK_STUDENT_GROUP']?>" name="PK_STUDENT_GROUP[]" value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" <?=$checked?> >
			<label class="custom-control-label" for="PK_STUDENT_GROUP_<?=$res_type->fields['PK_STUDENT_GROUP']?>" style="<?=($res_type->fields['ACTIVE'] == 0)? 'color: red !important;': '';?>"><?=$res_type->fields['STUDENT_GROUP'] ?></label>
		</div>
	</div>
<?	$res_type->MoveNext();
} ?>