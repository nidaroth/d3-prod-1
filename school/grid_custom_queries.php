<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT CUSTOM_QUERIES FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if ($res_add_on->fields['CUSTOM_QUERIES'] == 0 || check_access('MANAGEMENT_CUSTOM_QUERY') == 0) {
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'CUSTOM_NAME';
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';

$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page - 1) * $rows;

$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_CUSTOM_QUERY.PK_CUSTOM_QUERY = M_CUSTOM_QUERY_ACCOUNT.PK_CUSTOM_QUERY  ";

if ($SEARCH != '')
	$where .= " AND (EXTERNAL_DESCRIPTION  like '%$SEARCH%' OR CUSTOM_NAME like '%$SEARCH%' )";

$rs = mysql_query("SELECT PK_CUSTOM_QUERY_ACCOUNT FROM M_CUSTOM_QUERY_ACCOUNT, M_CUSTOM_QUERY WHERE " . $where . " ") or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);

$query = "SELECT PK_CUSTOM_QUERY_ACCOUNT,CUSTOM_NAME,EXTERNAL_DESCRIPTION FROM M_CUSTOM_QUERY_ACCOUNT, M_CUSTOM_QUERY WHERE " . $where . " order by $sort $order ";
// echo $query;exit;	

$rs = mysql_query($query . " limit $offset,$rows") or die(mysql_error());

$items = array();
while ($row = mysql_fetch_array($rs)) {

	$str   = '<a href="custom_queries?id=' . $row['PK_CUSTOM_QUERY_ACCOUNT'] . '" title="Data View" class="btn edit-color btn-circle"><i class="fas fa-newspaper"></i> </a>';
	$str  .= '&nbsp;<a href="custom_queries_excel?id=' . $row['PK_CUSTOM_QUERY_ACCOUNT'] . '" title="Excel" class="btn pdf-color btn-circle"><i class="fas fa-file-excel"></i> </a>';

	$row['ACTION'] = $str;

	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);
