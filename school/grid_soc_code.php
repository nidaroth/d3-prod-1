<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'SOC_CODE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (SOC_CODE like '%$SEARCH%' OR SOC_TITLE  like '%$SEARCH%' OR IPEDS_CATEGORY like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_SOC_CODE) FROM M_SOC_CODE LEFT JOIN M_IPEDS_CATEGORY_MASTER ON M_IPEDS_CATEGORY_MASTER.PK_IPEDS_CATEGORY_MASTER = M_SOC_CODE.PK_IPEDS_CATEGORY_MASTER WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_SOC_CODE,SOC_CODE,SOC_TITLE, IPEDS_CATEGORY, IF(M_SOC_CODE.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_SOC_CODE LEFT JOIN M_IPEDS_CATEGORY_MASTER ON M_IPEDS_CATEGORY_MASTER.PK_IPEDS_CATEGORY_MASTER = M_SOC_CODE.PK_IPEDS_CATEGORY_MASTER WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="soc_code?id='.$row['PK_SOC_CODE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$res_check1 = $db->Execute("select PK_COMPANY_JOB from S_COMPANY_JOB WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SOC_CODE = '$row[PK_SOC_CODE]' ");
	$res_check2 = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SOC_CODE = '$row[PK_SOC_CODE]' ");
	$res_check3 = $db->Execute("select PK_STUDENT_JOB from S_STUDENT_JOB WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SOC_CODE = '$row[PK_SOC_CODE]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 &&  $row['PK_SOC_CODE_MASTER'] == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_SOC_CODE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);