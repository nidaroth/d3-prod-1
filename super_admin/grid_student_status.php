<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_STUDENT_STATUS_MASTER';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " 1=1 ";
	
if($SEARCH != '')
	$where .= " AND (STUDENT_STATUS like '%$SEARCH%' OR M_STUDENT_STATUS_MASTER.DESCRIPTION like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_STUDENT_STATUS_MASTER) FROM M_STUDENT_STATUS_MASTER LEFT JOIN M_END_DATE ON M_END_DATE.PK_END_DATE = M_STUDENT_STATUS_MASTER.PK_END_DATE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_STUDENT_STATUS_MASTER,STUDENT_STATUS,M_STUDENT_STATUS_MASTER.DESCRIPTION,CODE,FA_STATUS,IF(ADMISSIONS = 1,'Yes','') AS ADMISSIONS, IF(POST_TUITION = 1,'Yes','') AS POST_TUITION, IF(DOC_28_1 = 1,'Yes','') AS DOC_28_1, IF(CLASS_ENROLLMENT = 1,'Yes','') AS CLASS_ENROLLMENT, IF(ALLOW_ATTENDANCE = 1,'Yes','') AS ALLOW_ATTENDANCE, IF(_1098T = 1,'Yes','') AS _1098T, IF(COMPLETED = 1,'Yes','') AS COMPLETED, IF(M_STUDENT_STATUS_MASTER.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_STUDENT_STATUS_MASTER LEFT JOIN M_END_DATE ON M_END_DATE.PK_END_DATE = M_STUDENT_STATUS_MASTER.PK_END_DATE WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str = '<a href="student_status?id='.$row['PK_STUDENT_STATUS_MASTER'].'" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="javascript:void(0)" onclick="delete_row('.$row['PK_STUDENT_STATUS_MASTER'].')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].'&nbsp;'.$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);