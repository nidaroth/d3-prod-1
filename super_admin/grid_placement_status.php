<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_PLACEMENT_STATUS_MASTER';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " 1=1 ";
	
if($SEARCH != '')
	$where .= " AND (PLACEMENT_STATUS  like '%$SEARCH%' OR PLACEMENT_STUDENT_STATUS_CATEGORY like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_PLACEMENT_STATUS_MASTER) FROM M_PLACEMENT_STATUS_MASTER LEFT JOIN M_PLACEMENT_STUDENT_STATUS_CATEGORY ON M_PLACEMENT_STUDENT_STATUS_CATEGORY.PK_PLACEMENT_STUDENT_STATUS_CATEGORY = M_PLACEMENT_STATUS_MASTER.PK_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_PLACEMENT_STATUS_MASTER,PLACEMENT_STATUS,PLACEMENT_STUDENT_STATUS_CATEGORY, IF(EMPLOYED = 1,'Yes','No') as EMPLOYED,IF(M_PLACEMENT_STATUS_MASTER.ACTIVE = 1,'Yes','No') AS ACTIVE, IF(M_PLACEMENT_STATUS_MASTER.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE_1 FROM M_PLACEMENT_STATUS_MASTER LEFT JOIN M_PLACEMENT_STUDENT_STATUS_CATEGORY ON M_PLACEMENT_STUDENT_STATUS_CATEGORY.PK_PLACEMENT_STUDENT_STATUS_CATEGORY = M_PLACEMENT_STATUS_MASTER.PK_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str = '<a href="placement_status?id='.$row['PK_PLACEMENT_STATUS_MASTER'].'" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="javascript:void(0)" onclick="delete_row('.$row['PK_PLACEMENT_STATUS_MASTER'].')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE_1'].'&nbsp;'.$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);