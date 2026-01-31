<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'GRADE_BOOK_TYPE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (GRADE_BOOK_TYPE  like '%$SEARCH%' OR DESCRIPTION like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_GRADE_BOOK_TYPE) FROM M_GRADE_BOOK_TYPE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_GRADE_BOOK_TYPE,GRADE_BOOK_TYPE,DESCRIPTION,PK_GRADE_BOOK_TYPE_MASTER,  IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_GRADE_BOOK_TYPE WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="grade_book_type?id='.$row['PK_GRADE_BOOK_TYPE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_COURSE_GRADE_BOOK from S_COURSE_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_BOOK_TYPE = '$row[PK_GRADE_BOOK_TYPE]' ");
	$res_check2 = $db->Execute("select PK_COURSE_OFFERING_GRADE from S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_BOOK_TYPE = '$row[PK_GRADE_BOOK_TYPE]' ");
	$res_check3 = $db->Execute("select PK_PROGRAM_GRADE_BOOK from S_PROGRAM_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_BOOK_TYPE = '$row[PK_GRADE_BOOK_TYPE]' ");
	$res_check4 = $db->Execute("select PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT from S_STUDENT_PROGRAM_GRADE_BOOK_INPUT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_BOOK_TYPE = '$row[PK_GRADE_BOOK_TYPE]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $row['PK_GRADE_BOOK_TYPE_MASTER'] == 0) 
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_GRADE_BOOK_TYPE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);