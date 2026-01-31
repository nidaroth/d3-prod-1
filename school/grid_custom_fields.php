<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : "FIELD(SECTION,2,1,3), FIELD_NAME ASC";  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (FIELD_NAME  like '%$SEARCH%' OR TAB  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_CUSTOM_FIELDS) FROM S_CUSTOM_FIELDS WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_CUSTOM_FIELDS,FIELD_NAME, IF(TAB = 'Other','Enrollment',TAB) as TAB, IF(SECTION = 1, 'Student',IF(SECTION = 2, 'Employee', IF(SECTION = 3, 'Teacher',''))) AS SECTION, IF(S_CUSTOM_FIELDS.PK_DEPARTMENT = -1, 'All Modules', DEPARTMENT) as DEPARTMENT, IF(S_CUSTOM_FIELDS.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM S_CUSTOM_FIELDS LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_CUSTOM_FIELDS.PK_DEPARTMENT WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$PK_CUSTOM_FIELDS = $row['PK_CUSTOM_FIELDS'];
	$str  = '&nbsp;<a href="custom_fields?id='.$row['PK_CUSTOM_FIELDS'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_EMPLOYEE_CUSTOM_FIELDS from S_EMPLOYEE_CUSTOM_FIELDS WHERE PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS='$PK_CUSTOM_FIELDS' ");
	$res_check2 = $db->Execute("select PK_STUDENT_CUSTOM_FIELDS from S_STUDENT_CUSTOM_FIELDS WHERE PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS='$PK_CUSTOM_FIELDS' ");
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_CUSTOM_FIELDS'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);