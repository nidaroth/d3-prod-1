<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
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

$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_FINAL_GRADE_IMPORT';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_FINAL_GRADE_IMPORT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_FINAL_GRADE_IMPORT.PK_FINAL_GRADE_IMPORT = S_FINAL_GRADE_IMPORT_DETAIL.PK_FINAL_GRADE_IMPORT ";
	
if($SEARCH != '')
	$where .= " AND (FILE_NAME  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(S_FINAL_GRADE_IMPORT.PK_FINAL_GRADE_IMPORT) FROM S_FINAL_GRADE_IMPORT LEFT JOIN Z_USER ON Z_USER.PK_USER = S_FINAL_GRADE_IMPORT.UPLOADED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE = 2, S_FINAL_GRADE_IMPORT_DETAIL WHERE " . $where. "  GROUP BY S_FINAL_GRADE_IMPORT.PK_FINAL_GRADE_IMPORT  ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT S_FINAL_GRADE_IMPORT.PK_FINAL_GRADE_IMPORT, S_FINAL_GRADE_IMPORT.POSTED, FILE_NAME, IF(MATCH_BY = 1, 'Course Offering', IF(MATCH_BY = 2, 'External ID', '')) AS MATCH_BY_1, MATCH_BY, UPLOADED_ON, CONCAT(LAST_NAME,', ',FIRST_NAME) as UPLOADED_BY_NAME FROM S_FINAL_GRADE_IMPORT LEFT JOIN Z_USER ON Z_USER.PK_USER = S_FINAL_GRADE_IMPORT.UPLOADED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2), S_FINAL_GRADE_IMPORT_DETAIL WHERE " . $where ." GROUP BY S_FINAL_GRADE_IMPORT.PK_FINAL_GRADE_IMPORT order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	$row['UPLOADED_ON'] = convert_to_user_date($row['UPLOADED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
	
	if($row['POSTED'] == 1)
		$row['POSTED'] = 'Posted';
	else
		$row['POSTED'] = 'Unposted';
		
	if($row['POSTED'] == 'Unposted') {
		foreach($row as $key => $val) {
			if($key != "ACTION" && $key != "PK_FINAL_GRADE_IMPORT" && $key != "MATCH_BY" )
				$row[$key] = "<span style='color:red'>".$val."</span>";
		}
	}	
	
	$str  = '&nbsp;<a href="time_clock_result?id='.$row['PK_FINAL_GRADE_IMPORT'].'&t='.$row['MATCH_BY'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';

	$row['ACTION'] = $str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);