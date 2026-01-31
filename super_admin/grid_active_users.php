<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'DATE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$PK_ACCOUNT = isset($_REQUEST['PK_ACCOUNT']) ? mysql_real_escape_string($_REQUEST['PK_ACCOUNT']) : '';
$START_DATE = isset($_REQUEST['START_DATE']) ? mysql_real_escape_string($_REQUEST['START_DATE']) : '';
$TO_DATE    = isset($_REQUEST['TO_DATE']) ? mysql_real_escape_string($_REQUEST['TO_DATE']) : '';
$USER_TYPE  = isset($_REQUEST['USER_TYPE']) ? mysql_real_escape_string($_REQUEST['USER_TYPE']) : '';

$offset = ($page-1)*$rows;
	
$result = array();
$where = " 1=1 ";

if($START_DATE != '' && $TO_DATE != '') {
	$ST = date("Y-m-d",strtotime($START_DATE));
	$ET = date("Y-m-d",strtotime($TO_DATE));
	$where .= " AND DATE BETWEEN '$ST' AND '$ET' ";
} else if($START_DATE != ''){
	$ST = date("Y-m-d",strtotime($START_DATE));
	$where .= " AND DATE >= '$ST' ";
} else if($TO_DATE != ''){
	$ET = date("Y-m-d",strtotime($TO_DATE));
	$where .= " AND DATE <= '$ET' ";
}

if($PK_ACCOUNT != '')
	$where .= " AND Z_ACTIVE_USERS.PK_ACCOUNT = '$PK_ACCOUNT' ";

if($USER_TYPE != '')
	$where .= " AND USER_TYPE  = '$USER_TYPE' ";

$rs = mysql_query("SELECT DISTINCT(PK_ACTIVE_USERS) FROM Z_ACTIVE_USERS LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_ACTIVE_USERS.PK_ACCOUNT WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_ACTIVE_USERS,DATE,CONCAT(LAST_NAME,' ',FIRST_NAME) as NAME, IF(USER_TYPE = 1, 'School User',IF(USER_TYPE = 2, 'Faculty',IF(USER_TYPE = 3, 'Student',''))) as USER_TYPE, SCHOOL_NAME FROM Z_ACTIVE_USERS LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_ACTIVE_USERS.PK_ACCOUNT WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	if($row['DATE'] != '0000-00-00')
		$row['DATE'] = date("m/d/Y",strtotime($row['DATE']));
	else
		$row['DATE'] = '';
		
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);