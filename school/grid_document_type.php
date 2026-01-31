<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'DOCUMENT_TYPE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " M_DOCUMENT_TYPE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (DOCUMENT_TYPE  like '%$SEARCH%' OR CODE like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_DOCUMENT_TYPE) FROM M_DOCUMENT_TYPE LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = M_DOCUMENT_TYPE.PK_DEPARTMENT WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_DOCUMENT_TYPE,DOCUMENT_TYPE,CODE,PK_DOCUMENT_TYPE_MASTER, IF(M_DOCUMENT_TYPE.PK_DEPARTMENT = -1 , 'All Departments',DEPARTMENT) AS DEPARTMENT,  IF(M_DOCUMENT_TYPE.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_DOCUMENT_TYPE LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = M_DOCUMENT_TYPE.PK_DEPARTMENT WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="document_type?id='.$row['PK_DOCUMENT_TYPE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_STUDENT_DOCUMENTS from S_STUDENT_DOCUMENTS WHERE PK_DOCUMENT_TYPE = '$row[PK_DOCUMENT_TYPE]' ");
	if($res_check1->RecordCount() == 0  && $row['PK_DOCUMENT_TYPE_MASTER'] == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_DOCUMENT_TYPE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);