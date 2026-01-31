<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_STUDENT') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'DEPARTMENT ASC, NOTE_TYPE ASC';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " M_NOTE_TYPE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '$_GET[t]' ";
	
if($SEARCH != '')
	$where .= " AND (NOTE_TYPE  like '%$SEARCH%' OR DEPARTMENT like '%$SEARCH%' OR M_NOTE_TYPE.DESCRIPTION like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_NOTE_TYPE) FROM M_NOTE_TYPE LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = M_NOTE_TYPE.PK_DEPARTMENT WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_NOTE_TYPE,NOTE_TYPE, IF(M_NOTE_TYPE.PK_DEPARTMENT = -1 , 'All Departments',DEPARTMENT) AS DEPARTMENT,PK_NOTE_TYPE_MASTER,  IF(M_NOTE_TYPE.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, M_NOTE_TYPE.DESCRIPTION FROM M_NOTE_TYPE LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = M_NOTE_TYPE.PK_DEPARTMENT WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="note_types?id='.$row['PK_NOTE_TYPE'].'&t='.$_GET['t'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	/*$res_check1 = $db->Execute("select PK_STUDENT_NOTES from S_STUDENT_NOTES WHERE PK_NOTE_TYPE = '$row[PK_NOTE_TYPE]' ");
	if($res_check1->RecordCount() == 0  && $row['PK_NOTE_TYPE_MASTER'] == 0) */
	if($row['PK_NOTE_TYPE_MASTER'] == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_NOTE_TYPE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);