<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
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
$where = " M_GRADE_BOOK_CODE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (CODE  like '%$SEARCH%' OR M_GRADE_BOOK_CODE.DESCRIPTION like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_GRADE_BOOK_CODE) FROM M_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_CODE.PK_GRADE_BOOK_TYPE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_GRADE_BOOK_CODE,CODE,M_GRADE_BOOK_CODE.DESCRIPTION,HOUR, SESSIONS, POINTS, GRADE_BOOK_TYPE,  IF(M_GRADE_BOOK_CODE.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_CODE.PK_GRADE_BOOK_TYPE WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="grade_book_code?id='.$row['PK_GRADE_BOOK_CODE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_PROGRAM_GRADE_BOOK from S_PROGRAM_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_BOOK_CODE = '$row[PK_GRADE_BOOK_CODE]' ");
	$res_check2 = $db->Execute("select PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT from S_STUDENT_PROGRAM_GRADE_BOOK_INPUT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_BOOK_CODE = '$row[PK_GRADE_BOOK_CODE]' ");
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $row['PK_GRADE_BOOK_CODE_MASTER'] == 0) 
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_GRADE_BOOK_CODE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);