<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'START_DATE';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " M_ACADEMIC_CALENDAR.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (LEAVE_TYPE like '%$SEARCH%' OR TITLE like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR) FROM M_ACADEMIC_CALENDAR WHERE " . $where. " GROUP BY M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR, TITLE,IF(LEAVE_TYPE = 1,'Holiday',IF(LEAVE_TYPE = 2,'Break',IF(LEAVE_TYPE = 3,'Closure',''))) AS LEAVE_TYPE,START_DATE,END_DATE, IF(M_ACADEMIC_CALENDAR.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>', '<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, GROUP_CONCAT(CAMPUS_CODE SEPARATOR ', ') as CAMPUS FROM M_ACADEMIC_CALENDAR LEFT JOIN M_ACADEMIC_CALENDAR_CAMPUS ON M_ACADEMIC_CALENDAR_CAMPUS.PK_ACADEMIC_CALENDAR = M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = M_ACADEMIC_CALENDAR_CAMPUS.PK_CAMPUS WHERE " . $where ." GROUP BY M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR  order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	if($row['START_DATE'] != '0000-00-00')
		$row['START_DATE'] = date("m/d/Y",strtotime($row['START_DATE']));
	else
		$row['START_DATE'] = '';
		
	if($row['END_DATE'] != '0000-00-00')
		$row['END_DATE'] = date("m/d/Y",strtotime($row['END_DATE']));
	else
		$row['END_DATE'] = '';
		
	$SESSION = '';
	$rs1 = mysql_query("SELECT SESSION FROM M_SESSION,M_ACADEMIC_CALENDAR_SESSION WHERE M_ACADEMIC_CALENDAR_SESSION.PK_SESSION = M_SESSION.PK_SESSION AND PK_ACADEMIC_CALENDAR = '$row[PK_ACADEMIC_CALENDAR]' GROUP BY M_SESSION.PK_SESSION  ");	
	while($row1 = mysql_fetch_array($rs1)){
		if($SESSION != '')
			$SESSION .= ', ';
			
		$SESSION .= $row1['SESSION'];
	}
	
	$row['SESSION'] = $SESSION;

	$str  = '&nbsp;<a href="academic_calendar?id='.$row['PK_ACADEMIC_CALENDAR'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_ACADEMIC_CALENDAR'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);