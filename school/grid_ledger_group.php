<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");
 

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'LEDGER_CODE_GROUP';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (LEDGER_CODE_GROUP  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_LEDGER_CODE_GROUP) FROM S_LEDGER_CODE_GROUP WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_LEDGER_CODE_GROUP,LEDGER_CODE_GROUP,LEDGER_CODE_GROUP_DESC,PK_AR_LEDGER_CODES, IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM S_LEDGER_CODE_GROUP WHERE " . $where ." order by $sort $order" ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="ledger_code_group?id='.$row['PK_LEDGER_CODE_GROUP'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_LEDGER_CODE_GROUP'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	$curr_PK_AR_LEDGER_CODES = $row['PK_AR_LEDGER_CODES'] ?? 0;
	$sub_query = $db->Execute("SELECT GROUP_CONCAT(CODE) AS CODES FROM M_AR_LEDGER_CODE WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE IN ($curr_PK_AR_LEDGER_CODES)");
	$row['LEDGER_CODE_GROUP_CODES'] = $sub_query->fields['CODES'];
	$row['ACTION'] = $row['ACTIVE'].$str;
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);