<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/employee.php");
require_once("../language/school_profile.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
} ?>

<table class="table table-hover">
	<thead>
		<tr>
			<th>#</th>
			<th><?=DATE?></th>
			<th><?=TYPE?></th>
			<th><?=STATUS?></th>
			<th><?=COMMENTS?></th>
			<th><?=CREATED_BY?></th>
			<th><?=OPTION?></th>
		</tr>
	</thead>
	<tbody>
		<? $cond = "";
		$res_type = $db->Execute("select S_EMPLOYEE_NOTES.PK_EMPLOYEE_NOTES, DATE_FORMAT(S_EMPLOYEE_NOTES.CREATED_ON,'%m/%d/%Y<br />%r') AS CREATED_ON, NOTES, EMPLOYEE_NOTE_TYPE, CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME, NOTE_STATUS, IF(NOTE_DATE = '0000-00-00', '', DATE_FORMAT(NOTE_DATE,'%m/%d/%Y')) AS NOTE_DATE, NOTE_TIME  
		FROM 
		S_EMPLOYEE_NOTES 
		LEFT JOIN Z_USER ON Z_USER.PK_USER = S_EMPLOYEE_NOTES.CREATED_BY  
		LEFT JOIN S_EMPLOYEE_MASTER ON Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE = 2 
		LEFT JOIN M_NOTE_STATUS ON M_NOTE_STATUS.PK_NOTE_STATUS = S_EMPLOYEE_NOTES.PK_NOTE_STATUS 
		LEFT JOIN M_EMPLOYEE_NOTE_TYPE ON M_EMPLOYEE_NOTE_TYPE.PK_EMPLOYEE_NOTE_TYPE = S_EMPLOYEE_NOTES.PK_EMPLOYEE_NOTE_TYPE
		
		WHERE 
		S_EMPLOYEE_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
		AND S_EMPLOYEE_NOTES.PK_EMPLOYEE_MASTER = '$_REQUEST[id]' 
		
		$cond ORDER BY S_EMPLOYEE_NOTES.CREATED_ON DESC ");
		$i = 0;
		while (!$res_type->EOF) { 
			$i++; ?>
			<tr>
				<td><?=$i?></td>
				<td>
					<? echo $res_type->fields['NOTE_DATE'];
					if($res_type->fields['NOTE_TIME'] != '00-00-00' && $res_type->fields['NOTE_DATE'] != '') 
						echo '<br />'.date("h:i A", strtotime($res_type->fields['NOTE_TIME'])); ?>
				</td>
				<td><?=$res_type->fields['EMPLOYEE_NOTE_TYPE']?></td>
				<td><?=$res_type->fields['NOTE_STATUS']?></td>
				<td><?=nl2br($res_type->fields['NOTES'])?></td>
				<td><?=$res_type->fields['NAME']?></td>
				<td>
					<a href="employee_notes?eid=<?=$_REQUEST['id']?>&id=<?=$res_type->fields['PK_EMPLOYEE_NOTES']?>&t=<?=$_REQUEST['t']?>" title="<?=EDIT?>" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
					<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_EMPLOYEE_NOTES']?>','notes')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
				</td>
			</tr>
		<?	$res_type->MoveNext();
		} ?>
	</tbody>
</table>