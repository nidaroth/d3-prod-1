<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT _4807G FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 || $res_add_on->fields['_4807G'] == 0){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'EIN_NO';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (EIN_NO  like '%$SEARCH%' OR EFCN_NO like '%$SEARCH%' OR CONTACT_NAME like '%$SEARCH%' OR CONTACT_PHONE like '%$SEARCH%' OR CONTACT_EMAIL like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_4807G_EIN) FROM _4807G_EIN WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_4807G_EIN,EIN_NO,EFCN_NO,CONTACT_NAME,CONTACT_EMAIL,CONTACT_PHONE FROM _4807G_EIN WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$CAMPUS = "";
	$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_CAMPUS, _4807G_EIN_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = _4807G_EIN_CAMPUS.PK_CAMPUS  AND PK_4807G_EIN = '".$row['PK_4807G_EIN']."' order by CAMPUS_CODE ASC ");
	while (!$res_campus->EOF) { 
		if($CAMPUS != '')
			$CAMPUS .= ', ';
			
		$CAMPUS .= $res_campus->fields['CAMPUS_CODE'];
		$res_campus->MoveNext();
	}
	$row['CAMPUS'] = $CAMPUS;

	$str  = '&nbsp;<a href="_480G_ein?id='.$row['PK_4807G_EIN'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_4807G_EIN'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);