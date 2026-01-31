<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'M_STUDENT_GROUP.ACTIVE DESC, STUDENT_GROUP ASC ';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " M_STUDENT_GROUP.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (STUDENT_GROUP  like '%$SEARCH%' OR CODE  like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_STUDENT_GROUP) FROM M_STUDENT_GROUP LEFT JOIN M_CAMPUS_PROGRAM on M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = M_STUDENT_GROUP.PK_CAMPUS_PROGRAM WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_STUDENT_GROUP,STUDENT_GROUP,CODE, IF(M_STUDENT_GROUP.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE_1 FROM M_STUDENT_GROUP LEFT JOIN M_CAMPUS_PROGRAM on M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = M_STUDENT_GROUP.PK_CAMPUS_PROGRAM WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="student_group?id='.$row['PK_STUDENT_GROUP'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';

	/*$res_check1 = $db->Execute("select PK_ENROLL_MANDATE_FIELDS from S_ENROLL_MANDATE_FIELDS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GROUP = '$row[PK_STUDENT_GROUP]' ");
	$res_check2 = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GROUP = '$row[PK_STUDENT_GROUP]' ");
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 ) */
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_STUDENT_GROUP'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	$row['ACTION'] = $row['ACTIVE_1'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);