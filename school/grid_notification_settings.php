<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'EVENT_TYPE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_EVENT_TEMPLATE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND Z_EVENT_TYPE.PK_EVENT_TYPE = S_EVENT_TEMPLATE.PK_EVENT_TYPE ";
	
if($SEARCH != '')
	$where .= " AND (EVENT_TYPE  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_EVENT_TEMPLATE) FROM S_EVENT_TEMPLATE, Z_EVENT_TYPE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_EVENT_TEMPLATE,EVENT_TYPE, IF(S_EVENT_TEMPLATE.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM S_EVENT_TEMPLATE, Z_EVENT_TYPE WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$CAMPUS = "";
	$res_campus = $db->Execute("SELECT OFFICIAL_CAMPUS_NAME FROM S_CAMPUS, S_EVENT_TEMPLATE_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_EVENT_TEMPLATE_CAMPUS.PK_CAMPUS  AND PK_EVENT_TEMPLATE = '".$row['PK_EVENT_TEMPLATE']."' ");
	while (!$res_campus->EOF) { 
		if($CAMPUS != '')
			$CAMPUS .= ', ';
			
		$CAMPUS .= $res_campus->fields['OFFICIAL_CAMPUS_NAME'];
		$res_campus->MoveNext();
	}
	$row['CAMPUS'] = $CAMPUS;
	
	$str  = '&nbsp;<a href="notification_settings?id='.$row['PK_EVENT_TEMPLATE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_NOTIFICATION from Z_NOTIFICATION WHERE PK_EVENT_TEMPLATE = '$row[PK_EVENT_TEMPLATE]' ");
	if($res_check1->RecordCount() == 0  )
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_EVENT_TEMPLATE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);