<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['_90_10'] == 0 || check_access('MANAGEMENT_90_10') == 0){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'M_AR_LEDGER_CODE.ACTIVE DESC,CODE ASC';   //DIAM-1680
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
$LEDGER_CODE_SATATUS = isset($_REQUEST['LEDGER_CODE_SATATUS']) ? mysql_real_escape_string($_REQUEST['LEDGER_CODE_SATATUS']) : '2'; //DIAM-1680

	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '1' ";
	
if($SEARCH != '')
	$where .= " AND (LEDGER_DESCRIPTION  like '%$SEARCH%' OR CODE like '%$SEARCH%' OR CATEGORY_NAME like '%$SEARCH%')";
//DIAM-1680
	if($LEDGER_CODE_SATATUS == 0) {		
		$where .= " AND M_AR_LEDGER_CODE.ACTIVE = 0 ";
	}else if($LEDGER_CODE_SATATUS == 1){
		$where .= " AND M_AR_LEDGER_CODE.ACTIVE = 1 ";
	}	
//DIAM-1680
$rs = mysql_query("SELECT DISTINCT(PK_AR_LEDGER_CODE) FROM M_AR_LEDGER_CODE LEFT JOIN Z_90_10_CATEGORY ON Z_90_10_CATEGORY.PK_90_10_CATEGORY = M_AR_LEDGER_CODE.PK_90_10_CATEGORY WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
//DIAM-1680	
$query = "SELECT PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION, IF(M_AR_LEDGER_CODE.PK_90_10_CATEGORY = 0, 'Not Set', CATEGORY_NAME) as CATEGORY_NAME,IF(M_AR_LEDGER_CODE.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE_1 FROM M_AR_LEDGER_CODE LEFT JOIN Z_90_10_CATEGORY ON Z_90_10_CATEGORY.PK_90_10_CATEGORY = M_AR_LEDGER_CODE.PK_90_10_CATEGORY WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	

$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="90_10_setup?id='.$row['PK_AR_LEDGER_CODE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);
