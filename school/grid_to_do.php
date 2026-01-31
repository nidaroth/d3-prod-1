<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_TO_DO_LIST';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_USER = '$_SESSION[PK_USER]'  ";
	
if($SEARCH != '')
	$where .= " AND (HEADER  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_TO_DO_LIST) FROM S_TO_DO_LIST WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_TO_DO_LIST,HEADER, CONTENT, DATE, IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, IF(COMPLETED = 1,'Yes', '') as COMPLETED FROM S_TO_DO_LIST WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	if($row['DATE'] != '0000-00-00')
		$row['DATE'] = date("m/d/Y",strtotime($row['DATE']));
	else
		$row['DATE'] = '';

	$str  = '&nbsp;<a href="to_do?id='.$row['PK_TO_DO_LIST'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_TO_DO_LIST'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);