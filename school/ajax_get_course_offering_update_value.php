<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
$type 		= $_REQUEST['type'];
$PK_CAMPUS 	= $_REQUEST['campus'];
if($type == 1) { ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ></option>
		<? $res_type = $db->Execute("select PK_COURSE_OFFERING_STATUS,COURSE_OFFERING_STATUS from M_COURSE_OFFERING_STATUS WHERE ACTIVE = 1 order by COURSE_OFFERING_STATUS ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_COURSE_OFFERING_STATUS'] ?>" ><?=$res_type->fields['COURSE_OFFERING_STATUS']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 2) {
	$_REQUEST['campus'] 		= $PK_CAMPUS;
	$_REQUEST['id'] 			= 'UPDATE_VALUE';
	$_REQUEST['SELECTED_VALUE'] = '';
	include("ajax_get_teacher_from_campus.php");
} else if($type == 3) { ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ></option>
		<option value="1" ><?=YES ?></option>
		<option value="0" ><?=NO ?></option>
	</select>
<? } else if($type == 4) {
	$_REQUEST['campus'] 		 = $PK_CAMPUS;
	$_REQUEST['name'] 		 	 = 'UPDATE_VALUE';
	$_REQUEST['id'] 		 	 = 'UPDATE_VALUE';
	$_REQUEST['SELECTED_VALUE1'] = '';
	$_REQUEST['disable'] 		 = '';
	include("ajax_get_campus_room.php");
} else if($type == 5) {
} else if($type == 6) { ?>
	<input id="UPDATE_VALUE" name="UPDATE_VALUE" value="" type="text" class="form-control" placeholder="LMS Code" >
<? } else { ?>
<? } ?>