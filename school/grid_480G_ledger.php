<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT _4807G FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if(check_access('MANAGEMENT_ACCOUNTING') == 0 || $res_add_on->fields['_4807G'] == 0){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'CODE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '1' ";
	
if($SEARCH != '')
	$where .= " AND (LEDGER_DESCRIPTION  like '%$SEARCH%' OR CODE like '%$SEARCH%' OR FINANCIAL_ASSISTANCE_TYPE like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_AR_LEDGER_CODE) FROM M_AR_LEDGER_CODE LEFT JOIN _4807G_CATEGORY_FINANCIAL_ASSISTANCE_TYPE ON _4807G_CATEGORY_FINANCIAL_ASSISTANCE_TYPE.PK_FINANCIAL_ASSISTANCE_TYPE_4807G = M_AR_LEDGER_CODE.PK_FINANCIAL_ASSISTANCE_TYPE_4807G WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION, IF(INCLUDE_IN_REPORTING_4807G = 1, 'Yes', 'No') as INCLUDE_IN_REPORTING_4807G,FINANCIAL_ASSISTANCE_TYPE FROM M_AR_LEDGER_CODE LEFT JOIN _4807G_CATEGORY_FINANCIAL_ASSISTANCE_TYPE ON _4807G_CATEGORY_FINANCIAL_ASSISTANCE_TYPE.PK_FINANCIAL_ASSISTANCE_TYPE_4807G = M_AR_LEDGER_CODE.PK_FINANCIAL_ASSISTANCE_TYPE_4807G WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	

$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="480G_ledger?id='.$row['PK_AR_LEDGER_CODE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);