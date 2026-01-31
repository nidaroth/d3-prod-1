<? require_once("../global/config.php"); 
require_once("check_access.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_ANNOUNCEMENT';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " ANNOUNCEMENT_FROM = 2 AND Z_ANNOUNCEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";

if($_SESSION['PK_ROLES'] == 3)
	$where .= " AND Z_ANNOUNCEMENT_CAMPUS.PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) ";
	
if($SEARCH != '')
	$where .= " AND (HEADER  like '%$SEARCH%' )";
	

$rs = mysql_query("SELECT DISTINCT(Z_ANNOUNCEMENT.PK_ANNOUNCEMENT) FROM Z_ANNOUNCEMENT LEFT JOIN Z_ANNOUNCEMENT_CAMPUS ON Z_ANNOUNCEMENT_CAMPUS.PK_ANNOUNCEMENT = Z_ANNOUNCEMENT.PK_ANNOUNCEMENT WHERE " . $where. " GROUP BY Z_ANNOUNCEMENT.PK_ANNOUNCEMENT")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT Z_ANNOUNCEMENT.PK_ANNOUNCEMENT, HEADER,START_DATE_TIME,END_DATE_TIME, IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM Z_ANNOUNCEMENT LEFT JOIN Z_ANNOUNCEMENT_CAMPUS ON Z_ANNOUNCEMENT_CAMPUS.PK_ANNOUNCEMENT = Z_ANNOUNCEMENT.PK_ANNOUNCEMENT WHERE " . $where ." GROUP BY Z_ANNOUNCEMENT.PK_ANNOUNCEMENT order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	if($row['START_DATE_TIME'] == '0000-00-00 00:00:00')
		$row['START_DATE_TIME'] = '';
	else
		$row['START_DATE_TIME'] = date("m/d/Y",strtotime($row['START_DATE_TIME']));
		
	if($row['END_DATE_TIME'] == '0000-00-00 00:00:00')
		$row['END_DATE_TIME'] = '';
	else
		$row['END_DATE_TIME'] = date("m/d/Y",strtotime($row['END_DATE_TIME']));
		
	$CAMPUS = "";
	$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_CAMPUS, Z_ANNOUNCEMENT_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = Z_ANNOUNCEMENT_CAMPUS.PK_CAMPUS  AND PK_ANNOUNCEMENT = '".$row['PK_ANNOUNCEMENT']."' ORDER By CAMPUS_CODE ASC");
	while (!$res_campus->EOF) { 
		if($CAMPUS != '')
			$CAMPUS .= ', ';
			
		$CAMPUS .= $res_campus->fields['CAMPUS_CODE'];
		$res_campus->MoveNext();
	}
	$row['CAMPUS'] = $CAMPUS;
	
	$ANNOUNCEMENT_FOR = "";
	$res_campus = $db->Execute("SELECT ANNOUNCEMENT_FOR FROM M_ANNOUNCEMENT_FOR_MASTER, Z_ANNOUNCEMENT_FOR WHERE M_ANNOUNCEMENT_FOR_MASTER.PK_ANNOUNCEMENT_FOR_MASTER = Z_ANNOUNCEMENT_FOR.PK_ANNOUNCEMENT_FOR_MASTER  AND PK_ANNOUNCEMENT = '".$row['PK_ANNOUNCEMENT']."' ");
	while (!$res_campus->EOF) { 
		if($ANNOUNCEMENT_FOR != '')
			$ANNOUNCEMENT_FOR .= ', ';
			
		$ANNOUNCEMENT_FOR .= $res_campus->fields['ANNOUNCEMENT_FOR'];
		$res_campus->MoveNext();
	}
	$row['ANNOUNCEMENT_FOR'] = $ANNOUNCEMENT_FOR;
		
	$str = '<a href="announcement?id='.$row['PK_ANNOUNCEMENT'].'" title="Edit" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	//$str .= '&nbsp;<a href="javascript:void(0)" onclick="delete_row('.$row['PK_ANNOUNCEMENT'].')" title="Delete" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].'&nbsp;'.$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);