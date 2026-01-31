<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3 && $_SESSION['PK_ROLES'] != 4 && $_SESSION['PK_ROLES'] != 5)){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_PLACEMENT_EVENTS 	= $_REQUEST['PK_STUDENT_PLACEMENT_EVENTS'];
$placement_events_id			= $_REQUEST['placement_events_id'];
$PK_STUDENT_MASTER				= $_REQUEST['id'];
$PK_STUDENT_ENROLLMENT			= $_REQUEST['eid'];

if($PK_STUDENT_PLACEMENT_EVENTS == ''){
	$PK_PLACEMENT_STUDENT_EVENT_TYPE 	= '';
	$PK_PLACEMENT_STUDENT_EVENT_STATUS 	= '';
	$PK_PLACEMENT_STUDENT_EVENT_OTHER 	= '';
	$PK_EMPLOYEE_MASTER_PLACEMENT_EVENTS= '';
	$PK_COMPANY_PLACEMENT_EVENTS 		= '';
	$NOTES					 			= '';
	$EVENT_COMPLETE				 		= '';
	$EVENT_DATE						 	= '';
	$ACTIVE								= '';
} else {
	$res = $db->Execute("select * from S_STUDENT_PLACEMENT_EVENTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_PLACEMENT_EVENTS = '$PK_STUDENT_PLACEMENT_EVENTS'");

	$PK_STUDENT_MASTER					= $res->fields['PK_STUDENT_MASTER'];
	$PK_STUDENT_MASTER					= $res->fields['PK_STUDENT_MASTER'];
	$PK_PLACEMENT_STUDENT_EVENT_TYPE 	= $res->fields['PK_PLACEMENT_STUDENT_EVENT_TYPE'];
	$PK_PLACEMENT_STUDENT_EVENT_STATUS 	= $res->fields['PK_PLACEMENT_STUDENT_EVENT_STATUS'];
	$PK_PLACEMENT_STUDENT_EVENT_OTHER 	= $res->fields['PK_PLACEMENT_STUDENT_EVENT_OTHER'];
	$PK_EMPLOYEE_MASTER_PLACEMENT_EVENTS= $res->fields['PK_EMPLOYEE_MASTER_PLACEMENT_EVENTS'];
	$PK_COMPANY_PLACEMENT_EVENTS 		= $res->fields['PK_COMPANY_PLACEMENT_EVENTS'];
	$NOTES 								= $res->fields['NOTES'];
	$EVENT_COMPLETE 					= $res->fields['EVENT_COMPLETE'];
	$EVENT_DATE 						= $res->fields['EVENT_DATE'];
	$ACTIVE 							= $res->fields['ACTIVE'];
		
	if($EVENT_DATE != '0000-00-00')
		$EVENT_DATE = date("m/d/Y",strtotime($EVENT_DATE));
	else
		$EVENT_DATE = '';
}
?>
<tr id="student_event_div_<?=$placement_events_id?>" >
	<td width="10%">
		<input type="hidden" name="PK_STUDENT_PLACEMENT_EVENTS[]" id="PK_STUDENT_PLACEMENT_EVENTS<?=$placement_events_id?>" value="<?=$PK_STUDENT_PLACEMENT_EVENTS?>" />
		<input type="hidden" name="placement_events_id[]"  value="<?=$placement_events_id?>" />
		<input type="text" class="form-control date" placeholder="" name="EVENT_DATE[]" id="EVENT_DATE<?=$placement_events_id?>" value="<?=$EVENT_DATE?>" style="width:100%;" />
	</td>
	<td width="12%">
		<select id="PK_PLACEMENT_STUDENT_EVENT_TYPE<?=$placement_events_id?>" name="PK_PLACEMENT_STUDENT_EVENT_TYPE[]" class="form-control" style="width:100%;">
			<option></option>
			<? $res_type = $db->Execute("select PK_PLACEMENT_STUDENT_EVENT_TYPE,EVENT_DESCRIPTION from M_PLACEMENT_STUDENT_EVENT_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by EVENT_DESCRIPTION ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_PLACEMENT_STUDENT_EVENT_TYPE']?>" <? if($PK_PLACEMENT_STUDENT_EVENT_TYPE == $res_type->fields['PK_PLACEMENT_STUDENT_EVENT_TYPE']) echo "selected"; ?> ><?=$res_type->fields['EVENT_DESCRIPTION'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td width="12%">
		<select id="PK_PLACEMENT_STUDENT_EVENT_STATUS<?=$placement_events_id?>" name="PK_PLACEMENT_STUDENT_EVENT_STATUS[]" class="form-control" style="width:100%;">
			<option></option>
			<? $res_type = $db->Execute("select PK_PLACEMENT_STUDENT_EVENT_STATUS,EVENT_STATUS from M_PLACEMENT_STUDENT_EVENT_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by EVENT_STATUS ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_PLACEMENT_STUDENT_EVENT_STATUS']?>" <? if($PK_PLACEMENT_STUDENT_EVENT_STATUS == $res_type->fields['PK_PLACEMENT_STUDENT_EVENT_STATUS']) echo "selected"; ?> ><?=$res_type->fields['EVENT_STATUS'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td width="12%">
		<select id="PK_PLACEMENT_STUDENT_EVENT_OTHER<?=$placement_events_id?>" name="PK_PLACEMENT_STUDENT_EVENT_OTHER[]" class="form-control" style="width:100%;">
			<option></option>
			<? $res_type = $db->Execute("select PK_PLACEMENT_STUDENT_EVENT_OTHER,PLACEMENT_STUDENT_EVENT_OTHER from M_PLACEMENT_STUDENT_EVENT_OTHER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by PLACEMENT_STUDENT_EVENT_OTHER ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_PLACEMENT_STUDENT_EVENT_OTHER']?>" <? if($PK_PLACEMENT_STUDENT_EVENT_OTHER == $res_type->fields['PK_PLACEMENT_STUDENT_EVENT_OTHER']) echo "selected"; ?> ><?=$res_type->fields['PLACEMENT_STUDENT_EVENT_OTHER'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td width="15%">
		<select id="PK_EMPLOYEE_MASTER_PLACEMENT_EVENTS<?=$placement_events_id?>" name="PK_EMPLOYEE_MASTER_PLACEMENT_EVENTS[]" class="form-control" style="width:100%;">
			<option></option>
			<? $res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <? if($PK_EMPLOYEE_MASTER_PLACEMENT_EVENTS == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> ><?=$res_type->fields['NAME'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td width="12%">
		<select id="PK_COMPANY_PLACEMENT_EVENTS<?=$placement_events_id?>" name="PK_COMPANY_PLACEMENT_EVENTS[]" class="form-control" style="width:100%;">
			<option></option>
			<? $res_type = $db->Execute("select PK_COMPANY,COMPANY_NAME from S_COMPANY WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COMPANY_NAME ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_COMPANY']?>" <? if($PK_COMPANY_PLACEMENT_EVENTS == $res_type->fields['PK_COMPANY']) echo "selected"; ?> ><?=$res_type->fields['COMPANY_NAME'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td width="15%">
		<textarea class="form-control  rich" id="NOTES<?=$placement_events_id?>" name="NOTES[]"><?=$NOTES?></textarea>
	</td>
	<td width="6%" class="text-center">
		<input type="checkbox" name="EVENT_COMPLETE[]" id="EVENT_COMPLETE<?=$placement_events_id?>" value="1" <? if($EVENT_COMPLETE == 1) echo "checked"; ?> />
	</td>
	<td width="10%" class="text-center">
		<a href="javascript:void(0);" onclick="delete_row('<?=$placement_events_id?>','event')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</td>
</tr>