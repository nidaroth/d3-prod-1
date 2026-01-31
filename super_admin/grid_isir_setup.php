<? require_once("../global/config.php"); 
require_once("../language/common.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'FROM_NAME';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " 1=1 ";
	
if($SEARCH != '')
	$where .= " AND (FROM_NAME  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_ISIR_SETUP_MASTER) FROM Z_ISIR_SETUP_MASTER WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_ISIR_SETUP_MASTER,YEAR_INDICATION,FROM_NAME,IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM Z_ISIR_SETUP_MASTER WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="isir_setup?id='.$row['PK_ISIR_SETUP_MASTER'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="isir_setup?id='.$row['PK_ISIR_SETUP_MASTER'].'&copy=1" title="'.COPY.'" class="btn edit-color btn-circle"><i class="far fa-copy"></i> </a>';
	$str .= '&nbsp;<a href="isir_setup_excel?id='.$row['PK_ISIR_SETUP_MASTER'].'&copy=1" title="Export" class="btn edit-color btn-circle"><i class="fas fa-download"></i> </a>';
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_ISIR_SETUP_MASTER'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);