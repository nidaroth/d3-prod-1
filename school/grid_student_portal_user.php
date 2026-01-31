<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student_portal_user.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'S_STUDENT_MASTER.LAST_NAME ASC, S_STUDENT_MASTER.FIRST_NAME ASC ';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH 			= isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$PK_STUDENT_STATUS	= isset($_REQUEST['PK_STUDENT_STATUS']) ? ($_REQUEST['PK_STUDENT_STATUS']) : '';
$PK_CAMPUS_PROGRAM	= isset($_REQUEST['PK_CAMPUS_PROGRAM']) ? ($_REQUEST['PK_CAMPUS_PROGRAM']) : '';
$PK_TERM_MASTER		= isset($_REQUEST['PK_TERM_MASTER']) ? ($_REQUEST['PK_TERM_MASTER']) : '';
$LOGIN_STATUS		= isset($_REQUEST['LOGIN_STATUS']) ? ($_REQUEST['LOGIN_STATUS']) : '';
$PK_CAMPUS			= isset($_REQUEST['PK_CAMPUS']) ? ($_REQUEST['PK_CAMPUS']) : '';

$offset = ($page-1)*$rows;

$table = '';

$result = array();
$where = " S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.IS_ACTIVE_ENROLLMENT = 1 AND Z_USER.ID = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_USER_TYPE = 3 AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND LOGIN_CREATED = 1 AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ";

if($PK_STUDENT_STATUS != '') {
	$PK_STUDENT_STATUS1 = implode(",",$PK_STUDENT_STATUS);
	$where .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($PK_STUDENT_STATUS1) ";
}

if($PK_TERM_MASTER != '') {
	$PK_TERM_MASTER1 = implode(",",$PK_TERM_MASTER);
	$where .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN ($PK_TERM_MASTER1) ";
}
	
if(!empty($PK_CAMPUS_PROGRAM) != '') {
	$PK_CAMPUS_PROGRAM1 = implode(",",$PK_CAMPUS_PROGRAM);
	$where .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM1) ";
}

if($PK_CAMPUS != '') {
	$table .= ",S_STUDENT_CAMPUS ";
	$PK_CAMPUS = implode(",",$PK_CAMPUS);
	$where  .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
}

if($LOGIN_STATUS == 1)
	$where .= " AND Z_USER.ACTIVE = 1 ";
else if($LOGIN_STATUS == 2)
	$where .= " AND Z_USER.ACTIVE = 0 ";
	
if($SEARCH != '')
	$where .= " AND CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) LIKE '%$SEARCH%' ";

$rs = mysql_query("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, IF (Z_USER.ACTIVE = 1,'Active','Inactive') as LOGIN_STATUS  FROM S_STUDENT_MASTER LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER  AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 $table , S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS, Z_USER LEFT JOIN Z_LOGIN_HISTORY ON Z_USER.PK_USER = Z_LOGIN_HISTORY.PK_USER WHERE " . $where. " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);

$query = "SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_STATUS, S_TERM_MASTER.BEGIN_DATE, M_CAMPUS_PROGRAM.CODE, USER_ID,STUDENT_ID, DATE_OF_BIRTH, IF (Z_USER.ACTIVE = 1,'Active','Inactive') as LOGIN_STATUS, S_STUDENT_CONTACT.EMAIL, MAX(LOGIN_TIME) as LOGIN_TIME, Z_USER.PK_USER  FROM S_STUDENT_MASTER LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER  AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 $table , S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS, Z_USER LEFT JOIN Z_LOGIN_HISTORY ON Z_USER.PK_USER = Z_LOGIN_HISTORY.PK_USER WHERE " . $where ." GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER order by $sort $order " ;
//echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	if($row['BEGIN_DATE'] != '0000-00-00' && $row['BEGIN_DATE'] != '')
		$row['BEGIN_DATE'] = date("m/d/Y",strtotime($row['BEGIN_DATE']));
	else
		$row['BEGIN_DATE'] = '';
		
	if($row['DATE_OF_BIRTH'] != '0000-00-00' && $row['DATE_OF_BIRTH'] != '')
		$row['DATE_OF_BIRTH'] = date("m/d/Y",strtotime($row['DATE_OF_BIRTH']));
	else
		$row['DATE_OF_BIRTH'] = '';

	if($row['LOGIN_TIME'] != '0000-00-00 00-00-00' && $row['LOGIN_TIME'] != '')
		$row['LOGIN_TIME'] = date("m/d/Y h:i A",strtotime($row['LOGIN_TIME']));
	else
		$row['LOGIN_TIME'] = '';
		
	$PK_STUDENT_MASTER = $row['PK_STUDENT_MASTER'];
	
	$CAMPUS = "";
	$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_CAMPUS, S_STUDENT_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  AND PK_STUDENT_ENROLLMENT = '".$row['PK_STUDENT_ENROLLMENT']."' ");
	while (!$res_campus->EOF) { 
		if($CAMPUS != '')
			$CAMPUS .= ',<br /> ';
			
		$CAMPUS .= $res_campus->fields['CAMPUS_CODE'];
		$res_campus->MoveNext();
	}
	$row['CAMPUS'] = $CAMPUS;
	
	if($row['LOGIN_STATUS'] == 'Active')
		$str = '<a href="javascript:void(0);" onclick="delete_row('.$row['PK_STUDENT_MASTER'].',\'make_inactive\')" title="'.MAKE_INACTIVE.'" class="btn edit-color btn-circle"><i class="mdi mdi-account-minus"></i></a>';
	else
		$str = '<a href="javascript:void(0);" onclick="delete_row('.$row['PK_STUDENT_MASTER'].',\'make_active\')" title="'.MAKE_ACTIVE.'" class="btn edit-color btn-circle"><i class="mdi mdi-account-check"></i></a>';
	
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_STUDENT_MASTER'].',\'reset_password\')" title="'.RESET_PASSWORD.'" class="btn cc-color btn-circle"><i class="fas fa-key"></i></a>';
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_USER'].',\'log\')" title="'.VIEW_LOG.'" class="btn pdf-color btn-circle"><i class="mdi mdi-clipboard-flow"></i></a>';
	//$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_STUDENT_MASTER'].',\'delete\')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);