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
	$cond .= " AND (CODE  like '%$SEARCH%' OR M_CAMPUS_PROGRAM.DESCRIPTION LIKE '%$SEARCH%' OR PROGRAM_TRANSCRIPT_CODE LIKE '%$SEARCH%' )";

//echo $cond;
?>
<? $res_s = $db->Execute("select PK_CAMPUS_PROGRAM from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
$PK_CAMPUS_PROGRAM_ARR = explode(",",$res_s->fields['PK_CAMPUS_PROGRAM']);

$res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by ACTIVE DESC,CODE ASC"); //<!-- DIAM-1965 -->
while (!$res_type->EOF) { 
	$checked = "";
	if(!empty($PK_CAMPUS_PROGRAM_ARR)){
		foreach($PK_CAMPUS_PROGRAM_ARR as $PK_CAMPUS_PROGRAM){
			if($res_type->fields['PK_CAMPUS_PROGRAM'] == $PK_CAMPUS_PROGRAM)
				$checked = "checked";
		}
	} ?>
	<div class="col-md-12">
		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
			<input type="checkbox" class="custom-control-input" id="PK_CAMPUS_PROGRAM_<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" name="PK_CAMPUS_PROGRAM[]" value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <?=$checked?> >
			<label class="custom-control-label" for="PK_CAMPUS_PROGRAM_<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" style="<?=($res_type->fields['ACTIVE'] == 0)? 'color: red !important;': '';?>"><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></label><!-- DIAM-1965 -->
		</div>
	</div>
<?	$res_type->MoveNext();
} ?>