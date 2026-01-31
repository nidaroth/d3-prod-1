<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");
if(check_access('SETUP_ACCOUNTING') == 0 && check_access('SETUP_FINANCE') == 0 ){
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
$where = " M_TITLE_IV_RECIPIENTS_CATEGORY.ACTIVE = '1' ";
	
if($SEARCH != '')
	$where .= " AND (CODE like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_TITLE_IV_RECIPIENTS_CATEGORY) FROM M_TITLE_IV_RECIPIENTS_CATEGORY WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_TITLE_IV_RECIPIENTS_CATEGORY,CODE FROM M_TITLE_IV_RECIPIENTS_CATEGORY WHERE  " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$LEDGER_CODES = '';
	$rs1 = mysql_query("SELECT CODE FROM M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER, M_AR_LEDGER_CODE WHERE PK_TITLE_IV_RECIPIENTS_CATEGORY = '".$row['PK_TITLE_IV_RECIPIENTS_CATEGORY']."' AND M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ")or die(mysql_error());
	while($row1 = mysql_fetch_array($rs1)){
		if($LEDGER_CODES != '')
			$LEDGER_CODES .= ", ";
		$LEDGER_CODES .= $row1['CODE'];
	}
	
	$row['LEDGER_CODES'] = $LEDGER_CODES;
	$str = '<a href="title_iv_recipients_by_category_setup?id='.$row['PK_TITLE_IV_RECIPIENTS_CATEGORY'].'" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>';

	$row['ACTION'] = $row['ACTIVE'].'&nbsp;'.$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);