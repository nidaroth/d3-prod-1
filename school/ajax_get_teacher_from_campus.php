<? require_once("../global/config.php"); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}
$PK_CAMPUS 			= $_REQUEST['campus'];
$id 				= $_REQUEST['id'];
$DONT_INCLUDE		= $_REQUEST['DONT_INCLUDE'];

if($_REQUEST['SELECTED_VALUE'] != '')
	$SELECTED_VALUE_A = explode(",",$_REQUEST['SELECTED_VALUE']);
else
	$SELECTED_VALUE_A = array();

$onchange = "";
if($id == 'INSTRUCTOR')
	$onchange = " onchange='set_assistance()' ";
	
if($DONT_INCLUDE != '')
	$INSTRUCTOR_cond = " AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER NOT IN ($DONT_INCLUDE) ";

$name 		= $id;
$multiple 	= "";

if($id == "ASSISTANT"){
	$name 		= "ASSISTANT[]";
	$multiple 	= "multiple";
} 
$actual_URL = $_SERVER['HTTP_HOST'];
$where_instructor="(IS_FACULTY = 1 OR IS_ADMIN=1)";


?>
<select id="<?=$id?>" name="<?=$name?>" <?=$multiple?> class="form-control" <?=$onchange?> >
	<? if($multiple == ''){ ?>
		<option value=""></option>
	<? } 
	$res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER, S_EMPLOYEE_CAMPUS WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND S_EMPLOYEE_CAMPUS.PK_CAMPUS = '$PK_CAMPUS' AND $where_instructor $INSTRUCTOR_cond GROUP BY S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER order by S_EMPLOYEE_MASTER.ACTIVE DESC, CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
	while (!$res_type->EOF) { 
		$selected = "";
		foreach($SELECTED_VALUE_A as $SELECTED_VALUE_A1){
			if($SELECTED_VALUE_A1 == $res_type->fields['PK_EMPLOYEE_MASTER']) {
				$selected = "selected";
				break;
			}
		} 
		
		$option_label = $res_type->fields['NAME'];
		if($res_type->fields['ACTIVE'] == 0)
			$option_label .= " (Inactive)"; ?>
		<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" <?=$selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>