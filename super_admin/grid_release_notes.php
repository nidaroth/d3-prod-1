<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_RELEASE_NOTES';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$PK_RELEASE_CATEGORY 	= isset($_REQUEST['PK_RELEASE_CATEGORY']) ? mysql_real_escape_string($_REQUEST['PK_RELEASE_CATEGORY']) : '';
$PK_RELEASE_TYPE 		= isset($_REQUEST['PK_RELEASE_TYPE']) ? mysql_real_escape_string($_REQUEST['PK_RELEASE_TYPE']) : '';
$SUBJECT 				= isset($_REQUEST['SUBJECT']) ? mysql_real_escape_string($_REQUEST['SUBJECT']) : '';
$LOCATION 				= isset($_REQUEST['LOCATION']) ? mysql_real_escape_string($_REQUEST['LOCATION']) : '';
$RELEASE_NOTES 			= isset($_REQUEST['RELEASE_NOTES']) ? mysql_real_escape_string($_REQUEST['RELEASE_NOTES']) : '';
$DATE_FITER_TYPE 		= isset($_REQUEST['DATE_FITER_TYPE']) ? mysql_real_escape_string($_REQUEST['DATE_FITER_TYPE']) : '';
$START_DATE 			= isset($_REQUEST['START_DATE']) ? mysql_real_escape_string($_REQUEST['START_DATE']) : '';
$TO_DATE 				= isset($_REQUEST['TO_DATE']) ? mysql_real_escape_string($_REQUEST['TO_DATE']) : '';

$PUSHED_TO_D3 			= isset($_REQUEST['PUSHED_TO_D3']) ? mysql_real_escape_string($_REQUEST['PUSHED_TO_D3']) : '';
$KNOWLEDGEBASE_URL 		= isset($_REQUEST['KNOWLEDGEBASE_URL']) ? mysql_real_escape_string($_REQUEST['KNOWLEDGEBASE_URL']) : '';

$offset = ($page-1)*$rows;
	
$result = array();
$where = " 1=1 ";

if($PK_RELEASE_CATEGORY != '')
	$where .= " AND Z_RELEASE_NOTES.PK_RELEASE_CATEGORY like '%$PK_RELEASE_CATEGORY%' ";
	
if($PK_RELEASE_TYPE != '')
	$where .= " AND Z_RELEASE_NOTES.PK_RELEASE_TYPE like '%$PK_RELEASE_TYPE%' ";
	
if($KNOWLEDGEBASE_URL != '')
	$where .= " AND Z_RELEASE_NOTES.KNOWLEDGEBASE_URL like '%$KNOWLEDGEBASE_URL%' ";
	
if($PUSHED_TO_D3 == 1)
	$where .= " AND Z_RELEASE_NOTES.RELEASE_NOTES_PUSHED = 1 ";
else if($PUSHED_TO_D3 == 2)
	$where .= " AND Z_RELEASE_NOTES.RELEASE_NOTES_PUSHED = 0 ";	
	
if($SUBJECT != '')
	$where .= " AND SUBJECT like '%$SUBJECT%' ";
	
if($LOCATION != '')
	$where .= " AND LOCATION like '%$LOCATION%' ";
	
if($DATE_FITER_TYPE == 1)
	$field = "PUSHED_TO_D3_DATE";
else
	$field = "RELEASE_NOTES_PUSHED_DATE";
	
if($START_DATE != '') {
	$START_DATE = date("Y-m-d",strtotime($START_DATE));
}

if($TO_DATE != '') {
	$TO_DATE = date("Y-m-d",strtotime($TO_DATE));
}
	
if($START_DATE != '' && $TO_DATE != '') {
	$where .= " AND $field BETWEEN '$START_DATE' AND '$TO_DATE' ";
} else if($START_DATE != '') {
	$where .= " AND $field >= '$START_DATE' ";
} else if($TO_DATE != '') {
	$where .= " AND $field <='$TO_DATE' ";
}

if($RELEASE_NOTES != '') {
	$where .= " AND (PROGRAMMING_NOTES like '%$RELEASE_NOTES%' OR RELEASE_NOTES like '%$RELEASE_NOTES%') ";
}


$rs = mysql_query("SELECT DISTINCT(PK_RELEASE_NOTES) FROM Z_RELEASE_NOTES LEFT JOIN M_RELEASE_TYPE ON M_RELEASE_TYPE.PK_RELEASE_TYPE = Z_RELEASE_NOTES.PK_RELEASE_TYPE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_RELEASE_NOTES,PK_RELEASE_CATEGORY,RELEASE_TYPE,SUBJECT,LOCATION, PROGRAMMING_NOTES, PUSHED_TO_D3_DATE, RELEASE_NOTES_PUSHED_DATE, IF(RELEASE_NOTES_PUSHED = 1,'Yes','No') as RELEASE_NOTES_PUSHED, KNOWLEDGEBASE_URL FROM Z_RELEASE_NOTES LEFT JOIN M_RELEASE_TYPE ON M_RELEASE_TYPE.PK_RELEASE_TYPE = Z_RELEASE_NOTES.PK_RELEASE_TYPE WHERE " . $where ." " ;
// echo $query;exit;
$_SESSION['REL_QRY'] = 	$query;
$rs = mysql_query($query. " order by $sort $order limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$PK_RELEASE_CATEGORY = $row['PK_RELEASE_CATEGORY'];
	$CATEGORY = '';
	$res_type = $db->Execute("select RELEASE_CATEGORY from M_RELEASE_CATEGORY WHERE PK_RELEASE_CATEGORY IN ($PK_RELEASE_CATEGORY) ORDER BY RELEASE_CATEGORY ASC");
	while (!$res_type->EOF) { 
		if($CATEGORY != '')
			$CATEGORY .= '<br />';
			
		$CATEGORY .= $res_type->fields['RELEASE_CATEGORY'];
		
		$res_type->MoveNext();
	}
	$row['CATEGORY'] = $CATEGORY;
	
	if($row['PUSHED_TO_D3_DATE'] != '0000-00-00')
		$row['PUSHED_TO_D3_DATE'] = date("m/d/Y",strtotime($row['PUSHED_TO_D3_DATE']));
	else
		$row['PUSHED_TO_D3_DATE'] = '';
		
	if($row['RELEASE_NOTES_PUSHED_DATE'] != '0000-00-00')
		$row['RELEASE_NOTES_PUSHED_DATE'] = date("m/d/Y",strtotime($row['RELEASE_NOTES_PUSHED_DATE']));
	else
		$row['RELEASE_NOTES_PUSHED_DATE'] = '';	
		
	$str = '<a href="release_notes?id='.$row['PK_RELEASE_NOTES'].'" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>';
	$str .= '&nbsp;<a href="javascript:void(0)" onclick="delete_row('.$row['PK_RELEASE_NOTES'].')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);