<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}
$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
$TIMEZONE = $res->fields['TIMEZONE'];

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : ' FILTER_NAME ASC';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_SELECT_STUDENT_FILTER.PK_USER = '$_SESSION[PK_USER]'  AND S_SELECT_STUDENT_FILTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'";
	
if($SEARCH != '')
	$where .= " AND (FILTER_NAME like '%$SEARCH%' )";

	// echo "SELECT DISTINCT(PK_SELECT_STUDENT_FILTER) FROM S_SELECT_STUDENT_FILTER WHERE " . $where. " ";
$rs = mysql_query("SELECT DISTINCT(PK_SELECT_STUDENT_FILTER) FROM S_SELECT_STUDENT_FILTER WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_SELECT_STUDENT_FILTER,FILTER_NAME,S_SELECT_STUDENT_FILTER.CREATED_ON AS CREATED_ON ,S_SELECT_STUDENT_FILTER.EDITED_ON AS EDITED_ON,
CONCAT(CREATED_EMP.LAST_NAME,', ', CREATED_EMP.FIRST_NAME) AS CREATED_BY_NAME, CONCAT(EDITED_EMP.LAST_NAME,', ', EDITED_EMP.FIRST_NAME) AS LAST_EDITED_BY  
FROM S_SELECT_STUDENT_FILTER 
LEFT JOIN Z_USER as CREATED_USER ON S_SELECT_STUDENT_FILTER.CREATED_BY = CREATED_USER.PK_USER 
LEFT JOIN S_EMPLOYEE_MASTER as CREATED_EMP ON CREATED_EMP.PK_EMPLOYEE_MASTER = CREATED_USER.ID 

LEFT JOIN Z_USER as EDITED_USER ON S_SELECT_STUDENT_FILTER.EDITED_BY = EDITED_USER.PK_USER 
LEFT JOIN S_EMPLOYEE_MASTER as EDITED_EMP ON EDITED_EMP.PK_EMPLOYEE_MASTER = EDITED_USER.ID 


WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
// print_r($row);exit;
	$str  = '&nbsp;<a href="student_report_selection?id='.$row['PK_SELECT_STUDENT_FILTER'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_SELECT_STUDENT_FILTER'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	if($row['CREATED_ON'] != '0000-00-00 00:00:00' )
		$row['CREATED_ON'] = convert_to_user_date($row['CREATED_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
	else
		$row['CREATED_ON'] = '';
		
	if($row['EDITED_ON'] != '0000-00-00 00:00:00' )
		$row['EDITED_ON'] = convert_to_user_date($row['EDITED_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
	else
		$row['EDITED_ON'] = '';

	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);