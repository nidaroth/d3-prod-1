<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3 && $_SESSION['PK_ROLES'] != 4 && $_SESSION['PK_ROLES'] != 5)){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_PLACEMENT_NOTES 	= $_REQUEST['PK_STUDENT_PLACEMENT_NOTES'];
$placement_notes_id				= $_REQUEST['placement_notes_id'];
$PK_STUDENT_MASTER				= $_REQUEST['id'];
$PK_STUDENT_ENROLLMENT			= $_REQUEST['eid'];

if($PK_STUDENT_PLACEMENT_NOTES == ''){
	$PK_PLACEMENT_STUDENT_NOTE_TYPE 	= '';
	$PK_PLACEMENT_STUDENT_NOTE_STATUS 	= '';
	$PK_EMPLOYEE_MASTER_PLACEMENT_NOTES	= '';
	$COMMENTS					 		= '';
	$NOTE_COMPLETE				 		= '';
	$NOTE_DATE						 	= '';
	$FOLLOW_UP_DATE						= '';
	$ACTIVE								= '';
} else {
	$res = $db->Execute("select * from S_STUDENT_PLACEMENT_NOTES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_PLACEMENT_NOTES = '$PK_STUDENT_PLACEMENT_NOTES'");

	$PK_STUDENT_MASTER					= $res->fields['PK_STUDENT_MASTER'];
	$PK_STUDENT_ENROLLMENT				= $res->fields['PK_STUDENT_ENROLLMENT'];
	$PK_PLACEMENT_STUDENT_NOTE_TYPE 	= $res->fields['PK_PLACEMENT_STUDENT_NOTE_TYPE'];
	$PK_PLACEMENT_STUDENT_NOTE_STATUS 	= $res->fields['PK_PLACEMENT_STUDENT_NOTE_STATUS'];
	$PK_EMPLOYEE_MASTER_PLACEMENT_NOTES = $res->fields['PK_EMPLOYEE_MASTER_PLACEMENT_NOTES'];
	$COMMENTS 							= $res->fields['COMMENTS'];
	$NOTE_COMPLETE 						= $res->fields['NOTE_COMPLETE'];
	$NOTE_DATE 							= $res->fields['NOTE_DATE'];
	$FOLLOW_UP_DATE						= $res->fields['FOLLOW_UP_DATE'];
	$ACTIVE 							= $res->fields['ACTIVE'];
		
	if($NOTE_DATE != '0000-00-00')
		$NOTE_DATE = date("m/d/Y",strtotime($NOTE_DATE));
	else
		$NOTE_DATE = '';
	
	if($FOLLOW_UP_DATE != '0000-00-00')
		$FOLLOW_UP_DATE = date("m/d/Y",strtotime($FOLLOW_UP_DATE));
	else
		$FOLLOW_UP_DATE = '';
}
?>
<tr id="student_note_div_<?=$placement_notes_id?>" >
	<td width="10%">
		<input type="hidden" name="PK_STUDENT_PLACEMENT_NOTES[]" id="PK_STUDENT_PLACEMENT_NOTES<?=$placement_notes_id?>" value="<?=$PK_STUDENT_PLACEMENT_NOTES?>" />
		<input type="hidden" name="placement_notes_id[]"  value="<?=$placement_notes_id?>" />
		<input type="text" class="form-control date" placeholder="" name="NOTE_DATE[]" id="NOTE_DATE<?=$placement_notes_id?>" value="<?=$NOTE_DATE?>" style="width:100%;" />
	</td>
	<td width="12%">
		<select id="PK_PLACEMENT_STUDENT_NOTE_TYPE<?=$placement_notes_id?>" name="PK_PLACEMENT_STUDENT_NOTE_TYPE[]" class="form-control" style="width:100%;">
			<option></option>
			<? $res_type = $db->Execute("select PK_PLACEMENT_STUDENT_NOTE_TYPE,DESCRIPTION from M_PLACEMENT_STUDENT_NOTE_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DESCRIPTION ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_PLACEMENT_STUDENT_NOTE_TYPE']?>" <? if($PK_PLACEMENT_STUDENT_NOTE_TYPE == $res_type->fields['PK_PLACEMENT_STUDENT_NOTE_TYPE']) echo "selected"; ?> ><?=$res_type->fields['DESCRIPTION'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td width="12%">
		<select id="PK_PLACEMENT_STUDENT_NOTE_STATUS<?=$placement_notes_id?>" name="PK_PLACEMENT_STUDENT_NOTE_STATUS[]" class="form-control" style="width:100%;">
			<option></option>
			<? $res_type = $db->Execute("select PK_PLACEMENT_STUDENT_NOTE_STATUS,DESCRIPTION from M_PLACEMENT_STUDENT_NOTE_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DESCRIPTION ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_PLACEMENT_STUDENT_NOTE_STATUS']?>" <? if($PK_PLACEMENT_STUDENT_NOTE_STATUS == $res_type->fields['PK_PLACEMENT_STUDENT_NOTE_STATUS']) echo "selected"; ?> ><?=$res_type->fields['DESCRIPTION'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td width="15%">
		<select id="PK_EMPLOYEE_MASTER_PLACEMENT_NOTES<?=$placement_notes_id?>" name="PK_EMPLOYEE_MASTER_PLACEMENT_NOTES[]" class="form-control" style="width:100%;">
			<option></option>
			<? $res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <? if($PK_EMPLOYEE_MASTER_PLACEMENT_NOTES == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> ><?=$res_type->fields['NAME'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td width="10%" class="text-center">
		<input type="text" class="form-control date" placeholder="" name="FOLLOW_UP_DATE[]" id="FOLLOW_UP_DATE<?=$placement_notes_id?>" value="<?=$FOLLOW_UP_DATE?>" style="width:100%;" />
	</td>
	<td width="20%">
		<textarea class="form-control  rich" id="COMMENTS<?=$placement_notes_id?>" name="COMMENTS[]"><?=$COMMENTS?></textarea>
	</td>
	<td width="6%" class="text-center">
		<input type="checkbox" name="NOTE_COMPLETE[]" id="NOTE_COMPLETE<?=$placement_notes_id?>" value="1" <? if($NOTE_COMPLETE == 1) echo "checked"; ?> />
	</td>
	<td width="10%" class="text-center">
		<a href="javascript:void(0);" onclick="delete_row('<?=$placement_notes_id?>','note')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</td>
</tr>