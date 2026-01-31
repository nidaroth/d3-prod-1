<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'S_COURSE.ACTIVE DESC, COURSE_CODE ASC';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();

$where = " S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (COURSE_CODE like '%$SEARCH%' OR COURSE_DESCRIPTION LIKE '%$SEARCH%' OR TRANSCRIPT_CODE LIKE '%$SEARCH%' OR UNITS LIKE '%$SEARCH%' OR FA_UNITS LIKE '%$SEARCH%' OR HOURS LIKE '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(S_COURSE.PK_COURSE) FROM S_COURSE LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_COURSE.PK_ATTENDANCE_CODE LEFT JOIN S_COURSE_CAMPUS ON S_COURSE_CAMPUS.PK_COURSE = S_COURSE.PK_COURSE LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_CAMPUS.PK_CAMPUS WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT S_COURSE.PK_COURSE,COURSE_CODE,COURSE_DESCRIPTION, TRANSCRIPT_CODE, GROUP_CONCAT(CAMPUS_CODE SEPARATOR ', ') as CAMPUS_CODE, UNITS,FA_UNITS,HOURS, IF(S_COURSE.ACTIVE = 1,'Y','N') as ACTIVE_1,MAX_CLASS_SIZE, CODE, IF(S_COURSE.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE_2 FROM S_COURSE LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_COURSE.PK_ATTENDANCE_CODE LEFT JOIN S_COURSE_CAMPUS ON S_COURSE_CAMPUS.PK_COURSE = S_COURSE.PK_COURSE LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_CAMPUS.PK_CAMPUS WHERE " . $where  ;
// echo $query;exit;
$_SESSION['query'] = $query." GROUP BY PK_COURSE ";
$rs = mysql_query($query." GROUP BY PK_COURSE order by $sort $order limit $offset,$rows")or die(mysql_error());	

$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str  = '&nbsp;<a href="course?id='.$row['PK_COURSE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	//$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_COURSE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE_2'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);