<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : ' EMPLOYEE_NOTE_TYPE ASC';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " M_EMPLOYEE_NOTE_TYPE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (EMPLOYEE_NOTE_TYPE  like '%$SEARCH%' OR DESCRIPTION like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_EMPLOYEE_NOTE_TYPE) FROM M_EMPLOYEE_NOTE_TYPE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_EMPLOYEE_NOTE_TYPE,EMPLOYEE_NOTE_TYPE,DESCRIPTION, PK_EMPLOYEE_NOTE_TYPE_MASTER,  IF(M_EMPLOYEE_NOTE_TYPE.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_EMPLOYEE_NOTE_TYPE WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$PK_EMPLOYEE_NOTE_TYPE = $row['PK_EMPLOYEE_NOTE_TYPE'];
	$str  = '&nbsp;<a href="employee_note_types?id='.$row['PK_EMPLOYEE_NOTE_TYPE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_EMPLOYEE_NOTES from S_EMPLOYEE_NOTES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_NOTE_TYPE = '$PK_EMPLOYEE_NOTE_TYPE' ");
	if($res_check1->RecordCount() == 0 && $row['PK_EMPLOYEE_NOTE_TYPE_MASTER'] == 0 )
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_EMPLOYEE_NOTE_TYPE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);