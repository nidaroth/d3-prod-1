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
				


$PK_CAMPUS 		= isset($_REQUEST['PK_CAMPUS']) ? implode(",",$_REQUEST['PK_CAMPUS']) : '';

$PK_STUDENT_STATUS 		= isset($_REQUEST['PK_STUDENT_STATUS']) ? implode(",",$_REQUEST['PK_STUDENT_STATUS']) : '';

$TREM_BEGIN_START_DATE = isset($_REQUEST['TREM_BEGIN_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_START_DATE']) : $_SESSION['TREM_BEGIN_START_DATE'];

$TREM_BEGIN_END_DATE = isset($_REQUEST['TREM_BEGIN_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_END_DATE']) : $_SESSION['TREM_BEGIN_END_DATE'];


$offset = ($page-1)*$rows;
	
$result = array();

$where = " S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";


if($TREM_BEGIN_START_DATE != '' && $TREM_BEGIN_END_DATE != '' ) {
	$TREM_BEGIN_START_DATE=date('Y-m-d',strtotime($TREM_BEGIN_START_DATE));
	$TREM_BEGIN_END_DATE=date('Y-m-d',strtotime($TREM_BEGIN_END_DATE));
	$where .= " AND S_TERM_MASTER.BEGIN_DATE >= '$TREM_BEGIN_START_DATE' AND S_TERM_MASTER.BEGIN_DATE <= '$TREM_BEGIN_END_DATE' ";
}

	
if($PK_CAMPUS != '') {
	$where .= " AND S_COURSE_OFFERING.PK_CAMPUS IN($PK_CAMPUS)";
	$_SESSION['SRC_PK_CAMPUS'] = $PK_CAMPUS;
} else {
	$_SESSION['SRC_PK_CAMPUS'] = '';
}

if($PK_STUDENT_STATUS != '') {
	$where .= " AND SE.PK_STUDENT_STATUS IN($PK_STUDENT_STATUS)";
	$_SESSION['SRC_PK_STUDENT_STATUS'] = $PK_STUDENT_STATUS;
} else {
	$_SESSION['SRC_PK_STUDENT_STATUS'] = '';
}



// $rs = mysql_query("SELECT DISTINCT(PK_COURSE_OFFERING) FROM S_COURSE_OFFERING LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR WHERE " . $where. " ")or die(mysql_error());


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
			LEFT JOIN (SELECT PK_COURSE_OFFERING, COUNT(*) AS C, PK_STUDENT_ENROLLMENT FROM S_STUDENT_COURSE AS SC WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY PK_COURSE_OFFERING) AS sqSC
			               ON S_COURSE_OFFERING.PK_COURSE_OFFERING = sqSC.PK_COURSE_OFFERING
						   INNER JOIN S_STUDENT_ENROLLMENT AS SE ON sqSC.PK_STUDENT_ENROLLMENT = SE.PK_STUDENT_ENROLLMENT
			WHERE " . $where ." order by $sort $order " ;

// echo "start >>".(microtime(true) - $start_time);
//echo $query;exit;	
$_SESSION['REPORT_QUERY'] = $where; 
//$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
$rs = mysql_query($query)or die(mysql_error());	

//echo $query;exit;	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str  = '&nbsp;<a href="course_offering?id='.$row['PK_COURSE_OFFERING'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$PK_COURSE_OFFERING = $row['PK_COURSE_OFFERING'];
	
	$row['SESSION'] = substr($row['SESSION'],0,1);

	
	
	if($row['INSTRUCTOR'] == 0 || $row['INSTRUCTOR'] == ''){
		$row['INSTRUCTOR_NAME'] = '<a href="javascript:void(0);" onclick="assign_ins('.$row['PK_COURSE_OFFERING'].','.$row['PK_CAMPUS'].')" title="'.ASSIGN.'" >'.ASSIGN.'</a>';
	}
	
	$row['ACTION'] = $row['ACTIVE'].$str; //Ticket # 1458
	
	$row['SELECT'] = '<input type="checkbox" name="CHK_PK_COURSE_OFFERING[]" id="CHK_PK_COURSE_OFFERING_'.$PK_COURSE_OFFERING.'" value="'.$PK_COURSE_OFFERING.'" onchange="show_btn()" >';
	
	array_push($items, $row);
}

$result["rows"] = $items;
$result["camp"] = $PK_CAMPUS;
echo json_encode($result);