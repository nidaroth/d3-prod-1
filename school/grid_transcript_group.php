<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_STUDENT') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : ' TRANSCRIPT_GROUP_SORT_ORDER';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " M_TRANSCRIPT_GROUP.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (TRANSCRIPT_GROUP like '%$SEARCH%' OR DESCRIPTION like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_TRANSCRIPT_GROUP) FROM M_TRANSCRIPT_GROUP WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_TRANSCRIPT_GROUP,TRANSCRIPT_GROUP,DESCRIPTION, IF(M_TRANSCRIPT_GROUP.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, IF(WEIGHTED = 1, 'Yes', 'No') as WEIGHTED, TRANSCRIPT_GROUP_SORT_ORDER, IF(TRANSCRIPT_DETAIL_SORT_ORDER_TYPE = 1, 'Course Code', IF(TRANSCRIPT_DETAIL_SORT_ORDER_TYPE = 2, 'Term', '') ) as TRANSCRIPT_DETAIL_SORT_ORDER_TYPE FROM M_TRANSCRIPT_GROUP WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="transcript_group?id='.$row['PK_TRANSCRIPT_GROUP'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_TRANSCRIPT_GROUP'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);