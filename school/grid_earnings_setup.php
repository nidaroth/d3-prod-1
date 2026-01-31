<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_EARNINGS_SETUP';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " ";

$rs = mysql_query("SELECT DISTINCT(PK_EARNINGS_SETUP) FROM S_EARNINGS_SETUP WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_EARNINGS_SETUP, PK_EARNING_TYPE, EXCLUDED_PK_CAMPUS_PROGRAM, EXCLUDED_PK_STUDENT_STATUS, INCLUDED_PK_AR_LEDGER_CODE, IF(IGNORE_FUTURE_TUITION = 1, 'Yes', 'No') as IGNORE_FUTURE_TUITION, IF(PRORATE_FIRST_MONTH = 1, 'Yes', 'No') as PRORATE_FIRST_MONTH, IF(PRORATE_LOA_STATUS = 1, 'Yes', 'No') as PRORATE_LOA_STATUS, IF(PRORATE_BREAKS = 1, 'Yes', 'No') as PRORATE_BREAKS, IF(PRORATE_CLOSURES = 1, 'Yes', 'No') as PRORATE_CLOSURES, IF(PRORATE_HOLIDAYS = 1, 'Yes', 'No') as PRORATE_HOLIDAYS, IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM S_EARNINGS_SETUP WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	$res = $db->Execute("SELECT GROUP_CONCAT(CAMPUS_CODE ORDER BY CAMPUS_CODE SEPARATOR ', ') as CAMPUS FROM S_CAMPUS, S_EARNINGS_SETUP_CAMPUS WHERE S_EARNINGS_SETUP_CAMPUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EARNINGS_SETUP_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_EARNINGS_SETUP = '$row[PK_EARNINGS_SETUP]' "); 
	$row['CAMPUS'] = $res->fields['CAMPUS'];

	$res = $db->Execute("SELECT EARNING_TYPE as EARNING_TYPE FROM M_EARNING_TYPE WHERE PK_EARNING_TYPE = $row[PK_EARNING_TYPE] "); 
	$row['EARNING_TYPE'] = $res->fields['EARNING_TYPE'];
	
	$res = $db->Execute("SELECT GROUP_CONCAT(STUDENT_STATUS SEPARATOR ', ') as STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS IN ($row[EXCLUDED_PK_STUDENT_STATUS]) AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$row['EXCLUDED_STUDENT_STATUS'] = $res->fields['STUDENT_STATUS'];
	
	$res = $db->Execute("SELECT GROUP_CONCAT(CODE SEPARATOR ', ') as PROGRAMS FROM M_CAMPUS_PROGRAM WHERE PK_CAMPUS_PROGRAM IN ($row[EXCLUDED_PK_CAMPUS_PROGRAM]) AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$row['EXCLUDED_PROGRAMS'] = $res->fields['PROGRAMS'];
	
	$res = $db->Execute("SELECT GROUP_CONCAT(CODE SEPARATOR ', ') as LEDGER_CODE FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE IN ($row[INCLUDED_PK_AR_LEDGER_CODE]) AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$row['INCLUDED_FEE_LEDGER_CODES'] = $res->fields['LEDGER_CODE'];

	$str  = '&nbsp;<a href="earnings_setup?id='.$row['PK_EARNINGS_SETUP'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_EARNINGS_SETUP'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);