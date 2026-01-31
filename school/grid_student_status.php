<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_STUDENT') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'STUDENT_STATUS ASC';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH 			= isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$ADMISSIONS			= isset($_REQUEST['ADMISSIONS']) ? mysql_real_escape_string($_REQUEST['ADMISSIONS']) : '';
$POST_TUITION		= isset($_REQUEST['POST_TUITION']) ? mysql_real_escape_string($_REQUEST['POST_TUITION']) : '';
$CLASS_ENROLLMENT	= isset($_REQUEST['CLASS_ENROLLMENT']) ? mysql_real_escape_string($_REQUEST['CLASS_ENROLLMENT']) : '';
$COMPLETED			= isset($_REQUEST['COMPLETED']) ? mysql_real_escape_string($_REQUEST['COMPLETED']) : '';
$ACTIVE				= isset($_REQUEST['ACTIVE']) ? mysql_real_escape_string($_REQUEST['ACTIVE']) : '';
$ALLOW_ATTENDANCE	= isset($_REQUEST['ALLOW_ATTENDANCE']) ? mysql_real_escape_string($_REQUEST['ALLOW_ATTENDANCE']) : '';
$PK_END_DATE		= isset($_REQUEST['PK_END_DATE']) ? ($_REQUEST['PK_END_DATE']) : '';

$offset = ($page-1)*$rows;
	
$result = array();
$where = " M_STUDENT_STATUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";

if(!empty($PK_END_DATE)) {
	$where .= " AND M_STUDENT_STATUS.PK_END_DATE IN (".implode(",",$PK_END_DATE).") ";
}

if($SEARCH != '')
	$where .= " AND (STUDENT_STATUS  like '%$SEARCH%' )";
	
$cond = "";
if($ADMISSIONS == "true") {
	if($cond != '')
		$cond .= " OR ";
	$cond .= " ADMISSIONS = 1 ";
}
if($POST_TUITION == "true") {
	if($cond != '')
		$cond .= " OR ";
	$cond .= " POST_TUITION = 1 ";
}
if($CLASS_ENROLLMENT == "true") {
	if($cond != '')
		$cond .= " OR ";
	$cond .= " CLASS_ENROLLMENT = 1 ";
}
if($COMPLETED == "true") {
	if($cond != '')
		$cond .= " OR ";
	$cond .= " COMPLETED = 1 ";
}
if($ACTIVE == "true") {
	if($cond != '')
		$cond .= " OR ";
	$cond .= " M_STUDENT_STATUS.ACTIVE = 1 ";
}
if($ALLOW_ATTENDANCE == "true") {
	if($cond != '')
		$cond .= " OR ";
	$cond .= " M_STUDENT_STATUS.ALLOW_ATTENDANCE = 1 ";
}


if($cond != "")
	$where .= " AND (".$cond.") ";

$rs = mysql_query("SELECT DISTINCT(PK_STUDENT_STATUS) FROM M_STUDENT_STATUS LEFT JOIN M_END_DATE ON M_END_DATE.PK_END_DATE = M_STUDENT_STATUS.PK_END_DATE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_STUDENT_STATUS,PK_STUDENT_STATUS_MASTER,STUDENT_STATUS, CODE,FA_STATUS,IF(ADMISSIONS = 1,'Yes','') AS ADMISSIONS_1, IF(POST_TUITION = 1,'Yes','') AS POST_TUITION_1, IF(DOC_28_1 = 1,'Yes','') AS DOC_28_1, IF(CLASS_ENROLLMENT = 1,'Yes','') AS CLASS_ENROLLMENT_1, IF(ALLOW_ATTENDANCE = 1,'Yes','') AS ALLOW_ATTENDANCE_1, IF(_1098T = 1,'Yes','') AS _1098T, IF(COMPLETED = 1,'Yes','') AS COMPLETED_1, IF(M_STUDENT_STATUS.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE_1 FROM M_STUDENT_STATUS LEFT JOIN M_END_DATE ON M_END_DATE.PK_END_DATE = M_STUDENT_STATUS.PK_END_DATE WHERE " . $where ." order by $sort $order " ;
//echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="student_status?id='.$row['PK_STUDENT_STATUS'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_CUSTOM_REPORT from S_CUSTOM_REPORT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS = '$row[PK_STUDENT_STATUS]' ");
	$res_check2 = $db->Execute("select PK_ENROLL_MANDATE_FIELDS from S_ENROLL_MANDATE_FIELDS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS = '$row[PK_STUDENT_STATUS]' ");
	$res_check3 = $db->Execute("select PK_NOTIFICATION_SETTINGS_DETAIL from S_NOTIFICATION_SETTINGS_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS = '$row[PK_STUDENT_STATUS]' ");
	$res_check4 = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS = '$row[PK_STUDENT_STATUS]'");
	$res_check5 = $db->Execute("select PK_STUDENT_STATUS_LOG from S_STUDENT_STATUS_LOG WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS = '$row[PK_STUDENT_STATUS]' ");
	
	if($row['PK_STUDENT_STATUS_MASTER'] == 0 && $res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_STUDENT_STATUS'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	$row['ACTION'] = $row['ACTIVE_1'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);