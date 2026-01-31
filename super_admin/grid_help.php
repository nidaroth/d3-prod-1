<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'HELP_CATEGORY ASC, HELP_SUB_CATEGORY ASC, Z_HELP.DISPLAY_ORDER ASC';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " 1=1 ";
	
if($SEARCH != '')
	$where .= " AND (NAME_ENG  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_HELP) FROM Z_HELP LEFT JOIN M_HELP_CATEGORY ON M_HELP_CATEGORY.PK_HELP_CATEGORY = Z_HELP.PK_HELP_CATEGORY LEFT JOIN M_HELP_SUB_CATEGORY ON M_HELP_SUB_CATEGORY.PK_HELP_SUB_CATEGORY = Z_HELP.PK_HELP_SUB_CATEGORY WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_HELP,NAME_ENG,HELP_CATEGORY, HELP_SUB_CATEGORY, IF(Z_HELP.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, Z_HELP.DISPLAY_ORDER FROM Z_HELP LEFT JOIN M_HELP_CATEGORY ON M_HELP_CATEGORY.PK_HELP_CATEGORY = Z_HELP.PK_HELP_CATEGORY LEFT JOIN M_HELP_SUB_CATEGORY ON M_HELP_SUB_CATEGORY.PK_HELP_SUB_CATEGORY = Z_HELP.PK_HELP_SUB_CATEGORY WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str = '<a href="help?id='.$row['PK_HELP'].'" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="javascript:void(0)" onclick="delete_row('.$row['PK_HELP'].')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].'&nbsp;'.$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);