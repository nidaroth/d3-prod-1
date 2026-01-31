<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'BEGIN_DATE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_TERM_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (TERM_DESCRIPTION  like '%$SEARCH%' OR TERM_GROUP  like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(S_TERM_MASTER.PK_TERM_MASTER) FROM S_TERM_MASTER LEFT JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_TERM_MASTER_CAMPUS.PK_CAMPUS WHERE " . $where. "  GROUP BY S_TERM_MASTER.PK_TERM_MASTER  ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT S_TERM_MASTER.PK_TERM_MASTER,BEGIN_DATE, END_DATE, TERM_DESCRIPTION,TERM_GROUP,IF(ALLOW_ONLINE_ENROLLMENT = 1, 'Yes', 'No') AS ALLOW_ONLINE_ENROLLMENT ,IF(LMS_ACTIVE = 1, 'Yes', 'No') AS LMS_ACTIVE, IF(S_TERM_MASTER.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, GROUP_CONCAT(CAMPUS_CODE SEPARATOR ', ' ) as CAMPUS_CODE FROM S_TERM_MASTER LEFT JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_TERM_MASTER_CAMPUS.PK_CAMPUS WHERE " . $where ." GROUP BY S_TERM_MASTER.PK_TERM_MASTER order by $sort $order " ;
// echo $query;exit;	
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

	$str  = '&nbsp;<a href="term_master?id='.$row['PK_TERM_MASTER'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';

	/*$res_check1 = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$row[PK_TERM_MASTER]' ");
	$res_check2 = $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$row[PK_TERM_MASTER]' ");
	$res_check3 = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$row[PK_TERM_MASTER]' ");
	$res_check4 = $db->Execute("select PK_STUDENT_FEE_BUDGET from S_STUDENT_FEE_BUDGET WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$row[PK_TERM_MASTER]' ");
	$res_check5 = $db->Execute("select PK_TUITION_BATCH_MASTER from S_TUITION_BATCH_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$row[PK_TERM_MASTER]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0) */
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_TERM_MASTER'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	$row['SELECT'] = '<input type="checkbox" name="CHK_PK_TERM_MASTER[]" id="CHK_PK_TERM_MASTER_'.$row['PK_TERM_MASTER'].'" value="'.$row['PK_TERM_MASTER'].'" onchange="show_btn()" >'; //Ticket # 1487
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);