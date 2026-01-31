<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$status_count 		 				= $_REQUEST['status_count'];
$PK_NOTIFICATION_SETTINGS_DETAIL 	= $_REQUEST['PK_NOTIFICATION_SETTINGS_DETAIL'];
if($PK_NOTIFICATION_SETTINGS_DETAIL != '') {
	$res_type1 = $db->Execute("select PK_STUDENT_STATUS,PK_EMPLOYEE_MASTER from S_NOTIFICATION_SETTINGS_DETAIL WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_NOTIFICATION_SETTINGS_DETAIL = '$PK_NOTIFICATION_SETTINGS_DETAIL'  ");
	$PK_STUDENT_STATUS 		= $res_type1->fields['PK_STUDENT_STATUS'];
	$PK_EMPLOYEE_MASTER_arr = explode(",",$res_type1->fields['PK_EMPLOYEE_MASTER']);
} else {
	$PK_STUDENT_STATUS 		= '';
	$PK_EMPLOYEE_MASTER_arr = array();
}
?>
<div class="row" id="add_status_div_<?=$status_count?>" >
	<div class="col-sm-4">
		<input type="hidden" name="PK_NOTIFICATION_SETTINGS_DETAIL[]" value="<?=$PK_NOTIFICATION_SETTINGS_DETAIL?>" />
		<input type="hidden" name="status_count[]" value="<?=$status_count?>" />
		
		<select id="NOTI_PK_STUDENT_STATUS_<?=$status_count?>" name="NOTI_PK_STUDENT_STATUS_<?=$status_count?>" class="form-control" >
			<option value="" ></option>
			<? $res_type1 = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 $cond order by STUDENT_STATUS ASC");
			while (!$res_type1->EOF) { ?>
				<option value="<?=$res_type1->fields['PK_STUDENT_STATUS']?>" <? if($PK_STUDENT_STATUS == $res_type1->fields['PK_STUDENT_STATUS']) echo "selected"; ?> ><?=$res_type1->fields['STUDENT_STATUS'].' - '.$res_type1->fields['DESCRIPTION']?></option>
			<?	$res_type1->MoveNext();
			} ?>
		</select>
	</div>
	<div class="col-sm-7">
		<select id="NOTI_PK_EMPLOYEE_MASTER_<?=$status_count?>" name="NOTI_PK_EMPLOYEE_MASTER_<?=$status_count?>[]" multiple class="form-control select2" >
		<? $res_type1 = $db->Execute("SELECT * FROM (SELECT S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME, EMPLOYEE_ID FROM S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  S_EMPLOYEE_MASTER.ACTIVE = 1 AND IS_FACULTY = 0) as TEMP GROUP BY TEMP.PK_EMPLOYEE_MASTER ORDER BY NAME ASC"); 
		while (!$res_type1->EOF) { 
			$selected = '';
			$PK_EMPLOYEE_MASTER = $res_type1->fields['PK_EMPLOYEE_MASTER'];
			
			$dep = '';
			$res2 = $db->Execute("select DEPARTMENT FROM M_DEPARTMENT,S_EMPLOYEE_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
			while (!$res2->EOF) {
				if($dep != '')
					$dep .= ', ';
					
				$dep .= $res2->fields['DEPARTMENT'];
				$res2->MoveNext();
			}
			
			if($dep != '')
				$dep = ' ['.$dep.']';
																
			foreach($PK_EMPLOYEE_MASTER_arr as $PK_EMPLOYEE_MASTER1) {
				if($PK_EMPLOYEE_MASTER1 == $PK_EMPLOYEE_MASTER)
					$selected = 'selected'; 
			} ?>
			<option value="<?=$PK_EMPLOYEE_MASTER ?>" <?=$selected ?> ><?=$res_type1->fields['NAME'].' '.$dep ?></option>
		<?	$res_type1->MoveNext();
		} ?>
		</select>
	</div>
	<div class="col-sm-1">
		<a href="javascript:void(0);" onclick="delete_row('<?=$status_count?>','noti_status')" title="Delete" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</div>
</div>