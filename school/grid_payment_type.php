<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

/* DIAM - 124, Only data access for Main Admin */
$PK_USER = $_SESSION['PK_USER'];
$PK_ACCOUNT_DATA = mysql_query("SELECT * FROM Z_USER WHERE PK_USER = ".$PK_USER." ") or die(mysql_error());
$Record = mysql_fetch_array($PK_ACCOUNT_DATA);
$PK_USER_TYPE = $Record['PK_USER_TYPE'];
/* End DIAM - 124, Only data access for Main Admin */

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'AR_PAYMENT_TYPE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (AR_PAYMENT_TYPE  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_AR_PAYMENT_TYPE) FROM M_AR_PAYMENT_TYPE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);


$query = "SELECT PK_AR_PAYMENT_TYPE,AR_PAYMENT_TYPE, IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM M_AR_PAYMENT_TYPE WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	/* DIAM - 124, Only data access for Main Admin */
	if ($PK_USER_TYPE == '1') {
		$str  = '&nbsp;<a href="payment_type?id='.$row['PK_AR_PAYMENT_TYPE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	}
	else
	{
		$str  = '&nbsp;';
	}//DIAM-2131
	/* End DIAM - 124, Only data access for Main Admin */

	
	//$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_AR_PAYMENT_TYPE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);
