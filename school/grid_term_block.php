<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_TERM_BLOCK';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (DESCRIPTION  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_TERM_BLOCK) FROM S_TERM_BLOCK WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_TERM_BLOCK,BEGIN_DATE, END_DATE, DESCRIPTION,DAYS, IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM S_TERM_BLOCK WHERE " . $where ." order by $sort $order " ;
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

	$str  = '&nbsp;<a href="term_block?id='.$row['PK_TERM_BLOCK'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_ENROLL_MANDATE_FIELDS from S_ENROLL_MANDATE_FIELDS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_BLOCK = '$row[PK_TERM_BLOCK]' ");
	$res_check2 = $db->Execute("select PK_MISC_BATCH_DETAIL from S_MISC_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_BLOCK = '$row[PK_TERM_BLOCK]' ");
	$res_check3 = $db->Execute("select PK_PAYMENT_BATCH_DETAIL from S_PAYMENT_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_BLOCK = '$row[PK_TERM_BLOCK]' ");
	$res_check4 = $db->Execute("select PK_STUDENT_DISBURSEMENT from S_STUDENT_DISBURSEMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_BLOCK = '$row[PK_TERM_BLOCK]' ");
	$res_check5 = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ENROLLMENT_PK_TERM_BLOCK = '$row[PK_TERM_BLOCK]' ");
	$res_check6 = $db->Execute("select PK_TUITION_BATCH_DETAIL from S_TUITION_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_BLOCK = '$row[PK_TERM_BLOCK]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0 && $res_check6->RecordCount() == 0) 
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_TERM_BLOCK'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);