<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PLACEMENT_COMPANY_QUESTION_GROUP';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (PLACEMENT_COMPANY_QUESTION_GROUP  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_PLACEMENT_COMPANY_QUESTION_GROUP) FROM M_PLACEMENT_COMPANY_QUESTION_GROUP WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_PLACEMENT_COMPANY_QUESTION_GROUP,PLACEMENT_COMPANY_QUESTION_GROUP, IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_PLACEMENT_COMPANY_QUESTION_GROUP WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="placement_company_question_group?id='.$row['PK_PLACEMENT_COMPANY_QUESTION_GROUP'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_PLACEMENT_COMPANY_QUESTIONNAIRE from M_PLACEMENT_COMPANY_QUESTIONNAIRE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PLACEMENT_COMPANY_QUESTION_GROUP = '$row[PK_PLACEMENT_COMPANY_QUESTION_GROUP]' ");
	$res_check2 = $db->Execute("select PK_COMPANY from S_COMPANY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PLACEMENT_COMPANY_QUESTION_GROUP = '$row[PK_PLACEMENT_COMPANY_QUESTION_GROUP]' ");

	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 )
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_PLACEMENT_COMPANY_QUESTION_GROUP'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);