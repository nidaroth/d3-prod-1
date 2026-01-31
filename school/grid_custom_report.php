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
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : ' REPORT_NAME ASC';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_CUSTOM_REPORT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (REPORT_NAME like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_CUSTOM_REPORT) 
FROM S_CUSTOM_REPORT 
LEFT JOIN Z_USER as CREATED_USER ON S_CUSTOM_REPORT.CREATED_BY = CREATED_USER.PK_USER 
LEFT JOIN S_EMPLOYEE_MASTER as CREATED_EMP ON CREATED_EMP.PK_EMPLOYEE_MASTER = CREATED_USER.ID 

LEFT JOIN Z_USER as EDITED_USER ON S_CUSTOM_REPORT.EDITED_BY = EDITED_USER.PK_USER 
LEFT JOIN S_EMPLOYEE_MASTER as EDITED_EMP ON EDITED_EMP.PK_EMPLOYEE_MASTER = EDITED_USER.ID 
WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_CUSTOM_REPORT, REPORT_NAME, S_CUSTOM_REPORT.CREATED_ON, S_CUSTOM_REPORT.EDITED_ON, CONCAT(CREATED_EMP.LAST_NAME,', ', CREATED_EMP.FIRST_NAME) AS CREATED_BY_NAME, CONCAT(EDITED_EMP.LAST_NAME,', ', EDITED_EMP.FIRST_NAME) AS LAST_EDITED_BY  
FROM S_CUSTOM_REPORT 
LEFT JOIN Z_USER as CREATED_USER ON S_CUSTOM_REPORT.CREATED_BY = CREATED_USER.PK_USER 
LEFT JOIN S_EMPLOYEE_MASTER as CREATED_EMP ON CREATED_EMP.PK_EMPLOYEE_MASTER = CREATED_USER.ID 

LEFT JOIN Z_USER as EDITED_USER ON S_CUSTOM_REPORT.EDITED_BY = EDITED_USER.PK_USER 
LEFT JOIN S_EMPLOYEE_MASTER as EDITED_EMP ON EDITED_EMP.PK_EMPLOYEE_MASTER = EDITED_USER.ID 

WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	if($row['CREATED_ON'] != '0000-00-00 00:00:00' )
		$row['CREATED_ON'] = convert_to_user_date($row['CREATED_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
	else
		$row['CREATED_ON'] = '';
		
	if($row['EDITED_ON'] != '0000-00-00 00:00:00' )
		$row['EDITED_ON'] = convert_to_user_date($row['EDITED_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
	else
		$row['EDITED_ON'] = '';

	$str  = '&nbsp;<a href="custom_report?id='.$row['PK_CUSTOM_REPORT'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="custom_report_excel?id='.$row['PK_CUSTOM_REPORT'].'" title="'.EXCEL.'" class="btn btn-info btn-circle"><i class="mdi mdi-file-excel-box"></i></a>';
	$str .= '&nbsp;<a href="custom_report_pdf?id='.$row['PK_CUSTOM_REPORT'].'" title="'.PDF.'" class="btn btn-info btn-circle"><i class="mdi mdi-file-pdf-box"></i></a>';
	$str .= '&nbsp;<a href="custom_report?id='.$row['PK_CUSTOM_REPORT'].'&duplicate=1" title="'.DUPLICATE.'" class="btn pdf-color btn-circle"><i class="fas fa-copy"></i></a>';
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_CUSTOM_REPORT'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);