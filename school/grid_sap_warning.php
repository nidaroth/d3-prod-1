<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'SAP_WARNING';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
if( !isset($_POST['sort']) && !isset($_POST['order'])){
	$sort = '';
	$order = ' ACTIVE DESC , SAP_WARNING ASC';
}		
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (SAP_WARNING  like '%$SEARCH%' OR DESCRIPTION like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_SAP_WARNING) FROM S_SAP_WARNING WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_SAP_WARNING,SAP_WARNING,DESCRIPTION, IF(ACTIVE = 1,'Yes','No') AS ACTIVE FROM S_SAP_WARNING WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="sap_warning?id='.$row['PK_SAP_WARNING'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_SAP_WARNING'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	$row['ACTION'] = $str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);