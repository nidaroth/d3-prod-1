<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PLACEMENT_STATUS';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (PLACEMENT_STATUS  like '%$SEARCH%' OR PLACEMENT_STUDENT_STATUS_CATEGORY  like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_PLACEMENT_STATUS) FROM M_PLACEMENT_STATUS LEFT JOIN M_PLACEMENT_STUDENT_STATUS_CATEGORY ON M_PLACEMENT_STUDENT_STATUS_CATEGORY.PK_PLACEMENT_STUDENT_STATUS_CATEGORY = M_PLACEMENT_STATUS.PK_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_PLACEMENT_STATUS,PLACEMENT_STATUS,PLACEMENT_STUDENT_STATUS_CATEGORY, IF(EMPLOYED = 1,'Yes','No') as EMPLOYED, IF(M_PLACEMENT_STATUS.ACTIVE = 1,'Yes','No') as ACTIVE , PK_PLACEMENT_STATUS_MASTER, IF(M_PLACEMENT_STATUS.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE_1 FROM M_PLACEMENT_STATUS LEFT JOIN M_PLACEMENT_STUDENT_STATUS_CATEGORY ON M_PLACEMENT_STUDENT_STATUS_CATEGORY.PK_PLACEMENT_STUDENT_STATUS_CATEGORY = M_PLACEMENT_STATUS.PK_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="placement_status?id='.$row['PK_PLACEMENT_STATUS'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PLACEMENT_STATUS = '$row[PK_PLACEMENT_STATUS]' ");
	$res_check2 = $db->Execute("select PK_STUDENT_JOB from S_STUDENT_JOB WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PLACEMENT_STATUS = '$row[PK_PLACEMENT_STATUS]' ");
	$res_check3 = $db->Execute("select PK_STUDENT_WAIVER from S_STUDENT_WAIVER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PLACEMENT_STATUS = '$row[PK_PLACEMENT_STATUS]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $row['PK_PLACEMENT_STATUS_MASTER'] == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_PLACEMENT_STATUS'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	
	$row['ACTION'] = $row['ACTIVE_1'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);