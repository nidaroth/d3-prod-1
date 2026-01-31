<? 
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

$page   = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows   = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort   = isset($_POST['sort']) ? strval($_POST['sort']) : 'COMPANY_NAME';  
$order  = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';

$PK_PLACEMENT_TYPE				= isset($_REQUEST['PK_PLACEMENT_TYPE']) ? ($_REQUEST['PK_PLACEMENT_TYPE']) : '';
$PK_PLACEMENT_COMPANY_STATUS	= isset($_REQUEST['PK_PLACEMENT_COMPANY_STATUS']) ? ($_REQUEST['PK_PLACEMENT_COMPANY_STATUS']) : '';
$JOB_TYPE						= isset($_REQUEST['JOB_TYPE']) ? ($_REQUEST['JOB_TYPE']) : '';
$ACTIVE_COMPANY					= isset($_REQUEST['ACTIVE_COMPANY']) ? ($_REQUEST['ACTIVE_COMPANY']) : '';
$PK_COMPANY_SOURCE				= isset($_REQUEST['PK_COMPANY_SOURCE']) ? ($_REQUEST['PK_COMPANY_SOURCE']) : '';
$PK_CAMPUS						= isset($_REQUEST['PK_CAMPUS']) ? ($_REQUEST['PK_CAMPUS']) : '';

$offset = ($page-1)*$rows;
	
$result = array();
$where  = " S_COMPANY.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
$table	= "";
	
if($SEARCH != '')
	$where .= " AND (COMPANY_NAME like '%$SEARCH%' OR S_COMPANY.ADDRESS like '%$SEARCH%' OR S_COMPANY.CITY like '%$SEARCH%')";
	
if(!empty($PK_PLACEMENT_TYPE) != '') {
	$PK_PLACEMENT_TYPE1 = implode(",",$PK_PLACEMENT_TYPE);
	$where .= " AND S_COMPANY.PK_PLACEMENT_TYPE IN ($PK_PLACEMENT_TYPE1) ";
}

if(!empty($PK_PLACEMENT_COMPANY_STATUS) != '') {
	$PK_PLACEMENT_COMPANY_STATUS1 = implode(",",$PK_PLACEMENT_COMPANY_STATUS);
	$where .= " AND S_COMPANY.PK_PLACEMENT_COMPANY_STATUS IN ($PK_PLACEMENT_COMPANY_STATUS1) ";
}

if(!empty($PK_COMPANY_SOURCE) != '') {
	$PK_COMPANY_SOURCE1 = implode(",",$PK_COMPANY_SOURCE);
	$where .= " AND S_COMPANY.PK_COMPANY_SOURCE IN ($PK_COMPANY_SOURCE1) ";
}

if(!empty($PK_CAMPUS) != '') {
	$table	= ",S_COMPANY_CAMPUS ";
	$PK_CAMPUS = implode(",",$PK_CAMPUS);
	$where .= " AND S_COMPANY_CAMPUS.PK_COMPANY = S_COMPANY.PK_COMPANY  AND S_COMPANY_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
}

$having = "";
if($JOB_TYPE == 1)
	$having = " HAVING OPEN_JOBS > 0 ";
else if($JOB_TYPE == 2)
	$having = " HAVING OPEN_JOBS = 0 ";

if($ACTIVE_COMPANY == 1)
	$where .= " AND S_COMPANY.ACTIVE = 1 ";
else if($ACTIVE_COMPANY == 2)
	$where .= " AND S_COMPANY.ACTIVE = 0 ";

