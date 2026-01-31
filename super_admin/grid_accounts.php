<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'ACTIVE ASC, SCHOOL_NAME ASC';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT != 1 ";
	
if($SEARCH != '')
	$where .= " AND (SCHOOL_NAME  like '%$SEARCH%' OR PHONE like '%$SEARCH%' OR EMAIL like '%$SEARCH%' OR CITY like '%$SEARCH%' OR STATE_CODE like '%$SEARCH%' OR WEBSITE like '%$SEARCH%' OR STUD_CODE like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_ACCOUNT) FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_ACCOUNT,SCHOOL_NAME, PHONE, EMAIL, STUD_CODE, CITY, STATE_CODE, WEBSITE , IF(Z_ACCOUNT.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str  = '&nbsp;<a href="accounts?id='.$row['PK_ACCOUNT'].'" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>';
	//$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_ACCOUNT'].')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i></a>';
	$str .= '&nbsp;<a target="_blank" href="school_login?id='.$row['PK_ACCOUNT'].'" title="Login" class="btn btn-info btn-circle"><i class="mdi mdi-login-variant"></i></a>';
	
	
	$row['ACTION'] = $row['ACTIVE'].'&nbsp;'.$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);