<? require_once("../global/config.php"); 
require_once("../language/common.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'USER_ID ASC, LOGIN_TIME ASC, LOGOUT_TIME ASC ';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH 			= isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$PK_ACCOUNT 		= isset($_REQUEST['PK_ACCOUNT']) ? mysql_real_escape_string($_REQUEST['PK_ACCOUNT']) : '';
$USER_TYPE  		= isset($_REQUEST['USER_TYPE']) ? ($_REQUEST['USER_TYPE']) : '';
$FROM_LOGIN_DATE	= isset($_REQUEST['FROM_LOGIN_DATE']) ? mysql_real_escape_string($_REQUEST['FROM_LOGIN_DATE']) : '';
$TO_LOGIN_DATE		= isset($_REQUEST['TO_LOGIN_DATE']) ? mysql_real_escape_string($_REQUEST['TO_LOGIN_DATE']) : '';

$offset = ($page-1)*$rows;
	
$result = array();
$where = " Z_USER.PK_USER = Z_LOGIN_HISTORY.PK_USER AND Z_ACCOUNT.PK_ACCOUNT = Z_USER.PK_ACCOUNT AND Z_ACCOUNT.PK_ACCOUNT != 1 ";

if($FROM_LOGIN_DATE != '')
	$FROM_LOGIN_DATE = date("Y-m-d",strtotime($FROM_LOGIN_DATE));

if($TO_LOGIN_DATE != '')
	$TO_LOGIN_DATE = date("Y-m-d",strtotime($TO_LOGIN_DATE));

if($FROM_LOGIN_DATE != '' && $TO_LOGIN_DATE != '') {
	$where .= " AND DATE_FORMAT(LOGIN_TIME,'%Y-%m-%d') BETWEEN '$FROM_LOGIN_DATE' AND '$TO_LOGIN_DATE' ";
} else if($FROM_LOGIN_DATE != '') {
	$where .= " AND DATE_FORMAT(LOGIN_TIME,'%Y-%m-%d') >= '$FROM_LOGIN_DATE' ";
} else if($TO_LOGIN_DATE != '') {
	$where .= " AND DATE_FORMAT(LOGIN_TIME,'%Y-%m-%d') <='$TO_LOGIN_DATE' ";
}

if($PK_ACCOUNT != '')
	$where .= " AND Z_USER.PK_ACCOUNT = '$PK_ACCOUNT'  ";

$sub_cond = "";
if(!empty($USER_TYPE)) {
	foreach($USER_TYPE as $USER_TYPE_1){
		if($sub_cond != '')
			$sub_cond .= " OR ";
		
		if($USER_TYPE_1 == 1)
			$sub_cond .= " Z_USER.PK_ROLES = 2 ";
		else if($USER_TYPE_1 == 2)
			$sub_cond .= " (Z_USER.PK_USER_TYPE = 2 AND IS_FACULTY = 0 AND Z_USER.PK_ROLES NOT IN (1,2) ) ";
		else if($USER_TYPE_1 == 3)
			$sub_cond .= " (Z_USER.PK_USER_TYPE = 2 AND IS_FACULTY = 1) ";
		else if($USER_TYPE_1 == 4)
			$sub_cond .= " Z_USER.PK_USER_TYPE = 3 ";
	}
}

if($sub_cond != '')
	$where .= " AND (".$sub_cond.") ";
//echo $where;exit;	
if($SEARCH != '')
	$where .= " AND (USER_ID  like '%$SEARCH%' OR  CONCAT(S_STUDENT_MASTER.LAST_NAME, ', ', S_STUDENT_MASTER.FIRST_NAME) LIKE '%$SEARCH%' OR  CONCAT(S_EMPLOYEE_MASTER.LAST_NAME, ', ', S_EMPLOYEE_MASTER.FIRST_NAME) LIKE '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_LOGIN_HISTORY) FROM Z_LOGIN_HISTORY, Z_ACCOUNT, Z_USER LEFT JOIN Z_USER_TYPE ON Z_USER_TYPE.PK_USER_TYPE = Z_USER.PK_USER_TYPE LEFT JOIN Z_ROLES ON Z_ROLES.PK_ROLES = Z_USER.PK_ROLES LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND Z_USER.PK_USER_TYPE = 3 LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND Z_USER.PK_USER_TYPE = 2 WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_LOGIN_HISTORY,USER_ID,LOGIN_TIME, LOGOUT_TIME, IF(Z_USER.PK_USER_TYPE = 3, USER_TYPE, IF(IS_FACULTY = 1, 'Faculty', ROLES)) AS ROLES, IF(Z_USER.PK_USER_TYPE = 2, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME, ', ', S_EMPLOYEE_MASTER.FIRST_NAME), CONCAT(S_STUDENT_MASTER.LAST_NAME, ', ', S_STUDENT_MASTER.FIRST_NAME)) AS NAME, SCHOOL_NAME FROM Z_LOGIN_HISTORY, Z_ACCOUNT, Z_USER LEFT JOIN Z_USER_TYPE ON Z_USER_TYPE.PK_USER_TYPE = Z_USER.PK_USER_TYPE LEFT JOIN Z_ROLES ON Z_ROLES.PK_ROLES = Z_USER.PK_ROLES LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND Z_USER.PK_USER_TYPE = 3 LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND Z_USER.PK_USER_TYPE = 2 WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$_SESSION['QUERY'] = $query;
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	$row['LOGIN_TIME'] = convert_to_user_date($row['LOGIN_TIME'],'l, M d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
	
	if($row['LOGOUT_TIME'] == '' || $row['LOGOUT_TIME'] == '0000-00-00 00:00:00')
		$row['LOGOUT_TIME'] = 'User did not log out';
	else
		$row['LOGOUT_TIME'] = convert_to_user_date($row['LOGOUT_TIME'],'l, M d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);