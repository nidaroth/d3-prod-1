<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'DEFAULT_STATUS';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (DEFAULT_STATUS  like '%$SEARCH%' OR DESCRIPTION  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_DEFAULT_STATUS) FROM S_DEFAULT_STATUS WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_DEFAULT_STATUS,DEFAULT_STATUS,DESCRIPTION,   IF(ACTIVE = 1,'Yes','No') AS ACTIVE, IF(IN_DEFAULT = 1,'Yes','No') AS IN_DEFAULT FROM S_DEFAULT_STATUS WHERE " . $where ." order by $sort $order " ;
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)) {

	$str  = '&nbsp;<a href="default_status?id='.$row['PK_DEFAULT_STATUS'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_DEFAULT_STATUS'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);