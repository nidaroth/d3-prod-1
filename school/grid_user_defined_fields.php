<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_USER_DEFINED_FIELDS';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_USER_DEFINED_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (NAME  like '%$SEARCH%' OR DATA_TYPES  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_USER_DEFINED_FIELDS) FROM S_USER_DEFINED_FIELDS LEFT JOIN M_DATA_TYPES ON M_DATA_TYPES.PK_DATA_TYPES = S_USER_DEFINED_FIELDS.PK_DATA_TYPES WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_USER_DEFINED_FIELDS,NAME,DATA_TYPES, IF(S_USER_DEFINED_FIELDS.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM S_USER_DEFINED_FIELDS LEFT JOIN M_DATA_TYPES ON M_DATA_TYPES.PK_DATA_TYPES = S_USER_DEFINED_FIELDS.PK_DATA_TYPES WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="user_defined_fields?id='.$row['PK_USER_DEFINED_FIELDS'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_CUSTOM_FIELDS from S_CUSTOM_FIELDS WHERE PK_USER_DEFINED_FIELDS = '$row[PK_USER_DEFINED_FIELDS]' AND ACTIVE = 1");
	if($res_check1->RecordCount() == 0  )
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_USER_DEFINED_FIELDS'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);