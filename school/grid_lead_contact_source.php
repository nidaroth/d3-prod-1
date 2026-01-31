<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_ADMISSION') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'LEAD_CONTACT_SOURCE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (LEAD_CONTACT_SOURCE like '%$SEARCH%' OR DESCRIPTION like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_LEAD_CONTACT_SOURCE) FROM M_LEAD_CONTACT_SOURCE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_LEAD_CONTACT_SOURCE,PK_LEAD_CONTACT_SOURCE_MASTER,LEAD_CONTACT_SOURCE, DESCRIPTION, IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_LEAD_CONTACT_SOURCE WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str  = '&nbsp;<a href="lead_contact_source?id='.$row['PK_LEAD_CONTACT_SOURCE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';

	$res_check1 = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_LEAD_CONTACT_SOURCE = '$row[PK_LEAD_CONTACT_SOURCE]' "); //Ticket # 1762 
	if($res_check1->RecordCount() == 0  && $row['PK_LEAD_CONTACT_SOURCE_MASTER'] == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_LEAD_CONTACT_SOURCE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);