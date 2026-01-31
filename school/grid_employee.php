<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : "S_EMPLOYEE_MASTER.ACTIVE DESC, CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH 			 = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$SHOW_AVAILABLE_ONLY = isset($_REQUEST['SHOW_AVAILABLE_ONLY']) ? mysql_real_escape_string($_REQUEST['SHOW_AVAILABLE_ONLY']) : '';
$SHOW_ACTIVE_ONLY	 = isset($_REQUEST['SHOW_ACTIVE_ONLY']) ? mysql_real_escape_string($_REQUEST['SHOW_ACTIVE_ONLY']) : '';

/* Ticket # 1353 */
$HAS_LOGIN	 	= isset($_REQUEST['HAS_LOGIN']) ? mysql_real_escape_string($_REQUEST['HAS_LOGIN']) : '';
$IS_FACULTY	 	= isset($_REQUEST['IS_FACULTY']) ? mysql_real_escape_string($_REQUEST['IS_FACULTY']) : '';
$SCHOOL_ADMIN	= isset($_REQUEST['SCHOOL_ADMIN']) ? mysql_real_escape_string($_REQUEST['SCHOOL_ADMIN']) : '';
/* Ticket # 1353 */

$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_CONTACT.PK_EMPLOYEE_MASTER ";

/* if($_GET['t'] == 1)
	$where .= " AND IS_FACULTY = 0 ";
else if($_GET['t'] == 2)
	$where .= " AND IS_FACULTY = 1 "; */

$table = "";
if($_SESSION['PK_ROLES'] == 3) {
	$table  = ", S_EMPLOYEE_CAMPUS";
	$where .= " AND S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND S_EMPLOYEE_CAMPUS.PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) ";
}

if($SEARCH != '')
	$where .= " AND (EMPLOYEE_ID like '%$SEARCH%' OR COMPANY_EMP_ID like '%$SEARCH%' OR CELL_PHONE like '%$SEARCH%' OR EMAIL like '%$SEARCH%' OR FULL_PART_TIME like '%$SEARCH%' OR CONCAT(LAST_NAME,', ',FIRST_NAME) like '%$SEARCH%' OR LAST_NAME like '%$SEARCH%' OR FIRST_NAME like '%$SEARCH%' OR TITLE like '%$SEARCH%' OR DEPARTMENT like '%$SEARCH%' )";
	
if($SHOW_AVAILABLE_ONLY == 1)
	$where .= " AND TURN_OFF_ASSIGNMENTS = 0 ";
else if($SHOW_AVAILABLE_ONLY == 2)
	$where .= " AND TURN_OFF_ASSIGNMENTS = 1 ";
	
if($SHOW_ACTIVE_ONLY == 1)
	$where .= " AND S_EMPLOYEE_MASTER.ACTIVE = 1 ";
else if($SHOW_ACTIVE_ONLY == 2)
	$where .= " AND S_EMPLOYEE_MASTER.ACTIVE = 0 ";
	
/* Ticket # 1353 */
if($HAS_LOGIN == 1)
	$where .= " AND S_EMPLOYEE_MASTER.LOGIN_CREATED = 1 ";
else if($HAS_LOGIN == 2)
	$where .= " AND S_EMPLOYEE_MASTER.LOGIN_CREATED = 0 ";
	
if($IS_FACULTY == 1)
	$where .= " AND S_EMPLOYEE_MASTER.IS_FACULTY = 1 ";
else if($IS_FACULTY == 2)
	$where .= " AND S_EMPLOYEE_MASTER.IS_FACULTY = 0 ";
	
if($SCHOOL_ADMIN == 1)
	$where .= " AND S_EMPLOYEE_MASTER.IS_ADMIN = 1 ";
else if($SCHOOL_ADMIN == 2)
	$where .= " AND S_EMPLOYEE_MASTER.IS_ADMIN = 0 ";
/* Ticket # 1353 */

$rs = mysql_query("SELECT DISTINCT(S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER) FROM 
S_EMPLOYEE_MASTER 
LEFT JOIN S_EMPLOYEE_DEPARTMENT ON S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT 
,S_EMPLOYEE_CONTACT $table WHERE " . $where. " GROUP BY S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);

