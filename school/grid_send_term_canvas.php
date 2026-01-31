<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

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
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'BEGIN_DATE DESC ';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
/*$PK_COURSE_OFFERING = isset($_REQUEST['PK_COURSE_OFFERING']) ? ($_REQUEST['PK_COURSE_OFFERING']) : '';
$PK_TERM_MASTER 	= isset($_REQUEST['PK_TERM_MASTER']) ? ($_REQUEST['PK_TERM_MASTER']) : '';
$PK_CAMPUS 			= isset($_REQUEST['PK_CAMPUS']) ? ($_REQUEST['PK_CAMPUS']) : '';
$SEARCH 			= isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';*/

$offset = ($page-1)*$rows;
	
$result = array();

$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND LMS_ACTIVE = 1 ";

$rs = mysql_query("SELECT DISTINCT(PK_TERM_MASTER) FROM S_TERM_MASTER WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_TERM_MASTER,BEGIN_DATE, END_DATE, TERM_DESCRIPTION,TERM_GROUP,IF(ALLOW_ONLINE_ENROLLMENT = 1, 'Yes', 'No') AS ALLOW_ONLINE_ENROLLMENT ,IF(LMS_ACTIVE = 1, 'Yes', 'No') AS LMS_ACTIVE, SIS_ID FROM S_TERM_MASTER WHERE " . $where ." order by $sort $order " ;
//echo $query;exit;	
$_SESSION['query'] = $query;
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	if($row['BEGIN_DATE'] != '' && $row['BEGIN_DATE'] != '0000-00-00')
		$row['BEGIN_DATE'] = date('m/d/Y',strtotime($row['BEGIN_DATE']));
	else
		$row['BEGIN_DATE'] = '';
		
	if($row['END_DATE'] != '' && $row['END_DATE'] != '0000-00-00')
		$row['END_DATE'] = date('m/d/Y',strtotime($row['END_DATE']));
	else
		$row['END_DATE'] = '';

	$PK_TERM_MASTER = $row['PK_TERM_MASTER'];
	$res1 = $db->Execute("SELECT SUCCESS, S_TERM_CANVAS.CREATED_ON, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME,MESSAGE FROM S_TERM_CANVAS LEFT JOIN Z_USER ON Z_USER.PK_USER = S_TERM_CANVAS.CREATED_BY AND PK_USER_TYPE IN (1,2) LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE  PK_TERM_MASTER = '$PK_TERM_MASTER' ORDER BY PK_TERM_CANVAS DESC ");	
	
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

	$row['ACTION'] = '<input type="checkbox" name="PK_TERM_MASTER[]" id="PK_TERM_MASTER_'.$row['PK_TERM_MASTER'].'" value="'.$row['PK_TERM_MASTER'].'" onclick="show_btn()" >';
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);