$rs     = mysql_query("SELECT DISTINCT(S_COMPANY.PK_COMPANY)
FROM 
S_COMPANY 
LEFT JOIN Z_STATES ON S_COMPANY.PK_STATES = Z_STATES.PK_STATES 
LEFT JOIN M_COMPANY_SOURCE ON M_COMPANY_SOURCE.PK_COMPANY_SOURCE = S_COMPANY.PK_COMPANY_SOURCE 

LEFT JOIN S_COMPANY_JOB as S_COMPANY_JOB_ALL ON S_COMPANY_JOB_ALL.PK_COMPANY = S_COMPANY.PK_COMPANY AND S_COMPANY_JOB_ALL.ACTIVE = 1 
LEFT JOIN S_COMPANY_JOB as S_COMPANY_JOB_OPEN ON S_COMPANY_JOB_OPEN.PK_COMPANY = S_COMPANY.PK_COMPANY AND S_COMPANY_JOB_OPEN.OPEN_JOB = 'Y' AND S_COMPANY_JOB_OPEN.ACTIVE = 1 
	
LEFT JOIN M_PLACEMENT_TYPE ON M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE=  S_COMPANY.PK_PLACEMENT_TYPE $table 
WHERE " . $where. " GROUP BY S_COMPANY.PK_COMPANY ")or die(mysql_error());
$row    = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query  = "SELECT S_COMPANY.PK_COMPANY, COMPANY_NAME, S_COMPANY.ADDRESS, S_COMPANY.ADDRESS_1, STATE_CODE, S_COMPANY.CITY, M_PLACEMENT_TYPE.TYPE AS PLACEMENT_TYPE, IF(S_COMPANY.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, COUNT(S_COMPANY_JOB_OPEN.PK_COMPANY) AS OPEN_JOBS, COUNT(S_COMPANY_JOB_ALL.PK_COMPANY) AS TOTAL_JOBS, PLACEMENT_COMPANY_STATUS, COMPANY_SOURCE    
FROM 
S_COMPANY 
LEFT JOIN Z_STATES ON S_COMPANY.PK_STATES = Z_STATES.PK_STATES 
LEFT JOIN M_COMPANY_SOURCE ON M_COMPANY_SOURCE.PK_COMPANY_SOURCE = S_COMPANY.PK_COMPANY_SOURCE 
LEFT JOIN M_PLACEMENT_COMPANY_STATUS ON S_COMPANY.PK_PLACEMENT_COMPANY_STATUS = M_PLACEMENT_COMPANY_STATUS.PK_PLACEMENT_COMPANY_STATUS 

LEFT JOIN S_COMPANY_JOB as S_COMPANY_JOB_ALL ON S_COMPANY_JOB_ALL.PK_COMPANY = S_COMPANY.PK_COMPANY AND S_COMPANY_JOB_ALL.ACTIVE = 1 
LEFT JOIN S_COMPANY_JOB as S_COMPANY_JOB_OPEN ON S_COMPANY_JOB_OPEN.PK_COMPANY = S_COMPANY.PK_COMPANY AND S_COMPANY_JOB_OPEN.OPEN_JOB = 'Y' AND S_COMPANY_JOB_OPEN.ACTIVE = 1 
	
LEFT JOIN M_PLACEMENT_TYPE ON M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE=  S_COMPANY.PK_PLACEMENT_TYPE $table 
WHERE " . $where ." GROUP BY S_COMPANY.PK_COMPANY $having ORDER BY $sort $order " ;
// echo $query;exit;	
$rs 	= mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items  = array();
while($row = mysql_fetch_array($rs)) {
	$str  = '&nbsp;<a href="company?id='.$row['PK_COMPANY'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$PK_COMPANY = $row['PK_COMPANY'];
	
	$res_check = $db->Execute("select PK_STUDENT_JOB from S_STUDENT_JOB WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$PK_COMPANY' ");
	if($res_check->RecordCount() == 0)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_COMPANY'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';

	$com_jobs = $db->Execute("SELECT COUNT(PK_COMPANY_JOB) AS OPEN_JOBS FROM S_COMPANY_JOB WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$row[PK_COMPANY]' AND ACTIVE = '1' AND OPEN_JOB = 'Y' ");
	if($com_jobs->RecordCount() > 0) {													
		$row['OPEN_JOBS'] = $com_jobs->fields['OPEN_JOBS'];
	} else {
		$row['OPEN_JOBS'] = 0;
	}
	
	$com_jobs = $db->Execute("SELECT COUNT(PK_COMPANY_JOB) AS TOTAL_JOBS FROM S_COMPANY_JOB WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$row[PK_COMPANY]' AND ACTIVE = '1' ");
	if($com_jobs->RecordCount() > 0) {													
		$row['TOTAL_JOBS'] = $com_jobs->fields['TOTAL_JOBS'];
	} else {
		$row['TOTAL_JOBS'] = 0;
	}
	
	$res_camp = $db->Execute("SELECT GROUP_CONCAT(CAMPUS_CODE ORDER BY CAMPUS_CODE SEPARATOR ', ') as CAMPUS FROM S_COMPANY_CAMPUS, S_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_COMPANY_CAMPUS.PK_CAMPUS AND PK_COMPANY = '$row[PK_COMPANY]' ");
	$row['CAMPUS'] = $res_camp->fields['CAMPUS'];

	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}

$result["rows"] = $items;
echo json_encode($result);