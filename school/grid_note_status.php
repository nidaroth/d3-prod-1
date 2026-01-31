<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 && check_access('SETUP_STUDENT') == 0){
	header("location:../index");
	exit;
}


$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'NOTE_STATUS';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " M_NOTE_STATUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '$_GET[t]' ";
	
if($SEARCH != '')
	$where .= " AND (NOTE_STATUS  like '%$SEARCH%' OR DEPARTMENT  like '%$SEARCH%' )";

/*$rs = mysql_query("SELECT DISTINCT(PK_NOTE_STATUS) FROM M_NOTE_STATUS LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = M_NOTE_STATUS.PK_DEPARTMENT  WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);*/

$rs = mysql_query("SELECT COUNT(DISTINCT(PK_NOTE_STATUS)) AS cnt FROM M_NOTE_STATUS  WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_assoc($rs);
$result["total"] = $row['cnt'];

$query = "SELECT PK_NOTE_STATUS,NOTE_STATUS,PK_NOTE_STATUS_MASTER, IF(M_NOTE_STATUS.PK_DEPARTMENT = -1 , 'All Departments',DEPARTMENT) AS DEPARTMENT, IF(M_NOTE_STATUS.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_NOTE_STATUS LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = M_NOTE_STATUS.PK_DEPARTMENT WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	$PK_NOTE_STATUS = $row['PK_NOTE_STATUS'];
	$str  = '&nbsp;<a href="note_status?id='.$row['PK_NOTE_STATUS'].'&t='.$_GET['t'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$res_check2=0;
	$rec_check1 = mysql_query("SELECT PK_EMPLOYEE_NOTES FROM S_EMPLOYEE_NOTES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_NOTE_STATUS = '$PK_NOTE_STATUS' LIMIT 1 ");
	$res_check1 = mysql_num_rows($rec_check1);
	if ( $res_check1 == 0 ){
		$rec_check2_notes = mysql_query("SELECT PK_STUDENT_NOTES FROM S_STUDENT_NOTES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_NOTE_STATUS = '$PK_NOTE_STATUS' LIMIT 1 ");
		$res_check2 = mysql_num_rows($rec_check2_notes);
	}
	if($res_check1 ==0 && $res_check2== 0 && $row['PK_NOTE_STATUS_MASTER'] == 0 )
	{
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_NOTE_STATUS'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	}
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);