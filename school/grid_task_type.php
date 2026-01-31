<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_TASK_MANAGEMENT') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'TASK_TYPE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " M_TASK_TYPE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (TASK_TYPE like '%$SEARCH%' OR M_TASK_TYPE.DESCRIPTION like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_TASK_TYPE) FROM M_TASK_TYPE LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = M_TASK_TYPE.PK_DEPARTMENT WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_TASK_TYPE,PK_TASK_TYPE_MASTER, IF(M_TASK_TYPE.PK_DEPARTMENT = -1 , 'All Departments',DEPARTMENT) AS DEPARTMENT, TASK_TYPE, IF(M_TASK_TYPE.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, M_TASK_TYPE.DESCRIPTION FROM M_TASK_TYPE LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = M_TASK_TYPE.PK_DEPARTMENT WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str  = '&nbsp;<a href="task_type?id='.$row['PK_TASK_TYPE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	/*$res_check1 = $db->Execute("select PK_EVENT_TEMPLATE from S_EVENT_TEMPLATE WHERE  PK_TASK_TYPE = '$row[PK_TASK_TYPE]' LIMIT 0,1 ");
	$res_check2 = $db->Execute("select PK_STUDENT_OTHER_EDU from S_STUDENT_OTHER_EDU WHERE  PK_TASK_TYPE = '$row[PK_TASK_TYPE]' LIMIT 0,1  ");
	$res_check3 = $db->Execute("select PK_STUDENT_TASK from S_STUDENT_TASK WHERE  PK_TASK_TYPE = $row[PK_TASK_TYPE] LIMIT 0,1  ");
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $row['PK_TASK_TYPE_MASTER'] == 0)*/
	if($row['PK_TASK_TYPE_MASTER'] == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_TASK_TYPE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);