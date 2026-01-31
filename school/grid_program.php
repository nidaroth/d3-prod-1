<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'M_CAMPUS_PROGRAM.ACTIVE DESC, CODE ASC';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();

$where = " M_CAMPUS_PROGRAM.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
$table = "";
if($_SESSION['PK_ROLES'] == 3) {
	$table = ", M_CAMPUS_PROGRAM_CAMPUS ";
	$where .= " AND M_CAMPUS_PROGRAM_CAMPUS.PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) AND M_CAMPUS_PROGRAM_CAMPUS.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM ";
}

if($SEARCH != '')
	$where .= " AND (CODE  like '%$SEARCH%' OR M_CAMPUS_PROGRAM.DESCRIPTION LIKE '%$SEARCH%' OR PROGRAM_TRANSCRIPT_CODE LIKE '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM) FROM M_CAMPUS_PROGRAM $table WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM,CODE, PROGRAM_TRANSCRIPT_CODE, M_CAMPUS_PROGRAM.DESCRIPTION, IF(M_CAMPUS_PROGRAM.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE_1, MONTHS, WEEKS, HOURS, UNITS, FA_UNITS FROM M_CAMPUS_PROGRAM $table WHERE " . $where ." order by $sort $order " ; //Ticket # 1339
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	if($row['UNITS'] != '')
		$row['UNITS'] = number_format_value_checker($row['UNITS'], 2);
	else
		$row['UNITS'] = '0.00';
		
	if($row['FA_UNITS'] != '')
		$row['FA_UNITS'] = number_format_value_checker($row['FA_UNITS'], 2);
	else
		$row['FA_UNITS'] = '0.00';

	$CAMPUS_CODE = '';
	$res_type = $db->Execute("select CAMPUS_CODE from M_CAMPUS_PROGRAM_CAMPUS,S_CAMPUS WHERE M_CAMPUS_PROGRAM_CAMPUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM = '$row[PK_CAMPUS_PROGRAM]' AND S_CAMPUS.PK_CAMPUS = M_CAMPUS_PROGRAM_CAMPUS.PK_CAMPUS ORDER BY CAMPUS_CODE ASC  ");
	while (!$res_type->EOF) {
		if($CAMPUS_CODE != '')
			$CAMPUS_CODE .= ', ';
			
		$CAMPUS_CODE .= $res_type->fields['CAMPUS_CODE'];
		
		$res_type->MoveNext();
	}
	
	$row['CAMPUS_CODE'] = $CAMPUS_CODE;
	
	$str  = '&nbsp;<a href="program?id='.$row['PK_CAMPUS_PROGRAM'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="program?id='.$row['PK_CAMPUS_PROGRAM'].'&duplicate=1" title="'.DUPLICATE.'" class="btn pdf-color btn-circle"><i class="fas fa-copy"></i></a>';

	//$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_CAMPUS_PROGRAM'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE_1'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);