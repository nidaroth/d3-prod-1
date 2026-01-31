<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
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

$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'Z_NOTIFICATION.PK_NOTIFICATION';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH 			= isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$PK_EMPLOYEE_MASTER = isset($_REQUEST['PK_EMPLOYEE_MASTER']) ? mysql_real_escape_string($_REQUEST['PK_EMPLOYEE_MASTER']) : '';
$PK_EVENT_TYPE 		= isset($_REQUEST['PK_EVENT_TYPE']) ? mysql_real_escape_string($_REQUEST['PK_EVENT_TYPE']) : '';
$offset 			= ($page-1)*$rows;
	
$result = array();
$where = " NOTIFICATION_TO_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND Z_NOTIFICATION.PK_EVENT_TEMPLATE = S_EVENT_TEMPLATE.PK_EVENT_TEMPLATE AND Z_EVENT_TYPE.PK_EVENT_TYPE = S_EVENT_TEMPLATE.PK_EVENT_TYPE ";
	
if($SEARCH != '')
	$where .= " AND (TEXT  like '%$SEARCH%' )";

$table = "";	
if($PK_EMPLOYEE_MASTER != '') {
	$table .= ",Z_NOTIFICATION_RECIPIENTS ";	
	$where .= " AND Z_NOTIFICATION_RECIPIENTS.PK_NOTIFICATION = Z_NOTIFICATION.PK_NOTIFICATION AND Z_NOTIFICATION_RECIPIENTS.PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ";
}

if($PK_EVENT_TYPE != '') {	
	$where .= " AND Z_EVENT_TYPE.PK_EVENT_TYPE = '$PK_EVENT_TYPE' ";
}

$rs = mysql_query("SELECT DISTINCT(Z_NOTIFICATION.PK_NOTIFICATION) FROM Z_NOTIFICATION,S_EVENT_TEMPLATE, Z_EVENT_TYPE $table WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT DISTINCT(Z_NOTIFICATION.PK_NOTIFICATION),EVENT_TYPE,TEXT,LINK, Z_NOTIFICATION.CREATED_ON FROM Z_NOTIFICATION,S_EVENT_TEMPLATE, Z_EVENT_TYPE $table WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$NOTIFICATION_TO = '';
	$PK_NOTIFICATION = $row['PK_NOTIFICATION'];
	$res1 = mysql_query("SELECT CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME FROM Z_NOTIFICATION_RECIPIENTS,S_EMPLOYEE_MASTER WHERE Z_NOTIFICATION_RECIPIENTS.PK_NOTIFICATION = '$PK_NOTIFICATION' AND Z_NOTIFICATION_RECIPIENTS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER ");
	while($row1 = mysql_fetch_array($res1)){
		if($NOTIFICATION_TO != '')
			$NOTIFICATION_TO .= ', ';
			
		$NOTIFICATION_TO .= $row1['NAME'];
	}
	
	$str  = '&nbsp;<a href="'.$row['LINK'].'" title="'.VIEW.'" target="_blank" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';

	$row['ACTION'] 			= $str;
	$row['NOTIFICATION_TO'] = $NOTIFICATION_TO;
	$row['CREATED_ON'] 		= convert_to_user_date($row['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);