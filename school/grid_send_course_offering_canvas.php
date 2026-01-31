<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

$res = $db->Execute("SELECT ENABLE_CANVAS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ENABLE_CANVAS'] == 0) {
	header("location:../index");
	exit;
}

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'S_TERM_MASTER.BEGIN_DATE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$PK_COURSE_OFFERING = isset($_REQUEST['PK_COURSE_OFFERING']) ? ($_REQUEST['PK_COURSE_OFFERING']) : '';
$PK_TERM_MASTER 	= isset($_REQUEST['PK_TERM_MASTER']) ? ($_REQUEST['PK_TERM_MASTER']) : '';
$PK_CAMPUS 			= isset($_REQUEST['PK_CAMPUS']) ? ($_REQUEST['PK_CAMPUS']) : '';
$SEARCH 			= isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';

$offset = ($page-1)*$rows;
	
$result = array();

$where = " S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.LMS_ACTIVE = 1 AND S_TERM_MASTER.LMS_ACTIVE = 1 ";
	
if($SEARCH != '')
	$where .= " AND (COURSE_CODE like '%$SEARCH%' OR CAMPUS_CODE LIKE '%$SEARCH%' OR CONCAT(EMP_INSTRUCTOR.FIRST_NAME,' ',EMP_INSTRUCTOR.LAST_NAME) LIKE '%$SEARCH%' OR SESSION LIKE '%$SEARCH%' OR SESSION_NO LIKE '%$SEARCH%' )";

if(!empty($PK_COURSE_OFFERING) != '')
	$where .= " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN (".implode(",",$PK_COURSE_OFFERING).") ";
	
if(!empty($PK_TERM_MASTER) != '')
	$where .= " AND S_COURSE_OFFERING.PK_TERM_MASTER IN (".implode(",",$PK_TERM_MASTER).") ";

if(!empty($PK_CAMPUS) != '')
	$where .= " AND S_COURSE_OFFERING.PK_CAMPUS IN (".implode(",",$PK_CAMPUS).") ";

$rs = mysql_query("SELECT DISTINCT(PK_COURSE_OFFERING) FROM S_COURSE_OFFERING LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING,COURSE_CODE, LMS_CODE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d'),'') AS TERM_BEGIN_DATE, CAMPUS_CODE ,CONCAT(EMP_INSTRUCTOR.FIRST_NAME,' ',EMP_INSTRUCTOR.LAST_NAME) AS INSTRUCTOR_NAME,CONCAT(SESSION,' - ',SESSION_NO) as SESSION , CONCAT(ROOM_NO,' - ', ROOM_DESCRIPTION) AS ROOM_NO, IF(S_COURSE_OFFERING.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, COURSE_OFFERING_STATUS FROM S_COURSE_OFFERING LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM  LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR WHERE " . $where ." order by $sort $order " ;
//echo $query;exit;	
$_SESSION['query'] = $query;
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$PK_COURSE_OFFERING = $row['PK_COURSE_OFFERING'];
	$res1 = $db->Execute("SELECT SUCCESS, S_COURSE_OFFERING_CANVAS.CREATED_ON, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME,MESSAGE FROM S_COURSE_OFFERING_CANVAS LEFT JOIN Z_USER ON Z_USER.PK_USER = S_COURSE_OFFERING_CANVAS.CREATED_BY AND PK_USER_TYPE IN (1,2) LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE  PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ORDER BY PK_COURSE_OFFERING_CANVAS DESC ");	
	
	if($res1->RecordCount() == 0){
		$row['SENT'] 		= 'N';
		$row['SENT_ON'] 	= '';
		$row['SENT_BY'] 	= '';
		$row['MESSAGE'] 	= '';
	} else {
		//if($res1->fields['SUCCESS'] == 1)
			$row['SENT'] = 'Y';
		/*else
			$row['SENT'] = 'N';*/
			
		$row['SENT_ON'] = convert_to_user_date($res1->fields['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
		$row['SENT_BY'] = $res1->fields['NAME'];
		$row['MESSAGE'] = $res1->fields['MESSAGE'];
	}

	$row['ACTION'] = '<input type="checkbox" checked name="PK_COURSE_OFFERING[]" id="PK_COURSE_OFFERING_'.$row['PK_COURSE_OFFERING'].'" value="'.$row['PK_COURSE_OFFERING'].'" onclick="show_btn()" >';
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);