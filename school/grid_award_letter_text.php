<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'NAME';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();

$where = " S_AWARD_LETTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
if($_SESSION['PK_ROLES'] == 3)
	$where .= " AND S_AWARD_LETTER.PK_CAMPUS = '$_SESSION[PK_CAMPUS]' ";
	
if($SEARCH != '')
	$where .= " AND (NAME like '%$SEARCH%')";

$rs = mysql_query("SELECT DISTINCT(PK_AWARD_LETTER) FROM S_AWARD_LETTER LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_AWARD_LETTER.PK_CAMPUS WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_AWARD_LETTER,NAME,OFFICIAL_CAMPUS_NAME, IF(S_AWARD_LETTER.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM S_AWARD_LETTER LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_AWARD_LETTER.PK_CAMPUS WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str  = '&nbsp;<a href="award_letter_text?id='.$row['PK_AWARD_LETTER'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_AWARD_LETTER'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);