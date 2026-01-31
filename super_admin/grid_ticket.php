<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : $_SESSION['PAGE'];
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : $_SESSION['rows'];
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : $_SESSION['SORT_FIELD'];  
$order = isset($_POST['order']) ? strval($_POST['order']) : $_SESSION['SORT_ORDER'];
	
$_SESSION['rows'] 		= $rows;
$_SESSION['PAGE'] 		= $page;
$_SESSION['SORT_FIELD'] = $sort;
$_SESSION['SORT_ORDER'] = $order;

$SEARCH 			= isset($_POST['SEARCH']) ? mysql_real_escape_string($_POST['SEARCH']) : $_SESSION['SRC_TXT_1'];
$PK_TICKET_STATUS 	= isset($_POST['PK_TICKET_STATUS']) ? mysql_real_escape_string($_POST['PK_TICKET_STATUS']) : $_SESSION['SRC_TXT_2'];
$PK_TICKET_CATEGORY = isset($_POST['PK_TICKET_CATEGORY']) ? mysql_real_escape_string($_POST['PK_TICKET_CATEGORY']) : $_SESSION['SRC_TXT_3'];

$offset = ($page-1)*$rows;

$result = array();
$where = " IS_PARENT = 1 AND IS_DELETED = 0 ";

if($PK_TICKET_STATUS != ''){
	$where .= " AND Z_TICKET.PK_TICKET_STATUS = '$PK_TICKET_STATUS' ";
	$_SESSION['SRC_TXT_2'] = $PK_TICKET_STATUS;
} else {
	$_SESSION['SRC_TXT_2'] = '';
	if($SEARCH == '')
		$where .= " AND Z_TICKET.PK_TICKET_STATUS NOT IN (2,3) ";
}

if($PK_TICKET_CATEGORY != ''){
	$where .= " AND Z_TICKET.PK_TICKET_CATEGORY = '$PK_TICKET_CATEGORY' ";
	$_SESSION['SRC_TXT_3'] = $PK_TICKET_CATEGORY;
} else {
	$_SESSION['SRC_TXT_3'] = '';
}
	
if($SEARCH != ''){
	$where .= " AND ( Z_TICKET.TICKET_NO like '%$SEARCH%' OR SUBJECT like '%$SEARCH%' OR TICKET_STATUS like '%$SEARCH%' OR CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) like '%$SEARCH%' OR SCHOOL_NAME like '%$SEARCH%' OR TICKET_CATEGORY like '%$SEARCH%' )";
	$_SESSION['SRC_TXT_1'] = $SEARCH;
} else {
	$_SESSION['SRC_TXT_1'] = '';
}
	
$rs = mysql_query("SELECT COUNT(Z_TICKET.PK_TICKET) FROM Z_TICKET LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_TICKET.TICKET_FOR LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_CATEGORY ON Z_TICKET_CATEGORY.PK_TICKET_CATEGORY = Z_TICKET.PK_TICKET_CATEGORY LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE " . $where." GROUP BY Z_TICKET.INTERNAL_ID ")or die(mysql_error());
$result["total"] = mysql_num_rows($rs); 

$query = "SELECT SCHOOL_NAME,Z_TICKET.PK_TICKET,Z_TICKET.INTERNAL_ID,SUBJECT,TICKET_PRIORITY,TICKET_STATUS,TICKET_NO, Z_TICKET.PK_TICKET_STATUS, DUE_DATE, TICKET_CATEGORY, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, Z_TICKET.PK_TICKET_PRIORITY, CLOSED_DATE, Z_TICKET.CREATED_ON AS CREATED_DATE, Z_TICKET.CREATED_BY FROM Z_TICKET LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_TICKET.TICKET_FOR LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_CATEGORY ON Z_TICKET_CATEGORY.PK_TICKET_CATEGORY = Z_TICKET.PK_TICKET_CATEGORY LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE ";

$_SESSION['TICKET_Q'] = $query. " IS_PARENT = 1 AND IS_DELETED = 0 GROUP BY Z_TICKET.INTERNAL_ID ";
//echo $query. " GROUP BY Z_TICKET.INTERNAL_ID  order by $sort $order limit $offset,$rows";exit;
$rs = mysql_query( $query." ".$where." GROUP BY Z_TICKET.INTERNAL_ID  order by $sort $order limit $offset,$rows")or die(mysql_error());	
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
		
	$style = "";
	if($row['DUE_DATE'] != '0000-00-00' && strtotime(date("Y-m-d")) > strtotime($row['DUE_DATE']) ) {
		$style = "color:red;";
	}
	
	if($row['DUE_DATE'] != '0000-00-00')
		$row['DUE_DATE'] = date("m/d/Y",strtotime($row['DUE_DATE']));
	else
		$row['DUE_DATE'] = '';	

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
	
	$row['SCHOOL_NAME'] 	= '<span style="'.$style.'">'.$row['SCHOOL_NAME'].'</span>';
	$row['TICKET_NO'] 		= '<span style="'.$style.'">'.$row['TICKET_NO'].'</span>';
	$row['TICKET_STATUS'] 	= '<span style="'.$style.'">'.$row['TICKET_STATUS'].'</span>';
	$row['TICKET_CATEGORY'] = '<span style="'.$style.'">'.$row['TICKET_CATEGORY'].'</span>';
	$row['SUBJECT'] 		= '<span style="'.$style.'">'.$row['SUBJECT'].'</span>';
	$row['TICKET_PRIORITY'] = '<span style="'.$style.'">'.$row['TICKET_PRIORITY'].'</span>';
	$row['CREATED_DATE'] 	= '<span style="'.$style.'">'.$row['CREATED_DATE'].'</span>';
	$row['NAME'] 			= '<span style="'.$style.'">'.$row['NAME'].'</span>';
	$row['DUE_DATE'] 		= '<span style="'.$style.'">'.$row['DUE_DATE'].'</span>';
	$row['LAST_UPDATE_ON'] 	= '<span style="'.$style.'">'.$row['LAST_UPDATE_ON'].'</span>';
	$row['LAST_UPDATE_BY'] 	= '<span style="'.$style.'">'.$row['LAST_UPDATE_BY'].'</span>';
	$row['CLOSED_DATE'] 	= '<span style="'.$style.'">'.$row['CLOSED_DATE'].'</span>';
	$row['ACTION'] 			= '<a href="view_ticket?id='.$row['INTERNAL_ID'].'">View</a>&nbsp;&nbsp;&nbsp;';
	
	array_push($items, $row);
}
$result["rows"] = $items;

echo json_encode($result);