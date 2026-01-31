<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : $_SESSION['PAGE'];
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : $_SESSION['rows'];
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : $_SESSION['SORT_FIELD'];  
$order = isset($_POST['order']) ? strval($_POST['order']) : $_SESSION['SORT_ORDER'];

$_SESSION['rows'] 		= $rows;
$_SESSION['PAGE'] 		= $page;
$_SESSION['SORT_FIELD'] = $sort;
$_SESSION['SORT_ORDER'] = $order;
				
$PK_COURSE 		= isset($_REQUEST['PK_COURSE']) ? mysql_real_escape_string($_REQUEST['PK_COURSE']) : $_SESSION['SRC_PK_COURSE'];
$PK_TERM_MASTER = isset($_REQUEST['PK_TERM_MASTER']) ? mysql_real_escape_string($_REQUEST['PK_TERM_MASTER']) : $_SESSION['SRC_PK_TERM_MASTER'];
$PK_CAMPUS 		= isset($_REQUEST['PK_CAMPUS']) ? mysql_real_escape_string($_REQUEST['PK_CAMPUS']) : $_SESSION['SRC_PK_CAMPUS'];
$INSTRUCTOR 	= isset($_REQUEST['INSTRUCTOR']) ? mysql_real_escape_string($_REQUEST['INSTRUCTOR']) : $_SESSION['SRC_INSTRUCTOR'];
$SEARCH 		= isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : $_SESSION['SRC_SEARCH'];
$PK_SESSION		= isset($_REQUEST['PK_SESSION']) ? mysql_real_escape_string($_REQUEST['PK_SESSION']) : $_SESSION['SRC_PK_SESSION'];
$PK_CAMPUS_ROOM = isset($_REQUEST['PK_CAMPUS_ROOM']) ? mysql_real_escape_string($_REQUEST['PK_CAMPUS_ROOM']) : $_SESSION['SRC_PK_CAMPUS_ROOM'];



//589
$TREM_BEGIN_START_DATE = isset($_REQUEST['TREM_BEGIN_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_START_DATE']) : $_SESSION['TREM_BEGIN_START_DATE'];

$TREM_BEGIN_END_DATE = isset($_REQUEST['TREM_BEGIN_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_END_DATE']) : $_SESSION['TREM_BEGIN_END_DATE'];

$TREM_END_START_DATE = isset($_REQUEST['TREM_END_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_START_DATE']) : $_SESSION['TREM_END_START_DATE'];

$TREM_END_END_DATE = isset($_REQUEST['TREM_END_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_END_DATE']) : $_SESSION['TREM_END_END_DATE'];



$offset = ($page-1)*$rows;
	
$result = array();

$where = " S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '') {
	$where .= " AND (COURSE_CODE like '%$SEARCH%' OR OFFICIAL_CAMPUS_NAME LIKE '%$SEARCH%' OR CONCAT(EMP_INSTRUCTOR.FIRST_NAME,' ',EMP_INSTRUCTOR.LAST_NAME) LIKE '%$SEARCH%' OR SESSION LIKE '%$SEARCH%' OR SESSION_NO LIKE '%$SEARCH%' OR CONCAT(ROOM_NO,' - ', ROOM_DESCRIPTION) LIKE '%$SEARCH%' OR LMS_CODE LIKE '%$SEARCH%' OR TRANSCRIPT_CODE LIKE '%$SEARCH%' )";
	$_SESSION['SRC_SEARCH'] = $SEARCH;
} else {
	$_SESSION['SRC_SEARCH'] = '';
}
	
if($PK_COURSE != '') {
	$where .= " AND S_COURSE_OFFERING.PK_COURSE = '$PK_COURSE' ";
	$_SESSION['SRC_PK_COURSE'] = $PK_COURSE;
} else {
	$_SESSION['SRC_PK_COURSE'] = '';
}
//589	
if($TREM_BEGIN_START_DATE != '' && $TREM_BEGIN_END_DATE != '' ) {
	$TREM_BEGIN_START_DATE=date('Y-m-d',strtotime($TREM_BEGIN_START_DATE));
	$TREM_BEGIN_END_DATE=date('Y-m-d',strtotime($TREM_BEGIN_END_DATE));
	$where .= " AND S_TERM_MASTER.BEGIN_DATE >= '$TREM_BEGIN_START_DATE' AND S_TERM_MASTER.BEGIN_DATE <= '$TREM_BEGIN_END_DATE' ";
}

if($TREM_END_START_DATE != '' && $TREM_END_END_DATE != '' ) {
	$TREM_END_START_DATE=date('Y-m-d',strtotime($TREM_END_START_DATE));
	$TREM_END_END_DATE=date('Y-m-d',strtotime($TREM_END_END_DATE));
	$where .= " AND S_TERM_MASTER.END_DATE >= '$TREM_END_START_DATE' AND S_TERM_MASTER.END_DATE <= '$TREM_END_END_DATE' ";
} 
//589

if($PK_TERM_MASTER != '') {
	$where .= " AND S_COURSE_OFFERING.PK_TERM_MASTER = '$PK_TERM_MASTER' ";
	$_SESSION['SRC_PK_TERM_MASTER'] = $PK_TERM_MASTER;
} else {
	$_SESSION['SRC_PK_TERM_MASTER'] = '';
}
	
if($PK_CAMPUS != '') {
	$where .= " AND S_COURSE_OFFERING.PK_CAMPUS = '$PK_CAMPUS' ";
	$_SESSION['SRC_PK_CAMPUS'] = $PK_CAMPUS;
} else {
	$_SESSION['SRC_PK_CAMPUS'] = '';
}

