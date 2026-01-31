<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_DEFAULT_COHORT_YEAR';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";

	
if($SEARCH != '')
	$where .= " AND (DEFAULT_COHORT_YEAR  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_DEFAULT_COHORT_YEAR) FROM S_DEFAULT_COHORT_YEAR WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_DEFAULT_COHORT_YEAR,DEFAULT_COHORT_YEAR,BEGIN_DATE,END_DATE, IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE,IF(ACTIVE = 1,'Yes','No') as ACTIVE_STATUS FROM S_DEFAULT_COHORT_YEAR WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	if($row['BEGIN_DATE'] != '0000-00-00')
		$row['BEGIN_DATE'] = date("m/d/Y",strtotime($row['BEGIN_DATE']));
		
	if($row['END_DATE'] != '0000-00-00')
		$row['END_DATE'] = date("m/d/Y",strtotime($row['END_DATE']));
	
	$str = '<a href="default_cohort_year?id='.$row['PK_DEFAULT_COHORT_YEAR'].'" title="Edit" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="javascript:void(0)" onclick="delete_row('.$row['PK_DEFAULT_COHORT_YEAR'].')" title="Delete" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTIVE_STATUS'] = $row['ACTIVE_STATUS'];
	$row['ACTION'] = $row['ACTIVE'].'&nbsp;'.$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);
