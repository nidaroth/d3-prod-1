<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");
$res_add_on = $db->Execute("SELECT ECM FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_TITLE_IV_SERVICER') == 0 || $res_add_on->fields['ECM'] == 0){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'ECM_LEDGER';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " M_ECM_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (ECM_LEDGER like '%$SEARCH%' OR ECM_LEDGER.DESCRIPTION like '%$SEARCH%' OR CODE like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_ECM_LEDGER) FROM M_ECM_LEDGER 
LEFT JOIN M_AWARD_YEAR ON M_AWARD_YEAR.PK_AWARD_YEAR = M_ECM_LEDGER.PK_AWARD_YEAR 
LEFT JOIN M_ECM_LEDGER_MASTER ON M_ECM_LEDGER_MASTER.PK_ECM_LEDGER_MASTER = M_ECM_LEDGER.PK_ECM_LEDGER_MASTER 
LEFT JOIN M_ECM_LEDGER_TYPE_MASTER ON M_ECM_LEDGER_TYPE_MASTER.PK_ECM_LEDGER_TYPE_MASTER = M_ECM_LEDGER_MASTER.PK_ECM_LEDGER_TYPE_MASTER 
LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = M_ECM_LEDGER.PK_AR_LEDGER_CODE 
WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_ECM_LEDGER,ECM_LEDGER,M_ECM_LEDGER.PK_ECM_LEDGER_MASTER, M_ECM_LEDGER.DESCRIPTION, CODE, ECM_LEDGER_TYPE, AWARD_YEAR, ECM_LEDGER_TYPE, IF(M_ECM_LEDGER.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM 
M_ECM_LEDGER 
LEFT JOIN M_AWARD_YEAR ON M_AWARD_YEAR.PK_AWARD_YEAR = M_ECM_LEDGER.PK_AWARD_YEAR 
LEFT JOIN M_ECM_LEDGER_MASTER ON M_ECM_LEDGER_MASTER.PK_ECM_LEDGER_MASTER = M_ECM_LEDGER.PK_ECM_LEDGER_MASTER 
LEFT JOIN M_ECM_LEDGER_TYPE_MASTER ON M_ECM_LEDGER_TYPE_MASTER.PK_ECM_LEDGER_TYPE_MASTER = M_ECM_LEDGER_MASTER.PK_ECM_LEDGER_TYPE_MASTER 
LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = M_ECM_LEDGER.PK_AR_LEDGER_CODE
 WHERE  " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str = '<a href="ecm_ledger?id='.$row['PK_ECM_LEDGER'].'" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>';
	
	if($row['PK_ECM_LEDGER_MASTER'] == 0) 
		$str .= '&nbsp;<a href="javascript:void(0)" onclick="delete_row('.$row['PK_ECM_LEDGER'].')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].'&nbsp;'.$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);