if($PK_CAMPUS_ROOM != '') {
	if($PK_CAMPUS_ROOM == -1)
		$where .= " AND S_COURSE_OFFERING.PK_CAMPUS_ROOM = 0 ";
	else
		$where .= " AND S_COURSE_OFFERING.PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' ";
	$_SESSION['SRC_PK_CAMPUS_ROOM'] = $PK_CAMPUS_ROOM;
} else {
	$_SESSION['SRC_PK_CAMPUS_ROOM'] = '';
}
	
if($INSTRUCTOR != '') {
	if($INSTRUCTOR == -1)
		$where .= " AND S_COURSE_OFFERING.INSTRUCTOR = 0 ";
	else
		$where .= " AND S_COURSE_OFFERING.INSTRUCTOR = '$INSTRUCTOR' ";
	$_SESSION['SRC_INSTRUCTOR'] = $INSTRUCTOR;
} else {
	$_SESSION['SRC_INSTRUCTOR'] = '';
}
	
if($PK_SESSION != '') {
	$where .= " AND S_COURSE_OFFERING.PK_SESSION = '$PK_SESSION' ";
	$_SESSION['SRC_PK_SESSION'] = $PK_SESSION;
} else {
	$_SESSION['SRC_PK_SESSION'] = '';
}

$rs = mysql_query("SELECT DISTINCT(PK_COURSE_OFFERING) FROM S_COURSE_OFFERING LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR WHERE " . $where. " ")or die(mysql_error());
//$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);


$query = "SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING
			,COURSE_CODE
			, LMS_CODE
			, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE
			,CAMPUS_CODE
			,CONCAT(EMP_INSTRUCTOR.LAST_NAME,', ',EMP_INSTRUCTOR.FIRST_NAME) AS INSTRUCTOR_NAME
			,SESSION,SESSION_NO, ROOM_NO, S_COURSE_OFFERING.ROOM_SIZE
			,S_COURSE_OFFERING.CLASS_SIZE
			,IF(S_COURSE_OFFERING.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE
			,IF(S_COURSE_OFFERING.LMS_ACTIVE = 1, 'Yes', 'No') as LMS_ACTIVE
			,COALESCE(sqSC.C,0) AS NO_STUDENT
			,COURSE_OFFERING_STATUS, S_COURSE_OFFERING.INSTRUCTOR, S_COURSE_OFFERING.PK_CAMPUS, TRANSCRIPT_CODE
			FROM S_COURSE_OFFERING
			LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS
			LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM
			LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE
			LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER
			LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
			LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS
			LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR
			LEFT JOIN (SELECT PK_COURSE_OFFERING, COUNT(*) AS C FROM S_STUDENT_COURSE AS SC WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY PK_COURSE_OFFERING) AS sqSC
			               ON S_COURSE_OFFERING.PK_COURSE_OFFERING = sqSC.PK_COURSE_OFFERING
			WHERE " . $where ." order by $sort $order " ;

// echo "start >>".(microtime(true) - $start_time);
//echo $query;exit;	
$_SESSION['REPORT_QUERY'] = $where; //Ticket # 1826
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	

$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str  = '&nbsp;<a href="course_offering?id='.$row['PK_COURSE_OFFERING'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$PK_COURSE_OFFERING = $row['PK_COURSE_OFFERING'];
	
	$row['SESSION'] = substr($row['SESSION'],0,1);

	//$res_def_grade 	= $db->Execute("SELECT PK_GRADE  FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_DEFAULT = 1 ");
	//$PK_GRADE 		= $res_def_grade->fields['PK_GRADE'];

	/* Ticket # 1458
	$res_grade  = $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND (FINAL_GRADE > 0 AND FINAL_GRADE != '$PK_GRADE') ");
	$res_attend = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND COMPLETED = 1");
	$res_stu 	= $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");

	if($res_grade->RecordCount() == 0 && $res_attend->RecordCount() == 0 && $res_stu->RecordCount() == 0 ) {
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_COURSE_OFFERING'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	}
	*/
	
	/* Ticket # 1515 */
	//$res_stu = $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_MASTER, S_STUDENT_COURSE, S_STUDENT_ENROLLMENT WHERE  S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	//$res_stu = $db->Execute("select COUNT(PK_STUDENT_COURSE) as NO from S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'  ");
	/*$query_stu = "select COUNT(PK_STUDENT_COURSE) as NO from S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'  ";
	$record_stu = mysql_query($query_stu) or die(mysql_error());
	$res_stu = mysql_fetch_assoc($record_stu);	

	$row['NO_STUDENT'] = $res_stu['NO'];*/
	/* Ticket # 1515 */
	
	if($row['INSTRUCTOR'] == 0 || $row['INSTRUCTOR'] == ''){
		$row['INSTRUCTOR_NAME'] = '<a href="javascript:void(0);" onclick="assign_ins('.$row['PK_COURSE_OFFERING'].','.$row['PK_CAMPUS'].')" title="'.ASSIGN.'" >'.ASSIGN.'</a>';
	}
	
	$row['ACTION'] = $row['ACTIVE'].$str; //Ticket # 1458
	
	$row['SELECT'] = '<input type="checkbox" name="CHK_PK_COURSE_OFFERING[]" id="CHK_PK_COURSE_OFFERING_'.$PK_COURSE_OFFERING.'" value="'.$PK_COURSE_OFFERING.'" onchange="show_btn()" >';
	
	array_push($items, $row);
}

$result["rows"] = $items;
echo json_encode($result);