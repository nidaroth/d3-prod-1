<? require_once("../global/config.php"); 
require_once("get_department_from_t.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$emp_cond = "";
if($_REQUEST['t'] != '') {	
	$t_1 	= explode(',',$_REQUEST['t']);
	$dep_t 	= "-1";
	foreach($t_1 as $t){
		if($dep_t != '')
			$dep_t .= ",";
			
		$dep_t .= get_department_from_t($t);
	}
	$emp_cond .= " AND PK_DEPARTMENT IN ($dep_t) ";
}

if($_REQUEST['show_inactive'] != 1) {	
	//$emp_cond .= " AND S_EMPLOYEE_MASTER.ACTIVE = 1 ";
}

$res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(trim(LAST_NAME),', ',trim(FIRST_NAME)) AS NAME, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER, S_EMPLOYEE_DEPARTMENT WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER $emp_cond  GROUP BY S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER order by S_EMPLOYEE_MASTER.ACTIVE DESC, NAME ASC"); ?>

<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER" class="form-control">
	<option></option>
	<? while (!$res_type->EOF) { 
		$option_label = $res_type->fields['NAME'];
		if($res_type->fields['ACTIVE'] == 0)
			$option_label .= " (Inactive)"; ?>
		<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>