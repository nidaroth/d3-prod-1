<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 2 ){ 
	header("location:../index.php");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'LEAD_CONTACT_STATUS';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (LEAD_CONTACT_STATUS  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_LEAD_CONTACT_STATUS) FROM M_LEAD_CONTACT_STATUS WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_LEAD_CONTACT_STATUS,PK_LEAD_CONTACT_STATUS_MASTER,LEAD_CONTACT_STATUS, IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_LEAD_CONTACT_STATUS WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str  = '&nbsp;<a href="lead_contact_status.php?id='.$row['PK_LEAD_CONTACT_STATUS'].'" title="'.EDIT.'" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>';
	
	if($row['PK_LEAD_CONTACT_STATUS_MASTER'] == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_LEAD_CONTACT_STATUS'].')" title="'.DELETE.'" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i></a>';

	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);