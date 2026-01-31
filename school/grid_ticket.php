<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'TICKET_NO';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';

$_SESSION['rows'] 			= $rows;
$_SESSION['PAGE'] 			= $page;
$_SESSION['SORT_FIELD'] 	= $sort;
$_SESSION['SORT_ORDER'] 	= $order;

$SEARCH 			= isset($_POST['SEARCH']) ? mysql_real_escape_string($_POST['SEARCH']) : '';
$PK_TICKET_STATUS 	= isset($_POST['PK_TICKET_STATUS']) ? mysql_real_escape_string($_POST['PK_TICKET_STATUS']) : '';

$offset = ($page-1)*$rows;

$result = array();
$where = " Z_TICKET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_PARENT = 1 AND IS_DELETED = 0 ";

if($PK_TICKET_STATUS != ''){
	$where .= " AND Z_TICKET.PK_TICKET_STATUS = '$PK_TICKET_STATUS' ";
} else {
	if($SEARCH == '')
		$where .= " AND Z_TICKET.PK_TICKET_STATUS NOT IN (2,3) ";
}
	
if($SEARCH != ''){
	$where .= " AND ( Z_TICKET.TICKET_NO like '%$SEARCH%' OR SUBJECT like '%$SEARCH%' OR TICKET_STATUS like '%$SEARCH%' OR CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) like '%$SEARCH%' )";
}
	
$rs = mysql_query("SELECT COUNT(Z_TICKET.PK_TICKET) FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE " . $where." GROUP BY Z_TICKET.INTERNAL_ID ")or die(mysql_error());
$result["total"] = mysql_num_rows($rs); 

$query = "SELECT Z_TICKET.PK_TICKET,Z_TICKET.INTERNAL_ID,SUBJECT,TICKET_PRIORITY,TICKET_STATUS,TICKET_NO, Z_TICKET.PK_TICKET_STATUS, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, Z_TICKET.PK_TICKET_PRIORITY, CLOSED_DATE, Z_TICKET.CREATED_ON AS CREATED_DATE, Z_TICKET.CREATED_BY FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE " . $where;
//echo $query. " GROUP BY Z_TICKET.INTERNAL_ID  order by $sort $order limit $offset,$rows";exit;
$rs = mysql_query( $query. " GROUP BY Z_TICKET.INTERNAL_ID  order by $sort $order limit $offset,$rows")or die(mysql_error());	
$items = array();
while($row = mysql_fetch_array($rs)){
	$PK_TICKET 	 = $row['PK_TICKET'];
	$INTERNAL_ID = $row['INTERNAL_ID'];
	
	if($row['CLOSED_DATE'] != '0000-00-00')
		$row['CLOSED_DATE'] = date("m/d/Y",strtotime($row['CLOSED_DATE']));
	else
		$row['CLOSED_DATE'] = '';
		
	if($row['CREATED_DATE'] != '0000-00-00')
		$row['CREATED_DATE'] = date("m/d/Y",strtotime($row['CREATED_DATE']));
	else
		$row['CREATED_DATE'] = '';

	$res_ticket4 = $db->Execute("SELECT Z_TICKET.CREATED_ON,CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE INTERNAL_ID = '$INTERNAL_ID' AND IS_PARENT = 0 ORDER BY PK_TICKET DESC"); 
	$res_ticket5 = $db->Execute("SELECT CHANGED_ON,CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME FROM Z_TICKET_STATUS_CHANGE_HISTORY LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET_STATUS_CHANGE_HISTORY.CHANGED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE INTERNAL_ID = '$INTERNAL_ID' ORDER BY PK_TICKET_STATUS_CHANGE_HISTORY DESC"); 
	
	$last_activity_date = '';
	if(strtotime($res_ticket4->fields['CREATED_ON']) > strtotime($res_ticket5->fields['CHANGED_ON'])) {
		$row['LAST_UPDATE_ON'] = date("m/d/Y",strtotime($res_ticket4->fields['CREATED_ON']));
		$row['LAST_UPDATE_BY'] = $res_ticket4->fields['NAME'];
	} else {
		$row['LAST_UPDATE_ON'] = date("m/d/Y",strtotime($res_ticket5->fields['CHANGED_ON']));
		$row['LAST_UPDATE_BY'] = $res_ticket5->fields['NAME'];
	}
	
	$row['ACTION'] = '<a href="view_ticket?id='.$row['INTERNAL_ID'].'">View</a>&nbsp;&nbsp;&nbsp;';
	
	array_push($items, $row);
}
$result["rows"] = $items;

echo json_encode($result);