/* Ticket # 1353 */
$query = "SELECT S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, EMPLOYEE_ID, COMPANY_EMP_ID, CELL_PHONE, EMAIL, IF(TURN_OFF_ASSIGNMENTS = 1,'No','Yes') AS TURN_OFF_ASSIGNMENTS, IF(S_EMPLOYEE_MASTER.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE_1, IF(LOGIN_CREATED = 1,'Yes','No') as LOGIN_CREATED_1, LOGIN_CREATED, IF(IS_FACULTY = 1, 'Yes','No') as IS_FACULTY_1, IF(IS_ADMIN = 1, 'Yes','No') as IS_ADMIN_1, GROUP_CONCAT(DEPARTMENT separator ', ') as DEPARTMENT, TITLE 
FROM 
S_EMPLOYEE_MASTER 
LEFT JOIN S_EMPLOYEE_DEPARTMENT ON S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT 
,S_EMPLOYEE_CONTACT $table WHERE " . $where ." GROUP BY S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER order by $sort $order " ;
/* Ticket # 1353 */

// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$CAMPUS = "";
	$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_CAMPUS, S_EMPLOYEE_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS  AND PK_EMPLOYEE_MASTER = '".$row['PK_EMPLOYEE_MASTER']."' ORDER BY CAMPUS_CODE ASC "); //Ticket # 1608
	while (!$res_campus->EOF) { 
		if($CAMPUS != '')
			$CAMPUS .= ', ';
			
		$CAMPUS .= $res_campus->fields['CAMPUS_CODE'];
		$res_campus->MoveNext();
	}
	
	/*$DEPARTMENT = "";
	$res_department = $db->Execute("SELECT DEPARTMENT FROM M_DEPARTMENT, S_EMPLOYEE_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT  AND PK_EMPLOYEE_MASTER = '".$row['PK_EMPLOYEE_MASTER']."' ");
	while (!$res_department->EOF) { 
		if($DEPARTMENT != '')
			$DEPARTMENT .= ', ';
			
		$DEPARTMENT .= $res_department->fields['DEPARTMENT'];
		$res_department->MoveNext();
	}*/

	$str  = '&nbsp;<a href="employee?id='.$row['PK_EMPLOYEE_MASTER'].'&t='.$_GET['t'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';

	if($_SESSION['ADMIN_PK_USER'] > 0 && $row['LOGIN_CREATED'] == 1){
		$str .= '&nbsp;<a href="employee_login?id='.$row['PK_EMPLOYEE_MASTER'].'" title="Login" class="btn btn-info btn-circle"><i class="mdi mdi-login-variant"></i></a>';
	}
	
	/*$PK_EMPLOYEE_MASTER = $row['PK_EMPLOYEE_MASTER'];
	$res_check1 = $db->Execute("select PK_CUSTOM_REPORT from S_CUSTOM_REPORT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
	$res_check2 = $db->Execute("select PK_EVENT_TEMPLATE_RECIPIENTS from S_EVENT_TEMPLATE_RECIPIENTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
	$res_check3 = $db->Execute("select PK_INSTRUCTOR_STUDENT from S_INSTRUCTOR_STUDENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
	$res_check4 = $db->Execute("select PK_NOTIFICATION_SETTINGS_DETAIL from S_NOTIFICATION_SETTINGS_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
	$res_check5 = $db->Execute("select PK_STUDENT_DOCUMENTS from S_STUDENT_DOCUMENTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
	$res_check6 = $db->Execute("select PK_STUDENT_TASK from S_STUDENT_TASK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
	$res_check7 = $db->Execute("select PK_ANNOUNCEMENT_EMPLOYEE from Z_ANNOUNCEMENT_EMPLOYEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
	$res_check8 = $db->Execute("select PK_NOTIFICATION_RECIPIENTS from Z_NOTIFICATION_RECIPIENTS WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
	$res_check9 = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE INSTRUCTOR = '$PK_EMPLOYEE_MASTER' ");
	$res_check10 = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING_ASSISTANT WHERE ASSISTANT = '$PK_EMPLOYEE_MASTER' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0 && $res_check6->RecordCount() == 0 && $res_check7->RecordCount() == 0 && $res_check8->RecordCount() == 0 && $res_check9->RecordCount() == 0 && $res_check10->RecordCount() == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_EMPLOYEE_MASTER'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	*/
	$row['ACTION'] 		= $row['ACTIVE_1'].$str;
	$row['CAMPUS'] 		= $CAMPUS;
	//$row['DEPARTMENT'] 	= $DEPARTMENT;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);